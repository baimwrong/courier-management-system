<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\BulkImport;
use App\Models\Customer;
use App\Models\Manifest;
use App\Models\ManifestItem;
use App\Models\Service;
use App\Models\Shipment;
use App\Models\ShipmentTracking;
use App\Models\Tariff;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // 1. Branches & Hubs
        $branchesData = [
            [
                'code' => 'CGK-HUB01',
                'name' => 'Jakarta Mega Sorting Hub',
                'type' => 'central_hub',
                'city' => 'Jakarta',
                'province' => 'DKI Jakarta',
                'postal_code' => '13440',
                'address' => 'Jl. D.I. Panjaitan No. 88, Cipinang Cempedak, Jatinegara',
                'phone' => '021-88492011',
                'email' => 'hub.jakarta@courier.test',
                'latitude' => -6.2415840,
                'longitude' => 106.8719200,
                'is_active' => true,
            ],
            [
                'code' => 'BDG-DC01',
                'name' => 'Bandung Regional Delivery Center',
                'type' => 'sorting_hub',
                'city' => 'Bandung',
                'province' => 'Jawa Barat',
                'postal_code' => '40286',
                'address' => 'Jl. Soekarno Hatta No. 450, Batununggal',
                'phone' => '022-7561920',
                'email' => 'hub.bandung@courier.test',
                'latitude' => -6.9452010,
                'longitude' => 107.6382900,
                'is_active' => true,
            ],
            [
                'code' => 'SUB-HUB01',
                'name' => 'Surabaya Gateway Hub',
                'type' => 'central_hub',
                'city' => 'Surabaya',
                'province' => 'Jawa Timur',
                'postal_code' => '60293',
                'address' => 'Jl. Rungkut Industri Raya No. 12, Rungkut',
                'phone' => '031-8439012',
                'email' => 'hub.surabaya@courier.test',
                'latitude' => -7.3241000,
                'longitude' => 112.7532000,
                'is_active' => true,
            ],
            [
                'code' => 'SMG-BR01',
                'name' => 'Semarang Central Branch',
                'type' => 'branch',
                'city' => 'Semarang',
                'province' => 'Jawa Tengah',
                'postal_code' => '50134',
                'address' => 'Jl. Pemuda No. 110, Sekayu',
                'phone' => '024-3519820',
                'email' => 'branch.semarang@courier.test',
                'latitude' => -6.9812000,
                'longitude' => 110.4182000,
                'is_active' => true,
            ],
            [
                'code' => 'YGY-DP01',
                'name' => 'Yogyakarta Malioboro Drop Point',
                'type' => 'drop_point',
                'city' => 'Yogyakarta',
                'province' => 'DI Yogyakarta',
                'postal_code' => '55271',
                'address' => 'Jl. Malioboro No. 56, Sosromenduran',
                'phone' => '0274-512890',
                'email' => 'dp.jogja@courier.test',
                'latitude' => -7.7925000,
                'longitude' => 110.3662000,
                'is_active' => true,
            ],
            [
                'code' => 'DPS-BR01',
                'name' => 'Denpasar Express Branch',
                'type' => 'branch',
                'city' => 'Denpasar',
                'province' => 'Bali',
                'postal_code' => '80113',
                'address' => 'Jl. Teuku Umar No. 88, Dauh Puri Kauh',
                'phone' => '0361-228190',
                'email' => 'branch.bali@courier.test',
                'latitude' => -8.6781000,
                'longitude' => 115.2045000,
                'is_active' => true,
            ],
            [
                'code' => 'MDN-HUB01',
                'name' => 'Medan Gateway Hub',
                'type' => 'sorting_hub',
                'city' => 'Medan',
                'province' => 'Sumatera Utara',
                'postal_code' => '20151',
                'address' => 'Jl. Gatot Subroto No. 200, Sei Sikambing',
                'phone' => '061-4528190',
                'email' => 'hub.medan@courier.test',
                'latitude' => 3.5852000,
                'longitude' => 98.6621000,
                'is_active' => true,
            ],
        ];

        $branches = [];
        foreach ($branchesData as $b) {
            $branches[$b['code']] = Branch::create($b);
        }

        // 2. Users (Roles: Superadmin, Manager, Hub Operator, Courier, Cashier, Customer)
        $defaultPassword = Hash::make('password');

        $admin = User::create([
            'name' => 'Alex Handoko (Super Admin)',
            'email' => 'admin@courier.test',
            'password' => $defaultPassword,
            'role' => 'superadmin',
            'phone' => '081198765432',
            'is_active' => true,
        ]);

        $managerJkt = User::create([
            'name' => 'Rian Pratama (Branch Manager)',
            'email' => 'manager.jkt@courier.test',
            'password' => $defaultPassword,
            'role' => 'branch_manager',
            'branch_id' => $branches['CGK-HUB01']->id,
            'phone' => '081234567890',
            'is_active' => true,
        ]);

        $hubOperator = User::create([
            'name' => 'Dedi Kurniawan (Hub Operator)',
            'email' => 'hub.cgk@courier.test',
            'password' => $defaultPassword,
            'role' => 'hub_operator',
            'branch_id' => $branches['CGK-HUB01']->id,
            'phone' => '081398765432',
            'is_active' => true,
        ]);

        $cashier = User::create([
            'name' => 'Siti Rahma (Counter Cashier)',
            'email' => 'cashier.siti@courier.test',
            'password' => $defaultPassword,
            'role' => 'cashier',
            'branch_id' => $branches['BDG-DC01']->id,
            'phone' => '081512345678',
            'is_active' => true,
        ]);

        $courier1 = User::create([
            'name' => 'Budi Santoso (Delivery Courier)',
            'email' => 'courier.budi@courier.test',
            'password' => $defaultPassword,
            'role' => 'courier',
            'branch_id' => $branches['BDG-DC01']->id,
            'phone' => '081788990011',
            'vehicle_type' => 'motorcycle',
            'vehicle_plate' => 'D 4521 ABC',
            'is_active' => true,
        ]);

        $courier2 = User::create([
            'name' => 'Agus Wijaya (Van Courier)',
            'email' => 'courier.agus@courier.test',
            'password' => $defaultPassword,
            'role' => 'courier',
            'branch_id' => $branches['CGK-HUB01']->id,
            'phone' => '081822334455',
            'vehicle_type' => 'van',
            'vehicle_plate' => 'B 9876 XYZ',
            'is_active' => true,
        ]);

        $customerUser = User::create([
            'name' => 'PT Toko Jaya Abadi (VIP Client)',
            'email' => 'client.tokopedia@courier.test',
            'password' => $defaultPassword,
            'role' => 'customer',
            'phone' => '081299887766',
            'is_active' => true,
        ]);

        // 3. Services (Shipping Products)
        $servicesData = [
            [
                'code' => 'REG',
                'name' => 'Regular (Standard)',
                'description' => 'Fast and reliable standard delivery reaching nationwide destinations.',
                'estimated_days_min' => 2,
                'estimated_days_max' => 3,
                'price_multiplier' => 1.00,
                'volumetric_divisor' => 5000,
                'min_weight_kg' => 1.00,
                'is_active' => true,
                'display_order' => 1,
            ],
            [
                'code' => 'EXP',
                'name' => 'Express (Next Day / YES)',
                'description' => 'Priority overnight delivery with guaranteed next business day arrival.',
                'estimated_days_min' => 1,
                'estimated_days_max' => 1,
                'price_multiplier' => 1.60,
                'volumetric_divisor' => 5000,
                'min_weight_kg' => 1.00,
                'is_active' => true,
                'display_order' => 2,
            ],
            [
                'code' => 'CARGO',
                'name' => 'Cargo (Heavy Trucking)',
                'description' => 'Economical solution for heavy packages starting at minimum 10kg with door-to-door trucking.',
                'estimated_days_min' => 3,
                'estimated_days_max' => 5,
                'price_multiplier' => 0.50,
                'volumetric_divisor' => 4000,
                'min_weight_kg' => 10.00,
                'is_active' => true,
                'display_order' => 3,
            ],
            [
                'code' => 'SAMEDAY',
                'name' => 'SameDay City',
                'description' => 'Instant intra-city delivery dispatched and delivered within 6 to 8 hours.',
                'estimated_days_min' => 0,
                'estimated_days_max' => 1,
                'price_multiplier' => 2.20,
                'volumetric_divisor' => 5000,
                'min_weight_kg' => 1.00,
                'is_active' => true,
                'display_order' => 4,
            ],
            [
                'code' => 'ECO',
                'name' => 'Economy Saver',
                'description' => 'Super budget-friendly shipping option with flexible delivery timeframe.',
                'estimated_days_min' => 4,
                'estimated_days_max' => 7,
                'price_multiplier' => 0.75,
                'volumetric_divisor' => 5000,
                'min_weight_kg' => 1.00,
                'is_active' => true,
                'display_order' => 5,
            ],
        ];

        $services = [];
        foreach ($servicesData as $s) {
            $services[$s['code']] = Service::create($s);
        }

        // 4. Tariffs (Rate Matrix)
        $routes = [
            ['Jakarta', 'DKI Jakarta', 'Bandung', 'Jawa Barat', 12000, 9000, '1-2 Days'],
            ['Jakarta', 'DKI Jakarta', 'Surabaya', 'Jawa Timur', 19000, 14000, '2-3 Days'],
            ['Jakarta', 'DKI Jakarta', 'Semarang', 'Jawa Tengah', 16000, 11000, '2-3 Days'],
            ['Jakarta', 'DKI Jakarta', 'Yogyakarta', 'DI Yogyakarta', 17000, 12000, '2-3 Days'],
            ['Jakarta', 'DKI Jakarta', 'Denpasar', 'Bali', 25000, 18000, '2-4 Days'],
            ['Jakarta', 'DKI Jakarta', 'Medan', 'Sumatera Utara', 32000, 24000, '3-4 Days'],
            ['Bandung', 'Jawa Barat', 'Jakarta', 'DKI Jakarta', 12000, 9000, '1-2 Days'],
            ['Bandung', 'Jawa Barat', 'Surabaya', 'Jawa Timur', 18000, 13000, '2-3 Days'],
            ['Surabaya', 'Jawa Timur', 'Jakarta', 'DKI Jakarta', 19000, 14000, '2-3 Days'],
            ['Surabaya', 'Jawa Timur', 'Denpasar', 'Bali', 16000, 11000, '1-2 Days'],
            ['Semarang', 'Jawa Tengah', 'Jakarta', 'DKI Jakarta', 16000, 11000, '2-3 Days'],
            ['Yogyakarta', 'DI Yogyakarta', 'Jakarta', 'DKI Jakarta', 17000, 12000, '2-3 Days'],
        ];

        foreach ($routes as $r) {
            // Regular Tariff
            Tariff::create([
                'origin_city' => $r[0],
                'origin_province' => $r[1],
                'destination_city' => $r[2],
                'destination_province' => $r[3],
                'service_id' => $services['REG']->id,
                'base_weight_kg' => 1.00,
                'base_rate' => $r[4],
                'per_kg_rate' => $r[5],
                'estimated_etd' => $r[6],
                'is_active' => true,
            ]);

            // Express Tariff
            Tariff::create([
                'origin_city' => $r[0],
                'origin_province' => $r[1],
                'destination_city' => $r[2],
                'destination_province' => $r[3],
                'service_id' => $services['EXP']->id,
                'base_weight_kg' => 1.00,
                'base_rate' => round($r[4] * 1.6),
                'per_kg_rate' => round($r[5] * 1.5),
                'estimated_etd' => '1 Day (Next Day)',
                'is_active' => true,
            ]);

            // Cargo Tariff (minimum 10kg base rate)
            Tariff::create([
                'origin_city' => $r[0],
                'origin_province' => $r[1],
                'destination_city' => $r[2],
                'destination_province' => $r[3],
                'service_id' => $services['CARGO']->id,
                'base_weight_kg' => 10.00,
                'base_rate' => round($r[4] * 3.5),
                'per_kg_rate' => round($r[5] * 0.45),
                'estimated_etd' => '3-5 Days (Trucking)',
                'is_active' => true,
            ]);
        }

        // 5. Customers
        $customer1 = Customer::create([
            'customer_code' => 'CUST-1001',
            'name' => 'PT Toko Jaya Abadi',
            'company_name' => 'Jaya Abadi Group',
            'email' => 'client.tokopedia@courier.test',
            'phone' => '081299887766',
            'address' => 'Mangga Dua Mall Lt. 3 No. 45',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'postal_code' => '10730',
            'account_type' => 'corporate',
            'credit_limit' => 50000000.00,
            'balance' => 12500000.00,
            'is_active' => true,
        ]);

        $customer2 = Customer::create([
            'customer_code' => 'CUST-1002',
            'name' => 'Batik Kencana Heritage',
            'company_name' => 'CV Batik Kencana',
            'email' => 'sales@batikkencana.id',
            'phone' => '081377889900',
            'address' => 'Jl. Malioboro No. 120',
            'city' => 'Yogyakarta',
            'province' => 'DI Yogyakarta',
            'postal_code' => '55271',
            'account_type' => 'regular',
            'credit_limit' => 10000000.00,
            'balance' => 3200000.00,
            'is_active' => true,
        ]);

        // 6. Realistic Shipments
        // Shipment 1: Delivered (Jakarta -> Bandung, Regular)
        $s1 = Shipment::create([
            'tracking_number' => 'CR-202610-884912',
            'booking_date' => Carbon::now()->subDays(2),
            'origin_branch_id' => $branches['CGK-HUB01']->id,
            'destination_branch_id' => $branches['BDG-DC01']->id,
            'service_id' => $services['REG']->id,
            'creator_id' => $managerJkt->id,
            'current_branch_id' => $branches['BDG-DC01']->id,
            'assigned_courier_id' => $courier1->id,
            'customer_id' => $customer1->id,
            'sender_name' => 'PT Toko Jaya Abadi',
            'sender_phone' => '081299887766',
            'sender_address' => 'Mangga Dua Mall Lt. 3 No. 45',
            'sender_city' => 'Jakarta',
            'sender_province' => 'DKI Jakarta',
            'sender_postal_code' => '10730',
            'recipient_name' => 'Andi Wijaya',
            'recipient_phone' => '081912345678',
            'recipient_address' => 'Jl. Dago Asri No. 15, Dago',
            'recipient_city' => 'Bandung',
            'recipient_province' => 'Jawa Barat',
            'recipient_postal_code' => '40135',
            'package_type' => 'electronics',
            'package_description' => 'Wireless Mechanical Keyboard & Mouse',
            'actual_weight_kg' => 1.80,
            'length_cm' => 35.00,
            'width_cm' => 20.00,
            'height_cm' => 10.00,
            'volumetric_weight_kg' => 1.40,
            'chargeable_weight_kg' => 1.80,
            'piece_count' => 1,
            'shipping_cost' => 21000.00,
            'insurance_cost' => 2500.00,
            'declared_goods_value' => 1250000.00,
            'cod_amount' => 0.00,
            'cod_fee' => 0.00,
            'total_cost' => 23500.00,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'current_status' => 'delivered',
            'routing_code' => 'CGK-BDG-01',
            'pod_recipient_name' => 'Andi Wijaya',
            'pod_relationship' => 'Self',
            'pod_delivered_at' => Carbon::now()->subHours(4),
            'pod_notes' => 'Received directly at front porch in good condition.',
        ]);

        $s1->trackings()->createMany([
            [
                'status' => 'booking_created',
                'title' => 'Shipment Booked at Jakarta Counter',
                'location_city' => 'Jakarta',
                'location_details' => 'CGK-HUB01 - Jakarta Central',
                'description' => 'Order created and airway bill generated.',
                'user_id' => $managerJkt->id,
                'branch_id' => $branches['CGK-HUB01']->id,
                'occurred_at' => Carbon::now()->subDays(2),
            ],
            [
                'status' => 'received_at_origin_hub',
                'title' => 'Processed at Jakarta Mega Sorting Hub',
                'location_city' => 'Jakarta',
                'location_details' => 'CGK-HUB01',
                'description' => 'Package scanned and sorted into Bandung transit container.',
                'user_id' => $hubOperator->id,
                'branch_id' => $branches['CGK-HUB01']->id,
                'occurred_at' => Carbon::now()->subDays(2)->addHours(4),
            ],
            [
                'status' => 'manifested_in_transit',
                'title' => 'Dispatched on Line-Haul Truck to Bandung',
                'location_city' => 'Jakarta',
                'location_details' => 'Line-Haul Trunk #TR-88',
                'description' => 'Departed Jakarta Mega Sorting Hub heading to Bandung Regional Delivery Center.',
                'user_id' => $hubOperator->id,
                'manifest_code' => 'MNF-202610-0012',
                'branch_id' => $branches['CGK-HUB01']->id,
                'occurred_at' => Carbon::now()->subDay()->subHours(10),
            ],
            [
                'status' => 'received_at_dest_branch',
                'title' => 'Arrived at Bandung Regional Delivery Center',
                'location_city' => 'Bandung',
                'location_details' => 'BDG-DC01',
                'description' => 'Package unloaded and inbound manifest verified.',
                'user_id' => $cashier->id,
                'branch_id' => $branches['BDG-DC01']->id,
                'occurred_at' => Carbon::now()->subHours(12),
            ],
            [
                'status' => 'out_for_delivery',
                'title' => 'With Delivery Courier [Budi Santoso]',
                'location_city' => 'Bandung',
                'location_details' => 'Bandung Utara - Dago Area',
                'description' => 'Assigned to courier run sheet and out for final delivery.',
                'user_id' => $courier1->id,
                'branch_id' => $branches['BDG-DC01']->id,
                'occurred_at' => Carbon::now()->subHours(6),
            ],
            [
                'status' => 'delivered',
                'title' => 'Delivered to Recipient [Andi Wijaya]',
                'location_city' => 'Bandung',
                'location_details' => 'Jl. Dago Asri No. 15',
                'description' => 'Package delivered successfully. Signed by recipient.',
                'user_id' => $courier1->id,
                'branch_id' => $branches['BDG-DC01']->id,
                'occurred_at' => Carbon::now()->subHours(4),
            ],
        ]);

        // Shipment 2: Out for Delivery (COD Package)
        $s2 = Shipment::create([
            'tracking_number' => 'CR-202610-771234',
            'booking_date' => Carbon::now()->subDay(),
            'origin_branch_id' => $branches['CGK-HUB01']->id,
            'destination_branch_id' => $branches['BDG-DC01']->id,
            'service_id' => $services['EXP']->id,
            'creator_id' => $managerJkt->id,
            'current_branch_id' => $branches['BDG-DC01']->id,
            'assigned_courier_id' => $courier1->id,
            'sender_name' => 'Fashion Glam Jakarta',
            'sender_phone' => '081233445566',
            'sender_address' => 'Grand Indonesia Mall East Mall Lt. 2',
            'sender_city' => 'Jakarta',
            'sender_province' => 'DKI Jakarta',
            'sender_postal_code' => '10310',
            'recipient_name' => 'Clara Novita',
            'recipient_phone' => '087811223344',
            'recipient_address' => 'Jl. R.E. Martadinata No. 89, Citarum',
            'recipient_city' => 'Bandung',
            'recipient_province' => 'Jawa Barat',
            'recipient_postal_code' => '40115',
            'package_type' => 'clothing',
            'package_description' => 'Designer Silk Dress & Cardigan',
            'actual_weight_kg' => 0.90,
            'length_cm' => 25.00,
            'width_cm' => 20.00,
            'height_cm' => 5.00,
            'volumetric_weight_kg' => 0.50,
            'chargeable_weight_kg' => 1.00,
            'piece_count' => 1,
            'shipping_cost' => 19200.00,
            'insurance_cost' => 1500.00,
            'declared_goods_value' => 750000.00,
            'cod_amount' => 750000.00,
            'cod_fee' => 18750.00,
            'total_cost' => 39450.00,
            'payment_method' => 'cod',
            'payment_status' => 'pending_cod',
            'current_status' => 'out_for_delivery',
            'routing_code' => 'CGK-BDG-02',
            'special_instructions' => 'Please call recipient before arriving. Prepare exact cash for COD.',
        ]);

        $s2->trackings()->createMany([
            [
                'status' => 'booking_created',
                'title' => 'Shipment Booked at Jakarta Hub',
                'location_city' => 'Jakarta',
                'location_details' => 'CGK-HUB01',
                'description' => 'Express overnight booking registered.',
                'user_id' => $managerJkt->id,
                'branch_id' => $branches['CGK-HUB01']->id,
                'occurred_at' => Carbon::now()->subDay(),
            ],
            [
                'status' => 'received_at_origin_hub',
                'title' => 'Inbound Sorting at Jakarta Mega Hub',
                'location_city' => 'Jakarta',
                'location_details' => 'CGK-HUB01',
                'description' => 'Fast-track priority sorting completed.',
                'user_id' => $hubOperator->id,
                'branch_id' => $branches['CGK-HUB01']->id,
                'occurred_at' => Carbon::now()->subDay()->addHours(3),
            ],
            [
                'status' => 'manifested_in_transit',
                'title' => 'Departed on Express Night Shuttle',
                'location_city' => 'Jakarta',
                'location_details' => 'Shuttle Van #B-9876-XYZ',
                'description' => 'In transit to Bandung Regional Delivery Center.',
                'user_id' => $hubOperator->id,
                'branch_id' => $branches['CGK-HUB01']->id,
                'occurred_at' => Carbon::now()->subHours(14),
            ],
            [
                'status' => 'received_at_dest_branch',
                'title' => 'Received at Bandung DC',
                'location_city' => 'Bandung',
                'location_details' => 'BDG-DC01',
                'description' => 'Sorted into Riau/Citarum delivery zone.',
                'user_id' => $cashier->id,
                'branch_id' => $branches['BDG-DC01']->id,
                'occurred_at' => Carbon::now()->subHours(7),
            ],
            [
                'status' => 'out_for_delivery',
                'title' => 'Out for Delivery with Courier [Budi Santoso]',
                'location_city' => 'Bandung',
                'location_details' => 'Citarum Zone',
                'description' => 'Courier is currently delivering packages in your area. COD amount Rp 750.000.',
                'user_id' => $courier1->id,
                'branch_id' => $branches['BDG-DC01']->id,
                'occurred_at' => Carbon::now()->subHours(2),
            ],
        ]);

        // Shipment 3: In Transit (Jakarta -> Surabaya, Cargo Trucking)
        $s3 = Shipment::create([
            'tracking_number' => 'CR-202610-339811',
            'booking_date' => Carbon::now()->subHours(18),
            'origin_branch_id' => $branches['CGK-HUB01']->id,
            'destination_branch_id' => $branches['SUB-HUB01']->id,
            'service_id' => $services['CARGO']->id,
            'creator_id' => $hubOperator->id,
            'current_branch_id' => $branches['CGK-HUB01']->id,
            'sender_name' => 'AutoParts Makmur Jaya',
            'sender_phone' => '081122334455',
            'sender_address' => 'Kawasan Industri Pulogadung Blok F No. 20',
            'sender_city' => 'Jakarta',
            'sender_province' => 'DKI Jakarta',
            'sender_postal_code' => '13920',
            'recipient_name' => 'Bengkel Surabaya Motor',
            'recipient_phone' => '081234112233',
            'recipient_address' => 'Jl. Kenjeran No. 340, Tambaksari',
            'recipient_city' => 'Surabaya',
            'recipient_province' => 'Jawa Timur',
            'recipient_postal_code' => '60134',
            'package_type' => 'parcel',
            'package_description' => 'Car Brake Calipers & Heavy Suspension Set',
            'actual_weight_kg' => 28.50,
            'length_cm' => 60.00,
            'width_cm' => 50.00,
            'height_cm' => 40.00,
            'volumetric_weight_kg' => 30.00, // (60*50*40)/4000 = 30kg
            'chargeable_weight_kg' => 30.00,
            'piece_count' => 2,
            'shipping_cost' => 192500.00,
            'insurance_cost' => 5000.00,
            'declared_goods_value' => 2500000.00,
            'cod_amount' => 0.00,
            'total_cost' => 197500.00,
            'payment_method' => 'bank_transfer',
            'payment_status' => 'paid',
            'current_status' => 'manifested_in_transit',
            'routing_code' => 'CGK-SUB-CARGO',
        ]);

        $s3->trackings()->createMany([
            [
                'status' => 'booking_created',
                'title' => 'Cargo Shipment Booked at Pulogadung Counter',
                'location_city' => 'Jakarta',
                'location_details' => 'CGK-HUB01',
                'description' => '2 Pieces heavy cargo booked.',
                'user_id' => $hubOperator->id,
                'branch_id' => $branches['CGK-HUB01']->id,
                'occurred_at' => Carbon::now()->subHours(18),
            ],
            [
                'status' => 'received_at_origin_hub',
                'title' => 'Weighed & Staged at Mega Cargo Hub',
                'location_city' => 'Jakarta',
                'location_details' => 'CGK-HUB01 Cargo Deck',
                'description' => 'Loaded onto pallet container.',
                'user_id' => $hubOperator->id,
                'branch_id' => $branches['CGK-HUB01']->id,
                'occurred_at' => Carbon::now()->subHours(14),
            ],
            [
                'status' => 'manifested_in_transit',
                'title' => 'In Transit: Java Trans-Highway Trunk Line',
                'location_city' => 'Semarang',
                'location_details' => 'Passing Semarang Transit Waypoint',
                'description' => 'Truck en route to Surabaya Gateway Hub.',
                'user_id' => $hubOperator->id,
                'branch_id' => $branches['CGK-HUB01']->id,
                'manifest_code' => 'MNF-202610-0044',
                'occurred_at' => Carbon::now()->subHours(4),
            ],
        ]);

        // Shipment 4: Booking Created (Pending pickup)
        $s4 = Shipment::create([
            'tracking_number' => 'CR-202610-990011',
            'booking_date' => Carbon::now()->subMinutes(45),
            'origin_branch_id' => $branches['BDG-DC01']->id,
            'destination_branch_id' => $branches['DPS-BR01']->id,
            'service_id' => $services['REG']->id,
            'creator_id' => $cashier->id,
            'current_branch_id' => $branches['BDG-DC01']->id,
            'sender_name' => 'Batik Kencana Heritage',
            'sender_phone' => '081377889900',
            'sender_address' => 'Jl. Riau No. 34',
            'sender_city' => 'Bandung',
            'sender_province' => 'Jawa Barat',
            'sender_postal_code' => '40115',
            'recipient_name' => 'Made Sukerta',
            'recipient_phone' => '081338877665',
            'recipient_address' => 'Jl. Sunset Road No. 88, Seminyak',
            'recipient_city' => 'Denpasar',
            'recipient_province' => 'Bali',
            'recipient_postal_code' => '80361',
            'package_type' => 'parcel',
            'package_description' => 'Traditional Handwoven Batik Fabrics',
            'actual_weight_kg' => 2.50,
            'length_cm' => 30.00,
            'width_cm' => 25.00,
            'height_cm' => 15.00,
            'volumetric_weight_kg' => 2.25,
            'chargeable_weight_kg' => 2.50,
            'piece_count' => 1,
            'shipping_cost' => 32500.00,
            'insurance_cost' => 2000.00,
            'declared_goods_value' => 1000000.00,
            'cod_amount' => 0.00,
            'total_cost' => 34500.00,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'current_status' => 'booking_created',
            'routing_code' => 'BDG-DPS-01',
        ]);

        $s4->trackings()->create([
            'status' => 'booking_created',
            'title' => 'Shipment Booking Created',
            'location_city' => 'Bandung',
            'location_details' => 'BDG-DC01',
            'description' => 'Airway bill generated at Bandung counter. Awaiting sorting.',
            'user_id' => $cashier->id,
            'branch_id' => $branches['BDG-DC01']->id,
            'occurred_at' => Carbon::now()->subMinutes(45),
        ]);

        // 7. Manifest Example
        $manifest1 = Manifest::create([
            'manifest_number' => 'MNF-202610-0012',
            'type' => 'inter_hub_transit',
            'origin_branch_id' => $branches['CGK-HUB01']->id,
            'destination_branch_id' => $branches['BDG-DC01']->id,
            'courier_id' => $courier2->id,
            'created_by' => $hubOperator->id,
            'vehicle_plate' => 'B 9876 XYZ',
            'vehicle_type' => 'van',
            'status' => 'completed',
            'total_shipments' => 2,
            'total_weight_kg' => 2.70,
            'dispatched_at' => Carbon::now()->subDay()->subHours(10),
            'received_at' => Carbon::now()->subHours(12),
            'notes' => 'Evening line haul container from Jakarta to Bandung.',
        ]);

        $manifest1->items()->createMany([
            [
                'shipment_id' => $s1->id,
                'status' => 'received',
                'scanned_at' => Carbon::now()->subHours(12),
                'notes' => 'Received intact at destination DC.',
            ],
            [
                'shipment_id' => $s2->id,
                'status' => 'received',
                'scanned_at' => Carbon::now()->subHours(12),
                'notes' => 'Received intact at destination DC.',
            ],
        ]);

        // 8. Bulk Import Record Example
        BulkImport::create([
            'user_id' => $admin->id,
            'import_type' => 'shipments',
            'original_filename' => 'initial_ecommerce_shipments_batch_01.csv',
            'file_path' => 'imports/sample_batch.csv',
            'total_rows' => 50,
            'successful_rows' => 50,
            'failed_rows' => 0,
            'status' => 'completed',
            'errors_log' => null,
            'created_at' => Carbon::now()->subDays(3),
        ]);
    }
}
