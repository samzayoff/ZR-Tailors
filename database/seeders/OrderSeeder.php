<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Clear existing orders & customers (preserves reference data)
        DB::table('order_design_options')->truncate();
        DB::table('order_measurements')->truncate();
        DB::table('order_garments')->truncate();
        DB::table('orders')->truncate();
        DB::table('customers')->truncate();

        // ── 10 Customers ──────────────────────────────────────────────────
        $customers = [
            ['name' => 'Ahmed Raza',      'phone' => '0300-1234567', 'reference' => 'Walk-in',       'address' => 'Gulshan-e-Iqbal, Karachi',   'notes' => null],
            ['name' => 'Muhammad Bilal',  'phone' => '0301-2345678', 'reference' => 'Ali Khan',       'address' => 'Johar Town, Lahore',          'notes' => 'Regular customer'],
            ['name' => 'Faisal Mahmood',  'phone' => '0302-3456789', 'reference' => 'Walk-in',       'address' => 'G-9, Islamabad',              'notes' => null],
            ['name' => 'Usman Tariq',     'phone' => '0303-4567890', 'reference' => 'Online',         'address' => 'Saddar, Rawalpindi',          'notes' => 'Needs early delivery'],
            ['name' => 'Zubair Hassan',   'phone' => '0304-5678901', 'reference' => 'Ahmed Raza',     'address' => 'DHA Phase 5, Lahore',         'notes' => null],
            ['name' => 'Khalid Mehmood',  'phone' => '0305-6789012', 'reference' => 'Walk-in',       'address' => 'Model Town, Lahore',          'notes' => 'Prefers loose fit'],
            ['name' => 'Imran Javed',     'phone' => '0306-7890123', 'reference' => 'Facebook Ad',   'address' => 'Clifton, Karachi',            'notes' => null],
            ['name' => 'Tariq Nawaz',     'phone' => '0307-8901234', 'reference' => 'Walk-in',       'address' => 'F-7, Islamabad',              'notes' => 'VIP customer'],
            ['name' => 'Shahzad Ali',     'phone' => '0308-9012345', 'reference' => 'Muhammad Bilal', 'address' => 'Bahria Town, Rawalpindi',     'notes' => null],
            ['name' => 'Naeem Akhtar',    'phone' => '0309-0123456', 'reference' => 'Walk-in',       'address' => 'North Nazimabad, Karachi',    'notes' => 'Discount requested'],
        ];

        DB::table('customers')->insert(array_map(function ($c) {
            return array_merge($c, [
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }, $customers));

        // ── 10 Orders ─────────────────────────────────────────────────────
        $now    = Carbon::now();
        $orders = [
            [
                'order_no'     => '1001',
                'customer_id'  => 1,
                'booking_date' => $now->copy()->subDays(30)->toDateString(),
                'delivery_date'=> $now->copy()->subDays(20)->toDateString(),
                'quantity'     => 2,
                'price'        => 3500.00,
                'advance_paid' => 2000.00,
                'colour_note'  => 'White with grey embroidery',
                'extra_notes'  => 'Double stitching on sleeves',
                'status'       => 'delivered',
            ],
            [
                'order_no'     => '1002',
                'customer_id'  => 2,
                'booking_date' => $now->copy()->subDays(25)->toDateString(),
                'delivery_date'=> $now->copy()->subDays(10)->toDateString(),
                'quantity'     => 1,
                'price'        => 1800.00,
                'advance_paid' => 1000.00,
                'colour_note'  => 'Navy blue',
                'extra_notes'  => null,
                'status'       => 'delivered',
            ],
            [
                'order_no'     => '1003',
                'customer_id'  => 3,
                'booking_date' => $now->copy()->subDays(20)->toDateString(),
                'delivery_date'=> $now->copy()->subDays(5)->toDateString(),
                'quantity'     => 1,
                'price'        => 2200.00,
                'advance_paid' => 1500.00,
                'colour_note'  => 'Light grey',
                'extra_notes'  => 'Collar: khal been',
                'status'       => 'ready',
            ],
            [
                'order_no'     => '1004',
                'customer_id'  => 4,
                'booking_date' => $now->copy()->subDays(15)->toDateString(),
                'delivery_date'=> $now->copy()->addDays(2)->toDateString(),
                'quantity'     => 3,
                'price'        => 5400.00,
                'advance_paid' => 3000.00,
                'colour_note'  => 'Cream / off-white',
                'extra_notes'  => 'Wedding order – urgent',
                'status'       => 'stitching',
            ],
            [
                'order_no'     => '1005',
                'customer_id'  => 5,
                'booking_date' => $now->copy()->subDays(12)->toDateString(),
                'delivery_date'=> $now->copy()->addDays(5)->toDateString(),
                'quantity'     => 1,
                'price'        => 1600.00,
                'advance_paid' => 800.00,
                'colour_note'  => 'Dark green',
                'extra_notes'  => null,
                'status'       => 'stitching',
            ],
            [
                'order_no'     => '1006',
                'customer_id'  => 6,
                'booking_date' => $now->copy()->subDays(10)->toDateString(),
                'delivery_date'=> $now->copy()->addDays(7)->toDateString(),
                'quantity'     => 2,
                'price'        => 4000.00,
                'advance_paid' => 2000.00,
                'colour_note'  => 'Black with silver border',
                'extra_notes'  => 'Loose fit – extra 2 inches on chest',
                'status'       => 'pending',
            ],
            [
                'order_no'     => '1007',
                'customer_id'  => 7,
                'booking_date' => $now->copy()->subDays(8)->toDateString(),
                'delivery_date'=> $now->copy()->addDays(10)->toDateString(),
                'quantity'     => 1,
                'price'        => 2500.00,
                'advance_paid' => 1000.00,
                'colour_note'  => 'Royal blue',
                'extra_notes'  => 'Waistcoat included',
                'status'       => 'pending',
            ],
            [
                'order_no'     => '1008',
                'customer_id'  => 8,
                'booking_date' => $now->copy()->subDays(5)->toDateString(),
                'delivery_date'=> $now->copy()->addDays(12)->toDateString(),
                'quantity'     => 4,
                'price'        => 8000.00,
                'advance_paid' => 5000.00,
                'colour_note'  => 'Maroon / deep red',
                'extra_notes'  => 'VIP – priority handling',
                'status'       => 'pending',
            ],
            [
                'order_no'     => '1009',
                'customer_id'  => 9,
                'booking_date' => $now->copy()->subDays(3)->toDateString(),
                'delivery_date'=> $now->copy()->addDays(14)->toDateString(),
                'quantity'     => 1,
                'price'        => 1400.00,
                'advance_paid' => 0.00,
                'colour_note'  => 'Sky blue',
                'extra_notes'  => null,
                'status'       => 'pending',
            ],
            [
                'order_no'     => '1010',
                'customer_id'  => 10,
                'booking_date' => $now->copy()->subDay()->toDateString(),
                'delivery_date'=> $now->copy()->addDays(20)->toDateString(),
                'quantity'     => 2,
                'price'        => 3200.00,
                'advance_paid' => 1500.00,
                'colour_note'  => 'Olive green',
                'extra_notes'  => 'Customer wants discount on balance',
                'status'       => 'pending',
            ],
        ];

        foreach ($orders as $order) {
            DB::table('orders')->insert(array_merge($order, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->command->info('✅  10 customers and 10 orders seeded successfully.');
    }
}
