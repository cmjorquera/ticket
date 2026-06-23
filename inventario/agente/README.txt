SEDUC Inventario Agent
======================

DESCRIPCION
-----------
Agente para Windows que recopila automaticamente informacion tecnica del
equipo y genera un archivo JSON listo para importar en el Sistema Ticket.

El archivo fuente es:

  inventario/agente/seduc_inventario_agent.py

El agente no usa librerias externas innecesarias. Trabaja con modulos
estandar de Python y herramientas incluidas en Windows:

  - ctypes
  - json
  - os
  - socket
  - subprocess
  - sys
  - datetime
  - PowerShell / WMI de Windows


POR QUE SE COMPILA A .EXE
-------------------------
Los computadores de los colegios no deben depender de tener Python instalado.
Por eso el agente se distribuye como ejecutable Windows:

  seduc_inventario_agent.exe

El tecnico descarga ese .exe desde el sistema, lo copia al equipo que desea
inventariar y lo ejecuta directamente.


COMO GENERAR EL .EXE CON PYINSTALLER
------------------------------------
Requisitos en el computador donde se compila:

  - Windows
  - Python 3 instalado
  - pip disponible

Desde esta carpeta:

  inventario/agente/

ejecutar:

  pip install pyinstaller

  pyinstaller --onefile --console --name seduc_inventario_agent seduc_inventario_agent.py

El resultado final queda en:

  inventario/agente/dist/seduc_inventario_agent.exe

Despues de compilar, copiar el ejecutable final a:

  inventario/agente/seduc_inventario_agent.exe

Ese es el archivo que descarga el sistema desde:

  inventario/ajax/descargar_agente.php

Si el .exe no existe, el sistema no entrega ningun respaldo al usuario final y
muestra un mensaje para contactar al administrador.


COMPILACION RAPIDA EN WINDOWS
-----------------------------
Tambien se puede usar:

  inventario/agente/compilar_exe.bat

Ese archivo ejecuta PyInstaller con el nombre correcto. Al terminar, verificar:

  inventario/agente/dist/seduc_inventario_agent.exe

Luego copiarlo manualmente a:

  inventario/agente/seduc_inventario_agent.exe


COMO USAR EL AGENTE
-------------------
1. En el Sistema Ticket, entrar a inventario/registrar_equipo.php.
2. Presionar "Descargar Agente".
3. Copiar seduc_inventario_agent.exe al equipo Windows que se quiere inventariar.
4. Ejecutarlo como Administrador.
5. El agente generara:

     inventario_NOMBREEQUIPO.json

6. Volver al sistema e importar ese JSON con "Importar Inventario Automatico".


DATOS QUE CAPTURA
-----------------
  - Nombre del equipo
  - Fabricante y modelo
  - Numero de serie BIOS
  - Procesador
  - RAM total
  - Discos y tipo de almacenamiento
  - Version de Windows
  - Usuario Windows activo
  - Monitores detectados y resoluciones


DATOS QUE SE COMPLETAN MANUALMENTE
----------------------------------
  - Colegio y ubicacion
  - Usuario asignado
  - Estado del equipo
  - Datos de compra
  - Fotos del equipo
