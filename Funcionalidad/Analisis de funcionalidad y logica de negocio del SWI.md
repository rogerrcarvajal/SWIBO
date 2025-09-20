# Análisis de Funcionalidad y Lógica de Negocio: SWIBO

## 1. Resumen General

**SWIBO (Sistema Web para el Control de Inventario)** es una aplicación web diseñada para gestionar y controlar el inventario de productos de una empresa. El sistema permite llevar un registro detallado de productos, categorizarlos y monitorear todas las entradas y salidas de stock, proporcionando trazabilidad a través de reportes y un kardex detallado.

La arquitectura es la de una aplicación PHP tradicional, con una separación de la lógica en diferentes páginas y un módulo central de conexión a la base de datos PostgreSQL.

## 2. Lógica de Negocio y Módulos Principales

El sistema se organiza en los siguientes módulos funcionales:

### 2.1. Autenticación y Control de Acceso
- **Login:** La entrada al sistema es a través de `index.php`, que valida las credenciales (usuario y contraseña) contra la tabla `usuarios` en la base de datos.
- **Seguridad de Contraseña:** El sistema utiliza `password_verify()`, lo que implica que las contraseñas en la base de datos están hasheadas de forma segura.
- **Gestión de Sesión:** Una vez validado, el sistema crea una sesión (`$_SESSION`) que almacena la identidad del usuario (`usuario_id`, `nombre`, `rol`).
- **Protección de Rutas:** El script `src/protector.php` se incluye en todas las páginas internas para asegurar que solo los usuarios autenticados puedan acceder a ellas. Si no hay una sesión activa, redirige automáticamente al login.

### 2.2. Gestión de Entidades (CRUD)
El sistema permite realizar operaciones de Crear, Leer, Actualizar y Eliminar (CRUD) sobre las siguientes entidades principales:

- **Gestión de Usuarios (`gestion_usuarios.php`):**
  - Permite listar, crear, editar y eliminar usuarios del sistema.
  - Esta es una funcionalidad **administrativa**, probablemente restringida a usuarios con un rol de "administrador".

- **Gestión de Categorías (`gestion_categorias.php`):**
  - Permite organizar los productos en diferentes categorías (ej. "Insumos de Limpieza", "Materia Prima", etc.).
  - Facilita la búsqueda y el filtrado de productos.

- **Gestión de Productos (`gestion_productos.php`):**
  - Corazón del sistema. Permite dar de alta nuevos productos, editar su información (nombre, descripción, etc.) y asociarlos a una categoría.
  - Muestra el stock actual de cada producto.

### 2.3. Control de Inventario (Movimientos)
Esta es la funcionalidad central para el control de stock:

- **Entrada de Productos (`entrada_producto.php`):**
  - Permite registrar el ingreso de stock para un producto existente.
  - Aumenta la cantidad en el campo `stock` de la tabla de productos.
  - Probablemente genera un registro en una tabla de `movimientos` para trazabilidad.

- **Salida de Productos (`salida_producto.php`):**
  - Permite registrar la salida o consumo de stock.
  - Disminuye la cantidad en el campo `stock`.
  - Al igual que las entradas, genera un registro de movimiento.

### 2.4. Reportes y Trazabilidad
El sistema ofrece herramientas clave para el análisis del inventario:

- **Kardex de Producto (`kardex_producto.php`):**
  - Ofrece una vista detallada de **todos los movimientos** (entradas y salidas) de un producto específico a lo largo del tiempo.
  - Es la herramienta fundamental para la auditoría y trazabilidad del inventario.

- **Reporte General (`reporte_general.php`):**
  - Muestra un resumen global del estado del inventario, listando todos los productos con su stock actual y posiblemente su valor.

- **Exportación a PDF:**
  - Los reportes (Kardex y General) se pueden exportar a formato PDF (`generar_pdf_kardex.php`, `generar_pdf_reporte_general.php`), utilizando la librería FPDF. Esto facilita su almacenamiento, impresión y distribución.

### 2.5. Dashboard (`dashboard.php`)
- Es la página principal a la que se accede después del login.
- Actúa como un panel de control central, proporcionando acceso rápido a los diferentes módulos y, probablemente, mostrando estadísticas clave como el número total de productos, categorías o los últimos movimientos registrados.

## 3. Arquitectura y Aspectos Técnicos

- **Lenguaje:** PHP.
- **Base de Datos:** PostgreSQL.
- **Conexión a BD:** Se utiliza la extensión PDO de PHP, con sentencias preparadas para evitar inyecciones SQL.
- **Estructura:** El proyecto separa las páginas visibles al usuario (`pages/`), los recursos públicos (`public/`) y el código fuente de soporte (`src/`).
- **Interfaz de Usuario:** HTML y CSS estándar, sin un framework de frontend aparente.

## 4. Conclusión

SWIBO es un sistema de inventario robusto y funcional. Su lógica de negocio cubre los aspectos esenciales del control de stock: gestión de catálogo, registro de movimientos y trazabilidad a través de reportes. La implementación de medidas de seguridad como el hasheo de contraseñas y el uso de sentencias preparadas demuestra buenas prácticas de desarrollo.
