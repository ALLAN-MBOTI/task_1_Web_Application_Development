<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ============================================================================
 * Database Migration: Create AR Invoice System Tables
 * ============================================================================
 * Purpose: Sets up the relational database schema required for the SAP Business
 * One style A/R Invoice module. Creates tables for master data (Customers,
 * Sales Employees, Items) and transactional document data (Invoice Headers,
 * Invoice Lines).
 *
 * Tables Created:
 *  1. customers        - Master data for business partners / clients.
 *  2. sales_employees  - Master data for sales personnel options.
 *  3. items            - Master data for inventory stock items & prices.
 *  4. invoice_headers  - Parent records storing document summary details.
 *  5. invoice_lines    - Child records storing detailed line item entries.
 * ============================================================================
 */
return new class extends Migration {
    /**
     * Run the migrations to build the database structure.
     *
     * @return void
     */
    public function up(): void {

        /**
         * --------------------------------------------------------------------
         * 1. Customers Table (Business Partner Master Data)
         * --------------------------------------------------------------------
         * Stores customer information used in the header lookup fields.
         * Primary Key: `customer_code` (Alphanumeric string, e.g., 'CC00001')
         */
        Schema::create('customers', function (Blueprint $table) {
            $table->string('customer_code', 20)->primary(); // Unique customer identification code
            $table->string('customer_name', 100);            // Full business/customer name
            $table->string('contact_person', 100)->nullable(); // Primary contact individual
            $table->string('bp_currency', 10)->default('KES'); // Business Partner default currency
            $table->string('kra_pin', 20)->nullable();       // Kenya Revenue Authority Tax Identification Number
            $table->timestamps();                            // Created_at and updated_at tracking
        });

        /**
         * --------------------------------------------------------------------
         * 2. Sales Employees Table
         * --------------------------------------------------------------------
         * Stores sales representative profiles selectable in invoice footers.
         * Primary Key: `emp_id` (Auto-incrementing Integer)
         */
        Schema::create('sales_employees', function (Blueprint $table) {
            $table->id('emp_id');             // Unique identifier for each sales employee
            $table->string('emp_name', 100);  // Full name of the sales representative
            $table->timestamps();             // Created_at and updated_at tracking
        });

        /**
         * --------------------------------------------------------------------
         * 3. Items Table (Inventory Stock Master Data)
         * --------------------------------------------------------------------
         * Stores product inventory items selectable within invoice line rows.
         * Primary Key: `item_no` (Alphanumeric string, e.g., 'FG00011')
         */
        Schema::create('items', function (Blueprint $table) {
            $table->string('item_no', 50)->primary();           // Unique stock keeping unit (SKU) code
            $table->string('item_description', 200);            // Full description of the item
            $table->string('whse', 20)->default('FG WHS');       // Default warehouse code
            $table->integer('qty_in_whse')->default(100);       // Available inventory stock quantity
            $table->string('uom_code', 20)->default('Bales');    // Unit of Measure (e.g., Bales, Cartons)
            $table->decimal('unit_price', 18, 3);               // Base unit selling price (up to 3 decimal places)
            $table->timestamps();                               // Created_at and updated_at tracking
        });

        /**
         * --------------------------------------------------------------------
         * 4. Invoice Headers Table (Transaction Header)
         * --------------------------------------------------------------------
         * Parent record for storing invoice header information, overall totals,
         * remarks, and workflow approval statuses.
         * Primary Key: `doc_num` (Auto-incrementing Document ID)
         */
        Schema::create('invoice_headers', function (Blueprint $table) {
            $table->id('doc_num');                              // Auto-incrementing A/R Invoice document number
            
            // Customer Foreign Key
            $table->string('customer_code', 20);
            $table->foreign('customer_code')->references('customer_code')->on('customers');
            
            $table->date('posting_date');                       // Transaction posting date
            
            // Sales Employee Foreign Key
            $table->unsignedBigInteger('sales_employee_id');
            $table->foreign('sales_employee_id')->references('emp_id')->on('sales_employees');
            
            $table->text('remarks');                            // Mandatory transaction notes/remarks
            $table->decimal('total_before_discount', 18, 3);    // Gross invoice total before line/header discounts
            $table->decimal('discount_percent', 5, 3)->default(0); // Header-level overall discount percentage
            $table->decimal('total_after_discount', 18, 3);     // Net payable total amount
            $table->boolean('requires_approval')->default(false); // Approval flag (Set to true if Net Total > 10,000)
            $table->timestamps();                               // Created_at and updated_at tracking
        });

        /**
         * --------------------------------------------------------------------
         * 5. Invoice Lines Table (Transaction Line Items)
         * --------------------------------------------------------------------
         * Child table containing individual product items, pricing breakdowns,
         * and calculated totals for each invoice row.
         * Primary Key: `line_id` (Auto-incrementing Row ID)
         */
        Schema::create('invoice_lines', function (Blueprint $table) {
            $table->id('line_id');                              // Unique identifier for each line item row
            
            // Invoice Header Parent Foreign Key (Cascades delete when parent header is deleted)
            $table->unsignedBigInteger('doc_num');
            $table->foreign('doc_num')->references('doc_num')->on('invoice_headers')->onDelete('cascade');
            
            $table->integer('line_num');                        // Line sequence position index (1, 2, 3...)
            
            // Item Master Foreign Key
            $table->string('item_no', 50);
            $table->foreign('item_no')->references('item_no')->on('items');
            
            $table->string('item_description', 200);            // Snapshot of item description at invoice creation
            $table->integer('quantity');                        // Quantity of items billed
            $table->decimal('price_before_discount', 18, 3);    // Base price per unit before line discount
            $table->decimal('discount_percent', 5, 3)->default(0); // Line item discount percentage (Max 50%)
            $table->decimal('price_after_discount', 18, 3);     // Discounted unit price (up to 3 decimal places)
            $table->decimal('line_total', 18, 3);               // Calculated total for row: quantity * price_after_discount
            $table->timestamps();                               // Created_at and updated_at tracking
        });
    }

    /**
     * Reverse the migrations (Drop tables in reverse order to respect foreign key constraints).
     *
     * @return void
     */
    public function down(): void {
        // Drop child tables first to avoid foreign key violation errors
        Schema::dropIfExists('invoice_lines');
        Schema::dropIfExists('invoice_headers');
        
        // Drop independent master data tables
        Schema::dropIfExists('items');
        Schema::dropIfExists('sales_employees');
        Schema::dropIfExists('customers');
    }
};