# Sistema de autenticación, permisos y menú dinámico
### SEDUC Chile · Panel Central

Cinco archivos, todos en la **raíz del proyecto**, **aditivos**: no reemplazan
`index.php`, `api/login.php`, `includes/validar_sesion.php` ni
`includes/header.php`.

| Archivo | Rol |
|---|---|
| `login.php` | Formulario + proceso de login, con bloqueo por intentos fallidos. |
| `validar_sesion.php` | Guard: se incluye al inicio de cada página protegida. |
| `menu_lateral.php` | `menu_lateral()`, `menu_lateral_js()`, `verificar_permiso_menu()`. |
| `menu_lateral.css` | Estilos del sidebar (tema oscuro, responsive, variables). |
| `README_SISTEMA.md` | Este documento. |

- **BD:** `crist668_sistema_panel_central` · host `localhost`
- **Conexión:** `Conexion::getInstance('sistema_panel_central')` (PDO, con
  `fetchAll()` / `fetchOne()` / `execute()`).
- **Sesión:** `$_SESSION['id']`, `$_SESSION['nombre']`, `$_SESSION['perfil']`,
  `$_SESSION['perfiles']` (array).

---

## ⚠️ 1. Antes de nada: alinear la base de datos

El código de este paquete asume el esquema que entregaste. El código **actual**
del repo (`clases/Usuario.php`, `api/login.php`) usa otras columnas
(`correo`, `activo`, `rol`). Si tu tabla `usuarios` todavía es la vieja, aplica
esta migración (ajusta tipos/longitudes a tu gusto):

```sql
-- ---- usuarios: columnas que faltan ----
ALTER TABLE usuarios
  ADD COLUMN email            VARCHAR(190) NULL AFTER nombre,
  ADD COLUMN estado           ENUM('activo','bloqueado','inactivo') NOT NULL DEFAULT 'activo',
  ADD COLUMN intento_fallidos INT NOT NULL DEFAULT 0,
  ADD COLUMN ultima_conexion  DATETIME NULL,
  ADD COLUMN fecha_creacion   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- Si ya tenías `correo` y `activo`, traspasa los datos:
UPDATE usuarios SET email  = correo WHERE email IS NULL;
UPDATE usuarios SET estado = IF(activo = 1, 'activo', 'inactivo');

-- Clave única de email (después de rellenar):
ALTER TABLE usuarios ADD UNIQUE KEY uq_usuarios_email (email);

-- ---- perfiles ----
CREATE TABLE IF NOT EXISTS perfiles (
  id_perfil   INT AUTO_INCREMENT PRIMARY KEY,
  nombre      VARCHAR(80)  NOT NULL,
  descripcion TEXT NULL,
  estado      ENUM('activo','inactivo') NOT NULL DEFAULT 'activo'
);

-- ---- usuario_perfil (N-M) ----
CREATE TABLE IF NOT EXISTS usuario_perfil (
  id_usuario       INT NOT NULL,
  id_perfil        INT NOT NULL,
  fecha_asignacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_usuario, id_perfil),
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id)         ON DELETE CASCADE,
  FOREIGN KEY (id_perfil)  REFERENCES perfiles(id_perfil)  ON DELETE CASCADE
);

-- ---- menu_1: falta caracteristica (si aplica) ----
ALTER TABLE menu_1 ADD COLUMN caracteristica TEXT NULL AFTER icono;

-- ---- menu_1_sub ----
CREATE TABLE IF NOT EXISTS menu_1_sub (
  id_submenu INT AUTO_INCREMENT PRIMARY KEY,
  id_menu    INT NOT NULL,
  nombre     VARCHAR(120) NOT NULL,
  archivo    VARCHAR(255) NOT NULL,
  icono      VARCHAR(255) NULL,
  orden      INT NOT NULL DEFAULT 0,
  FOREIGN KEY (id_menu) REFERENCES menu_1(id_menu) ON DELETE CASCADE
);
```

