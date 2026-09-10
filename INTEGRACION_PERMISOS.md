# Integración de `PermisosManager`

Incluye la clase después de iniciar o validar la sesión:

```php
require_once __DIR__ . '/PermisosManager.php';
$permisos = new PermisosManager((int) ($_SESSION['id'] ?? 0));
```

En archivos dentro de subcarpetas, ajusta únicamente la ruta, por ejemplo:

```php
require_once __DIR__ . '/../PermisosManager.php';
```

## Sustitución de lógica existente

- Reemplaza comparaciones manuales de perfil por `esSuperAdmin()`,
  `esJefeDepartamento()` o `getRol()`.
- Antes de mostrar un ticket, usa `puedeVer($idTicket)`.
- Antes de asignar un técnico, usa `puedeAsignar($idTecnico)`.
- Para botones generales usa `tienePermiso($accion)`, pero conserva la
  validación específica del recurso en el endpoint que procesa la acción.
- Usa `getDepartamento()` y consultas preparadas para filtrar las bandejas del
  jefe. `uso_permisos.php` contiene un ejemplo completo.

## Bases de datos

La clase abre dos conexiones mediante `clases/Conexion.php`:

- `sistema_panel_central` para usuarios, colegios y tickets.
- `logica_permisos` para jefaturas y departamentos habilitados.

`Conexion` añade el prefijo definido por el proyecto (`crist668_` en este
entorno). Si el prefijo de producción es distinto, debe cambiarse de forma
centralizada en la clase de conexión, no dentro del gestor.

El acceso falla de forma cerrada: un usuario inexistente o inactivo, una
jefatura inconsistente, o una consulta fallida no concede permisos.
