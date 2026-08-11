<?php

namespace Tests\Browser;

use App\Models\Customer;
use App\Models\Item;
use App\Models\SalesEmployee;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class InvoiceFormTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected Customer $customer;
    protected Item $item1;
    protected Item $item2;
    protected SalesEmployee $salesEmployee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = Customer::create([
            'customer_code' => 'C10001',
            'customer_name' => 'Acme Corporation',
        ]);

        $this->item1 = Item::create([
            'item_no'          => 'I10001',
            'item_description' => 'Wireless Optical Mouse',
            'unit_price'       => 25.000,
        ]);

        $this->item2 = Item::create([
            'item_no'          => 'I10002',
            'item_description' => 'Mechanical Keyboard',
            'unit_price'       => 120.000,
        ]);

        $this->salesEmployee = SalesEmployee::create([
            'emp_name' => 'John Doe',
        ]);
    }

    /* =========================================================================
     * 1. Customer Live Type-Ahead & Auto-Suggestion Test
     * ========================================================================= */

    /** @test */
    public function test_customer_type_ahead_autocompletes_code_and_name()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/invoice/create')
                    ->waitFor('#customer_code')
                    ->type('#customer_code', 'C100')
                    ->waitFor('.typeahead-dropdown')
                    ->assertSeeIn('.typeahead-dropdown', 'Acme Corporation')
                    ->click('.typeahead-dropdown .dropdown-item:first-child')
                    ->assertInputValue('#customer_code', 'C10001')
                    ->assertInputValue('#customer_name', 'Acme Corporation');
        });
    }

    /* =========================================================================
     * 2. Dynamic Line Item Insertion & Real-Time Calculations Test
     * ========================================================================= */

    /** @test */
    public function test_dynamic_row_insertion_and_realtime_line_calculations()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/invoice/create')
                    ->waitFor('#invoice-lines-table')
                    
                    // Line 1 calculations
                    ->type('.line-item-no:nth-child(1)', 'I10001')
                    ->waitFor('.item-dropdown')
                    ->click('.item-dropdown .dropdown-item:first-child')
                    ->assertInputValue('.line-description:nth-child(1)', 'Wireless Optical Mouse')
                    ->clear('.line-quantity:nth-child(1)')
                    ->type('.line-quantity:nth-child(1)', '4')
                    ->clear('.line-discount:nth-child(1)')
                    ->type('.line-discount:nth-child(1)', '10')

                    ->assertInputValue('.line-total:nth-child(1)', '90.000')

                    // Dynamic row addition & Line 2 calculation
                    ->click('#add-line-btn')
                    ->waitFor('.invoice-line-row:nth-child(2)')
                    ->type('.line-item-no:nth-child(2)', 'I10002')
                    ->waitFor('.item-dropdown')
                    ->click('.item-dropdown .dropdown-item:first-child')
                    ->clear('.line-quantity:nth-child(2)')
                    ->type('.line-quantity:nth-child(2)', '1')

                    // Aggregate totals update in real-time
                    ->assertSeeIn('#total-before-discount', '220.000')
                    ->assertSeeIn('#total-after-discount', '210.000');
        });
    }

    /* =========================================================================
     * 3. Approval Banner Trigger Test
     * ========================================================================= */

    /** @test */
    public function test_approval_label_becomes_visible_when_total_exceeds_10000()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/invoice/create')
                    ->waitFor('#invoice-lines-table')
                    ->assertMissing('#approval-label')

                    ->type('.line-item-no:nth-child(1)', 'I10002')
                    ->waitFor('.item-dropdown')
                    ->click('.item-dropdown .dropdown-item:first-child')
                    ->clear('.line-quantity:nth-child(1)')
                    ->type('.line-quantity:nth-child(1)', '100')

                    ->waitFor('#approval-label')
                    ->assertVisible('#approval-label')
                    ->assertSeeIn('#approval-label', 'Invoice will go for approval – Amount: 12,000.000');
        });
    }

    /* =========================================================================
     * 4. Client-Side Validation Error Notifications Test
     * ========================================================================= */

    /** @test */
    public function test_frontend_validation_triggers_error_for_excessive_discount()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/invoice/create')
                    ->waitFor('#invoice-lines-table')
                    ->clear('.line-discount:nth-child(1)')
                    ->type('.line-discount:nth-child(1)', '55')
                    ->click('#submit-invoice-btn')

                    ->waitFor('.validation-error-alert')
                    ->assertSeeIn('.validation-error-alert', 'Discount cannot exceed 50%')
                    ->assertSeeIn('.validation-error-alert', 'Remarks field is required');
        });
    }
}