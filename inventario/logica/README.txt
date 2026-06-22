DOCUMENTACION TECNICA — MODULO INVENTARIO
==========================================

Esta carpeta contiene la documentacion funcional y tecnica del modulo inventario.

ARCHIVOS
--------

logica.txt
  Logica completa del modulo: layout, reglas de assets, flujos, convenciones,
  descripcion de metodos, estilos CSS y decisiones tecnicas tomadas.
  Referencia principal para entender como funciona el modulo.

distribucion_de_tablas.txt
  Distribucion y descripcion de todas las tablas de base de datos del modulo,
  organizadas en tres secciones:
    A) Inventario PC
    B) Inventario Monitores independientes
    C) Catalogos compartidos

esquema_tablas_inventario.html
  Esquema visual oficial de las tablas. Version actualizada.
  Muestra entidades, campos y relaciones en formato HTML con CSS propio.
  NO requiere internet para abrirse.

esquema_tablas_inventario_legacy.html
  Version antigua del esquema (diagrama Mermaid via CDN).
  Se conserva solo como respaldo historico.
  No refleja el estado actual del modulo.
  NO usar como referencia.

esquema_tablas_inventario.png
  Imagen del esquema en formato PNG.
  Puede estar desactualizada respecto al HTML oficial.

REGLAS DE ESTA CARPETA
----------------------

- La carpeta inventario/sql/ queda reservada SOLO para scripts .sql.
- El esquema oficial de tablas es esquema_tablas_inventario.html.
- El archivo legacy se conserva solo como respaldo historico.
- No eliminar archivos antiguos hasta validar que todo este actualizado.
- Toda nueva decision de estructura o logica del modulo inventario
  debe quedar documentada en logica.txt.

DIFERENCIA IMPORTANTE: equipo_monitor vs monitores
---------------------------------------------------

equipo_monitor (tabla legacy, modulo PC)
  - Monitores fisicamente asociados a un PC especifico.
  - Se administran dentro del formulario de PC.
  - No confundir con el modulo de monitores independientes.
  - NO eliminar esta tabla.

monitores (tabla nueva, modulo Monitores independientes)
  - Monitores comprados de forma independiente, sin relacion a un PC.
  - Se administran desde la pestana "Monitores" en inventario/index.php.
  - Tienen fotos, compra, movimientos e historial de asignacion propios.

ELIMINACION LOGICA
------------------

equipos.eliminado = 0   → PC visible
equipos.eliminado = 1   → PC eliminado logicamente (no aparece en listados)

monitores.eliminado = 0 → Monitor visible
monitores.eliminado = 1 → Monitor eliminado logicamente (no aparece en listados)

El boton basurero SOLO marca eliminado = 1. NO cambia el estado operativo.
El estado operativo (Activo, Bodega, Reparacion, Baja, Prestado) se
maneja con equipos.id_estado o monitores.id_estado.
