          <?= $this->extend('layout') ?>
<?= $this->section('content') ?>

<div class="row">
    <?php foreach ($products as $key => $item) : ?>
        <div class="col-lg-6 mb-4">
            <div class="card">
                <img src="<?= base_url('img/' . $item['foto']) ?>" class="card-img-top" alt="<?= $item['nama'] ?>">
                <div class="card-body">
                    <h5 class="card-title"><?= $item['nama'] ?></h5>
                    <p class="card-text">Rp <?= number_format($item['harga'], 0, ',', '.') ?></p>
                </div>
            </div>
        </div>
    <?php endforeach ?>
</div>     
<?= $this->endSection() ?>