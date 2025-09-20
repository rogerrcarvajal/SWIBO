# SWIBO - Sistema Web para el Control de Inventario

![Logo](public/img/logo.PNG)

SWIBO es una aplicación web desarrollada en PHP puro y PostgreSQL, diseñada para ofrecer una solución completa y eficiente para la gestión de inventarios. Permite a las empresas llevar un control detallado de sus productos, categorías, usuarios y movimientos de stock (entradas y salidas), garantizando la trazabilidad a través de reportes y un kardex detallado.

## ✨ Características Principales

- **Autenticación Segura:** Sistema de login con contraseñas hasheadas para proteger el acceso.
- **Gestión de Usuarios:** Módulo para administrar los usuarios del sistema (CRUD), con un sistema de roles (administrador, operario).
- **Gestión de Catálogo:**
  - **Categorías:** Permite organizar los productos en distintas categorías.
  - **Productos:** Administración completa de productos, asociándolos a sus respectivas categorías.
- **Control de Inventario:**
  - **Entradas y Salidas:** Registro de todos los movimientos de stock, actualizando las cantidades disponibles en tiempo real.
- **Trazabilidad y Reportes:**
  - **Kardex de Producto:** Historial detallado de todos los movimientos de un producto específico.
  - **Reporte General:** Vista global del estado actual de todo el inventario.
- **Exportación a PDF:** Generación de documentos PDF para los reportes principales, facilitando su archivo y distribución.

## 🛠️ Tecnologías Utilizadas

- **Backend:** PHP 8+
- **Base de Datos:** PostgreSQL
- **Dependencias (incluidas en el repositorio):**
  - **FPDF:** para la generación de reportes en PDF.

## 🚀 Instalación y Puesta en Marcha

Sigue estos pasos para instalar y ejecutar el proyecto en tu entorno de desarrollo local.

### 1. Prerrequisitos

- Un servidor web local (Apache, Nginx, etc.).
- PHP 8 o superior.
- PostgreSQL.

### 2. Clonar el Repositorio

```bash
git clone https://github.com/rogerrcarvajal/SWIBO.git
cd SWIBO
```

### 3. Configurar la Base de Datos

1.  Abre una terminal de `psql` o una herramienta de gestión de bases de datos como pgAdmin.
2.  Crea una nueva base de datos. Por defecto, el sistema usa `swibo_db`.

    ```sql
    CREATE DATABASE swibo_db;
    ```

3.  Importa la estructura de las tablas y los datos iniciales desde el archivo `swibo_db.sql`.

    ```bash
    psql -U tu_usuario_postgres -d swibo_db -f PostgreSQL-DB/swibo_db.sql
    ```

### 4. Configurar la Conexión

1.  En la raíz del proyecto, crea un archivo llamado `db.php`.
2.  Copia y pega el siguiente contenido en el archivo, asegurándote de reemplazar los valores (`host`, `user`, `password`) con tus propias credenciales de PostgreSQL.

    ```php
    <?php
    // db.php
    $host = "localhost"; // O la IP de tu servidor de BD
    $db = "swibo_db";
    $user = "tu_usuario_postgres";
    $password = "tu_contraseña";

    try {
        $conn = new PDO("pgsql:host=$host;dbname=$db", $user, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        die("Error en la conexión a la base de datos: " . $e->getMessage());
    }
    ?>
    ```

### 5. Ejecutar la Aplicación

1.  Inicia tu servidor web y asegúrate de que apunte al directorio raíz del proyecto (`SWIBO`).
2.  Abre tu navegador y accede a la URL correspondiente (ej. `http://localhost/SWIBO/`).

##  usage Uso del Sistema

- **Acceso:** Puedes ingresar con las credenciales de usuario que se encuentran en el archivo `swibo_db.sql` o crear un nuevo usuario.
- **Navegación:** Una vez dentro, el dashboard te dará acceso a todos los módulos del sistema.

## 📂 Estructura del Proyecto

```
/SWIBO
├── Funcionalidad/         # Documentación del análisis funcional
├── pages/                 # Páginas y módulos principales de la aplicación
├── PostgreSQL-DB/         # Scripts de la base de datos
├── public/                # Archivos públicos (CSS, imágenes)
├── src/                   # Código fuente de soporte (protector, librerías)
├── db.php                 # Archivo de configuración de la BD (local)
├── index.php              # Página de login
└── README.md              # Este archivo
```
