# Turnos Virtuales

Sistema web de turnos virtuales y gestión de colas en tiempo real para instituciones públicas o privadas. Permite a los ciudadanos solicitar un turno, seguir su posición y estado en vivo, y permite al personal llamar, repetir, atender, cancelar o marcar ausencias desde su ventanilla. La pantalla pública muestra el llamado actual y el historial sin recargar la página.

**Stack:** Laravel 13 · PHP 8.3+ · MySQL/MariaDB · Bootstrap 5 · Vite · Laravel Reverb · Laravel Echo · WebSockets.

---

## 1. Funcionalidades

### Administración
- Inicio de sesión con control de acceso por rol.
- Dashboard con turnos del día, cola por área, tiempos promedio y actividad reciente.
- Gestión de usuarios administradores y empleados, con asignación de área y ventanilla.
- CRUD de áreas, servicios (con prefijo de turno) y ventanillas.
- Activación/desactivación de cuentas y catálogos sin perder el historial.
- Reportes por rango de fechas, área y servicio, con métricas de efectividad y tiempos.

### Ciudadanos
- Registro e inicio de sesión.
- Selección de servicio agrupado por área.
- Generación automática de turno único y consecutivo por servicio y día.
- Consulta de posición en la cola.
- Seguimiento del estado en tiempo real, sin recargar la página.

### Atención
- Panel del empleado con su ventanilla y área.
- Llamado del siguiente turno en orden de llegada.
- Repetición del llamado.
- Marcado como atendido, ausente o cancelado.
- Cola del área y estadísticas del puesto, actualizadas por WebSocket.

### Pantalla pública
- Turno llamado y ventanilla en tipografía grande.
- Historial de los últimos llamados del día.
- Actualización automática por WebSockets.
- Reloj y estado de la conexión en vivo.

---

## 2. Requisitos

- PHP **8.3 o superior** con extensiones `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `tokenizer`, `xml`, `ctype` y **`curl`**.
- Composer 2.
- MySQL 8 o MariaDB 10.4+.
- Node.js 20+ y npm.
- Puertos libres: `8000` (aplicación) y `8080` (Reverb) en desarrollo.

> **Importante:** Laravel Reverb requiere `ext-curl` para publicar eventos. Si `php -m` no muestra `curl`, habilita `extension=curl` en tu `php.ini` y reinicia la terminal. En XAMPP la línea suele estar comentada como `;extension=curl`.

---

## 3. Instalación

```bash
# 1. Dependencias PHP
composer install

# 2. Configuración
cp .env.example .env
php artisan key:generate

# 3. Base de datos (MariaDB/MySQL)
mysql -u root -e "CREATE DATABASE turnos_virtuales CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Edita `.env` con tus credenciales:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=turnos_virtuales
DB_USERNAME=root
DB_PASSWORD=
```

Ajusta `APP_TIMEZONE` al huso horario de la institución (el ejemplo usa `America/La_Paz`). La fecha de los turnos y los cortes diarios se calculan con esa zona horaria.

Configura Reverb (los valores de `.env.example` son de desarrollo; cámbialos en producción):

```dotenv
BROADCAST_CONNECTION=reverb
QUEUE_CONNECTION=sync

REVERB_APP_ID=local
REVERB_APP_KEY=local-key
REVERB_APP_SECRET=local-secret
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

Si necesitas regenerar credenciales de Reverb:

```bash
php artisan reverb:install
```

Luego ejecuta migraciones, datos de prueba y compila los assets:

```bash
php artisan migrate:fresh --seed
npm install
npm run build
```

> `migrate:fresh` elimina las tablas existentes. En una base con datos usa `php artisan migrate --seed` la primera vez.

---

## 4. Ejecución

En tres terminales (la tercera solo si vas a modificar CSS/JS):

```bash
# Terminal 1: aplicación HTTP
php artisan serve

# Terminal 2: servidor WebSocket Reverb
php artisan reverb:start

# Terminal 3 (opcional): Vite en modo desarrollo
npm run dev
```

- Aplicación: <http://localhost:8000>
- Pantalla pública: <http://localhost:8000/pantalla>
- WebSocket: `ws://localhost:8080`

La pantalla pública y los paneles usan el canal público `turnos`. Los datos publicados incluyen únicamente número de turno, servicio, área y ventanilla; **nunca** nombre, documento ni otros datos personales.

---

## 5. Usuarios de prueba

| Rol | Correo | Contraseña |
|---|---|---|
| Administrador | `admin@turnos.test` | `Admin123!` |
| Empleado (V-01) | `maria.lopez@turnos.test` | `Empleado123!` |
| Empleado (V-04) | `carlos.ramirez@turnos.test` | `Empleado123!` |
| Empleado (V-06) | `ana.flores@turnos.test` | `Empleado123!` |
| Ciudadano | `ciudadano@turnos.test` | `Ciudadano123!` |

El seeder también crea áreas, servicios, ventanillas y turnos de ejemplo para hoy, incluyendo un turno en llamado y turnos atendidos.

---

## 6. Estructura del proyecto

```
app/
  Events/                 Eventos broadcast (TurnoCreado, TurnoLlamado, TurnoActualizado)
  Http/Controllers/       Auth, Admin, Ciudadano, Atencion, Pantalla, Home
  Http/Middleware/        EnsureUserHasRole, EnsureUserIsActive
  Http/Requests/          Validación de formularios y reglas de negocio
  Models/                 Rol, User, Ciudadano, Area, Servicio, Ventanilla, Turno, HistorialTurno
  Services/               TurnoService (colas/transacciones), EstadisticaService (métricas)
database/
  migrations/             Esquema normalizado con claves, índices y restricciones
  seeders/                Roles, catálogo, usuarios y turnos de demostración
  factories/              Datos para pruebas
resources/
  css/app.css             Tema institucional sobre Bootstrap 5
  js/                     Eco/Reverb y scripts de pantalla, atención, turno y usuarios
  views/                  Layouts, componentes y vistas por módulo
routes/web.php            Rutas web con middleware de autenticación y roles
docs/ARQUITECTURA.md      Modelo de datos, estados y flujos técnicos
```

