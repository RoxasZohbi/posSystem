<?php

namespace Database\Seeders;

use App\Models\Deal;
use App\Models\Expense;
use App\Models\Service;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::where('email', 'owner@salon.com')->first();
        $ownerId = $owner?->id ?? 1;

        // Services
        $services = [
            ['name' => 'Haircut (Men)',        'description' => 'Classic men haircut and styling',   'price' => 500,  'is_active' => true],
            ['name' => 'Haircut (Women)',       'description' => 'Women haircut with blow-dry',        'price' => 1200, 'is_active' => true],
            ['name' => 'Hair Color',            'description' => 'Full head color treatment',          'price' => 3500, 'is_active' => true],
            ['name' => 'Highlights',            'description' => 'Partial or full highlights',         'price' => 4500, 'is_active' => true],
            ['name' => 'Facial',                'description' => 'Deep cleansing facial',              'price' => 1500, 'is_active' => true],
            ['name' => 'Manicure',              'description' => 'Nail shaping and polish',            'price' => 800,  'is_active' => true],
            ['name' => 'Pedicure',              'description' => 'Foot care and nail polish',          'price' => 1000, 'is_active' => true],
            ['name' => 'Threading (Eyebrows)',  'description' => 'Eyebrow threading and shaping',      'price' => 150,  'is_active' => true],
            ['name' => 'Waxing (Full Arms)',    'description' => 'Full arm waxing service',            'price' => 700,  'is_active' => true],
            ['name' => 'Head Massage',          'description' => 'Relaxing head and scalp massage',    'price' => 600,  'is_active' => true],
        ];

        foreach ($services as $s) {
            Service::firstOrCreate(['name' => $s['name']], array_merge($s, ['created_by' => $ownerId]));
        }

        // Deals
        $deals = [
            ['name' => 'Bridal Package',       'price' => 15000, 'is_active' => true],
            ['name' => 'Summer Glow Combo',    'price' => 3500,  'is_active' => true],
            ['name' => 'Men\'s Grooming Deal', 'price' => 1200,  'is_active' => true],
            ['name' => 'Full Pampering Day',   'price' => 8000,  'is_active' => true],
            ['name' => 'Nail & Brow Combo',    'price' => 900,   'is_active' => true],
        ];

        foreach ($deals as $d) {
            Deal::firstOrCreate(['name' => $d['name']], array_merge($d, ['created_by' => $ownerId]));
        }

        // Staff
        $staffMembers = [
            ['name' => 'Ayesha Khan',   'email' => 'ayesha@salon.com',  'phone' => '03001234567', 'nic' => '3520112345678', 'commission_rate' => 10.00],
            ['name' => 'Sara Ahmed',    'email' => 'sara@salon.com',    'phone' => '03011234567', 'nic' => '3520112345679', 'commission_rate' => 12.00],
            ['name' => 'Zara Malik',    'email' => 'zara@salon.com',    'phone' => '03021234567', 'nic' => '3520112345680', 'commission_rate' => 8.00],
            ['name' => 'Nadia Hussain', 'email' => 'nadia@salon.com',   'phone' => '03031234567', 'nic' => '3520112345681', 'commission_rate' => 15.00],
            ['name' => 'Hina Baig',     'email' => 'hina@salon.com',    'phone' => '03041234567', 'nic' => '3520112345682', 'commission_rate' => 10.00],
        ];

        foreach ($staffMembers as $st) {
            Staff::firstOrCreate(['email' => $st['email']], array_merge($st, ['created_by' => $ownerId]));
        }

        // Expenses
        $expenses = [
            ['date' => now()->toDateString(),                    'amount' => 5000,  'note' => 'Electricity bill'],
            ['date' => now()->toDateString(),                    'amount' => 2000,  'note' => 'Salon supplies (shampoo, conditioner)'],
            ['date' => now()->subDay()->toDateString(),          'amount' => 1500,  'note' => 'Cleaning supplies'],
            ['date' => now()->subDays(2)->toDateString(),        'amount' => 3000,  'note' => 'Hair color products'],
            ['date' => now()->subDays(3)->toDateString(),        'amount' => 800,   'note' => 'Disposable gloves and masks'],
        ];

        foreach ($expenses as $e) {
            Expense::create(array_merge($e, ['logged_by' => $ownerId]));
        }

        $this->command->info('Demo data seeded: 10 services, 5 deals, 5 staff, 5 expenses.');
    }
}
