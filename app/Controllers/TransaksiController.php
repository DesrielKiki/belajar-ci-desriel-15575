<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Services\RajaOngkirService;
use App\Models\TransactionModel;
use App\Models\TransactionDetailModel;
use App\Models\ProductModel;

class TransaksiController extends BaseController
{

protected $cart;
protected $transactionModel;
protected $transactionDetailModel;
protected $productModel;

public function __construct()
{
    helper(['number', 'form', 'transaksi']);
    $this->cart = service('cart');
    $this->transactionModel = new TransactionModel();
    $this->transactionDetailModel = new TransactionDetailModel();
    $this->productModel = new ProductModel();
}

    public function index()
{  
    $items = $this->cart->contents();
    $productIds = array_column($items, 'id');
    $products = empty($productIds) ? [] : $this->productModel->whereIn('id', $productIds)->findAll();
    $stokById = array_column($products, 'jumlah', 'id');

    foreach ($items as $rowid => $item) {
        $items[$rowid]['stok'] = $stokById[$item['id']] ?? 0;
    }

    $data = [
        'items' => $items,
        'total' => $this->cart->total()  
    ];

    return view('v_keranjang', $data);
}

public function cart_add()
{
	$productId = $this->request->getPost('id');
	$product = $this->productModel->find($productId);

	if (!$product) {
	    return redirect()->back();
	}

	$existingQty = 0;
	foreach ($this->cart->contents() as $item) {
	    if ($item['id'] == $productId) {
	        $existingQty = $item['qty'];
	        break;
	    }
	}

	if ($existingQty + 1 > $product['jumlah']) {
	    session()->setFlashdata('error', 'Stok produk "' . $product['nama'] . '" tidak mencukupi.');
	    return redirect()->back();
	}

	$this->cart->insert([
	    'id'      => $productId,
	    'qty'     => 1,
	    'price'   => $this->request->getPost('harga'),
	    'name'    => $this->request->getPost('nama'),
	    'options' => [
	        'foto' => $this->request->getPost('foto')
	    ]
	]);
	
	session()->setFlashdata(
	    'success',
	    'Produk berhasil ditambahkan ke keranjang. 
	    <a href="' . base_url('keranjang') . '">Lihat</a>'
	);
	
	return redirect()->to(base_url('/'));
} 

private function updateCartQuantitiesFromRequest(): bool
{
    $i = 1;
    $stokTidakCukup = false;

    foreach ($this->cart->contents() as $item) {
        $qty = (int) $this->request->getPost('qty' . $i++);
        $product = $this->productModel->find($item['id']);
        $stok = $product['jumlah'] ?? 0;
        $batasQty = max($stok, (int) $item['qty']);

        if ($qty > $batasQty) {
            $qty = $batasQty;
            $stokTidakCukup = true;
        }

        $this->cart->update([
            'rowid' => $item['rowid'],
            'qty'   => $qty
        ]);
    }

    return $stokTidakCukup;
}

public function cart_edit()
{
    $stokTidakCukup = $this->updateCartQuantitiesFromRequest();

    if ($stokTidakCukup) {
        session()->setFlashdata(
            'error',
            'Sebagian jumlah pembelian disesuaikan karena melebihi stok yang tersedia.'
        );
    }

    session()->setFlashdata(
        'success',
        'Keranjang berhasil diperbarui'
    );

    return redirect()->to(base_url('keranjang'));
}

public function cart_checkout()
{
    $stokTidakCukup = $this->updateCartQuantitiesFromRequest();

    if ($stokTidakCukup) {
        session()->setFlashdata(
            'error',
            'Sebagian jumlah pembelian disesuaikan karena melebihi stok yang tersedia.'
        );
    }

    return redirect()->to(base_url('checkout'));
}

public function cart_delete($rowid)
{
    $this->cart->remove($rowid);

    session()->setFlashdata(
        'success',
        'Produk berhasil dihapus dari keranjang'
    );

    return redirect()->to(base_url('keranjang'));
}

public function cart_clear()
{
    $this->cart->destroy();

    session()->setFlashdata(
        'success',
        'Keranjang berhasil dikosongkan'
    );

    return redirect()->to(base_url('keranjang'));
}

public function checkout()
{
    $data = [
        'items' => $this->cart->contents(),
        'total' => $this->cart->total()
    ];

    return view('v_checkout', $data);
}

public function destinations()
{
    $search = $this->request->getGet('q');

    $service = new RajaOngkirService();
    $response = $service->getDestination($search);

    $results = [];
    $data = $response['data'] ?? [];

    foreach ($data as $item) {
        $results[] = [
            'id'   => $item['id'],
            'text' => $item['label']
        ];
    }

    return $this->response->setJSON([
        'results' => $results
    ]);
}

public function costs()
{
    $origin = '64999';
    $destination = $this->request->getGet('destination');
    $weight = '1000';
    $courier = 'jne';

    $service = new RajaOngkirService();
    $response = $service->getCost($origin, $destination, $weight, $courier);

    $results = [];
    $data = $response['data'] ?? [];

    foreach ($data as $item) {
        $results[] = [
            'service'     => $item['service'],
            'description' => $item['description'],
            'cost'        => $item['cost'],
            'etd'         => $item['etd']
        ];
    }

    return $this->response->setJSON($results);
}

public function buy()
{
    $cartItems = $this->cart->contents();

    if (empty($cartItems)) {
        return redirect()->back();
    }

    foreach ($cartItems as $item) {
        $product = $this->productModel->find($item['id']);
        $stok = $product['jumlah'] ?? 0;

        if ($item['qty'] > $stok) {
            session()->setFlashdata('error', 'Stok produk "' . $item['name'] . '" tidak mencukupi.');
            return redirect()->back();
        }
    }

    $db = \Config\Database::connect();
    $db->transStart();

    $subtotal = 0;
    foreach ($cartItems as $item) {
        $subtotal += $item['qty'] * $item['price'];
    }

    $ongkir = (int) $this->request->getPost('ongkir');
    $kuponCode = $this->request->getPost('kupon_code');

    $biayaAdmin = hitung_biaya_admin($subtotal);
    $diskonKupon = hitung_diskon_kupon($subtotal, $kuponCode);
    $cashback = hitung_cashback($subtotal);

    $totalHarga = $subtotal - $diskonKupon + $biayaAdmin + $ongkir;

    $transaction = [
        'username'     => $this->request->getPost('username'),
        'alamat'       => $this->request->getPost('alamat'),
        'ongkir'       => $ongkir,
        'total_harga'  => $totalHarga,
        'status'       => 0,
        'biaya_admin'  => $biayaAdmin,
        'kupon_code'   => $kuponCode ?: null,
        'diskon_kupon' => $diskonKupon,
        'cashback'     => $cashback,
    ];

    if (!$this->transactionModel->insert($transaction)) {
        $db->transRollback();
        return redirect()->back()->with('error', 'Gagal membuat transaksi');
    }

    $transactionId = $this->transactionModel->getInsertID();

    foreach ($cartItems as $item) {
        $this->transactionDetailModel->insert([
            'transaction_id' => $transactionId,
            'product_id'     => $item['id'],
            'jumlah'         => $item['qty'],
            'diskon'         => 0,
            'subtotal_harga' => $item['qty'] * $item['price']
        ]);

        $this->productModel->set('jumlah', 'jumlah - ' . (int) $item['qty'], false)
            ->where('id', $item['id'])
            ->update();
    }

    $db->transComplete();

    if (!$db->transStatus()) {
        return redirect()->back()->with('error', 'Gagal membuat transaksi');
    }

    $this->cart->destroy();
    session()->setFlashdata('order_success', 'Pesanan kamu berhasil dibuat dan sedang diproses.');
    return redirect()->to(base_url());
}
public function history()
{
    $username = session()->get('username'); 
 
    $transactions = $this->transactionModel->where('username', $username)->findAll();
    $transactionIds = array_column($transactions, 'id');

    $products = $this->transactionDetailModel->getProductsByTransactionIds($transactionIds);

    $data = [
        'username'      => $username,
        'transactions'  => $transactions,
        'products'      => $products
    ]; 

    return view('v_history', $data);
}   

}