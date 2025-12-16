<?php
/**
 * =========================================================================
 * ECOMARKET - DIAGNÓSTICO DEL PROYECTO
 * =========================================================================
 * 
 * Este archivo verifica automáticamente la configuración del proyecto
 * para la evaluación de Mini-Retos 1-20.
 * 
 * INSTRUCCIONES DE INSTALACIÓN:
 * 1. Copiar este archivo a la carpeta PUBLIC de Laravel: public/diagnostico.php
 * 2. Acceder desde el navegador: http://localhost:8000/diagnostico.php
 * 3. Verificar que todos los checkmarks estén en verde ✅
 * 
 * IMPORTANTE: Este archivo NO debe estar en producción.
 * Solo para evaluación académica.
 * 
 * @version 1.0
 * @author Sistema de Evaluación EcoMarket
 */

// Prevenir acceso en producción
if (getenv('APP_ENV') === 'production') {
    die('Este archivo solo está disponible en entornos de desarrollo.');
}

// Configurar errores para desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Intentar cargar autoload de Composer y Laravel
$basePath = dirname(__DIR__);
$autoloadPath = $basePath . '/vendor/autoload.php';

if (!file_exists($autoloadPath)) {
    die('❌ Error: No se encontró vendor/autoload.php. Ejecuta: composer install');
}

require $autoloadPath;

// Cargar aplicación Laravel
$app = require_once $basePath . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Crear request falso
$request = Illuminate\Http\Request::capture();
$kernel->handle($request);

// Ahora tenemos acceso a Laravel
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

// Variables para almacenar resultados
$checks = [];
$totalChecks = 0;
$passedChecks = 0;

// Función helper para verificar
function checkItem($name, $condition, $successMsg, $failMsg, $hint = '') {
    global $checks, $totalChecks, $passedChecks;
    
    $totalChecks++;
    $passed = $condition;
    
    if ($passed) {
        $passedChecks++;
    }
    
    $checks[] = [
        'name' => $name,
        'passed' => $passed,
        'message' => $passed ? $successMsg : $failMsg,
        'hint' => $hint
    ];
    
    return $passed;
}

// =========================================================================
// VERIFICACIONES
// =========================================================================

// 1. Versión de PHP
checkItem(
    'PHP Version',
    version_compare(PHP_VERSION, '8.1.0', '>='),
    '✅ PHP ' . PHP_VERSION . ' (Compatible)',
    '❌ PHP ' . PHP_VERSION . ' (Se requiere PHP 8.1+)',
    'Actualiza PHP a versión 8.1 o superior'
);

// 2. Versión de Laravel
$laravelVersion = app()->version();
checkItem(
    'Laravel Version',
    version_compare($laravelVersion, '11.0', '>='),
    '✅ Laravel ' . $laravelVersion,
    '⚠️ Laravel ' . $laravelVersion . ' (Se recomienda 11+)',
    ''
);

// 3. Archivo .env existe
checkItem(
    'Archivo .env',
    file_exists($basePath . '/.env'),
    '✅ Archivo .env encontrado',
    '❌ Archivo .env NO encontrado',
    'Copia .env.example a .env y ejecuta: php artisan key:generate'
);

// 4. APP_KEY generada
checkItem(
    'APP_KEY',
    !empty(env('APP_KEY')),
    '✅ APP_KEY configurada',
    '❌ APP_KEY NO configurada',
    'Ejecuta: php artisan key:generate'
);

// 5. Conexión a base de datos
$dbConnected = false;
$dbError = '';
try {
    DB::connection()->getPdo();
    $dbConnected = true;
} catch (\Exception $e) {
    $dbError = $e->getMessage();
}

checkItem(
    'Conexión Base de Datos',
    $dbConnected,
    '✅ Conectado a: ' . env('DB_DATABASE'),
    '❌ Error de conexión: ' . substr($dbError, 0, 100),
    'Verifica configuración en .env: DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD'
);

