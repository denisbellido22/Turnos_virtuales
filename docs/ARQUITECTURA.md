# Documentación técnica

## 1. Visión general

La aplicación sigue el patrón MVC de Laravel con una capa de servicios para las reglas de cola y estadísticas:

- **Modelos Eloquent** para el dominio y las relaciones.
- **FormRequests** para validación y autorización de entrada.
- **Servicios** (`TurnoService`, `EstadisticaService`) para transacciones, bloqueos y métricas.
- **Eventos broadcast** para propagar cambios por WebSocket.
- **Controladores delgados** que orquestan servicios y vistas Blade.

```
Ciudadano ──POST /mis-turnos──> TurnoService::crear ──> turnos + historial
                                                   └──> TurnoCreado (broadcast)
Empleado  ──POST /atencion/turnos/llamar──> TurnoService::llamarSiguiente
                                                   ├──> turnos + historial
                                                   └──> TurnoLlamado (broadcast)
Pantalla pública / panel / seguimiento <── Laravel Echo <── Reverb <── eventos
```

## 2. Modelo de datos

### Relaciones

```
roles 1 ─── * users
areas 1 ─── * servicios
areas 1 ─── * ventanillas
areas 1 ─── * users (personal asignado)
areas 1 ─── * turnos
users 1 ─── 1 ciudadanos
users 1 ─── * turnos (empleado que atendió)
servicios 1 ─── * turnos
ventanillas 1 ─── * turnos
ciudadanos 1 ─── * turnos
turnos 1 ─── * historial_turnos
users 1 ─── * historial_turnos
ventanillas 1 ─── * historial_turnos
```

### Diccionario de tablas

#### `roles`
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| nombre | varchar(50) | único: `administrador`, `empleado`, `ciudadano` |
| descripcion | varchar(150) | opcional |

#### `users`
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| rol_id | FK roles | `nullOnDelete` |
| area_id | FK areas | opcional, para empleados |
| ventanilla_id | FK ventanillas | opcional, para empleados |
| name, email | varchar | email único |
| password | varchar | hash |
| activo | boolean | índice junto a `rol_id` |

#### `ciudadanos`
| Campo | Tipo | Notas |
|---|---|---|
| user_id | FK users | único, `cascadeOnDelete` |
| documento | varchar(30) | único |
| telefono, direccion | varchar | opcionales |

#### `areas`
`nombre` único, `descripcion`, `activo` (índice).

#### `servicios`
| Campo | Tipo | Notas |
|---|---|---|
| area_id | FK areas | `restrictOnDelete` |
| nombre | varchar(100) | único por área |
| prefijo | varchar(5) | único global; forma el código del turno (INF-001) |
| activo | boolean | índice `(area_id, activo)` |

#### `ventanillas`
`area_id` FK, `nombre` único por área, `codigo` único global, `activo`.

#### `turnos`
| Campo | Tipo | Notas |
|---|---|---|
| codigo | varchar(20) | prefijo + consecutivo, ej. `INF-001` |
| numero | unsigned int | consecutivo por servicio y fecha |
| fecha | date | día de la cola |
| servicio_id, area_id, ciudadano_id | FK | `restrictOnDelete` |
| ventanilla_id, user_id | FK nullable | asignados al llamar/atender |
| estado | varchar(20) | `pendiente`, `llamando`, `atendido`, `ausente`, `cancelado` |
| llamado_at | timestamp | primer/último llamado |
| atendido_at, finalizado_at | timestamp | cierres |
| created_at / updated_at | timestamps | orden FIFO por `created_at`, `id` |

Restricciones e índices:
- `unique(fecha, codigo)` evita códigos repetidos en el día.
- `unique(servicio_id, fecha, numero)` garantiza numeración consecutiva.
- `(fecha, estado)`, `(area_id, fecha, estado, created_at)`, `(ciudadano_id, fecha, estado)`, `(ventanilla_id, estado)` y `(fecha, estado, llamado_at)`.

#### `historial_turnos`
Registro append-only de transiciones: `turno_id`, `user_id`, `ventanilla_id`, `estado_anterior`, `estado_nuevo`, `accion`, `observacion`, `created_at`. Acciones: `crear`, `llamar`, `repetir_llamado`, `atender`, `ausente`, `cancelar`.

## 3. Máquina de estados

```
                 crear
                   │
                   ▼
              ┌──────────┐   llamar    ┌───────────┐   atender   ┌──────────┐
              │pendiente │ ──────────> │ llamando  │ ──────────> │ atendido │
              └──────────┘             └───────────┘             └──────────┘
                   │                        │
                   │ cancelar               │ ausente
                   ▼                        ▼
              ┌──────────┐             ┌──────────┐
              │cancelado │             │ ausente  │
              └──────────┘             └──────────┘
```

- Solo se puede llamar un turno `pendiente` del área de la ventanilla del empleado.
- No se permiten dos llamados activos en la misma ventanilla: primero debe atenderse o marcarse ausente.
- `repetir_llamado` mantiene el estado `llamando` y actualiza `llamado_at`.
- Cancelar aplica a turnos `pendiente` del área del empleado.
- Cada transición crea un registro en `historial_turnos`.

