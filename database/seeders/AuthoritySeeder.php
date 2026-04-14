<?php

namespace Database\Seeders;

use App\Models\Authority;
use Illuminate\Database\Seeder;

class AuthoritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $authorities = [
            ['name' => 'Municipality', 'description' => 'Municipal services and infrastructure'],
            ['name' => 'Health Department', 'description' => 'Healthcare and public health services'],
            ['name' => 'Education Authority', 'description' => 'Educational institutions and services'],
            ['name' => 'Public Works', 'description' => 'Construction and maintenance services'],
            ['name' => 'Police Department', 'description' => 'Public safety and law enforcement'],
        ];

        foreach ($authorities as $authority) {
            Authority::updateOrCreate(
                ['name' => $authority['name']],
                $authority
            );
        }
    }
}
