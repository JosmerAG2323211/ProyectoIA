<?php
// procesar_reporte.php
require_once 'config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $equipo_id = isset($_POST['equipo_id']) ? intval($_POST['equipo_id']) : 0;
    $descripcion_falla = isset($_POST['descripcion_falla']) ? trim($_POST['descripcion_falla']) : '';
    $usuario_id = 1; // ID por defecto del técnico Wilber Ruiz asignado en base de datos
    $foto_nombre = null;

    if ($equipo_id === 0 || empty($descripcion_falla)) {
        die("Error: Datos del reporte incompletos.");
    }

    // 1. Obtener la ruta del manual digital del equipo seleccionado
    $stmt = $pdo->prepare("SELECT archivo_manual FROM equipos WHERE id = ?");
    $stmt->execute([$equipo_id]);
    $equipo = $stmt->fetch();

    if (!$equipo) {
        die("Error: El equipo seleccionado no existe en el inventario.");
    }

    // Procesar la carga de la imagen de evidencia si se adjuntó una
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['foto']['tmp_name'];
        $fileName = $_FILES['foto']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (in_array($fileExtension, ['jpg', 'jpeg', 'png'])) {
            $foto_nombre = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
            // Asegúrate de tener la carpeta 'uploads' creada en la raíz
            if (!is_dir('./uploads')) {
                mkdir('./uploads', 0777, true);
            }
            move_uploaded_file($fileTmpPath, './uploads/' . $foto_nombre);
        }
    }

    // 2. Configurar rutas absolutas para invocar el script de Python de forma unificada
    $script_python = __DIR__ . DIRECTORY_SEPARATOR . "scripts_ia" . DIRECTORY_SEPARATOR . "procesar_rag.py";
    $ruta_manual = __DIR__ . DIRECTORY_SEPARATOR . "manuales" . DIRECTORY_SEPARATOR . $equipo['archivo_manual'];

    $arg_falla = escapeshellarg($descripcion_falla);
    $arg_manual = escapeshellarg($ruta_manual);

    // 3.--- DETECCIÓN DE PYTHON CON RUTA ABSOLUTA PARA WINDOWS XAMPP ---
    $python_bin = "python";
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    // Obtenemos el nombre de usuario de tu Windows de forma automática
    $user_windows = getenv('USERNAME') ? getenv('USERNAME') : 'Default';
    
    // Ruta estándar de instalación de Python de 64 bits en Windows
    $ruta_estandar_python = "C:\\Users\\" . $user_windows . "\\AppData\\Local\\Programs\\Python\\Python311\\python.exe";
    
    // Si la versión instalada es ligeramente diferente, usamos la ruta por defecto de la Microsoft Store
    $ruta_store_python = "C:\\Users\\" . $user_windows . "\\AppData\\Local\\Microsoft\\WindowsApps\\python.exe";
    
    if (file_exists($ruta_estandar_python)) {
        $python_bin = '"' . $ruta_estandar_python . '"';
    } elseif (file_exists($ruta_store_python)) {
        $python_bin = '"' . $ruta_store_python . '"';
    } else {
        // Si no se encuentra en las rutas comunes, dejamos 'python' para que intente usar el entorno global
        $python_bin = "python";
    }
}


    // Invocamos el comando y capturamos la salida consolidada
    $comando = "$python_bin " . escapeshellarg($script_python) . " $arg_falla $arg_manual 2>&1";
    $salida_ia = shell_exec($comando);

    // 4. Valores de contingencia en caso de que falle la API o el entorno local
    $prioridad = "Rutinaria";
    $diagnostico = "La IA no pudo procesar una respuesta debido a una interrupción en el entorno de ejecución.";

    if ($salida_ia) {
        // Extraemos y parseamos el JSON estructurado devuelto por Python
        $inicio_json = strpos($salida_ia, '{');
        $fin_json = strrpos($salida_ia, '}');
        
        if ($inicio_json !== false && $fin_json !== false) {
            $json_puro = substr($salida_ia, $inicio_json, ($fin_json - $inicio_json) + 1);
            $datos_ia = json_decode($json_puro, true);
            
            if (isset($datos_ia['prioridad']) && isset($datos_ia['diagnostico'])) {
                $prioridad = $datos_ia['prioridad'];
                $diagnostico = $datos_ia['diagnostico'];
            }
        }
    }

    // 5. Insertar el reporte de la falla con los datos analizados por la IA en MySQL
    $sql = "INSERT INTO reportes_fallas (equipo_id, usuario_id, descripcion_falla, foto_evidencia, diagnostico_ia, prioridad, estado) 
            VALUES (?, ?, ?, ?, ?, ?, 'Abierto')";
    $stmt_insert = $pdo->prepare($sql);
    
    if ($stmt_insert->execute([$equipo_id, $usuario_id, $descripcion_falla, $foto_nombre, $diagnostico, $prioridad])) {
        // Redireccionar al panel principal con estado exitoso
        header("Location: index.php?status=success");
        exit();
    } else {
        die("Error crítico: No se pudo actualizar el registro en la base de datos.");
    }
} else {
    header("Location: index.php");
    exit();
}
