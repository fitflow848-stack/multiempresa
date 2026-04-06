-- =====================================================
-- RESTAURACIÓN COMPLETA DE PERMISOS Y ROLES
-- Ejecutar en MySQL de producción
-- Fecha: 6 de abril de 2026
-- =====================================================

-- PASO 0: Limpiar cache de Spatie
-- (Después del SQL ejecutar: php artisan cache:clear)

-- =====================================================
-- PASO 1: RECREAR TODOS LOS PERMISOS (guard 'admin')
-- =====================================================

INSERT IGNORE INTO permissions (name, guard_name, created_at, updated_at) VALUES
-- Administración
('usuarios.ver', 'admin', NOW(), NOW()),
('usuarios.crear', 'admin', NOW(), NOW()),
('usuarios.editar', 'admin', NOW(), NOW()),
('usuarios.eliminar', 'admin', NOW(), NOW()),
('empresas.ver', 'admin', NOW(), NOW()),
('empresas.crear', 'admin', NOW(), NOW()),
('empresas.editar', 'admin', NOW(), NOW()),
('empresas.eliminar', 'admin', NOW(), NOW()),
('roles.ver', 'admin', NOW(), NOW()),
('roles.crear', 'admin', NOW(), NOW()),
('roles.editar', 'admin', NOW(), NOW()),
('roles.eliminar', 'admin', NOW(), NOW()),
('sucursales.ver', 'admin', NOW(), NOW()),
('sucursales.crear', 'admin', NOW(), NOW()),
('sucursales.editar', 'admin', NOW(), NOW()),
('sucursales.eliminar', 'admin', NOW(), NOW()),
-- Ventas y POS
('ventas.ver', 'admin', NOW(), NOW()),
('ventas.crear', 'admin', NOW(), NOW()),
('ventas.editar', 'admin', NOW(), NOW()),
('ventas.eliminar', 'admin', NOW(), NOW()),
('ventas.anular', 'admin', NOW(), NOW()),
('ventas.pos', 'admin', NOW(), NOW()),
('pos.ver', 'admin', NOW(), NOW()),
('comprobantes.ver', 'admin', NOW(), NOW()),
('comprobantes.cancelar', 'admin', NOW(), NOW()),
('comprobantes.anular', 'admin', NOW(), NOW()),
('comprobantes.imprimir', 'admin', NOW(), NOW()),
-- Inventario y Productos
('inventario.ver', 'admin', NOW(), NOW()),
('inventario.crear', 'admin', NOW(), NOW()),
('inventario.editar', 'admin', NOW(), NOW()),
('inventario.eliminar', 'admin', NOW(), NOW()),
('inventario.ajustar', 'admin', NOW(), NOW()),
('inventario.transferir', 'admin', NOW(), NOW()),
('inventario.kardex', 'admin', NOW(), NOW()),
('productos.ver', 'admin', NOW(), NOW()),
('productos.crear', 'admin', NOW(), NOW()),
('productos.editar', 'admin', NOW(), NOW()),
('productos.eliminar', 'admin', NOW(), NOW()),
('productos.modificar_precio', 'admin', NOW(), NOW()),
-- Compras y Proveedores
('compras.ver', 'admin', NOW(), NOW()),
('compras.crear', 'admin', NOW(), NOW()),
('compras.editar', 'admin', NOW(), NOW()),
('compras.eliminar', 'admin', NOW(), NOW()),
('compras.recibir', 'admin', NOW(), NOW()),
('proveedores.ver', 'admin', NOW(), NOW()),
('proveedores.crear', 'admin', NOW(), NOW()),
('proveedores.editar', 'admin', NOW(), NOW()),
('proveedores.eliminar', 'admin', NOW(), NOW()),
-- Clientes y Deudas
('clientes.ver', 'admin', NOW(), NOW()),
('clientes.crear', 'admin', NOW(), NOW()),
('clientes.editar', 'admin', NOW(), NOW()),
('clientes.eliminar', 'admin', NOW(), NOW()),
('deudas.ver', 'admin', NOW(), NOW()),
('deudas.pagar', 'admin', NOW(), NOW()),
('deudas.reporte', 'admin', NOW(), NOW()),
-- Caja y Operaciones
('caja.ver', 'admin', NOW(), NOW()),
('caja.abrir_cerrar', 'admin', NOW(), NOW()),
('caja.ajustar', 'admin', NOW(), NOW()),
('caja.arquear', 'admin', NOW(), NOW()),
('cajas.ver', 'admin', NOW(), NOW()),
('cajas.crear', 'admin', NOW(), NOW()),
('cajas.editar', 'admin', NOW(), NOW()),
('cajas.eliminar', 'admin', NOW(), NOW()),
('cajas.abrir_cerrar', 'admin', NOW(), NOW()),
('cajas.ajustar', 'admin', NOW(), NOW()),
('cajas.arquear', 'admin', NOW(), NOW()),
('operaciones_caja.ver', 'admin', NOW(), NOW()),
('operaciones_caja.ver_todo', 'admin', NOW(), NOW()),
('operaciones_caja.crear', 'admin', NOW(), NOW()),
('operaciones_caja.eliminar', 'admin', NOW(), NOW()),
-- Equipos de caja
('equipos_caja.ver', 'admin', NOW(), NOW()),
('equipos_caja.crear', 'admin', NOW(), NOW()),
('equipos_caja.editar', 'admin', NOW(), NOW()),
('equipos_caja.eliminar', 'admin', NOW(), NOW()),
('equipos_caja.asignar', 'admin', NOW(), NOW()),
-- Documentos
('guias_remision.ver', 'admin', NOW(), NOW()),
('guias_remision.crear', 'admin', NOW(), NOW()),
('guias_remision.editar', 'admin', NOW(), NOW()),
('guias_remision.eliminar', 'admin', NOW(), NOW()),
('guias_remision.enviar', 'admin', NOW(), NOW()),
('cotizaciones.ver', 'admin', NOW(), NOW()),
('cotizaciones.crear', 'admin', NOW(), NOW()),
('cotizaciones.editar', 'admin', NOW(), NOW()),
('cotizaciones.eliminar', 'admin', NOW(), NOW()),
('cotizaciones.convertir', 'admin', NOW(), NOW()),
-- Contabilidad y Finanzas
('contabilidad.ver', 'admin', NOW(), NOW()),
('contabilidad.gestionar_activos', 'admin', NOW(), NOW()),
('contabilidad.gestionar_pasivos', 'admin', NOW(), NOW()),
('finanzas.ver', 'admin', NOW(), NOW()),
('finanzas.crear', 'admin', NOW(), NOW()),
('finanzas.editar', 'admin', NOW(), NOW()),
('finanzas.eliminar', 'admin', NOW(), NOW()),
('finanzas.balance', 'admin', NOW(), NOW()),
('finanzas.estado_resultados', 'admin', NOW(), NOW()),
('tesoreria.ver', 'admin', NOW(), NOW()),
('tesoreria.crear', 'admin', NOW(), NOW()),
('tesoreria.editar', 'admin', NOW(), NOW()),
('tesoreria.eliminar', 'admin', NOW(), NOW()),
-- Catálogos
('catalogos.ver', 'admin', NOW(), NOW()),
('catalogos.gestionar', 'admin', NOW(), NOW()),
-- Reportes
('reportes.ver', 'admin', NOW(), NOW()),
('reportes.crear', 'admin', NOW(), NOW()),
('reportes.editar', 'admin', NOW(), NOW()),
('reportes.eliminar', 'admin', NOW(), NOW()),
('reportes.exportar', 'admin', NOW(), NOW()),
-- Configuración
('configuracion.ver', 'admin', NOW(), NOW()),
('configuracion.editar', 'admin', NOW(), NOW());


