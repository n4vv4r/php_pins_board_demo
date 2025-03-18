<?php
session_start();
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('Location: dashboard/');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>web sin nombre aun</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
</head>
<body>
    <div class="container">
        <div class="image-section">
            <img src="a.png" alt="imagen">
        </div>

        <div class="content-section">
            <h1>Lorem ipsum dolor, sit amet consectetur.</h1>
            <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit.</p>
            <button class="login-btn" onclick="window.location.href='login/';">Iniciar sesión</button>
            <button class="register-btn" onclick="window.location.href='register/';">Registrarse</button>
        </div>
    </div>
</body>
</html>
