<?php
/**
 * =========================================================================
 * ECOMARKET - SISTEMA DE DIAGNÓSTICO Y EVALUACIÓN AUTOMÁTICA
 * =========================================================================
 * 
 * Compatible con Laravel 12, Breeze y Form Requests
 * Evalúa Mini-Retos 1-20 (Obligatorios) + Extras 21-31 (Bonus)
 * 
 * PUNTUACIÓN:
 * - Mini-Retos 1-20: 100 puntos (obligatorios)
 * - Extras 21-31: +30 puntos bonus
 * - Total máximo: 130 puntos
 * 
 * INSTRUCCIONES:
 * 1. Copiar a: public/evaluacion.php
 * 2. Acceder: http://localhost:8000/evaluacion.php
 * 3. Revisar puntuación y recomendaciones
 * 
 * @version 3.0 - Laravel 12 Edition
 * @author Sistema de Evaluación Automática EcoMarket
 * @date Diciembre 2024
 */

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

// =========================================================================
// CONFIGURACIÓN Y SEGURIDAD
// =========================================================================

// Prevenir acceso en producción
if (getenv('APP_ENV') === 'production') {
    http_response_code(403);
    die('⛔ Este archivo solo está disponible en entornos de desarrollo.');
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(60);

// =========================================================================
// INICIALIZACIÓN DE LARAVEL
// =========================================================================

$basePath = dirname(__DIR__);
$autoloadPath = $basePath . '/vendor/autoload.php';

if (!file_exists($autoloadPath)) {
    die('❌ Error: Ejecuta <code>composer install</code> primero');
}

require $autoloadPath;

$app = require_once $basePath . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$kernel->handle($request);

// =========================================================================
// SISTEMA DE EVALUACIÓN
// =========================================================================

class EvaluacionEcoMarket {

    private $basePath;
    private $evaluations = [];
    private $totalPoints = 0;
    private $maxBasePoints = 100;
    private $bonusPoints = 0;
    private $maxBonusPoints = 30;

    // Estadísticas
    private $stats = [
        'obligatorios_completados' => 0,
        'obligatorios_totales' => 20,
        'extras_completados' => 0,
        'extras_totales' => 11,
        'criticos_fallidos' => [],
    ];

    public function __construct($basePath) {
        $this->basePath = $basePath;
    }

    /**
     * Evaluar un item y asignar puntos
     */
    private function evaluate($miniReto, $category, $name, $points, $condition, $successMsg, $failMsg, $hint = '', $isCritical = false, $isBonus = false) {
        $earned = $condition ? $points : 0;

        if ($isBonus) {
            $this->bonusPoints += $earned;
            // IMPORTANTE: Los extras SOLO suman, nunca afectan negativamente
            // Por tanto, NUNCA pueden ser críticos
            $isCritical = false; // Forzar a false para extras
        } else {
            $this->totalPoints += $earned;
        }

        // Estadísticas
        if ($condition) {
            if ($isBonus) {
                $this->stats['extras_completados']++;
            } else {
                $this->stats['obligatorios_completados']++;
            }
        } else if ($isCritical && !$isBonus) {
            // Solo registrar como crítico si NO es bonus
            $this->stats['criticos_fallidos'][] = $name;
        }

        $this->evaluations[] = [
            'miniReto' => $miniReto,
            'category' => $category,
            'name' => $name,
            'maxPoints' => $points,
            'earned' => $earned,
            'passed' => $condition,
            'message' => $condition ? $successMsg : $failMsg,
            'hint' => $hint,
            'isCritical' => $isCritical,
            'isBonus' => $isBonus
        ];

        return $condition;
    }

    /**
     * Ejecutar todas las evaluaciones
     */
    public function runAllEvaluations() {
        $this->evaluarConfiguracionBasica();
        $this->evaluarModelos();          // ✅ AÑADIDO (antes faltaba)
        $this->evaluarCRUDCursos();
        $this->evaluarImagenes();
        $this->evaluarValidacion();
        $this->evaluarComponentesI18n();
        $this->evaluarBreeze();
        $this->evaluarSesionesFlash();
        $this->evaluarPolicies();
        $this->evaluarRelaciones();

        // EXTRAS/BONUS
        $this->evaluarExtras();
    }

    /**
     * MR 5-7 (extra soporte): Modelos básicos (evita error por método inexistente)
     */
    private function evaluarModelos() {
        $category = 'MR 5-7: Modelos';

        $modeloCurso = file_exists($this->basePath . '/app/Models/Curso.php');

        $this->evaluate(
            'MR 5-7',
            $category,
            'Modelo Curso',
            1,
            $modeloCurso,
            '✅ Modelo app/Models/Curso.php',
            '❌ Falta el Modelo Curso',
            'Crea: php artisan make:model Curso',
            false
        );

        // (Opcional) Modelo User existe (Laravel lo trae por defecto)
        $modeloUser = file_exists($this->basePath . '/app/Models/User.php');
        $this->evaluate(
            'MR 5-7',
            $category,
            'Modelo User',
            1,
            $modeloUser,
            '✅ Modelo app/Models/User.php',
            '⚠️ Falta el Modelo User',
            'Revisa instalación base de Laravel/Breeze',
            false
        );
    }

    /**
     * MINI-RETOS 1-4: Configuración Básica (15 puntos)
     */
    private function evaluarConfiguracionBasica() {
        $category = 'MR 1-4: Configuración y Estructura';

        // PHP 8.2+ (Laravel 12 requiere)
        $this->evaluate(
            'MR 1-4',
            $category,
            'PHP 8.2+',
            2,
            version_compare(PHP_VERSION, '8.2.0', '>='),
            '✅ PHP ' . PHP_VERSION,
            '❌ PHP ' . PHP_VERSION . ' (Laravel 12 requiere 8.2+)',
            'Actualiza PHP a 8.2 o superior',
            true
        );

        // Laravel 11+ o 12
        $laravelVersion = app()->version();
        $this->evaluate(
            'MR 1-4',
            $category,
            'Laravel 11+ o 12',
            2,
            version_compare($laravelVersion, '11.0', '>='),
            '✅ Laravel ' . $laravelVersion,
            '⚠️ Laravel ' . $laravelVersion . ' (Se recomienda 11+)',
            'Actualiza a Laravel 11 o 12',
            false
        );

        // Archivo .env configurado
        $envExists = file_exists($this->basePath . '/.env');
        $appKeySet = !empty(env('APP_KEY'));
        $this->evaluate(
            'MR 1-4',
            $category,
            'Archivo .env y APP_KEY',
            2,
            $envExists && $appKeySet,
            '✅ .env correctamente configurado',
            '❌ .env no encontrado o sin APP_KEY',
            'Ejecuta: cp .env.example .env && php artisan key:generate',
            true
        );

        // Conexión BD
        $dbConnected = false;
        $dbName = '';
        try {
            DB::connection()->getPdo();
            $dbConnected = true;
            $dbName = env('DB_DATABASE', 'desconocida');
        } catch (\Exception $e) {}

        $this->evaluate(
            'MR 1-4',
            $category,
            'Conexión a Base de Datos',
            2,
            $dbConnected,
            '✅ Conectado a: ' . $dbName,
            '❌ Sin conexión a BD',
            'Verifica .env: DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD',
            true
        );

        // Composer instalado
        $this->evaluate(
            'MR 1-4',
            $category,
            'Dependencias Composer',
            1,
            file_exists($this->basePath . '/composer.lock'),
            '✅ Composer instalado',
            '❌ Ejecuta composer install',
            'Ejecuta: composer install',
            false
        );

        // NPM instalado
        $this->evaluate(
            'MR 1-4',
            $category,
            'Dependencias NPM',
            1,
            is_dir($this->basePath . '/node_modules'),
            '✅ NPM instalado',
            '⚠️ Ejecuta npm install',
            'Ejecuta: npm install && npm run build',
            false
        );

        // Storage link
        $storageLink = is_link($this->basePath . '/public/storage') || is_dir($this->basePath . '/public/storage');
        $this->evaluate(
            'MR 1-4',
            $category,
            'Storage Link',
            1,
            $storageLink,
            '✅ Storage link creado (public/storage existe)',
            '⚠️ Storage link no creado',
            'Ejecuta: php artisan storage:link (Si ya existe el link, ignorar el error)',
            false
        );

        // .env.example
        $this->evaluate(
            'MR 1-4',
            $category,
            '.env.example (documentación)',
            1,
            file_exists($this->basePath . '/.env.example'),
            '✅ .env.example presente',
            '⚠️ .env.example no encontrado',
            'Incluye .env.example en tu entrega',
            false
        );

        // README.md
        $readmeExists = file_exists($this->basePath . '/README.md');
        $readmeContent = $readmeExists ? file_get_contents($this->basePath . '/README.md') : '';
        $readmeGood = $readmeExists && strlen($readmeContent) > 300;

        $this->evaluate(
            'MR 1-4',
            $category,
            'README.md completo',
            2,
            $readmeGood,
            '✅ README.md documentado (' . strlen($readmeContent) . ' caracteres)',
            '⚠️ README.md falta o muy corto',
            'Crea README.md con instrucciones de instalación (mínimo 300 caracteres)',
            false
        );

        // Git inicializado
        $this->evaluate(
            'MR 1-4',
            $category,
            'Repositorio Git',
            1,
            is_dir($this->basePath . '/.git'),
            '✅ Git inicializado',
            '⚠️ Git no inicializado',
            'Ejecuta: git init',
            false
        );
    }

    /**
     * MINI-RETOS 5-7: CRUD Cursos (20 puntos)
     */
    private function evaluarCRUDCursos() {
        $category = 'MR 5-7: CRUD de Cursos';

        // Migración cursos
        $cursosMigration = $this->findMigration('cursos');
        $this->evaluate(
            'MR 5-7',
            $category,
            'Migración cursos',
            2,
            $cursosMigration !== null,
            '✅ Migración create_cursos_table',
            '❌ No se encontró migración de cursos',
            'Crea: php artisan make:migration create_cursos_table',
            true
        );

        // Tabla cursos en BD
$dbConnected = false;
try {
    DB::connection()->getPdo();
    $dbConnected = true;
} catch (\Exception $e) {}

if ($dbConnected) {
    $tableExists = Schema::hasTable('cursos');
    $this->evaluate(
        'MR 5-7',
        $category,
        'Tabla cursos en BD',
        3,
        $tableExists,
        '✅ Tabla cursos existe',
        '❌ Tabla cursos NO existe',
        'Ejecuta: php artisan migrate',
        true
    );

    // Campos de la tabla
    if ($tableExists) {
        // Campos permitidos según el modelo
        $fillableFields = ['nombre', 'precio', 'vacantes', 'foto', 'fecha_inicio', 'fecha_fin', 'categoria_id'];

        // Verificar si todos los campos están presentes en la tabla
        $hasNombre = Schema::hasColumn('cursos', 'nombre');
        $hasPrecio = Schema::hasColumn('cursos', 'precio');
        $hasVacantes = Schema::hasColumn('cursos', 'vacantes');
        $hasFoto = Schema::hasColumn('cursos', 'foto');
        $hasFechaInicio = Schema::hasColumn('cursos', 'fecha_inicio');
        $hasFechaFin = Schema::hasColumn('cursos', 'fecha_fin');
        $hasCategoriaId = Schema::hasColumn('cursos', 'categoria_id');

        // Validar que todos los campos requeridos existan en la tabla
        $camposCorrectos = $hasNombre && $hasPrecio && $hasVacantes && $hasFoto && $hasFechaInicio && $hasFechaFin && $hasCategoriaId;

        // Construir mensaje de campos faltantes
        $camposFaltantes = [];
        if (!$hasNombre) $camposFaltantes[] = 'nombre';
        if (!$hasPrecio) $camposFaltantes[] = 'precio';
        if (!$hasVacantes) $camposFaltantes[] = 'vacantes';
        if (!$hasFoto) $camposFaltantes[] = 'foto';
        if (!$hasFechaInicio) $camposFaltantes[] = 'fecha_inicio';
        if (!$hasFechaFin) $camposFaltantes[] = 'fecha_fin';
        if (!$hasCategoriaId) $camposFaltantes[] = 'categoria_id';

        $failMsg = $camposFaltantes 
            ? '❌ Faltan campos obligatorios: ' . implode(', ', $camposFaltantes)
            : '❌ Faltan campos obligatorios';

        $this->evaluate(
            'MR 5-7',
            $category,
            'Campos de tabla cursos',
            2,
            $camposCorrectos,
            '✅ Campos: ' . implode(', ', $fillableFields),
            $failMsg,
            'Campos requeridos: ' . implode(', ', $fillableFields),
            true
        );
    }
}


        // Modelo Curso
        $modeloExists = file_exists($this->basePath . '/app/Models/Curso.php');
        $this->evaluate(
            'MR 5-7',
            $category,
            'Modelo Curso',
            2,
            $modeloExists,
            '✅ Modelo app/Models/Curso.php',
            '❌ Modelo Curso NO existe',
            'Crea: php artisan make:model Curso',
            true
        );

        // Verificar $fillable en modelo
        if ($modeloExists) {
            $modelContent = file_get_contents($this->basePath . '/app/Models/Curso.php');
            $hasFillable = strpos($modelContent, '$fillable') !== false || strpos($modelContent, '$guarded') !== false;

            $this->evaluate(
                'MR 5-7',
                $category,
                'Mass Assignment Protection',
                1,
                $hasFillable,
                '✅ $fillable o $guarded definido',
                '⚠️ Sin $fillable o $guarded',
                'Añade protected $fillable = [\'nombre\', \'descripcion\', \'precio\', \'stock\'];',
                false
            );
        }

        // CursoController
        $controllerExists = file_exists($this->basePath . '/app/Http/Controllers/CursoController.php');
        $this->evaluate(
            'MR 5-7',
            $category,
            'CursoController',
            2,
            $controllerExists,
            '✅ CursoController existe',
            '❌ CursoController NO existe',
            'Crea: php artisan make:controller CursoController --resource',
            true
        );

        // Métodos CRUD en controlador
        if ($controllerExists) {
            $controllerContent = file_get_contents($this->basePath . '/app/Http/Controllers/CursoController.php');
            
            // Búsquedas más flexibles usando regex y múltiples patrones
            $hasIndex = preg_match('/function\s+index\s*\(/i', $controllerContent) ||
                       preg_match('/public\s+function\s+index/i', $controllerContent);
                       
            $hasStore = preg_match('/function\s+store\s*\(/i', $controllerContent) ||
                       preg_match('/public\s+function\s+store/i', $controllerContent) ||
                       preg_match('/function\s+guardar/i', $controllerContent) ||
                       preg_match('/function\s+crear/i', $controllerContent);
                       
            $hasUpdate = preg_match('/function\s+update\s*\(/i', $controllerContent) ||
                        preg_match('/public\s+function\s+update/i', $controllerContent) ||
                        preg_match('/function\s+actualizar/i', $controllerContent) ||
                        preg_match('/function\s+editar/i', $controllerContent);
                        
            $hasDestroy = preg_match('/function\s+destroy\s*\(/i', $controllerContent) ||
                         preg_match('/public\s+function\s+destroy/i', $controllerContent) ||
                         preg_match('/function\s+eliminar/i', $controllerContent) ||
                         preg_match('/function\s+borrar/i', $controllerContent) ||
                         preg_match('/function\s+delete/i', $controllerContent);

            $metodosCRUD = $hasIndex && $hasStore && $hasUpdate && $hasDestroy;

            $this->evaluate(
                'MR 5-7',
                $category,
                'Métodos CRUD completos',
                3,
                $metodosCRUD,
                '✅ Métodos CRUD implementados (index, store, update, destroy)',
                '❌ Faltan métodos CRUD',
                'Implementa: index(), create(), store(), edit(), update(), destroy()',
                true
            );
        }

        // Rutas de cursos
        $routes = Route::getRoutes();
        $hasCursoRoutes = false;
        $routeCount = 0;

        foreach ($routes as $route) {
            if (strpos($route->uri(), 'cursos') !== false) {
                $hasCursoRoutes = true;
                $routeCount++;
            }
        }

        $this->evaluate(
            'MR 5-7',
            $category,
            'Rutas de cursos',
            2,
            $hasCursoRoutes && $routeCount >= 5,
            '✅ ' . $routeCount . ' rutas de cursos configuradas',
            '❌ Rutas de cursos incompletas',
            'Define rutas en routes/web.php: Route::resource(\'cursos\', CursoController::class)',
            true
        );

        // Vistas Blade
        $viewsExist = is_dir($this->basePath . '/resources/views/cursos');
        $this->evaluate(
            'MR 5-7',
            $category,
            'Vistas de cursos',
            3,
            $viewsExist,
            '✅ Carpeta resources/views/cursos/',
            '❌ Carpeta de vistas NO existe',
            'Crea: resources/views/cursos/{index, create, edit, show}.blade.php',
            true
        );
    }

    /**
     * MINI-RETO 8: Imágenes (8 puntos)
     */
    private function evaluarImagenes() {
        $category = 'MR 8: Gestión de Imágenes';

        // Campo imagen en tabla
        $dbConnected = false;
        try {
            DB::connection()->getPdo();
            $dbConnected = true;
        } catch (\Exception $e) {}

        if ($dbConnected && Schema::hasTable('cursos')) {
            // Buscar columna de imagen con varios nombres posibles
            $imageColumnNames = ['imagen', 'foto', 'image', 'picture', 'img'];
            $hasImagen = false;
            $foundColumnName = '';
            
            foreach ($imageColumnNames as $columnName) {
                if (Schema::hasColumn('cursos', $columnName)) {
                    $hasImagen = true;
                    $foundColumnName = $columnName;
                    break;
                }
            }
            
            $this->evaluate(
                'MR 8',
                $category,
                'Campo imagen en BD',
                2,
                $hasImagen,
                '✅ Campo ' . ($foundColumnName ? "\"$foundColumnName\"" : 'imagen') . ' en tabla cursos',
                '❌ Campo de imagen NO existe (probado: imagen, foto, image)',
                'Añade a migración: $table->string(\'imagen\')->nullable(); (o \'foto\')',
                false
            );
        }

        // Storage configurado
        $storagePublic = is_dir($this->basePath . '/storage/app/public');
        $this->evaluate(
            'MR 8',
            $category,
            'Carpeta storage/app/public',
            2,
            $storagePublic,
            '✅ storage/app/public existe',
            '⚠️ storage/app/public NO existe',
            'La carpeta debería existir por defecto',
            false
        );

        // Lógica de subida en controlador
        $controllerExists = file_exists($this->basePath . '/app/Http/Controllers/CursoController.php');
        if ($controllerExists) {
            $controllerContent = file_get_contents($this->basePath . '/app/Http/Controllers/CursoController.php');
            
            // Búsquedas flexibles para detectar subida de archivos
            $hasImageUpload = preg_match('/->store\s*\(/i', $controllerContent) ||
                             preg_match('/->storeAs\s*\(/i', $controllerContent) ||
                             preg_match('/->putFile\s*\(/i', $controllerContent) ||
                             preg_match('/\$request->file\s*\(/i', $controllerContent) ||
                             preg_match('/\$request->hasFile\s*\(/i', $controllerContent) ||
                             preg_match('/Storage::put/i', $controllerContent) ||
                             preg_match('/move_uploaded_file/i', $controllerContent);

            $this->evaluate(
                'MR 8',
                $category,
                'Lógica de subida de imagen',
                2,
                $hasImageUpload,
                '✅ Código de subida implementado',
                '⚠️ No se detectó código de subida',
                'Implementa: $request->file(\'imagen\')->store(\'cursos\', \'public\')',
                false
            );
        }

        // Input file en formulario
        $createView = $this->basePath . '/resources/views/cursos/create.blade.php';
        $hasFileInput = false;

        if (file_exists($createView)) {
            $createContent = file_get_contents($createView);
            $hasFileInput = strpos($createContent, 'type="file"') !== false;
        }

        $this->evaluate(
            'MR 8',
            $category,
            'Input file en formulario',
            2,
            $hasFileInput,
            '✅ Campo de imagen en formulario',
            '⚠️ Sin campo de imagen',
            'Añade: <input type="file" name="imagen"> y enctype="multipart/form-data" al form',
            false
        );
    }

    /**
     * MINI-RETOS 9-11: Validación y Form Requests (12 puntos)
     */
    private function evaluarValidacion() {
        $category = 'MR 9-11: Validación';

        // Buscar Form Requests
        $requestsPath = $this->basePath . '/app/Http/Requests';
        $hasFormRequests = is_dir($requestsPath);

        $cursoRequest = null;
        if ($hasFormRequests) {
            $files = File::files($requestsPath);
            foreach ($files as $file) {
                if (strpos($file->getFilename(), 'Curso') !== false) {
                    $cursoRequest = $file->getPathname();
                    break;
                }
            }
        }

        $this->evaluate(
            'MR 9-11',
            $category,
            'Form Request de Curso',
            4,
            $cursoRequest !== null,
            '✅ Form Request: ' . ($cursoRequest ? basename($cursoRequest) : ''),
            '⚠️ Sin Form Request (recomendado)',
            'Crea: php artisan make:request CursoStoreRequest',
            false
        );

        // Reglas de validación en Form Request
        if ($cursoRequest) {
            $requestContent = file_get_contents($cursoRequest);
            // Búsquedas flexibles de reglas de validación
            $hasRules = preg_match('/function\s+rules\s*\(/i', $requestContent) ||
                       preg_match('/function\s+reglas\s*\(/i', $requestContent);
            $hasRequired = preg_match('/[\'"]required[\'"]/i', $requestContent) ||
                          preg_match('/[\'"]obligatorio[\'"]/i', $requestContent);

            $this->evaluate(
                'MR 9-11',
                $category,
                'Reglas de validación',
                3,
                $hasRules && $hasRequired,
                '✅ Reglas de validación definidas',
                '⚠️ Sin reglas de validación',
                'Define rules(): [\'nombre\' => \'required|string\', ...]',
                false
            );

            // Mensajes personalizados
            $hasMessages = preg_match('/function\s+messages\s*\(/i', $requestContent) ||
                          preg_match('/function\s+mensajes\s*\(/i', $requestContent);
            $this->evaluate(
                'MR 9-11',
                $category,
                'Mensajes personalizados',
                2,
                $hasMessages,
                '✅ Mensajes personalizados',
                '⚠️ Sin mensajes personalizados (opcional)',
                'Añade messages(): [\'nombre.required\' => \'...\']',
                false
            );
        } else {
            // Verificar validación inline en controlador - BÚSQUEDA MÁS FLEXIBLE
            $controllerExists = file_exists($this->basePath . '/app/Http/Controllers/CursoController.php');
            if ($controllerExists) {
                $controllerContent = file_get_contents($this->basePath . '/app/Http/Controllers/CursoController.php');
                
                // Múltiples patrones de validación
                $hasValidate = preg_match('/\$request->validate\s*\(/i', $controllerContent) ||
                              preg_match('/\$this->validate\s*\(/i', $controllerContent) ||
                              preg_match('/Validator::make\s*\(/i', $controllerContent) ||
                              preg_match('/validate\(\s*\$request/i', $controllerContent);

                $this->evaluate(
                    'MR 9-11',
                    $category,
                    'Validación en controlador',
                    3,
                    $hasValidate,
                    '✅ Validación implementada',
                    '❌ Sin validación de datos',
                    'Añade: $request->validate([...]) en store() y update()',
                    true
                );
            }
        }

        // @error en vistas
        $createView = $this->basePath . '/resources/views/cursos/create.blade.php';
        if (file_exists($createView)) {
            $createContent = file_get_contents($createView);
            $hasError = strpos($createContent, '@error') !== false;

            $this->evaluate(
                'MR 9-11',
                $category,
                'Mostrar errores en vista',
                2,
                $hasError,
                '✅ Directiva @error implementada',
                '⚠️ Sin mostrar errores de validación',
                'Añade: @error(\'nombre\') <p>{{ $message }}</p> @enderror',
                false
            );

            // Función old()
            $hasOld = strpos($createContent, 'old(') !== false;
            $this->evaluate(
                'MR 9-11',
                $category,
                'Preservar datos con old()',
                1,
                $hasOld,
                '✅ Función old() en inputs',
                '⚠️ Sin preservar datos',
                'Añade: value="{{ old(\'nombre\') }}" en inputs',
                false
            );
        }
    }

    /**
     * MINI-RETOS 12-13: Components e i18n (10 puntos)
     */
    private function evaluarComponentesI18n() {
        $category = 'MR 12-13: Components e i18n';

        // Componentes Blade
        $componentsPath = $this->basePath . '/resources/views/components';
        $hasComponents = is_dir($componentsPath);

        $componentCount = 0;
        if ($hasComponents) {
            $files = File::files($componentsPath);
            $componentCount = count($files);
        }

        $this->evaluate(
            'MR 12-13',
            $category,
            'Componentes Blade',
            3,
            $hasComponents && $componentCount > 0,
            '✅ ' . $componentCount . ' componente(s) creado(s)',
            '⚠️ Sin componentes Blade',
            'Crea: resources/views/components/alert.blade.php',
            false
        );

        // Uso de componentes en vistas
        if ($hasComponents) {
            $indexView = $this->basePath . '/resources/views/cursos/index.blade.php';
            if (file_exists($indexView)) {
                $indexContent = file_get_contents($indexView);
                // Búsqueda más flexible de componentes (Blade components o x-components)
                $usesComponent = preg_match('/<x-[\w\-\.]+/i', $indexContent) ||
                                preg_match('/@component\s*\(/i', $indexContent) ||
                                preg_match('/<livewire:/i', $indexContent);

                $this->evaluate(
                    'MR 12-13',
                    $category,
                    'Uso de componentes',
                    2,
                    $usesComponent,
                    '✅ Componentes usados en vistas',
                    '⚠️ Componentes creados pero no usados',
                    'Usa: <x-nombre-componente />',
                    false
                );
            }
        }

        // Carpetas de idiomas (buscar también resources/lang)
        $langEs = is_dir($this->basePath . '/lang/es') || 
                 is_dir($this->basePath . '/resources/lang/es');
        $langEn = is_dir($this->basePath . '/lang/en') || 
                 is_dir($this->basePath . '/resources/lang/en');

        $this->evaluate(
            'MR 12-13',
            $category,
            'Carpetas de idiomas',
            2,
            $langEs && $langEn,
            '✅ Carpetas de idiomas (es/en)',
            '⚠️ Faltan carpetas de idiomas',
            'Crea: lang/es/ y lang/en/ con archivos de traducción',
            false
        );

        // Archivos de traducción
        if ($langEs && $langEn) {
            $esPath = is_dir($this->basePath . '/lang/es') ? 
                     $this->basePath . '/lang/es' : 
                     $this->basePath . '/resources/lang/es';
            $enPath = is_dir($this->basePath . '/lang/en') ? 
                     $this->basePath . '/lang/en' : 
                     $this->basePath . '/resources/lang/en';
                     
            $esFiles = is_dir($esPath) ? File::files($esPath) : [];
            $enFiles = is_dir($enPath) ? File::files($enPath) : [];

            $hasTranslations = count($esFiles) > 0 && count($enFiles) > 0;

            $this->evaluate(
                'MR 12-13',
                $category,
                'Archivos de traducción',
                2,
                $hasTranslations,
                '✅ Archivos de traducción presentes',
                '⚠️ Carpetas vacías',
                'Crea archivos: lang/es/messages.php y lang/en/messages.php',
                false
            );
        }

        // Uso de traducciones en vistas - BÚSQUEDA MÁS FLEXIBLE
        $indexView = $this->basePath . '/resources/views/cursos/index.blade.php';
        if (file_exists($indexView)) {
            $indexContent = file_get_contents($indexView);
            // Múltiples patrones de traducción
            $usesTranslation = preg_match('/__\s*\(/i', $indexContent) ||
                              preg_match('/@lang\s*\(/i', $indexContent) ||
                              preg_match('/\{\{\s*__\s*\(/i', $indexContent) ||
                              preg_match('/trans\s*\(/i', $indexContent) ||
                              preg_match('/@__\s*\(/i', $indexContent);

            $this->evaluate(
                'MR 12-13',
                $category,
                'Uso de traducciones',
                1,
                $usesTranslation,
                '✅ Traducciones usadas en vistas',
                '⚠️ Sin uso de traducciones',
                'Usa: {{ __(\'messages.welcome\') }} o @lang(\'messages.welcome\')',
                false
            );
        }
    }

    /**
     * MINI-RETOS 14-15: Laravel Breeze (15 puntos)
     */
    private function evaluarBreeze() {
        $category = 'MR 14-15: Laravel Breeze';

        // Breeze instalado
        $authController = file_exists($this->basePath . '/app/Http/Controllers/Auth/AuthenticatedSessionController.php');
        $this->evaluate(
            'MR 14-15',
            $category,
            'Laravel Breeze instalado',
            4,
            $authController,
            '✅ Breeze instalado',
            '❌ Breeze NO instalado',
            'Instala: composer require laravel/breeze --dev && php artisan breeze:install',
            true
        );

        // Rutas de autenticación
        $routes = Route::getRoutes();
        $hasLogin = false;
        $hasRegister = false;
        $hasDashboard = false;

        foreach ($routes as $route) {
            $uri = $route->uri();
            if ($uri === 'login') $hasLogin = true;
            if ($uri === 'register') $hasRegister = true;
            if ($uri === 'dashboard') $hasDashboard = true;
        }

        $this->evaluate(
            'MR 14-15',
            $category,
            'Rutas de autenticación',
            3,
            $hasLogin && $hasRegister && $hasDashboard,
            '✅ Rutas: login, register, dashboard',
            '❌ Rutas de auth incompletas',
            'Breeze debería crear las rutas automáticamente',
            true
        );

        // Usuarios en BD
        $dbConnected = false;
        $userCount = 0;
        try {
            DB::connection()->getPdo();
            $dbConnected = true;
            $userCount = DB::table('users')->count();
        } catch (\Exception $e) {}

        if ($dbConnected) {
            $this->evaluate(
                'MR 14-15',
                $category,
                'Usuarios registrados',
                2,
                $userCount > 0,
                '✅ ' . $userCount . ' usuario(s) registrado(s)',
                '⚠️ No hay usuarios',
                'Registra al menos un usuario de prueba',
                false
            );
        }

        // Middleware auth
        $webRoutes = file_get_contents($this->basePath . '/routes/web.php');
        $hasAuthMiddleware = strpos($webRoutes, 'middleware(\'auth\')') !== false ||
                            strpos($webRoutes, 'middleware([\'auth\'])') !== false;

        $this->evaluate(
            'MR 14-15',
            $category,
            'Middleware auth aplicado',
            2,
            $hasAuthMiddleware,
            '✅ Middleware auth en rutas',
            '⚠️ Sin middleware auth',
            'Protege rutas: ->middleware(\'auth\')',
            false
        );

        // @csrf en formularios
        $createView = $this->basePath . '/resources/views/cursos/create.blade.php';
        $hasCsrf = false;
        if (file_exists($createView)) {
            $createContent = file_get_contents($createView);
            $hasCsrf = strpos($createContent, '@csrf') !== false;
        }

        $this->evaluate(
            'MR 14-15',
            $category,
            'Protección CSRF',
            2,
            $hasCsrf,
            '✅ @csrf en formularios',
            '❌ Sin protección CSRF',
            'Añade @csrf en todos los formularios POST',
            true
        );

        // @method en formularios PUT/DELETE
        $editView = $this->basePath . '/resources/views/cursos/edit.blade.php';
        $hasMethod = false;
        if (file_exists($editView)) {
            $editContent = file_get_contents($editView);
            $hasMethod = strpos($editContent, '@method') !== false;
        }

        $this->evaluate(
            'MR 14-15',
            $category,
            'Método HTTP correcto',
            2,
            $hasMethod,
            '✅ @method(\'PUT\') o @method(\'DELETE\')',
            '⚠️ Sin @method en formularios',
            'Añade: @method(\'PUT\') en formularios de actualización',
            false
        );
    }

    /**
     * MINI-RETOS 16-18: Sesiones y Flash Messages (5 puntos)
     */
    private function evaluarSesionesFlash() {
        $category = 'MR 16-18: Sesiones y Flash';

        // Flash messages en controlador
        $controllerExists = file_exists($this->basePath . '/app/Http/Controllers/CursoController.php');
        if ($controllerExists) {
            $controllerContent = file_get_contents($this->basePath . '/app/Http/Controllers/CursoController.php');
            
            // Búsqueda flexible de flash messages
            $hasFlash = preg_match('/->with\s*\(/i', $controllerContent) ||
                       preg_match('/session\(\)->flash\s*\(/i', $controllerContent) ||
                       preg_match('/Session::flash\s*\(/i', $controllerContent) ||
                       preg_match('/\$request->session\(\)->flash/i', $controllerContent);

            $this->evaluate(
                'MR 16-18',
                $category,
                'Flash messages en controlador',
                2,
                $hasFlash,
                '✅ Flash messages implementados',
                '⚠️ Sin flash messages',
                'Añade: return redirect()->with(\'success\', \'Curso creado\')',
                false
            );
        }

        // Mostrar flash en vistas - BÚSQUEDA MÁS FLEXIBLE
        $layoutPath = $this->basePath . '/resources/views/layouts/app.blade.php';
        $hasFlashDisplay = false;

        if (file_exists($layoutPath)) {
            $layoutContent = file_get_contents($layoutPath);
            $hasFlashDisplay = preg_match('/session\s*\(/i', $layoutContent) ||
                              preg_match('/@if\s*\(\s*session/i', $layoutContent) ||
                              preg_match('/Session::/i', $layoutContent) ||
                              preg_match('/\$__env->yieldContent\(.*session/i', $layoutContent);
        }

        // Buscar en otras vistas si no está en layout
        if (!$hasFlashDisplay) {
            $indexView = $this->basePath . '/resources/views/cursos/index.blade.php';
            if (file_exists($indexView)) {
                $indexContent = file_get_contents($indexView);
                $hasFlashDisplay = preg_match('/session\s*\(/i', $indexContent) ||
                                  preg_match('/@if\s*\(\s*session/i', $indexContent);
            }
        }

        $this->evaluate(
            'MR 16-18',
            $category,
            'Mostrar flash en vistas',
            2,
            $hasFlashDisplay,
            '✅ Flash messages mostrados',
            '⚠️ Flash no se muestra',
            'Añade: @if(session(\'success\')) <div>{{ session(\'success\') }}</div> @endif',
            false
        );

        // Debugging habilitado en .env
        $debugEnabled = env('APP_DEBUG', false);
        $this->evaluate(
            'MR 16-18',
            $category,
            'Debug mode (desarrollo)',
            1,
            $debugEnabled,
            '✅ APP_DEBUG=true',
            '⚠️ Debug deshabilitado',
            'En .env: APP_DEBUG=true (solo desarrollo)',
            false
        );
    }

    /**
     * MINI-RETO 19: Policies y Gates (8 puntos)
     */
    private function evaluarPolicies() {
        $category = 'MR 19: Policies y Gates';

        // Buscar Policy
        $policiesPath = $this->basePath . '/app/Policies';
        $hasCursoPolicy = false;

        if (is_dir($policiesPath)) {
            $files = File::files($policiesPath);
            foreach ($files as $file) {
                if (strpos($file->getFilename(), 'Curso') !== false) {
                    $hasCursoPolicy = true;
                    break;
                }
            }
        }

        $this->evaluate(
            'MR 19',
            $category,
            'CursoPolicy creada',
            3,
            $hasCursoPolicy,
            '✅ Policy de Curso',
            '⚠️ Sin Policy (opcional)',
            'Crea: php artisan make:policy CursoPolicy --model=Curso',
            false
        );

        // Métodos en Policy
        if ($hasCursoPolicy) {
            $policyFiles = File::files($policiesPath);
            foreach ($policyFiles as $file) {
                if (strpos($file->getFilename(), 'Curso') !== false) {
                    $policyContent = file_get_contents($file->getPathname());
                    $hasUpdate = strpos($policyContent, 'function update') !== false;
                    $hasDelete = strpos($policyContent, 'function delete') !== false;

                    $this->evaluate(
                        'MR 19',
                        $category,
                        'Métodos de autorización',
                        2,
                        $hasUpdate || $hasDelete,
                        '✅ Métodos: update, delete',
                        '⚠️ Sin métodos implementados',
                        'Implementa: update(), delete() en Policy',
                        false
                    );
                    break;
                }
            }

            // Uso de $this->authorize() en controlador
            $controllerExists = file_exists($this->basePath . '/app/Http/Controllers/CursoController.php');
            if ($controllerExists) {
                $controllerContent = file_get_contents($this->basePath . '/app/Http/Controllers/CursoController.php');
                $usesAuthorize = strpos($controllerContent, '$this->authorize') !== false ||
                                strpos($controllerContent, 'Gate::') !== false;

                $this->evaluate(
                    'MR 19',
                    $category,
                    'Uso de autorización',
                    3,
                    $usesAuthorize,
                    '✅ $this->authorize() o Gate::',
                    '⚠️ Sin uso de autorización',
                    'Usa: $this->authorize(\'update\', $curso);',
                    false
                );
            }
        }
    }

    /**
     * MINI-RETO 20: Relaciones Eloquent (7 puntos)
     */
    private function evaluarRelaciones() {
        $category = 'MR 20: Relaciones Eloquent';

        // Modelo Curso con relaciones
        $modeloExists = file_exists($this->basePath . '/app/Models/Curso.php');
        if ($modeloExists) {
            $modelContent = file_get_contents($this->basePath . '/app/Models/Curso.php');

            // Buscar relaciones con múltiples patrones
            $hasBelongsTo = preg_match('/belongsTo\s*\(/i', $modelContent) ||
                           preg_match('/perteneceA\s*\(/i', $modelContent);
            $hasHasMany = preg_match('/hasMany\s*\(/i', $modelContent) ||
                         preg_match('/tieneMuchos\s*\(/i', $modelContent);
            $hasBelongsToMany = preg_match('/belongsToMany\s*\(/i', $modelContent) ||
                               preg_match('/perteneceAMuchos\s*\(/i', $modelContent);
            $hasHasOne = preg_match('/hasOne\s*\(/i', $modelContent) ||
                        preg_match('/tieneUno\s*\(/i', $modelContent);

            $hasRelations = $hasBelongsTo || $hasHasMany || $hasBelongsToMany || $hasHasOne;

            $this->evaluate(
                'MR 20',
                $category,
                'Relaciones en Modelo',
                3,
                $hasRelations,
                '✅ Relaciones Eloquent definidas',
                '⚠️ Sin relaciones (opcional)',
                'Define: public function user() { return $this->belongsTo(User::class); }',
                false
            );

            // Uso de relaciones en vistas
            if ($hasRelations) {
                $indexView = $this->basePath . '/resources/views/cursos/index.blade.php';
                if (file_exists($indexView)) {
                    $indexContent = file_get_contents($indexView);
                    // Buscar acceso a propiedades relacionadas (ej: $curso->user->name)
                    $usesRelations = preg_match('/\$\w+->(\w+)->/i', $indexContent) ||
                                    preg_match('/\{\{\s*\$\w+->(\w+)->/i', $indexContent);

                    $this->evaluate(
                        'MR 20',
                        $category,
                        'Uso de relaciones en vistas',
                        2,
                        $usesRelations,
                        '✅ Relaciones usadas: $curso->user->name',
                        '⚠️ Relaciones no usadas',
                        'Usa: {{ $curso->user->name }}',
                        false
                    );
                }
            }
        }

        // Eager loading (with()) - BÚSQUEDA MÁS FLEXIBLE
        $controllerExists = file_exists($this->basePath . '/app/Http/Controllers/CursoController.php');
        if ($controllerExists) {
            $controllerContent = file_get_contents($this->basePath . '/app/Http/Controllers/CursoController.php');
            // Buscar with() pero solo en contexto de queries, no de redirect()->with()
            $usesEagerLoading = preg_match('/::with\s*\(/i', $controllerContent) ||
                               preg_match('/\$query->with\s*\(/i', $controllerContent) ||
                               preg_match('/Curso::.*with\s*\(/i', $controllerContent) ||
                               (preg_match('/->with\s*\([\'\"]/i', $controllerContent) && 
                                strpos($controllerContent, 'Curso') !== false);

            $this->evaluate(
                'MR 20',
                $category,
                'Eager Loading (N+1 query)',
                2,
                $usesEagerLoading,
                '✅ Eager loading: ->with()',
                '⚠️ Sin eager loading (opcional)',
                'Optimiza: Curso::with(\'user\')->get()',
                false
            );
        }
    }

    /**
     * EXTRAS/BONUS: Mini-Retos 21-31 (30 puntos)
     */
    private function evaluarExtras() {
        $this->evaluarPaginacion();
        $this->evaluarTesting();
        $this->evaluarEmailsPDFExcel();
        $this->evaluarEventosColas();
        $this->evaluarAPIs();
        $this->evaluarFrontend();
        $this->evaluarPlanificacion();
    }

    private function evaluarPaginacion() {
        $category = 'EXTRA: MR 21 - Paginación';

        $controllerExists = file_exists($this->basePath . '/app/Http/Controllers/CursoController.php');
        if ($controllerExists) {
            $controllerContent = file_get_contents($this->basePath . '/app/Http/Controllers/CursoController.php');
            $usesPagination = strpos($controllerContent, '->paginate(') !== false;

            $this->evaluate(
                'EXTRA 21',
                $category,
                'Paginación implementada',
                2,
                $usesPagination,
                '✅ ->paginate() en controlador',
                'Sin paginación',
                'Usa: Curso::paginate(15)',
                '',
                false,
                true
            );

            // Scopes
            $modeloExists = file_exists($this->basePath . '/app/Models/Curso.php');
            if ($modeloExists) {
                $modelContent = file_get_contents($this->basePath . '/app/Models/Curso.php');
                $hasScopes = strpos($modelContent, 'function scope') !== false;

                $this->evaluate(
                    'EXTRA 21',
                    $category,
                    'Scopes locales',
                    1,
                    $hasScopes,
                    '✅ Scopes definidos',
                    'Sin scopes',
                    'Crea: public function scopeActivos($query) {...}',
                    '',
                    false,
                    true
                );
            }
        }
    }

    private function evaluarTesting() {
        $category = 'EXTRA: MR 22 - Testing';

        $testsPath = $this->basePath . '/tests/Feature';
        $hasTests = false;
        $testCount = 0;

        if (is_dir($testsPath)) {
            $files = File::files($testsPath);
            foreach ($files as $file) {
                if (strpos($file->getFilename(), 'Curso') !== false ||
                    strpos($file->getFilename(), 'Test') !== false) {
                    $hasTests = true;
                    $testCount++;
                }
            }
        }

        $this->evaluate(
            'EXTRA 22',
            $category,
            'Tests creados',
            3,
            $hasTests && $testCount > 0,
            '✅ ' . $testCount . ' test(s) encontrado(s)',
            'Sin tests',
            'Crea: php artisan make:test CursoTest',
            '',
            false,
            true
        );
    }

    private function evaluarEmailsPDFExcel() {
        $category = 'EXTRA: MR 23-24 - Mails/PDF/Excel';

        // Mailable
        $mailsPath = $this->basePath . '/app/Mail';
        $hasMailable = false;
        if (is_dir($mailsPath)) {
            $files = File::files($mailsPath);
            $hasMailable = count($files) > 0;
        }

        $this->evaluate(
            'EXTRA 23-24',
            $category,
            'Mailable creado',
            2,
            $hasMailable,
            '✅ Mailable en app/Mail',
            'Sin Mailable',
            'Crea: php artisan make:mail WelcomeMail',
            '',
            false,
            true
        );

        // PDF - DomPDF
        $composerContent = file_get_contents($this->basePath . '/composer.json');
        $hasDomPDF = strpos($composerContent, 'dompdf') !== false;

        $this->evaluate(
            'EXTRA 23-24',
            $category,
            'Generación PDF',
            2,
            $hasDomPDF,
            '✅ DomPDF instalado',
            'Sin generación PDF',
            'Instala: composer require barryvdh/laravel-dompdf',
            '',
            false,
            true
        );

        // Excel - PhpSpreadsheet
        $hasSpreadsheet = strpos($composerContent, 'phpspreadsheet') !== false ||
                         strpos($composerContent, 'laravel-excel') !== false;

        $this->evaluate(
            'EXTRA 23-24',
            $category,
            'Exportación Excel',
            2,
            $hasSpreadsheet,
            '✅ PhpSpreadsheet o Laravel Excel',
            'Sin exportación Excel',
            'Instala: composer require phpoffice/phpspreadsheet',
            '',
            false,
            true
        );
    }

    private function evaluarEventosColas() {
        $category = 'EXTRA: MR 25-26 - Eventos/Colas';

        // Eventos
        $eventsPath = $this->basePath . '/app/Events';
        $hasEvents = is_dir($eventsPath) && count(File::files($eventsPath)) > 0;

        $this->evaluate(
            'EXTRA 25-26',
            $category,
            'Eventos creados',
            2,
            $hasEvents,
            '✅ Eventos en app/Events',
            'Sin eventos',
            'Crea: php artisan make:event CursoCreado',
            '',
            false,
            true
        );

        // Listeners
        $listenersPath = $this->basePath . '/app/Listeners';
        $hasListeners = is_dir($listenersPath) && count(File::files($listenersPath)) > 0;

        $this->evaluate(
            'EXTRA 25-26',
            $category,
            'Listeners creados',
            2,
            $hasListeners,
            '✅ Listeners en app/Listeners',
            'Sin listeners',
            'Crea: php artisan make:listener NotificarUsuario',
            '',
            false,
            true
        );

        // Jobs
        $jobsPath = $this->basePath . '/app/Jobs';
        $hasJobs = is_dir($jobsPath) && count(File::files($jobsPath)) > 1;

        $this->evaluate(
            'EXTRA 25-26',
            $category,
            'Jobs creados',
            2,
            $hasJobs,
            '✅ Jobs en app/Jobs',
            'Sin jobs de cola',
            'Crea: php artisan make:job EnviarEmailJob',
            '',
            false,
            true
        );

        // Tabla jobs
        $dbConnected = false;
        try {
            DB::connection()->getPdo();
            $dbConnected = true;
        } catch (\Exception $e) {}

        if ($dbConnected) {
            $hasJobsTable = Schema::hasTable('jobs');
            $this->evaluate(
                'EXTRA 25-26',
                $category,
                'Tabla de colas',
                1,
                $hasJobsTable,
                '✅ Tabla jobs configurada',
                'Sin tabla de colas',
                'Ejecuta: php artisan queue:table && php artisan migrate',
                '',
                false,
                true
            );
        }
    }

    private function evaluarAPIs() {
    $category = 'EXTRA: MR 27-28 - APIs REST';

    // Controlador API
    $apiPath = $this->basePath . '/app/Http/Controllers/Api';
    $hasApiController = is_dir($apiPath);

    $this->evaluate(
        'EXTRA 27-28',
        $category,
        'Controlador API',
        2,
        $hasApiController,
        '✅ Controladores en app/Http/Controllers/Api',
        'Sin controladores API',
        'Crea: php artisan make:controller Api/CursoController --api',
        '',
        false,
        true
    );

    // Rutas API (Laravel puede no tener routes/api.php si no se usa API)
    $apiFile = $this->basePath . '/routes/api.php';
    $apiRoutes = file_exists($apiFile) ? file_get_contents($apiFile) : '';
    $hasApiRoutes = file_exists($apiFile) && strlen($apiRoutes) > 50; // umbral más realista

    $this->evaluate(
        'EXTRA 27-28',
        $category,
        'Rutas API',
        2,
        $hasApiRoutes,
        file_exists($apiFile) ? '✅ Rutas definidas en api.php' : '⚠️ api.php no existe (no usas API)',
        file_exists($apiFile) ? 'Sin rutas API' : 'Sin archivo routes/api.php',
        file_exists($apiFile) ? 'Define rutas en routes/api.php' : 'Crea routes/api.php o instala API routes según tu stack',
        '',
        false,
        true
    );

    // Sanctum
    $composerFile = $this->basePath . '/composer.json';
    $composerContent = file_exists($composerFile) ? file_get_contents($composerFile) : '';
    $hasSanctum = strpos($composerContent, 'sanctum') !== false;

    $this->evaluate(
        'EXTRA 27-28',
        $category,
        'Laravel Sanctum',
        2,
        $hasSanctum,
        '✅ Sanctum instalado',
        'Sin Sanctum',
        'Si lo necesitas: composer require laravel/sanctum && php artisan sanctum:install',
        '',
        false,
        true
    );
}

    private function evaluarFrontend() {
        $category = 'EXTRA: MR 29-31 - Frontend';

        // Tailwind CSS
        $tailwindConfig = file_exists($this->basePath . '/tailwind.config.js');
        $this->evaluate(
            'EXTRA 29-31',
            $category,
            'Tailwind CSS',
            2,
            $tailwindConfig,
            '✅ Tailwind configurado',
            'Sin Tailwind',
            'Laravel Breeze incluye Tailwind',
            '',
            false,
            true
        );

        // Vite
        $viteConfig = file_exists($this->basePath . '/vite.config.js');
        $this->evaluate(
            'EXTRA 29-31',
            $category,
            'Vite',
            1,
            $viteConfig,
            '✅ Vite configurado',
            'Sin Vite',
            'Laravel 12 usa Vite por defecto',
            '',
            false,
            true
        );

        // React/Inertia
        $packageJson = file_get_contents($this->basePath . '/package.json');
        $hasReact = strpos($packageJson, 'react') !== false;
        $hasInertia = strpos($packageJson, 'inertiajs') !== false;

        $this->evaluate(
            'EXTRA 29-31',
            $category,
            'React + Inertia.js',
            3,
            $hasReact && $hasInertia,
            '✅ Stack React/Inertia',
            'Sin React/Inertia',
            'Instala: php artisan breeze:install react',
            '',
            false,
            true
        );
    }

    private function evaluarPlanificacion() {
        $category = 'EXTRA: Planificación Ágil';

        // Buscar documentación ágil
        $readmeExists = file_exists($this->basePath . '/README.md');
        $hasAgileDocs = false;

        if ($readmeExists) {
            $readmeContent = file_get_contents($this->basePath . '/README.md');
            $hasUserStories = stripos($readmeContent, 'historia') !== false ||
                             stripos($readmeContent, 'user story') !== false ||
                             stripos($readmeContent, 'como usuario') !== false;

            $hasAcceptance = stripos($readmeContent, 'criterio') !== false ||
                            stripos($readmeContent, 'acceptance') !== false ||
                            stripos($readmeContent, 'dado que') !== false ||
                            stripos($readmeContent, 'given') !== false;

            $hasAgileDocs = $hasUserStories || $hasAcceptance;
        }

        // También buscar archivo separado
        $agileDocs = [
            '/docs/planificacion.md',
            '/docs/user-stories.md',
            '/PLANNING.md',
            '/USER_STORIES.md'
        ];

        foreach ($agileDocs as $doc) {
            if (file_exists($this->basePath . $doc)) {
                $hasAgileDocs = true;
                break;
            }
        }

        $this->evaluate(
            'EXTRA Planif.',
            $category,
            'Documentación Ágil',
            2,
            $hasAgileDocs,
            '✅ Historias de usuario y/o criterios',
            'Sin documentación ágil',
            'Documenta: historias de usuario, criterios de aceptación',
            '',
            false,
            true
        );
    }

    /**
     * Encontrar migración por nombre
     */
    private function findMigration($name) {
        $migrationsPath = $this->basePath . '/database/migrations';
        if (!is_dir($migrationsPath)) return null;

        $files = File::files($migrationsPath);
        foreach ($files as $file) {
            if (stripos($file->getFilename(), $name) !== false) {
                return $file->getPathname();
            }
        }
        return null;
    }

    public function getEvaluations() { return $this->evaluations; }

    public function getScore() {
        return [
            'base' => $this->totalPoints,
            'maxBase' => $this->maxBasePoints,
            'bonus' => $this->bonusPoints,
            'maxBonus' => $this->maxBonusPoints,
            'total' => $this->totalPoints + $this->bonusPoints,
            'maxTotal' => $this->maxBasePoints + $this->maxBonusPoints,
            'percentage' => round(($this->totalPoints / $this->maxBasePoints) * 100),
            'percentageWithBonus' => round((($this->totalPoints + $this->bonusPoints) / ($this->maxBasePoints + $this->maxBonusPoints)) * 100)
        ];
    }

    public function getStats() { return $this->stats; }

    public function getGrade() {
        $percentage = round(($this->totalPoints / $this->maxBasePoints) * 100);
        $criticalCount = count($this->stats['criticos_fallidos']);

        // =====================================================================
        // REGLAS ESTRICTAS PARA ERRORES CRÍTICOS
        // =====================================================================
        // Si hay 3+ errores críticos → INSUFICIENTE (automáticamente suspenso)
        // Si hay 2 errores críticos → Máximo BIEN (6.0-6.9)
        // Si hay 1 error crítico → Máximo NOTABLE (7.0-7.9)
        // Sin errores críticos → Calificación normal según porcentaje
        // =====================================================================

        if ($criticalCount >= 3) {
            // Con 3 o más errores críticos: AUTOMÁTICAMENTE INSUFICIENTE
            return ['text' => 'INSUFICIENTE', 'class' => 'insuficiente', 'number' => '<5'];
        }

        if ($criticalCount == 2) {
            // Con 2 errores críticos: MÁXIMO BIEN (limitado a 6.0-6.9)
            if ($percentage >= 60) {
                return ['text' => 'BIEN', 'class' => 'bien', 'number' => '6.0-6.9'];
            }
            if ($percentage >= 50) {
                return ['text' => 'SUFICIENTE', 'class' => 'suficiente', 'number' => '5.0-5.9'];
            }
            return ['text' => 'INSUFICIENTE', 'class' => 'insuficiente', 'number' => '<5'];
        }

        if ($criticalCount == 1) {
            // Con 1 error crítico: MÁXIMO NOTABLE (limitado a 7.0-7.9)
            if ($percentage >= 70) {
                return ['text' => 'NOTABLE', 'class' => 'notable', 'number' => '7.0-7.9'];
            }
            if ($percentage >= 60) {
                return ['text' => 'BIEN', 'class' => 'bien', 'number' => '6.0-6.9'];
            }
            if ($percentage >= 50) {
                return ['text' => 'SUFICIENTE', 'class' => 'suficiente', 'number' => '5.0-5.9'];
            }
            return ['text' => 'INSUFICIENTE', 'class' => 'insuficiente', 'number' => '<5'];
        }

        // SIN ERRORES CRÍTICOS: Calificación normal según porcentaje
        if ($percentage >= 95) return ['text' => 'MATRÍCULA DE HONOR', 'class' => 'matricula', 'number' => '10'];
        if ($percentage >= 90) return ['text' => 'SOBRESALIENTE', 'class' => 'sobresaliente', 'number' => '9.0-9.9'];
        if ($percentage >= 80) return ['text' => 'NOTABLE', 'class' => 'notable', 'number' => '8.0-8.9'];
        if ($percentage >= 70) return ['text' => 'NOTABLE', 'class' => 'notable', 'number' => '7.0-7.9'];
        if ($percentage >= 60) return ['text' => 'BIEN', 'class' => 'bien', 'number' => '6.0-6.9'];
        if ($percentage >= 50) return ['text' => 'SUFICIENTE', 'class' => 'suficiente', 'number' => '5.0-5.9'];
        return ['text' => 'INSUFICIENTE', 'class' => 'insuficiente', 'number' => '<5'];
    }
}

// =========================================================================
// EJECUTAR EVALUACIÓN
// =========================================================================

$evaluacion = new EvaluacionEcoMarket($basePath);
$evaluacion->runAllEvaluations();

$evaluations = $evaluacion->getEvaluations();
$score = $evaluacion->getScore();
$stats = $evaluacion->getStats();
$grade = $evaluacion->getGrade();

// Agrupar por categoría
$byCategory = [];
foreach ($evaluations as $eval) {
    $category = $eval['category'];
    if (!isset($byCategory[$category])) {
        $byCategory[$category] = [];
    }
    $byCategory[$category][] = $eval;
}

// Información del sistema
$serverInfo = [
    'PHP Version' => PHP_VERSION,
    'Laravel Version' => app()->version(),
    'Database' => env('DB_CONNECTION', 'Unknown') . ' (' . env('DB_DATABASE', 'N/A') . ')',
    'APP_ENV' => env('APP_ENV', 'Unknown'),
    'APP_DEBUG' => env('APP_DEBUG') ? 'Enabled' : 'Disabled',
];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluación EcoMarket - Laravel 12</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 1400px;
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
            font-size: 3em;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        .header p { font-size: 1.2em; opacity: 0.95; }
        .score-section {
            background: linear-gradient(to right, #f8f9fa, #ffffff);
            padding: 30px;
            border-bottom: 3px solid #e0e0e0;
        }
        .score-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .score-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-left: 5px solid;
            transition: transform 0.3s;
        }
        .score-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        .score-card.base { border-left-color: #667eea; }
        .score-card.bonus { border-left-color: #fbbf24; }
        .score-card.total { border-left-color: #10b981; }
        .score-card.grade { border-left-color: #ef4444; }
        .score-label {
            font-size: 0.9em;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        .score-value {
            font-size: 2.5em;
            font-weight: bold;
            color: #1f2937;
        }
        .score-subtext {
            font-size: 0.95em;
            color: #9ca3af;
            margin-top: 5px;
        }
        .grade-badge {
            display: inline-block;
            padding: 15px 30px;
            border-radius: 10px;
            font-size: 1.5em;
            font-weight: bold;
            color: white;
            text-transform: uppercase;
            letter-spacing: 2px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        .grade-badge.matricula { background: linear-gradient(135deg, #fbbf24, #f59e0b); }
        .grade-badge.sobresaliente { background: linear-gradient(135deg, #10b981, #059669); }
        .grade-badge.notable-alto { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .grade-badge.notable { background: linear-gradient(135deg, #6366f1, #4f46e5); }
        .grade-badge.bien { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
        .grade-badge.aprobado { background: linear-gradient(135deg, #14b8a6, #0d9488); }
        .grade-badge.suficiente { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .grade-badge.insuficiente { background: linear-gradient(135deg, #ef4444, #dc2626); }
        .stats-bar {
            background: #f3f4f6;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        .stat-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #667eea;
        }
        .stat-label {
            font-size: 0.85em;
            color: #6b7280;
            margin-top: 5px;
        }
        .content { padding: 40px; }
        .category-section { margin-bottom: 40px; }
        .category-header {
            background: linear-gradient(to right, #667eea, #764ba2);
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            font-size: 1.3em;
            font-weight: bold;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        .category-header.extra {
            background: linear-gradient(to right, #fbbf24, #f59e0b);
        }
        .eval-item {
            display: flex;
            align-items: flex-start;
            padding: 18px;
            margin-bottom: 12px;
            border-radius: 12px;
            background: #f9fafb;
            border-left: 5px solid transparent;
            transition: all 0.3s;
        }
        .eval-item:hover {
            background: #f3f4f6;
            transform: translateX(5px);
        }
        .eval-item.passed { border-left-color: #10b981; }
        .eval-item.failed { border-left-color: #ef4444; }
        .eval-item.critical { border: 2px solid #ef4444; background: #fef2f2; }
        .eval-item.bonus { border-left-color: #fbbf24; }
        .eval-icon {
            font-size: 1.8em;
            margin-right: 15px;
            min-width: 35px;
            text-align: center;
        }
        .eval-content { flex: 1; }
        .eval-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .eval-name {
            font-weight: 600;
            color: #1f2937;
            font-size: 1.05em;
        }
        .eval-points {
            background: #667eea;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: bold;
        }
        .eval-points.bonus { background: #fbbf24; }
        .eval-message {
            color: #6b7280;
            font-size: 0.95em;
            margin-bottom: 5px;
        }
        .eval-hint {
            margin-top: 10px;
            padding: 12px;
            background: #fef3c7;
            border-left: 3px solid #f59e0b;
            border-radius: 6px;
            font-size: 0.9em;
            color: #92400e;
        }
        .eval-hint strong { color: #78350f; }
        .critical-alert {
            background: #fee2e2;
            border: 2px solid #ef4444;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .critical-alert h3 { color: #991b1b; margin-bottom: 10px; }
        .critical-alert ul { margin-left: 20px; color: #991b1b; }
        .info-section {
            background: #f3f4f6;
            padding: 30px;
            margin-top: 40px;
            border-radius: 10px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .info-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }
        .info-label {
            font-size: 0.85em;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
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
            padding: 30px;
            background: #f9fafb;
            border-radius: 10px;
        }
        .btn {
            display: inline-block;
            padding: 14px 28px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            cursor: pointer;
            border: none;
            font-size: 1em;
        }
        .btn-primary { background: #667eea; color: white; }
        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(102, 126, 234, 0.4);
        }
        .btn-secondary { background: #e5e7eb; color: #1f2937; }
        .btn-secondary:hover { background: #d1d5db; }
        .footer {
            background: #1f2937;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .footer p { margin: 5px 0; opacity: 0.9; }
        .progress-bar {
            height: 30px;
            background: #e5e7eb;
            border-radius: 15px;
            overflow: hidden;
            margin-top: 15px;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            transition: width 1s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 0.9em;
        }
        @media print {
            body { background: white; padding: 0; }
            .container { box-shadow: none; }
            .actions { display: none; }
            .eval-item { break-inside: avoid; }
        }
        @media (max-width: 768px) {
            .header h1 { font-size: 2em; }
            .score-grid { grid-template-columns: 1fr; }
            .content { padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛍️ EcoMarket</h1>
            <p>Sistema de Evaluación Automática - Laravel 12</p>
            <p style="font-size: 0.9em; margin-top: 10px; opacity: 0.85;">
                Mini-Retos 1-20 (Obligatorios) + Extras 21-31 (Bonus)
            </p>
        </div>

        <div class="score-section">
            <div class="score-grid">
                <div class="score-card base">
                    <div class="score-label">📚 Obligatorios (MR 1-20)</div>
                    <div class="score-value"><?php echo $score['base']; ?>/<?php echo $score['maxBase']; ?></div>
                    <div class="score-subtext"><?php echo $score['percentage']; ?>% completado</div>
                </div>

                <div class="score-card bonus">
                    <div class="score-label">⭐ Extras/Bonus (MR 21+)</div>
                    <div class="score-value"><?php echo $score['bonus']; ?>/<?php echo $score['maxBonus']; ?></div>
                    <div class="score-subtext">Puntos adicionales</div>
                </div>

                <div class="score-card total">
                    <div class="score-label">🎯 Puntuación Total</div>
                    <div class="score-value"><?php echo $score['total']; ?>/<?php echo $score['maxTotal']; ?></div>
                    <div class="score-subtext"><?php echo $score['percentageWithBonus']; ?>% global</div>
                </div>

                <div class="score-card grade">
                    <div class="score-label">📝 Calificación</div>
                    <div style="margin-top: 10px;">
                        <span class="grade-badge <?php echo $grade['class']; ?>">
                            <?php echo $grade['text']; ?>
                        </span>
                    </div>
                    <div class="score-subtext" style="margin-top: 10px;">
                        <?php echo $grade['number']; ?> sobre 10
                    </div>
                </div>
            </div>

            <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo $score['percentage']; ?>%;">
                    <?php echo $score['percentage']; ?>%
                </div>
            </div>

            <div class="stats-bar">
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $stats['obligatorios_completados']; ?>/<?php echo $stats['obligatorios_totales']; ?></div>
                        <div class="stat-label">Mini-Retos Obligatorios</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $stats['extras_completados']; ?>/<?php echo $stats['extras_totales']; ?></div>
                        <div class="stat-label">Extras Completados</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo count($stats['criticos_fallidos']); ?></div>
                        <div class="stat-label">Problemas Críticos</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <?php if (count($stats['criticos_fallidos']) > 0): ?>
                <div class="critical-alert">
                    <h3>⚠️ ERRORES CRÍTICOS DETECTADOS (<?php echo count($stats['criticos_fallidos']); ?>)</h3>
                    <p><strong>Los errores críticos limitan tu calificación máxima:</strong></p>
                    <ul style="margin: 15px 0; padding-left: 20px;">
                        <li><strong>3+ errores críticos</strong> → Calificación máxima: INSUFICIENTE (&lt;5) - suspenso automático</li>
                        <li><strong>2 errores críticos</strong> → Calificación máxima: BIEN (6.0-6.9)</li>
                        <li><strong>1 error crítico</strong> → Calificación máxima: NOTABLE (7.0-7.9)</li>
                        <li><strong>0 errores críticos</strong> → Sin limitación (hasta Matrícula de Honor - 10)</li>
                    </ul>
                    <p style="margin-top: 15px;"><strong>Problemas críticos detectados:</strong></p>
                    <ul style="margin: 10px 0; padding-left: 20px;">
                        <?php foreach ($stats['criticos_fallidos'] as $critical): ?>
                            <li><?php echo $critical; ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <p style="margin-top: 15px; padding: 15px; background: rgba(255,255,255,0.9); border-radius: 8px;">
                        💡 <strong>Importante:</strong> Debes resolver TODOS los errores críticos para optar a las calificaciones más altas (Notable, Sobresaliente, Matrícula de Honor).
                    </p>
                </div>
            <?php elseif ($score['percentage'] >= 90): ?>
                <div class="critical-alert" style="background: #d1fae5; border-color: #10b981;">
                    <h3 style="color: #065f46;">✅ ¡Excelente trabajo!</h3>
                    <p style="color: #065f46;">Tu proyecto está muy bien configurado y listo para la evaluación. ¡Felicidades!</p>
                </div>
            <?php elseif ($score['percentage'] >= 70): ?>
                <div class="critical-alert" style="background: #fef3c7; border-color: #f59e0b;">
                    <h3 style="color: #92400e;">⚠️ Casi listo</h3>
                    <p style="color: #92400e;">Tu proyecto está bien encaminado. Revisa las advertencias abajo para mejorarlo.</p>
                </div>
            <?php else: ?>
                <div class="critical-alert">
                    <h3>❌ Atención Requerida</h3>
                    <p>Tu proyecto tiene problemas importantes que deben solucionarse antes de la evaluación.</p>
                    <?php if (count($stats['criticos_fallidos']) > 0): ?>
                        <p style="margin-top: 10px;"><strong>Problemas críticos:</strong></p>
                        <ul>
                            <?php foreach ($stats['criticos_fallidos'] as $critical): ?>
                                <li><?php echo $critical; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <h2 style="font-size: 2em; margin-bottom: 30px; color: #1f2937;">📚 EVALUACIÓN DETALLADA</h2>

            <?php foreach ($byCategory as $category => $items): ?>
                <?php
                    $isExtra = strpos($category, 'EXTRA') !== false;
                    $categoryClass = $isExtra ? 'extra' : '';
                ?>
                <div class="category-section">
                    <div class="category-header <?php echo $categoryClass; ?>">
                        <?php echo $category; ?>
                    </div>

                    <?php foreach ($items as $item): ?>
                        <div class="eval-item <?php echo $item['passed'] ? 'passed' : 'failed'; ?>
                                              <?php echo $item['isCritical'] ? 'critical' : ''; ?>
                                              <?php echo $item['isBonus'] ? 'bonus' : ''; ?>">
                            <div class="eval-icon"><?php echo $item['passed'] ? '✅' : '❌'; ?></div>
                            <div class="eval-content">
                                <div class="eval-header">
                                    <div class="eval-name">
                                        <?php echo $item['name']; ?>
                                        <?php if ($item['isCritical'] && !$item['passed']): ?>
                                            <span style="color: #ef4444; font-size: 0.9em;"> (CRÍTICO)</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="eval-points <?php echo $item['isBonus'] ? 'bonus' : ''; ?>">
                                        <?php echo $item['earned']; ?>/<?php echo $item['maxPoints']; ?> pts
                                    </div>
                                </div>
                                <div class="eval-message"><?php echo $item['message']; ?></div>
                                <?php if (!$item['passed'] && !empty($item['hint'])): ?>
                                    <div class="eval-hint"><strong>💡 Solución:</strong> <?php echo $item['hint']; ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>

            <div class="info-section">
                <h2 style="color: #1f2937; margin-bottom: 15px;">ℹ️ Información del Sistema</h2>
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
                <a href="/register" class="btn btn-primary">👤 Registrar Usuario</a>
                <button onclick="window.print()" class="btn btn-secondary">🖨️ Imprimir Reporte</button>
                <button onclick="location.reload()" class="btn btn-secondary">🔄 Actualizar Evaluación</button>
            </div>
        </div>

        <div class="footer">
            <p><strong>🛍️ EcoMarket</strong> - Sistema de Evaluación Automática</p>
            <p>Compatible con Laravel 12, Breeze y Form Requests</p>
            <p style="margin-top: 15px; font-size: 0.9em;">
                Mini-Retos 1-20 (Obligatorios: 100 pts) + Extras 21-31 (Bonus: 30 pts) = Total: 130 pts
            </p>
            <p style="margin-top: 10px; font-size: 0.85em; opacity: 0.7;">
                ⚠️ Este archivo es solo para evaluación. Elimínalo antes de producción.
            </p>
            <p style="margin-top: 5px; font-size: 0.85em; opacity: 0.7;">
                Versión 3.0 - Diciembre 2024
            </p>
        </div>
    </div>
</body>
</html>
