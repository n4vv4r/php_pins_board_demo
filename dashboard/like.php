<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(["error" => "No autenticado"]);
    http_response_code(403);
    exit;
}

require_once('../private/config/login/db_config.php');

if (!isset($servername) || !isset($username) || !isset($password) || !isset($dbname) || !isset($port)) {
    echo json_encode(["error" => "Error en configuración"]);
    http_response_code(500);
    exit;
}

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    echo json_encode(["error" => "Error de conexión"]);
    http_response_code(500);
    exit;
}

$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
if (!$stmt) {
    echo json_encode(["error" => "Error en SELECT"]);
    http_response_code(500);
    exit;
}
$stmt->bind_param("s", $_SESSION['usuario']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$user_id = $user['id'] ?? null;
$stmt->close();

if (!$user_id) {
    echo json_encode(["error" => "Usuario no encontrado"]);
    http_response_code(500);
    exit;
}

$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;

if ($post_id <= 0) {
    echo json_encode(["error" => "ID de post inválido"]);
    http_response_code(400);
    exit;
}

$checkStmt = $conn->prepare("SELECT COUNT(*) FROM likes WHERE user_id = ? AND post_id = ?");
$checkStmt->bind_param("ii", $user_id, $post_id);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();
$hasLiked = $checkResult->fetch_row()[0] > 0;
$checkStmt->close();

if ($hasLiked) {
    $stmt = $conn->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ?");
    $stmt->bind_param("ii", $user_id, $post_id);
    $stmt->execute();
    $stmt->close();
    $status = "unliked";
} else {
    $stmt = $conn->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $post_id);
    $stmt->execute();
    $stmt->close();
    $status = "liked";
}

$countStmt = $conn->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ?");
$countStmt->bind_param("i", $post_id);
$countStmt->execute();
$countResult = $countStmt->get_result();
$likeCount = $countResult->fetch_row()[0];
$countStmt->close();

$conn->close();

echo json_encode(["status" => $status, "likeCount" => $likeCount]);
?>
