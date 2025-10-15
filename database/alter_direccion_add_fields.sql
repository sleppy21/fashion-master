-- ========================================
-- MODIFICACIÓN DE LA TABLA DIRECCION
-- Ejecutar este SQL en phpMyAdmin
-- ========================================

-- Agregar campos para almacenar información completa del cliente
ALTER TABLE `direccion`
ADD COLUMN `email_direccion` VARCHAR(100) NULL AFTER `telefono_direccion`,
ADD COLUMN `dni_ruc_direccion` VARCHAR(11) NULL AFTER `email_direccion`,
ADD COLUMN `metodo_pago_favorito` ENUM('tarjeta', 'transferencia', 'yape', 'efectivo') NULL AFTER `referencia_direccion`;

-- Verificar que se agregaron correctamente
-- DESCRIBE direccion;
