<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../login/');
    exit;
}

if (!isset($_SESSION['usuario'])) {
    die("Error: User not logged in.");
}
$usuario = $_SESSION['usuario'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../../favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="styles.css">
    <title>web sin nombre aun | Subir Fotos</title>
    <style>
        .hashtags-container {
            margin-top: 20px;
        }
        .hashtag {
            display: inline-block;
            padding: 5px 10px;
            margin: 5px;
            border: 1px solid #ccc;
            cursor: pointer;
            background-color: #f9f9f9;
        }
        .hashtag.selected {
            background-color: #007BFF;
            color: white;
            border-color: #007BFF;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Subir multimedia</h2>
        <?php
        if (isset($_SESSION['usuario'])) {
            echo "<br><br>Subiendo archivos como: <b>@" . $_SESSION['usuario'] . "</b><br><br>";
        }
        ?>
        <div class="preview-container">
            <form action="upload.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="username" value="<?php echo htmlspecialchars($usuario); ?>">
                <input type="text" name="title" placeholder="Título" required>
                <input type="file" name="file" id="fileInput" required>

                <div class="hashtags-container">
                    <p>Selecciona hashtags:</p>
                    <?php
                    $hashtags = [
                        "Humor", "Animales", "Música", "Videojuegos", "Tecnología",
                        "Cine y series", "Libros y literatura", "Ciencia", "Historia",
                        "Arte y diseño", "Moda y belleza", "Salud y bienestar",
                        "Viajes y turismo", "Autos y motos", "Política y actualidad",
                        "Finanzas y emprendimiento", "Cultura geek", "DIY y manualidades",
                        "Comida y recetas"
                    ];
                    foreach ($hashtags as $index => $hashtag) {
                        $id = "hashtag" . $index; 
                        echo "<input type='checkbox' id='$id' name='hashtags[]' value='$hashtag' style='display: none;'>";
                        echo "<label for='$id' class='hashtag'>$hashtag</label>";
                    }
                    ?>
                </div>

                <input type="submit" value="Subir">
            </form>
            <img id="imagePreview" src="#" alt="El archivo ha de ser PNG, JPG, o GIF" style="display: none;">
        </div>
    
        <center>
        <br><a href="../">Cancelar</a>
    </center>
    </div>

    <script>
        document.getElementById('fileInput').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.getElementById('imagePreview');
                    img.src = e.target.result;
                    img.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });

        document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('.hashtag').forEach(label => {
        const checkbox = document.getElementById(label.getAttribute('for'));

        checkbox.addEventListener('change', function () {
            label.classList.toggle('selected', this.checked);
        });

        if (checkbox.checked) {
            label.classList.add('selected');
        }
    });
});

    </script>
</body>
</html>