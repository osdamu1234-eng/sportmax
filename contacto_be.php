<?php
include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Captura de variables con fallback para evitar errores si cambia el nombre del input
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : (isset($_POST['correo']) ? trim($_POST['correo']) : '');
    $tipo_mensaje = isset($_POST['tipo_mensaje']) ? trim($_POST['tipo_mensaje']) : 'General';
    $mensaje = isset($_POST['mensaje']) ? trim($_POST['mensaje']) : '';

    if (!empty($nombre) && !empty($email) && !empty($mensaje)) {
        // Sentencia preparada en PDO para registrar en la tabla 'contacto'
        $stmt = $conexion->prepare("INSERT INTO contacto (nombre, email, tipo_mensaje, mensaje) VALUES (?, ?, ?, ?)");

        // En PDO los parámetros se pasan dentro del método execute() como un array
        if ($stmt->execute([$nombre, $email, $tipo_mensaje, $mensaje])) {
            echo '<script>
                    alert("¡Gracias por contactar a Sport Max! Tu mensaje ha sido enviado correctamente.");
                    window.location.href = "index.php#contacto";
                  </script>';
        } else {
            echo '<script>
                    alert("Ocurrió un error al enviar el mensaje. Inténtalo más tarde.");
                    window.location.href = "index.php#contacto";
                  </script>';
        }

        // En PDO no se requiere $stmt->close(); se destruye la variable automáticamente al finalizar el script
    } else {
        echo '<script>
                alert("Por favor completa todos los campos obligatorios del formulario.");
                window.location.href = "index.php#contacto";
              </script>';
    }

    // En PDO para cerrar la conexión basta con asignar null a la variable
    $conexion = null;
} else {
    header("Location: index.php");
    exit();
}
?>