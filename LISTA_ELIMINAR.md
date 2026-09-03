# LISTA DE ARCHIVOS A ELIMINAR

Revisado el 2026-09-03 sobre `c:\Users\cmjor\Documents\sistema_base`.

---

## ✅ Ya eliminados (verificado — no existen)

Nada que hacer con estos, se confirman fuera del proyecto:

| Elemento | Motivo |
|---|---|
| `login.php` | El login vive integrado en `index.php`. |
| `includes/validar_sesion.php` | Duplicado del `validar_sesion.php` de la raíz. |
| `login_FINAL.php` | Archivo temporal de iteración. |
| `validar_sesion_FINAL.php` | Archivo temporal de iteración. |
| `resetear_clave.php` | Utilidad temporal. |
| `limpiar_validar_sesion.sh` | Script temporal (ya cumplió su función). |
| `sistema_base_ARREGLADO/` | Carpeta temporal / copia de trabajo. |
| `api/login.php` | Login antiguo (usaba `clases/Usuario.php` con esquema viejo). |
| `sistema_base.zip` | El ZIP original ya extraído. |

---

## ❌ Pendientes de eliminar (todavía existen)

| Ruta | Motivo | Riesgo al borrar |
|---|---|---|
| `menu_lateral.php` (raíz) | **Duplicado.** La versión válida y en uso es `clases/menu_lateral.php`. La de la raíz es una copia más antigua de otra iteración. | Ninguno: nada la incluye (`require`/`include`). |
| `README_SISTEMA.md` | Documentación mía desactualizada: menciona `password`, `ultima_conexion`, `login.php` y una migración que ya no aplica. Reemplazada por `VERIFICACION.md`. | Ninguno: es solo documentación. |

Comando (desde la raíz del proyecto):

```bash
git rm menu_lateral.php README_SISTEMA.md
# o sin git:
rm menu_lateral.php README_SISTEMA.md
```

---

## ⚠️ Revisar por separado (NO borrar sin decidir)

Código del esqueleto viejo que **todavía está referenciado** en alguna parte.
No forma parte del flujo nuevo (index → validar_sesion → dashboard/pages), pero
borrarlo rompe otras cosas:

| Ruta | Quién lo usa | Problema |
|---|---|---|
| `clases/Usuario.php` | `api/usuarios.php` | Usa columnas viejas: `correo`, `activo`, `rol`, `password`. Roto contra el esquema real (`email`, `estado`, `clave`). |
| `api/usuarios.php`, `api/modulos.php` | `dashboard.php` (fetch de estadísticas) | `api/usuarios.php` hereda el problema de `Usuario.php`. Si borras estos, el dashboard deja de mostrar los contadores (no rompe la página). |
| `helpers/funciones.php` | Nadie del flujo nuevo | Define `soloAdmin()` que mira `$_SESSION['tipo']`, variable que ya nadie setea. |
| `middlewares/solo_admin.php`, `middlewares/solo_autenticado.php` | Nadie | Igual que arriba: dependen de `$_SESSION['tipo']`. |
| `clases/Session.php` | `includes/header.php` | Se usa. NO borrar mientras `header.php` lo incluya. |

Recomendación: dejarlos por ahora. Cuando migres `api/usuarios.php` al esquema
real (`email`/`estado`/`clave`) podrás decidir si `clases/Usuario.php` sigue
teniendo sentido o lo reemplazas.
