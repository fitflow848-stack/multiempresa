-- Tabla para manejar deudas de clientes
CREATE TABLE IF NOT EXISTS `deudas` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `cliente_id` bigint(20) UNSIGNED NOT NULL,
    `venta_id` bigint(20) UNSIGNED NULL,
    `numero_comprobante` varchar(50) NOT NULL,
    `tipo_documento` varchar(20) NOT NULL DEFAULT 'boleta',
    `monto_total` decimal(10,2) NOT NULL,
    `monto_pagado` decimal(10,2) NOT NULL DEFAULT 0.00,
    `monto_deuda` decimal(10,2) NOT NULL,
    `fecha_venta` datetime NOT NULL,
    `fecha_vencimiento` datetime NULL,
    `estado` enum('pendiente','parcial','pagada','vencida') NOT NULL DEFAULT 'pendiente',
    `observaciones` text NULL,
    `user_id` bigint(20) UNSIGNED NOT NULL,
    `sucursal_id` bigint(20) UNSIGNED NOT NULL DEFAULT 1,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_cliente_id` (`cliente_id`),
    INDEX `idx_venta_id` (`venta_id`),
    INDEX `idx_estado` (`estado`),
    INDEX `idx_fecha_vencimiento` (`fecha_vencimiento`),
    INDEX `idx_sucursal` (`sucursal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índice compuesto para consultas frecuentes
CREATE INDEX `idx_cliente_estado_fecha` ON `deudas` (`cliente_id`, `estado`, `fecha_vencimiento`);

-- Índice para reportes
CREATE INDEX `idx_fecha_venta_estado` ON `deudas` (`fecha_venta`, `estado`);