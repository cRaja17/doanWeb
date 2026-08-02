<?php

namespace Database\Seeders;

use App\Models\Config;
use Illuminate\Database\Seeder;

class ConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configs = [
            'slides' => '[]',
            'images' => '[]',
            'email_payment' => 'admin@gmail.com',
            'content_email_admin' => '<p>Đơn hàng mới từ [name] - Mã đơn: [id]</p>',
            'status_payment' => '1',
        ];

        foreach ($configs as $name => $value) {
            Config::updateOrCreate(
                ['name' => $name],
                ['value' => $value]
            );
        }
    }
}