Si tu BD ya coincide con el esquema entregado, **omite este paso**.

---

## 2. Estructura de BD que usa el paquete

```
usuarios         id, nombre, email, password(hash), estado ENUM(activo|bloqueado|inactivo),
                 intento_fallidos INT, fecha_creacion, ultima_conexion
perfiles         id_perfil, nombre, descripcion, estado ENUM(activo|inactivo)
usuario_perfil   id_usuario, id_perfil, fecha_asignacion   (PK compuesta)
menu_1           id_menu, nombre, archivo, icono, caracteristica, orden
menu_1_sub       id_submenu, id_menu, nombre, archivo, icono, orden
permisos_menu_1  id, id_menu1, id_usuario, id_tipo_permiso  (1=ver, 2=editar, 3=eliminar)
```

`icono` guarda HTML de **Bootstrap Icons**: `<i class="bi bi-house"></i>`.

---

## 3. `login.php`

### Flujo

1. Usuario envía **email + contraseña** (con token CSRF).
2. Email no existe → **"Usuario no encontrado."**
3. `estado = 'bloqueado'` → **"Cuenta bloqueada. Contacte al administrador."**
4. `estado = 'inactivo'` → **"Cuenta inactiva. Contacte al administrador."**
5. `password_verify()` falla:
   - `intento_fallidos += 1`
   - si `>= MAX_INTENTOS` (3) → `estado = 'bloqueado'` + mensaje de bloqueo
   - si no → **"Contraseña incorrecta. Te quedan N intentos."**
6. `password_verify()` correcto:
   - `intento_fallidos = 0`, `ultima_conexion = NOW()`
   - lee perfiles del usuario (`usuario_perfil` ⨝ `perfiles`)
   - `session_regenerate_id(true)`
   - crea `$_SESSION['id']`, `['nombre']`, `['perfil']` (primer perfil),
     `['perfiles']` (todos)
   - redirige a `RUTA_POST_LOGIN` (`dashboard.php`)

### Configurable (arriba del archivo)

```php
const MAX_INTENTOS    = 3;
const RUTA_POST_LOGIN = 'dashboard.php';
```

### Consultas

```sql
-- Buscar usuario
SELECT id, nombre, email, password, estado, intento_fallidos
  FROM usuarios WHERE email = ? LIMIT 1;

-- Intento fallido (con o sin bloqueo)
UPDATE usuarios SET intento_fallidos = ?               WHERE id = ?;
UPDATE usuarios SET intento_fallidos = ?, estado = 'bloqueado' WHERE id = ?;

-- Acceso correcto
UPDATE usuarios SET intento_fallidos = 0, ultima_conexion = NOW() WHERE id = ?;

-- Perfiles
SELECT pf.id_perfil, pf.nombre
  FROM usuario_perfil up
  INNER JOIN perfiles pf ON pf.id_perfil = up.id_perfil
 WHERE up.id_usuario = ? AND pf.estado = 'activo'
 ORDER BY pf.id_perfil ASC;
```

### Crear un hash de contraseña

```php
echo password_hash('miClave123', PASSWORD_BCRYPT);
// guarda el resultado en usuarios.password
```

### Desbloquear una cuenta

```sql
UPDATE usuarios SET estado = 'activo', intento_fallidos = 0 WHERE email = ?;
```

---

## 4. `validar_sesion.php`

Se incluye **al principio** de cada página protegida, antes de imprimir HTML:

```php
<?php require_once __DIR__ . '/../validar_sesion.php';   // ajusta ../ según la carpeta ?>
```

Comprueba, en orden:

1. `!empty($_SESSION['id'])` — si no, → `login.php`.
2. El usuario existe en `usuarios`.
3. `estado === 'activo'` (rechaza `bloqueado` e `inactivo`).

Si algo falla: `Session::destruir()` + redirect a `login.php`.
Si todo va bien: deja `$usuario_actual` (array `id, nombre, email, estado`)
disponible en la página y refresca `$_SESSION['nombre']`.

