# Integración de `PermisosManager`

## Uso básico

Después de iniciar o validar la sesión:

```php
require_once __DIR__ . '/PermisosManager.php';
$permisos = new PermisosManager((int) ($_SESSION['id'] ?? 0));
```

Desde una subcarpeta usa `__DIR__ . '/../PermisosManager.php'`. Consulta
`archivo_ejemplo_uso.php` para ejemplos de dashboard, listados y endpoints.

## Perfiles múltiples

`getPerfiles()` retorna todos los perfiles normalizados, por ejemplo
`['usuario', 'tecnico', 'admin_area']`. Los permisos son acumulativos: una
persona con perfiles `tecnico` y `admin_area` conserva las capacidades de
ambos. `getRolPrincipal()` sólo escoge la vista principal de la interfaz:

1. `SUPERADMIN`
2. `JEFE_DEPARTAMENTO`
3. `ADMIN_COLEGIO`
4. `TECNICO`
5. `USUARIO`

Ser jefe requiere simultáneamente el perfil `admin_area` y una jefatura activa.

## Flujo de autorización

1. Usa `tienePermiso()` para decidir si muestras una acción general.
2. En el endpoint, repite la autorización con `puedeVer()` o
   `puedeAsignar()` para validar el recurso concreto.
3. Usa consultas preparadas al construir el listado del rol.

Los alcances son: superadmin global; jefe por departamento y colegio; admin de
colegio por colegio; técnico por tickets asignados; usuario por tickets propios.

## Conexiones

Sin configuración adicional, la clase utiliza `clases/Conexion.php` y abre
`crist668_sistema_panel_central` y `crist668_logica_permisos`, que es el prefijo
real del proyecto. El requerimiento menciona `crisf668_`; si corresponde a otro
ambiente, pásalo mediante los DSN opcionales:

```php
$permisos = new PermisosManager($idUsuario, [
    'principal' => [
        'dsn' => 'mysql:host=localhost;dbname=crisf668_sistema_panel_central;charset=utf8mb4',
        'usuario' => 'usuario_principal',
        'password' => 'clave_principal',
    ],
    'permisos' => [
        'dsn' => 'mysql:host=localhost;dbname=crisf668_logica_permisos;charset=utf8mb4',
        'usuario' => 'usuario_permisos',
        'password' => 'clave_permisos',
    ],
]);
```

No guardes claves reales en páginas públicas ni en el repositorio. Ante una
conexión fallida, usuario inválido o consulta inconsistente, el gestor retorna
`false`, `null` o un arreglo vacío y registra el detalle solamente en el log.
