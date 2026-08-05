<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ============================================================================
 * InvoiceHeader Model
 * ============================================================================
 * Purpose: Represents the main header record of an A/R Invoice document.
 * Maps to the `invoice_headers` database table in the ERP system.
 *
 * Core Responsibilities:
 *  - Defines custom primary key configuration (`doc_num`).
 *  - Controls mass-assignment permissions for header-level fields.
 *  - Houses the One-to-Many relationship pointing to detail line items.
 * ============================================================================
 */
class InvoiceHeader extends Model
{
    use HasFactory;

    /**
     * Primary Key Definition
     * Overrides Eloquent's default 'id' column name to match the ERP document number.
     * 
     * @var string
     */
    protected $primaryKey = 'doc_num';

    /**
     * Mass Assignment Protection
     * Specifies the white-listed attributes allowed during mass creation or updates.
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'doc_num', // Added to allow manual assignment or mass assignment retrieval
        'customer_code',
        'posting_date',
        'sales_employee_id', 
        'remarks',
        'total_before_discount',
        'discount_percent', 
        'total_after_discount',
        'requires_approval'
    ];

    /**
     * Relationship: Invoice Header Has Many Lines
     * Links this invoice header to its associated detail line items (`InvoiceLine`).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function lines()
    {
        return $this->hasMany(InvoiceLine::class, 'doc_num', 'doc_num');
    }

    /**
     * Relationship: Invoice Header Belongs To Customer
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_code', 'customer_code');
    }

    /**
     * Relationship: Invoice Header Belongs To Sales Employee
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function salesEmployee()
    {
        return $this->belongsTo(SalesEmployee::class, 'sales_employee_id', 'emp_id');
    }
}