<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Item;
use App\Models\SalesEmployee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

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
     * 1. Predictive Type-Ahead Search API Tests
     * ========================================================================= */

    public function test_type_ahead_can_search_customers_by_code_and_name(): void
    {
        $response = $this->getJson('/api/customers/search?q=C100');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'customer_code' => 'C10001',
                'customer_name' => 'Acme Corporation',
            ]);
    }

    public function test_type_ahead_can_search_sales_employees_by_name(): void
    {
        $response = $this->getJson('/api/search-sales-employees?q=John');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'emp_name' => 'John Doe',
            ]);
    }

    /* =========================================================================
     * 2. Invoice Submission & Business Logic Tests
     * ========================================================================= */

    public function test_valid_invoice_can_be_successfully_stored(): void
    {
        $payload = [
            'customer_code'     => $this->customer->customer_code,
            'posting_date'      => now()->format('Y-m-d'),
            'sales_employee_id' => $this->salesEmployee->id,
            'remarks'           => 'Standard delivery order.',
            'lines'             => [
                [
                    'item_no'               => $this->item1->item_no,
                    'item_description'      => $this->item1->item_description,
                    'quantity'              => 2,
                    'price_before_discount' => 25.000,
                    'discount'              => 10.000,
                ],
            ],
        ];

        $response = $this->postJson('/invoice/store', $payload);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('invoices', [
            'customer_code' => 'C10001',
            'remarks'       => 'Standard delivery order.',
        ]);
    }

    /* =========================================================================
     * 3. Validation & Error Handling Tests
     * ========================================================================= */

    public function test_invoice_submission_fails_when_remarks_field_is_empty(): void
    {
        $payload = [
            'customer_code'     => $this->customer->customer_code,
            'posting_date'      => now()->format('Y-m-d'),
            'sales_employee_id' => $this->salesEmployee->id,
            'remarks'           => '',
            'lines'             => [
                [
                    'item_no'               => $this->item1->item_no,
                    'item_description'      => $this->item1->item_description,
                    'quantity'              => 1,
                    'price_before_discount' => 25.000,
                    'discount'              => 0.000,
                ],
            ],
        ];

        $response = $this->postJson('/invoice/store', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['remarks']);
    }

    public function test_invoice_submission_fails_when_line_discount_exceeds_50_percent(): void
    {
        $payload = [
            'customer_code'     => $this->customer->customer_code,
            'posting_date'      => now()->format('Y-m-d'),
            'sales_employee_id' => $this->salesEmployee->id,
            'remarks'           => 'Special discount requested.',
            'lines'             => [
                [
                    'item_no'               => $this->item1->item_no,
                    'item_description'      => $this->item1->item_description,
                    'quantity'              => 1,
                    'price_before_discount' => 25.000,
                    'discount'              => 55.000,
                ],
            ],
        ];

        $response = $this->postJson('/invoice/store', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['lines.0.discount']);
    }

    /* =========================================================================
     * 4. High-Value Invoice Approval Logic Test
     * ========================================================================= */

    public function test_invoice_total_exceeding_10000_triggers_approval_status(): void
    {
        $payload = [
            'customer_code'     => $this->customer->customer_code,
            'posting_date'      => now()->format('Y-m-d'),
            'sales_employee_id' => $this->salesEmployee->id,
            'remarks'           => 'High-value order purchase.',
            'lines'             => [
                [
                    'item_no'               => $this->item2->item_no,
                    'item_description'      => $this->item2->item_description,
                    'quantity'              => 100,
                    'price_before_discount' => 120.000,
                    'discount'              => 0.000,
                ],
            ],
        ];

        $response = $this->postJson('/invoice/store', $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('invoices', [
            'customer_code'     => 'C10001',
            'requires_approval' => true,
        ]);
    }
}