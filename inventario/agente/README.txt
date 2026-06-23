SEDUC Inventario Agent v1.7
============================

DESCRIPCION
-----------
Agente para Windows que recopila automaticamente informacion tecnica del
equipo y genera un archivo JSON listo para importar en el Sistema Ticket.

El agente es un archivo .bat autocontenido:

  inventario/agente/seduc_inventario_agent.bat

No requiere Python, .exe ni ninguna instalacion adicional.
Funciona en Windows 7 SP1 / 8 / 10 / 11 con PowerShell 3.0+.


COMO USAR EL AGENTE
-------------------
1. En el Sistema Ticket, entrar a inventario/registrar_equipo.php.
2. Presionar "Descargar Agente".
3. Copiar seduc_inventario_agent.bat al equipo Windows que se quiere inventariar.
4. Hacer doble clic en el archivo.
   - Si aparece advertencia SmartScreen, clic en "Mas informacion" y luego
     "Ejecutar de todos modos".
5. Aceptar el cuadro de permisos de Administrador (UAC) que aparece automaticamente.
6. El agente generara en el Escritorio:

     inventario_NOMBREEQUIPO.json

7. Volver al sistema e importar ese JSON con "Importar Inventario Automatico".


DATOS QUE CAPTURA
-----------------
  - Nombre del equipo
  - Fabricante y modelo
  - Numero de serie BIOS
  - Procesador (nombre, fabricante, velocidad)
  - RAM total en GB
  - Discos y tipo de almacenamiento (SSD NVMe / SSD / HDD)
  - Version de Windows
  - Usuario Windows activo
  - Monitores detectados y resoluciones


DATOS QUE SE COMPLETAN MANUALMENTE
-----------------------------------
  - Colegio y ubicacion
  - Usuario asignado
  - Estado del equipo
  - Datos de compra
  - Fotos del equipo


ENDPOINT DE DESCARGA
---------------------
  inventario/ajax/descargar_agente.php

Sirve el .bat si existe. Si no existe, devuelve mensaje de error.
