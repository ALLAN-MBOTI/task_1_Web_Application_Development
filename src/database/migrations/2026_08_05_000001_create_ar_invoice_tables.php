<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ============================================================================
 * Database Migration: Create AR Invoice System Tables
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
         * 1. Customers Table (Business Partner Master Data)
         */
        Schema::create('customers', function (Blueprint $table) {
            $table->string('customer_code', 20)->primary();
            $table->string('customer_name', 100);
            $table->string('contact_person', 100)->nullable();
            $table->string('bp_currency', 10)->default('KES');
            $table->string('kra_pin', 20)->nullable();
            $table->timestamps();
        });

        /**
         * 2. Sales Employees Table
         */
        Schema::create('sales_employees', function (Blueprint $table) {
            $table->id('emp_id');             // Explicit primary key: emp_id
            $table->string('emp_name', 100);  // Full name of sales representative
            $table->timestamps();
        });

        /**
         * 3. Items Table (Inventory Stock Master Data)
         */
        Schema::create('items', function (Blueprint $table) {
            $table->string('item_no', 50)->primary();
            $table->string('item_description', 200);
            $table->string('whse', 20)->default('FG WHS');
            $table->integer('qty_in_whse')->default(100);
            $table->string('uom_code', 20)->default('Bales');
            $table->decimal('unit_price', 18, 3);
            $table->timestamps();
        });

        /**
         * 4. Invoice Headers Table
         */
        Schema::create('invoice_headers', function (Blueprint $table) {
            $table->id('doc_num');
            
            // Customer Foreign Key
            $table->string('customer_code', 20);
            $table->foreign('customer_code')->references('customer_code')->on('customers');
            
            $table->date('posting_date');
            
            // Sales Employee Foreign Key referencing emp_id on sales_employees
            $table->unsignedBigInteger('sales_employee_id');
            $table->foreign('sales_employee_id')->references('emp_id')->on('sales_employees');
            
            $table->text('remarks');
            $table->decimal('total_before_discount', 18, 3);
            $table->decimal('discount_percent', 5, 3)->default(0);
            $table->decimal('total_after_discount', 18, 3);
            $table->boolean('requires_approval')->default(false);
            $table->timestamps();
        });

        /**
         * 5. Invoice Lines Table
         */
        Schema::create('invoice_lines', function (Blueprint $table) {
            $table->id('line_id');
            
            // Invoice Header Parent Foreign Key
            $table->unsignedBigInteger('doc_num');
            $table->foreign('doc_num')->references('doc_num')->on('invoice_headers')->onDelete('cascade');
            
            $table->integer('line_num');
            
            // Item Foreign Key
            $table->string('item_no', 50);
            $table->foreign('item_no')->references('item_no')->on('items');
            
            $table->string('item_description', 200);
            $table->integer('quantity');
            $table->decimal('price_before_discount', 18, 3);
            $table->decimal('discount_percent', 5, 3)->default(0);
            $table->decimal('price_after_discount', 18, 3);
            $table->decimal('line_total', 18, 3);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        Schema::dropIfExists('invoice_lines');
        Schema::dropIfExists('invoice_headers');
        Schema::dropIfExists('items');
        Schema::dropIfExists('sales_employees');
        Schema::dropIfExists('customers');
    }
};