<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Language::updateOrCreate(
            ['abbr' => 'vi'],
            [
                'name' => json_encode(['vi' => 'Tiếng Việt', 'en' => 'Vietnamese']),
                'flag' => 'vi.svg',
                'abbr' => 'vi',
                'script' => 'Latn',
                'native' => 'Tiếng Việt',
                'unit' => 'VND',
                'active' => 1,
                'default' => 1,
            ]
        );

        Language::updateOrCreate(
            ['abbr' => 'en'],
            [
                'name' => json_encode(['vi' => 'Tiếng Anh', 'en' => 'English']),
                'flag' => 'en.svg',
                'abbr' => 'en',
                'script' => 'Latn',
                'native' => 'English',
                'unit' => 'USD',
                'active' => 1,
                'default' => 0,
            ]
        );
    }
}
