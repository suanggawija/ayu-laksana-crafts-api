<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $products = [
            [
                'name' => 'Product 1',
                'description' => 'Description for product 1',
                'price' => 100000.00,
                'stock' => 10,
                'category_id' => 1,
                'status' => 'active',
            ],
            [
                'name' => 'Product 2',
                'description' => 'Description for product 2',
                'price' => 200000.00,
                'stock' => 20,
                'category_id' => 2,
                'status' => 'active',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
