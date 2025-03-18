<?php
session_start();
require_once('../../private/config/login/db_config.php');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    die("Debes iniciar sesión.");
}

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Error de conexión.");
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || !$user['is_admin']) {
    die("No tienes permisos para acceder a esta página.");
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $post_id = intval($_GET['id']);

    $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $stmt->close();

    echo "El post ha sido eliminado con éxito.";
    header("Location: /rogeryte.xyz/dashboard/");
    exit;
} else {
    die("No se ha especificado un ID de publicación válido.");
}
?>
