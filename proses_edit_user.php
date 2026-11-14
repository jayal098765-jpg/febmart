<?php
session_start();

require_once __DIR__ . '/includes/role_guard.php';
requireRole(['Admin']);

require_once __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: users.php");
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$role = strtolower(trim($_POST['role'] ?? ''));

if (
    $id <= 0 ||
    $name === '' ||
    $username === '' ||
    !in_array($role, ['admin', 'kasir'], true)
) {
    die("Data pengguna tidak valid!");
}

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Format email tidak valid!");
}

$check = $conn->prepare(
    "SELECT id FROM users WHERE username = ? AND id != ?"
);
$check->bind_param("si", $username, $id);
$check->execute();

if ($check->get_result()->num_rows > 0) {
    $check->close();
    die("Username sudah digunakan oleh pengguna lain!");
}

$check->close();

if ($email !== '') {
    $check = $conn->prepare(
        "SELECT id FROM users WHERE email = ? AND id != ?"
    );
    $check->bind_param("si", $email, $id);
    $check->execute();

    if ($check->get_result()->num_rows > 0) {
        $check->close();
        die("Email sudah digunakan oleh pengguna lain!");
    }

    $check->close();
}

$stmt = $conn->prepare(
    "UPDATE users SET name = ?, username = ?, email = ?, role = ? WHERE id = ?"
);

if (!$stmt) {
    die("Gagal menyiapkan query: " . $conn->error);
}

$stmt->bind_param(
    "ssssi",
    $name,
    $username,
    $email,
    $role,
    $id
);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();

    header("Location: users.php?status=edit_berhasil");
    exit;
}

$error = $stmt->error;

$stmt->close();
$conn->close();

die("Gagal memperbarui pengguna: " . $error);