-- =====================================================
-- PASO 2: RECREAR ROLES (guard 'admin')
-- =====================================================

INSERT IGNORE INTO roles (name, guard_name, created_at, updated_at) VALUES
('super_admin', 'admin', NOW(), NOW()),
('admin_empresa', 'admin', NOW(), NOW()),
('admin', 'admin', NOW(), NOW()),
('administrador', 'admin', NOW(), NOW()),
('supervisor', 'admin', NOW(), NOW()),
('jefe_almacen', 'admin', NOW(), NOW()),
('vendedor', 'admin', NOW(), NOW()),
('cajero', 'admin', NOW(), NOW());


-- =====================================================
-- PASO 3: ASIGNAR TODOS LOS PERMISOS A super_admin
-- =====================================================

INSERT IGNORE INTO role_has_permissions (permission_id, role_id)
SELECT p.id, r.id 
FROM permissions p, roles r 
WHERE r.name = 'super_admin' AND r.guard_name = 'admin' AND p.guard_name = 'admin';


-- =====================================================
-- PASO 4: ASIGNAR PERMISOS A admin_empresa (TODO menos crear/eliminar empresas y sucursales)
-- =====================================================

INSERT IGNORE INTO role_has_permissions (permission_id, role_id)
SELECT p.id, r.id 
FROM permissions p, roles r 
WHERE r.name = 'admin_empresa' AND r.guard_name = 'admin' AND p.guard_name = 'admin'
AND p.name NOT IN ('empresas.crear', 'empresas.eliminar', 'sucursales.crear', 'sucursales.editar', 'sucursales.eliminar');

