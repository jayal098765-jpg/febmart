<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

require_once __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: profil.php");
    exit;
}

$userId = (int)$_SESSION['user_id'];
$name = trim($_POST['name'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$passwordBaru = (string)($_POST['password_baru'] ?? '');
$konfirmasi = (string)($_POST['konfirmasi_password'] ?? '');
$passwordLama = (string)($_POST['password_lama'] ?? '');

function redirectProfilError(string $pesan): void
{
    $_SESSION['flash_error'] = $pesan;
    header("Location: profil.php");
    exit;
}

if ($name === '' || $username === '') {
    redirectProfilError('Nama dan username wajib diisi.');
}

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectProfilError('Format email tidak valid.');
}

$stmt = $conn->prepare("SELECT id, password FROM users WHERE id = ? AND is_active = 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$userRow = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$userRow) {
    session_destroy();
    header("Location: auth/login.php");
    exit;
}

$check = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
$check->bind_param("si", $username, $userId);
$check->execute();

if ($check->get_result()->num_rows > 0) {
    $check->close();
    $conn->close();
    redirectProfilError('Username sudah digunakan oleh pengguna lain.');
}

$check->close();

if ($email !== '') {
    $check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $check->bind_param("si", $email, $userId);
    $check->execute();

    if ($check->get_result()->num_rows > 0) {
        $check->close();
        $conn->close();
        redirectProfilError('Email sudah digunakan oleh pengguna lain.');
    }

    $check->close();
}

$hashBaru = null;

if ($passwordBaru !== '' || $konfirmasi !== '') {
    if (strlen($passwordBaru) < 6) {
        $conn->close();
        redirectProfilError('Password baru minimal 6 karakter.');
    }

    if ($passwordBaru !== $konfirmasi) {
        $conn->close();
        redirectProfilError('Konfirmasi password tidak cocok.');
    }

    if (!password_verify($passwordLama, $userRow['password'])) {
        $conn->close();
        redirectProfilError('Password lama salah. Gagal mengubah password.');
    }

    $hashBaru = password_hash($passwordBaru, PASSWORD_DEFAULT);
}

if ($hashBaru !== null) {
    $update = $conn->prepare(
        "UPDATE users SET name = ?, username = ?, email = ?, password = ? WHERE id = ?"
    );
    $update->bind_param("ssssi", $name, $username, $email, $hashBaru, $userId);
} else {
    $update = $conn->prepare(
        "UPDATE users SET name = ?, username = ?, email = ? WHERE id = ?"
    );
    $update->bind_param("sssi", $name, $username, $email, $userId);
}

if (!$update->execute()) {
    $error = $update->error;
    $update->close();
    $conn->close();
    redirectProfilError('Gagal memperbarui profil: ' . $error);
}

$update->close();
$conn->close();

$_SESSION['name'] = $name;
$_SESSION['username'] = $username;

header("Location: profil.php?status=berhasil");
exit;
