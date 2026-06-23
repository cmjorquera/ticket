@echo off
setlocal

cd /d "%~dp0"

echo ==========================================
echo Compilando Agente Inventario SEDUC
echo ==========================================

echo.
echo Limpiando compilaciones anteriores...
if exist build rmdir /s /q build
if exist dist rmdir /s /q dist
if exist seduc_inventario_agent.spec del /q seduc_inventario_agent.spec

echo.
echo Verificando Python...
set "PYTHON_CMD="

where py >nul 2>&1
if %errorlevel% equ 0 (
py -3 --version >nul 2>&1
if %errorlevel% equ 0 set "PYTHON_CMD=py -3"
)

if not defined PYTHON_CMD (
where python >nul 2>&1
if %errorlevel% equ 0 (
python --version >nul 2>&1
if %errorlevel% equ 0 set "PYTHON_CMD=python"
)
)

if not defined PYTHON_CMD (
echo ERROR: No se encontro una instalacion valida de Python.
echo Instala Python 3 para Windows y vuelve a ejecutar este archivo.
pause
exit /b 1
)

echo.
echo Instalando PyInstaller...
call %PYTHON_CMD% -m pip install --upgrade pip
if errorlevel 1 (
echo ERROR: No se pudo actualizar pip.
pause
exit /b 1
)

call %PYTHON_CMD% -m pip install pyinstaller
if errorlevel 1 (
echo ERROR: No se pudo instalar PyInstaller.
pause
exit /b 1
)

echo.
echo Compilando ejecutable...
call %PYTHON_CMD% -m PyInstaller --onefile --console --clean --name seduc_inventario_agent seduc_inventario_agent.py
if errorlevel 1 (
echo ERROR: Fallo la compilacion con PyInstaller.
pause
exit /b 1
)

echo.
echo Verificando ejecutable generado...
if not exist "dist\seduc_inventario_agent.exe" (
echo ERROR: No se genero dist\seduc_inventario_agent.exe
echo Revisa los errores anteriores de PyInstaller.
pause
exit /b 1
)

echo.
echo Copiando ejecutable final a carpeta agente...
copy /Y "dist\seduc_inventario_agent.exe" "seduc_inventario_agent.exe"

echo.
if exist "seduc_inventario_agent.exe" (
echo OK: El agente quedo listo en:
echo %~dp0seduc_inventario_agent.exe
) else (
echo ERROR: No se pudo copiar seduc_inventario_agent.exe
pause
exit /b 1
)

echo.
echo Proceso terminado correctamente.
pause
