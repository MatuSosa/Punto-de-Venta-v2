Dim WshShell, InstallDir, oExec
Set WshShell = CreateObject("WScript.Shell")

' Obtener la ruta del directorio donde se encuentra el script
InstallDir = CreateObject("Scripting.FileSystemObject").GetParentFolderName(WScript.ScriptFullName)

' Verificar si la ruta está vacía
If InstallDir = "" Then
    WScript.Echo "Error: No se pudo obtener la ruta de instalación"
    WScript.Quit
End If

' Ejecutar el archivo BAT sin mostrar la consola
WshShell.Run """" & InstallDir & "\iniciar_app.bat""", 0, False

Set WshShell = Nothing
