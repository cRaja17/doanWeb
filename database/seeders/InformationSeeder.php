<?php

namespace Database\Seeders;

use App\Models\Information;
use Illuminate\Database\Seeder;

class InformationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $infoPages = [
            [
                'slug' => 'gioi-thieu',
                'name' => ['vi' => 'Giới thiệu công ty', 'en' => 'About Us'],
                'description' => ['vi' => '<h2>Chào mừng bạn đến với WebĐaNgônNgữ</h2><p>Chúng tôi là đơn vị tiên phong cung cấp giải pháp công nghệ hiện đại và sản phẩm chính hãng hàng đầu.</p>', 'en' => '<h2>Welcome to WebMultilingual</h2><p>We are a pioneer in delivering cutting-edge tech solutions and top genuine products.</p>'],
                'view_header' => 1,
                'view_footer' => 1,
            ],
            [
                'slug' => 'chinh-sach-bao-hanh',
                'name' => ['vi' => 'Chính sách bảo hành', 'en' => 'Warranty Policy'],
                'description' => ['vi' => '<h2>Quy định bảo hành sản phẩm</h2><p>Tất cả sản phẩm bán ra đều được bảo hành chính hãng từ 12 đến 24 tháng theo quy định của nhà sản xuất.</p>', 'en' => '<h2>Warranty Terms</h2><p>All products carry official manufacturer warranty from 12 to 24 months.</p>'],
                'view_header' => 0,
                'view_footer' => 1,
            ],
            [
                'slug' => 'dieu-khoan-dich-vu',
                'name' => ['vi' => 'Điều khoản dịch vụ', 'en' => 'Terms of Service'],
                'description' => ['vi' => '<h2>Điều khoản & Điều kiện sử dụng</h2><p>Bằng việc truy cập trang web này, bạn đồng ý tuân thủ các quy định dịch vụ của chúng tôi.</p>', 'en' => '<h2>Terms & Conditions</h2><p>By accessing this site, you agree to comply with our service regulations.</p>'],
                'view_header' => 0,
                'view_footer' => 1,
            ],
            [
                'slug' => 'chinh-sach-bao-mat',
                'name' => ['vi' => 'Chính sách bảo mật', 'en' => 'Privacy Policy'],
                'description' => ['vi' => '<h2>Bảo vệ thông tin cá nhân</h2><p>Chúng tôi cam kết bảo mật tuyệt đối mọi thông tin cá nhân của quý khách hàng.</p>', 'en' => '<h2>Data Privacy Protection</h2><p>We are committed to absolute protection of all customer personal information.</p>'],
                'view_header' => 0,
                'view_footer' => 1,
            ],
            [
                'slug' => 'lien-he',
                'name' => ['vi' => 'Liên hệ & Hỗ trợ', 'en' => 'Contact & Support'],
                'description' => ['vi' => '<h2>Thông tin liên hệ</h2><p>Hotline: 1900 8888 | Email: support@webdangonngu.com | Địa chỉ: Hà Nội, Việt Nam.</p>', 'en' => '<h2>Contact Information</h2><p>Hotline: 1900 8888 | Email: support@webdangonngu.com | Address: Hanoi, Vietnam.</p>'],
                'view_header' => 1,
                'view_footer' => 1,
            ],
        ];

        foreach ($infoPages as $data) {
            $info = new Information();
            $info->slug = $data['slug'];
            $info->status = 1;
            $info->view_header = $data['view_header'];
            $info->view_footer = $data['view_footer'];
            $info->meta_robots = 'index,follow';
            $info->rel = 'dofollow';
            $info->target = '_self';

            foreach ($data['name'] as $lang => $val) {
                $info->setTranslation('name', $lang, $val);
                $info->setTranslation('meta_title', $lang, $val);
                $info->setTranslation('meta_keyword', $lang, mb_strtolower($val));
            }
            foreach ($data['description'] as $lang => $val) {
                $info->setTranslation('description', $lang, $val);
                $info->setTranslation('meta_description', $lang, strip_tags($val));
            }
            $info->save();
        }
    }
}
