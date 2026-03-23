🏢 CondoPro - Sistema de Gestión de Condominios
CondoPro es una aplicación web diseñada para digitalizar la administración de condominios, enfocándose inicialmente en el control técnico y el mantenimiento de infraestructura.

🚀 Funcionalidades Actuales
👷 Gestión de Proveedores (CRUD Completo)
Registro y Edición: Panel centralizado para dar de alta técnicos y empresas de servicios.

Relación N:M: Soporte para múltiples especialidades por proveedor mediante tablas intermedias.

Borrado Lógico: Sistema de "desactivación" para mantener la integridad histórica de los datos sin eliminar registros de la base de datos.

Interfaz Moderna: Uso de botones degradados y confirmaciones dinámicas con SweetAlert2.

🏷️ Maestro de Categorías
Administración de especialidades (Ascensores, Bombas, Electricidad, etc.).

Controlador maestro único para operaciones de guardado y eliminación.

🛠️ Detalles Técnicos
Arquitectura: PHP Nativo con una estructura modular de carpetas (includes/, modulos/, assets/).

Seguridad: Consultas preparadas con PDO para prevenir inyecciones SQL.

Base de Datos: Relacional (MySQL) con soporte para transacciones en procesos críticos de actualización.

📂 Estructura del Proyecto
Bash
/
├── includes/           # Conexión a BD, menús y componentes reutilizables
│   └── modals/         # Modales de Bootstrap (Proveedores, Categorías)
├── modulos/            # Lógica de procesamiento PHP (Controladores)
├── assets/             # CSS personalizado y librerías JS
└── gestion_proveedores.php  # Vista principal del directorio técnico
🛠️ Instalación
Clona el repositorio:

Bash
git clone https://github.com/tu-usuario/condopro.git
Importa el archivo SQL (disponible en /db) a tu servidor MySQL.

Configura tus credenciales de acceso en includes/conexion.php.

¡Listo! Ejecuta en tu servidor local (XAMPP/WAMP).

📅 Próximamente...
Módulo de Mantenciones: Calendario preventivo y alertas de vencimiento.

Gestión de Pagos: Control de facturación de proveedores.

Panel de Administración: Estadísticas de gastos por torre o comunidad.

Desarrollado con ❤️ STI + IA para mejorar la convivencia y eficiencia en comunidades.
