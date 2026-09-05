<?php

// ملف بيانات ودجت "المساعد" - يتولد آليًا، لا تعدله يدويًا بدون مراجعة النص العربي/الإنجليزي المقابل.

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
      'key' => 'sales',
      'label' => 'Sales',
      'icon' => 'bag',
      'items' => 
      array (
        0 => 
        array (
          'key' => 'new_invoice',
          'label' => 'Create New Sales Invoice',
          'steps' => 
          array (
            0 => 'On the "New Sales Invoice" screen, choose the customer from the "Customer" dropdown, or click "Add New Customer" to add one without leaving the page.',
            1 => 'Choose the "Payment Method" (Cash / Bank Transfer / Card / Credit / Split Payment); choosing "Split Payment" reveals "Cash Amount" and "Bank/Card Amount" fields.',
            2 => 'The "Branch" is set automatically to your current branch; pick the "Tax" rate applied to all items, and add "Notes" if needed.',
            3 => 'Search for a product in the search box or click "Choose Product" to open the full product picker (with search and paging); repeat this to add multiple line items.',
            4 => 'For each item in the table, adjust "Quantity", "Unit Price" and "Discount" as needed — the line total and profit update automatically.',
            5 => 'Enter an "Invoice Discount" (extra discount on the whole invoice) and a "P.O. Number" if one applies.',
            6 => 'Review the totals below the table (Subtotal, Total Discount, Total Tax, Total Profit, Grand Total).',
            7 => 'Click "Save Invoice" to record it as an official invoice immediately, or "Save as Draft" to finish it later from "Previous Drafts".',
          ),
        ),
        1 => 
        array (
          'key' => 'new_return',
          'label' => 'Create Sales Return',
          'steps' => 
          array (
            0 => 'On the "Sales Return" screen, search for the original invoice in the "Invoice" field by invoice number or customer name, or click "Search".',
            1 => 'Select the invoice from the search results; its details appear (Invoice Number, Customer, Payment Method, Grand Total).',
            2 => 'In the items table, enter the "Return Quantity" for each item you want to return (capped by the "Available to Return" column).',
            3 => 'If part of the refund is actual cash, choose a "Refund Method" (Cash / Bank Transfer / Card).',
            4 => 'Review "Deducted from Customer Credit Balance", "Actual Refund Amount" and "Total Return" below the table.',
            5 => 'Click "Save Return" to record it, or "Cancel" to go back to the invoice list.',
          ),
        ),
        2 => 
        array (
          'key' => 'new_quotation',
          'label' => 'Create New Quotation',
          'steps' => 
          array (
            0 => 'On the "New Quotation" screen, choose the "Customer" from the dropdown, or click "Add New Customer" to add one directly.',
            1 => 'Choose the "Payment Method"; selecting "Split Payment" reveals "Cash Amount" and "Bank Amount" fields.',
            2 => 'The "Branch" is set automatically; pick the "Tax" rate and add "Note" if needed.',
            3 => 'If this customer has earlier quotations, they appear automatically in the "Previous quotations for this customer" table for reference.',
            4 => 'Search for a product or click "Choose Product" to add it to the table; repeat to add multiple items.',
            5 => 'Adjust "Quantity", "Unit Price" and "Discount" for each item as needed.',
            6 => 'Enter an "Extra Discount" and a "PO Number" if applicable, and review the totals below the table.',
            7 => 'Click "Save Quotation" to save it, or "Cancel" to go back.',
          ),
        ),
      ),
    ),
    1 => 
    array (
      'key' => 'delivery',
      'label' => 'Product Delivery',
      'icon' => 'box',
      'items' => 
      array (
        0 => 
        array (
          'key' => 'new_delivery',
          'label' => 'Deliver Product to Customer',
          'steps' => 
          array (
            0 => 'On the "Deliver Product" screen, choose the customer from "Choose Customer" (defaults to "CASH CUSTOMER"), or click "Add New Customer".',
            1 => 'Choose the "Payment Method"; selecting "Split (Cash & Bank)" reveals "Cash Amount" and "Bank Amount" fields.',
            2 => 'Add "Notes" and a "P.O#" if applicable.',
            3 => 'Search for a product or click "Choose Product", or click "New Product" to add one that doesn\'t exist yet; repeat to add multiple items.',
            4 => 'Adjust "Quantity", "Unit Price" and "Discount" for each item in the table.',
            5 => 'Enter a "Discount on Invoice" below the table and review the totals.',
            6 => 'Click "Save Delivery" to record it, or "Cancel" to go back.',
          ),
        ),
      ),
    ),
    2 => 
    array (
      'key' => 'delivery_note',
      'label' => 'Delivery Note',
      'icon' => 'box',
      'items' => 
      array (
        0 => 
        array (
          'key' => 'new_delivery_note',
          'label' => 'Create Pending Delivery Note',
          'steps' => 
          array (
            0 => 'On the delivery note screen, choose the customer from "Choose Customer" (defaults to "CASH CUSTOMER"), or click "Add New Customer".',
            1 => 'Add "Notes" and a "P.O#" if applicable — there\'s no payment method here since invoicing happens later on approval.',
            2 => 'Search for a product or click "Choose Product", or "New Product" to add a new one; repeat to add multiple items.',
            3 => 'Adjust "Quantity", "Unit Price" and "Discount" for each item in the table.',
            4 => 'Enter a "Discount on Invoice" and review the "Estimated Pending Value" below the table.',
            5 => 'Click "Save Delivery Note" to record it as pending until it\'s later converted into an invoice, or "Cancel".',
          ),
        ),
        1 => 
        array (
          'key' => 'approve_convert',
          'label' => 'Approve Delivery Note & Convert to Invoice',
          'steps' => 
          array (
            0 => 'From the "Approve & Convert to Invoice" screen, you\'ll see a list of customers who still have pending, un-invoiced delivered quantities.',
            1 => 'Click "Review Pending" next to the customer you want, to open all of their pending quantities.',
            2 => 'Enter the "Quantity to Invoice" for each item you want to convert into an invoice now.',
            3 => 'Choose the "Payment Method"; if you pick Split Payment enter the cash and bank/card amounts, and add a note if needed.',
            4 => 'Review the totals: "Subtotal", "Total Tax", "Grand Total (incl. tax)".',
            5 => 'Click "Approve & Convert to Invoice" to create a real tax invoice for the selected quantities; this action is final and cannot be undone.',
          ),
        ),
      ),
    ),
    3 => 
    array (
      'key' => 'purchases',
      'label' => 'Purchases',
      'icon' => 'cart',
      'items' => 
      array (
        0 => 
        array (
          'key' => 'new_purchase',
          'label' => 'Create New Purchase Invoice',
          'steps' => 
          array (
            0 => 'On the "New Purchase Invoice" screen, choose the "Supplier" or click "Add New Supplier" to add one directly.',
            1 => 'Choose the "Branch" (this loads its payment accounts), then pick the "Payment Method" from the branch\'s accounts, or leave it as "Credit (on supplier account)".',
            2 => 'Enter the "Supplier invoice number" and "Invoice date", the "Warehouse" and "Cost center" (or click "+" to add a new cost center), the "Shipping fee" and "Note".',
            3 => 'Upload any "Invoice attachments (PDF or image)" if you have them.',
            4 => 'Search for a product or click "Choose product" to add it to the table, or click "Download Excel template" then "Import from Excel" to add many items at once.',
            5 => 'For each item, adjust "Quantity", "Purchase unit price", "Sale price (optional)", "Discount" and the "Tax" rate.',
            6 => 'Enter an "Extra invoice discount" and review the totals (including shipping) below the table.',
            7 => 'Click "Save purchase invoice" to record it, or "Cancel" to go back.',
          ),
        ),
        1 => 
        array (
          'key' => 'new_return',
          'label' => 'Create Purchase Return',
          'steps' => 
          array (
            0 => 'On the "New Purchase Return" screen, search for the original purchase invoice in the search box and click "Search".',
            1 => 'Select the invoice you want from the results; its details appear (Invoice Number, Supplier, Payment Method).',
            2 => 'If the invoice was paid immediately, choose the "Refund Account" the money should return to; if it was on credit, the amount is credited back to the supplier\'s account automatically instead.',
            3 => 'Enter the "Return Date" and the "Reason" for the return.',
            4 => 'In the items table, enter the "Return Quantity" for each item you want to return (capped by what\'s still available to return).',
            5 => 'Review the totals (Subtotal, Total Tax, Grand Total) below the table.',
            6 => 'Click "Save Return" to record it, or "Cancel" to go back to the returns list.',
          ),
        ),
        2 => 
        array (
          'key' => 'new_order',
          'label' => 'Create New Purchase Order',
          'steps' => 
          array (
            0 => 'From the "Purchase Orders" list, click the "+ New Purchase Order" button at the top to open a separate order-creation screen.',
            1 => 'Choose the "Supplier" and the "Branch" the order should be recorded under.',
            2 => 'Enter the "Warehouse", "Cost center", "Shipping fee", "Invoice date" and "Note" if needed.',
            3 => 'Search for a product or click "Choose Product" to add it to the items table; repeat to add multiple items.',
            4 => 'For each item set the "Quantity", "Unit Price", "Discount" and the "Tax" rate.',
            5 => 'Enter an "Extra Invoice Discount" if needed and review the automatically calculated totals.',
            6 => 'Save the purchase order; it is only a purchase intent and does not affect stock or accounting until it\'s later converted into a real purchase invoice.',
          ),
        ),
      ),
    ),
    4 => 
    array (
      'key' => 'stock_transfers',
      'label' => 'Stock Transfers',
      'icon' => 'box',
      'items' => 
      array (
        0 => 
        array (
          'key' => 'new_dispatch',
          'label' => 'Dispatch Stock to Another Branch',
          'steps' => 
          array (
            0 => 'On the "Select Sending Branch" screen, click the branch you\'re working from (each branch has its own dispatch voucher).',
            1 => 'On the "Dispatch Products to Another Branch" screen, choose the "Receiving Branch" from the dropdown.',
            2 => 'Choose the "Receiving Employee" (this list becomes active once the receiving branch is selected).',
            3 => 'Adjust the "Date" if needed, and add "Notes".',
            4 => 'Click "Pick Product" to open the full list of products available in your branch, or use the quick search box next to it; repeat to add multiple items.',
            5 => 'For each added item, set the "Quantity" to send (capped by the "Available Stock" in your branch).',
            6 => 'Click "Save as Draft" to finish it later, or "Confirm Dispatch" to actually send the voucher to the receiving branch.',
          ),
        ),
        1 => 
        array (
          'key' => 'receive',
          'label' => 'Receive Stock Transfer',
          'steps' => 
          array (
            0 => 'On the "Select Receiving Branch" screen, click your branch to open its transfer-receiving screen.',
            1 => 'On the "Receive Products from Another Branch" screen, choose the voucher from the "Voucher No." dropdown (only vouchers pending receipt for your branch are listed).',
            2 => 'Review the voucher details that appear automatically: "Sending Branch", "Sending Employee", "Date".',
            3 => 'Review the items table (Product and Quantity) sent to you.',
            4 => 'Click "Confirm Receipt" to confirm receiving the quantities and add them to your branch stock.',
          ),
        ),
      ),
    ),
    5 => 
    array (
      'key' => 'customers',
      'label' => 'Customers',
      'icon' => 'store',
      'items' => 
      array (
        0 => 
        array (
          'key' => 'new_customer',
          'label' => 'New Customer',
          'steps' => 
          array (
            0 => 'Type the "Name" (required).',
            1 => 'Enter "Phone" (required) and "Email" (optional).',
            2 => 'If the customer is a company, fill in "Company Name", "Tax Number" and "Commercial Registration No.".',
            3 => 'Set the "Credit Limit" and "Grace Period (Days)" allowed for this customer.',
            4 => 'Enter an "Opening Balance" if the customer already had a balance before you started using the system.',
            5 => 'Fill in the "National Address" fields: City, District, Street Name, Building Number, Additional Number, Postal Code.',
            6 => 'Add "Notes" if needed.',
            7 => 'Click "Save" to add the customer - a linked account is automatically created for them in the Chart of Accounts.',
          ),
        ),
      ),
    ),
    6 => 
    array (
      'key' => 'suppliers',
      'label' => 'Suppliers',
      'icon' => 'store',
      'items' => 
      array (
        0 => 
        array (
          'key' => 'new_supplier',
          'label' => 'New Supplier',
          'steps' => 
          array (
            0 => 'Type the "Name" (required), and add "Name (English)" if needed.',
            1 => 'Enter "Phone" (required) and "Email" (optional).',
            2 => 'If the supplier is a company, fill in "Company Name", "Tax Number" and "Commercial Registration No.".',
            3 => 'Set the "Credit Limit" granted to this supplier.',
            4 => 'Add "Notes" if needed.',
            5 => 'Fill in the "Address" fields: City, District, Street Name, Building Number, Additional Number, Postal Code.',
            6 => 'Click "Save" to add the supplier.',
          ),
        ),
      ),
    ),
    7 => 
    array (
      'key' => 'hr',
      'label' => 'HR',
      'icon' => 'user',
      'items' => 
      array (
        0 => 
        array (
          'key' => 'new_employee',
          'label' => 'New Employee',
          'steps' => 
          array (
            0 => 'Type the "Name" (required), and add the "Name (English)" if needed.',
            1 => 'Enter the "National ID/Iqama", "Phone" and "Email".',
            2 => 'Type the "Job Title" and "Department" the employee belongs to.',
            3 => 'Choose the "Branch" and set the "Hire Date".',
            4 => 'Enter the "Basic Salary" and "Allowances".',
            5 => 'Choose the "Pay Method" (Cash or Bank Transfer) - for bank transfer, fill in "Bank Name" and "IBAN".',
            6 => 'Add the "National Address" if available, and any extra "Notes".',
            7 => 'Click "Save" to add the employee - an "Employee No." is assigned automatically.',
          ),
        ),
        1 => 
        array (
          'key' => 'new_attendance',
          'label' => 'Record Attendance',
          'steps' => 
          array (
            0 => 'Choose the "Employee" from the list (required).',
            1 => 'Check the "Date" field (defaults to today, and can be changed).',
            2 => 'Enter "Check In" time if you want to record it.',
            3 => 'Enter "Check Out" time if you want to record it.',
            4 => 'Add any "Notes" about that day.',
            5 => 'Click "Save" to record the attendance day.',
          ),
        ),
        2 => 
        array (
          'key' => 'new_loan',
          'label' => 'New Loan/Custody',
          'steps' => 
          array (
            0 => 'From the "Loans & Custodies" screen, choose the "Employee" (required).',
            1 => 'Choose the "Type": Cash Loan or Custody.',
            2 => 'Choose "Pay From Account" - the treasury/bank account the amount will be paid from.',
            3 => 'Enter the "Amount".',
            4 => 'Set the "Monthly Installment" if it will be deducted in instalments, or leave it empty to deduct the full remaining amount in one go on the next payroll run.',
            5 => 'Check the "Date" field (defaults to today).',
            6 => 'Add a "Description" and "Notes" if needed.',
            7 => 'Click "Save" to record the loan/custody and automatically post its accounting entry.',
          ),
        ),
        3 => 
        array (
          'key' => 'new_custody',
          'label' => 'New Asset Custody',
          'steps' => 
          array (
            0 => 'From the "Asset Custody" screen, choose the "Employee" (required).',
            1 => 'Type the "Item Name" (required), e.g. laptop or phone.',
            2 => 'Pick or type a "Category" (Laptop / Vehicle / Phone / Other).',
            3 => 'Enter the "Serial Number" and "Value" if available.',
            4 => 'Choose the "Condition" on issue (New / Good / Used / Damaged).',
            5 => 'Check "Issued Date" (defaults to today) and set an "Expected Return Date" if known.',
            6 => 'Add "Notes" if needed.',
            7 => 'Click "Save" to record the custody item.',
          ),
        ),
        4 => 
        array (
          'key' => 'new_leave',
          'label' => 'New Leave Request',
          'steps' => 
          array (
            0 => 'From the "Leave Requests" screen, choose the "Employee" (required) - you can check their balance in the "Annual Leave Balance" table further down the page.',
            1 => 'Choose the "Leave Type": Annual / Sick / Unpaid / Emergency / Other.',
            2 => 'Set the "From" and "To" dates for the leave (both required).',
            3 => 'Type a "Reason" if needed.',
            4 => 'Click "Submit Request" - the request appears as "Pending" until it is approved or rejected.',
          ),
        ),
        5 => 
        array (
          'key' => 'new_end_of_service',
          'label' => 'End of Service Settlement',
          'steps' => 
          array (
            0 => 'Choose the "Employee" (required).',
            1 => 'Choose the "Termination Reason": Resignation / Termination by Employer / Contract End / Death / Termination for Cause (no gratuity).',
            2 => 'Check the "Termination Date" (defaults to today).',
            3 => 'Choose "Pay From Account" - the treasury/bank account the gratuity will be paid from.',
            4 => 'Enter a "Wage Basis (monthly)" if you want to override it, or leave it empty to automatically use the employee\'s current basic salary plus allowances.',
            5 => 'Add "Notes" if needed.',
            6 => 'Click "Calculate & Post" - the system calculates the gratuity per official Saudi labor law and posts its accounting entry immediately.',
          ),
        ),
        6 => 
        array (
          'key' => 'run_payroll',
          'label' => 'Run Payroll',
          'steps' => 
          array (
            0 => 'Choose the "Month" and optionally a "Branch" or "Employee", then click "Filter" to display that month\'s payroll sheet.',
            1 => 'Review each employee\'s "Basic Salary", "Allowances", "Overtime", and the absence/lateness/unpaid-leave/loan deductions, down to "Net Pay".',
            2 => 'To add a bonus for an employee, type the value under "Monthly Bonus" and click ✓ to save it.',
            3 => 'You can print any employee\'s slip with "Print Slip".',
            4 => 'Once the figures are ready, pick a pay-from account under "Pay From Account" next to "Post Month Payroll", or leave it empty and choose "Defer payment" if salaries will be paid later.',
            5 => 'Click "Post Month Payroll" to post the accounting entry for every employee shown.',
            6 => 'If posted as deferred, a "Payable - not paid yet" badge appears - choose a treasury account and click "Pay Now" once you actually disburse the money.',
            7 => 'If something is wrong before payment, click "Cancel Posting" to fully reverse the entry.',
          ),
        ),
      ),
    ),
    8 => 
    array (
      'key' => 'products',
      'label' => 'Products & Inventory',
      'icon' => 'box',
      'items' => 
      array (
        0 => 
        array (
          'key' => 'new_product',
          'label' => 'Add a New Product',
          'steps' => 
          array (
            0 => 'A new product is added from inside the sales invoice or purchase invoice screen directly, not from a separate page.',
            1 => 'While adding a line item to an invoice, click "New Product" instead of "Choose Product" if the item doesn\'t exist in stock yet.',
            2 => 'Type the product name, code (if you have one), unit, sale price, cost price, and opening quantity.',
            3 => 'Save the product - it\'s added to the invoice line you were on, and becomes available to pick on any future invoice.',
            4 => 'To organize products into groups, from "All Products" click "Add Product Group" and type the group name to save it.',
          ),
        ),
      ),
    ),
    9 => 
    array (
      'key' => 'accounting',
      'label' => 'Accounting & Invoices',
      'icon' => 'ledger',
      'items' => 
      array (
        0 => 
        array (
          'key' => 'new_account',
          'label' => 'New Account',
          'steps' => 
          array (
            0 => 'Open "New Account" and type the "Account Name" (required).',
            1 => 'If this account sits under a bigger account, search for it and pick it in "Parent Account" - leave it empty for a top-level account.',
            2 => 'Enter an "Account Number" if you want a specific one (optional).',
            3 => 'Choose the "Branch" this account belongs to, or leave it as "All Branches".',
            4 => 'Choose the "Account Category" (Assets / Liabilities / Revenue / Expenses / Equity).',
            5 => 'Enter the "Opening Balance" and set its "Opening Balance Side" - Debit or Credit.',
            6 => 'Keep "Active" checked if the account should be usable right away, and check "Parent account" only if it is a classification-only account with no direct transactions.',
            7 => 'Add any "Notes" if needed.',
            8 => 'Click "Save" to add the account to the Chart of Accounts.',
          ),
        ),
        1 => 
        array (
          'key' => 'new_receipt_voucher',
          'label' => 'New Receipt Voucher',
          'steps' => 
          array (
            0 => 'Choose the "Treasury / Bank Account" that will receive the money - suggestions appear right away, or search by name.',
            1 => 'Choose "Received From (customer / account)" - the account the money came from - by searching its name or number.',
            2 => 'Enter the "Amount" received.',
            3 => 'Check the "Date" field (defaults to today, and can be changed).',
            4 => 'Optionally pick a "Branch" and "Cost Center", and add a "Description".',
            5 => 'If the amount is subject to VAT, check "Subject to VAT?" and pick a rate under "Select VAT rate" - the entered amount is treated as VAT-inclusive and the system automatically works out the "Net (after VAT)" and "VAT amount".',
            6 => 'Note that each voucher records only one treasury account and one counterpart account - for money from more than one account, create a separate receipt voucher for each.',
            7 => 'Click "Save Voucher" to save the receipt voucher.',
          ),
        ),
        2 => 
        array (
          'key' => 'new_payment_voucher',
          'label' => 'New Payment Voucher',
          'steps' => 
          array (
            0 => 'Choose the "Treasury / Bank Account" the money will be paid from - suggestions appear right away, or search by name.',
            1 => 'Choose "Paid To (supplier / account)" - the account that will receive the money - by searching its name or number.',
            2 => 'Enter the "Amount" paid.',
            3 => 'Check the "Date" field (defaults to today, and can be changed).',
            4 => 'Optionally pick a "Branch" and "Cost Center", and add a "Description".',
            5 => 'If the amount is subject to VAT, check "Subject to VAT?" and pick a rate under "Select VAT rate" - the entered amount is treated as VAT-inclusive and the system automatically works out the "Net (after VAT)" and "VAT amount".',
            6 => 'Note that each voucher records only one treasury account and one counterpart account - for payments to more than one account, create a separate payment voucher for each.',
            7 => 'Click "Save Voucher" to save the payment voucher.',
          ),
        ),
        3 => 
        array (
          'key' => 'new_daily_entry',
          'label' => 'New Journal Entry',
          'steps' => 
          array (
            0 => 'Check the "Entry Date" (defaults to today).',
            1 => 'Optionally pick a "Branch" and "Cost Center", and type the overall "Description" for the entry.',
            2 => 'In the "Entry Lines" table, search for the first "Account" and select it.',
            3 => 'Enter the amount in either "Debit" or "Credit" for that line (a line can only carry one of the two, not both).',
            4 => 'Click "+ Add Line" to add a line for each other account, repeating the same step.',
            5 => 'Total "Debit" must equal total "Credit" - the indicator below the table shows "Entry is balanced ✓" and Save only unlocks once it does.',
            6 => 'You can remove any line with its "Remove" button (at least two lines must remain).',
            7 => 'Click "Save Entry" once "Entry is balanced ✓" is shown.',
          ),
        ),
        4 => 
        array (
          'key' => 'new_opening_entry',
          'label' => 'New Opening Entry',
          'steps' => 
          array (
            0 => 'This uses the same journal entry screen - set the "Entry Date" (usually the date you start using the system or the start of the fiscal year).',
            1 => 'Optionally pick a "Branch" and "Cost Center", and type a "Description" (e.g. Opening balances).',
            2 => 'In the "Entry Lines" table, search for each account from the Chart of Accounts and select it, one line at a time.',
            3 => 'Enter its opening balance in "Debit" or "Credit" depending on the account\'s nature.',
            4 => 'Click "+ Add Line" to add a line for every account that has an opening balance.',
            5 => 'Make sure total "Debit" equals total "Credit" before saving - opening entries are usually recorded only once, so double-check the balances.',
            6 => 'You can remove any line with its "Remove" button (at least two lines must remain).',
            7 => 'Click "Save Entry" once "Entry is balanced ✓" is shown.',
          ),
        ),
      ),
    ),
    10 => 
    array (
      'key' => 'reports',
      'label' => 'Reports',
      'icon' => 'doc',
      'items' => 
      array (
        0 => 
        array (
          'key' => 'overview',
          'label' => 'How to use report screens',
          'steps' => 
          array (
            0 => 'Report screens aren\'t creation wizards - they\'re filters (branch, period, type) followed by viewing, exporting to Excel, or printing. From "Reports" in the sidebar, pick the section you want (Accounting, Sales, Purchases, Products, HR, Product Delivery), then pick the specific report, adjust the filters at the top of the page (mainly branch and date range), and click "Apply" to view the result, or "Export to Excel"/"Print" to export it.',
          ),
        ),
      ),
    ),
    11 => 
    array (
      'key' => 'settings',
      'label' => 'Settings',
      'icon' => 'gear',
      'items' => 
      array (
        0 => 
        array (
          'key' => 'new_tax',
          'label' => 'Add a New Tax Rate',
          'steps' => 
          array (
            0 => 'From "Tax & Priority Management", type the "Tax Name" (e.g. VAT).',
            1 => 'Enter the "Rate (%)" (e.g. 15).',
            2 => 'Set the "Priority (Order)" - a smaller number means higher priority when more than one tax applies to the same operation.',
            3 => 'Click "Save & Add" - the new tax immediately appears in the tax list available on invoices and vouchers.',
          ),
        ),
      ),
    ),
    12 => 
    array (
      'key' => 'admin',
      'label' => 'Administration',
      'icon' => 'shield',
      'items' => 
      array (
        0 => 
        array (
          'key' => 'new_user',
          'label' => 'New User',
          'steps' => 
          array (
            0 => 'Type the "Name" and "Email" (both required - the email becomes their login).',
            1 => 'Enter a "Password" (required for a new user).',
            2 => 'Choose the "Branch" this user belongs to.',
            3 => 'Choose the "Role" that defines their permissions, or leave "-- No role (no permissions) --" if they should not have any yet.',
            4 => 'Make sure "Active" stays checked so they can log in right away.',
            5 => 'Click "Save" to create the user.',
          ),
        ),
        1 => 
        array (
          'key' => 'new_branch',
          'label' => 'New Branch',
          'steps' => 
          array (
            0 => 'Type the "Branch Name" (required), and "Name (English)" if needed.',
            1 => 'Enter the "Location" of the branch.',
            2 => 'Choose the "Type": Main branch or Sub branch.',
            3 => 'If this is a sub branch, choose its parent under "Parent branch".',
            4 => 'Click "Save" to add the branch.',
          ),
        ),
        2 => 
        array (
          'key' => 'new_role',
          'label' => 'New Role',
          'steps' => 
          array (
            0 => 'Type the "Role Name" (required) and "Name (English)" if needed.',
            1 => 'Permissions are shown as separate cards, each card representing one module in the system.',
            2 => 'Check the permissions you want this role to have inside each card, or click that card\'s "Select all" to check every permission in that module at once.',
            3 => 'You can use "Select all" or "Clear all" at the top of the page to control every permission across all modules at once.',
            4 => 'Click "Save" to create the role with the permissions you selected.',
          ),
        ),
      ),
    ),
  ),
);
