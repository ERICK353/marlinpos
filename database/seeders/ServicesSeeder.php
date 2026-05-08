<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServicesSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['name' => 'Hair Cut (H/CUT)', 'price' => 350, 'sort_order' => 1, 'is_haircut' => true],
            ['name' => 'Beard Cut (B/CUT)', 'price' => 200, 'sort_order' => 2, 'is_haircut' => false],
            ['name' => 'Teens', 'price' => 200, 'sort_order' => 3, 'is_haircut' => true],
            ['name' => 'Back to School', 'price' => 150, 'sort_order' => 4, 'is_haircut' => true],
            ['name' => 'Steaming', 'price' => 700, 'sort_order' => 5],
            ['name' => 'Scrub (500)', 'price' => 500, 'sort_order' => 6],
            ['name' => 'Scrub (800)', 'price' => 800, 'sort_order' => 7],
            ['name' => 'Scrub (1000)', 'price' => 1000, 'sort_order' => 8],
            ['name' => 'Masking', 'price' => 300, 'sort_order' => 9],
            ['name' => 'Manicure', 'price' => 800, 'sort_order' => 10],
            ['name' => 'Pedicure', 'price' => 1000, 'sort_order' => 11],
            ['name' => 'Full Facial (1500)', 'price' => 1500, 'sort_order' => 12],
            ['name' => 'Full Facial (2000)', 'price' => 2000, 'sort_order' => 13],
            ['name' => 'Texturizer', 'price' => 400, 'sort_order' => 14],
            ['name' => 'Body Scrub', 'price' => 2000, 'sort_order' => 15],
            ['name' => 'Deep Tissue Massage', 'price' => 3000, 'sort_order' => 16],
            ['name' => 'Swedish Massage', 'price' => 2500, 'sort_order' => 17],
            ['name' => 'Back Massage', 'price' => 1500, 'sort_order' => 18],
            ['name' => 'Black Shampoo Dye', 'price' => 500, 'sort_order' => 19],
            ['name' => 'Bigen Dye', 'price' => 700, 'sort_order' => 20],
            ['name' => 'Radiant Dye', 'price' => 800, 'sort_order' => 21],
            ['name' => 'Crème of Nature Dye', 'price' => 1000, 'sort_order' => 22],
            ['name' => 'Bigen Speedy Dye', 'price' => 1000, 'sort_order' => 23],
        ];

        foreach ($services as $data) {
            Service::updateOrCreate(['name' => $data['name']], array_merge($data, ['is_active' => true]));
        }
    }
}
