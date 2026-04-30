<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServicesSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['name' => 'Shave',              'price' => 300,  'sort_order' => 1],
            ['name' => 'Haircut',            'price' => 500,  'sort_order' => 2],
            ['name' => 'Shave + Haircut',    'price' => 700,  'sort_order' => 3],
            ['name' => 'Beard Trim',         'price' => 200,  'sort_order' => 4],
            ['name' => 'Kids Haircut',       'price' => 350,  'sort_order' => 5],
        ];

        foreach ($services as $data) {
            Service::updateOrCreate(['name' => $data['name']], array_merge($data, ['is_active' => true]));
        }
    }
}