**Redirección:** usa `BASE_URL` de `config.php` si está definida; si no, calcula
una ruta relativa según la profundidad de la URL.

```sql
SELECT id, nombre, email, estado FROM usuarios WHERE id = ? LIMIT 1;
```

---

## 5. `menu_lateral.php`

### `menu_lateral($id_usuario, $db, $modulo_activo = null): void`

Imprime `<aside class="sidebar">…</aside>` + backdrop.

1. Valida `$id_usuario` (> 0).
2. **Menús permitidos** — `menu_1` *INNER JOIN* `permisos_menu_1` por
   `id_usuario`. Sin fila de permiso → el menú no aparece.
3. **Submenús** — `menu_1_sub` de los menús permitidos, en una sola consulta.
   Los submenús **heredan** el permiso del menú padre.
4. **Página activa** — desde `$modulo_activo` o, si es `null`, desde `REQUEST_URI`.
5. Render: menú con hijos → `<a>` con `onclick="toggleSubmenu(event, ID)"` +
   `<ul class="submenu-container">`; menú sin hijos → enlace directo.
   Activo → clase `.active`; si un submenú está activo, el padre abre (`.open`).

**SQL:**

```sql
-- Menús del usuario
SELECT DISTINCT m.id_menu, m.nombre, m.archivo, m.icono, m.caracteristica, m.orden
  FROM menu_1 m
  INNER JOIN permisos_menu_1 p ON p.id_menu1 = m.id_menu
 WHERE p.id_usuario = ?
 ORDER BY CAST(m.orden AS UNSIGNED) ASC, m.nombre ASC;

-- Submenús (un ? por menú permitido)
SELECT s.id_submenu, s.id_menu, s.nombre, s.archivo, s.icono, s.orden
  FROM menu_1_sub s
 WHERE s.id_menu IN (?, ?, ?)
 ORDER BY s.id_menu ASC, CAST(s.orden AS UNSIGNED) ASC, s.nombre ASC;
```

- `DISTINCT`: el usuario puede tener varias filas de permiso (distinto
  `id_tipo_permiso`).
- `CAST(orden AS UNSIGNED)`: `menu_1.orden` es VARCHAR; sin cast, `"10" < "2"`.

### `menu_lateral_js(): void`

`<script>` (una sola vez, antes de `</body>`):

- `toggleSubmenu(event, menuId)` — expandir/contraer con animación real
  (`max-height = scrollHeight`).
- Modo **acordeón** (cierra los demás). Desactívalo con `var ACORDEON = false;`.
- Marca el enlace activo por URL y abre su submenú padre.
- `toggleSidebar(forzar)` — sidebar móvil; se cierra con `Esc`.

Expone en `window`: `toggleSubmenu`, `toggleSidebar`.

### `verificar_permiso_menu($id_usuario, $id_menu, $db): bool`

```sql
SELECT 1 FROM permisos_menu_1 WHERE id_usuario = ? AND id_menu1 = ? LIMIT 1;
```

Úsala al inicio de una página para bloquear el acceso por URL directa.

---

## 6. `menu_lateral.css`

Edita el bloque `:root`:

| Variable | Efecto |
|---|---|
| `--sb-bg`, `--sb-bg-elev` | Fondo del sidebar / cabecera (por defecto `#1e1b4b`). |
| `--sb-text`, `--sb-text-active`, `--sb-text-muted` | Colores de texto (`#c7d2fe`). |
| `--sb-active-bg`, `--sb-active-bar` | Ítem activo y su barrita. |
| `--sb-width` | Ancho del sidebar. |
| `--sb-radius` | Redondeo de ítems. |
| `--sb-transition` | Velocidad de animación. |
| `--sb-submenu-max` | Altura máx. de submenús si el JS no carga. |

Responsive: bajo **768px** el sidebar es off-canvas (`.sidebar.is-open` +
`.sidebar-backdrop`).

