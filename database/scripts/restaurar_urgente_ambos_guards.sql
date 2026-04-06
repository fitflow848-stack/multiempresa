-- =====================================================
-- RESTAURACIÓN URGENTE - AMBOS GUARDS (web + admin)
-- Incluye company_id para soporte multi-tenant
-- =====================================================

-- PASO 1: Asegurar permisos existen en AMBOS guards
INSERT IGNORE INTO permissions (name, guard_name, created_at, updated_at)
SELECT name, 'admin', NOW(), NOW() FROM permissions WHERE guard_name = 'web'
AND name NOT IN (SELECT name FROM permissions WHERE guard_name = 'admin');

INSERT IGNORE INTO permissions (name, guard_name, created_at, updated_at)
SELECT name, 'web', NOW(), NOW() FROM permissions WHERE guard_name = 'admin'
AND name NOT IN (SELECT name FROM permissions WHERE guard_name = 'web');

-- PASO 2: Crear roles 'admin' para CADA company_id (clave: unique index es company_id+name+guard_name)
INSERT IGNORE INTO roles (name, guard_name, company_id, created_at, updated_at)
SELECT name, 'admin', company_id, NOW(), NOW() FROM roles WHERE guard_name = 'web';

-- PASO 3: SUPER_ADMIN - todos los permisos en ambos guards
INSERT IGNORE INTO role_has_permissions (permission_id, role_id)
SELECT p.id, r.id FROM permissions p, roles r 
WHERE r.name = 'super_admin' AND r.guard_name = p.guard_name;

-- PASO 4: ADMIN_EMPRESA / ADMIN / ADMINISTRADOR - todos menos empresas.crear/eliminar y sucursales
INSERT IGNORE INTO role_has_permissions (permission_id, role_id)
SELECT p.id, r.id FROM permissions p, roles r 
WHERE r.name IN ('admin_empresa', 'admin', 'administrador') 
AND r.guard_name = p.guard_name
AND p.name NOT IN ('empresas.crear', 'empresas.eliminar', 'sucursales.crear', 'sucursales.editar', 'sucursales.eliminar');

-- PASO 5: SUPERVISOR
INSERT IGNORE INTO role_has_permissions (permission_id, role_id)
SELECT p.id, r.id FROM permissions p, roles r 
WHERE r.name = 'supervisor' AND r.guard_name = p.guard_name
AND p.name IN (
    'usuarios.ver', 'usuarios.crear', 'usuarios.editar', 'sucursales.ver',
    'ventas.ver', 'ventas.crear', 'ventas.editar', 'ventas.anular',
    'pos.ver', 'comprobantes.ver', 'comprobantes.imprimir',
    'inventario.ver', 'productos.ver', 'clientes.ver', 'clientes.crear',
    'caja.ver', 'caja.abrir_cerrar', 'caja.arquear', 
    'operaciones_caja.ver', 'reportes.ver'
);

-- PASO 6: JEFE_ALMACEN
INSERT IGNORE INTO role_has_permissions (permission_id, role_id)
SELECT p.id, r.id FROM permissions p, roles r 
WHERE r.name = 'jefe_almacen' AND r.guard_name = p.guard_name
AND p.name IN (
    'productos.ver', 'productos.crear', 'productos.editar', 'productos.eliminar',
    'inventario.ver', 'inventario.ajustar', 'inventario.transferir', 'inventario.kardex',
    'compras.ver', 'compras.crear', 'compras.recibir', 
    'catalogos.ver', 'catalogos.gestionar', 'sucursales.ver'
);

-- PASO 7: VENDEDOR (con finanzas.crear para pagos)
INSERT IGNORE INTO role_has_permissions (permission_id, role_id)
SELECT p.id, r.id FROM permissions p, roles r 
WHERE r.name = 'vendedor' AND r.guard_name = p.guard_name
AND p.name IN (
    'ventas.ver', 'ventas.crear', 'ventas.editar', 'ventas.pos', 'pos.ver',
    'comprobantes.ver', 'comprobantes.imprimir', 
    'productos.ver', 'productos.crear', 'productos.editar',
    'clientes.ver', 'clientes.crear', 'clientes.editar',
    'caja.ver', 'cajas.ver', 'cajas.abrir_cerrar',
    'inventario.ver', 'inventario.kardex', 'inventario.transferir',
    'cotizaciones.ver', 'cotizaciones.crear', 'cotizaciones.convertir',
    'equipos_caja.ver', 'tesoreria.ver', 'reportes.ver',
    'usuarios.ver', 'finanzas.crear'
);

-- PASO 8: CAJERO
INSERT IGNORE INTO role_has_permissions (permission_id, role_id)
SELECT p.id, r.id FROM permissions p, roles r 
WHERE r.name = 'cajero' AND r.guard_name = p.guard_name
AND p.name IN (
    'ventas.ver', 'ventas.crear', 'pos.ver', 
    'comprobantes.ver', 'comprobantes.imprimir', 'productos.ver', 
    'caja.ver', 'caja.abrir_cerrar', 'caja.arquear', 
    'operaciones_caja.ver', 'operaciones_caja.crear',
    'clientes.ver', 'clientes.crear',
    'cajas.ver', 'cajas.abrir_cerrar', 'cajas.ajustar', 'cajas.arquear',
    'equipos_caja.ver', 'equipos_caja.asignar',
    'tesoreria.ver', 'reportes.ver'
);

-- PASO 9: REASIGNAR USUARIOS A ROLES ADMIN (copiar de web a admin)
INSERT IGNORE INTO model_has_roles (role_id, model_type, model_id, company_id)
SELECT r_admin.id, mhr.model_type, mhr.model_id, mhr.company_id
FROM model_has_roles mhr
JOIN roles r_web ON r_web.id = mhr.role_id AND r_web.guard_name = 'web'
JOIN roles r_admin ON r_admin.name = r_web.name AND r_admin.guard_name = 'admin'
WHERE mhr.model_type = 'App\\Models\\User';

-- VERIFICACIÓN
SELECT r.name, r.guard_name, COUNT(rhp.permission_id) as permisos
FROM roles r
LEFT JOIN role_has_permissions rhp ON r.id = rhp.role_id
GROUP BY r.id, r.name, r.guard_name
ORDER BY r.name, r.guard_name;

SELECT u.id, u.name, GROUP_CONCAT(CONCAT(r.name, '(', r.guard_name, ')')) as roles
FROM users u
JOIN model_has_roles mhr ON u.id = mhr.model_id
JOIN roles r ON r.id = mhr.role_id
GROUP BY u.id, u.name;