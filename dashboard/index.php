<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../login/');
    exit;
}

echo '
<div class="navbar">
    <center>
        <p><br>Bienvenido  <b>@' . $_SESSION['usuario'] . '</b>  <a href="../dashboard/upload/">Subir</a> <a href="../logout">Cerrar sesión</a> <br>
        <br><form action="" method="POST" style="display:inline;">
            <input type="text" name="search" placeholder="Buscar títulos o usuarios..." value="' . (isset($_POST['search']) ? htmlspecialchars($_POST['search']) : '') . '">
            <input type="submit" value="Buscar">
        </form>';
if (isset($_POST['search']) && !empty(trim($_POST['search']))) {
    echo ' <a href="../dashboard/">Borrar búsqueda</a>';
}
echo '
        </p>
    </center>
</div>
';

$hashtags = [
    "Humor", "Animales", "Música", "Videojuegos", "Tecnología",
    "Cine y series", "Libros y literatura", "Ciencia", "Historia",
    "Arte y diseño", "Moda y belleza", "Salud y bienestar",
    "Viajes y turismo", "Autos y motos", "Política y actualidad",
    "Finanzas y emprendimiento", "Cultura geek", "DIY y manualidades",
    "Comida y recetas"
];
echo '<div class="category-navbar">';
echo '<center><div class="categories">';
echo '<a href="/dashboard/" class="category active">Todas</a>';
foreach ($hashtags as $hashtag) {
    $hashtag_url = str_replace(' ', '_', $hashtag);
    echo '<a href="../dashboard/hashtag/' . urlencode($hashtag_url) . '" class="category">' . htmlspecialchars($hashtag) . '</a>';
}
echo '</div></center>';
echo '</div>';

echo '<center><br>
<img src="../a.png" alt=" logo" width="360px">
<div class="anuncio">
    <p>En mantenimiento</p>
</div>
</center>';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>web sin nombre aun | Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="stylepins.css">
    <link rel="icon" href="../favicon.ico" type="image/x-icon">
</head>
<body>

<style>
@font-face {
    font-family: 'Coolvetica';
    src: url('fonts/Coolvetica Rg.otf') format('opentype');
    font-weight: normal;
    font-style: normal;
}
body, html, div, p {
    font-family: 'Coolvetica', sans-serif;
}
.container {
    font-family: 'Coolvetica', sans-serif;
}
</style>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script type="module" src="main.js"></script>

<div class="container">
    <?php
    $username = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : 'usuario desconocido';

    require_once('../private/config/login/db_config.php');

    $conn = new mysqli($servername, $username, $password, $dbname, $port);

    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    $userStmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $userStmt->bind_param("s", $_SESSION['usuario']);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    $current_user = $userResult->fetch_assoc();
    $current_user_id = $current_user['id'];
    $userStmt->close();

    $sql = "SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id";
    if (isset($_POST['search']) && !empty(trim($_POST['search']))) {
        $sql .= " WHERE posts.title LIKE ? OR users.username LIKE ?";
        $searchStmt = $conn->prepare($sql);
        if ($searchStmt) {
            $searchTerm = '%' . trim($_POST['search']) . '%';
            $searchStmt->bind_param("ss", $searchTerm, $searchTerm);
            $searchStmt->execute();
            $result = $searchStmt->get_result();
        } else {
            die("Error preparando la consulta de búsqueda");
        }
    } else {
        $sql .= " ORDER BY posts.upload_date DESC";
        $result = $conn->query($sql);
    }

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $imagePath = str_replace('../uploads/', 'uploads/', $row["image_path"]);

            $likeStmt = $conn->prepare("SELECT COUNT(*) FROM likes WHERE user_id = ? AND post_id = ?");
            $likeStmt->bind_param("ii", $current_user_id, $row["id"]);
            $likeStmt->execute();
            $likeResult = $likeStmt->get_result();
            $hasLiked = $likeResult->fetch_row()[0] > 0;
            $likeStmt->close();

            $countStmt = $conn->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ?");
            $countStmt->bind_param("i", $row["id"]);
            $countStmt->execute();
            $countResult = $countStmt->get_result();
            $likeCount = $countResult->fetch_row()[0];
            $countStmt->close();

            echo '<div class="pin">';
            echo '    <img src="' . htmlspecialchars($imagePath) . '" alt="Imagen">';
            echo '    <div class="pin-overlay">';
            echo '        <a href="../pin/' . $row["id"] . '">🔗</a>';
            echo '    </div>';
            echo '    <div class="pin-description">' . htmlspecialchars($row["title"]) . '</div>';
            echo '    <div class="pin-user"><b>@' . htmlspecialchars($row["username"]) . '</b> <br> ' . htmlspecialchars($row["upload_date"]) . '</div>';
            echo '    <div class="pin-buttons">';
            echo '        <span class="like-btn ' . ($hasLiked ? 'liked' : '') . '" data-post-id="' . $row["id"] . '">♥</span>';
            echo '        <span class="like-count">' . $likeCount . ' likes</span><br>';
            echo '    </div>';
            echo '</div>';
        }
    } else {
        echo "Esto se ve muy vacío... ¿Qué tal si subes algo, eh?";
    }

    if (isset($searchStmt) && $searchStmt instanceof mysqli_stmt) {
        $searchStmt->close();
    }
    $conn->close();
    ?>
</div>

<script>
$(document).ready(function() {
    $('.like-btn').click(function() {
        var $btn = $(this);
        var postId = $btn.data('post-id');
        var $count = $btn.siblings('.like-count');

        $.ajax({
            url: 'like.php',
            method: 'POST',
            data: { post_id: postId },
            dataType: 'json',
            success: function(response) {
                if (response.error) {
                    console.log("Error:", response.error);
                    return;
                }
                $btn.addClass('pop');
                setTimeout(function() {
                    $btn.removeClass('pop');
                }, 300);
                if (response.status === 'liked') {
                    $btn.addClass('liked');
                } else if (response.status === 'unliked') {
                    $btn.removeClass('liked');
                }
                $count.text(response.likeCount + ' likes');
            },
            error: function(xhr, status, error) {
                console.log('Error en AJAX:', status, error);
            }
        });
    });
});
</script>

<center></center><br><br><br>
</body>
</html>