Clases: `.sidebar`, `.sidebar-nav`, `.menu-item`, `.menu-link`, `.menu-icon`,
`.menu-text`, `.menu-arrow`, `.submenu-container`, `.submenu-link`, `.active`.

---

## 7. Implementación paso a paso

### 7.1 `<head>` de tu layout

```html
<html lang="es" class="js">
<head>
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="/menu_lateral.css">
</head>
```

`class="js"` mantiene los submenús cerrados cuando el JS sí carga (sin JS se
muestran abiertos).

### 7.2 `dashboard.php` (o cualquier página protegida)

```php
<?php
require_once __DIR__ . '/validar_sesion.php';          // 1) guard
require_once __DIR__ . '/menu_lateral.php';            // 2) funciones del menú

$id_usuario = $_SESSION['id'];
$db         = Conexion::getInstance('sistema_panel_central');
?>
<!DOCTYPE html>
<html lang="es" class="js">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="/menu_lateral.css">
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
<div class="layout">

  <?php menu_lateral($id_usuario, $db); ?>       <!-- 3) pinta el sidebar -->

  <div class="main-wrap">
    <button class="btn-menu-movil" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
    <main class="content">
      <h1>Hola, <?= htmlspecialchars($_SESSION['nombre']) ?></h1>
      <p>Perfil: <?= htmlspecialchars($_SESSION['perfil']) ?></p>
    </main>
  </div>
</div>

<?php menu_lateral_js(); ?>                        <!-- 4) JS, una sola vez -->
</body>
</html>
```

### 7.3 Página dentro de una carpeta con control de permiso

`configuracion/permisos.php`:

```php
<?php
require_once __DIR__ . '/../validar_sesion.php';
require_once __DIR__ . '/../menu_lateral.php';

$id_usuario = $_SESSION['id'];
$db         = Conexion::getInstance('sistema_panel_central');

define('ID_MENU_CONFIGURACION', 7);               // id_menu de esta sección
if (!verificar_permiso_menu($id_usuario, ID_MENU_CONFIGURACION, $db)) {
    http_response_code(403);
    die('No tienes permiso para acceder a esta sección.');
}
?>
<!-- ... HTML de la página ... -->
```

---

## 8. Casos de uso

| Necesito… | Cómo |
|---|---|
| Proteger una página | `require_once '.../validar_sesion.php';` al inicio. |
| Bloquear acceso por URL a una sección | `verificar_permiso_menu($_SESSION['id'], $id_menu, $db)`. |
| Mostrar el nombre del usuario | `$_SESSION['nombre']` (lo refresca `validar_sesion.php`). |
| Saber el perfil | `$_SESSION['perfil']` (principal) o `$_SESSION['perfiles']` (todos). |
| Dar permiso a un usuario sobre un menú | `INSERT INTO permisos_menu_1 (id_menu1, id_usuario, id_tipo_permiso) VALUES (?,?,1);` |
| Quitar un permiso | `DELETE FROM permisos_menu_1 WHERE id_usuario = ? AND id_menu1 = ?;` |
| Desbloquear cuenta | `UPDATE usuarios SET estado='activo', intento_fallidos=0 WHERE email=?;` |
| Cambiar nº de intentos | `MAX_INTENTOS` en `login.php`. |
| Cambiar destino tras login | `RUTA_POST_LOGIN` en `login.php`. |

---

## 9. Seguridad

- **SQL:** 100% `prepare()` + parámetros vía `Conexion`. Nunca se concatena.
- **Salida:** `nombre` y `archivo` se escapan con `htmlspecialchars()`.
- **`icono` se imprime SIN escapar** (guarda HTML por diseño): restringe su
  edición a administradores.
- **Contraseñas:** `password_hash(PASSWORD_BCRYPT)` al crear, `password_verify()`
  al validar. Nunca en texto plano.
- **Sesión:** `session_regenerate_id(true)` tras login (anti-fijación);
  `validar_sesion.php` revalida el `estado` contra la BD en cada carga, así un
  usuario bloqueado en caliente pierde el acceso al recargar.
