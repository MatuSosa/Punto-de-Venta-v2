@echo off

REM Obtener la ruta del script actual y usarla como base
set "InstallDir=%~dp0"

REM Iniciar el servidor PHP sin mostrar la consola
start /b "Servidor PHP" "%InstallDir%\php\php.exe" -S localhost:8000 -t "%InstallDir%"

:: Esperar unos segundos para asegurarse de que el servidor PHP se haya iniciado
timeout /t 5 /nobreak >nul

:: Iniciar la aplicación Electron sin mostrar la consola
start /b "" cmd /c "npm start"

exit