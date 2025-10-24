# 🚨 ACCIÓN REQUERIDA - EJECUTAR MIGRACIÓN

## ¿Por qué este error?

El sistema de reportes necesita **nuevas columnas en la base de datos** que aún no existen:

- `ventas.id_usuario` - Para saber qué usuario hizo la venta
- `ventas.metodo_pago` - Para registrar si fue efectivo, tarjeta, etc.
- `ventas.turno` - Para filtrar por turno (mañana/tarde/noche)
- `ventas.monto_pagado` - Para calcular el vuelto
- `ventas.vuelto` - Para mostrar cuánto se devolvió
- `producto.stock_minimo` - Para alertas de stock bajo personalizadas

## ✅ SOLUCIÓN INMEDIATA

He modificado temporalmente los archivos para que funcionen **SIN** estas columnas, pero con funcionalidad limitada:

- ✅ Puedes generar reportes por **fecha y producto**
- ❌ NO puedes filtrar por **usuario, turno o método de pago** (hasta migrar)
- ✅ Las exportaciones (Excel/PDF) funcionan pero mostrarán valores por defecto

## 🔧 CÓMO EJECUTAR LA MIGRACIÓN (2 PASOS)

### Paso 1: Iniciar tu servidor local
Asegúrate de que tu aplicación esté corriendo (doble clic en `ejecutar_app.vbs` o `iniciar_app.bat`)

### Paso 2: Abrir en el navegador
Abre esta URL en tu navegador:

```
http://localhost/Punto-de-Venta-v2/ejecutar_migracion_web.php
```

O si usas otro puerto, ajusta según corresponda.

## 📋 ¿Qué hace la migración?

La migración:
1. ✅ Agrega las columnas nuevas a las tablas
2. ✅ Establece valores por defecto para ventas existentes
3. ✅ No borra ni modifica datos existentes
4. ✅ Es 100% segura y reversible

## 🎯 DESPUÉS DE LA MIGRACIÓN

Una vez ejecutada la migración, necesitas:

1. **Reiniciar la aplicación** (cerrar y volver a abrir)
2. **Actualizar los archivos de reportes** - Te daré las versiones completas
3. **Probar todas las funcionalidades**:
   - Stock mínimo por producto
   - Reportes con todos los filtros
   - Exportación Excel/PDF

## 🆘 SI TIENES PROBLEMAS

Si al abrir `ejecutar_migracion_web.php` ves un error:
- Asegúrate de que el servidor esté corriendo
- Verifica que `sistema.db` existe en la carpeta raíz
- Avísame y te ayudo a solucionarlo

## 📞 SIGUIENTE PASO

**Cuando hayas ejecutado la migración**, avísame con:
- ✅ "Migración ejecutada exitosamente"
- ❌ "Error: [descripción del error]"

Y yo actualizaré los archivos para que usen todas las funcionalidades nuevas.
