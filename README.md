<center>
    <h1>PHPins (DEMO)</h1>
    <p><img src="https://img.shields.io/badge/php-%23777BB4.svg?style=for-the-badge&logo=php&logoColor=white"> <img src="https://img.shields.io/badge/css3-%231572B6.svg?style=for-the-badge&logo=css3&logoColor=white"> <img src="https://img.shields.io/badge/html5-%23E34F26.svg?style=for-the-badge&logo=html5&logoColor=white"> <img src="https://img.shields.io/badge/mysql-4479A1.svg?style=for-the-badge&logo=mysql&logoColor=white"></p>
</center>
Es un proyecto en PHP puro y duro, sin ningún framework. Esto fue un proyecto que hice cuando me aburría. Quise recrear un Pinterest con una temática más oscura, (como X). Podrías llamarle un "clon de pinterest".

## Configuración SQL
en `/private/config/login/db_config.php` encontrarás:

```php
<?php
$servername = "";
$username = "";
$password = "";
$dbname = "";
$port = 3306;
```

Aquí has de poner el servername, el username, nombre de la database y password que configurarás en tu database por defecto.
Este proyecto utiliza MySQL.

## Configuración redirecciones en PHP
En `dashboard/mod/index.php`:
```php
    echo "El post ha sido eliminado con éxito.";
    header("Location: /EL_NOMBRE_DE_TU_CARPETA_ROOT/dashboard/");
    exit;
} else {
    die("No se ha especificado un ID de publicación válido.");
}
```

En `dashboard/pin/pin.php`:
```
<img src="<?php echo $baseURL; ?>../EL_NOMBRE_DE_TU_CARPETA_ROOT/dashboard/uploads/<?php echo htmlspecialchars(basename($imagePath)); ?>" alt="Imagen">
            <br>
            <a href="<?php echo $baseURL; ?>../EL_NOMBRE_DE_TU_CARPETA_ROOT/dashboard/uploads/<?php echo htmlspecialchars(basename($imagePath)); ?>" download>Descargar imagen</a>
```

Son las únicas líneas que han de ser modificadas. El nombre de tu carpeta ROOT ha de ser el nombre que le pones.
## Licencia

[AGPL-3.0](https://www.gnu.org/licenses/agpl-3.0.en.html)
![Licencia](https://www.gnu.org/graphics/agplv3-155x51.png)

