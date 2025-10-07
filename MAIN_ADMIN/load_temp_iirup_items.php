<?php
// This file handles loading temporary IIRUP items into the form
// Include this file in iirup_form.php to add temp items functionality

// Function to get temporary items from temp_iirup_items table
function getTempIIRUPItems($conn) {
    $temp_items = [];
    
    // Fetch all temp items (since table doesn't have user_id/session_id filtering)
    $stmt_temp = $conn->prepare("
        SELECT ti.*, a.description, a.inventory_tag, a.value, o.office_name
        FROM temp_iirup_items ti
        LEFT JOIN assets a ON ti.asset_id = a.id
        LEFT JOIN offices o ON a.office_id = o.id
        ORDER BY ti.id ASC
    ");
    $stmt_temp->execute();
    $result_temp = $stmt_temp->get_result();
    
    while ($row_temp = $result_temp->fetch_assoc()) {
        $temp_items[] = $row_temp;
    }
    $stmt_temp->close();
    
    return $temp_items;
}

// Function to generate table rows with temp items
function generateIIRUPTableRows($preselected_asset, $temp_items) {
    $all_items = [];
    
    // Add preselected asset from QR scan as first item
    if ($preselected_asset) {
        $all_items[] = [
            'type' => 'preselected',
            'asset_id' => $preselected_asset['id'],
            'date_acquired' => date('Y-m-d'),
            'particulars' => $preselected_asset['description'],
            'property_no' => $preselected_asset['inventory_tag'] ?? '',
            'quantity' => 1,
            'unit_cost' => $preselected_asset['value'],
            'office' => $preselected_asset['office_name'],
            'code' => ''
        ];
    }
    
    // Add temporary items using actual temp_iirup_items data
    foreach ($temp_items as $temp_item) {
        $all_items[] = [
            'type' => 'temp',
            'asset_id' => $temp_item['asset_id'],
            'date_acquired' => $temp_item['date_acquired'] ?? date('Y-m-d'),
            'particulars' => $temp_item['particulars'] ?? $temp_item['description'],
            'property_no' => $temp_item['property_no'] ?? $temp_item['inventory_tag'],
            'quantity' => $temp_item['quantity'] ?? 1,
            'unit_cost' => $temp_item['unit_cost'] ?? $temp_item['value'],
            'office' => $temp_item['office'] ?? $temp_item['office_name'],
            'code' => $temp_item['code'] ?? ''
        ];
    }
    
    // Show at least 1 row, or as many as we have items
    $total_rows = max(1, count($all_items));
    
    $rows_html = '';
    for ($i = 0; $i < $total_rows; $i++) {
        $current_item = isset($all_items[$i]) ? $all_items[$i] : null;
        
        $row_date_acquired = $current_item ? htmlspecialchars($current_item['date_acquired']) : date('Y-m-d');
        $row_particulars = $current_item ? htmlspecialchars($current_item['particulars']) : '';
        $row_asset_id = $current_item ? htmlspecialchars($current_item['asset_id']) : '';
        $row_property_no = $current_item ? htmlspecialchars($current_item['property_no']) : '';
        $row_quantity = $current_item ? htmlspecialchars($current_item['quantity']) : '';
        $row_unit_cost = $current_item ? htmlspecialchars($current_item['unit_cost']) : '';
        $row_office = $current_item ? htmlspecialchars($current_item['office']) : '';
        $row_code = $current_item ? htmlspecialchars($current_item['code']) : '';
        $show_remove_btn = $current_item ? 'inline-block' : 'none';
        
        $today = date('Y-m-d');
        
        $rows_html .= '
            <tr class="iirup-row">
                <td data-label="Date Acquired">
                    <input type="date" name="date_acquired[]" value="' . $row_date_acquired . '" 
                           title="Date when the asset was originally acquired by the organization">
                </td>
                <td data-label="Particulars/Articles">
                    <div class="d-flex align-items-center">
                        <input type="text" name="particulars[]" list="asset_descriptions" class="particulars flex-grow-1" 
                               value="' . $row_particulars . '" placeholder="Select or type asset description"
                               title="Description of the asset/item being inspected for disposal">
                        <button type="button" class="btn btn-sm btn-danger ms-1 remove-asset" 
                                style="display: ' . $show_remove_btn . ';" title="Remove Asset">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <input type="hidden" name="asset_id[]" class="asset_id" value="' . $row_asset_id . '">
                </td>
                <td data-label="Property No">
                    <input type="text" name="property_no[]" value="' . $row_property_no . '" placeholder="Property Number"
                           title="Official property number or inventory tag assigned to the asset">
                </td>
                <td data-label="Quantity">
                    <input type="number" name="qty[]" min="1" class="qty" max="1" value="' . $row_quantity . '" placeholder="Qty"
                           title="Number of units of this asset being inspected">
                </td>
                <td data-label="Unit Cost">
                    <input type="number" step="0.01" name="unit_cost[]" min="1" class="unit_cost" 
                           value="' . $row_unit_cost . '" placeholder="0.00"
                           title="Original purchase price per unit of the asset">
                </td>
                <td data-label="Total Cost">
                    <input type="number" step="0.01" name="total_cost[]" min="1" readonly 
                           value="' . ($row_unit_cost ? $row_unit_cost : '') . '" placeholder="Auto-calculated"
                           title="Automatically calculated: Quantity × Unit Cost">
                </td>
                <td data-label="Accumulated Depreciation">
                    <input type="number" step="0.01" name="accumulated_depreciation[]" min="1" placeholder="0.00"
                           title="Total depreciation accumulated over the asset\'s useful life">
                </td>
                <td data-label="Accumulated Impairment">
                    <input type="number" step="0.01" name="accumulated_impairment_losses[]" min="1" placeholder="0.00"
                           title="Total impairment losses recognized for this asset">
                </td>
                <td data-label="Carrying Amount">
                    <input type="number" step="0.01" name="carrying_amount[]" min="1" placeholder="0.00"
                           title="Current book value: Cost - Depreciation - Impairment">
                </td>
                <td data-label="Remarks">
                    <select name="remarks[]" class="form-select" title="Current condition status of the asset">
                        <option value="Unserviceable" selected>Unserviceable</option>
                    </select>
                </td>
                <td data-label="Sale">
                    <input type="text" name="sale[]" placeholder="Sale info"
                           title="Details if asset is to be sold (buyer, price, etc.)">
                </td>
                <td data-label="Transfer">
                    <input type="text" name="transfer[]" placeholder="Transfer info"
                           title="Details if asset is to be transferred (recipient, location)">
                </td>
                <td data-label="Destruction">
                    <input type="text" name="destruction[]" placeholder="Destruction info"
                           title="Details if asset is to be destroyed (method, date, reason)">
                </td>
                <td data-label="Others">
                    <input type="text" name="others[]" placeholder="Other disposal"
                           title="Other disposal methods not covered above">
                </td>
                <td data-label="Total">
                    <input type="number" step="0.01" name="total[]" min="1" placeholder="0.00"
                           title="Total disposal value or cost">
                </td>
                <td data-label="Appraised Value">
                    <input type="number" step="0.01" name="appraised_value[]" min="1" placeholder="0.00"
                           title="Current market value as determined by appraisal">
                </td>
                <td data-label="OR Number">
                    <input type="text" name="or_no[]" placeholder="OR Number"
                           title="Official Receipt number for any sales transaction">
                </td>
                <td data-label="Amount">
                    <input type="number" step="0.01" name="amount[]" min="1" placeholder="0.00"
                           title="Amount received from sale or disposal">
                </td>
                <td data-label="Department/Office">
                    <input type="text" name="dept_office[]" class="dept_office" value="' . $row_office . '" readonly placeholder="Auto-filled"
                           title="Department/Office responsible for this asset">
                </td>
                <td data-label="Code">
                    <input type="text" name="code[]" value="' . $row_code . '" placeholder="Code"
                           title="Internal classification or reference code">
                </td>
                <td data-label="Date Received">
                    <input type="date" name="date_received[]" value="' . $today . '"
                           title="Date when IIRUP form was received/processed">
                </td>
                <td data-label="Actions">
                    <button type="button" class="btn btn-sm btn-info edit-row-btn" 
                            title="Edit row details in modal">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                </td>
            </tr>';
    }
    
    return $rows_html;
}

// JavaScript functions for temp items management
function getTempItemsJavaScript($temp_items) {
    $temp_items_json = json_encode($temp_items);
    return "
    <script>
    const tempItems = $temp_items_json;
    
    function loadTempItems() {
        if (tempItems.length === 0) {
            alert('No temporary items to load.');
            return;
        }
        
        // Clear existing rows except the first one
        const tbody = document.querySelector('.excel-table tbody');
        const rows = tbody.querySelectorAll('.iirup-row');
        for (let i = rows.length - 1; i > 0; i--) {
            rows[i].remove();
        }
        
        // Clear the first row
        const firstRow = tbody.querySelector('.iirup-row');
        if (firstRow) {
            clearAssetRow(firstRow);
        }
        
        // Add temp items to form
        tempItems.forEach((item, index) => {
            if (index === 0) {
                // Use first row
                populateRow(firstRow, item);
            } else {
                // Add new rows
                addNewRow();
                const newRow = tbody.lastElementChild;
                populateRow(newRow, item);
            }
        });
        
        // Update selected assets
        tempItems.forEach(item => {
            selectedAssetIds.add(item.asset_id.toString());
        });
        
        updateDatalist();
        alert('Loaded ' + tempItems.length + ' temporary items into the form.');
    }
    
    function populateRow(row, item) {
        if (!row || !item) return;
        
        const particularInput = row.querySelector('.particulars');
        const assetIdInput = row.querySelector('.asset_id');
        const propertyNoInput = row.querySelector('input[name=\"property_no[]\"]');
        const qtyInput = row.querySelector('.qty');
        const unitCostInput = row.querySelector('.unit_cost');
        const totalCostInput = row.querySelector('input[name=\"total_cost[]\"]');
        const deptInput = row.querySelector('.dept_office');
        const removeBtn = row.querySelector('.remove-asset');
        
        if (particularInput) particularInput.value = item.description || '';
        if (assetIdInput) assetIdInput.value = item.asset_id || '';
        if (propertyNoInput) propertyNoInput.value = item.inventory_tag || '';
        if (qtyInput) qtyInput.value = '1';
        if (unitCostInput) unitCostInput.value = item.value || '';
        if (totalCostInput) totalCostInput.value = item.value || '';
        if (deptInput) deptInput.value = item.office_name || '';
        if (removeBtn) removeBtn.style.display = 'inline-block';
    }
    
    function clearTempItems() {
        if (confirm('Are you sure you want to clear all temporary items? This action cannot be undone.')) {
            fetch('clear_temp_iirup.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Cleared ' + data.count + ' temporary items.');
                    location.reload(); // Reload to update the alert banner
                } else {
                    alert('Error clearing temporary items: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error clearing temporary items.');
            });
        }
    }
    </script>";
}
?>
