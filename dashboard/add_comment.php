<?php
session_start();
require_once('../private/config/login/db_config.php');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    die("No estás autenticado.");
}

if (!isset($_SESSION['usuario'])) {
    die("Error: No se ha definido el usuario en la sesión.");
}

$web_username = $_SESSION['usuario']; 
$post_id = intval($_POST['post_id']);
$comment = trim($_POST['comment']);

if (empty($comment)) {
    die("Comentario vacío.");
}

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $web_username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    die("Error: El usuario '$web_username' no existe en la base de datos.");
}

$row = $result->fetch_assoc();
$user_id = $row['id']; 
$stmt->close();

$stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $post_id, $user_id, $comment);
if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    header("Location: ../pin/" . $post_id);
        exit;
} else {
    $error = $conn->error;
    $stmt->close();
    $conn->close();
    die("Error al insertar el comentario: " . $error);
}
?>