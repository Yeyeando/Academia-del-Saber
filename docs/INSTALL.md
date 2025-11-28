# EcoMarket – Guía de Instalación (INSTALL.md)

EcoMarket es una tienda online de ejemplo desarrollada en **Laravel 12** para practicar **PHP, POO y el patrón MVC** en un entorno realista.

---

## 1. Requisitos previos

Antes de instalar el proyecto asegúrate de tener:

- **PHP** ≥ 8.2  
- **Composer** 2.x  
- **MySQL / MariaDB** (u otro motor compatible)  
- **Node.js** + **npm** (para compilar los assets front-end, si se usan)  
- Extensiones PHP típicas activadas: `pdo`, `mbstring`, `openssl`, `tokenizer`, `json`, `xml`, etc.

Opcional (pero recomendado):

- **Git** para clonar el repositorio.
- Un servidor local tipo **XAMPP**, **Laragon**, **MAMP**, o similar.

---

## 2. Clonado del proyecto (Quick Start)

```bash
git clone https://github.com/usuario/ecomarket.git
cd ecomarket
```

> Si no usas Git, también puedes descargar el proyecto como ZIP y descomprimirlo en tu carpeta de trabajo (por ejemplo `C:\xampp\htdocs\ecomarket`).

---

## 3. Instalación de dependencias

Instala las dependencias de PHP con Composer:

```bash
composer install
```

Instala las dependencias de Node (si vas a usar assets con Vite):

```bash
npm install
```

---

## 4. Configuración del archivo `.env`

Copia el archivo de ejemplo y genera la clave de la aplicación:

```bash
cp .env.example .env
php artisan key:generate
```

Edita el archivo `.env` y ajusta, como mínimo:

```env
APP_NAME="EcoMarket"
APP_ENV=local
APP_KEY=    # (se rellena al ejecutar php artisan key:generate)
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecomarket
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

Asegúrate de que la base de datos `ecomarket` exista (puedes crearla desde **phpMyAdmin** o desde consola):

```sql
CREATE DATABASE ecomarket CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Si el proyecto utiliza servicios externos (correo, APIs, etc.), configura también las variables correspondientes en el `.env`.

---

## 5. Migraciones y datos de ejemplo

Ejecuta las migraciones (y, si existen, los seeders) para crear las tablas necesarias:

```bash
php artisan migrate
# o, si hay seeders configurados
php artisan migrate --seed
```

Esto creará las tablas de productos, usuarios y demás entidades que use EcoMarket.

---

## 6. Compilación de assets (CSS/JS)

Si el proyecto incluye assets gestionados con Vite:

- Durante el desarrollo:

```bash
npm run dev
```

- Para generar los ficheros optimizados de producción:

```bash
npm run build
```

---

## 7. Arranque del proyecto en local

### Opción A: servidor integrado de Laravel

```bash
php artisan serve
```

Por defecto la aplicación estará disponible en:

- `http://localhost:8000`

### Opción B: servidor Apache / Nginx

Configura tu host virtual para que el **DocumentRoot** (o `root`) apunte a la carpeta:

- `public/` del proyecto (por ejemplo, `C:\xampp\htdocs\ecomarket\public`)

Y asegúrate de que la URL apunte a ese directorio.

---

## 8. Usuarios de prueba (si aplica)

Si el proyecto incluye usuarios de ejemplo (seeders), podrás iniciar sesión con credenciales como:

```text
Email: admin@ecomarket.test
Password: password
```

> Ajusta estos datos a los que realmente configures en tus seeders.

---

## 9. Mini-reto inicial: crear tu primer producto

Una vez que la aplicación esté funcionando en `http://localhost:8000`:

1. Accede a la sección **Productos** (por ejemplo `http://localhost:8000/productos`).  
2. Haz clic en **“Crear primer producto”** o similar.  
3. Rellena los campos básicos:
   - Nombre
   - Precio
   - Stock
4. Guarda el formulario.
5. Comprueba que el producto aparece en la lista y que se ha guardado en la base de datos.

Este mini-reto sirve para comprobar que:
- Las rutas funcionan.
- El controlador recibe la petición.
- El modelo `Producto` guarda datos en la base de datos.
- La vista muestra la información correctamente.

---

## 10. Ejecución de tests (opcional)

Si el proyecto incluye tests automatizados, puedes ejecutarlos con:

```bash
php artisan test
# o
phpunit
```

---

## 11. Problemas frecuentes (FAQ rápida)

**Error de conexión a la base de datos**  
- Revisa `DB_HOST`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` en el `.env`.  
- Comprueba que MySQL/MariaDB está en ejecución.

**`APP_KEY` vacío o error de encriptación**  
- Ejecuta de nuevo:  
  ```bash
  php artisan key:generate
  ```

**Los estilos o el JS no se cargan**  
- Asegúrate de haber ejecutado `npm install` y `npm run dev` o `npm run build`.  
- Verifica que estás accediendo a la app mediante la URL definida en `APP_URL`.

---

Si sigues estos pasos, deberías tener **EcoMarket** funcionando en tu entorno local y listo para usar en tus clases de POO y MVC con PHP.
