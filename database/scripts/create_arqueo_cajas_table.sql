-- SQL script to create arqueo_cajas table
-- Run this on your MySQL server (adjust engine/charset as needed)

CREATE TABLE IF NOT EXISTS `arqueo_cajas` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NULL,
  `cierre_id` BIGINT UNSIGNED NULL,
  `sucursal_id` BIGINT UNSIGNED NULL,
  `fecha` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `monedas` JSON NULL,
  `billetes` JSON NULL,
  `total` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total_caja` DECIMAL(12,2) NULL,
  `total_cierre` DECIMAL(12,2) NULL,
  `descuento` DECIMAL(12,2) NULL,
  `ultimo_conteo` DATETIME NULL,
  `notas` TEXT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