---

## 7. Base de datos

Tablas principales:

| Tabla | Propósito | Restricciones relevantes |
|---|---|---|
| `roles` | Administrador, empleado y ciudadano | `nombre` único |
| `users` | Cuentas internas y de ciudadanos | `email` único; FK a rol, área y ventanilla |
| `ciudadanos` | Perfil del ciudadano | `user_id` y `documento` únicos |
| `areas` | Unidades de atención | `nombre` único |
| `servicios` | Trámites por área | `prefijo` único; nombre único por área |
| `ventanillas` | Puestos de atención | `codigo` único; nombre único por área |
| `turnos` | Turnos del día | `(fecha, codigo)` y `(servicio_id, fecha, numero)` únicos |
| `historial_turnos` | Auditoría de cada transición | FK a turno, usuario y ventanilla |

Índices adicionales en `(fecha, estado)`, `(area_id, fecha, estado, created_at)`, `(ciudadano_id, fecha, estado)` y `(ventanilla_id, estado)` para las consultas de cola, seguimiento y pantalla.

El detalle completo, el diagrama de relaciones y la máquina de estados están en [`docs/ARQUITECTURA.md`](docs/ARQUITECTURA.md).

---

## 8. Flujo en tiempo real

1. El ciudadano solicita un turno (`TurnoService::crear`).
2. Se guarda el turno y su historial dentro de una transacción; se emite `TurnoCreado`.
3. El empleado pulsa **Llamar siguiente**. La transacción bloquea el área, toma el turno pendiente más antiguo, asigna ventanilla y emite `TurnoLlamado`.
4. La pantalla pública escucha `turno.llamado` y actualiza el número y la ventanilla al instante; el panel del empleado refresca su cola.
5. Al marcar atendido, ausente o cancelado se emite `TurnoActualizado`; el ciudadano refresca su estado y posición consultando un endpoint JSON protegido por sesión.

Los eventos usan `ShouldBroadcastNow` para que la actualización llegue inmediatamente sin exigir un worker de colas. `QUEUE_CONNECTION=sync` es suficiente para esta configuración; si más adelante se encolan eventos, configura un worker.

---

## 9. Seguridad y calidad

- Autenticación por sesión con regeneración de sesión, rotación de token CSRF y cierre de sesión seguro.
- Límite de intentos de inicio de sesión (5 intentos / 60 s) con bloqueo temporal.
- Contraseñas hasheadas mediante el cast `hashed` de Laravel.
- Middleware `auth`, `activo` y `role:*` en todos los paneles; verificación adicional de pertenencia del turno a la ventanilla/ciudadano.
- Validación en `FormRequest` y reglas de unicidad a nivel de base de datos.
- Transacciones y bloqueos (`lockForUpdate`) al generar y llamar turnos para evitar duplicados o llamados simultáneos del mismo ticket.
- Restricción de un turno activo por ciudadano y día, además de índices únicos.
- Historial de auditoría de cada transición y logging de operaciones relevantes.
- Payloads públicos sin información personal.
- Consultas con *eager loading* para evitar N+1 en tablas y paneles.

---

## 10. Pruebas

```bash
php artisan test
```

La suite usa SQLite en memoria y cubre:

- Registro de ciudadanos, login, cuentas inactivas y separación de roles.
- Generación consecutiva de turnos, historial y bloqueo de duplicados.
- Llamado, repetición, atención y protección entre ventanillas.
- Pantalla pública y ausencia de datos personales en el payload.
- Alta de áreas/empleados, validación de área-ventanilla y renderizado de las vistas principales.

Para verificar solo la sintaxis de PHP:

```bash
find app database routes -name '*.php' -print0 | xargs -0 -n1 php -l
```

---

## 11. Producción

1. Configura `APP_ENV=production`, `APP_DEBUG=false` y una `APP_URL` con HTTPS.
2. Genera claves de Reverb propias y sirve WebSockets detrás de un proxy inverso (Nginx) con TLS. Usa `REVERB_SCHEME=https` y reconstruye los assets con `npm run build`.
3. Ejecuta `php artisan migrate --force` y `php artisan optimize`.
4. Mantén Reverb supervisado por un gestor de procesos (Supervisor, systemd, etc.).
5. Configura backups de MySQL y retención del historial.
6. Cambia las credenciales de demostración o elimina los usuarios de prueba.

---

## 12. Solución de problemas

**La pantalla no se actualiza**
- Verifica que `php artisan reverb:start` esté corriendo y que el puerto `8080` esté libre.
- Revisa que `BROADCAST_CONNECTION=reverb` y que `php -m` incluya `curl`.
- Si cambiaste variables `VITE_REVERB_*`, vuelve a ejecutar `npm run build`.

**Error de conexión a MySQL**
- Comprueba las credenciales de `.env` y que la base `turnos_virtuales` exista.
- Ejecuta `php artisan config:clear` después de editar `.env`.

**No hay servicios para solicitar**
- Ejecuta `php artisan db:seed` o crea áreas y servicios desde el panel de administración.
- Un servicio solo aparece si está activo y su área también lo está.

**Un empleado no puede llamar turnos**
- Debe tener un rol `empleado`, un área y una ventanilla activas asignadas desde **Usuarios y empleados**.
