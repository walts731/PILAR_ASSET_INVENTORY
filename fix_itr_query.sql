-- Fix for ITR form query
-- The employees table uses 'employee_id' not 'id'
-- Updated query should be:

SELECT DISTINCT e.employee_id, e.name 
FROM employees e 
INNER JOIN assets a ON a.employee_id = e.employee_id 
WHERE e.status = 'permanent' AND a.type = 'asset' 
ORDER BY e.name ASC;
