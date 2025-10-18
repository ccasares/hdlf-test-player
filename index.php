<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servidor de Videos Protegidos</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 800px;
            width: 100%;
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 16px;
        }
        
        .content {
            padding: 40px;
        }
        
        .section {
            margin-bottom: 30px;
        }
        
        .section h2 {
            color: #333;
            font-size: 24px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        
        .section h2::before {
            content: "→";
            color: #667eea;
            margin-right: 10px;
            font-weight: bold;
        }
        
        .feature-list {
            list-style: none;
            margin-left: 35px;
        }
        
        .feature-list li {
            padding: 10px 0;
            color: #555;
            line-height: 1.6;
        }
        
        .feature-list li::before {
            content: "✓";
            color: #10b981;
            font-weight: bold;
            margin-right: 10px;
        }
        
        .code-block {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            overflow-x: auto;
            margin: 15px 0;
        }
        
        .code-block code {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            color: #e83e8c;
        }
        
        .endpoints {
            display: grid;
            gap: 15px;
            margin-top: 15px;
        }
        
        .endpoint {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 4px;
        }
        
        .endpoint .method {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            margin-right: 10px;
        }
        
        .endpoint .path {
            font-family: 'Courier New', monospace;
            color: #333;
            font-weight: 600;
        }
        
        .endpoint .description {
            color: #666;
            margin-top: 8px;
            font-size: 14px;
        }
        
        .demo-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.2s;
            margin-top: 20px;
        }
        
        .demo-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        
        .alert {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        
        .alert strong {
            color: #856404;
        }
        
        .footer {
            background: #f8f9fa;
            padding: 20px 40px;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔒 Servidor de Videos Protegidos</h1>
            <p>Sistema de streaming seguro con tokens temporales y validación HMAC-SHA256</p>
        </div>
        
        <div class="content">
            <div class="section">
                <h2>Características</h2>
                <ul class="feature-list">
                    <li><strong>Tokens Temporales</strong>: URLs con expiración automática</li>
                    <li><strong>Validación HMAC-SHA256</strong>: Seguridad criptográfica</li>
                    <li><strong>Protección por Referer</strong>: Solo dominios autorizados</li>
                    <li><strong>Streaming Parcial</strong>: Soporte para Range requests (HTTP 206)</li>
                    <li><strong>Sin Acceso Directo</strong>: Los videos no son accesibles sin token válido</li>
                </ul>
            </div>
            
            <div class="section">
                <h2>Reproductores Disponibles</h2>
                <div class="endpoints">
                    <div class="endpoint">
                        <span class="method">GET</span>
                        <span class="path">/access.php</span>
                        <div class="description">
                            🔒 <strong>NUEVO:</strong> Reproductor Ultra-Seguro con tokens de sesión. 
                            La URL del video NUNCA se expone en el navegador.
                        </div>
                    </div>
                    
                    <div class="endpoint">
                        <span class="method">GET</span>
                        <span class="path">/demo.html</span>
                        <div class="description">
                            Reproductor con URLs firmadas temporalmente (sistema anterior).
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="section">
                <h2>Endpoints de API</h2>
                <div class="endpoints">
                    <div class="endpoint">
                        <span class="method">POST</span>
                        <span class="path">/api/session.php?action=create</span>
                        <div class="description">
                            Crea un token de sesión temporal para el reproductor seguro.
                        </div>
                    </div>
                    
                    <div class="endpoint">
                        <span class="method">GET</span>
                        <span class="path">/api/stream.php?token=...</span>
                        <div class="description">
                            API proxy para streaming de video con validación de token.
                        </div>
                    </div>
                    
                    <div class="endpoint">
                        <span class="method">GET</span>
                        <span class="path">/video.php?file=video.mp4&token=...&expires=...</span>
                        <div class="description">
                            Reproduce un video protegido con URL firmada (sistema legacy).
                        </div>
                    </div>
                    
                    <div class="endpoint">
                        <span class="method">POST</span>
                        <span class="path">/generate_url.php</span>
                        <div class="description">
                            Genera URLs firmadas para videos. Enviar JSON: <code>{"file": "video.mp4", "expires_in": 60}</code>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="section">
                <h2>Configuración</h2>
                <div class="alert">
                    <strong>⚠️ Importante:</strong> Antes de usar en producción, configura las siguientes variables de entorno:
                </div>
                <div class="code-block">
                    <code>
SECRET_KEY=tu-clave-secreta-aqui<br>
ALLOWED_DOMAIN=https://tudominio.com<br>
VIDEO_PATH=/volume1/web/videos/<br>
BASE_URL=https://tu-app.herokuapp.com
                    </code>
                </div>
            </div>
            
            <div class="section">
                <h2>Uso desde CLI</h2>
                <div class="code-block">
                    <code>
# Generar URL firmada<br>
php generate_url.php video1.mp4 60<br>
<br>
# Resultado:<br>
# http://localhost/video.php?file=video1.mp4&token=abc123...&expires=1234567890
                    </code>
                </div>
            </div>
            
            <div style="text-align: center; display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                <a href="access.php" class="demo-button" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    🔒 Reproductor Ultra-Seguro (NUEVO)
                </a>
                <a href="demo.html" class="demo-button">
                    🎬 Demo Clásico
                </a>
            </div>
        </div>
        
        <div class="footer">
            Servidor de Videos Protegidos v1.0 | Compatible con Synology NAS y Heroku
        </div>
    </div>
</body>
</html>

