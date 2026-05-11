# CLAUDE.md — Contexto del Sistema SEDUC TICKET

> Este archivo debe estar en la raíz del proyecto `SISTEMA_TICKET/`.
> Claude Code lo leerá automáticamente en cada sesión.

---

## ¿Qué es este sistema?

Panel web desarrollado en **PHP + MySQL** para **SEDUC SPA** (corporación educacional que administra varios colegios en Chile). El sistema centraliza la gestión de tickets de soporte TI, inventario de equipos y herramientas, gestión de usuarios, y un panel de accesos personalizables por usuario.

- **URL producción:** `https://acceso.seduc.cl/`
- **URL QA/desarrollo:** `https://qa.seduc.cl/`
- **Base de datos:** `acceso_sistema_panel` (MariaDB 10.6, PHP 8.1)
- **Zona horaria:** `America/Santiago`

---

## Stack tecnológico

- **Backend:** PHP 8.1 (sin framework, estructura MVC manual)
- **Base de datos:** MariaDB / MySQL — clase de conexión propia `MySQL` en `class/`
- **Frontend:** Bootstrap 5.3, Bootstrap Icons, SweetAlert2, DataTables, Chart.js, Intro.js
- **Correos:** PHPMailer (en `class/PHPMailer/`)
- **Autenticación:** Sessions PHP (`$_SESSION`)
- **Cifrado:** AES-256-CBC, clave en `class/config.php`

---

## Estructura de carpetas

```
SISTEMA_TICKET/
├── index.php                   # Login principal
├── principal.php               # Dashboard principal (tickets del usuario)
├── ticket.php                  # Vista de un ticket individual
├── ticket_admin_v2.php         # Vista administrador de tickets
├── ticket_asignados.php        # Tickets asignados al técnico
├── configuracion.php           # Panel de configuración
├── estadistica.php             # Estadísticas y gráficos
├── inventario_index.php        # Inventario de equipos PC
├── chat.php / chatBox.php      # Chat interno
├── colaboradores.php           # Directorio de usuarios/colegios
├── perfil.php                  # Perfil de usuario
├── generadorQr.php             # Generación de QR
├── denunciaAcoso.php           # Módulo denuncias
│
├── class/
│   ├── funciones.php           # Clase principal Funciones (métodos globales, menú, header, etc.)
│   ├── personas.php            # Clase Personas
│   ├── config.php              # Constantes cifrado AES
│   ├── CursosCodigosQR.php     # Clase QR
│   └── correos/                # Plantillas HTML de correos por evento
│
├── modelos/
│   ├── guardar/                # INSERT - guardar datos nuevos
│   ├── editar/                 # UPDATE - modificar datos
│   ├── eliminar/               # DELETE - eliminar registros
│   ├── rescatar/               # SELECT - consultas y listados
│   ├── actualizar/             # UPDATE específicos
│   ├── filtros/                # Filtros dinámicos para tablas
│   ├── correos/                # Lógica de envío de correos
│   ├── descarga/               # Descarga de archivos
│   └── imprimir/               # Vistas de impresión
│
├── componentes/
│   ├── offcanvas.php           # Menú lateral offcanvas
│   ├── bloque_tabla_usuario.php
│   ├── bloque_tabla_tecnico.php
│   ├── bloque_tabla_adminn.php
│   └── ajax/ + js/
│
├── configuracion/              # Módulo de configuración (usuarios, permisos, menús, mantenimiento)
├── inventario/                 # Inventario equipos computacionales
├── inventario_herramintas/     # Inventario herramientas físicas
├── inventario_aseo/            # Inventario equipos aseo
├── inventario_sofware_odd/     # Inventario software / licencias
├── licenciamiento/             # Módulo licenciamiento software
├── estadistica/                # Módulo estadísticas
├── eventos/                    # Módulo eventos/calendario
├── ajax/                       # Endpoints AJAX globales
├── api/                        # API REST (tickets)
├── assets/                     # CSS, JS, imágenes del tema
├── css/                        # CSS propios del sistema
├── js/                         # JS propios del sistema
├── archivos/                   # Archivos adjuntos subidos por usuarios
├── img/ img_/                  # Imágenes del sistema (logos colegios, avatares)
└── pruebas/                    # Carpeta de pruebas — NO forma parte del sistema productivo
```

---

## Módulos principales

### 1. Tickets (núcleo del sistema)
El módulo más importante. Permite crear, asignar, gestionar y cerrar solicitudes de soporte.

