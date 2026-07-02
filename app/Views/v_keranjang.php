<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<?php
if (session()->getFlashData('success')) {
?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= session()->getFlashData('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php
}

if (session()->getFlashData('error')) {
?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= session()->getFlashData('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php
}
?> 
<?php echo form_open('keranjang/edit') ?>
<!-- Table with stripped rows -->
<table class="table datatable">
    <thead>
        <tr>
            <th scope="col">Nama</th>
            <th scope="col">Foto</th>
            <th scope="col">Harga</th> 
            <th scope="col">Jumlah</th>
<th scope="col">Subtotal</th>
<th scope="col">Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $i = 1;
        if (!empty($items)) :
            foreach ($items as $index => $item) :
        ?>
                <tr>
                    <td>
                        <?php echo $item['name'] ?>
                        <?php if ((int) $item['stok'] <= 0) : ?>
                            <br><span class="badge bg-danger">Stok Habis</span>
                        <?php endif; ?>
                    </td>
                    <td><img src="<?php echo base_url() . "img/" . $item['options']['foto'] ?>" width="100px"></td>
                    <td><?php echo number_to_currency($item['price'], 'IDR') ?></td> 
                    <td><input type="number" min="1" name="qty<?php echo $i ?>" class="form-control qty-input" data-nama="<?php echo esc($item['name']) ?>" data-max="<?php echo max((int) $item['stok'], (int) $item['qty']) ?>" data-price="<?php echo $item['price'] ?>" data-index="<?php echo $i ?>" value="<?php echo $item['qty'] ?>"></td>
<td><span id="subtotal-<?php echo $i ?>"><?php echo number_to_currency($item['subtotal'], 'IDR') ?></span></td>
<td>
    <a href="<?php echo base_url('keranjang/delete/' . $item['rowid'] . '') ?>" class="btn btn-danger"><i class="bi bi-trash"></i></a>
</td>
                </tr>
                <?php $i++; ?>
        <?php
            endforeach;
        endif;
        ?>
    </tbody>
</table> 

<div class="alert alert-info">
    <span id="cartTotal"><?php echo "Total = " . number_to_currency($total, 'IDR') ?></span>
</div>

<button type="submit" class="btn btn-primary">Perbarui Keranjang</button>

<a class="btn btn-warning" href="<?php echo base_url() ?>keranjang/clear">Kosongkan Keranjang</a>

<?php if (!empty($items)) : ?>
    <button type="submit" class="btn btn-success" formaction="<?php echo base_url('keranjang/checkout') ?>">Selesai Belanja</button>
<?php endif; ?>

<?php echo form_close() ?>

<div class="modal fade" id="qtyLimitModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning-subtle">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                    Stok Tidak Mencukupi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0" id="qtyLimitMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Oke</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('script') ?>
<script>
$(document).ready(function() {
    let qtyLimitModal = new bootstrap.Modal(document.getElementById('qtyLimitModal'));

    function recalcCart() {
        let total = 0;

        $('.qty-input').each(function() {
            let price = parseFloat($(this).data('price'));
            let index = $(this).data('index');
            let qty = parseInt($(this).val()) || 0;
            let subtotal = price * qty;

            $('#subtotal-' + index).text('IDR ' + subtotal.toLocaleString('id-ID'));
            total += subtotal;
        });

        $('#cartTotal').text('Total = IDR ' + total.toLocaleString('id-ID'));
    }

    $('.qty-input').on('input', function() {
        let $input = $(this);
        let max = parseInt($input.data('max'));
        let val = parseInt($input.val());
        let nama = $input.data('nama');

        if (!isNaN(val) && val > max) {
            $input.val(max);
            $('#qtyLimitMessage').text('Stok produk "' + nama + '" hanya tersedia sampai ' + max + ' pcs.');
            qtyLimitModal.show();
        }

        recalcCart();
    });
});
</script>
<?= $this->endSection() ?>