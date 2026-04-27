<?= $this->extend('layout') ?>
<?= $this->section('content') ?>

<section class="section profile">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Profil Pengguna</h5>

                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><strong>Username:</strong> <?= esc($username) ?></li>
                        <li class="list-group-item"><strong>Role:</strong> <?= esc($role) ?></li>
                        <li class="list-group-item"><strong>Email:</strong> <?= esc($email) ?></li>
                        <li class="list-group-item"><strong>Waktu Login:</strong> <?= esc($waktu_login) ?></li>
                        <li class="list-group-item"><strong>Status Login:</strong> <?= esc($status_login) ?></li>
                    </ul>

                </div>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>