// 6. Tabla usuarios (verificar migraciones)
if ($dbConnected) {
    checkItem(
        'Migraciones Ejecutadas',
        Schema::hasTable('users') && Schema::hasTable('migrations'),
        '✅ Migraciones ejecutadas correctamente',
        '❌ Migraciones NO ejecutadas',
        'Ejecuta: php artisan migrate'
    );
    
    // 7. Tabla cursos
    checkItem(
        'Tabla cursos',
        Schema::hasTable('cursos'),
        '✅ Tabla cursos existe',
        '❌ Tabla cursos NO existe',
        'Verifica migración de cursos'
    );
    
    // 8. Datos en cursos (seeders)
    $productCount = 0;
    try {
        $productCount = DB::table('cursos')->count();
    } catch (\Exception $e) {}
    
    checkItem(
        'Cursos en BD',
        $productCount > 0,
        '✅ ' . $productCount . ' cursos encontrados',
        '⚠️ No hay cursos en la BD',
        'Ejecuta: php artisan db:seed --class=CursoSeeder'
    );
    
    // 9. Usuarios registrados
    $userCount = 0;
    try {
        $userCount = DB::table('users')->count();
    } catch (\Exception $e) {}
    
    checkItem(
        'Usuarios en BD',
        $userCount > 0,
        '✅ ' . $userCount . ' usuario(s) registrado(s)',
        '⚠️ No hay usuarios registrados',
        'Registra al menos un usuario de prueba'
    );
}

// 10. Laravel Breeze instalado
$breezeInstalled = file_exists($basePath . '/app/Http/Controllers/Auth/AuthenticatedSessionController.php');
checkItem(
    'Laravel Breeze',
    $breezeInstalled,
    '✅ Breeze instalado',
    '❌ Breeze NO instalado',
    'Ejecuta: composer require laravel/breeze --dev && php artisan breeze:install'
);

// 11. Storage link
$storageLinkExists = is_link($basePath . '/public/storage') || is_dir($basePath . '/public/storage');
checkItem(
    'Storage Link',
    $storageLinkExists,
    '✅ Storage link creado',
    '⚠️ Storage link NO creado',
    'Ejecuta: php artisan storage:link'
);

// 12. Carpeta de uploads
$uploadsExist = is_dir($basePath . '/storage/app/public');
checkItem(
    'Carpeta Storage',
    $uploadsExist,
    '✅ Carpeta storage/app/public existe',
    '❌ Carpeta storage/app/public NO existe',
    'Crea la carpeta manualmente o verifica permisos'
);

// 13. CursoController
$cursoController = file_exists($basePath . '/app/Http/Controllers/CursoController.php');
checkItem(
    'CursoController',
    $cursoController,
    '✅ CursoController encontrado',
    '❌ CursoController NO encontrado',
    'Crea el controlador: php artisan make:controller CursoController'
);

// 14. Modelo Curso
$cursoModel = file_exists($basePath . '/app/Models/Curso.php');
checkItem(
    'Modelo Curso',
    $cursoModel,
    '✅ Modelo Curso encontrado',
    '❌ Modelo Curso NO encontrado',
    'Crea el modelo: php artisan make:model Curso'
);

// 15. Vistas de cursos
$viewsExist = is_dir($basePath . '/resources/views/cursos');
checkItem(
    'Vistas cursos',
    $viewsExist,
    '✅ Carpeta views/cursos existe',
    '⚠️ Carpeta views/cursos NO existe',
    'Crea las vistas CRUD en resources/views/cursos/'
);

// 16. Internacionalización
$langEsExists = is_dir($basePath . '/lang/es');
$langEnExists = is_dir($basePath . '/lang/en');
checkItem(
    'Internacionalización',
    $langEsExists && $langEnExists,
    '✅ Carpetas lang/es y lang/en encontradas',
    '⚠️ Falta configurar i18n',
    'Crea carpetas: lang/es/ y lang/en/ con archivos de traducción'
);

