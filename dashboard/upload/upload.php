<?php
session_start();
require_once('../../private/config/login/db_config.php');

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['username']) || !isset($_POST['title'])) {
        die("Username o title no especificado.");
    }

    $username = htmlspecialchars($_POST['username']);
    $title = htmlspecialchars($_POST['title']);
    $hashtags = isset($_POST['hashtags']) ? implode(", ", $_POST['hashtags']) : ""; 
    $target_dir = "../uploads/";
    $imageFileType = strtolower(pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION));
    
    $clean_title = str_replace([':', '(', ')'], '_', $title);
    $target_file = $target_dir . $clean_title . '.' . $imageFileType;
    $uploadOk = 1;

    $check = getimagesize($_FILES["file"]["tmp_name"]);
    if ($check !== false) {
        echo "Es una imagen y se subió - " . $check["mime"] . ".";
        $uploadOk = 1;
    } else {
        echo "Esto no es una imagen.";
        $uploadOk = 0;
    }

    $counter = 1;
    while (file_exists($target_file)) {
        $target_file = $target_dir . $clean_title . '_' . $counter . '.' . $imageFileType;
        $counter++;
    }

    if ($_FILES["file"]["size"] > 5242880) {
        echo "Tu archivo es muy largo.";
        $uploadOk = 0;
    }

    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        echo "Solo se pueden subir JPG, JPEG, PNG y GIF.";
        $uploadOk = 0;
    }

    if ($uploadOk == 0) {
        echo "Lo sentimos, tu archivo no se subió. ¿Caracteres inválidos quizá?";
    } else {
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            if (!$stmt) {
                die("Error: " . $conn->error);
            }
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->bind_result($user_id);
            $stmt->fetch();
            $stmt->close();

            if (!$user_id) {
                die("No se encontró el usuario.");
            }

            $stmt = $conn->prepare("INSERT INTO posts (user_id, image_path, title, hashtags, upload_date) VALUES (?, ?, ?, ?, NOW())");
            if (!$stmt) {
                die("Error: " . $conn->error);
            }
            $stmt->bind_param("isss", $user_id, $target_file, $title, $hashtags);
            if ($stmt->execute()) {
                echo "Se ha guardado bien tu imagen con hashtags.";
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Hubo un error al subir tu imagen.";
        }
    }
    $conn->close();
}
header("Location: ../");
exit();
?>