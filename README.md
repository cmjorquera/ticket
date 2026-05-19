# Sistema SEDUC Ticket

Este archivo es la memoria tecnica del proyecto. Antes de hacer cambios en el sistema, revisar este README para no perder el contexto de carpetas, modulos, tablas y reglas de funcionamiento.

## Resumen

Sistema web PHP + MySQL/MariaDB para SEDUC SPA. Centraliza tickets de soporte, inventarios, usuarios, permisos, mensajes internos, eventos, QR, estadisticas y accesos directos.

- Backend: PHP puro, sin framework.
- Base de datos principal: `acceso_sistema_panel`.
- Conexion: `class/conexion.php`, clase `MySQL`.
- Zona horaria usada por el sistema: `America/Santiago`.
- Frontend: Bootstrap, Bootstrap Icons, FontAwesome, DataTables, Chart.js, SweetAlert2, Intro.js.
- Correos: PHPMailer en `class/PHPMailer/`.
- Librerias Composer: PhpSpreadsheet, FPDF, mPDF, chillerlan/php-qrcode.

## Punto de entrada y flujo general

- `index.php`: login principal.
- `validacion.php` / `validacion_2.php`: validacion de acceso.
- `principal.php`: panel principal despues del login. Muestra tickets, accesos y alertas.
- `ticket.php`: vista de ticket individual.
- `ticket_admin_v2.php`: administracion de tickets.
- `ticket_asignados.php`: tickets asignados a un tecnico.
- `configuracion.php` y `configuracion/`: administracion de usuarios, permisos y mantenimiento.
- `class/funciones.php`: clase central del sistema, renderiza menu, header, scripts, colaboradores, accesos, alertas y varias consultas globales.

El sistema trabaja mucho con `$_SESSION`:

- `$_SESSION['id']`: id del usuario logueado.
- `$_SESSION['nombre']`: nombre.
- `$_SESSION['apellido_paterno']`: apellido.
- `$_SESSION['id_area_trabajo']`: area de trabajo.
- `$_SESSION['primera_vez']`: control de primer ingreso.

## Conexion a base de datos

Archivo principal: `class/conexion.php`.

La clase `MySQL` ignora normalmente los parametros recibidos y conecta internamente a:

```php
mysqli_connect('localhost', 'acceso_comun', 'jorquera86;', 'acceso_sistema_panel');
```

Patron usado en muchas paginas:

```php
require_once 'class/conexion.php';
$bdato = new MySQL('', '', '');
$resultado = $bdato->consulta('SELECT ...');
$row = $bdato->fetch_array($resultado);
```

Importante:

- La conexion configura `utf8mb4`.
- Algunas partes antiguas pueden venir de datos `latin1` o usar `htmlentities(..., "ISO-8859-1")`.
- Hay SQL directo concatenado en varios archivos legacy. Al tocar codigo nuevo, preferir `prepare()` cuando sea posible.

## Estructura de carpetas

```text
SISTEMA_TICKET/
|-- index.php                    Login
|-- principal.php                Dashboard principal
|-- ticket.php                   Vista usuario/admin de ticket
|-- ticket_admin_v2.php          Administracion de tickets
|-- ticket_asignados.php         Tickets de tecnico
|-- configuracion.php            Configuracion legacy
|-- estadistica.php              Estadisticas legacy
|-- graficos.php                 Graficos de tickets
|-- colaboradores.php            Directorio de usuarios
|-- perfil.php                   Perfil de usuario
|-- mensaje.php                  Mensajes internos
|-- chat.php                     Chat interno
|-- generadorQr.php              Generacion QR
|-- denunciaAcoso.php            Denuncias internas
|
|-- class/
|   |-- conexion.php             Conexion MySQL
|   |-- funciones.php            Clase central Funciones
|   |-- personas.php             Clase Personas
|   |-- config.php               Constantes de cifrado AES
|   |-- CursosCodigosQR.php      Logica QR
|   |-- correos/                 Plantillas HTML de correos
|   |-- PHPMailer/               Libreria PHPMailer
|
|-- modelos/
|   |-- guardar/                 Inserts y acciones POST
|   |-- editar/                  Updates
|   |-- eliminar/                Deletes o soft deletes
|   |-- rescatar/                Selects y endpoints de consulta
|   |-- filtros/                 Filtros dinamicos
|   |-- correos/                 Logica de correos
|   |-- descarga/                Exportaciones PDF/Excel
|   |-- imprimir/                Impresiones
|
|-- componentes/                 Bloques visuales reutilizados
|-- ajax/                        Endpoints AJAX globales
|-- api/                         API de tickets
|-- configuracion/               Modulo nuevo/configuracion
|-- estadistica/                 Modulo estadisticas
|-- eventos/                     Calendario y eventos
|-- inventario/                  Inventario equipos computacionales
|-- inventario_herramintas/      Inventario herramientas fisicas
|-- inventario_aseo/             Inventario aseo
|-- inventario_sofware_odd/      Inventario software antiguo/odd
|-- licenciamiento/              Inventario software/licencias actual
|-- archivos/                    Adjuntos subidos
|-- img/, img_, imagenes/        Imagenes, logos y avatares
|-- assets/, css/, js/, scss/    Estilos y scripts
|-- vendor/                      Dependencias Composer
|-- node_modules/                Dependencias npm
|-- pruebas/                     Pruebas, no tomar como productivo
```