// 17. Composer packages
$composerLock = file_exists($basePath . '/composer.lock');
checkItem(
    'Composer Install',
    $composerLock,
    '✅ Packages de Composer instalados',
    '❌ Ejecuta composer install',
    'Ejecuta: composer install'
);

// 18. Node packages
$nodeModules = is_dir($basePath . '/node_modules');
checkItem(
    'NPM Install',
    $nodeModules,
    '✅ Packages de NPM instalados',    
    '⚠️ Ejecuta npm install',
    'Ejecuta: npm install && npm run build'
);

// 19. Rutas principales
$routes = Route::getRoutes();
$routesCount = count($routes);
$hasCursoRoutes = false;

foreach ($routes as $route) {
    if (str_contains($route->uri(), 'cursos')) {
        $hasCursoRoutes = true;
        break;
    }
}

checkItem(
    'Rutas Cursos',
    $hasCursoRoutes,
    '✅ Rutas de cursos configuradas',
    '⚠️ No se encontraron rutas de cursos',
    'Define rutas en routes/web.php'
);

// 20. .env.example
$envExample = file_exists($basePath . '/.env.example');
checkItem(
    'Archivo .env.example',
    $envExample,
    '✅ .env.example presente',
    '⚠️ .env.example NO encontrado',
    'Incluye .env.example en tu entrega para documentación'
);

// Calcular porcentaje
$percentage = $totalChecks > 0 ? round(($passedChecks / $totalChecks) * 100) : 0;

