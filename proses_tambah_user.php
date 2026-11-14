<?php
session_start();

require_once __DIR__ . '/includes/role_guard.php';
requireRole(['Admin']);

require_once __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: tambah_user.php");
    exit;
}

$name = trim($_POST['name'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role = strtolower(trim($_POST['role'] ?? ''));

if (
    $name === '' ||
    $username === '' ||
    $password === '' ||
    !in_array($role, ['admin', 'kasir'], true)
) {
    die("Semua data wajib diisi dengan benar!");
}

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Format email tidak valid!");
}

$check = $conn->prepare("SELECT id FROM users WHERE username = ?");
$check->bind_param("s", $username);
$check->execute();

if ($check->get_result()->num_rows > 0) {
    $check->close();
    die("Username sudah digunakan! Silakan gunakan username lain.");
}

$check->close();

if ($email !== '') {
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();

    if ($check->get_result()->num_rows > 0) {
        $check->close();
        die("Email sudah digunakan! Silakan gunakan email lain.");
    }

    $check->close();
}

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare(
    "INSERT INTO users (name, username, email, password, role)
     VALUES (?, ?, ?, ?, ?)"
);

if (!$stmt) {
    die("Gagal menyiapkan query: " . $conn->error);
}

$stmt->bind_param(
    "sssss",
    $name,
    $username,
    $email,
    $hashed_password,
    $role
);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();

    header("Location: users.php?status=berhasil");
    exit;
}

$error = $stmt->error;

$stmt->close();
$conn->close();

die("Gagal menambahkan pengguna: " . $error);
