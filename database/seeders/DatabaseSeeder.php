<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            LanguageSeeder::class,
            ConfigSeeder::class,
            LanguageLineSeeder::class,
            PostCategorySeeder::class,
            PostSeeder::class,
            ProductCategorySeeder::class,
            ProductSeeder::class,
            InformationSeeder::class,
            OrderSeeder::class,
        ]);
    }
}
