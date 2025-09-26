<?php
session_start();
include 'db.php';

$action = $_POST['action'];
$username = $_POST['username'];
$password = $_POST['password'];
$role = $_POST['role'];

if ($action === 'signup') {
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $hashed, $role);
    if ($stmt->execute()) {
        $_SESSION['id'] = $stmt->insert_id;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;
        header("Location: ../" . $role . ".php");
        exit;
    } else {
        echo "Signup failed: " . $stmt->error;
    }
} elseif ($action === 'login') {
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header("Location: ../" . $user['role'] . ".php");
            exit;
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "User not found.";
    }
}
?>
