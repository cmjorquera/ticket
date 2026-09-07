# 🗑️ ARCHIVOS A ELIMINAR

Borra estos archivos/carpetas:

❌ `login.php` (el login está en `index.php`)
❌ `includes/validar_sesion.php` (duplicado, usar el de la raíz)
❌ `login_FINAL.php`
❌ `validar_sesion_FINAL.php`
❌ `resetear_clave.php`
❌ `limpiar_validar_sesion.sh`
❌ `sistema_base_ARREGLADO/` (carpeta completa)
❌ `api/login.php` (código antiguo)
❌ `menu_lateral.php` (raíz — duplicado; el bueno es `clases/menu_lateral.php`)
❌ `menu_lateral.css` (raíz — reemplazado por `css/menu_lateral.css`)
❌ `README_SISTEMA.md` (doc antigua — reemplazada por `INSTRUCCIONES_FINALES.md`)
❌ `sistema_base.zip` (ya extraído)

Después de eliminar estos, tu sistema estará limpio y funcional.

---

## Estado (verificado 2026-09-03)

Ya **no existen** (se borraron en pasos previos):
`login.php`, `includes/validar_sesion.php`, `login_FINAL.php`,
`validar_sesion_FINAL.php`, `resetear_clave.php`, `limpiar_validar_sesion.sh`,
`sistema_base_ARREGLADO/`, `api/login.php`, `sistema_base.zip`,
`menu_lateral.php` (raíz), `menu_lateral.css` (raíz), `README_SISTEMA.md`.

➡️ **No queda nada de esta lista por borrar.** Si trabajas sobre una copia en
cPanel, revisa que tampoco estén allí.

---

## ⚠️ Revisar por separado (NO borrar sin decidir)

Esqueleto viejo, aún referenciado por el dashboard:

| Ruta | Lo usa | Nota |
|---|---|---|
| `clases/Usuario.php` | `api/usuarios.php` | Columnas viejas (`correo`, `activo`, `rol`, `password`). |
| `api/usuarios.php`, `api/modulos.php` | `dashboard.php` (contadores) | Borrarlos solo quita las estadísticas del dashboard. |
| `helpers/funciones.php`, `middlewares/*` | Nadie del flujo nuevo | Dependen de `$_SESSION['tipo']` (ya nadie lo setea). |
| `clases/Session.php` | `includes/header.php` | En uso. NO borrar. |