-- admin y administrador = mismos permisos que admin_empresa
INSERT IGNORE INTO role_has_permissions (permission_id, role_id)
SELECT p.id, r.id 
FROM permissions p, roles r 
WHERE r.name = 'admin' AND r.guard_name = 'admin' AND p.guard_name = 'admin'
AND p.name NOT IN ('empresas.crear', 'empresas.eliminar', 'sucursales.crear', 'sucursales.editar', 'sucursales.eliminar');

INSERT IGNORE INTO role_has_permissions (permission_id, role_id)
SELECT p.id, r.id 
FROM permissions p, roles r 
WHERE r.name = 'administrador' AND r.guard_name = 'admin' AND p.guard_name = 'admin'
AND p.name NOT IN ('empresas.crear', 'empresas.eliminar', 'sucursales.crear', 'sucursales.editar', 'sucursales.eliminar');


-- =====================================================
-- PASO 5: ASIGNAR PERMISOS A supervisor
-- =====================================================

INSERT IGNORE INTO role_has_permissions (permission_id, role_id)
SELECT p.id, r.id 
FROM permissions p, roles r 
WHERE r.name = 'supervisor' AND r.guard_name = 'admin' AND p.guard_name = 'admin'
AND p.name IN (
    'usuarios.ver', 'usuarios.crear', 'usuarios.editar', 'sucursales.ver',
    'ventas.ver', 'ventas.crear', 'ventas.editar', 'ventas.anular',
    'pos.ver', 'comprobantes.ver', 'comprobantes.imprimir',
    'inventario.ver', 'productos.ver', 'clientes.ver', 'clientes.crear',
    'caja.ver', 'caja.abrir_cerrar', 'caja.arquear', 
    'operaciones_caja.ver', 'reportes.ver'
);


-- =====================================================
-- PASO 6: ASIGNAR PERMISOS A jefe_almacen
-- =====================================================

INSERT IGNORE INTO role_has_permissions (permission_id, role_id)
SELECT p.id, r.id 
FROM permissions p, roles r 
WHERE r.name = 'jefe_almacen' AND r.guard_name = 'admin' AND p.guard_name = 'admin'
AND p.name IN (
    'productos.ver', 'productos.crear', 'productos.editar', 'productos.eliminar',
    'inventario.ver', 'inventario.ajustar', 'inventario.transferir', 'inventario.kardex',
    'compras.ver', 'compras.crear', 'compras.recibir', 
    'catalogos.ver', 'catalogos.gestionar', 'sucursales.ver'
);


-- =====================================================
-- PASO 7: ASIGNAR PERMISOS A vendedor (CON finanzas.crear para pagos)
-- =====================================================

