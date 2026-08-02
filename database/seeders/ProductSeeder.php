<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate existing products table
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Product::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $categories = ProductCategory::all();
        if ($categories->isEmpty()) {
            return;
        }

        $imagesPool = [
            '/images/products/multivitamin_daily.png',
            '/images/products/vitamin_c_shield.png',
            '/images/products/whey_protein.png',
            '/images/products/collagen_peptide.png',
            '/images/products/herbal_detox_tea.png',
            '/images/products/omega3_fish_oil.png',
        ];

        $originsPool = [
            ['vi' => 'Mỹ', 'en' => 'USA'],
            ['vi' => 'Đức', 'en' => 'Germany'],
            ['vi' => 'Nhật Bản', 'en' => 'Japan'],
            ['vi' => 'Pháp', 'en' => 'France'],
            ['vi' => 'Hàn Quốc', 'en' => 'Korea'],
            ['vi' => 'Việt Nam', 'en' => 'Vietnam'],
            ['vi' => 'Úc', 'en' => 'Australia'],
        ];

        $catalog = [
            'bo-sung-vitamin-khoang-chat' => [
                ['name_vi' => 'Daily Multivitamin Complex 30-in-1', 'name_en' => 'Daily Multivitamin Complex 30-in-1', 'price_vi' => '480000', 'price_en' => '22'],
                ['name_vi' => 'Vitamin D3 K2 Drops 5000IU High Potency', 'name_en' => 'Vitamin D3 K2 Drops 5000IU High Potency', 'price_vi' => '390000', 'price_en' => '18'],
                ['name_vi' => 'Magnesium Glycinate 400mg Chống Mệt Mỏi', 'name_en' => 'Magnesium Glycinate 400mg Anti-Fatigue', 'price_vi' => '520000', 'price_en' => '24'],
                ['name_vi' => 'Zinc Picolinate 50mg Bổ Sung Kẽm Tinh Khiết', 'name_en' => 'Zinc Picolinate 50mg Pure Bioavailable', 'price_vi' => '320000', 'price_en' => '15'],
                ['name_vi' => 'B-Complex High Potency Vitamin B Tổng Hợp', 'name_en' => 'B-Complex High Potency Vitamin B Energy', 'price_vi' => '450000', 'price_en' => '20'],
                ['name_vi' => 'Vitamin B12 Methylcobalamin 5000mcg', 'name_en' => 'Vitamin B12 Methylcobalamin 5000mcg Sublingual', 'price_vi' => '380000', 'price_en' => '17'],
                ['name_vi' => 'Calcium Citrate Magnesium D3 Bổ Xương', 'name_en' => 'Calcium Citrate Magnesium D3 Bone Formula', 'price_vi' => '550000', 'price_en' => '25'],
                ['name_vi' => 'Iron Complex Gentle Iron Bổ Máu Khởi Sắc', 'name_en' => 'Iron Complex Gentle Iron Blood Booster', 'price_vi' => '360000', 'price_en' => '16'],
                ['name_vi' => 'Electrolyte Hydration Mineral Powder', 'name_en' => 'Electrolyte Hydration Mineral Powder Mix', 'price_vi' => '420000', 'price_en' => '19'],
                ['name_vi' => 'Trace Mineral Concentrate Drops 118ml', 'name_en' => 'Trace Mineral Concentrate Liquid Drops', 'price_vi' => '490000', 'price_en' => '23'],
            ],
            'ho-tro-mien-dich-de-khang' => [
                ['name_vi' => 'Vitamin C 1000mg Tăng Cường Miễn Dịch', 'name_en' => 'Vitamin C 1000mg Immune Shield', 'price_vi' => '390000', 'price_en' => '18'],
                ['name_vi' => 'Elderberry Extract Gummies Cây Cơm Cháy', 'name_en' => 'Elderberry Extract Gummies Sambucus', 'price_vi' => '460000', 'price_en' => '21'],
                ['name_vi' => 'Quercetin Phytosome 500mg Kháng Hấp Thu', 'name_en' => 'Quercetin Phytosome 500mg Antioxidant', 'price_vi' => '680000', 'price_en' => '30'],
                ['name_vi' => 'Propolis Extract Throat Spray Keo Ong Mỹ', 'name_en' => 'Propolis Extract Throat Spray Natural Shield', 'price_vi' => '290000', 'price_en' => '13'],
                ['name_vi' => 'Colostrum IgG 1000mg Sữa Non Đề Kháng', 'name_en' => 'Colostrum IgG 1000mg Immunity Powder', 'price_vi' => '850000', 'price_en' => '38'],
                ['name_vi' => 'Olive Leaf Extract 20% Oleuropein', 'name_en' => 'Olive Leaf Extract 20% Oleuropein Formula', 'price_vi' => '510000', 'price_en' => '23'],
                ['name_vi' => 'Astragalus Root Extract Hoàng Kỳ Cao Cấp', 'name_en' => 'Astragalus Root Extract Vitality Shield', 'price_vi' => '440000', 'price_en' => '20'],
                ['name_vi' => 'Beta Glucan 1-3 1-6 Immune Booster', 'name_en' => 'Beta Glucan 1-3 1-6 Cellular Defense', 'price_vi' => '720000', 'price_en' => '32'],
                ['name_vi' => 'Zinc & Vitamin C Effervescent Viên Sủi', 'name_en' => 'Zinc & Vitamin C Effervescent Immunity', 'price_vi' => '240000', 'price_en' => '11'],
                ['name_vi' => 'Echinacea Goldenseal Complex Dược Liệu', 'name_en' => 'Echinacea Goldenseal Complex Herbal Guard', 'price_vi' => '480000', 'price_en' => '21'],
            ],
            'dinh-duong-the-thao-whey' => [
                ['name_vi' => 'Whey Protein Isolate Bổ Sung Cơ Bắp (2.27kg)', 'name_en' => 'Whey Protein Isolate Pure Muscle (5 lbs)', 'price_vi' => '1850000', 'price_en' => '79'],
                ['name_vi' => 'Hydrolyzed Whey Platinum HydroWhey 1.6kg', 'name_en' => 'Hydrolyzed Whey Platinum HydroWhey', 'price_vi' => '2100000', 'price_en' => '92'],
                ['name_vi' => 'Creatine Monohydrate Pure Micronized 500g', 'name_en' => 'Creatine Monohydrate Pure Micronized Powder', 'price_vi' => '650000', 'price_en' => '28'],
                ['name_vi' => 'BCAA 2:1:1 Amino Acid Recovery Matrix', 'name_en' => 'BCAA 2:1:1 Amino Acid Muscle Recovery', 'price_vi' => '580000', 'price_en' => '26'],
                ['name_vi' => 'Pre-Workout Explosive Energy Matrix 300g', 'name_en' => 'Pre-Workout Explosive Energy Matrix Powder', 'price_vi' => '750000', 'price_en' => '33'],
                ['name_vi' => 'Mass Gainer Muscle Building Powder (5.4kg)', 'name_en' => 'Mass Gainer Muscle Building Powder (12 lbs)', 'price_vi' => '1950000', 'price_en' => '85'],
                ['name_vi' => 'L-Glutamine Pure Micronized Anti-Catabolic', 'name_en' => 'L-Glutamine Pure Micronized Muscle Saver', 'price_vi' => '490000', 'price_en' => '22'],
                ['name_vi' => 'L-Carnitine Liquid 3000mg Đốt Mỡ Thể Thao', 'name_en' => 'L-Carnitine Liquid 3000mg Fat Burner', 'price_vi' => '590000', 'price_en' => '26'],
                ['name_vi' => 'Whey Protein Concentrate Chocolate Fudge', 'name_en' => 'Whey Protein Concentrate Chocolate Fudge 2kg', 'price_vi' => '1450000', 'price_en' => '64'],
                ['name_vi' => 'EAA Essential Amino Acids Hydration Formula', 'name_en' => 'EAA Essential Amino Acids Muscle Hydration', 'price_vi' => '690000', 'price_en' => '30'],
            ],
            'lam-dep-collagen-tre-hoa' => [
                ['name_vi' => 'Bột Collagen Peptide Thủy Phân Trẻ Hóa Da', 'name_en' => 'Hydrolyzed Marine Collagen Peptide Powder', 'price_vi' => '920000', 'price_en' => '42'],
                ['name_vi' => 'Collagen Type 1 & 3 Vitamin C Tablets', 'name_en' => 'Collagen Type 1 & 3 Vitamin C Skin Firming', 'price_vi' => '650000', 'price_en' => '29'],
                ['name_vi' => 'Biotin 10,000mcg Mọc Tóc Dày Chắc Khỏe', 'name_en' => 'Biotin 10,000mcg Hair Growth Strength', 'price_vi' => '350000', 'price_en' => '16'],
                ['name_vi' => 'Hyaluronic Acid 200mg Dưỡng Ẩm Căng Bóng', 'name_en' => 'Hyaluronic Acid 200mg Skin Hydration', 'price_vi' => '580000', 'price_en' => '25'],
                ['name_vi' => 'Glutathione 1000mg Skin Whitening Brightening', 'name_en' => 'Glutathione 1000mg Skin Whitening Glow', 'price_vi' => '1250000', 'price_en' => '55'],
                ['name_vi' => 'Astaxanthin 12mg Super Antioxidant Chống Lão Hóa', 'name_en' => 'Astaxanthin 12mg Super Antioxidant Care', 'price_vi' => '790000', 'price_en' => '35'],
                ['name_vi' => 'Evening Primrose Oil 1000mg Tinh Dầu Hoa Anh Thảo', 'name_en' => 'Evening Primrose Oil 1000mg Pure Cold Pressed', 'price_vi' => '460000', 'price_en' => '20'],
                ['name_vi' => 'Resveratrol Anti-Aging Youth Complex 500mg', 'name_en' => 'Resveratrol Anti-Aging Youth Complex 500mg', 'price_vi' => '880000', 'price_en' => '39'],
                ['name_vi' => 'CoQ10 200mg Softgels Tim Mạch & Làn Da', 'name_en' => 'CoQ10 200mg Softgels Cellular Health', 'price_vi' => '750000', 'price_en' => '33'],
                ['name_vi' => 'NMN 500mg Cellular Rejuvenation Trẻ Hóa Tế Bào', 'name_en' => 'NMN 500mg Cellular Rejuvenation Formula', 'price_vi' => '2400000', 'price_en' => '105'],
            ],
            'thao-duoc-detox-thanh-loc' => [
                ['name_vi' => 'Trà Thảo Dược Detox Thanh Lọc Cơ Thể Organic', 'name_en' => 'Organic Herbal Detox Cleansing Tea', 'price_vi' => '320000', 'price_en' => '15'],
                ['name_vi' => 'Milk Thistle Liver Detox 500mg Bổ Gan Mỹ', 'name_en' => 'Milk Thistle Liver Detox 500mg Silymarin', 'price_vi' => '490000', 'price_en' => '22'],
                ['name_vi' => 'Apple Cider Vinegar Gummies Dấm Táo Detox', 'name_en' => 'Apple Cider Vinegar Gummies Organic Mother', 'price_vi' => '380000', 'price_en' => '17'],
                ['name_vi' => 'Spirulina Organic Superfood Tảo Biển Nhật', 'name_en' => 'Spirulina Organic Superfood Green Powder', 'price_vi' => '540000', 'price_en' => '24'],
                ['name_vi' => 'Chlorella Green Detox Organic Tablets 1000 Viên', 'name_en' => 'Chlorella Green Detox Organic Cleanse Tablets', 'price_vi' => '620000', 'price_en' => '27'],
                ['name_vi' => 'Dandelion Root Extract Chiết Xuất Rễ Cây Bồ Công Anh', 'name_en' => 'Dandelion Root Extract Herbal Fluid Cleanse', 'price_vi' => '390000', 'price_en' => '18'],
                ['name_vi' => 'Wheatgrass Powder Organic Cỏ Lúa Mì Detox', 'name_en' => 'Wheatgrass Powder Organic Alkalizing Green', 'price_vi' => '430000', 'price_en' => '19'],
                ['name_vi' => 'Activated Charcoal Capsules Than Hoạt Tính', 'name_en' => 'Activated Charcoal Capsules Digestive Absorbent', 'price_vi' => '310000', 'price_en' => '14'],
                ['name_vi' => 'Triphala Herbal Ayurvedic Cleanse Formula', 'name_en' => 'Triphala Herbal Ayurvedic Cleanse Supplement', 'price_vi' => '370000', 'price_en' => '16'],
                ['name_vi' => 'Colon Cleanse 14-Day Quick Herbal Formula', 'name_en' => 'Colon Cleanse 14-Day Quick Detox Formula', 'price_vi' => '450000', 'price_en' => '20'],
            ],
            'bo-nao-tang-cuong-tri-nho' => [
                ['name_vi' => 'Ginkgo Biloba 120mg Bổ Não Bạch Quả Mỹ', 'name_en' => 'Ginkgo Biloba 120mg Brain Health Extract', 'price_vi' => '380000', 'price_en' => '17'],
                ['name_vi' => 'Omega-3 Fish Oil High DHA 500mg Não Bộ', 'name_en' => 'Omega-3 Fish Oil High DHA Brain Focus', 'price_vi' => '620000', 'price_en' => '27'],
                ['name_vi' => 'Phosphatidylserine 300mg Tăng Trí Nhớ', 'name_en' => 'Phosphatidylserine 300mg Memory Boost', 'price_vi' => '790000', 'price_en' => '34'],
                ['name_vi' => 'Alpha GPC 600mg Nootropic Focus Booster', 'name_en' => 'Alpha GPC 600mg Nootropic Cognitive Focus', 'price_vi' => '850000', 'price_en' => '37'],
                ['name_vi' => 'Lion\'s Mane Mushroom Extract Nấm Đầu Khỉ', 'name_en' => 'Lion\'s Mane Mushroom Nootropic Brain Extract', 'price_vi' => '560000', 'price_en' => '25'],
                ['name_vi' => 'Bacopa Monnieri Memory Support Extract', 'name_en' => 'Bacopa Monnieri Memory Retention Herbal', 'price_vi' => '470000', 'price_en' => '21'],
                ['name_vi' => 'Huperzine A 200mcg Brain Circulation Shield', 'name_en' => 'Huperzine A 200mcg Neuro Circulation', 'price_vi' => '510000', 'price_en' => '22'],
                ['name_vi' => 'Neuro Peak Nootropic Focus & Memory Formula', 'name_en' => 'Neuro Peak Nootropic Brain Support Matrix', 'price_vi' => '690000', 'price_en' => '30'],
                ['name_vi' => 'Omega 3-6-9 Organic Flaxseed Bổ Não Tim', 'name_en' => 'Omega 3-6-9 Organic Flaxseed Triple Care', 'price_vi' => '420000', 'price_en' => '19'],
                ['name_vi' => 'Phosphatidylcholine High Purity Complex', 'name_en' => 'Phosphatidylcholine High Purity Brain Formula', 'price_vi' => '730000', 'price_en' => '32'],
            ],
            'bo-tim-mach-huyet-ap' => [
                ['name_vi' => 'Dầu Cá Omega-3 Cao Cấp 1000mg (180 Viên)', 'name_en' => 'Premium Omega-3 Fish Oil 1000mg (180 Softgels)', 'price_vi' => '650000', 'price_en' => '29'],
                ['name_vi' => 'Coenzyme CoQ10 300mg High Absorption Tim', 'name_en' => 'Coenzyme CoQ10 300mg Heart Energy Boost', 'price_vi' => '820000', 'price_en' => '36'],
                ['name_vi' => 'Garlic Extract Odorless 5000mg Tinh Dầu Tỏi', 'name_en' => 'Garlic Extract Odorless Heart Support 5000mg', 'price_vi' => '340000', 'price_en' => '15'],
                ['name_vi' => 'Nattokinase 2000FU Nhật Bản Tan Cục Máu Đông', 'name_en' => 'Nattokinase 2000FU Japan Circulation Shield', 'price_vi' => '680000', 'price_en' => '30'],
                ['name_vi' => 'Red Yeast Rice 1200mg Men Gạo Đỏ Hạ Mỡ Máu', 'name_en' => 'Red Yeast Rice 1200mg Healthy Cholesterol', 'price_vi' => '590000', 'price_en' => '26'],
                ['name_vi' => 'Hawthorn Berry Extract Dược Liệu Tim Mạnh', 'name_en' => 'Hawthorn Berry Extract Cardiac Health Fluid', 'price_vi' => '410000', 'price_en' => '18'],
                ['name_vi' => 'Policosanol 20mg Cholesterol Balance Formula', 'name_en' => 'Policosanol 20mg Lipid Balance Formula', 'price_vi' => '480000', 'price_en' => '21'],
                ['name_vi' => 'Krill Oil Pure Antarctic 1000mg Dầu Tôm Nam Cực', 'name_en' => 'Krill Oil Pure Antarctic 1000mg Omega-3 Astaxanthin', 'price_vi' => '950000', 'price_en' => '42'],
                ['name_vi' => 'Grape Seed Extract 300mg Polyphenol Tim', 'name_en' => 'Grape Seed Extract 300mg Cardiovascular Shield', 'price_vi' => '530000', 'price_en' => '23'],
                ['name_vi' => 'Olive Leaf Polyphenols Heart & Vascular', 'name_en' => 'Olive Leaf Polyphenols Heart & Vascular Care', 'price_vi' => '570000', 'price_en' => '25'],
            ],
            'bo-xuong-khop-chieu-cao' => [
                ['name_vi' => 'Glucosamine Chondroitin MSM Ultra Joint Flex', 'name_en' => 'Glucosamine Chondroitin MSM Ultra Joint Support', 'price_vi' => '780000', 'price_en' => '34'],
                ['name_vi' => 'Collagen Type II Hydrolyzed Tái Tạo Sụn Khớp', 'name_en' => 'Collagen Type II Hydrolyzed Joint Cartilage', 'price_vi' => '890000', 'price_en' => '39'],
                ['name_vi' => 'Calcium Magnesium D3 K2 Bổ Xương Cao Cấp', 'name_en' => 'Calcium Magnesium D3 K2 Bone Builder Multi', 'price_vi' => '540000', 'price_en' => '24'],
                ['name_vi' => 'Hyaluronic Acid Joint Lubricant 100mg Tăng Nhờn Khớp', 'name_en' => 'Hyaluronic Acid Joint Lubricant Formula', 'price_vi' => '620000', 'price_en' => '27'],
                ['name_vi' => 'Turmeric Curcumin 95% Extract Tinh Nghệ Giảm Đau', 'name_en' => 'Turmeric Curcumin 95% Extract Joint Inflammation', 'price_vi' => '490000', 'price_en' => '22'],
                ['name_vi' => 'Boswellia Serrata Extract Cây Cỏ Cà Giảm Viêm', 'name_en' => 'Boswellia Serrata Extract Joint Relief Formula', 'price_vi' => '460000', 'price_en' => '20'],
                ['name_vi' => 'Coral Calcium Organic 1000mg Canxi San Hô Nhật', 'name_en' => 'Coral Calcium Organic 1000mg Japan Bioavailable', 'price_vi' => '590000', 'price_en' => '26'],
                ['name_vi' => 'Bone Density Multi Formula Vitamin K2 D3 Calc', 'name_en' => 'Bone Density Multi Formula Mineral Complex', 'price_vi' => '670000', 'price_en' => '29'],
                ['name_vi' => 'Shark Cartilage Pure Extract Sụn Vi Cá Cập', 'name_en' => 'Shark Cartilage Pure Extract Joint Shield', 'price_vi' => '720000', 'price_en' => '32'],
                ['name_vi' => 'Height Growth Calcium D3 K2 Phát Triển Chiều Cao', 'name_en' => 'Height Growth Calcium D3 K2 Youth Formula', 'price_vi' => '630000', 'price_en' => '28'],
            ],
            'ho-tro-tieu-hoa-men-vi-sinh' => [
                ['name_vi' => 'Probiotics 50 Billion CFU 14 Strains Men Vi Sinh', 'name_en' => 'Probiotics 50 Billion CFU 14 Strains Gut Health', 'price_vi' => '690000', 'price_en' => '30'],
                ['name_vi' => 'Spore Probiotics Bào Tử Lợi Khuẩn Kháng Acid', 'name_en' => 'Spore Probiotics Acid Resistant Gut Guard', 'price_vi' => '750000', 'price_en' => '33'],
                ['name_vi' => 'Digestive Enzymes 18-in-1 Enzyme Tiêu Hóa', 'name_en' => 'Digestive Enzymes 18-in-1 Full Spectrum Formula', 'price_vi' => '540000', 'price_en' => '24'],
                ['name_vi' => 'Prebiotic Fiber Organic Powder Chất Xơ Lợi Khuẩn', 'name_en' => 'Prebiotic Fiber Organic Powder Gut Support', 'price_vi' => '420000', 'price_en' => '19'],
                ['name_vi' => 'L-Glutamine Powder 500g Phục Hồi Niêm Mạc Ruột', 'name_en' => 'L-Glutamine Powder 500g Gut Lining Repair', 'price_vi' => '650000', 'price_en' => '28'],
                ['name_vi' => 'Papaya Enzyme Chewable Tablets Enzyme Đủ Đủ', 'name_en' => 'Papaya Enzyme Chewable Tablets Stomach Ease', 'price_vi' => '290000', 'price_en' => '13'],
                ['name_vi' => 'Peppermint Oil Enteric Coated Tinh Dầu Bạc Hà', 'name_en' => 'Peppermint Oil Enteric Coated IBS Comfort', 'price_vi' => '360000', 'price_en' => '16'],
                ['name_vi' => 'Aloe Vera Inner Leaf Juice Gel Nha Đam Tiêu Hóa', 'name_en' => 'Aloe Vera Inner Leaf Juice Gel Soothing Cleanser', 'price_vi' => '380000', 'price_en' => '17'],
                ['name_vi' => 'Berberine HCL 500mg Cân Bằng Tiêu Hóa', 'name_en' => 'Berberine HCL 500mg Gut Balance Complex', 'price_vi' => '490000', 'price_en' => '22'],
                ['name_vi' => 'Saccharomyces Boulardii Men Vi Sinh Kháng Sinh', 'name_en' => 'Saccharomyces Boulardii Probiotic Anti-Diarrheal', 'price_vi' => '580000', 'price_en' => '25'],
            ],
            'giac-ngu-ngon-giam-stress' => [
                ['name_vi' => 'Melatonin 5mg Fast Dissolve Giúp Ngủ Ngon Nhanh', 'name_en' => 'Melatonin 5mg Fast Dissolve Sleep Support', 'price_vi' => '290000', 'price_en' => '13'],
                ['name_vi' => 'Ashwagandha KSM-66 Organic Stress Relief', 'name_en' => 'Ashwagandha KSM-66 Organic Stress Relief Calm', 'price_vi' => '580000', 'price_en' => '26'],
                ['name_vi' => 'L-Theanine 200mg Calming Formula Thư Giãn Tinh Thần', 'name_en' => 'L-Theanine 200mg Calming Formula Relaxation', 'price_vi' => '390000', 'price_en' => '17'],
                ['name_vi' => 'Valerian Root Extract Rễ Cây Nữ Lang Ngủ Sâu', 'name_en' => 'Valerian Root Extract Restful Night Herbs', 'price_vi' => '420000', 'price_en' => '19'],
                ['name_vi' => '5-HTP 100mg Mood & Sleep Support Tiền Chất Serotonin', 'name_en' => '5-HTP 100mg Mood & Sleep Support Formula', 'price_vi' => '460000', 'price_en' => '20'],
                ['name_vi' => 'Passion Flower Organic Drops Cây Lạc Tiên Drops', 'name_en' => 'Passion Flower Organic Sleep Drops Fluid', 'price_vi' => '370000', 'price_en' => '16'],
                ['name_vi' => 'Chamomile & Lavender Night Tea Trà Hoa Cúc Thư Giãn', 'name_en' => 'Chamomile & Lavender Night Herbal Sleep Tea', 'price_vi' => '310000', 'price_en' => '14'],
                ['name_vi' => 'Magnesium Night Calming Drink Powder', 'name_en' => 'Magnesium Night Calming Drink Sleep Powder', 'price_vi' => '480000', 'price_en' => '21'],
                ['name_vi' => 'GABA 500mg Fast Acting Neuro Relaxation', 'name_en' => 'GABA 500mg Fast Acting Relaxation Matrix', 'price_vi' => '430000', 'price_en' => '19'],
                ['name_vi' => 'Holy Basil Tulsi Stress Shield Adaptogen', 'name_en' => 'Holy Basil Tulsi Stress Shield Adaptogen Care', 'price_vi' => '410000', 'price_en' => '18'],
            ],
        ];

        $pIndex = 1;
        foreach ($catalog as $catSlug => $items) {
            $catModel = $categories->where('slug', $catSlug)->first() ?? $categories->first();

            foreach ($items as $idx => $itemData) {
                $p = new Product();
                $p->product_category_id = $catModel->id;
                $p->code = strtoupper(Str::slug($itemData['name_en'], '-')) . '-' . rand(100, 999);
                $p->model = $itemData['name_en'];
                $img = $imagesPool[($pIndex - 1) % count($imagesPool)];
                $p->image = $img;
                $p->multiple_image = json_encode([$img, $img]);
                $p->quantity = rand(50, 300);
                $p->slug = Str::slug($itemData['name_vi'], '-') . '-' . $pIndex;
                $p->tag = 'supplement,health,wellness,dietary,nutrition';
                $p->meta_robots = 'index,follow';
                $p->rel = 'dofollow';
                $p->target = '_self';
                $p->status = 1;

                $origin = $originsPool[($pIndex - 1) % count($originsPool)];

                // Set Translatable Fields
                $p->setTranslation('name', 'vi', $itemData['name_vi']);
                $p->setTranslation('name', 'en', $itemData['name_en']);
                $p->setTranslation('meta_title', 'vi', $itemData['name_vi']);
                $p->setTranslation('meta_title', 'en', $itemData['name_en']);
                $p->setTranslation('meta_keyword', 'vi', mb_strtolower($itemData['name_vi']));
                $p->setTranslation('meta_keyword', 'en', mb_strtolower($itemData['name_en']));

                $p->setTranslation('price', 'vi', $itemData['price_vi']);
                $p->setTranslation('price', 'en', $itemData['price_en']);

                $p->setTranslation('unit', 'vi', 'đ');
                $p->setTranslation('unit', 'en', '$');

                $p->setTranslation('made', 'vi', $origin['vi']);
                $p->setTranslation('made', 'en', $origin['en']);

                $summaryVi = 'Sản phẩm bổ sung sức khỏe cao cấp ' . $itemData['name_vi'] . ' hỗ trợ cơ thể dồi dào sinh lực.';
                $summaryEn = 'Premium health supplement ' . $itemData['name_en'] . ' supports overall body vitality.';
                $p->setTranslation('summary', 'vi', $summaryVi);
                $p->setTranslation('summary', 'en', $summaryEn);

                $descVi = '<p>Sản phẩm ' . $itemData['name_vi'] . ' được chiết xuất tinh khiết nhập khẩu chính hãng từ ' . $origin['vi'] . '. Đạt tiêu chuẩn kiểm định an toàn thực phẩm GMP & FDA quốc tế.</p>';
                $descEn = '<p>The product ' . $itemData['name_en'] . ' is extracted with pure ingredients imported from ' . $origin['en'] . '. Certified under international GMP & FDA standards.</p>';
                $p->setTranslation('description', 'vi', $descVi);
                $p->setTranslation('description', 'en', $descEn);
                $p->setTranslation('meta_description', 'vi', strip_tags($summaryVi));
                $p->setTranslation('meta_description', 'en', strip_tags($summaryEn));

                $p->save();
                $pIndex++;
            }
        }
    }
}
