<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ============================================================================
 * InvoiceLine Model
 * ============================================================================
 * Purpose: Represents individual line items of an A/R Invoice document.
 * Maps to the `invoice_lines` database table in the ERP system.
 * ============================================================================
 */
class InvoiceLine extends Model
{
    use HasFactory;

    /**
     * Disable default auto-incrementing 'id' assumption if using a composite or custom setup.
     * Leave enabled if your table has a standard primary key `id`.
     */

    /**
     * Mass Assignment Protection
     * White-listed attributes allowed during creation.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'doc_num',
        'line_num',
        'item_no',
        'item_description',
        'quantity',
        'price_before_discount',
        'discount_percent',
        'price_after_discount',
        'line_total',
    ];

    /**
     * Relationship: Invoice Line belongs to Invoice Header
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function header()
    {
        return $this->belongsTo(InvoiceHeader::class, 'doc_num', 'doc_num');
    }

    /**
     * Relationship: Invoice Line belongs to Item
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_no', 'item_no');
    }
}