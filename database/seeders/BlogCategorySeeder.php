<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use Illuminate\Database\Seeder;

class BlogCategorySeeder extends Seeder
{
    public function run(): void
    {
        BlogCategory::firstOrCreate(['slug' => 'compliance'], ['name' => 'Compliance']);
        BlogCategory::firstOrCreate(['slug' => 'news'], ['name' => 'News']);
    }
}
