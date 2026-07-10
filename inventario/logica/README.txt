DOCUMENTACION OFICIAL - MODULO INVENTARIO PC
============================================

Esta carpeta es el unico punto oficial de documentacion tecnica del modulo
inventario dentro de SISTEMA TICKET.

El modulo Inventario PC administra equipos computacionales, sus componentes,
fotos, ubicaciones, estados, compras, movimientos, asignaciones e importacion
automatica desde agente. Tambien incluye una pestana separada para inventario
independiente de monitores.


QUE LEER SEGUN LO QUE NECESITO
------------------------------

QUIERO ENTENDER COMO FUNCIONA INVENTARIO

Leer:
  01_logica_general_inventario.txt


QUIERO SABER QUE TABLAS EXISTEN Y PARA QUE SIRVEN

Leer:
  02_distribucion_tablas.txt


QUIERO ENTENDER EL AGENTE DE INVENTARIO Y LA IMPORTACION AUTOMATICA

Leer:
  03_logica_agente_inventario.txt


QUIERO VER LA ESTRUCTURA REAL DE LAS TABLAS

Revisar:
  tablas/


QUIERO VER LAS RELACIONES ENTRE TABLAS

Abrir:
  esquema_tablas_inventario.html

Imagen de referencia:
  esquema_tablas_inventario.png


DOCUMENTOS OFICIALES
--------------------

01_logica_general_inventario.txt
  Logica funcional y tecnica general del modulo: layout, flujos, reglas,
  carga masiva, fotos, estados, ubicaciones, monitores independientes y
  decisiones vigentes.

02_distribucion_tablas.txt
  Distribucion y responsabilidad de las tablas del modulo:
  Inventario PC, monitores independientes, catalogos y tablas externas.

03_logica_agente_inventario.txt
  Funcionamiento del agente Windows, descarga, generacion del JSON y mapeo de
  datos al formulario de registro de PC.

esquema_tablas_inventario.html
  Diagrama oficial actualizado de entidades, campos y relaciones. No requiere
  internet para abrirse.

esquema_tablas_inventario.png
  Version PNG del esquema para referencia rapida.

arquitectura_inventario_SEDUC.pdf
  Documento tecnico de arquitectura del modulo.

tablas/
  Carpeta con las estructuras SQL oficiales exportadas para auditoria tecnica.
  Sirve para revisar campos reales, indices y relaciones documentadas.


REGLAS DE ORGANIZACION
----------------------

- inventario/logica/ contiene la documentacion oficial del modulo.
- Las estructuras SQL oficiales estan en inventario/logica/tablas/.
- inventario/sql/ contiene scripts SQL, migraciones, indices y scripts de
  instalacion o mantenimiento. No contiene documentacion funcional general.
- No mantener archivos legacy de documentacion dentro de esta carpeta.
- Toda nueva decision de estructura o logica debe quedar documentada en
  01_logica_general_inventario.txt o en el documento especifico que corresponda.


DIFERENCIA IMPORTANTE: equipo_monitor vs monitores
--------------------------------------------------

equipo_monitor
  - Monitores fisicamente asociados a un PC especifico.
  - Se administran dentro del formulario de PC.
  - Esta EN REVISION tecnica.
  - No es tabla puente real en su diseno actual.
  - Duplica datos tecnicos que deberian vivir en monitores si el monitor es un activo propio.
  - Sigue activa y NO debe eliminarse todavia porque codigo activo depende de ella.
  - No representa el inventario independiente de monitores.

monitores
  - Monitores comprados o administrados de forma independiente.
  - Se administran desde la pestana "Monitores" en inventario/index.php.
  - Tienen fotos, compra, movimientos e historial de asignacion propios.


ELIMINACION LOGICA
------------------

equipos.eliminado = 0   -> PC visible
equipos.eliminado = 1   -> PC eliminado logicamente

monitores.eliminado = 0 -> monitor visible
monitores.eliminado = 1 -> monitor eliminado logicamente

El boton basurero marca eliminado = 1. No cambia el estado operativo.
El estado operativo se maneja con equipos.id_estado o monitores.id_estado.


HISTORIAL OFICIAL DE EQUIPOS PC
-------------------------------

Las tablas oficiales de historial para equipos PC son:

- equipo_movimiento
- equipo_estado_historial
- equipo_asignacion_historial

La tabla equipo_historial no existe en la base real y no forma parte del modelo
oficial actual del modulo.


TABLAS EN REVISION
------------------

equipo_monitor queda en auditoria tecnica hasta decidir si se conserva, migra,
refactoriza o elimina en una fase posterior. No modificar ni eliminar sin una
migracion y ajuste funcional planificado.