**Tablas involucradas:**
- `tickets` — registro principal (asunto, descripción, estado, prioridad, técnico asignado, identificador único de 16 chars)
- `proceso_tickets` — línea de tiempo del ticket (fecha_creacion_inicio, fecha_estimada_admin, dias_estimada_admin, fecha_asignacion_tecnico, fecha_comienzo_ticket, fecha_termino_ticket, fecha_cierre_ticket)
- `estados_ticket` — estados posibles: 1=Recibido, 2=Asignado, 3=En proceso, 4=Borrador, 5=Terminado, 6=Cerrado, 7=Demorado
- `prioridad` — 1=Alta, 2=Media, 3=Baja
- `categoria_de_ticket` — categorías (Informática, SIAE, Redes, etc.)
- `avance_tecnicos` — bitácora de avances registrados por el técnico
- `ticket_conversaciones` — conversación entre usuario y técnico dentro del ticket
- `archivos_adjuntos_ticket` — archivos adjuntos (en carpeta `archivos/`)
- `comentarios_ticket` — comentarios adicionales
- `calificacion_ticket` — calificación del usuario al cerrar ticket
- `reactivacion_ticket` — historial de reactivaciones
- `reprogramar_ticket` — historial de reprogramaciones de fecha
- `log_eliminacion_tickets` — log cuando se elimina un ticket
- `recordatorio` — recordatorios asociados a tickets

**Flujo normal del ticket:**
`Borrador → Recibido → Asignado → En proceso → Terminado → Cerrado`
El estado `Demorado` se asigna automáticamente cuando se supera `fecha_estimada_admin`.

**`dias_estimada_admin`** se calcula en el backend como la diferencia entre `fecha_creacion_inicio` (de `proceso_tickets`) y la nueva `fecha_estimada_admin`. No viene del JS.

### 2. Usuarios y Permisos
- `usuarios` — tabla principal de usuarios (nombre, apellido, email, cargo, clave hasheada, intentos_fallidos, estado, id_area_trabajo)
- `perfiles` — perfil/rol del usuario
- `usuario_perfil` — relación usuario ↔ perfil
- `usuario_colegio` — relación usuario ↔ colegio (para usuarios tipo colegio)
- `area_trabajo` — áreas: INFORMATICA, CONTABILIDAD, RRHH, FINANZAS, GERENCIA, etc.
- `permisos` — permisos generales
- `permisos_menu_1` — permisos por ítem de menú por usuario
- `log_sesiones` / `log_sesiones_usuario` — registro de accesos

**Usuarios especiales (IDs hardcodeados en código):**
- IDs 28–34: corresponden a colegios (ven solo usuarios de su mismo grupo)
- El sistema distingue roles por perfil y por área de trabajo

### 3. Menú lateral dinámico
- `menu_1` — ítems principales del menú
- `menu_1_sub` — subítems del menú (FK → menu_1)
- `menu_h` / `menu_principal` — menús alternativos/históricos
- Los permisos de menú se gestionan en `configuracion/` por usuario
- Función principal: `$funciones->menuLateral($idUsuarioSession, $idPagActual)`

### 4. Inventario
Cuatro módulos de inventario independientes con estructura similar:
- **`inventario/`** — Equipos computacionales (PCs, notebooks)
  - Tablas: `equipos`, `equipo_memoria`, `equipo_monitor`, `equipo_procesador`, `equipo_almacenamiento`, `equipo_software`, `equipo_fotos`, `equipos_compra`
- **`inventario_herramintas/`** — Herramientas físicas
  - Tablas: `herramientas`, `herramienta_detalle`, `herramienta_fotos`, `herramienta_historial`, `herramienta_mantenciones`, `herramienta_prestamos`, `herramienta_compra`, `estado_herramienta`, `categoria_herramienta_catalogo`
- **`inventario_aseo/`** — Equipos de aseo
- **`inventario_sofware_odd/`** — Software/licencias
  - Tablas: `software_catalogo`, `software_historial`, `software_datos_almacenamiento`, `software_datos_sensibles_rel`, `software_tipo_usuario_rel`, `inventario_software_datos_sensibles`, `inventario_software_tipo_usuario`, `sitios_web_catalogo`
- **`licenciamiento/`** — Módulo licenciamiento

Otros dispositivos: `otros_dispositivos`, `tipos_dispositivos`, `inventario_pc`

### 5. Panel de inicio (Dashboard personalizable)
- `contenedor` — accesos directos rápidos por usuario (links a sistemas externos)
- `contenedores_layout` — widgets del panel (posición, color, icono, URL)
- `beneficios_principales` / `beneficios_destacados` — sección de beneficios
- `keep_notas` — notas tipo post-it del usuario

