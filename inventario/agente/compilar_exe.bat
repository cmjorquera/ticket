@echo off
setlocal

cd /d "%~dp0"

echo Instalando PyInstaller si no existe...
python -m pip install --upgrade pip
python -m pip install pyinstaller

echo.
echo Compilando SEDUC Inventario Agent...
python -m PyInstaller --onefile --console --name seduc_inventario_agent seduc_inventario_agent.py

echo.
echo Copiando ejecutable final...
copy /Y "dist\seduc_inventario_agent.exe" "seduc_inventario_agent.exe"

echo.
echo Listo.
echo El archivo final quedo en:
echo %~dp0seduc_inventario_agent.exe
echo.
pause
