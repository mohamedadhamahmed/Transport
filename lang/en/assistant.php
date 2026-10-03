<?php

// Assistant Widget Data - Tailored for Transportation & Fleet ERP.

return array (
  'title' => 'Assistant',
  'subtitle' => 'Choose a section and what you want to do, and we\'ll walk you through it',
  'back' => 'Back',
  'sections_title' => 'Sections',
  'close' => 'Close',
  'sections' => 
  array (
    0 => 
    array (
      'key' => 'loads',
      'label' => 'Truck Operations & Waybills',
      'icon' => 'truck',
      'items' => 
      array (
        0 => 
        array (
          'key' => 'new_load',
          'label' => 'Create New Waybill (Load Truck)',
          'steps' => 
          array (
            0 => 'From the sidebar "Truck Operations" or header button "Load Truck" / "New Waybill".',
            1 => 'Select the customer from the "Customer *" dropdown, or click "Add New Customer" directly.',
            2 => 'Select the "Truck *" from available trucks; default driver and cost center will be filled automatically.',
            3 => 'Set trip route: "Loading City", "Destination City", and scheduled pickup date/time.',
            4 => 'Enter cargo details: cargo type, approximate weight, package count, and shipping notes.',
            5 => 'Set the agreed freight rate / shipping charge and payment method (Cash / On Account / Pay on Delivery).',
            6 => 'Click "Save Waybill" to generate the shipment order and change truck status to "En Route".',
          ),
        ),
        1 => 
        array (
          'key' => 'trucks_board',
          'label' => 'Fleet Board & Status Tracking',
          'steps' => 
          array (
            0 => 'Open "Fleet Board" to view real-time distribution and status cards of your entire fleet.',
            1 => 'Browse status columns: (Available Trucks, En Route, Delayed Unloading, Under Maintenance).',
            2 => 'Check the smart oil change alert badge on each truck card (Green: Good, Yellow: Due Soon, Red: Overdue).',
            3 => 'Click on any truck card to register arrival, complete unloading, or update current odometer reading.',
            4 => 'Quickly toggle a truck to maintenance or back to ready with a single click.',
          ),
        ),
        2 => 
        array (
          'key' => 'convert_to_invoice',
          'label' => 'Convert Waybills to Transport Invoice',
          'steps' => 
          array (
            0 => 'Navigate to "Transport Invoices" -> "New Transport Invoice" or use the header shortcut.',
            1 => 'Select the "Customer"; all completed, uninvoiced waybills for this customer will be listed.',
            2 => 'Check the waybills you wish to include in this tax invoice.',
            3 => 'Review trip amounts, 15% VAT, and any additional services or discounts.',
            4 => 'Click "Issue Invoice" to generate an official e-invoice with verified QR code and auto-post ledger entries.',
          ),
        ),
      ),
    ),
    1 => 
    array (
      'key' => 'trucks',
      'label' => 'Fleet & Trucks',
      'icon' => 'truck',
      'items' => 
      array (
        0 => 
        array (
          'key' => 'new_truck',
          'label' => 'Add New Truck to Fleet',
          'steps' => 
          array (
            0 => 'From the sidebar "Trucks", click "Add New Truck".',
            1 => 'Enter "Plate Number *", truck type, model, manufacture year, and chassis number.',
            2 => 'Select "Default Driver", "Branch", and "Cost Center" (a cost center is also created automatically).',
            3 => 'Enter "Current Odometer (KM)" reading accurately.',
            4 => 'Choose "Oil Change Interval (KM)" (4,000, 5,000, or 10,000 km) for automatic maintenance alerts.',
            5 => 'Enter registration, inspection, and insurance expiry dates to receive renewal alerts.',
            6 => 'Click "Save Truck" to add it to your fleet.',
          ),
        ),
        1 => 
        array (
          'key' => 'toggle_maintenance',
          'label' => 'Toggle Truck to Maintenance (1-Click)',
          'steps' => 
          array (
            0 => 'On the "Fleet Board" or "Trucks" table, locate the quick maintenance button beside each truck.',
            1 => 'Click "Maintenance" to instantly move the truck into maintenance mode and prevent new bookings.',
            2 => 'Once workshop repairs are done, click "Ready for Work" to return it to active duty immediately.',
          ),
        ),
      ),
    ),
    2 => 
    array (
      'key' => 'truck_store_issues',
      'label' => 'Parts & Oil Store Issue',
      'icon' => 'wrench',
      'items' => 
      array (
        0 => 
        array (
          'key' => 'new_store_issue',
          'label' => 'Issue Oils, Tires or Parts to a Truck',
          'steps' => 
          array (
            0 => 'From the sidebar under "Trucks", select "Parts & Oil Store Issue".',
            1 => 'Select the target "Truck *"; driver, cost center, and latest odometer are loaded automatically.',
            2 => 'Enter the "Current Odometer" reading at the time of issuance.',
            3 => 'Select "Oil Change Interval" (4k / 5k / 10k km); the system calculates "Next Oil Change Odometer" instantly.',
            4 => 'Select expense category (Oils / Tires / Spare Parts / General Maintenance).',
            5 => 'In the items table, choose items from stock; current inventory balance and unit cost appear automatically.',
            6 => 'Enter exact issued quantity (e.g., 20 liters of oil, 2 tires). Use "+ Add Item" for multiple lines.',
            7 => 'Click "Save Store Issue"; stock is deducted immediately and an accounting entry is posted (Debit: Truck Maintenance Expense, Credit: Inventory 181) without touching cash or banks.',
          ),
        ),
        1 => 
        array (
          'key' => 'view_issues',
          'label' => 'Review & Print Previous Store Issues',
          'steps' => 
          array (
            0 => 'From the sidebar under "Trucks", select "Truck Store Issues".',
            1 => 'Browse past issue records with issue number, date, truck, odometer, and total cost.',
            2 => 'Click "View" to see full item details and associated double-entry journal lines.',
            3 => 'Print the issuance slip for the workshop or archives.',
            4 => 'Canceling an issue automatically restores quantities back to stock and reverses journal entries.',
          ),
        ),
        2 => 
        array (
          'key' => 'parts_report',
          'label' => 'Parts Consumption & Oil Change Report',
          'steps' => 
          array (
            0 => 'From "Trucks", select "Parts & Oil Issue Report".',
            1 => 'Filter by date range, truck, or category (Oils / Tires / Parts).',
            2 => 'View total expenditure and quantities consumed across the fleet.',
            3 => 'Inspect the "Fleet Oil Status Table" to see remaining kilometers for each truck and spot overdue oil changes.',
          ),
        ),
      ),
    ),
    3 => 
    array (
      'key' => 'drivers',
      'label' => 'Drivers & HR',
      'icon' => 'user',
      'items' => 
      array (
        0 => 
        array (
          'key' => 'new_driver',
          'label' => 'Add Driver (Auto-Synced with HR & Accounts)',
          'steps' => 
          array (
            0 => 'From the sidebar "Drivers", click "Add New Driver".',
            1 => 'Enter full name, National ID / Iqama, mobile phone, and monthly salary.',
            2 => 'Select driver type "Company Driver".',
            3 => 'Enter driving license details and expiration date.',
            4 => 'Click "Save Driver"; the system automatically creates an employee record in HR (EMP-...) and opens dedicated accounts in the Chart of Accounts.',
          ),
        ),
        1 => 
        array (
          'key' => 'driver_custody',
          'label' => 'Issue & Settle Driver Travel Custody',
          'steps' => 
          array (
            0 => 'From "Vouchers" choose "New Payment Voucher" or from HR "Employee Loans".',
            1 => 'Select the driver as the debit account and cash/bank as credit account.',
            2 => 'Enter custody amount for road expenses (diesel, tolls, weighbridges).',
            3 => 'Upon trip completion, settle receipts against the truck cost center and return surplus to treasury.',
          ),
        ),
      ),
    ),
    4 => 
    array (
      'key' => 'transport_invoices',
      'label' => 'Transport Invoices & Quotations',
      'icon' => 'doc',
      'items' => 
      array (
        0 => 
        array (
          'key' => 'new_transport_invoice',
          'label' => 'Issue Tax Transport Invoice',
          'steps' => 
          array (
            0 => 'From "Transport Invoices", click "+ New Transport Invoice".',
            1 => 'Select the customer and payment terms (Cash / Bank Transfer / Credit).',
            2 => 'Add freight items or trip details (route, number of trips, rate, VAT rate).',
            3 => 'Review totals (Subtotal, 15% VAT, Grand Total).',
            4 => 'Click "Save Invoice" to print and generate ZATCA compliant QR code.',
          ),
        ),
        1 => 
        array (
          'key' => 'new_quotation',
          'label' => 'Create Quotation for Client',
          'steps' => 
          array (
            0 => 'From "Transport Invoices", choose "New Quotation".',
            1 => 'Select customer, routes, and proposed freight rates.',
            2 => 'Add freight terms, validity period, and notes.',
            3 => 'Save and export as PDF to send to the client.',
          ),
        ),
      ),
    ),
    5 => 
    array (
      'key' => 'purchases',
      'label' => 'Purchases & Spare Parts',
      'icon' => 'cart',
      'items' => 
      array (
        0 => 
        array (
          'key' => 'new_purchase',
          'label' => 'Purchase Oils, Tires & Parts to Inventory',
          'steps' => 
          array (
            0 => 'From "Purchase Invoices", click "+ New Purchase Invoice".',
            1 => 'Select the supplier (oil supplier, tire dealer, workshop).',
            2 => 'Choose target parts warehouse, branch, and payment terms.',
            3 => 'Enter supplier invoice number and date.',
            4 => 'Add purchased items (oil barrels, tires, filters) with quantity, unit cost, and tax rate.',
            5 => 'Save invoice; items are immediately added to stock ready for truck issuance.',
          ),
        ),
        1 => 
        array (
          'key' => 'new_product',
          'label' => 'Add New Item (Oil / Tire / Part) in Stock',
          'steps' => 
          array (
            0 => 'From "Products & Inventory", click "Add Product".',
            1 => 'Enter item name (e.g., Rimula 15W-40 Oil, 315/80R22.5 Tire).',
            2 => 'Select unit of measure (Liter / Piece / Set / Drum) and default cost.',
            3 => 'Assign branch and warehouse, then save.',
          ),
        ),
      ),
    ),
    6 => 
    array (
      'key' => 'accounting',
      'label' => 'Accounting & Cost Centers',
      'icon' => 'ledger',
      'items' => 
      array (
        0 => 
        array (
          'key' => 'truck_expense_voucher',
          'label' => 'Payment Voucher for Truck Maintenance / Expense',
          'steps' => 
          array (
            0 => 'Click header button "New Truck Maintenance/Expense Voucher" or "Vouchers" -> "New Payment Voucher".',
            1 => 'Select debit expense account (Maintenance, Tires, Fuel, Road fees).',
            2 => 'Select credit payment account (Cash treasury or bank account).',
            3 => 'Crucial: Select the target truck "Cost Center" to allocate the expense directly to the truck P&L.',
            4 => 'Enter amount, description, attach bill, and save.',
          ),
        ),
        1 => 
        array (
          'key' => 'receipt_voucher',
          'label' => 'Receipt Voucher from Transport Client',
          'steps' => 
          array (
            0 => 'Click "New Receipt Voucher" from header or "Vouchers".',
            1 => 'Select the customer account.',
            2 => 'Select the receiving cash or bank account.',
            3 => 'Enter received amount, bank transfer reference, and description.',
            4 => 'Save voucher to settle customer ledger and credit treasury.',
          ),
        ),
        2 => 
        array (
          'key' => 'journal_entry',
          'label' => 'Create General Journal Entry',
          'steps' => 
          array (
            0 => 'From "Journal Entries", click "New Journal Entry".',
            1 => 'Enter entry date and general description.',
            2 => 'Add debit and credit accounts, linking truck cost centers to lines.',
            3 => 'Verify debit equals credit, then save.',
          ),
        ),
      ),
    ),
    7 => 
    array (
      'key' => 'entities',
      'label' => 'Customers & Suppliers',
      'icon' => 'store',
      'items' => 
      array (
        0 => 
        array (
          'key' => 'new_customer',
          'label' => 'Add New Transport Customer',
          'steps' => 
          array (
            0 => 'From "Customers" sidebar or header "+ New Customer" button.',
            1 => 'Enter customer/company name, phone number, and email.',
            2 => 'Enter VAT number, commercial registration, and national address.',
            3 => 'Set credit limit, payment grace days, and opening balance.',
            4 => 'Click Save; a dedicated financial ledger is created in the Chart of Accounts automatically.',
          ),
        ),
        1 => 
        array (
          'key' => 'new_supplier',
          'label' => 'Add Parts / Service Supplier',
          'steps' => 
          array (
            0 => 'From "Suppliers", click "Add New Supplier".',
            1 => 'Enter supplier name (fuel stations, external workshops, parts vendor).',
            2 => 'Enter phone number, tax number, and address, then save.',
          ),
        ),
      ),
    ),
    8 => 
    array (
      'key' => 'hr',
      'label' => 'HR & Payroll',
      'icon' => 'user',
      'items' => 
      array (
        0 => 
        array (
          'key' => 'payroll',
          'label' => 'Drivers & Staff Payroll Run',
          'steps' => 
          array (
            0 => 'From "Human Resources", choose "Payroll" -> "New Payroll Run".',
            1 => 'Select month, year, and branch.',
            2 => 'The system computes base salaries, allowances, loans, and deductions automatically.',
            3 => 'Approve payroll to post accounting accruals and bank payment orders.',
          ),
        ),
        1 => 
        array (
          'key' => 'documents',
          'label' => 'Track Expiring Licenses & Registrations',
          'steps' => 
          array (
            0 => 'From Dashboard or HR, check the "Expiring Documents" widget.',
            1 => 'Monitor driver license expirations for timely renewal.',
            2 => 'Track truck registrations and Transport General Authority operation cards to avoid penalties.',
          ),
        ),
      ),
    ),
    9 => 
    array (
      'key' => 'reports',
      'label' => 'Operational & Financial Reports',
      'icon' => 'doc',
      'items' => 
      array (
        0 => 
        array (
          'key' => 'truck_profitability',
          'label' => 'Truck Profitability & Cost Center Report',
          'steps' => 
          array (
            0 => 'From "Reports", choose Cost Center or Truck Profitability reports.',
            1 => 'Select truck and date range (month, quarter, year).',
            2 => 'Inspect freight revenue vs expenses (fuel, maintenance, oils, parts, driver) and net margin.',
          ),
        ),
        1 => 
        array (
          'key' => 'oil_report',
          'label' => 'Parts Consumption & Oil Change Report',
          'steps' => 
          array (
            0 => 'From "Trucks" -> "Parts & Oil Issue Report".',
            1 => 'View total quantities and costs of consumed oils and tires.',
            2 => 'Check fleet oil status to dispatch trucks due for service.',
          ),
        ),
        2 => 
        array (
          'key' => 'statement_report',
          'label' => 'Customer / Supplier Account Statement',
          'steps' => 
          array (
            0 => 'From "Chart of Accounts" or "Financial Reports", select "Account Statement".',
            1 => 'Select customer or supplier and date period.',
            2 => 'View all invoices, waybills, and vouchers with current balance, print or export.',
          ),
        ),
      ),
    ),
  ),
);
