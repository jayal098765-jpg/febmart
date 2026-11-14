<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

require_once __DIR__ . '/config/database.php';

$userId = (int)$_SESSION['user_id'];

$stmt = $conn->prepare(
    "SELECT id, name, username, email, role, photo FROM users WHERE id = ? AND is_active = 1"
);
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    session_destroy();
    header("Location: auth/login.php");
    exit;
}

$pesanError = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);

$pageTitle = 'Profil Saya';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/helper.php';
?>
<?php include __DIR__ . '/includes/navbar.php'; ?>

<div class="app-layout d-flex flex-grow-1">

    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main-content flex-grow-1 p-4">

        <div class="mb-4">
            <h3 class="fw-bold"><i class="bi bi-person-badge me-2"></i>Profil Saya</h3>
            <p class="text-muted">Perbarui informasi akun kamu di FEBMart.</p>
        </div>

        <?php if (isset($_GET['status']) && $_GET['status'] === 'berhasil'): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle-fill me-2"></i>
                Profil berhasil diperbarui!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($pesanError !== ''): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= htmlspecialchars($pesanError); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm" style="max-width: 600px;">
            <div class="card-body p-4">

                <div class="d-flex align-items-center gap-3 mb-4">
                    <img src="<?= $baseUrl ?>/assets/img/avatar-default.svg"
                        width="70" height="70"
                        class="rounded-circle border"
                        alt="Foto profil">

                    <div>
                        <h5 class="fw-bold mb-1"><?= htmlspecialchars($user['name']); ?></h5>
                        <span class="badge bg-primary text-white border-0">
                            <?= htmlspecialchars(ucfirst($user['role'])); ?>
                        </span>
                    </div>
                </div>

                <form action="proses_profil.php" method="POST">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control"
                            value="<?= htmlspecialchars($user['name']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Username</label>
                        <input type="text" name="username" class="form-control"
                            value="<?= htmlspecialchars($user['username']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="email" class="form-control"
                            value="<?= htmlspecialchars($user['email'] ?? ''); ?>">
                    </div>

                    <hr>

                    <p class="text-muted small mb-2">
                        <i class="bi bi-shield-lock me-1"></i>
                        Kosongkan kolom password jika tidak ingin mengganti password.
                    </p>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Password Lama</label>
                        <input type="password" name="password_lama" class="form-control"
                            placeholder="Diperlukan jika mengganti password" autocomplete="current-password">
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-sm-6">
                            <label class="form-label fw-bold">Password Baru</label>
                            <input type="password" name="password_baru" class="form-control"
                                placeholder="Minimal 6 karakter" autocomplete="new-password">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-bold">Konfirmasi Password</label>
                            <input type="password" name="konfirmasi_password" class="form-control"
                                placeholder="Ulangi password baru" autocomplete="new-password">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between pt-3 border-top">
                        <a href="dashboard.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Simpan Perubahan
                        </button>
                    </div>

                </form>

            </div>
        </div>

    </main>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
