
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


<!-- Table with stripped rows -->
     <div class="row">
    <?php foreach ($products as $key => $item) : ?>         
            <div class="col-lg-6">
                <?= form_open('keranjang') ?>
<?php
echo form_hidden('id', $item['id']);
echo form_hidden('nama', $item['nama']);
echo form_hidden('harga', $item['harga']);
echo form_hidden('foto', $item['foto']);
?>
                <div class="card">
                    <div class="card-body">
                        <img src="<?= base_url() . "img/" . $item['foto'] ?>" alt="..." width="50%">
                        <h5 class="card-title"><?= $item['nama'] ?></h5>
                        <p class="card-text"><?= number_to_currency($item['harga'], 'IDR') ?></p>
                        <?php if ((int) $item['jumlah'] <= 0) : ?>
                            <p class="mb-2"><span class="badge bg-danger">Stok Habis / Out of Stock</span></p>
                        <?php else : ?>
                            <p class="text-muted mb-2">Stok: <?= $item['jumlah'] ?></p>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-info rounded-pill" <?= ((int) $item['jumlah'] <= 0) ? 'disabled' : '' ?>>Beli</button>
                    </div>
                </div>
                <?= form_close() ?>
            </div> 
    <?php endforeach ?> 
</div>
              <!-- End Table with stripped rows -->

<?php if (session()->getFlashdata('order_success')) : ?>
    <div class="modal fade" id="orderSuccessModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success-subtle">
                    <h5 class="modal-title">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        Pesanan Berhasil
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0"><?= session()->getFlashdata('order_success') ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">Oke</button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

              <?= $this->endSection() ?>

<?php if (session()->getFlashdata('order_success')) : ?>
<?= $this->section('script') ?>
<script>
$(document).ready(function() {
    new bootstrap.Modal(document.getElementById('orderSuccessModal')).show();
});
</script>
<?= $this->endSection() ?>
<?php endif; ?>