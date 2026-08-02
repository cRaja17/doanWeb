<?php

namespace Database\Seeders;

use App\Models\LanguageLine;
use Illuminate\Database\Seeder;

class LanguageLineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lines = [
            // Breadcrumb
            ['group' => 'breadcrumb', 'key' => 'home', 'text' => ['vi' => 'Trang chủ', 'en' => 'Home']],

            // Header
            ['group' => 'header', 'key' => 'home', 'text' => ['vi' => 'Trang chủ', 'en' => 'Home']],
            ['group' => 'header', 'key' => 'products', 'text' => ['vi' => 'Sản phẩm', 'en' => 'Products']],
            ['group' => 'header', 'key' => 'blogs', 'text' => ['vi' => 'Bài viết', 'en' => 'Blogs']],
            ['group' => 'header', 'key' => 'cart', 'text' => ['vi' => 'Giỏ hàng', 'en' => 'Cart']],
            ['group' => 'header', 'key' => 'checkout', 'text' => ['vi' => 'Thanh toán', 'en' => 'Checkout']],

            // Home
            ['group' => 'home', 'key' => 'meta_title', 'text' => ['vi' => 'Trang chủ - Cửa hàng Đa Ngôn Ngữ', 'en' => 'Home - Multilingual Store']],
            ['group' => 'home', 'key' => 'meta_keyword', 'text' => ['vi' => 'trang chủ, điện thoại, laptop', 'en' => 'home, phones, laptops']],
            ['group' => 'home', 'key' => 'meta_description', 'text' => ['vi' => 'Cửa hàng chuyên kinh doanh sản phẩm công nghệ chính hãng', 'en' => 'Store specializing in genuine tech products']],
            ['group' => 'home', 'key' => 'description_slide', 'text' => ['vi' => 'Chào mừng bạn đến với cửa hàng công nghệ hàng đầu', 'en' => 'Welcome to our leading tech store']],
            ['group' => 'home', 'key' => 'heading_post', 'text' => ['vi' => 'Bài viết mới nhất', 'en' => 'Latest Articles']],
            ['group' => 'home', 'key' => 'heading_image', 'text' => ['vi' => 'Thư viện hình ảnh', 'en' => 'Image Gallery']],

            // Product Category
            ['group' => 'product_category', 'key' => 'categories', 'text' => ['vi' => 'Danh mục sản phẩm', 'en' => 'Product Categories']],
            ['group' => 'product_category', 'key' => 'unit_price', 'text' => ['vi' => 'Giá: ', 'en' => 'Price: ']],
            ['group' => 'product_category', 'key' => 'max_price', 'text' => ['vi' => '100000000', 'en' => '5000']],

            // Product
            ['group' => 'product', 'key' => 'empty', 'text' => ['vi' => 'Không có sản phẩm nào', 'en' => 'No products found']],
            ['group' => 'product', 'key' => 'information', 'text' => ['vi' => 'Thông tin sản phẩm', 'en' => 'Product Information']],
            ['group' => 'product', 'key' => 'see', 'text' => ['vi' => 'Xem chi tiết', 'en' => 'See Details']],
            ['group' => 'product', 'key' => 'quantity', 'text' => ['vi' => 'Số lượng', 'en' => 'Quantity']],
            ['group' => 'product', 'key' => 'price', 'text' => ['vi' => 'Giá bán', 'en' => 'Price']],
            ['group' => 'product', 'key' => 'add_to_cart', 'text' => ['vi' => 'Thêm vào giỏ hàng', 'en' => 'Add to Cart']],
            ['group' => 'product', 'key' => 'cart_added_successfully', 'text' => ['vi' => 'Đã thêm vào giỏ hàng', 'en' => 'Added to Cart']],
            ['group' => 'product', 'key' => 'affiliate', 'text' => ['vi' => 'Mua ngay', 'en' => 'Buy Now']],
            ['group' => 'product', 'key' => 'product_description', 'text' => ['vi' => 'Mô tả chi tiết', 'en' => 'Detailed Description']],
            ['group' => 'product', 'key' => 'related_products', 'text' => ['vi' => 'Sản phẩm liên quan', 'en' => 'Related Products']],

            // Post Category
            ['group' => 'post_category', 'key' => 'list_of_articles', 'text' => ['vi' => 'Danh sách bài viết', 'en' => 'List of Articles']],
            ['group' => 'post_category', 'key' => 'featured_article', 'text' => ['vi' => 'Bài viết nổi bật', 'en' => 'Featured Articles']],

            // Post
            ['group' => 'post', 'key' => 'index', 'text' => ['vi' => 'Tin tức & Bài viết', 'en' => 'News & Articles']],
            ['group' => 'post', 'key' => 'featured_article', 'text' => ['vi' => 'Bài viết nổi bật', 'en' => 'Featured Articles']],
            ['group' => 'post', 'key' => 'related_articles', 'text' => ['vi' => 'Bài viết liên quan', 'en' => 'Related Articles']],

            // Cart
            ['group' => 'cart', 'key' => 'name', 'text' => ['vi' => 'Giỏ hàng', 'en' => 'Shopping Cart']],
            ['group' => 'cart', 'key' => 'view', 'text' => ['vi' => 'Xem giỏ hàng', 'en' => 'View Cart']],
            ['group' => 'cart', 'key' => 'add_to_cart', 'text' => ['vi' => 'Thêm vào giỏ hàng', 'en' => 'Add to Cart']],
            ['group' => 'cart', 'key' => 'product', 'text' => ['vi' => 'Sản phẩm', 'en' => 'Product']],
            ['group' => 'cart', 'key' => 'information', 'text' => ['vi' => 'Thông tin', 'en' => 'Information']],
            ['group' => 'cart', 'key' => 'quantity', 'text' => ['vi' => 'Số lượng', 'en' => 'Quantity']],
            ['group' => 'cart', 'key' => 'price', 'text' => ['vi' => 'Đơn giá', 'en' => 'Price']],
            ['group' => 'cart', 'key' => 'code', 'text' => ['vi' => 'Mã SP', 'en' => 'Code']],
            ['group' => 'cart', 'key' => 'total_price', 'text' => ['vi' => 'Tổng tiền', 'en' => 'Total Price']],
            ['group' => 'cart', 'key' => 'checkout', 'text' => ['vi' => 'Tiến hành thanh toán', 'en' => 'Proceed to Checkout']],
            ['group' => 'cart', 'key' => 'empty', 'text' => ['vi' => 'Giỏ hàng của bạn đang trống', 'en' => 'Your shopping cart is empty']],

            // Checkout
            ['group' => 'checkout', 'key' => 'order', 'text' => ['vi' => 'Đơn hàng của bạn', 'en' => 'Your Order']],
            ['group' => 'checkout', 'key' => 'information', 'text' => ['vi' => 'Thông tin giao hàng', 'en' => 'Shipping Information']],
            ['group' => 'checkout', 'key' => 'first_name', 'text' => ['vi' => 'Họ và tên lót', 'en' => 'First Name']],
            ['group' => 'checkout', 'key' => 'last_name', 'text' => ['vi' => 'Tên', 'en' => 'Last Name']],
            ['group' => 'checkout', 'key' => 'name', 'text' => ['vi' => 'Họ và tên', 'en' => 'Full Name']],
            ['group' => 'checkout', 'key' => 'country', 'text' => ['vi' => 'Quốc gia', 'en' => 'Country']],
            ['group' => 'checkout', 'key' => 'state', 'text' => ['vi' => 'Tỉnh / Thành phố', 'en' => 'State / Province']],
            ['group' => 'checkout', 'key' => 'city', 'text' => ['vi' => 'Quận / Huyện', 'en' => 'City / District']],
            ['group' => 'checkout', 'key' => 'address', 'text' => ['vi' => 'Địa chỉ chi tiết', 'en' => 'Detail Address']],
            ['group' => 'checkout', 'key' => 'phone', 'text' => ['vi' => 'Số điện thoại', 'en' => 'Phone Number']],
            ['group' => 'checkout', 'key' => 'mess', 'text' => ['vi' => 'Ghi chú đơn hàng', 'en' => 'Order Note']],
            ['group' => 'checkout', 'key' => 'method_payment', 'text' => ['vi' => 'Phương thức thanh toán', 'en' => 'Payment Method']],
            ['group' => 'checkout', 'key' => 'bank_transfer', 'text' => ['vi' => 'Chuyển khoản ngân hàng', 'en' => 'Bank Transfer']],
            ['group' => 'checkout', 'key' => 'des_bank_transfer', 'text' => ['vi' => 'Chuyển khoản qua tài khoản ngân hàng của chúng tôi.', 'en' => 'Direct bank transfer to our bank account.']],
            ['group' => 'checkout', 'key' => 'des_paypal', 'text' => ['vi' => 'Thanh toán trực tuyến bảo mật qua cổng PayPal.', 'en' => 'Pay online securely via PayPal.']],
            ['group' => 'checkout', 'key' => 'button', 'text' => ['vi' => 'Xác nhận đặt hàng', 'en' => 'Place Order']],
            ['group' => 'checkout', 'key' => 'mess_nonpayment', 'text' => ['vi' => 'Thanh toán khi nhận hàng (COD)', 'en' => 'Cash on delivery (COD)']],

            // Payment
            ['group' => 'payment', 'key' => 'content_email_user', 'text' => ['vi' => 'Cảm ơn [name] đã đặt hàng. Mã đơn hàng: [id]', 'en' => 'Thank you [name] for your order. Order ID: [id]']],
            ['group' => 'payment', 'key' => 'content_email_user_nonpayment', 'text' => ['vi' => 'Cảm ơn [name] đã đặt hàng. Mã đơn hàng: [id]', 'en' => 'Thank you [name] for your order. Order ID: [id]']],
            ['group' => 'payment', 'key' => 'content_thanks_you', 'text' => ['vi' => 'Cảm ơn bạn đã mua hàng!', 'en' => 'Thank you for your purchase!']],
            ['group' => 'payment', 'key' => 'content_thanks_you_nonpayment', 'text' => ['vi' => 'Cảm ơn bạn đã đặt hàng thành công!', 'en' => 'Thank you for your order!']],
            ['group' => 'payment', 'key' => 'bank_info', 'text' => ['vi' => 'Thông tin tài khoản ngân hàng', 'en' => 'Bank Account Details']],

            // Footer
            ['group' => 'footer', 'key' => 'help', 'text' => ['vi' => 'Hỗ trợ & Thông tin', 'en' => 'Support & Information']],
            ['group' => 'footer', 'key' => 'certify', 'text' => ['vi' => 'Chứng nhận', 'en' => 'Certified by']],
            ['group' => 'footer', 'key' => 'products', 'text' => ['vi' => 'Danh mục sản phẩm', 'en' => 'Product Categories']],
            ['group' => 'footer', 'key' => 'follow', 'text' => ['vi' => 'Theo dõi chúng tôi', 'en' => 'Follow Us']],
            ['group' => 'footer', 'key' => 'about', 'text' => ['vi' => 'Về chúng tôi', 'en' => 'About Us']],
            ['group' => 'footer', 'key' => 'contact', 'text' => ['vi' => 'Liên hệ', 'en' => 'Contact']],
            ['group' => 'footer', 'key' => 'links', 'text' => ['vi' => 'Liên kết nhanh', 'en' => 'Quick Links']],
            ['group' => 'footer', 'key' => 'rights', 'text' => ['vi' => 'Bảo lưu mọi quyền.', 'en' => 'All rights reserved.']],
        ];

        foreach ($lines as $line) {
            LanguageLine::updateOrCreate(
                ['group' => $line['group'], 'key' => $line['key']],
                ['text' => $line['text']]
            );
        }
    }
}
