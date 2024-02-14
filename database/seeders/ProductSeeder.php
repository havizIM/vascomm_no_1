<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Helpers\GenerateCode;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productSeeder = array([
            'name'       => ucwords('Perfume 01'),
            'brand'      => 'The Body Shop',
            'categories' => ['Fragrance', 'Soft'],
            'price'      => 200000,
            'image'      => 'https://picsum.photos/200'
        ], [
            'name'       => ucwords('Perfume 02'),
            'brand'      => 'The Face Shop',
            'categories' => ['Fragrance', 'Soft'],
            'price'      => 150000,
            'image'      => 'https://picsum.photos/200'
        ], [
            'name'       => ucwords('Perfume 03'),
            'brand'      => 'Axe',
            'categories' => ['Fragrance', 'Soft'],
            'price'      => 30000,
            'image'      => 'https://picsum.photos/200'
        ]);

        foreach ($productSeeder as $key) {
            Product::create([
                ...$key,
                'code' => GenerateCode::productCode()
            ]);
        }
    }
}
