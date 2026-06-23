AGENTE SEDUC INVENTARIO v1.7
============================

El agente es un archivo .bat autocontenido.
No requiere Python, PyInstaller, compilacion ni ninguna dependencia externa.

ARCHIVO QUE DISTRIBUYE EL SISTEMA
----------------------------------
  inventario/agente/seduc_inventario_agent.bat

Este archivo ya esta listo en el repositorio.
No se compila ni se prepara de ninguna forma adicional.
Solo copiarlo al equipo objetivo y ejecutarlo.

COMO FUNCIONA INTERNAMENTE
--------------------------
La cabecera batch:
  1. Detecta si se ejecuta como Administrador (net session).
  2. Si NO es admin, se relanza a si mismo con permisos elevados (UAC).
  3. Si ES admin, extrae el bloque PowerShell incrustado (marcador ##PS1START##),
     lo escribe en %TEMP%\seduc_agent.ps1 con encoding UTF-8 y CRLF,
     lo ejecuta con powershell -File y lo borra al terminar.

El bloque PowerShell:
  - Recopila hardware via WMI (Win32_ComputerSystem, Win32_BIOS, Win32_Processor,
    Win32_PhysicalMemory, Win32_DiskDrive, Win32_OperatingSystem, WmiMonitorID,
    System.Windows.Forms.Screen).
  - Genera inventario_NOMBREEQUIPO.json en el Escritorio del usuario.

REGLAS DEL ARCHIVO .BAT
------------------------
  - Saltos de linea: CRLF (obligatorio para Windows).
  - Encoding: UTF-8 sin BOM.
  - Sin caracteres Unicode fuera de ASCII.
  - Sin comillas tipograficas. Solo comillas dobles ASCII en el bloque PS.
  - El marcador ##PS1START## aparece como linea propia al final de la
    seccion batch y una vez en el comando de extraccion (se usa LastIndexOf
    para encontrar el marcador real).

INSTRUCCIONES PARA EL TECNICO
------------------------------
  1. Descargar seduc_inventario_agent.bat desde el sistema SEDUC
     (boton "Descargar Agente" en la pantalla Registrar Equipo).
  2. Copiar el archivo al equipo que desea inventariar.
  3. Hacer doble clic en seduc_inventario_agent.bat.
     - Si Windows muestra advertencia SmartScreen (archivo de internet),
       hacer clic en "Mas informacion" y luego "Ejecutar de todos modos".
  4. El agente detecta automaticamente si necesita permisos de Administrador.
     Aceptar el cuadro UAC que aparece automaticamente.
  5. El agente recopila el hardware y genera:
       inventario_NOMBREEQUIPO.json
     en el Escritorio del usuario actual.
  6. Volver al sistema SEDUC, abrir Registrar Equipo y usar
     "Importar Inventario Automatico" para cargar el JSON.

DATOS QUE RECOPILA
------------------
  - nombre_equipo (hostname)
  - fabricante y modelo (Win32_ComputerSystem)
  - serial (Win32_BIOS)
  - procesador: nombre, fabricante, velocidad
  - ram_gb (Win32_PhysicalMemory)
  - almacenamiento[]: modelo, capacidad, tipo (SSD NVMe/SSD/HDD) por disco
  - windows (Win32_OperatingSystem.Caption)
  - usuario_windows ($env:USERNAME)
  - monitores[]: modelo y resolucion
  - generado_en (timestamp)
  - version_agente: "1.7"

PARA MODIFICAR EL AGENTE
-------------------------
Editar directamente inventario/agente/seduc_inventario_agent.bat.
El bloque PowerShell empieza en la linea que sigue al marcador ##PS1START##.
Asegurarse de guardar con CRLF y encoding UTF-8 sin BOM.
