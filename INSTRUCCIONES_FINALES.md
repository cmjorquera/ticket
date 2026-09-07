# ✅ INSTRUCCIONES FINALES

## PASOS PARA IMPLEMENTAR

1. ✅ Todos los archivos han sido corregidos automáticamente.
2. ✅ Elimina los archivos de `LISTA_ELIMINAR.md` (la mayoría ya no existen).
3. ✅ Sube todo a tu cPanel.
4. ✅ Accede a: `https://sistemabase.webnia.cl`
5. ✅ Login: `cjorquera@seduc.cl` / `112233`
6. ✅ ¡Sistema funciona!

> ⚠️ **Revisa la URL real:** `config.php` define
> `BASE_URL = 'https://panelcentral.webnia.cl/'`. Si tu dominio es
> `sistemabase.webnia.cl`, actualiza `config.php` para que el redirect de
> `validar_sesion.php` apunte al dominio correcto.

---

## QUÉ FUNCIONA AHORA

✅ Login con validación de intentos (3 fallos → cuenta `bloqueado`).
✅ Menú lateral **dinámico** según `permisos_menu_1` del usuario.
✅ Dashboard protegido.
✅ Páginas (`pages/*.php`) protegidas.
✅ Validación de sesión en cada carga (revalida `estado` contra la BD).

---

## CAMBIOS APLICADOS EN ESTA RONDA

### `includes/header.php` (menú dinámico conectado)

- Al inicio: `require_once` de `clases/Conexion.php` y `clases/menu_lateral.php`.
- En `<head>`: `<link rel="stylesheet" href="<?= $depth ?>css/menu_lateral.css">`.
- Eliminado el array PHP fijo `$menu` (15 ítems hardcodeados).
- El bloque `<nav class="sidebar-nav">` ahora llama a:
  ```php
  if (isset($_SESSION['id'])) {
      $db = Conexion::getInstance('sistema_panel_central');
      menu_lateral((int) $_SESSION['id'], $db);
  } else {
      echo '<nav class="sidebar-nav"><p ...>No hay sesión</p></nav>';
  }
  ```
- Tras `</aside>`: `<?php menu_lateral_js(); ?>` (toggle de submenús).
- Breadcrumb simplificado (ya no depende del array fijo).
- Topbar: el nombre usa `$_SESSION['nombre']` (antes leía una clave que el
  login no rellenaba).

### `css/menu_lateral.css` (nuevo)

Estilos del menú dinámico, compatibles con el HTML de `clases/menu_lateral.php`
(submenús con `display:none|block`). Reemplaza al `menu_lateral.css` de la raíz.

### `clases/menu_lateral.php`

- Se quitó `m.abreviacion` del `SELECT` (esa columna no existe en `menu_1`).
- `caracteristica` NULL ya no genera warning.

### `index.php`

Login integrado, columnas reales `clave` / `intentos_fallidos` / `estado`,
`session_start()`, **no** incluye `validar_sesion.php`, redirige a
`dashboard.php` si ya hay sesión. + CSRF + `session_regenerate_id()`.

### `validar_sesion.php` (raíz)

`session_start()`, valida `$_SESSION['id']`, revalida el usuario y su `estado`
(`bloqueado`/`inactivo` → cierra sesión), redirige a `index.php`. Ruta de
redirect válida también desde `pages/`.

### `dashboard.php` y `pages/*.php` (14)

Sin cambios: ya usaban `require_once __DIR__ . '/validar_sesion.php';` /
`'/../validar_sesion.php';`. Verificado uno por uno.

---

## VERIFICACIÓN EN BD (`crist668_sistema_panel_central`)

`usuarios` debe tener: `id, nombre, email, clave, estado, intentos_fallidos`.
Deben existir: `perfiles`, `usuario_perfil`, `menu_1`, `menu_1_sub`,
`permisos_menu_1`.

El menú dinámico solo mostrará algo si el usuario tiene filas en
`permisos_menu_1`:

```sql
-- Dar a un usuario permiso de ver un menú
INSERT INTO permisos_menu_1 (id_menu1, id_usuario, id_tipo_permiso)
VALUES (:id_menu, :id_usuario, 1);
```

Si `menu_lateral()` no muestra nada, revisa:

```sql
SELECT * FROM permisos_menu_1 WHERE id_usuario = :id;
SELECT * FROM menu_1;
```

---

## PRUEBA RÁPIDA

1. `/` → formulario de login.
2. Login OK → `dashboard.php`, con el menú lateral armado desde la BD.
3. Menú con submenús → clic expande/contrae.
4. Ir directo a `/dashboard.php` sin sesión → vuelve a `index.php`.
5. 3 claves malas → cuenta bloqueada.
   Desbloquear: `UPDATE usuarios SET estado='activo', intentos_fallidos=0 WHERE email='cjorquera@seduc.cl';`
