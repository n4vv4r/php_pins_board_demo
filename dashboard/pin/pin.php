<?php
session_start();
require_once('../../private/config/login/db_config.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Que estas buscando?");
}

$post_id = intval($_GET['id']);

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Error de conexión.");
}

$stmt = $conn->prepare("SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id WHERE posts.id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();
$stmt->close();

if (!$post) {
    die("Que estas buscando?");
}

$baseURL = "/dashboard/";
$imagePath = str_replace('dashboard/uploads/', 'uploads/', $post["image_path"]);
echo '    <link rel="stylesheet" href="styles.css">';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post["title"]); ?> | web sin nombre aun</title>
    <link rel="stylesheet" href="../dashboard/pin/styles.css">
    <link rel="icon" href="../favicon.ico" type="image/x-icon">
</head>
<body>

    <div class="container">
        <div class="image-section">
            <h2><a href="../">Volver atrás</a><br>   <?php echo htmlspecialchars($post["title"]); ?></h2>
            <p>Subido por <b>@<?php echo htmlspecialchars($post["username"]); ?></b> el <?php echo htmlspecialchars($post["upload_date"]); ?></p>
            <img src="<?php echo $baseURL; ?>../EL_NOMBRE_DE_TU_CARPETA_ROOT/dashboard/uploads/<?php echo htmlspecialchars(basename($imagePath)); ?>" alt="Imagen">
            <br>
            <a href="<?php echo $baseURL; ?>../EL_NOMBRE_DE_TU_CARPETA_ROOT/dashboard/uploads/<?php echo htmlspecialchars(basename($imagePath)); ?>" download>Descargar imagen</a>
            <br><br>
        </div>

        <div class="comments-section">
            <h3>Comentarios</h3>

            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                <form action="../dashboard/add_comment.php" method="POST">
                    <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                    <textarea name="comment" placeholder="Escribe un comentario..." required></textarea>
                    <button type="submit">Comentar</button>
                </form>
            <?php else: ?>
                <p>Debes <a href="../login/">iniciar sesión</a> para comentar.</p>
            <?php endif; ?>

            <div class="comments">
                <?php
                $stmt = $conn->prepare("SELECT comments.*, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE comments.post_id = ? ORDER BY comments.created_at DESC");
                $stmt->bind_param("i", $post_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while ($comment = $result->fetch_assoc()) {
                        echo '<div class="comment">';
                        echo '<b>@' . htmlspecialchars($comment["username"]) . '</b>: ' . htmlspecialchars($comment["comment"]);
                        echo '<br><small>' . htmlspecialchars($comment["created_at"]) . '</small>';
                        echo '</div>';
                    }
                } else {
                    echo "<p>No hay comentarios aún. Sé el primero en comentar!</p>";
                }
                $stmt->close();
                ?>
            </div>
        </div>
    </div>

</body>
</html>