- **CSRF:** `login.php` incluye token de un solo uso por sesión.
- **Doble control de menús:** ocultar un menú es solo UI. La barrera real es
  `verificar_permiso_menu()` en cada página/endpoint.
- **Nota (enumeración de usuarios):** el flujo pedido distingue "Usuario no
  encontrado" de "Contraseña incorrecta" y muestra intentos restantes. Es
  cómodo para el usuario pero revela qué correos existen. Si te importa,
  unifica ambos a un mensaje genérico ("Credenciales incorrectas").

---

## 10. Troubleshooting

| Síntoma | Causa probable | Solución |
|---|---|---|
| `Unknown column 'email'` / `'estado'` / `'intento_fallidos'` | La tabla `usuarios` aún es el esquema viejo. | Aplica la migración de la sección 1. |
| `Base table 'menu_1_sub' doesn't exist` | Falta la tabla de submenús. | `CREATE TABLE menu_1_sub …` (sección 1). |
| Login siempre "Usuario no encontrado" | Buscas por `email` pero los datos están en `correo`. | `UPDATE usuarios SET email = correo;` |
| Menú vacío ("Sin menús asignados") | El usuario no tiene filas en `permisos_menu_1`, o `$_SESSION['id']` es 0/null. | Revisa la sesión y `SELECT * FROM permisos_menu_1 WHERE id_usuario = ?`. |
| "Sesión no válida" en el sidebar | `menu_lateral()` recibió id ≤ 0. | Incluye `validar_sesion.php` antes y pásale `$_SESSION['id']`. |
| Redirige a login en bucle | `estado` del usuario no es exactamente `'activo'`, o `login.php` no setea `$_SESSION['id']`. | Verifica el valor de `estado` y que el login llegó al bloque de éxito. |
| `validar_sesion.php` redirige a una ruta rota | Sin `BASE_URL` y la app no está una carpeta bajo el docroot. | Define `BASE_URL` en `config.php`. |
| Iconos no se ven | Falta el CSS de Bootstrap Icons, o `icono` vacío. | Añade el `<link>` de `bootstrap-icons`. |
| Submenús no se abren al hacer clic | `menu_lateral_js()` no se llamó o se llamó antes del menú. | Ponlo una sola vez, al final del `<body>`. |
| Submenús siempre abiertos | Falta `class="js"` en `<html>` o el JS falló. | Añade `<html class="js">` y revisa la consola. |
| Orden de menús 1, 10, 2, 3… | `menu_1.orden` es texto. | Ya resuelto con `CAST(m.orden AS UNSIGNED)`. |
| Página activa no se marca | El `archivo` en BD no coincide con la URL real. | Usa el 3er parámetro: `menu_lateral($id, $db, 'principal.php')`. |
| Cuenta bloqueada sin querer | 3 intentos fallidos. | `UPDATE usuarios SET estado='activo', intento_fallidos=0 WHERE email=?;` |
| Error `getInstance()` de argumento | La clase exige el nombre corto de la BD. | `Conexion::getInstance('sistema_panel_central')`. |

---

## 11. Checklist de instalación

- [ ] Copiar los 5 archivos a la raíz del proyecto.
- [ ] Aplicar la migración de BD (sección 1) si tu esquema no coincide.
- [ ] Crear al menos un perfil y asignarlo (`perfiles`, `usuario_perfil`).
- [ ] Cargar filas en `menu_1` / `menu_1_sub` y dar permisos en `permisos_menu_1`.
- [ ] Añadir el `<link>` de Bootstrap Icons y de `menu_lateral.css` al layout.
- [ ] Poner `class="js"` en `<html>`.
- [ ] Incluir `validar_sesion.php` en cada página protegida.
- [ ] Llamar `menu_lateral($_SESSION['id'], $db)` y `menu_lateral_js()`.
- [ ] Crear un usuario de prueba con `password_hash()` y `estado = 'activo'`.
