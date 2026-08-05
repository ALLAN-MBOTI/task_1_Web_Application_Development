<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ============================================================================
 * Item Model
 * ============================================================================
 * Represents master item catalog data (Item No, Description, Unit Price)
 * used in SAP Business One A/R Invoice lines.
 * ============================================================================
 */
class Item extends Model
{
    use HasFactory;

    /**
     * Primary key definition (matching SAP item_no conventions).
     *
     * @var string
     */
    protected $primaryKey = 'item_no';

    /**
     * The primary key type.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Mass assignable attributes.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'item_no',
        'item_description',
        'price_before_discount',
    ];
}