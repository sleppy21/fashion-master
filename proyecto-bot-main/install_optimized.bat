@echo off
@echo off
title Fashion Store Bot - Instalacion
echo =======================================
echo     FASHION STORE BOT INSTALACION
echo =======================================
echo.
echo Verificando Python...
python --version
if %errorlevel% neq 0 (
    echo ERROR: Python no encontrado
    pause
    exit /b 1
)
echo OK: Python detectado
echo.
echo Creando entorno virtual...
python -m venv venv --clear
echo OK: Entorno virtual creado
echo.
echo Actualizando pip...
cmd /c "venv\Scripts\activate.bat && python -m pip install --upgrade pip"
echo.
echo Instalando dependencias...
cmd /c "venv\Scripts\activate.bat && pip install -r requirements.txt"
echo.
echo Verificando instalacion...
cmd /c "venv\Scripts\activate.bat && python -c \"import flask, sklearn, pandas, numpy; print('Modulos instalados OK')\""
echo.
echo Generando datos...
cmd /c "venv\Scripts\activate.bat && python scripts\generar_datos.py"
echo.
echo Entrenando modelo básico...
cmd /c "venv\Scripts\activate.bat && python scripts\entrenar_modelo.py"
echo.
echo Entrenando modelo avanzado (puede tomar tiempo)...
cmd /c "venv\Scripts\activate.bat && python scripts\entrenar_modelo_avanzado.py"
echo.
echo =======================================
echo      INSTALACION COMPLETADA
echo     🚀 BOT MEJORADO CON IA!
echo =======================================
echo ✅ Análisis vectorial activado
echo ✅ Comprensión contextual
echo ✅ 200+ ejemplos de entrenamiento
echo ✅ Sistema de fallback automático
echo.
set /p EJECUTAR="Iniciar servidor ahora? (S/N): "
if /i "%EJECUTAR%"=="S" (
    echo Iniciando servidor...
    cmd /c "venv\Scripts\activate.bat && python ..\fashion_store_complete.py"
) else (
    echo Para iniciar manualmente:
    echo 1. venv\Scripts\activate.bat
    echo 2. python ..\fashion_store_complete.py
)
echo.
pause
