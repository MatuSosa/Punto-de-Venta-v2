-- Script de migración para agregar sistema de permisos por acción (CRUD)
-- Fecha: 2025-10-23

-- 1. Agregar columnas a la tabla detalle_permisos para especificar acciones
ALTER TABLE detalle_permisos ADD COLUMN puede_crear INTEGER DEFAULT 1;
ALTER TABLE detalle_permisos ADD COLUMN puede_leer INTEGER DEFAULT 1;
ALTER TABLE detalle_permisos ADD COLUMN puede_actualizar INTEGER DEFAULT 1;
ALTER TABLE detalle_permisos ADD COLUMN puede_eliminar INTEGER DEFAULT 1;

-- 2. Actualizar permisos existentes (por defecto, todos tienen acceso completo)
UPDATE detalle_permisos SET 
    puede_crear = 1,
    puede_leer = 1,
    puede_actualizar = 1,
    puede_eliminar = 1
WHERE puede_crear IS NULL;

-- Nota: Los valores son 1 (permitido) o 0 (denegado)
-- puede_crear: Permite crear nuevos registros
-- puede_leer: Permite ver/listar registros (solo lectura)
-- puede_actualizar: Permite editar registros existentes
-- puede_eliminar: Permite eliminar registros
