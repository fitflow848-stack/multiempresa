-- ============================================================
-- Script: Bancos, permisos y reporte - 11/05/2026
-- ============================================================

-- 1. Permiso bancos.ver
INSERT IGNORE INTO permissions (name, guard_name, created_at, updated_at)
VALUES ('bancos.ver', 'web', NOW(), NOW());

-- 2. Asignar permiso a roles admin
SET @perm_id = (SELECT id FROM permissions WHERE name = 'bancos.ver' LIMIT 1);

INSERT IGNORE INTO role_has_permissions (permission_id, role_id)
SELECT @perm_id, id FROM roles WHERE name IN ('super_admin', 'admin_empresa', 'admin', 'administrador');

-- 3. Agregar columna sucursal_id a banco_movimientos
ALTER TABLE banco_movimientos ADD COLUMN sucursal_id BIGINT UNSIGNED NULL AFTER id_venta;
ALTER TABLE banco_movimientos ADD CONSTRAINT fk_banco_mov_sucursal FOREIGN KEY (sucursal_id) REFERENCES sucursales(id) ON DELETE SET NULL;

-- 4. Registrar reporte de movimientos de banco
INSERT INTO reports (category, name, active, method, created_at, updated_at)
SELECT 'Caja y Bancos', 'Movimientos de Banco', 1, 'reporteMovimientosBanco', NOW(), NOW()
FROM dual
WHERE NOT EXISTS (SELECT 1 FROM reports WHERE method = 'reporteMovimientosBanco');
