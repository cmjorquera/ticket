Logica y esquema del modulo licenciamiento
=============================================

1. Objetivo del modulo
- El modulo `licenciamiento` permite administrar dos grandes grupos de informacion:
  - software y licencias
  - sitios web, apps y clientes
- La portada principal es `licenciamiento/index.php`.
- Las consultas rapidas se realizan desde `licenciamiento/consulta_nueva.php`.
- El dashboard general se muestra en `licenciamiento/dashboard.php`.

2. Flujo funcional del sistema
- `registrar_software.php` crea un nuevo registro de software o licencia.
- `editar_software.php` actualiza un software existente.
- `ver_software.php` muestra la ficha completa del software.
- `guardar_software.php` procesa el guardado por AJAX.
- `actualizar_software.php` procesa la actualizacion por AJAX.
- `eliminar_logico_software.php` desactiva el software sin borrarlo fisicamente.
- `registrar_sitio_web.php` crea sitios, apps o clientes.
- `editar_sitio_web.php` actualiza sitios, apps o clientes.
- `ver_sitio_web.php` muestra la ficha del sitio.
- `guardar_sitio_web.php` procesa el guardado por AJAX.
- `actualizar_sitio_web.php` procesa la actualizacion por AJAX.
- `eliminar_logico_sitio_web.php` desactiva el sitio sin borrarlo fisicamente.
- Los listados de la portada cargan por AJAX desde la carpeta `ajax`.

3. Logica de software y licencias
- Cada software se guarda en `software_catalogo`.
- Un software pertenece a un colegio mediante `id_colegio`.
- Puede tener un responsable del registro o seguimiento mediante `id_usuario_responsable`.
- Puede tener un tipo de usuario del sistema mediante `id_tipo_usuario`.
- Puede registrar nombre, version, cantidad de licencias, tipo de licenciamiento, pagado por, costo, moneda, proveedor, URL o referencia y observaciones.
- Puede registrar periodo de inicio y periodo de finalizacion de la licencia mediante:
  - `fecha_inicio_licencia`
  - `fecha_fin_licencia`
- Puede tener multiples filas asociadas en `software_datos_almacenamiento` para guardar personas o cuentas relacionadas.
- Puede asociarse a multiples datos sensibles mediante la tabla relacional `software_datos_sensibles_rel`.
- Puede tener trazabilidad en `software_historial`.

4. Logica de datos sensibles
- El catalogo maestro de datos sensibles se guarda en `inventario_software_datos_sensibles`.
- Ejemplos: `RUT`, `Nombre`, `Email`, `Direccion`, `Tarjeta de credito`.
- La relacion entre software y dato sensible se guarda en `software_datos_sensibles_rel`.
- Esto permite consultar que software o licencia utiliza un dato sensible determinado.
- La consulta se puede hacer desde `consulta_nueva.php` en la vista `dato_sensible`.

5. Logica de sitios web, apps y clientes
- Cada registro se guarda en `sitios_web_catalogo`.
- Un sitio tambien pertenece a un colegio mediante `id_colegio`.
- Puede tener responsable mediante `id_usuario_responsable`.
- El tipo puede ser:
  - `Web`
  - `App`
  - `Cliente`
- Tambien puede guardar URL, proveedor de hosting, estado y observaciones.

6. Consultas del modulo
- Consulta por software:
  - muestra en cuantos colegios existe un software
  - suma licencias
  - resume costo total
- Consulta por colegio:
  - muestra softwares, licencias y sitios asociados a un colegio
- Consulta por dato sensible:
  - muestra que software o licencia utiliza un dato sensible del catalogo
  - lista colegio, version, licencias, licenciamiento, periodo, responsable y costo

7. Esquema de tablas

7.1 `software_catalogo`
- Tabla principal de software y licencias.
- Campos relevantes:
  - `id_software`
  - `id_colegio`
  - `id_usuario_responsable`
  - `id_tipo_usuario`
  - `nombre_software`
  - `version_software`
  - `cantidad_licencias`
  - `tipo_licenciamiento`
  - `fecha_inicio_licencia`
  - `fecha_fin_licencia`
  - `pagado_por`
  - `costo`
  - `moneda`
  - `proveedor`
  - `url_referencia`
  - `observaciones`
  - `activo`
  - `created_at`
  - `updated_at`

7.2 `software_datos_almacenamiento`
- Guarda datos asociados al almacenamiento o custodia del software.
- Permite multiples filas por software.
- Campos practicos:
  - `nombre_contacto`
  - `rut_contacto`
  - `email_contacto`
  - `otros_datos`
  - `orden_dato`

7.3 `software_historial`
- Guarda la trazabilidad del software.
- Ejemplos de accion:
  - `creacion`
  - `actualizacion`
  - `baja_logica`

7.4 `inventario_software_tipo_usuario`
- Catalogo de tipos de usuario que usan el sistema.
- Ejemplos:
  - `Colaborador`
  - `Alumno`
  - `Proveedor`
  - `Otro`

7.5 `inventario_software_datos_sensibles`
- Catalogo maestro de datos sensibles.
- Se usa para poblar checkboxes y consultas.

7.6 `software_datos_sensibles_rel`
- Tabla puente entre software y datos sensibles.
- Permite relacion muchos software con muchos datos sensibles.

7.7 `sitios_web_catalogo`
- Tabla principal de sitios web, apps y clientes.
- Campos clave:
  - `nombre_sitio`
  - `tipo_sitio`
  - `url_sitio`
  - `proveedor_hosting`
  - `estado_sitio`
  - `observaciones`

8. Relaciones entre tablas
- `software_catalogo.id_colegio -> colegio.id_colegio`
- `software_datos_almacenamiento.id_software -> software_catalogo.id_software`
- `software_historial.id_software -> software_catalogo.id_software`
- `software_datos_sensibles_rel.id_software -> software_catalogo.id_software`
- `software_datos_sensibles_rel.id_dato_sensible -> inventario_software_datos_sensibles.id_dato_sensible`
- `sitios_web_catalogo.id_colegio -> colegio.id_colegio`

9. Archivo SQL principal
- El esquema sugerido del modulo se encuentra en:
  - `licenciamiento/views/sql_sugerido_inventario_software.sql`

10. Limpieza de datos para pruebas
- Si se necesita reiniciar solo los registros de software y licencias para pruebas, deben limpiarse las tablas hijas antes de la tabla principal.
- Tablas involucradas:
  - `software_datos_sensibles_rel`
  - `software_datos_almacenamiento`
  - `software_historial`
  - `software_catalogo`
- No es necesario borrar catalogos como:
  - `inventario_software_tipo_usuario`
  - `inventario_software_datos_sensibles`