// Obtener información del sistema
$serverInfo = [
    'PHP Version' => PHP_VERSION,
    'Laravel Version' => $laravelVersion,
    'Server Software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'Database' => env('DB_CONNECTION', 'Unknown'),
    'APP_ENV' => env('APP_ENV', 'Unknown'),
    'APP_DEBUG' => env('APP_DEBUG') ? 'Enabled' : 'Disabled',
];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico EcoMarket - Proyecto Laravel</title>
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
            padding: 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }
        
        .score-badge {
            display: inline-block;
            background: white;
            color: #667eea;
            padding: 15px 30px;
            border-radius: 50px;
            font-size: 2em;
            font-weight: bold;
            margin-top: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .score-excellent { color: #10b981; }
        .score-good { color: #3b82f6; }
        .score-warning { color: #f59e0b; }
        .score-danger { color: #ef4444; }
        
        .content {
            padding: 40px;
        }
        
        .section {
            margin-bottom: 40px;
        }
        
        .section-title {
            font-size: 1.5em;
            color: #1f2937;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #667eea;
        }
        
        .check-item {
            display: flex;
            align-items: flex-start;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 10px;
            background: #f9fafb;
            border-left: 4px solid transparent;
            transition: all 0.3s;
        }
        
        .check-item:hover {
            background: #f3f4f6;
            transform: translateX(5px);
        }
        
        .check-item.passed {
            border-left-color: #10b981;
        }
        
        .check-item.failed {
            border-left-color: #ef4444;
        }
        
        .check-icon {
            font-size: 1.5em;
            margin-right: 15px;
            min-width: 30px;
        }
        
        .check-content {
            flex: 1;
        }
        
        .check-name {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 5px;
        }
        
        .check-message {
            color: #6b7280;
            font-size: 0.95em;
        }
        
        .check-hint {
            margin-top: 8px;
            padding: 10px;
            background: #fef3c7;
            border-left: 3px solid #f59e0b;
            border-radius: 5px;
            font-size: 0.9em;
            color: #92400e;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .info-card {
            background: #f9fafb;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }
        
        .info-label {
            font-size: 0.85em;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-size: 1.1em;
            font-weight: 600;
            color: #1f2937;
        }
        
        .actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px solid #e5e7eb;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #e5e7eb;
            color: #1f2937;
        }
        
        .btn-secondary:hover {
            background: #d1d5db;
        }
        
        .alert {
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-left: 4px solid;
        }
        
        .alert-success {
            background: #d1fae5;
            border-left-color: #10b981;
            color: #065f46;
        }
        
        .alert-warning {
            background: #fef3c7;
            border-left-color: #f59e0b;
            color: #92400e;
        }
        
        .alert-danger {
            background: #fee2e2;
            border-left-color: #ef4444;
            color: #991b1b;
        }
        
        .footer {
            background: #f9fafb;
            padding: 30px;
            text-align: center;
            color: #6b7280;
            font-size: 0.9em;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .container {
                box-shadow: none;
            }
            
            .actions {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛍️ EcoMarket</h1>
            <p>Diagnóstico del Proyecto - Mini-Retos 1-20</p>
            <div class="score-badge score-<?php 
                if ($percentage >= 90) echo 'excellent';
                elseif ($percentage >= 70) echo 'good';
                elseif ($percentage >= 50) echo 'warning';
                else echo 'danger';
            ?>">
                <?php echo $percentage; ?>% Completo
            </div>
            <p style="margin-top: 15px; font-size: 0.9em;">
                <?php echo $passedChecks; ?> de <?php echo $totalChecks; ?> verificaciones pasadas
            </p>
        </div>
        
        <div class="content">
            <?php if ($percentage >= 90): ?>
                <div class="alert alert-success">
                    <strong>✅ ¡Excelente!</strong> Tu proyecto está correctamente configurado y listo para la evaluación.
                </div>
            <?php elseif ($percentage >= 70): ?>
                <div class="alert alert-warning">
                    <strong>⚠️ Casi listo.</strong> Hay algunos detalles que necesitan atención. Revisa las advertencias abajo.
                </div>
            <?php else: ?>
                <div class="alert alert-danger">
                    <strong>❌ Atención.</strong> Tu proyecto tiene problemas importantes que deben solucionarse antes de la evaluación.
                </div>
            <?php endif; ?>
            
            <div class="section">
                <h2 class="section-title">📋 Verificaciones del Proyecto</h2>
                
                <?php foreach ($checks as $check): ?>
                    <div class="check-item <?php echo $check['passed'] ? 'passed' : 'failed'; ?>">
                        <div class="check-icon">
                            <?php echo $check['passed'] ? '✅' : '❌'; ?>
                        </div>
                        <div class="check-content">
                            <div class="check-name"><?php echo $check['name']; ?></div>
                            <div class="check-message"><?php echo $check['message']; ?></div>
                            <?php if (!$check['passed'] && !empty($check['hint'])): ?>
                                <div class="check-hint">
                                    💡 <strong>Solución:</strong> <?php echo $check['hint']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="section">
                <h2 class="section-title">ℹ️ Información del Sistema</h2>
                <div class="info-grid">
                    <?php foreach ($serverInfo as $label => $value): ?>
                        <div class="info-card">
                            <div class="info-label"><?php echo $label; ?></div>
                            <div class="info-value"><?php echo $value; ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="actions">
                <a href="/" class="btn btn-primary">🏠 Ir a la Página Principal</a>
                <a href="/cursos" class="btn btn-primary">🛍️ Ver Cursos</a>
                <a href="/register" class="btn btn-secondary">👤 Registrar Usuario</a>
                <button onclick="window.print()" class="btn btn-secondary">🖨️ Imprimir Reporte</button>
                <button onclick="location.reload()" class="btn btn-secondary">🔄 Actualizar</button>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>EcoMarket</strong> - Sistema de Diagnóstico de Proyectos Laravel</p>
            <p>Mini-Retos 1-20 | Versión 1.0</p>
            <p style="margin-top: 10px; font-size: 0.85em;">
                ⚠️ Este archivo es solo para evaluación. Elimínalo antes de pasar a producción.
            </p>
        </div>
    </div>
</body>
</html>
