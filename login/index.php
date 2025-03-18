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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = filter_input(INPUT_POST, 'username', FILTER_VALIDATE_REGEXP, [
        "options" => ["regexp" => "/^[a-zA-Z0-9_]+$/"]
    ]);
    
    $pass = $_POST['password'];

    if ($user === false) {
        $error = "nombre de usuario inválido.";
    } else {
        $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $user);

        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();

        if ($stmt->num_rows > 0) {
            if (password_verify($pass, $hashed_password)) {
                $_SESSION['login_user'] = $user;
                $_SESSION['usuario'] = $user;
                $_SESSION['loggedin'] = true;
                session_regenerate_id(true);
                $_SESSION['user_id'] = $id;  
                header("location: ../dashboard/");
                exit();
            } else {
                $error = "Credenciales inválidas.";
            }
        } else {
            $error = "Credenciales inválidas.";
        }

        $stmt->close();
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>web sin nombre aun | Login</title>
    <link rel="icon" href="../favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <div class="left">
        <img src="../FOTO.png" alt="imagenn">
    </div>
    <div class="right">
        <h2>Iniciar Sesión</h2>
        <form action="" method="post">
            <?php if (!empty($error)): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <label for="username">Usuario</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Ingresar</button>
        </form>

        <p>¿No tienes cuenta? <a href="../register/">Regístrate</a></p>
        <p><a href="../">Volver a inicio</a></p>
    </div>
</div>

</body>
</html>