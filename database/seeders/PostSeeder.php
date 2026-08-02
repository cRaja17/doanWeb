<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        $userId = $user ? $user->id : 1;
        $categories = PostCategory::all();

        $postsData = [
            [
                'name' => ['vi' => 'Top 10 smartphone đáng mua nhất năm 2026', 'en' => 'Top 10 smartphones worth buying in 2026'],
                'slug' => 'top-10-smartphone-dang-mua-nhat-nam-2026',
                'image' => 'https://picsum.photos/seed/phone1/800/500',
                'summary' => ['vi' => 'Tổng hợp danh sách các điện thoại thông minh sở hữu thiết kế đẹp, hiệu năng khủng và camera sắc nét.', 'en' => 'Comprehensive list of smartphones with stunning designs, powerful performance, and sharp cameras.'],
                'description' => ['vi' => '<p>Thị trường di động năm 2026 chứng kiến sự bùng nổ của trí tuệ nhân tạo tích hợp trực tiếp trên chip xử lý...</p>', 'en' => '<p>The mobile market in 2026 sees an explosion of AI integrated directly on processing chips...</p>'],
            ],
            [
                'name' => ['vi' => 'Hướng dẫn tối ưu hóa hiệu năng Laptop chơi game', 'en' => 'How to optimize your Gaming Laptop performance'],
                'slug' => 'huong-dan-toi-uu-hoa-hieu-nang-laptop-choi-game',
                'image' => 'https://picsum.photos/seed/laptop1/800/500',
                'summary' => ['vi' => 'Bí quyết giúp máy tính xách tay của bạn chạy mượt mà, giảm nhiệt độ và tăng số khung hình FPS khi chơi game.', 'en' => 'Tips to help your laptop run smoothly, lower temperatures, and boost FPS during gaming.'],
                'description' => ['vi' => '<p>Chơi game trên laptop đòi hỏi sự cân bằng giữa hiệu năng và nhiệt độ. Hãy kiểm tra trình điều khiển card đồ họa...</p>', 'en' => '<p>Gaming on laptops requires balancing performance and thermal output. Check graphics card drivers...</p>'],
            ],
            [
                'name' => ['vi' => 'Đánh giá tai nghe chống ồn không dây thế hệ mới', 'en' => 'Next-gen Wireless Noise-Canceling Earphones Review'],
                'slug' => 'danh-gia-tai-nghe-chong-on-khong-day-the-he-moi',
                'image' => 'https://picsum.photos/seed/audio1/800/500',
                'summary' => ['vi' => 'Trải nghiệm âm thanh đỉnh cao cùng khả năng triệt tiêu tiếng ồn chủ động thông minh.', 'en' => 'Experience premium sound quality paired with intelligent active noise cancellation.'],
                'description' => ['vi' => '<p>Tai nghe không dây mới mang lại sự tự do hoàn toàn và thời lượng pin vượt trội lên tới 40 giờ sử dụng...</p>', 'en' => '<p>The new wireless earphones provide full freedom and an outstanding battery life of up to 40 hours...</p>'],
            ],
            [
                'name' => ['vi' => 'Xu hướng trí tuệ nhân tạo AI bùng nổ toàn cầu', 'en' => 'The global boom of Artificial Intelligence trends'],
                'slug' => 'xu-huong-tri-tue-nhan-tao-ai-bung-no-toan-cau',
                'image' => 'https://picsum.photos/seed/ai1/800/500',
                'summary' => ['vi' => 'Trí tuệ nhân tạo đang thay đổi cách chúng ta làm việc, học tập và sáng tạo nội dung hàng ngày.', 'en' => 'AI is reshaping how we work, learn, and create content daily.'],
                'description' => ['vi' => '<p>Các mô hình ngôn ngữ lớn và công cụ tạo hình ảnh tự động đã tạo nên bước ngoặt lớn trong ngành công nghệ...</p>', 'en' => '<p>Large language models and generative image tools have established a massive milestone in tech...</p>'],
            ],
            [
                'name' => ['vi' => 'Mẹo bảo mật tài khoản cá nhân trên môi trường Internet', 'en' => 'Tips for securing your personal accounts online'],
                'slug' => 'meo-bao-mat-tai-khoan-ca-nhan-tren-internet',
                'image' => 'https://picsum.photos/seed/sec1/800/500',
                'summary' => ['vi' => 'Những thói quen quan trọng giúp bảo vệ dữ liệu cá nhân tránh bị kẻ xấu xâm nhập.', 'en' => 'Crucial habits to protect your personal data from unauthorized access.'],
                'description' => ['vi' => '<p>Sử dụng xác thực 2 lớp (2FA) và trình quản lý mật khẩu là bước đầu tiên để giữ an toàn trực tuyến...</p>', 'en' => '<p>Using two-factor authentication (2FA) and password managers is the first step to staying safe online...</p>'],
            ],
            [
                'name' => ['vi' => 'Sự kiện công nghệ nổi bật quý 3 năm 2026', 'en' => 'Highlight tech events of Q3 2026'],
                'slug' => 'su-kien-cong-nghe-noi-bat-quy-3-nam-2026',
                'image' => 'https://picsum.photos/seed/event2/800/500',
                'summary' => ['vi' => 'Điểm lại những triển lãm và hội nghị công nghệ quy mô lớn vừa diễn ra thành công.', 'en' => 'Recap of major tech exhibitions and conferences recently held with great success.'],
                'description' => ['vi' => '<p>Triển lãm năm nay chào đón hơn 1000 doanh nghiệp công nghệ hàng đầu thế giới tham gia trình diễn giải pháp...</p>', 'en' => '<p>This year’s exhibition welcomed over 1,000 leading tech companies showcasing innovative solutions...</p>'],
            ],
            [
                'name' => ['vi' => 'Bí quyết chọn mua bàn phím cơ phù hợp cho dân văn phòng', 'en' => 'Guide to choosing the right mechanical keyboard for office work'],
                'slug' => 'bi-quyet-chon-mua-ban-phim-co-phu-hop',
                'image' => 'https://picsum.photos/seed/key1/800/500',
                'summary' => ['vi' => 'Gợi ý các loại switch êm ái, gõ êm tay không gây ồn cho đồng nghiệp xung quanh.', 'en' => 'Recommendations for silent switches that offer great feel without disturbing colleagues.'],
                'description' => ['vi' => '<p>Bàn phím cơ không chỉ dành cho gamer mà còn hỗ trợ người làm việc văn phòng gõ nhanh và giảm mỏi tay...</p>', 'en' => '<p>Mechanical keyboards are not just for gamers; they help office workers type faster and reduce strain...</p>'],
            ],
            [
                'name' => ['vi' => 'Đánh giá đồng hồ thông minh hỗ trợ theo dõi sức khỏe', 'en' => 'Smartwatch review: Comprehensive health tracking'],
                'slug' => 'danh-gia-dong-ho-thong-minh-theo-doi-suc-khoe',
                'image' => 'https://picsum.photos/seed/watch1/800/500',
                'summary' => ['vi' => 'Đo nhịp tim, nồng độ oxy trong máu SpO2 và phân tích chất lượng giấc ngủ chính xác.', 'en' => 'Measure heart rate, SpO2 levels, and accurately analyze sleep quality.'],
                'description' => ['vi' => '<p>Chiếc smartwatch thế hệ mới nhất được trang bị cảm biến sinh học thế hệ thứ 4 cho kết quả chuẩn xác...</p>', 'en' => '<p>The latest smartwatch generation features a 4th-gen biosensor for highly accurate results...</p>'],
            ],
        ];

        foreach ($postsData as $index => $data) {
            $post = new Post();
            $post->user_id = $userId;
            $post->image = $data['image'];
            $post->slug = $data['slug'];
            $post->tag = 'tech,review,gadgets';
            $post->meta_robots = 'index,follow';
            $post->rel = 'dofollow';
            $post->target = '_self';
            $post->view = rand(50, 2000);
            $post->status = 1;

            foreach ($data['name'] as $lang => $val) {
                $post->setTranslation('name', $lang, $val);
                $post->setTranslation('meta_title', $lang, $val);
                $post->setTranslation('meta_keyword', $lang, mb_strtolower($val));
            }
            foreach ($data['summary'] as $lang => $val) {
                $post->setTranslation('summary', $lang, $val);
            }
            foreach ($data['description'] as $lang => $val) {
                $post->setTranslation('description', $lang, $val);
                $post->setTranslation('meta_description', $lang, strip_tags($val));
            }
            $post->save();

            // Attach to random category
            if ($categories->count() > 0) {
                $cat = $categories->get($index % $categories->count());
                $post->categories()->attach($cat->id);
            }
        }
    }
}
