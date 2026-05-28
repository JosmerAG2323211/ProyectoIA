<?php
// ajax_chat.php
header('Content-Type: application/json');

// Desactivar reporte de errores en pantalla para que no rompan el JSON de salida, pero registrarlos
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once 'config/conexion.php';

// 1. Capturar entrada JSON del JavaScript
$input = json_decode(file_get_contents('php://input'), true);
$mensaje_usuario = isset($input['mensaje']) ? trim($input['mensaje']) : '';

if (empty($mensaje_usuario)) {
    echo json_encode(["respuesta" => "Por favor, ingresa un mensaje válido."]);
    exit();
}

// 2. Intentar buscar el manual técnico del último equipo registrado
$ruta_manual = "";
try {
    $stmt = $pdo->query("SELECT archivo_manual FROM equipos ORDER BY id DESC LIMIT 1");
    $ultimo_equipo = $stmt->fetch();
    if ($ultimo_equipo && !empty($ultimo_equipo['archivo_manual'])) {
        $ruta_manual = __DIR__ . DIRECTORY_SEPARATOR . "manuales" . DIRECTORY_SEPARATOR . $ultimo_equipo['archivo_manual'];
    }
} catch (Exception $e) {
    // Si la tabla está vacía o falla, continuamos sin manual (Gemini usará conocimiento general)
    $ruta_manual = "";
}

// 3. Configurar scripts y argumentos de forma ultra segura
$script_python = __DIR__ . DIRECTORY_SEPARATOR . "scripts_ia" . DIRECTORY_SEPARATOR . "procesar_rag.py";
$arg_mensaje = escapeshellarg($mensaje_usuario);
$arg_manual = escapeshellarg($ruta_manual);

// 4. Ejecución adaptada según el Sistema Operativo (Evita bloqueos en XAMPP de Windows)
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    // 'cmd /c python' obliga a Apache/XAMPP a heredar las variables de entorno correctas de Windows
    $comando = "cmd /c python " . escapeshellarg($script_python) . " $arg_mensaje $arg_manual 2>&1";
} else {
    $comando = "python3 " . escapeshellarg($script_python) . " $arg_mensaje $arg_manual 2>&1";
}

// Ejecución del comando en la terminal
$salida_ia = shell_exec($comando);

// Mensaje de contingencia por defecto por si todo lo demás falla
$respuesta_chat = "🤖 Modo de asistencia local activo: El motor RAG no arrojó un flujo JSON legible. Intenta realizar inspecciones manuales en los sistemas.";

// 5. Intentar parsear la salida de Python de forma ultra tolerante
if ($salida_ia) {
    $inicio_json = strpos($salida_ia, '{');
    $fin_json = strrpos($salida_ia, '}');
    
    if ($inicio_json !== false && $fin_json !== false) {
        $json_puro = substr($salida_ia, $inicio_json, ($fin_json - $inicio_json) + 1);
        
        // Limpiamos caracteres de control ASCII (0-31) pero preservamos saltos de línea comunes (\n y \r)
        // para que json_decode no se rompa con los formatos de texto largos de Gemini
        $json_puro = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $json_puro);
        
        $datos_ia = json_decode($json_puro, true);
        
        if (json_last_error() === JSON_ERROR_NONE && isset($datos_ia['diagnostico'])) {
            // --- MEJORA: FORMATO VISUAL CON COLOR DINÁMICO SEGÚN PRIORIDAD ---
            $prioridad = isset($datos_ia['prioridad']) ? trim($datos_ia['prioridad']) : 'Rutinaria';
            
            // Asignación de colores neón adaptados al estilo del dashboard
            $color_tag = '#00ffcc'; // Turquesa / Cyan para Rutinaria
            if ($prioridad === 'Urgente') {
                $color_tag = '#ffaa00'; // Naranja
            } elseif ($prioridad === 'Crítica' || $prioridad === 'Critica') {
                $color_tag = '#ff0055'; // Rojo neón para fallas críticas
            }
            
            // Reemplazamos los saltos de línea de texto (\n) por etiquetas <br> de HTML para que se vea bien ordenado en el chat
            $diagnostico_html = nl2br(htmlspecialchars($datos_ia['diagnostico']));
            
            // Estructura final elegante que se pintará en el chat del asistente
            $respuesta_chat = "<b style='color: " . $color_tag . "; text-transform: uppercase;'>[Prioridad IA: " . $prioridad . "]</b><br><br>" . $diagnostico_html;
        } else {
            // Si Python mandó texto plano o un error de sintaxis crudo, se le muestra de respaldo al desarrollador
            $respuesta_chat = "🤖 Diagnóstico Directo (Sin Formato JSON):\n" . trim(strip_tags($salida_ia));
        }
    } else {
        // Si no hay llaves de JSON, mostramos la respuesta plana de la terminal directamente
        $respuesta_chat = "🤖 Servidor responde:<br>" . nl2br(htmlspecialchars(trim($salida_ia)));
    }
}

// 6. Enviar respuesta final garantizada al JavaScript en formato JSON
echo json_encode(["respuesta" => $respuesta_chat]);
exit();
?>