## Modulo Tickets

Es el nucleo del sistema. Permite crear tickets, asignarlos a tecnicos, actualizar estados, registrar avances, adjuntar archivos, conversar, cerrar y calificar.

Archivos importantes:

- `principal.php`: listado/dashboard.
- `ticket.php`: detalle del ticket.
- `ticket_admin_v2.php`: vista admin.
- `ticket_asignados.php`: vista tecnico.
- `modelos/guardar/guardar_ticket.php`: crea ticket.
- `modelos/guardar/guardar_responsable_ticket.php`: asignacion/responsable.
- `modelos/guardar/guardar_avance_tecnicos.php`: avances tecnicos.
- `modelos/rescatar/ticket.php`: datos del ticket.
- `modelos/rescatar/ticket_administracion.php`: listado admin.
- `modelos/eliminar/eliminar_ticket.php`: eliminacion/soft delete.
- `class/correos/`: plantillas de correo por evento.

Tablas principales:

| Tabla | Uso |
|---|---|
| `tickets` | Registro principal del ticket: usuario, asunto, descripcion, categoria, estado, tecnico, prioridad, identificador. |
| `proceso_tickets` | Fechas y horas del ciclo del ticket: creacion, asignacion, comienzo, termino, cierre, fecha estimada y dias estimados. |
| `estados_ticket` | Catalogo de estados del ticket. |
| `prioridad` | Prioridades: alta, media, baja u orden equivalente. |
| `categoria_de_ticket` | Categorias del ticket. |
| `categoria_tecnico` | Relacion categoria-tecnico para asignacion automatica. |
| `avance_tecnicos` | Bitacora de acciones/avances del tecnico. |
| `ticket_conversaciones` | Conversacion entre usuario y tecnico/admin. |
| `archivos_adjuntos_ticket` | Adjuntos asociados al ticket. Ruta relativa en `archivos/`. |
| `comentarios_ticket` | Comentarios adicionales. |
| `calificacion_ticket` | Evaluacion/calificacion del usuario al cerrar. |
| `reactivacion_ticket` | Historial de reactivaciones. |
| `reprogramar_ticket` | Historial de reprogramaciones. |
| `log_eliminacion_tickets` | Auditoria cuando se elimina o mantiene un ticket. |
| `recordatorio` | Recordatorios asociados a tickets. |

Estados usados:

- `1`: Recibido.
- `2`: Asignado.
- `3`: En proceso.
- `4`: Borrador.
- `5`: Terminado.
- `6`: Cerrado.
- `7`: Demorado.

Flujo normal:

```text
Borrador -> Recibido -> Asignado -> En proceso -> Terminado -> Cerrado
```

Notas del modulo:

- `tickets.identificador` es un string unico de 16 caracteres.
- `tickets.estado` se usa como estado logico en algunas partes: `1` activo, `2` eliminado.
- `proceso_tickets.dias_estimada_admin` se calcula desde backend al guardar fechas, no confiar solo en JS.
- Los adjuntos se guardan en `archivos/`; adjuntos de conversaciones pueden estar en `archivos/adjuntosConversacionTicket/`.

## Usuarios, colegios y permisos

Archivos importantes:

- `ingresar_usuario.php`
- `configuracion.php`
- `configuracion/index.php`
- `configuracion/componentes/usuarios_tab.php`
- `modelos/guardar/guardar_usuario.php`
- `modelos/guardar/guardar_permisos.php`
- `modelos/guardar/guardar_permisos_categoria.php`
- `modelos/rescatar/usuarios.php`
- `modelos/rescatar/usuario.php`
- `modelos/rescatar/area_trabajo.php`
- `class/funciones.php`, metodo `listaUsuarios()`.

Tablas:

| Tabla | Uso |
|---|---|
| `usuarios` | Usuarios del sistema: nombre, apellidos, email, telefono, clave, estado, cargo, area, tokens. |
| `perfiles` | Roles/perfiles disponibles. |
| `usuario_perfil` | Relacion usuario-perfil. |
| `colegio` | Catalogo de colegios. |
| `usuario_colegio` | Relacion usuario-colegio. |
| `area_trabajo` | Areas internas: informatica, contabilidad, RRHH, finanzas, gerencia, etc. |
| `permisos` | Permisos generales legacy. |
| `permisos_menu_1` | Permisos por item de menu para cada usuario. |
| `razones_bloqueo` | Motivos de bloqueo/estado de usuario. |
| `log_sesiones` | Auditoria de sesiones. |
| `log_sesiones_usuario` | Auditoria adicional por usuario. |

Usuarios/colegios con IDs especiales:

- En `class/funciones.php` se usan los IDs `28, 29, 30, 31, 32, 33, 34` como colegios o usuarios especiales de colegios.
- Esos usuarios tienen restricciones visuales y de alcance en colaboradores/tickets.

## Menu lateral

El menu se renderiza desde:

```php
$funciones->menuLateral($idUsuarioSession, $idPagActual);
```

Tablas:

| Tabla | Uso |
|---|---|
| `menu_1` | Items principales del menu actual. |
| `menu_1_sub` | Subitems del menu actual. |
| `permisos_menu_1` | Que usuario puede ver cada item/subitem. |
| `menu_h` | Menu historico/alternativo. |
| `menu_principal` | Menu legacy. |

Regla practica: para tocar permisos o menu actual, mirar primero `menu_1`, `menu_1_sub` y `permisos_menu_1`.

## Configuracion de usuarios, permisos y categorias

Carpeta: `configuracion/`.

Este modulo concentra el panel moderno de configuracion. Tiene vistas con pestanas, listado filtrable de usuarios, administracion de categorias de ticket y asignacion de categorias a tecnicos.

Archivos importantes:

- `configuracion/index.php`: panel con pestanas `Usuarios`, `Configuracion`, `Dashboard` y `Mantenimiento`.
- `configuracion/listado_usuario.php`: listado con filtros por colegio, area y estado; usa botones `lu-btn`.
- `configuracion/admin_categorias.php`: administracion visual de categorias por tecnico, con cards y chips de colores.
- `configuracion/componentes/head.php`: cabecera y assets del modulo.
- `configuracion/componentes/usuarios_tab.php`: contenido de la pestana de usuarios.
- `configuracion/css/index.css`: estilos del modulo.
- `configuracion/js/comun.js`: acciones de usuario y permisos: `agregarUsuario()`, `modificarUsuario()`, `mostrarPermisos()`, `estadoUsuario()`.
- `configuracion/js/index.js`: inicializacion DataTable solo para `configuracion/index.php`.
- `js/funciones.js`: funciones globales como `guardarCambios()`, `abrirModalAdministrarCategorias()` y `cargarTablaAdministrarCategorias()`.

Endpoints relacionados:

- `modelos/guardar/guardar_usuario.php`
- `modelos/guardar/guardar_permisos.php`
- `modelos/guardar/guardar_permisos_categoria.php`
- `modelos/guardar/crear_categorias.php`
- `modelos/rescatar/administrar_categorias.php`
- `modelos/rescatar/area_trabajo.php`
- `modelos/rescatar/colegio.php`
- `modelos/rescatar/menu_1.php`
- `modelos/editar/editar_categoria_ticket.php`
- `modelos/editar/cambiar_estado_categoria_ticket.php`

Tablas clave:

| Tabla | Uso |
|---|---|
| `usuarios` | Usuarios del sistema. `id_area_trabajo = 1` identifica a tecnicos de informatica. |
| `area_trabajo` | Areas de trabajo. `id_area = 1` es `INFORMATICA`. |
| `perfiles` | Roles: `1=usuario`, `2=tecnico`, `3=administrador`. |
| `usuario_perfil` | Relacion usuario-perfil. |
| `usuario_colegio` | Relacion usuario-colegio-perfil y estado. |
| `colegio` | Colegios disponibles. |
| `menu_1` | Menus principales. |
| `menu_1_sub` | Submenus. |
| `permisos_menu_1` | Permisos por usuario. `1=ver`, `3=oculto`. |
| `categoria_de_ticket` | Categorias de ticket, icono, orden y estado. |
| `categoria_tecnico` | Relacion entre tecnico (`usuarios.id`) y categoria. |

Convenciones de rutas:

```php
$assetPrefix = '../'; // dentro de /configuracion/
$assetPrefix = '';    // desde raiz
```

En JavaScript debe existir:

```js
window.CONFIG_RELATIVE_ROOT = '../'; // dentro de /configuracion/
window.CONFIG_RELATIVE_ROOT = '';    // desde raiz
```

Todas las URLs AJAX deben construirse asi:

```js
const root = typeof window.CONFIG_RELATIVE_ROOT === 'string' ? window.CONFIG_RELATIVE_ROOT : '';
const url = root + 'modelos/rescatar/administrar_categorias.php';
```

Notas ya resueltas y que no se deben rehacer:

- `class/funciones.php`, metodo `listaUsuarios()`: la columna Area diferencia tecnicos con punto azul y badge `Informatica`, y usuarios con punto verde.
- `configuracion/js/comun.js`: el modal Agregar usuario usa layout de dos columnas, formulario a la izquierda y menus a la derecha, con ancho aproximado de `860px`.
- `configuracion/listado_usuario.php`: pagina nueva con filtros por colegio, area y estado, mas botones `lu-btn`.
- `configuracion/admin_categorias.php`: pagina nueva con cards por tecnico y chips de colores para asignar categorias.
- `js/funciones.js`: `cargarTablaAdministrarCategorias()` y handlers `js-toggle` / `js-editar` usan `CONFIG_RELATIVE_ROOT`.
- `abrirModalAdministrarCategorias()` ya fue corregida para usar prefijo relativo antes de cada `$.ajax`.

## Inventario de equipos computacionales

Carpeta: `inventario/`.

Archivos importantes:

- `inventario/index.php`
- `inventario/class/Inventario.php`
- `inventario/registrar_equipo.php`
- `inventario/guardar_equipo.php`
- `inventario/editar_equipo.php`
- `inventario/actualizar_equipo.php`
- `inventario/ver_equipo.php`
- `inventario/ajax/listar_equipos.php`
- `inventario/views/sql_sugerido_inventario.sql`

Tablas:

| Tabla | Uso |
|---|---|
| `equipos` | Registro principal del equipo: colegio, usuario, asignado, nombre, fabricante, producto, serie, tipo, QR, estado. |
| `equipos_compra` | Datos de compra: valor, proveedor, factura, fecha, observacion. |
| `equipo_almacenamiento` | Disco/almacenamiento del equipo. |
| `equipo_procesador` | Procesador. |
| `equipo_software` | Windows, Office, antivirus. |
| `equipo_memoria` | Memorias RAM, puede tener varias por equipo. |
| `equipo_monitor` | Monitores asociados, puede tener varios. |
| `equipo_fotos` | Fotos normales y panoramicas del equipo. |
| `equipo_historial` | Auditoria/historial del equipo. |
| `estado_equipo` | Catalogo de estados: Activo, Bodega, Reparacion, Baja, Prestado. |
| `tipo_pc_catalogo` | Catalogo de tipos de PC. |

## Inventario de herramientas

Carpeta: `inventario_herramintas/` (el nombre de carpeta tiene error ortografico, mantenerlo).

Archivos importantes:

- `inventario_herramintas/portada_herramientas.php`
- `inventario_herramintas/class/Inventario.php`
- `inventario_herramintas/registrar_herramienta.php`
- `inventario_herramintas/guardar_herramienta.php`
- `inventario_herramintas/editar_herramienta.php`
- `inventario_herramintas/ver_herramienta.php`
- `inventario_herramintas/ajax/listar_herramientas.php`
- `inventario_herramintas/views/sql_sugerido_inventario_herramientas.sql`

Tablas:

| Tabla | Uso |
|---|---|
| `herramientas` | Registro principal de herramienta: colegio, usuario, asignado, nombre, marca, modelo, serie, categoria, QR, estado, stock. |
| `herramienta_detalle` | Energia, medida, capacidad, mantencion, garantia. |
| `herramienta_compra` | Valor, proveedor, factura, fecha de compra. |
| `herramienta_prestamos` | Prestamos, responsables, fechas y estado. |
| `herramienta_mantenciones` | Mantenciones, costo, proveedor y proxima fecha. |
| `herramienta_fotos` | Fotos de herramienta. |
| `herramienta_historial` | Auditoria/historial. |
| `estado_herramienta` | Disponible, En bodega, En reparacion, Baja, Prestada, Extraviada. |
| `categoria_herramienta_catalogo` | Categorias: Manual, Electrica, Medicion, Seguridad, Mantencion, Jardineria, Limpieza, Otro. |

## Inventario de aseo

Carpeta: `inventario_aseo/`.

Archivos importantes:

- `inventario_aseo/index.php`
- `inventario_aseo/class/Inventario.php`
- `inventario_aseo/registrar_producto.php`
- `inventario_aseo/guardar_producto.php`
- `inventario_aseo/reporte.php`
- `inventario_aseo/views/sql_sugerido_inventario_aseo.sql`

Tablas:

| Tabla | Uso |
|---|---|
| `aseo_productos` | Productos de aseo por colegio. |
| `aseo_movimientos` | Ingresos y salidas de stock. |
| `aseo_historial` | Historial/auditoria del producto. |
| `aseo_categoria_catalogo` | Categorias de productos. |
| `aseo_unidad_catalogo` | Unidades: Litro, Botella, Bidon, Unidad, Caja, Bolsa, etc. |

## Licenciamiento e inventario de software

Carpetas:

- `licenciamiento/`: modulo actual recomendado.
- `inventario_sofware_odd/`: modulo anterior o paralelo. El nombre tiene errores ortograficos; no renombrar sin revisar rutas.

Archivos importantes:

- `licenciamiento/index.php`
- `licenciamiento/dashboard.php`
- `licenciamiento/class/InventarioSoftware.php`
- `licenciamiento/registrar_software.php`
- `licenciamiento/guardar_software.php`
- `licenciamiento/registrar_sitio_web.php`
- `licenciamiento/guardar_sitio_web.php`
- `licenciamiento/views/sql_sugerido_inventario_software.sql`

Tablas:

| Tabla | Uso |
|---|---|
| `software_catalogo` | Software/licencias: colegio, responsable, nombre, version, licencias, fechas, costo, proveedor. |
| `software_datos_almacenamiento` | Contactos/datos asociados al software. |
| `software_historial` | Auditoria del software. |
| `inventario_software_datos_sensibles` | Catalogo de datos sensibles tratados por software. |
| `software_datos_sensibles_rel` | Relacion software-dato sensible. |
| `inventario_software_tipo_usuario` | Catalogo de tipos de usuarios: colaborador, alumno, proveedor, otro. |
| `software_tipo_usuario_rel` | Relacion software-tipo usuario. |
| `sitios_web_catalogo` | Sitios web, apps o clientes por colegio. |

## Eventos y calendario

Carpeta: `eventos/`.

Archivos importantes:

- `eventos/index.php`
- `eventos/calendario.php`
- `eventos/reporte.php`
- `eventos/ajax/guardar_evento_api.php`
- `eventos/ajax/obtener_eventos.php`
- `eventos/ajax/editar_evento.php`
- `eventos/ajax/eliminar_evento.php`
- `creacionEventos.php`
- `listarEventos.php`

Tablas relacionadas:

| Tabla | Uso |
|---|---|
| `eventos` | Eventos principales, si se usa en flujo legacy. |
| `calendario_eventos` | Eventos de calendario. |
| `eventos_historial` | Historial/auditoria de eventos. |

## Mensajes y chat

Archivos importantes:

- `mensaje.php`
- `mensaje_enviados.php`
- `men_archivados.php`
- `chat.php`
- `chatBox.php`
- `obtener_mensajes.php`
- `contar_mensajes.php`
- `modelos/guardar/guardar_mensaje.php`
- `modelos/guardar/mensajes.php`
- `modelos/guardar/chat.php`
- `modelos/rescatar/mensajes.php`

Tablas:

| Tabla | Uso |
|---|---|
| `mensajes` | Mensajes internos principales. |
| `mensajes_chat` | Chat interno. |
| `conversacion` | Conversaciones legacy. |
| `conversaciones` | Conversaciones nuevas/paralelas. |
| `tabla_conversacion` | Relacion o detalle de conversaciones. |
| `respuestas_mensajes` | Respuestas de mensajes. |

