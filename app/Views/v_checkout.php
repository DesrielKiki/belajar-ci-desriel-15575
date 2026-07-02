<?= $this->extend('layout') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-lg-6">
        <?= form_open('buy', ['class' => 'row g-3', 'id' => 'checkoutForm']) ?>

        <?= form_hidden('username', session()->get('username')) ?>
        <?= form_input([
            'type' => 'hidden',
            'name' => 'total_harga',
            'id'   => 'total_harga']) ?>

        <div class="col-12">
            <?= form_label('Nama', 'nama', ['class' => 'form-label']) ?>
            <?= form_input([
                'name'     => 'nama',
                'id'       => 'nama',
                'class'    => 'form-control',
                'value'    => session()->get('username'),
                'readonly' => true]) ?>
        </div>
        <div class="col-12">
            <?= form_label('Alamat', 'alamat', ['class' => 'form-label']) ?>
            <?= form_input([
                'name'     => 'alamat',
                'id'       => 'alamat',
                'class'    => 'form-control',
                'required' => true]) ?>
        </div>
        <div class="col-12">
            <?= form_label('Kelurahan', 'kelurahan', ['class' => 'form-label']) ?>
            <?= form_dropdown('kelurahan', [], '', ['id' => 'kelurahan', 'class' => 'form-control', 'required' => 'required']) ?>
        </div>
        <div class="col-12">
            <?= form_label('Layanan', 'layanan', ['class' => 'form-label']) ?>
            <?= form_dropdown('layanan', [], '', ['id' => 'layanan', 'class' => 'form-select', 'required' => 'required']) ?>
        </div>
        <div class="col-12">
            <?= form_label('Ongkir', 'ongkir', ['class' => 'form-label']) ?>
            <?= form_input([
                'name'     => 'ongkir',
                'id'       => 'ongkir',
                'class'    => 'form-control',
                'readonly' => true]) ?>
        </div>
        <div class="col-12">
            <?= form_label('Kode Kupon', 'kupon_code', ['class' => 'form-label']) ?>
            <?= form_input([
                'name'  => 'kupon_code',
                'id'    => 'kupon_code',
                'class' => 'form-control']) ?>
            <div class="form-text">Tersedia: HEMAT (15%), SUPER (20%)</div>
        </div>
        <div class="col-12">
            <?= form_submit(
                'submit',
                'Buat Pesanan',
                ['class' => 'btn btn-primary']) ?>
        </div>

        <?= form_close() ?>

        <div class="modal fade" id="kuponInvalidModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-warning-subtle">
                        <h5 class="modal-title">
                            <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                            Kupon Tidak Tersedia
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0">
                            Kode kupon <strong id="kuponInvalidCode"></strong> tidak ditemukan, sehingga tidak ada diskon kupon yang akan diberikan.
                        </p>
                        <p class="mb-0">Lanjutkan transaksi tanpa diskon kupon?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-warning" id="btnLanjutkanTanpaKupon">Lanjutkan Tanpa Diskon</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Nama</th>
                    <th scope="col">Harga</th>
                    <th scope="col">Jumlah</th>
                    <th scope="col">Sub Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($items)) :
                    foreach ($items as $index => $item) :
                ?>
                        <tr>
                            <td><?= $item['name'] ?></td>
                            <td><?= number_to_currency($item['price'], 'IDR') ?></td>
                            <td><?= $item['qty'] ?></td>
                            <td><?= number_to_currency($item['price'] * $item['qty'], 'IDR') ?></td>
                        </tr>
                <?php
                    endforeach;
                endif;
                ?>
                <tr>
                    <td colspan="2"></td>
                    <td>Subtotal</td>
                    <td><?= number_to_currency($total, 'IDR') ?></td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                    <td>Diskon Kupon</td>
                    <td><span id="diskon_kupon">-<?= number_to_currency(0, 'IDR') ?></span></td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                    <td>Biaya Admin</td>
                    <td><span id="biaya_admin"><?= number_to_currency(0, 'IDR') ?></span></td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                    <td>Cashback</td>
                    <td><span id="cashback"><?= number_to_currency(0, 'IDR') ?></span></td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                    <td>Subtotal (+Admin-Kupon)</td>
                    <td><span id="subtotal_adjusted"><?= number_to_currency($total, 'IDR') ?></span></td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                    <td>Grand Total (incl Ongkir)</td>
                    <td><span id="total"><?= number_to_currency($total, 'IDR') ?></span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('script') ?>
