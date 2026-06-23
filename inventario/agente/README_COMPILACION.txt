AGENTE SEDUC INVENTARIO v1.1
============================

El agente ahora es un archivo .bat nativo de Windows.
No requiere Python, PyInstaller ni ninguna dependencia externa.

ARCHIVO QUE DESCARGA EL TECNICO
--------------------------------
  inventario/agente/seduc_inventario_agent.bat

Este archivo ya esta listo. No necesita compilacion ni preparacion previa.
Solo copiarlo al equipo objetivo y ejecutarlo.

COMO FUNCIONA
-------------
El .bat incrusta un script PowerShell que:
  - Se ejecuta con las herramientas nativas de Windows (WMI, PowerShell)
  - No modifica el equipo ni accede a la red
  - Genera inventario_NOMBREEQUIPO.json en la misma carpeta donde se ejecuto

COMPATIBILIDAD
--------------
  Windows 7 SP1 / 8 / 8.1 / 10 / 11
  Requiere PowerShell 3.0 o superior (incluido por defecto desde Windows 8;
  en Windows 7 SP1 se instala automaticamente con las actualizaciones).

INSTRUCCIONES PARA EL TECNICO
------------------------------
  1. Descargar seduc_inventario_agent.bat desde el sistema SEDUC
     (boton "Descargar Agente" en la pantalla Registrar Equipo).
  2. Copiar el archivo al equipo que desea inventariar.
  3. Hacer doble clic en seduc_inventario_agent.bat.
     - Si Windows muestra advertencia SmartScreen (archivo de internet),
       hacer clic en "Mas informacion" y luego "Ejecutar de todos modos".
  4. El agente detecta automaticamente si necesita permisos de Administrador.
     Si no los tiene, vuelve a lanzarse pidiendo elevacion via cuadro UAC.
     Aceptar el cuadro de permisos para continuar.
     (Estos permisos son necesarios para leer datos de hardware por WMI.)
  5. El agente recopila el hardware y genera:
       inventario_NOMBREEQUIPO.json
     en la misma carpeta donde se ejecuto el .bat.
  6. Volver al sistema SEDUC, abrir Registrar Equipo y usar
     "Importar Inventario Automatico" para cargar el JSON.

DATOS QUE RECOPILA
------------------
  - nombre_equipo (hostname)
  - fabricante y modelo (Win32_ComputerSystem)
  - serial (Win32_BIOS)
  - procesador: nombre, fabricante, velocidad
  - ram_gb (Win32_PhysicalMemory)
  - almacenamiento[]: modelo, capacidad, tipo (SSD/HDD/NVMe) por disco
  - windows (Win32_OperatingSystem.Caption)
  - usuario_windows ($env:USERNAME)
  - monitores[]: modelo y resolucion
  - generado_en (timestamp)
  - version_agente: "1.1"

REFERENCIA PARA DESARROLLADORES
--------------------------------
El script PowerShell esta incrustado directamente en el .bat.
El mecanismo es:
  - La cabecera batch define las variables de entorno y lanza PowerShell.
  - PowerShell lee el propio .bat como texto y ejecuta todo lo que
    aparece despues del marcador ::__SEDUC_PS__
  - No se genera ni se necesita ningun archivo .ps1 temporal en disco.

Para actualizar la logica del agente, editar la seccion PowerShell
dentro de seduc_inventario_agent.bat a partir del marcador ::__SEDUC_PS__

FUENTE PYTHON (referencia historica)
-------------------------------------
  inventario/agente/seduc_inventario_agent.py
  inventario/agente/compilar_exe.bat

Estos archivos se conservan como referencia historica.
El sistema ya no usa el .py ni el .exe para distribuir el agente.
El endpoint inventario/ajax/descargar_agente.php sirve el .bat primero.
Si el .bat no existe pero hay un .exe, sirve el .exe como respaldo.
