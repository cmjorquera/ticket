COMPILACION DEL AGENTE SEDUC
============================

El agente se desarrolla en Python, pero se distribuye a usuarios finales solo
como ejecutable Windows.

Desde la carpeta:

  inventario/agente/

instalar PyInstaller:

  pip install pyinstaller

Generar el ejecutable:

  pyinstaller --onefile --console --name seduc_inventario_agent seduc_inventario_agent.py

Una vez compilado, copiar:

  dist/seduc_inventario_agent.exe

a:

  inventario/agente/seduc_inventario_agent.exe

Ese archivo es el unico agente que descarga el Sistema Ticket desde:

  inventario/ajax/descargar_agente.php