## 4. Generación consecutiva y concurrencia

`TurnoService::crear` ejecuta todo dentro de `DB::transaction`:

1. Bloquea la fila del ciudadano (`lockForUpdate`).
2. Bloquea la fila del servicio; esto serializa solicitudes concurrentes del mismo servicio.
3. Rechaza si el ciudadano ya tiene un turno `pendiente` o `llamando` hoy.
4. Calcula `max(numero) + 1` para el servicio y fecha.
5. Inserta el turno y su historial.
6. Al confirmar, emite `TurnoCreado`.

Las restricciones únicas de la base de datos actúan como última barrera ante condiciones de carrera.

`TurnoService::llamarSiguiente` bloquea la ventanilla y el área antes de seleccionar el turno más antiguo (`orderBy created_at, id`, `lockForUpdate`). Así dos ventanillas del mismo área nunca reciben el mismo turno y una ventanilla no puede tener dos llamados simultáneos.

## 5. Eventos y WebSockets

Los tres eventos implementan `ShouldBroadcastNow` y se publican en el canal público `turnos`:

| Evento | Nombre | Payload |
|---|---|---|
| `TurnoCreado` | `turno.creado` | `turno` con código, estado, servicio, área, ventanilla y hora de llamado |
| `TurnoLlamado` | `turno.llamado` | igual, con ventanilla asignada |
| `TurnoActualizado` | `turno.actualizado` | estado final y ventanilla |

El payload usa `Turno::resumen()` y excluye deliberadamente cualquier dato del ciudadano.

Suscriptores:
- **Pantalla pública:** `turno.llamado` actualiza el número actual, ventanilla e historial; también sincroniza contadores con `/pantalla/datos`.
- **Panel de atención:** refresca el fragmento `atencion/_cola` al recibir cualquier evento.
- **Seguimiento del ciudadano:** consulta `GET /mis-turnos/{turno}/estado` (protegido y limitado al dueño) y actualiza estado, posición y mensaje.

## 6. Roles y permisos

| Ruta | Middleware | Rol |
|---|---|---|
| `/mis-turnos/*` | `auth`, `activo`, `role:ciudadano` | ciudadano |
| `/atencion/*` | `auth`, `activo`, `role:empleado` | empleado |
| `/admin/*` | `auth`, `activo`, `role:administrador` | administrador |
| `/pantalla`, `/pantalla/datos` | público | visitantes |

Además del middleware, los controladores y servicios verifican:
- El ciudadano solo puede consultar turnos cuyo `ciudadano_id` coincide con su perfil.
- El empleado solo opera turnos de su ventanilla; cancelar exige que el turno pertenezca a su área.
- El administrador no puede desactivarse ni cambiar su propio rol.

## 7. Métricas

`EstadisticaService` calcula:
- Totales por estado.
- Tiempo promedio de espera (`llamado_at - created_at`) y de atención (`finalizado_at - llamado_at`), con expresión compatible con MySQL y SQLite.
- Agrupaciones por día, área y servicio.
- Efectividad = atendidos / total.

El dashboard usa los turnos de hoy; los reportes aceptan rango de fechas, área y servicio (máximo 366 días).

## 8. Decisiones y extensibilidad

- **Autenticación nativa** en lugar de Breeze: se implementó login, registro de ciudadanos, throttling, regeneración de sesión y middleware de roles sin arrastrar vistas Tailwind, manteniendo Bootstrap 5 como sistema visual único.
- **Eventos inmediatos**: `ShouldBroadcastNow` evita exigir un worker para la operación básica. Si se necesita desacoplar, se puede cambiar a `ShouldBroadcast` y encolar.
- **Un turno activo por ciudadano/día** simplifica la operación presencial. Si se requieren turnos para trámites simultáneos, la regla está centralizada en `TurnoService::crear`.
- **Posibles extensiones**: prioridad para grupos vulnerables, notificaciones SMS/correo, citas programadas, múltiples sedes, exportación CSV/PDF de reportes, autenticación con documento y contraseña, y políticas de autorización formales.

## 9. Archivos clave

| Responsabilidad | Archivo |
|---|---|
| Generación y transiciones de turnos | `app/Services/TurnoService.php` |
| Métricas y reportes | `app/Services/EstadisticaService.php` |
| Eventos WebSocket | `app/Events/Turno*.php` |
| Configuración de Reverb | `config/reverb.php`, `config/broadcasting.php`, `.env` |
| Cliente Echo | `resources/js/echo.js` |
| Pantalla en vivo | `resources/js/pages/pantalla.js`, `resources/views/pantalla/index.blade.php` |
| Panel de atención | `resources/js/pages/atencion.js`, `resources/views/atencion/*` |
| Seguimiento ciudadano | `resources/js/pages/turno.js`, `resources/views/ciudadano/turnos/show.blade.php` |
| Roles y cuentas activas | `app/Http/Middleware/*` |
| Esquema | `database/migrations/2026_11_12_*.php` |
