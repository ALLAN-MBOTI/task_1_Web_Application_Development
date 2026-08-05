# Task_1_Web_Application_Development
A task designed to evaluate technical proficiency, attention to detail, and ability to develop applications integrated with databases and APIs using Laravel &amp; Filament.

INSTRUCTIONS
-------------------------------------------------------------------------------------------------------------------------------------------
Task 1: Web Application Development
Objective:
Develop an application with a secure login interface and a main form similar to the attached sample screenshot (Sample Screen.jpg).
Requirements:
a) General Design
    • Maintain UI consistency, including fonts, colors, and layout, as shown in the provided screenshot.
    • Use an SQL Server database to store:
        ◦ Customer List
        ◦ Item list
        ◦ Sales employee list
        ◦ Invoices

b) Header Section
    • Customer Code:
        ◦ Include a “Choose From List” button that displays customer records from the database.
        ◦ Enable real-time search and auto-suggestion within a text field (type-ahead feature).
    • Customer Name:
        ◦ Similar functionality as Customer Code, except the first column in the list should be the customer name.
    • No.:
        ◦ Auto-incremented sequential number for each new record.
    • Posting Date
        ◦ Default to current date, with option to modify.
    • Approval Label:
        ◦ A hidden Label that becomes visible only when the Total Amount exceeds 10,000.
        ◦ When visible, it should display the message:
“Invoice will go for approval – Amount: {getAmount}”

c) Footer Section
    • Sales Employee: Choose From List with searchable functionality.
    • Remarks: Mandatory free-text field.
    • Total Before Discount
    • Discount
    • Total After Discount

d) Table Section
Each row should represent an invoice line item with the following columns:
    • Item No: Choose from list or type manually
    • Item Description: Auto-populate or choose from list
    • Quantity: Numeric only
    • Price Before Discount: Up to 3 decimal places
    • Discount: Up to 3 decimal places
    • Price After Discount: Up to 3 decimal places
    • Total: Up to 3 decimal places

e) Validations and Error Handling
Display an error message under the following conditions:
    • Discount > 50
    • Remarks field is empty