<script>
$(document).ready(function() {
    let ongkir = 0;
    let subtotal = <?= $total ?>;
    hitungTotal();

    function hitungBiayaAdmin(totalHarga) {
        let tarif = totalHarga > 20000000 ? 0.0075 : 0.005;
        return totalHarga * tarif;
    }

    function hitungDiskonKupon(totalHarga, kuponCode) {
        let kupon = {
            'HEMAT': 0.15,
            'SUPER': 0.20
        };

        kuponCode = (kuponCode || '').toUpperCase();

        if (!(kuponCode in kupon)) {
            return 0;
        }

        return totalHarga * kupon[kuponCode];
    }

    function hitungCashback(totalHarga) {
        if (totalHarga <= 10000000) {
            return 0;
        }

        return totalHarga * 0.02;
    }

    function hitungTotal() {
        let kuponCode = $("#kupon_code").val();
        let diskonKupon = hitungDiskonKupon(subtotal, kuponCode);
        let biayaAdmin = hitungBiayaAdmin(subtotal);
        let cashback = hitungCashback(subtotal);
        let subtotalAdjusted = subtotal - diskonKupon + biayaAdmin;
        let total = subtotalAdjusted + ongkir;

        $("#ongkir").val(ongkir);
        $("#diskon_kupon").text(`-IDR ${diskonKupon.toLocaleString('id-ID')}`);
        $("#biaya_admin").text(`IDR ${biayaAdmin.toLocaleString('id-ID')}`);
        $("#cashback").text(`IDR ${cashback.toLocaleString('id-ID')}`);
        $("#subtotal_adjusted").text(`IDR ${subtotalAdjusted.toLocaleString('id-ID')}`);
        $("#total").text(`IDR ${total.toLocaleString('id-ID')}`);
        $("#total_harga").val(total);
    }

    $("#kupon_code").on('input', function() {
        hitungTotal();
    });

    $('#kelurahan').select2({
        placeholder: 'Cari daerah tujuan',
        minimumInputLength: 3,
        ajax: {
            url: '<?= site_url('ajax/destinations') ?>',
            dataType: 'json',
            delay: 300,
            data: function(params) {
                return {
                    q: params.term
                };
            },
            processResults: function(data) {
                return data;
            },
            cache: true
        }
    });

    $("#kelurahan").on('change', function () {
        let id_kelurahan = $(this).val();

        $("#layanan").empty();
        ongkir = 0;
        hitungTotal();

        $.ajax({
            url: "<?= site_url('ajax/costs') ?>",
            dataType: "json",
            data: {
                destination: id_kelurahan
            },
            success: function (data) {
                data.forEach(function (item) {
                    $("#layanan").append(
                        $('<option>', {
                            value: item.cost,
                            text: `${item.description} (${item.service}) : estimasi ${item.etd}`
                        })
                    );
                });
            }
        });
    });

    $("#layanan").on('change', function() {
        ongkir = parseInt($(this).val());
        hitungTotal();
    });

    let lewatiCekKupon = false;
    let kuponInvalidModal = new bootstrap.Modal(document.getElementById('kuponInvalidModal'));
    let checkoutFormElement = document.getElementById('checkoutForm');

    $("#checkoutForm").on('submit', function(e) {
        let kuponCode = $("#kupon_code").val().trim().toUpperCase();
        let kuponValid = ['HEMAT', 'SUPER'];

        if (kuponCode && kuponValid.indexOf(kuponCode) === -1 && !lewatiCekKupon) {
            e.preventDefault();
            $("#kuponInvalidCode").text(kuponCode);
            kuponInvalidModal.show();
            return false;
        }
    });

    $("#btnLanjutkanTanpaKupon").on('click', function() {
        lewatiCekKupon = true;
        kuponInvalidModal.hide();
        HTMLFormElement.prototype.submit.call(checkoutFormElement);
    });
});
</script>
<?= $this->endSection() ?>
