-- =============================================
-- Assign Permissions to Roles
-- =============================================

-- First, clear any existing role-permission mappings
TRUNCATE TABLE `role_permissions`;

-- =============================================
-- 1. SYSTEM_ADMIN - Full access to everything
-- =============================================
INSERT INTO `role_permissions` (`role_id`, `role`, `permission_id`)
SELECT 1, 'SYSTEM_ADMIN', p.id 
FROM `permissions` p
WHERE p.name IN (
    -- System Admin has all permissions
    SELECT name FROM `permissions`
);

-- =============================================
-- 2. MAIN_ADMIN - Can manage assets, users, and basic settings
-- =============================================
INSERT INTO `role_permissions` (`role_id`, `role`, `permission_id`)
SELECT 2, 'MAIN_ADMIN', p.id 
FROM `permissions` p
WHERE p.name IN (
    -- Dashboard
    'view_dashboard',
    
    -- User Management
    'view_users', 'create_users', 'edit_users', 'delete_users',
    
    -- Asset Management
    'view_assets', 'create_assets', 'edit_assets', 'delete_assets',
    'import_assets', 'export_assets',
    
    -- Asset Assignment
    'assign_assets', 'checkout_assets', 'checkin_assets',
    
    -- Categories & Types
    'view_categories', 'create_categories', 'edit_categories', 'delete_categories',
    'view_types', 'create_types', 'edit_types', 'delete_types',
    
    -- Locations & Departments
    'view_locations', 'create_locations', 'edit_locations', 'delete_locations',
    'view_departments', 'create_departments', 'edit_departments', 'delete_departments',
    
    -- Maintenance
    'view_maintenance', 'create_maintenance', 'edit_maintenance', 'delete_maintenance',
    'schedule_maintenance', 'complete_maintenance',
    
    -- Reports
    'view_reports', 'generate_reports', 'export_reports',
    
    -- Settings (limited)
    'view_settings', 'edit_settings',
    
    -- ICS (Inventory Custodian Slip)
    'view_ics', 'create_ics', 'edit_ics', 'delete_ics', 'approve_ics',
    
    -- PAR (Property Acknowledgement Receipt)
    'view_par', 'create_par', 'edit_par', 'delete_par', 'approve_par',
    
    -- Inventory
    'view_inventory', 'perform_inventory', 'adjust_inventory'
);

-- =============================================
-- 3. MAIN_EMPLOYEE - Can view and borrow assets
-- =============================================
INSERT INTO `role_permissions` (`role_id`, `role`, `permission_id`)
SELECT 3, 'MAIN_EMPLOYEE', p.id 
FROM `permissions` p
WHERE p.name IN (
    -- Dashboard
    'view_dashboard',
    
    -- Asset Management (view only)
    'view_assets',
    
    -- Asset Assignment (checkout/checkin own assets)
    'checkout_assets', 'checkin_assets',
    
    -- Categories & Types (view only)
    'view_categories', 'view_types',
    
    -- Locations & Departments (view only)
    'view_locations', 'view_departments',
    
    -- Maintenance (view and request only)
    'view_maintenance', 'create_maintenance',
    
    -- Reports (view only)
    'view_reports',
    
    -- ICS (view own)
    'view_ics',
    
    -- PAR (view own)
    'view_par'
);

-- =============================================
-- 4. MAIN_USER - Basic user with limited access
-- =============================================
INSERT INTO `role_permissions` (`role_id`, `role`, `permission_id`)
SELECT 4, 'MAIN_USER', p.id 
FROM `permissions` p
WHERE p.name IN (
    -- Dashboard (limited)
    'view_dashboard',
    
    -- Asset Management (view only)
    'view_assets',
    
    -- Categories & Types (view only)
    'view_categories', 'view_types',
    
    -- Locations & Departments (view only)
    'view_locations', 'view_departments',
    
    -- Maintenance (view and request only)
    'view_maintenance', 'create_maintenance',
    
    -- Reports (view only)
    'view_reports'
);

-- =============================================
-- Verify the assignments
-- =============================================
SELECT 
    r.name AS role_name,
    COUNT(rp.permission_id) AS permission_count
FROM 
    roles r
LEFT JOIN 
    role_permissions rp ON r.id = rp.role_id
GROUP BY 
    r.id, r.name
ORDER BY 
    r.position DESC;
