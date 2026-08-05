<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * ============================================================================
 * DatabaseSeeder
 * ============================================================================
 * Purpose: Populates the database with initial seed data required to run and
 * test the SAP Business One A/R Invoice module.
 * 
 * Tables Seeded:
 *  1. users           - Default system user accounts with email authentication.
 *  2. customers       - Business Partner (BP) master records.
 *  3. sales_employees - Sales representative dropdown records.
 *  4. items           - Inventory item master records with unit pricing.
 * ============================================================================
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /**
         * --------------------------------------------------------------------
         * 1. Seed System Users
         * --------------------------------------------------------------------
         * Uses the default Laravel `email` column for user authentication.
         */
        DB::table('users')->insertOrIgnore([
            [
                'name' => 'Farouk Mohamed',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('password123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'System Operator',
                'email' => 'operator@gmail.com',
                'password' => Hash::make('password123'),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        /**
         * --------------------------------------------------------------------
         * 2. Seed Customers (Business Partners)
         * --------------------------------------------------------------------
         * Pre-populates Business Partner records used for invoice lookups.
         */
        DB::table('customers')->insertOrIgnore([
            [
                'customer_code' => 'CC00001',
                'customer_name' => 'Walk In Customer - HQ',
                'contact_person' => 'TEST TEST',
                'bp_currency' => 'KES',
                'kra_pin' => 'P051234567Z',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'customer_code' => 'CC00002',
                'customer_name' => 'Bidco Africa Ltd',
                'contact_person' => 'John Doe',
                'bp_currency' => 'KES',
                'kra_pin' => 'P059876543A',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'customer_code' => 'CC00003',
                'customer_name' => 'Pwani Life Ltd',
                'contact_person' => 'Jane Smith',
                'bp_currency' => 'KES',
                'kra_pin' => 'P051122334B',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        /**
         * --------------------------------------------------------------------
         * 3. Seed Sales Employees
         * --------------------------------------------------------------------
         * Pre-populates sales representatives selectable in the invoice footer.
         */
        DB::table('sales_employees')->insertOrIgnore([
            [
                'emp_id' => 1,
                'emp_name' => 'Farouk Abdulrehman Mohamed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'emp_id' => 2,
                'emp_name' => 'Jane Wanjiku',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'emp_id' => 3,
                'emp_name' => 'David Omondi',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        /**
         * --------------------------------------------------------------------
         * 4. Seed Inventory Items
         * --------------------------------------------------------------------
         * Pre-populates stock master data available for line item entry.
         */
        DB::table('items')->insertOrIgnore([
            [
                'item_no' => 'FG00011',
                'item_description' => 'Umi All Purpose Home Baking Flour 2Kg',
                'whse' => 'FG WHS',
                'qty_in_whse' => 648,
                'uom_code' => 'Bales',
                'unit_price' => 1850.000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'item_no' => 'FG00012',
                'item_description' => 'Axe Brand Cooking Oil 3L',
                'whse' => 'FG WHS',
                'qty_in_whse' => 320,
                'uom_code' => 'Cartons',
                'unit_price' => 1200.000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'item_no' => 'FG00013',
                'item_description' => 'Premium Fortified Sugar 1Kg',
                'whse' => 'FG WHS',
                'qty_in_whse' => 500,
                'uom_code' => 'Bags',
                'unit_price' => 210.000,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}