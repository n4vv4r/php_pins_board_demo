<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../../login/');
    exit;
}

$hashtag = isset($_GET['hashtag']) ? str_replace('_', ' ', $_GET['hashtag']) : null;

$base_url = rtrim(dirname($_SERVER['PHP_SELF'], 2), '/'); 

echo '
<div class="navbar">
    <center>
        <p><br>Bienvenido  <b>@' . $_SESSION['usuario'] . '</b>  <a href="' . $base_url . '/upload/">Subir</a> <a href="' . dirname($base_url) . '/logout">Cerrar sesión</a> <br>
        <br><form action="" method="POST" style="display:inline;">
            <input type="text" name="search" placeholder="Buscar títulos o usuarios..." value="' . (isset($_POST['search']) ? htmlspecialchars($_POST['search']) : '') . '">
            <input type="submit" value="Buscar">
        </form>';
if (isset($_POST['search']) && !empty(trim($_POST['search']))) {
    echo ' <a href="' . $base_url . '/hashtag/' . urlencode(str_replace(' ', '_', $hashtag)) . '">Borrar búsqueda</a>';
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
echo '<a href="' . $base_url . '/" class="category ' . (!$hashtag ? 'active' : '') . '">Todas</a>';
foreach ($hashtags as $htag) {
    $htag_url = str_replace(' ', '_', $htag);
    $active = ($hashtag === $htag) ? 'active' : '';
    echo '<a href="' . $base_url . '/hashtag/' . urlencode($htag_url) . '" class="category ' . $active . '">' . htmlspecialchars($htag) . '</a>';
}
echo '</div></center>';
echo '</div>';

echo '<center><br>
<img src="' . dirname($base_url) . '/a.png" alt=" logo" width="360px">
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
    <title>web sin nombre aun | <?php echo $hashtag ? htmlspecialchars($hashtag) : 'Dashboard'; ?></title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../stylepins.css">
    <link rel="icon" href="../favicon.ico" type="image/x-icon">
</head>
<body>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script type="module" src="../main.js"></script>

<div class="container">
    <?php
    require_once('../../private/config/login/db_config.php');

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
    if ($hashtag) {
        $sql .= " WHERE posts.hashtags LIKE ?";
    }
    if (isset($_POST['search']) && !empty(trim($_POST['search']))) {
        $searchTerm = '%' . trim($_POST['search']) . '%';
        if ($hashtag) {
            $sql .= " AND (posts.title LIKE ? OR users.username LIKE ?)";
        } else {
            $sql .= " WHERE posts.title LIKE ? OR users.username LIKE ?";
        }
    }
    $sql .= " ORDER BY posts.upload_date DESC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparando la consulta: " . $conn->error);
    }

    if ($hashtag && isset($_POST['search']) && !empty(trim($_POST['search']))) {
        $hashtagTerm = '%' . $hashtag . '%';
        $stmt->bind_param("sss", $hashtagTerm, $searchTerm, $searchTerm);
    } elseif ($hashtag) {
        $hashtagTerm = '%' . $hashtag . '%';
        $stmt->bind_param("s", $hashtagTerm);
    } elseif (isset($_POST['search']) && !empty(trim($_POST['search']))) {
        $stmt->bind_param("ss", $searchTerm, $searchTerm);
    }

    if ($hashtag || (isset($_POST['search']) && !empty(trim($_POST['search'])))) {
            $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($sql);
    }

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $imagePath = '../uploads/' . basename($row["image_path"]);
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
            echo '        <a href="' . dirname($base_url) . '/pin/' . $row["id"] . '">🔗</a>';
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
        echo "No hay publicaciones en esta categoría... ¿Qué tal si subes algo?";
    }

    $stmt->close();
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
            url: '../like.php',
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

</body>
</html>