## Dashboard, accesos y notas

Archivos importantes:

- `principal.php`
- `class/funciones.php`, metodos `accesoDirecto()`, `alertaModal()`, `mensajesModal()`.
- `modelos/guardar/guardar_contenedor.php`
- `modelos/editar/contenedor_accesos.php`
- `modelos/eliminar/eliminar_contenedor.php`

Tablas:

| Tabla | Uso |
|---|---|
| `contenedor` | Accesos directos por usuario. |
| `contenedores_layout` | Widgets/layout del dashboard. |
| `beneficios_principales` | Beneficios principales. |
| `beneficios_destacados` | Beneficios destacados. |
| `keep_notas` | Notas tipo post-it del usuario. |

## QR, dispositivos y otros modulos

QR:

- `generadorQr.php`
- `leyendoCursoQR.php`
- `leyendoDispositivoQR.php`
- `leyendoEquipoQR.php`
- `codigosQR/`
- `class/CursosCodigosQR.php`

Tablas QR:

- `codigos_qr`
- `curso_codigos_qr`

Otros dispositivos:

- `otros_dispositivos`
- `tipos_dispositivos`
- `inventario_pc`

Denuncias:

- `denunciaAcoso.php`
- `modelos/guardar/denuncia_acoso.php`
- Tabla: `denuncias_acoso`

Alumnos/retiros:

- `arquitectura_alumnos`
- `arquitectura_apoderado`
- `arquitectura_retiro`

Tecnicos:

- `tecnicos`
- `categoria_tecnico`

## API

Carpeta: `api/`.

Archivos:

- `api/index.php`
- `api/routes.php`
- `api/tickets.php`
- `api/logica.php`
- `api/funciones_api.php`
- Tambien existen `api_ticket.php` y `api_tickets.php` en raiz como endpoints legacy.

Uso principal: consultas o integraciones relacionadas con tickets.

## Correos

Plantillas en `class/correos/`:

- `ingreso_ticket.php`
- `ticket_tecnico.php`
- `sin_tecnico.php`
- `en_proceso_ticket.php`
- `terminado_ticket.php`
- `cerrado_ticket.php`
- `calificacion_ticket.php`
- `reinicio_pass.php`
- `activar_cuenta.php`
- `gracias.php`

Logica adicional en `modelos/correos/`.

## Reglas importantes para futuros cambios

1. Antes de modificar, revisar este README y luego el archivo concreto del modulo.
2. No tomar `pruebas/` como referencia productiva.
3. No renombrar carpetas con errores (`inventario_herramintas`, `inventario_sofware_odd`) sin revisar todas las rutas.
4. Para tickets, cuidar siempre las tablas `tickets` y `proceso_tickets`; normalmente se actualizan juntas.
5. Para permisos/menu actual, mirar `menu_1`, `menu_1_sub` y `permisos_menu_1`.
6. Para usuarios colegio, recordar IDs especiales `28` a `34`.
7. Para nuevos SQL, preferir consultas preparadas con `prepare()` o `mysqli_prepare`.
8. Al cambiar fechas, mantener `America/Santiago`.
9. Al tocar adjuntos, mantener rutas relativas dentro de `archivos/`.
10. Al tocar inventarios, revisar el SQL sugerido del modulo correspondiente en `views/sql_sugerido_*.sql`.
11. En `configuracion/`, toda URL AJAX debe usar `CONFIG_RELATIVE_ROOT`.
12. No cargar `configuracion/js/index.js` fuera de `configuracion/index.php`, porque causa doble inicializacion de DataTables.
13. Los tecnicos se detectan por `usuarios.id_area_trabajo = 1` (`INFORMATICA`).
14. Para asignacion de categorias a tecnicos, usar `categoria_tecnico` y `categoria_de_ticket`.

## Archivos de contexto complementarios

- `CLAUDE.md`: contiene contexto adicional del sistema y puede servir como referencia secundaria.
- `explicacion_menu_permisos.txt`: explica permisos/menu.
- SQL sugeridos:
  - `inventario/views/sql_sugerido_inventario.sql`
  - `inventario_herramintas/views/sql_sugerido_inventario_herramientas.sql`
  - `inventario_aseo/views/sql_sugerido_inventario_aseo.sql`
  - `licenciamiento/views/sql_sugerido_inventario_software.sql`
