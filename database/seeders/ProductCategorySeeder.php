<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productCategories = [
            [
                'name' => 'Electronics',
            ],
            [
                'name' => 'Fashion',
            ],
            [
                'name' => 'Home & Garden'
            ],
            [
                'name' => 'Sports & Outdoors',
            ],
        ];
        foreach ($productCategories as $category) {
            ProductCategory::create($category);
        }
    }
}
