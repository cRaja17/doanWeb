<?php

namespace Database\Seeders;

use App\Models\PostCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PostCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => ['vi' => 'Tin tức công nghệ', 'en' => 'Tech News'],
                'slug' => 'tin-tuc-cong-nghe',
                'description' => ['vi' => 'Cập nhật tin tức công nghệ mới nhất trong và ngoài nước.', 'en' => 'Latest technology news worldwide.'],
                'image' => 'https://picsum.photos/seed/tech/800/600',
                'view_home' => 1,
                'status' => 1,
            ],
            [
                'name' => ['vi' => 'Mẹo & Hướng dẫn', 'en' => 'Tips & Tutorials'],
                'slug' => 'meo-va-huong-dan',
                'description' => ['vi' => 'Các bài viết hướng dẫn sử dụng, mẹo vặt hữu ích.', 'en' => 'Useful guides and how-to tips.'],
                'image' => 'https://picsum.photos/seed/tips/800/600',
                'view_home' => 1,
                'status' => 1,
            ],
            [
                'name' => ['vi' => 'Đánh giá sản phẩm', 'en' => 'Product Reviews'],
                'slug' => 'danh-gia-san-pham',
                'description' => ['vi' => 'Đánh giá chi tiết các sản phẩm công nghệ hot nhất.', 'en' => 'Detailed reviews of hot tech gadgets.'],
                'image' => 'https://picsum.photos/seed/review/800/600',
                'view_home' => 1,
                'status' => 1,
            ],
            [
                'name' => ['vi' => 'Đời sống & Xu hướng', 'en' => 'Lifestyle & Trends'],
                'slug' => 'doi-song-xu-huong',
                'description' => ['vi' => 'Xu hướng công nghệ và đời sống số hiện đại.', 'en' => 'Modern digital lifestyle and trends.'],
                'image' => 'https://picsum.photos/seed/life/800/600',
                'view_home' => 1,
                'status' => 1,
            ],
            [
                'name' => ['vi' => 'Khuyến mãi & Sự kiện', 'en' => 'Promotions & Events'],
                'slug' => 'khuyen-mai-su-kien',
                'description' => ['vi' => 'Thông tin khuyến mãi hấp dẫn và sự kiện nổi bật.', 'en' => 'Exciting deals and event announcements.'],
                'image' => 'https://picsum.photos/seed/event/800/600',
                'view_home' => 1,
                'status' => 1,
            ],
        ];

        foreach ($categories as $data) {
            $cat = new PostCategory();
            $cat->slug = $data['slug'];
            $cat->image = $data['image'];
            $cat->status = $data['status'];
            $cat->view_home = $data['view_home'];
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
