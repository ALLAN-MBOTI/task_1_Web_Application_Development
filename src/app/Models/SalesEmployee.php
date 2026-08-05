<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ============================================================================
 * SalesEmployee Model
 * ============================================================================
 * Represents sales representative master data used for SAP A/R Invoices.
 * ============================================================================
 */
class SalesEmployee extends Model
{
    use HasFactory;

    /**
     * Primary key definition (matching SAP emp_id conventions).
     */
    protected $primaryKey = 'emp_id';

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'sales_employee_name',
    ];
}