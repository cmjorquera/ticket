# CHECKLIST DE VERIFICACIÓN DEL SISTEMA

Revisión completa · 2026-09-03 · BD `crist668_sistema_panel_central`

---

## Resumen

| Tarea | Estado |
|---|---|
| 1. `index.php` (login integrado) | 🔧 Corregido |
| 2. `validar_sesion.php` (raíz) | 🔧 Corregido |
| 3. `dashboard.php` | ✅ OK (1 aviso menor) |
| 4. `pages/*.php` (14 archivos) | ✅ OK |
| 5. `clases/Conexion.php` | ✅ OK |
| 6. `clases/menu_lateral.php` | 🔧 Corregido (no integrado aún) |
| 7. Lista de eliminación | ✅ Ver `LISTA_ELIMINAR.md` |

---

## TAREA 1 — `index.php`

| Chequeo | Resultado |
|---|---|
| Tiene `session_start()` | ✅ |
| **NO** incluye `validar_sesion.php` (evita loop) | ✅ |
| Procesa login por `POST` | ✅ |
| Columna de contraseña = `clave` (`password_verify($clave, $usuario['clave'])`) | ✅ corregido |
| Columna de intentos = `intentos_fallidos` | ✅ corregido |
| No usa `ultima_conexion` | ✅ |
| 3 intentos fallidos → `estado = 'bloqueado'` | ✅ |
| Rechaza `estado` `bloqueado` / `inactivo` | ✅ |
| Si ya hay sesión (`$_SESSION['id']`) → `dashboard.php` | ✅ |
| Crea `$_SESSION['id','nombre','email','perfil','perfiles']` | ✅ |
| Extra: token CSRF + `session_regenerate_id(true)` | ✅ |

**Antes** estaba como stub de 277 bytes que redirigía a `login.php` (archivo ya
borrado) → la raíz del sitio daba 404. **Ahora** es el login completo.

---

## TAREA 2 — `validar_sesion.php` (raíz)

| Chequeo | Resultado |
|---|---|
| Existe en la raíz | ✅ |
| Tiene `session_start()` | ✅ |
| **NO** se incluye a sí mismo ni se usa en `index.php` | ✅ |
| Valida que exista `$_SESSION['id']` | ✅ |
| Valida que el usuario exista en `usuarios` | ✅ |
| Bloquea `estado` `bloqueado` / `inactivo` | ✅ |
| Redirige a **`index.php`** si la sesión es inválida | ✅ corregido (antes iba a `login.php`, borrado) |
| Redirect válido desde subcarpetas (`pages/`) | ✅ usa `BASE_URL` o cálculo de profundidad |
| Columnas reales: `id, nombre, email, estado` | ✅ |

---

## TAREA 3 — `dashboard.php`

| Chequeo | Resultado |
|---|---|
| Incluye `require_once __DIR__ . '/validar_sesion.php';` (raíz) | ✅ |
| No hay referencia a `includes/validar_sesion.php` | ✅ |
| Muestra contenido (cards, sparklines, footer) | ✅ |

⚠️ **Aviso menor (no bloquea):** línea ~71 usa
`Session::get('usuario_nombre')`, pero el login guarda `$_SESSION['nombre']`.
Se mostrará "Bienvenido, " vacío y, en PHP 8.1+, un *deprecation warning* por
`htmlspecialchars(null)`. Cambiar a:

```php
<h3>Bienvenido, <?= htmlspecialchars($_SESSION['nombre'] ?? '') ?></h3>
```

---

## TAREA 4 — `pages/*.php`

14 archivos, **todos** con la referencia correcta:

```php
require_once __DIR__ . '/../validar_sesion.php';
```

`beneficios, capsulas, colegios, componentes, configuracion, contactos,
contenedores, estados, eventos, informa, modulos, perfil, permisos, usuarios`
→ ✅ ninguno apunta a `../includes/validar_sesion.php`.

---

## TAREA 5 — `clases/Conexion.php`

| Chequeo | Resultado |
|---|---|
| Usa PDO | ✅ |
| DSN → `crist668_sistema_panel_central` (prefijo `crist668_` + nombre corto) | ✅ |
| `ERRMODE_EXCEPTION`, `FETCH_ASSOC`, `EMULATE_PREPARES = false` | ✅ |
| `getInstance($bd)` (singleton) | ✅ |
| `fetchOne($sql, $params)` → `array|false` | ✅ |
| `fetchAll($sql, $params)` → `array` | ✅ |
| `execute($sql, $params)` → `int` (rowCount) | ✅ |
| Extra: `getPDO()`, `query()`, `lastInsertId()` | ✅ |

Sin cambios. Correcto.

---

## TAREA 6 — `clases/menu_lateral.php`

