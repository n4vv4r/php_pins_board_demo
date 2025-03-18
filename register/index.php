<?php
require_once('../private/config/login/db_config.php'); 

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    exit("conexion con la db fallida");
}

session_start();

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("location: ../dashboard/");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    if (empty($username)) {
        $error = "Nombre de usuario inválido.";
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email inválido.";
    } else {
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        if (empty($password) || empty($confirm_password)) {
            $error = "La contraseña no puede estar vacía.";
        } elseif ($password !== $confirm_password) {
            $error = "Las contraseñas no son iguales.";
        } else {
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error = "el nombre de usuario o email ya existe.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $username, $email, $hashed_password);

                if ($stmt->execute() === TRUE) {
                    header("Location: ../login/");
                    exit();
                } else {
                    $error = "error: " . $stmt->error;
                }
            }

            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>web sin nombre aun | Registro</title>
    <link rel="icon" href="../favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <div class="left">
        <img src="../FOTO.png" alt="sin foto aun">
    </div>
    <div class="right">
        <h2>Regístrate</h2>
        <form method="post" action="">
            <?php if (!empty($error)): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>

            <label for="username">Nombre de usuario</label>
            <input type="text" id="username" name="username" required>

            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required>

            <label for="confirm_password">Confirmar contraseña</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <button type="submit">Crear cuenta</button>
        </form>

        <p>¿Ya tienes cuenta? <a href="../login">Inicia sesión</a></p>
    </div>
</div>

</body>
</html>
