<?php
/**
 * Generador de URLs Firmadas para Videos Protegidos
 * 
 * Este script genera URLs temporales con tokens HMAC para acceder a videos protegidos
 */

// Cargar variables de entorno
function loadEnv($path) {
    if (!file_exists($path)) {
        return false;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        if (!array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
    return true;
}

// Cargar configuración
loadEnv(__DIR__ . '/.env');

// Configuración
$SECRET_KEY = getenv('SECRET_KEY') ?: 'change-this-secret-key-in-production';
$BASE_URL = getenv('BASE_URL') ?: 'http://localhost';

/**
 * Generar URL firmada para un video
 * 
 * @param string $filename Nombre del archivo (ej: "video1.mp4")
 * @param int $expirationMinutes Minutos hasta que expire el token (default: 60)
 * @return string URL completa con token
 */
function generateSecureUrl($filename, $expirationMinutes = 60) {
    global $SECRET_KEY, $BASE_URL;
    
    // Calcular timestamp de expiración
    $expires = time() + ($expirationMinutes * 60);
    
    // Generar token HMAC
    $token = hash_hmac('sha256', $filename . $expires, $SECRET_KEY);
    
    // Construir URL
    $url = rtrim($BASE_URL, '/') . '/video.php?' . http_build_query([
        'file' => $filename,
        'token' => $token,
        'expires' => $expires
    ]);
    
    return $url;
}

/**
 * Generar múltiples URLs firmadas
 */
function generateMultipleUrls($filenames, $expirationMinutes = 60) {
    $urls = [];
    foreach ($filenames as $filename) {
        $urls[$filename] = [
            'url' => generateSecureUrl($filename, $expirationMinutes),
            'expires_at' => date('Y-m-d H:i:s', time() + ($expirationMinutes * 60)),
            'valid_for' => $expirationMinutes . ' minutes'
        ];
    }
    return $urls;
}

// ========================================
// MODO CLI O API
// ========================================

// Detectar si se está ejecutando desde CLI
$isCLI = (php_sapi_name() === 'cli');

if ($isCLI) {
    // ========================================
    // MODO CLI
    // ========================================
    echo "===========================================\n";
    echo "  Generador de URLs Firmadas para Videos\n";
    echo "===========================================\n\n";
    
    // Obtener nombre del archivo
    if (isset($argv[1])) {
        $filename = $argv[1];
    } else {
        echo "Ingrese el nombre del archivo de video: ";
        $filename = trim(fgets(STDIN));
    }
    
    // Obtener minutos de expiración
    if (isset($argv[2])) {
        $expirationMinutes = intval($argv[2]);
    } else {
        echo "Minutos de validez (default: 60): ";
        $input = trim(fgets(STDIN));
        $expirationMinutes = empty($input) ? 60 : intval($input);
    }
    
    // Generar URL
    $url = generateSecureUrl($filename, $expirationMinutes);
    $expiresAt = date('Y-m-d H:i:s', time() + ($expirationMinutes * 60));
    
    echo "\n-------------------------------------------\n";
    echo "✓ URL generada correctamente\n";
    echo "-------------------------------------------\n";
    echo "Archivo: $filename\n";
    echo "Válida hasta: $expiresAt\n";
    echo "URL:\n$url\n\n";
    
} else {
    // ========================================
    // MODO WEB/API
    // ========================================
    header('Content-Type: application/json');
    
    // Método GET: Mostrar documentación
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        echo json_encode([
            'service' => 'Generador de URLs Firmadas',
            'version' => '1.0',
            'usage' => [
                'method' => 'POST',
                'content_type' => 'application/json',
                'body' => [
                    'file' => 'Nombre del archivo (ej: video1.mp4) o array de archivos',
                    'expires_in' => 'Minutos de validez (opcional, default: 60)'
                ],
                'examples' => [
                    'single_file' => [
                        'file' => 'video1.mp4',
                        'expires_in' => 60
                    ],
                    'multiple_files' => [
                        'files' => ['video1.mp4', 'video2.mp4', 'video3.mp4'],
                        'expires_in' => 120
                    ]
                ]
            ]
        ], JSON_PRETTY_PRINT);
        exit;
    }
    
    // Método POST: Generar URL(s)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode([
                'error' => true,
                'message' => 'Invalid JSON input'
            ]);
            exit;
        }
        
        $expirationMinutes = isset($input['expires_in']) ? intval($input['expires_in']) : 60;
        
        // Generar URL para un solo archivo
        if (isset($input['file'])) {
            $filename = $input['file'];
            $url = generateSecureUrl($filename, $expirationMinutes);
            
            echo json_encode([
                'success' => true,
                'file' => $filename,
                'url' => $url,
                'expires_at' => date('Y-m-d H:i:s', time() + ($expirationMinutes * 60)),
                'valid_for' => $expirationMinutes . ' minutes'
            ], JSON_PRETTY_PRINT);
            exit;
        }
        
        // Generar URLs para múltiples archivos
        if (isset($input['files']) && is_array($input['files'])) {
            $urls = generateMultipleUrls($input['files'], $expirationMinutes);
            
            echo json_encode([
                'success' => true,
                'count' => count($urls),
                'videos' => $urls
            ], JSON_PRETTY_PRINT);
            exit;
        }
        
        // Error: falta el parámetro file o files
        http_response_code(400);
        echo json_encode([
            'error' => true,
            'message' => 'Missing required parameter: file or files'
        ]);
        exit;
    }
    
    // Método no permitido
    http_response_code(405);
    echo json_encode([
        'error' => true,
        'message' => 'Method not allowed. Use GET for documentation or POST to generate URLs'
    ]);
}

