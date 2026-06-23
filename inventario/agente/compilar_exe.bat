@echo off
setlocal

cd /d "%~dp0"

echo Compilando SEDUC Inventario Agent...
echo.

pyinstaller --onefile --console --name seduc_inventario_agent seduc_inventario_agent.py

echo.
echo Resultado esperado:
echo   %~dp0dist\seduc_inventario_agent.exe
echo.
echo Para que el Sistema Ticket descargue el ejecutable, copie:
echo   %~dp0dist\seduc_inventario_agent.exe
echo a:
echo   %~dp0seduc_inventario_agent.exe
echo.

pause