### 6. Comunicaciones internas
- `mensajes` / `mensajes_chat` — mensajería interna entre usuarios
- `conversacion` / `conversaciones` / `tabla_conversacion` — conversaciones generales
- `respuestas_mensajes` — respuestas a mensajes

### 7. Colegios
- `colegio` — registro de colegios administrados por SEDUC
- Los colegios tienen usuarios propios que solo ven sus tickets

### 8. Otros módulos
- `eventos` / `calendario_eventos` / `eventos_historial` — módulo de eventos y calendario
- `codigos_qr` / `curso_codigos_qr` — generación y lectura de QR para asistencia
- `arquitectura_alumnos` / `arquitectura_apoderado` / `arquitectura_retiro` — gestión de retiros de alumnos
- `denuncias_acoso` — módulo de denuncias internas
- `tecnicos` / `categoria_tecnico` — técnicos y sus categorías de especialidad

---

## Clase principal: `Funciones` (`class/funciones.php`)

Es la clase central del sistema. Se instancia en casi todas las páginas:
```php
$funciones = new Funciones();
```

Métodos clave:
- `menuLateral($idUsuarioSession, $idPagActual)` — renderiza el menú lateral
- `header()` — renderiza el `<head>` HTML con todos los CSS/JS
- `script()` — renderiza scripts JS globales
- `listaUsuarios()` — tabla de usuarios para configuración
- `actualizarTicketsDemoradosAutomaticamente()` — cron manual, se llama en principal.php
- `colaboradores($idUsuarioSession)` — directorio de usuarios/colegios
- `accesoDirecto($idUsuarioSession)` — panel de accesos rápidos
- `alertaModal()` / `mensajesModal()` — alertas y mensajes del usuario

---

## Patrones de código

### Conexión a BD
```php
$bdato = new MySQL("", "", "");  // credenciales vacías = usa config interna
$resultado = $bdato->consulta("SELECT ...");
$row = $bdato->fetch_array($resultado);
```

### Páginas principales
```php
session_start();
require_once 'class/conexion.php';  // o la ruta relativa según carpeta
require_once 'class/funciones.php';
$funciones = new Funciones();
$idUsuarioSession = $_SESSION['id'];
```

### AJAX
- Los endpoints AJAX están en `modelos/` (guardar, rescatar, editar, eliminar) y en `ajax/`
- Los modelos retornan JSON o HTML según el caso
- El JS hace `fetch()` o `$.ajax()` a estos endpoints

### Correos
- Se envían con PHPMailer
- Plantillas HTML en `class/correos/` (un archivo por evento: ingreso_ticket, cerrado_ticket, etc.)
- Lógica de envío en `modelos/correos/`

---

## Convenciones importantes

1. **No hay framework** — PHP puro con includes manuales
2. **Sin Composer** en producción — librerías incluidas directamente en `class/`
3. **`pruebas/`** es carpeta de experimentos — ignorar completamente al analizar el sistema
4. **Charset mixto** — algunas tablas usan `utf8mb4`, otras `latin1` (legacy). Cuidado con acentos
5. **IDs hardcodeados** — algunos IDs de usuarios/colegios están en el código PHP (ej: IDs 28-34 para colegios)
6. **`identificador`** en tickets — string de 16 caracteres único, generado al crear el ticket
7. **`estado` en tickets** — campo entero: 1=activo, 2=eliminado (soft delete)
8. **Zona horaria** siempre `America/Santiago` — ya configurada en el constructor de `Funciones`

---

## Variables de sesión globales

```php
$_SESSION['id']               // ID del usuario logueado
$_SESSION['nombre']           // Nombre
$_SESSION['apellido_paterno'] // Apellido
$_SESSION['id_area_trabajo']  // ID del área de trabajo
$_SESSION['primera_vez']      // 0=primer ingreso, 1=ya ingresó
```

---

## Archivos adjuntos

- Se guardan en `archivos/` con nombre hasheado
- Los adjuntos de conversaciones van en `archivos/adjuntosConversacionTicket/`
- La tabla `archivos_adjuntos_ticket` guarda la ruta relativa

---

## Notas del desarrollador

- El sistema está en **desarrollo activo** — hay archivos de prueba y versiones antiguas
- Archivos como `ticket_manuel.php`, `guadalupej.php`, `traspasando_datos.php`, `test.php`, `ejemplo.php` son experimentales
- Hay duplicados históricos: `calificacion_tickett` (doble t), `api_ticket.php` y `api_tickets.php`, `validacion.php` y `validacion_2.php`
- `menu_h` y `menu_principal` son tablas de menú antiguas, el sistema actual usa `menu_1` y `menu_1_sub`
