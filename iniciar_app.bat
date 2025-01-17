@echo off

REM Leer la ruta de instalación desde el registro
for /f "usebackq tokens=2*" %%A in (`reg query "HKCU\Software\MiAplicacion" /v InstallDir`) do set InstallDir=%%B

cd "%InstallDir%\punto_de_venta version sqlite s_xampp"

:: Iniciar el servidor PHP sin mostrar la consola
start /b "Servidor PHP" "punto_de_venta version sqlite s_xampp\php\php.exe" -S localhost:8000 -t "%InstallDir%\punto_de_venta version sqlite s_xampp"

:: Esperar unos segundos para asegurarse de que el servidor PHP se haya iniciado
timeout /t 5 /nobreak >nul

:: Iniciar la aplicación Electron sin mostrar la consola
start /b "" cmd /c "npm start"

exit
