{{--
============================================================================
A/R Invoice Main Form Interface (SAP Business One ERP Theme)
============================================================================
Page Contents & Structure:
 1. Header Toolbar: User context, document status indicator, logout action.
 2. System Alerts: Dynamic red notification bar displaying runtime validation errors.
 3. Header Details Section:
    - Customer Code (Type-ahead predictive live search dropdown).
    - Customer Name linked auto-sync fields.
    - System-assigned Auto-Increment Doc Number.
    - Editable Posting Date (Defaults to current server date).
 4. Document Contents Table:
    - Line item entry supporting dynamic row creation (`Add Line` button).
    - Item No. selection populating unit costs & descriptions.
    - Input controls for Quantity and Discount Percentages.
    - Automatic row total and discounted price calculation.
 5. Threshold Approval Label:
    - Conditionally visible warning banner triggered when net totals exceed 10,000.
 6. Footer Details Section:
    - Searchable Sales Employee drop-down assignment.
    - Mandatory multi-line free text Remarks field.
    - Auto-summed Total Before Discount & Total After Discount summary fields.
============================================================================
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AR Invoice - SAP Business One</title>

    <style>
        /* ==========================================================================
           1. Global Resets & Base Styles
           ========================================================================== */
        body { 
            font-family: Arial, Tahoma, sans-serif; 
            font-size: 11px; 
            background-color: #d6dbe9; 
            margin: 15px; 
        }

        /* ==========================================================================
           2. SAP ERP Layout & Title Bar
           ========================================================================== */
        .sap-window { 
            background-color: #f2f3f5; 
            border: 1px solid #707070; 
            width: 100%; 
            max-width: 1250px; 
            margin: auto; 
            box-shadow: 2px 2px 6px rgba(0,0,0,0.3); 
        }

        .sap-titlebar { 
            background: linear-gradient(to bottom, #3f51b5, #1a237e); 
            color: #ffffff; 
            padding: 4px 8px; 
            font-weight: bold; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }

        /* ==========================================================================
           3. Form Layout & Inputs
           ========================================================================== */
        .flex-container { 
            display: flex; 
            justify-content: space-between; 
            padding: 8px; 
        }

        .sap-col { 
            width: 48%; 
        }

        .form-group { 
            display: flex; 
            margin-bottom: 4px; 
            align-items: center; 
            position: relative; 
        }

        .form-group label { 
            width: 130px; 
            color: #000000; 
        }

        .form-group input, 
        .form-group select, 
        .form-group textarea { 
            border: 1px solid #999999; 
            font-size: 11px; 
            padding: 2px 4px; 
        }

        .readonly { 
            background-color: #e9ecef; 
        }

        /* ==========================================================================
           4. Tab Containers & Line Items Table
           ========================================================================== */
        .sap-tabs { 
            border-bottom: 1px solid #adadad; 
            background: #e0e0e0; 
            padding-left: 8px; 
        }

        .tab { 
            background: #ffffff; 
            border: 1px solid #adadad; 
            border-bottom: none; 
            padding: 4px 12px; 
            font-weight: bold; 
        }

        .sap-table-container { 
            overflow-x: auto; 
            height: 220px; 
            background: #ffffff; 
            border: 1px solid #adadad; 
        }

        .sap-table { 
            width: 100%; 
            border-collapse: collapse; 
        }

        .sap-table th { 
            background: #e0e0e0; 
            border: 1px solid #b5b5b5; 
            padding: 4px; 
            text-align: left; 
        }

        .sap-table td { 
            border: 1px solid #d0d0d0; 
            padding: 2px; 
        }

        /* ==========================================================================
           5. Notifications & Type-Ahead Overlay
           ========================================================================== */
        .error-banner { 
            background-color: #d9534f; 
            color: #ffffff; 
            padding: 6px 10px; 
            font-weight: bold; 
            display: none; 
        }

        .approval-label { 
            background-color: #fff3cd; 
            color: #856404; 
            padding: 6px; 
            margin: 8px; 
            border: 1px solid #ffeeba; 
            font-weight: bold; 
            display: none; 
        }

        .suggestions { 
            position: absolute; 
            top: 22px; 
            left: 130px; 
            background: #ffffff; 
            border: 1px solid #cccccc; 
            width: 250px; 
            z-index: 100; 
            list-style: none; 
            padding: 0; 
            margin: 0; 
        }

        .suggestions li { 
            padding: 4px; 
            cursor: pointer; 
        }

        .suggestions li:hover { 
            background-color: #3f51b5; 
            color: #ffffff; 
        }

        /* ==========================================================================
           6. Buttons & Footer Action Bar
           ========================================================================== */
        .sap-actions { 
            padding: 8px; 
            background-color: #e8e8e8; 
            border-top: 1px solid #cccccc; 
            display: flex; 
            justify-content: space-between; 
        }

        .btn-primary { 
            background-color: #2b5797; 
            color: #ffffff; 
            border: 1px solid #1e395f; 
            padding: 4px 12px; 
            cursor: pointer; 
        }

        .btn-secondary { 
            background-color: #d6d6d6; 
            border: 1px solid #999999; 
            padding: 4px 12px; 
            cursor: pointer; 
        }

        .logout-btn { 
            background: #d9534f; 
            color: #ffffff; 
            border: none; 
            padding: 2px 8px; 
            font-size: 10px; 
            cursor: pointer; 
            text-decoration: none; 
            border-radius: 2px; 
        }
    </style>
</head>
<body>

<div class="sap-window">

    {{-- ----------------------------------------------------------------------
         1. Header Toolbar (User info & Session Logout)
         ---------------------------------------------------------------------- --}}
    <div class="sap-titlebar">
        <span>AR Invoice - Logged in as: {{ Auth::user()->name ?? 'User' }}</span>
        <div>
            <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="logout-btn">Log Out</button>
            </form>
        </div>
    </div>

    {{-- ----------------------------------------------------------------------
         2. Dynamic Runtime System Error Alert Bar
         ---------------------------------------------------------------------- --}}
    <div id="error-banner" class="error-banner"></div>

    {{-- ----------------------------------------------------------------------
         3. Header Input Details Section (Customer Code, Name, Doc #, Date)
         ---------------------------------------------------------------------- --}}
    <div class="flex-container">
        <!-- Left Header Column -->
        <div class="sap-col">
            <div class="form-group">
                <label>Customer Code</label>
                <input type="text" id="customer_code" onkeyup="searchCustomer(this.value)" autocomplete="off">
                <ul id="cust-suggestions" class="suggestions" style="display:none;"></ul>
            </div>
            <div class="form-group">
                <label>Customer Name</label>
                <input type="text" id="customer_name" onkeyup="searchCustomerName(this.value)" autocomplete="off">
                <ul id="cust-name-suggestions" class="suggestions" style="display:none;"></ul>
            </div>
        </div>

        <!-- Right Header Column -->
        <div class="sap-col" style="text-align: right;">
            <div class="form-group" style="justify-content: flex-end;">
                <label>No.</label>
                <input type="text" value="{{ $nextDocNum }}" class="readonly" readonly>
            </div>
            <div class="form-group" style="justify-content: flex-end;">
                <label>Posting Date</label>
                <input type="date" id="posting_date" value="{{ date('Y-m-d') }}">
            </div>
        </div>
    </div>

    {{-- ----------------------------------------------------------------------
         4. Contents Tab Bar
         ---------------------------------------------------------------------- --}}
    <div class="sap-tabs">
        <button class="tab" type="button">Contents</button>
    </div>

    {{-- ----------------------------------------------------------------------
         5. Dynamic Line Items Data Grid
         ---------------------------------------------------------------------- --}}
    <div class="sap-table-container">
        <table class="sap-table" id="invoice-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item No.</th>
                    <th>Item Description</th>
                    <th>Quantity</th>
                    <th>Price Before Disc.</th>
                    <th>Discount %</th>
                    <th>Price after Disc.</th>
                    <th>Total (LC)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="row-index">1</td>
                    <td>
                        <select onchange="selectItem(this)" class="line-item-select">
                            <option value="">--Select Item--</option>
                            @foreach($items as $item)
                                <option value="{{ $item->item_no }}" 
                                        data-desc="{{ $item->item_description }}" 
                                        data-price="{{ $item->unit_price }}">
                                    {{ $item->item_no }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="text" class="line-desc" readonly></td>
                    <td><input type="number" class="line-qty" value="1" min="1" oninput="calculateRow(this)"></td>
                    <td><input type="number" step="0.001" class="line-price" value="0.000" oninput="calculateRow(this)"></td>
                    <td><input type="number" step="0.001" class="line-disc" value="0.000" oninput="calculateRow(this)"></td>
                    <td><input type="text" class="line-after-disc readonly" readonly value="0.000"></td>
                    <td><input type="text" class="line-total readonly" readonly value="0.000"></td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- ----------------------------------------------------------------------
         6. Threshold Approval Notification Display
         ---------------------------------------------------------------------- --}}
    <div id="approval-label" class="approval-label"></div>

    {{-- ----------------------------------------------------------------------
         7. Footer Section (Sales Rep, Remarks, Document Totals)
         ---------------------------------------------------------------------- --}}
    <div class="flex-container">
        <!-- Left Footer Column -->
        <div class="sap-col">
            <div class="form-group">
                <label>Sales Employee</label>
                <input type="text" hidden id="sales_employee_id">
                <input type="text" id="sales_employee_name" onkeyup="searchEmployeeName(this.value)" autocomplete="off">
                <ul id="emplo-name-suggestions" class="suggestions" style="display:none;"></ul>
            </div>
            <div class="form-group">
                <label>Remarks *</label>
                <textarea id="remarks" rows="3" style="width: 250px;"></textarea>
            </div>
        </div>

        <!-- Right Footer Column (Calculated Totals) -->
        <div class="sap-col" style="text-align: right;">
            <div class="form-group" style="justify-content: flex-end;">
                <label>Total Before Discount</label>
                <input type="text" id="total-before-disc" class="readonly" readonly value="0.000">
            </div>
            <div class="form-group" style="justify-content: flex-end;">
                <label>Total After Discount</label>
                <input type="text" id="total-after-disc" class="readonly" readonly value="0.000">
            </div>
        </div>
    </div>

    {{-- ----------------------------------------------------------------------
         8. Document Footer Action Controls
         ---------------------------------------------------------------------- --}}
    <div class="sap-actions">
        <div>
            <button class="btn-primary" type="button" onclick="submitInvoice()">Add & New</button>
            <button class="btn-secondary" type="button" onclick="addNewRow()">Add Line</button>
        </div>
        <button class="btn-secondary" type="button" onclick="window.location.reload()">Cancel</button>
    </div>

</div>

{{-- ==========================================================================
     Dynamic Client-Side Calculations & API Interactive Scripting
     ========================================================================== --}}
<script>
    /* ------------------------------------------------------------------------
     * Module A: Line Item Calculations & Totals Accumulation
     * ------------------------------------------------------------------------ */

    /**
     * Triggered when an Item is selected from a table row dropdown.
     * Auto-populates description & price fields.
     */
    function selectItem(selectElem) {
        let selectedOption = selectElem.options[selectElem.selectedIndex];
        let row = selectElem.closest('tr');
        
        row.querySelector('.line-desc').value = selectedOption.dataset.desc || '';
        row.querySelector('.line-price').value = parseFloat(selectedOption.dataset.price || 0).toFixed(3);
        calculateRow(selectElem);
    }

    /**
     * Calculates line unit totals, applies line-item discounts, and verifies 50% cap.
     */
    function calculateRow(element) {
        let row = element.closest('tr');
        let qty = parseFloat(row.querySelector('.line-qty').value) || 0;
        let price = parseFloat(row.querySelector('.line-price').value) || 0;
        let disc = parseFloat(row.querySelector('.line-disc').value) || 0;

        let errorBanner = document.getElementById('error-banner');
        if (disc > 50) {
            errorBanner.innerText = "Validation Error: Line Discount cannot exceed 50%.";
            errorBanner.style.display = "block";
        } else {
            errorBanner.style.display = "none";
        }

        let priceAfterDisc = price - (price * (disc / 100));
        let lineTotal = qty * priceAfterDisc;

        row.querySelector('.line-after-disc').value = priceAfterDisc.toFixed(3);
        row.querySelector('.line-total').value = lineTotal.toFixed(3);

        calculateTotals();
    }

    /**
     * Computes document sum totals and checks approval workflow limit (> 10,000).
     */
    function calculateTotals() {
        let totalBefore = 0;
        let totalAfter = 0;

        document.querySelectorAll('#invoice-table tbody tr').forEach(row => {
            let qty = parseFloat(row.querySelector('.line-qty').value) || 0;
            let price = parseFloat(row.querySelector('.line-price').value) || 0;
            let lineTotal = parseFloat(row.querySelector('.line-total').value) || 0;

            totalBefore += (qty * price);
            totalAfter += lineTotal;
        });

        document.getElementById('total-before-disc').value = totalBefore.toFixed(3);
        document.getElementById('total-after-disc').value = totalAfter.toFixed(3);

        let approvalLabel = document.getElementById('approval-label');
        if (totalAfter > 10000) {
            approvalLabel.innerText = `Invoice will go for approval – Amount: KES ${totalAfter.toFixed(3)}`;
            approvalLabel.style.display = "block";
        } else {
            approvalLabel.style.display = "none";
        }
    }

    /**
     * Appends a new blank row to the invoice line items table.
     */
    function addNewRow() {
        let table = document.querySelectorAll('#invoice-table tbody')[0];
        let firstRow = table.rows[0];
        let newRow = firstRow.cloneNode(true);
        
        // Reset cloned field values
        newRow.querySelector('.line-desc').value = '';
        newRow.querySelector('.line-qty').value = 1;
        newRow.querySelector('.line-price').value = '0.000';
        newRow.querySelector('.line-disc').value = '0.000';
        newRow.querySelector('.line-after-disc').value = '0.000';
        newRow.querySelector('.line-total').value = '0.000';
        newRow.querySelector('.line-item-select').selectedIndex = 0;

        table.appendChild(newRow);
        
        // Re-index row sequence numbers
        Array.from(table.rows).forEach((row, idx) => {
            row.querySelector('.row-index').innerText = idx + 1;
        });
    }

    /* ------------------------------------------------------------------------
     * Module B: Predictive Live-Search Handlers (AJAX Auto-suggest)
     * ------------------------------------------------------------------------ */

    /**
     * Search customers by Customer Name.
     */
    function searchCustomerName(query) {
        let sugBox = document.getElementById('cust-name-suggestions');
        if (query.length < 1) {
            sugBox.style.display = "none";
            return;
        }
        fetch(`/api/customers/search?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                sugBox.innerHTML = '';
                data.forEach(cust => {
                    let li = document.createElement('li');
                    li.innerText = `${cust.customer_name} - ${cust.customer_code}`;
                    li.onclick = () => {
                        document.getElementById('customer_code').value = cust.customer_code;
                        document.getElementById('customer_name').value = cust.customer_name;
                        sugBox.style.display = "none";
                    };
                    sugBox.appendChild(li);
                });
                sugBox.style.display = "block";
            });
    }

    /**
     * Search customers by Customer Code.
     */
    function searchCustomer(query) {
        let sugBox = document.getElementById('cust-suggestions');
        if (query.length < 1) {
            sugBox.style.display = "none";
            return;
        }
        fetch(`/api/customers/search?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                sugBox.innerHTML = '';
                data.forEach(cust => {
                    let li = document.createElement('li');
                    li.innerText = `${cust.customer_code} - ${cust.customer_name}`;
                    li.onclick = () => {
                        document.getElementById('customer_code').value = cust.customer_code;
                        document.getElementById('customer_name').value = cust.customer_name;
                        sugBox.style.display = "none";
                    };
                    sugBox.appendChild(li);
                });
                sugBox.style.display = "block";
            });
    }

    /**
     * Search Sales Employees by Employee Name.
     */
    function searchEmployeeName(query) {
        let sugBox = document.getElementById('emplo-name-suggestions');
        if (query.length < 1) {
            sugBox.style.display = "none";
            return;
        }
        fetch(`/api/search-sales-employees?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                sugBox.innerHTML = '';
                data.forEach(employee => {
                    let li = document.createElement('li');
                    li.innerText = `${employee.emp_name}`;
                    li.onclick = () => {
                        document.getElementById('sales_employee_id').value = employee.emp_id;
                        document.getElementById('sales_employee_name').value = employee.emp_name;
                        sugBox.style.display = "none";
                    };
                    sugBox.appendChild(li);
                });
                sugBox.style.display = "block";
            });
    }

    /* ------------------------------------------------------------------------
     * Module C: Document POST Submission Handler
     * ------------------------------------------------------------------------ */

    /**
     * Validates and submits the complete invoice document payload to backend.
     */
    function submitInvoice() {
        let remarks = document.getElementById('remarks').value;
        let errorBanner = document.getElementById('error-banner');

        if (!remarks.trim()) {
            errorBanner.innerText = "Validation Error: Remarks field is mandatory.";
            errorBanner.style.display = "block";
            return;
        }

        let lines = [];
        document.querySelectorAll('#invoice-table tbody tr').forEach(row => {
            let itemNo = row.querySelector('.line-item-select').value;
            if (itemNo) {
                lines.push({
                    item_no: itemNo,
                    item_description: row.querySelector('.line-desc').value,
                    quantity: row.querySelector('.line-qty').value,
                    price_before_discount: row.querySelector('.line-price').value,
                    discount: row.querySelector('.line-disc').value,
                });
            }
        });

        let payload = {
            customer_code: document.getElementById('customer_code').value,
            posting_date: document.getElementById('posting_date').value,
            sales_employee_id: document.getElementById('sales_employee_id').value,
            remarks: remarks,
            lines: lines
        };

        fetch('/invoice/store', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(`Invoice successfully added! Doc Num: ${data.doc_num}`);
                window.location.reload();
            } else {
                errorBanner.innerText = data.errors.join(' | ');
                errorBanner.style.display = "block";
            }
        });
    }
</script>

</body>
</html>