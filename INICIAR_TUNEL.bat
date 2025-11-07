@echo off
title Fashion Store - Tunel HTTP Permanente
color 0A

echo.
echo ============================================================
echo    FASHION STORE - INICIANDO TUNEL HTTP
echo ============================================================
echo.

REM Verificar que XAMPP este corriendo
echo [1/3] Verificando XAMPP...
tasklist /FI "IMAGENAME eq httpd.exe" 2>NUL | find /I /N "httpd.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo     ✓ Apache esta corriendo
) else (
    echo     ✗ Apache NO esta corriendo
    echo.
    echo     Por favor inicia XAMPP primero!
    echo.
    pause
    exit /b
)

echo.
echo [2/3] Verificando Python...
python --version >nul 2>&1
if %errorlevel% neq 0 (
    echo     ✗ Python no esta instalado
    pause
    exit /b
)
echo     ✓ Python instalado

echo.
echo [3/3] Iniciando tunel...
echo.

REM Ejecutar script Python
C:\Users\julio\AppData\Local\Programs\Python\Python311\python.exe start_tunnel.py

pause
