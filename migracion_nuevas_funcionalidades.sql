-- ============================================================
-- Migración: Nuevas Funcionalidades - Stock Mínimo, Reportes y Métodos de Pago
-- Fecha: Enero 2025
-- ============================================================

-- 1. AGREGAR STOCK MÍNIMO A PRODUCTOS
-- Permite configurar un stock mínimo diferente por cada producto
ALTER TABLE producto ADD COLUMN stock_minimo INTEGER DEFAULT 5;

-- 2. AGREGAR CAMPOS PARA REPORTES Y MÉTODOS DE PAGO EN VENTAS
-- id_usuario: Para saber quién realizó la venta
-- metodo_pago: efectivo, tarjeta, transferencia, etc.
-- monto_pagado: Cantidad que pagó el cliente
-- vuelto: Diferencia a devolver (se calcula automáticamente)
-- turno: mañana, tarde, noche (opcional para filtros)
ALTER TABLE ventas ADD COLUMN id_usuario INTEGER DEFAULT 1;
ALTER TABLE ventas ADD COLUMN metodo_pago TEXT DEFAULT 'efectivo';
ALTER TABLE ventas ADD COLUMN monto_pagado REAL DEFAULT 0;
ALTER TABLE ventas ADD COLUMN vuelto REAL DEFAULT 0;
ALTER TABLE ventas ADD COLUMN turno TEXT DEFAULT '';

-- Actualizar registros existentes
UPDATE producto SET stock_minimo = 5 WHERE stock_minimo IS NULL;
UPDATE ventas SET id_usuario = 1 WHERE id_usuario IS NULL;
UPDATE ventas SET metodo_pago = 'efectivo' WHERE metodo_pago IS NULL;
UPDATE ventas SET monto_pagado = total WHERE monto_pagado IS NULL OR monto_pagado = 0;
UPDATE ventas SET vuelto = 0 WHERE vuelto IS NULL;

-- Mensaje de confirmación
SELECT 'Migración ejecutada correctamente' AS mensaje;
SELECT 'Stock mínimo agregado a productos' AS detalle;
SELECT 'Campos de pago y reportes agregados a ventas' AS detalle;
