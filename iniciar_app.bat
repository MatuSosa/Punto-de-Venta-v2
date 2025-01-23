REM //BUG: no inicia sesion en la bd
@echo off
set "InstallDir=%~dp0"

REM Iniciar el servidor PHP en segundo plano
start /b "Servidor PHP" "%InstallDir%\php\php.exe" -S localhost:8000 -t "%InstallDir%"

:: Esperar unos segundos para asegurarse de que el servidor PHP se haya iniciado
timeout /t 5 /nobreak >nul

:: Iniciar Electron y esperar a que se cierre
cmd /c "npm start"

:: Una vez que Electron se cierra, cerrar PHP
taskkill /f /im php.exe
exit