INSERT IGNORE INTO role_has_permissions (permission_id, role_id)
SELECT p.id, r.id 
FROM permissions p, roles r 
WHERE r.name = 'vendedor' AND r.guard_name = 'admin' AND p.guard_name = 'admin'
AND p.name IN (
    'ventas.ver', 'ventas.crear', 'ventas.editar', 'ventas.pos', 'pos.ver',
    'comprobantes.ver', 'comprobantes.imprimir', 
    'productos.ver', 'productos.crear', 'productos.editar',
    'clientes.ver', 'clientes.crear', 'clientes.editar',
    'caja.ver', 'cajas.ver', 'cajas.abrir_cerrar',
    'inventario.ver', 'inventario.kardex', 'inventario.transferir',
    'cotizaciones.ver', 'cotizaciones.crear', 'cotizaciones.convertir',
    'equipos_caja.ver', 'tesoreria.ver', 'reportes.ver',
    'usuarios.ver',
    'finanzas.crear'
);


-- =====================================================
-- PASO 8: ASIGNAR PERMISOS A cajero
-- =====================================================

INSERT IGNORE INTO role_has_permissions (permission_id, role_id)
SELECT p.id, r.id 
FROM permissions p, roles r 
WHERE r.name = 'cajero' AND r.guard_name = 'admin' AND p.guard_name = 'admin'
AND p.name IN (
    'ventas.ver', 'ventas.crear', 'pos.ver', 
    'comprobantes.ver', 'comprobantes.imprimir',
    'productos.ver', 
    'caja.ver', 'caja.abrir_cerrar', 'caja.arquear', 
    'operaciones_caja.ver', 'operaciones_caja.crear',
    'clientes.ver', 'clientes.crear',
    'cajas.ver', 'cajas.abrir_cerrar', 'cajas.ajustar', 'cajas.arquear',
    'equipos_caja.ver', 'equipos_caja.asignar',
    'tesoreria.ver', 'reportes.ver'
);


-- =====================================================
-- PASO 9: REASIGNAR ROL super_admin AL ADMINISTRADOR PRINCIPAL
-- (Ajustar el user_id si es necesario)
-- =====================================================

-- Verificar qué usuarios existen con roles
SELECT u.id, u.name, u.email, r.name as role_name, r.guard_name
FROM users u
LEFT JOIN model_has_roles mhr ON u.id = mhr.model_id AND mhr.model_type = 'App\\Models\\User'
LEFT JOIN roles r ON mhr.role_id = r.id
ORDER BY u.id;

-- Si el admin principal (user_id = 1) no tiene rol, asignarlo:
INSERT IGNORE INTO model_has_roles (role_id, model_type, model_id, company_id)
SELECT r.id, 'App\\Models\\User', 1, u.company_id
FROM roles r, users u
WHERE r.name = 'super_admin' AND r.guard_name = 'admin' AND u.id = 1;


-- =====================================================
-- VERIFICACIÓN FINAL
-- =====================================================

-- Contar permisos
SELECT guard_name, COUNT(*) as total FROM permissions GROUP BY guard_name;

-- Contar roles
SELECT guard_name, COUNT(*) as total FROM roles GROUP BY guard_name;

-- Permisos por rol
SELECT r.name as rol, r.guard_name, COUNT(rhp.permission_id) as total_permisos
FROM roles r
LEFT JOIN role_has_permissions rhp ON r.id = rhp.role_id
WHERE r.guard_name = 'admin'
GROUP BY r.id, r.name, r.guard_name
ORDER BY total_permisos DESC;

-- Usuarios con roles
SELECT u.id, u.name, u.email, GROUP_CONCAT(r.name) as roles
FROM users u
JOIN model_has_roles mhr ON u.id = mhr.model_id AND mhr.model_type = 'App\\Models\\User'
JOIN roles r ON mhr.role_id = r.id
WHERE r.guard_name = 'admin'
GROUP BY u.id, u.name, u.email;