| Chequeo | Resultado |
|---|---|
| Lee `permisos_menu_1` filtrando por `id_usuario` | ✅ |
| `JOIN menu_1` para traer solo menús permitidos | ✅ |
| Trae submenús de `menu_1_sub` (un solo query, `IN (...)`) | ✅ |
| Menú con submenús → expandible; sin submenús → enlace directo | ✅ |
| Columnas de `menu_1` correctas | ✅ **corregido**: se quitó `m.abreviacion` (no existe en el esquema real) |
| `caracteristica` NULL no rompe | ✅ corregido (`?? ''`) |
| Ordena por `CAST(orden AS UNSIGNED)` | ✅ |
| `verificar_permiso_menu($id_usuario, $id_menu, $db)` | ✅ |
| `menu_lateral_js()` (toggle submenús) | ✅ |

**Bug que tenía:** el `SELECT` pedía `m.abreviacion`; esa columna no existe
(`menu_1` = `id_menu, nombre, archivo, icono, caracteristica, orden`). La
consulta lanzaba `SQLSTATE 42S22 Unknown column`. Ya corregido.

⚠️ **No está integrado todavía.** `includes/header.php` sigue pintando un menú
con un array PHP **fijo**. Para usar el menú dinámico hay que editar
`includes/header.php`:

```php
// arriba del archivo
require_once __DIR__ . '/../clases/menu_lateral.php';
require_once __DIR__ . '/../clases/Conexion.php';

// donde hoy está el <nav> con el array fijo:
menu_lateral($_SESSION['id'], Conexion::getInstance('sistema_panel_central'));

// y antes de </body> (una vez):
menu_lateral_js();
```

Y cargar `menu_lateral.css` en el `<head>`. Dímelo si quieres que lo integre.

---

## TAREA 7 — Eliminación

Ver **`LISTA_ELIMINAR.md`**. Resumen:

- Ya borrados: `login.php`, `includes/validar_sesion.php`, `*_FINAL.php`,
  `resetear_clave.php`, `limpiar_validar_sesion.sh`, `sistema_base_ARREGLADO/`,
  `api/login.php`, `sistema_base.zip`.
- **Pendientes:** `menu_lateral.php` (raíz, duplicado) y `README_SISTEMA.md`
  (doc vieja).
- Revisar aparte: `clases/Usuario.php`, `api/usuarios.php`, `helpers/`,
  `middlewares/` (esquema viejo, aún referenciados por el dashboard).

---

## Estructura final esperada

```
index.php                 ← entrada + login (clave / intentos_fallidos / estado)
validar_sesion.php        ← guard (raíz), redirige a index.php
dashboard.php             ← require '/validar_sesion.php'
config.php
menu_lateral.css          ← estilos del menú dinámico
clases/
  Conexion.php            ← PDO, getInstance/fetchOne/fetchAll/execute
  Session.php
  menu_lateral.php        ← menú dinámico (permisos_menu_1 + menu_1 + menu_1_sub)
  Usuario.php             ← (viejo, revisar)
includes/
  header.php  footer.php  cerrar_sesion.php
pages/
  *.php  ← 14, todos con require '/../validar_sesion.php'
api/
  usuarios.php  modulos.php   ← (viejos, revisar)
```

---

## INSTRUCCIONES FINALES DE IMPLEMENTACIÓN

1. **Subir al servidor** estos archivos corregidos:
   `index.php`, `validar_sesion.php`, `clases/menu_lateral.php`.
2. **Borrar** en el servidor: `menu_lateral.php` (raíz) y `README_SISTEMA.md`.
   Confirmar que ya no están: `login.php`, `includes/validar_sesion.php`,
   `sistema_base_ARREGLADO/`, `api/login.php`, `*_FINAL.php`,
   `resetear_clave.php`, `limpiar_validar_sesion.sh`.
3. **Verificar la BD** `crist668_sistema_panel_central`:
   - `usuarios` tiene: `id, nombre, email, clave, estado, intentos_fallidos`.
   - Existen: `perfiles`, `usuario_perfil`, `menu_1`, `menu_1_sub`,
     `permisos_menu_1`.
4. **Crear un usuario de prueba** (si no hay):
   ```sql
   INSERT INTO usuarios (nombre, email, clave, estado, intentos_fallidos)
   VALUES ('Admin', 'admin@seduc.cl',
           '$2y$10$...hash bcrypt...', 'activo', 0);
   ```
   Generar el hash: `php -r "echo password_hash('TU_CLAVE', PASSWORD_BCRYPT);"`
5. **Probar el flujo:**
   - `/` o `/index.php` → formulario de login.
   - Login OK → `dashboard.php`.
   - Ir directo a `/dashboard.php` sin sesión → vuelve a `index.php`.
   - 3 claves malas → cuenta `bloqueado`, mensaje de bloqueo.
   - Desbloquear:
     `UPDATE usuarios SET estado='activo', intentos_fallidos=0 WHERE email=?;`
6. **(Opcional)** Integrar el menú dinámico en `includes/header.php` (ver Tarea 6)
   y corregir el saludo del `dashboard.php` (ver Tarea 3).
