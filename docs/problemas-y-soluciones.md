
## Error faltan archivos que deberían haberse descargado con el compiler
En la terminal en el proyecto de el que estamos, ejecutamos el comando 'composer install'

## Error: SQLSTATE[42S02]: Base table or view not found: 1146 Table '... not found
Este error generalmente ocurre cuando una tabla en la base de datos que se intenta acceder no existe. Es común después de haber creado o cambiado un modelo, pero no haber ejecutado las migraciones correspondientes para crear las tablas.

### Solución 
Ejecutar migraciones: Asegúrate de que todas las migraciones se hayan ejecutado correctamente. Usa el siguiente comando para correr las migraciones:

php artisan migrate


Revisar el nombre de la tabla: Verifica que el nombre de la tabla en tu base de datos coincida con el nombre que Laravel está buscando. Si no coinciden, puedes definir explícitamente el nombre de la tabla en el modelo:

class Curso extends Model
{
    protected $table = 'cursos';  // Asegúrate de que el nombre de la tabla sea correcto
}

## Error: Class 'App\Models\Model' not found o Class 'App\Models\NombreClase' not found

Este error suele ocurrir cuando Laravel no puede encontrar o cargar la clase del modelo que estás tratando de usar. Puede ser causado por un error de nombres, problemas con la declaración del espacio de nombres (namespace), o la ubicación incorrecta del archivo de la clase.

### Solución

Revisa el namespace: Asegúrate de que el namespace de tu modelo coincida con la estructura de directorios de Laravel. Por ejemplo, si tu archivo de modelo está en app/Models, debe declarar un namespace App\Models; al principio del archivo.

Uso correcto del modelo: Si tu modelo está en una subcarpeta dentro de app/Models, asegúrate de usar el espacio de nombres correctamente en el controlador o en cualquier otro archivo que lo use. Por ejemplo, si tienes app/Models/Cursos/Curso.php, deberías referenciarlo como use App\Models\Cursos\Curso;.