# Sistema de Imágenes Centralizado

## 📋 Resumen

Se ha implementado un sistema centralizado para gestionar el logo y el background del sistema desde un único lugar: **Configuración**.

## 🎯 Características

### Imágenes Configurables:
1. **Logo**: Se usa en:
   - Favicon del navegador
   - PDFs generados
   - Panel de login
   - Icono del sistema

2. **Background**: Se usa en:
   - Menú lateral del panel de administración
   - Fondo del login

## 🚀 Pasos para Activar

### 1. Ejecutar script de actualización de base de datos

Abre en tu navegador:
```
http://localhost/tu-proyecto/src/actualizar_configuracion_imagenes.php
```

Esto agregará las columnas `logo` y `background` a la tabla `configuracion`.

### 2. Configurar las imágenes

1. Ve a **Configuración** en el panel de administración
2. Verás dos nuevos campos:
   - **Logo de la Empresa**: Sube tu logo (PNG, JPG, GIF - máx 5MB)
   - **Imagen de Fondo (Background)**: Sube imagen para fondo (PNG, JPG, GIF - máx 5MB)
3. Haz clic en **Guardar**

## 📝 Datos del Establecimiento en Login

Ahora el login muestra automáticamente:
- ✅ Logo de la empresa
- ✅ Nombre del establecimiento
- ✅ Teléfono (si está configurado)
- ✅ Email (si está configurado)
- ✅ Dirección (si está configurada)

Todo se obtiene de la configuración del sistema.

## 🔧 Archivos Modificados

1. **src/config.php**: 
   - Agregado manejo de logo y background
   - Formulario con dos campos de carga de imágenes
   - Vista previa antes de guardar

2. **src/includes/header.php**:
   - Lee logo y background de la BD
   - Aplica dinámicamente al sidebar

3. **index.php** (Login):
   - Lee configuración completa
   - Muestra datos del establecimiento
   - Usa logo y background configurados

4. **src/pdf/generar.php**:
   - Usa logo de configuración en PDFs
   - Detecta automáticamente tipo de imagen (JPG/PNG)

5. **src/actualizar_configuracion_imagenes.php**: 
   - Script de migración (ejecutar una sola vez)

## 💡 Ventajas

- ✅ Cambias el logo en un solo lugar y se actualiza en todo el sistema
- ✅ No necesitas editar código para cambiar imágenes
- ✅ Vista previa antes de guardar
- ✅ Validación de formatos y tamaños
- ✅ Los datos del local se muestran automáticamente en el login

## 🎨 Valores por Defecto

Si no configuras imágenes personalizadas, el sistema usa:
- **Logo**: `logo.png`
- **Background**: `sidebar-1.jpg`

## ⚠️ Importante

Después de ejecutar el script de actualización, las imágenes existentes seguirán funcionando hasta que subas nuevas desde Configuración.
