<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate existing product categories
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        ProductCategory::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $categories = [
            [
                'name' => ['vi' => 'Bổ sung Vitamin & Khoáng chất', 'en' => 'Multivitamins & Essential Minerals'],
                'slug' => 'bo-sung-vitamin-khoang-chat',
                'image' => '/images/products/multivitamin_daily.png',
                'description' => ['vi' => 'Cung cấp đầy đủ các vitamin và khoáng chất thiết yếu cho cơ thể khỏe mạnh mỗi ngày.', 'en' => 'Provides essential vitamins and minerals for daily health and vitality.'],
            ],
            [
                'name' => ['vi' => 'Hỗ trợ Miễn dịch & Đề kháng', 'en' => 'Immunity & Resistance Boosters'],
                'slug' => 'ho-tro-mien-dich-de-khang',
                'image' => '/images/products/vitamin_c_shield.png',
                'description' => ['vi' => 'Tăng cường hệ miễn dịch, củng cố hàng rào đề kháng tự nhiên của cơ thể.', 'en' => 'Boosts immune system and strengthens natural health barrier.'],
            ],
            [
                'name' => ['vi' => 'Dinh dưỡng Thể thao & Whey Protein', 'en' => 'Sports Nutrition & Whey Protein'],
                'slug' => 'dinh-duong-the-thao-whey',
                'image' => '/images/products/whey_protein.png',
                'description' => ['vi' => 'Hỗ trợ phát triển cơ bắp, phục hồi nhanh và tăng cường sức bền cho người tập thể thao.', 'en' => 'Supports muscle building, fast recovery, and endurance.'],
            ],
            [
                'name' => ['vi' => 'Làm đẹp, Collagen & Trẻ hóa Da', 'en' => 'Beauty, Collagen & Anti-Aging Care'],
                'slug' => 'lam-dep-collagen-tre-hoa',
                'image' => '/images/products/collagen_peptide.png',
                'description' => ['vi' => 'Chăm sóc làn da tươi trẻ, tăng độ đàn hồi, mờ thâm nám và chống lão hóa.', 'en' => 'Promotes youthful skin, elasticity, and anti-aging protection.'],
            ],
            [
                'name' => ['vi' => 'Thảo dược Detox & Thanh lọc', 'en' => 'Herbal Detox & Body Cleansing'],
                'slug' => 'thao-duoc-detox-thanh-loc',
                'image' => '/images/products/herbal_detox_tea.png',
                'description' => ['vi' => 'Thanh lọc độc tố, hỗ trợ tiêu hóa, mát gan và giữ vóc dáng thon gọn.', 'en' => 'Flushes out toxins, aids digestion, and keeps body in shape.'],
            ],
            [
                'name' => ['vi' => 'Bổ Não & Tăng cường Trí nhớ', 'en' => 'Brain Health & Memory Support'],
                'slug' => 'bo-nao-tang-cuong-tri-nho',
                'image' => '/images/products/omega3_fish_oil.png',
                'description' => ['vi' => 'Tăng cường tuần hoàn máu não, tập trung tinh thần và tăng cường trí nhớ.', 'en' => 'Enhances cerebral circulation, mental focus, and memory capacity.'],
            ],
            [
                'name' => ['vi' => 'Bổ Tim mạch & Huyết áp', 'en' => 'Cardiovascular & Heart Health'],
                'slug' => 'bo-tim-mach-huyet-ap',
                'image' => '/images/products/omega3_fish_oil.png',
                'description' => ['vi' => 'Bảo vệ sức khỏe tim mạch, hỗ trợ cân bằng huyết áp và giảm cholesterol xấu.', 'en' => 'Supports cardiovascular system, blood pressure, and healthy cholesterol.'],
            ],
            [
                'name' => ['vi' => 'Bổ Xương khớp & Chiều cao', 'en' => 'Bone, Joint & Flexibility Care'],
                'slug' => 'bo-xuong-khop-chieu-cao',
                'image' => '/images/products/multivitamin_daily.png',
                'description' => ['vi' => 'Bổ sung Canxi, D3, K2 cho xương chắc khỏe, tái tạo sụn khớp dẻo dai.', 'en' => 'Calcium, D3, K2 formula for strong bones and flexible joints.'],
            ],
            [
                'name' => ['vi' => 'Hỗ trợ Tiêu hóa & Men vi sinh', 'en' => 'Digestive Health & Probiotics'],
                'slug' => 'ho-tro-tieu-hoa-men-vi-sinh',
                'image' => '/images/products/herbal_detox_tea.png',
                'description' => ['vi' => 'Bổ sung lợi khuẩn bào tử probiotic, cân bằng vi sinh đường ruột.', 'en' => 'Probiotic spore formula for intestinal microbial balance.'],
            ],
            [
                'name' => ['vi' => 'Giấc ngủ ngon & Giảm Stress', 'en' => 'Sleep Support & Stress Relief'],
                'slug' => 'giac-ngu-ngon-giam-stress',
                'image' => '/images/products/vitamin_c_shield.png',
                'description' => ['vi' => 'Hỗ trợ giấc ngủ sâu tự nhiên, xoa dịu thần kinh và giảm căng thẳng mệt mỏi.', 'en' => 'Promotes restful sleep, calms nervous system, and reduces stress.'],
            ],
        ];

        foreach ($categories as $data) {
            $cat = new ProductCategory();
            $cat->slug = $data['slug'];
            $cat->image = $data['image'];
            $cat->status = 1;
            $cat->meta_robots = 'index,follow';
            $cat->rel = 'dofollow';
            $cat->target = '_self';

            foreach ($data['name'] as $lang => $val) {
                $cat->setTranslation('name', $lang, $val);
                $cat->setTranslation('meta_title', $lang, $val);
                $cat->setTranslation('meta_keyword', $lang, mb_strtolower($val));
            }
            foreach ($data['description'] as $lang => $val) {
                $cat->setTranslation('description', $lang, $val);
                $cat->setTranslation('meta_description', $lang, $val);
            }
            $cat->save();
        }
    }
}
