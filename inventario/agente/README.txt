SEDUC Inventario Agent
======================

DESCRIPCION
-----------
Script Python que recopila automaticamente informacion de hardware del equipo
Windows y genera un archivo JSON listo para importar en el sistema SEDUC.

No requiere librerias externas. Usa PowerShell y ctypes (incluidos en Windows).


COMO COMPILAR EL EJECUTABLE (.exe)
------------------------------------
Requisitos:
  - Python 3.8 o superior instalado
  - pip instalado

Pasos:
  1. Instalar PyInstaller:
         pip install pyinstaller

  2. Compilar:
         pyinstaller --onefile --console seduc_inventario_agent.py

  3. El ejecutable quedara en:
         dist/seduc_inventario_agent.exe

  4. Copiar el ejecutable a esta carpeta:
         inventario/agente/seduc_inventario_agent.exe

  El sistema detectara automaticamente el .exe y lo ofrecera para descarga.
  Si no existe el .exe, se descarga el .py como alternativa.


COMO USAR
----------
  1. Descargar el ejecutable desde el sistema SEDUC
     (boton "Descargar Agente" en Registrar Equipo).

  2. Copiar el .exe al equipo que se desea inventariar.

  3. Ejecutar como Administrador para mayor precision de datos.

  4. Se generara un archivo:
         inventario_<NOMBRE_EQUIPO>.json

  5. Subir ese archivo al sistema SEDUC usando
     "Importar Inventario Automatico" en Registrar Equipo.


DATOS QUE CAPTURA
------------------
  - Nombre del equipo (hostname)
  - Fabricante y modelo del equipo
  - Numero de serie (BIOS)
  - Procesador (nombre, fabricante, velocidad)
  - RAM total en GB
  - Discos: modelo, capacidad, tipo (SSD/HDD/NVMe)
  - Sistema operativo Windows
  - Usuario de sesion actual
  - Monitores conectados: modelo y resolucion


DATOS QUE NO CAPTURA (se ingresan manualmente)
-------------------------------------------------
  - Colegio y ubicacion
  - Usuario asignado en el sistema
  - Estado del equipo
  - Datos de compra (valor, proveedor, factura, fecha)
  - Fotos del equipo


COMPATIBILIDAD
--------------
  Windows 7 / 8 / 10 / 11 (32 y 64 bits)
  Requiere PowerShell (incluido en Windows 7+)
