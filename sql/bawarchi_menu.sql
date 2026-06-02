-- ============================================
-- Bawarchi Restaurant Menu Data
-- Run this in phpMyAdmin after selecting the pos_bawarchi database
-- ============================================

USE pos_bawarchi;

-- ============================================
-- Insert Categories
-- ============================================
INSERT INTO categories (name_en, name_ar, sort_order) VALUES
('Breakfast', 'الإفطار', 1),
('Grills', 'مشاوي', 2),
('Chicken', 'دجاج', 3),
('Mutton', 'خروف', 4),
('Beef', 'لحم بقري', 5),
('Fish', 'سمك', 6),
('Chinese', 'صيني', 7),
('Biryani & Rice', 'برياني وأرز', 8),
('Special Meals', 'وجبات خاصة', 9),
('Veg & Egg', 'خضار وبيض', 10),
('Roti', 'روتي', 11),
('Beverages', 'مشروبات', 12),
('Chef''s Recommendation', 'توصيات الشيف', 13);

-- ============================================
-- Breakfast Items (category_id = 1)
-- ============================================
INSERT INTO items (category_id, name_en, name_ar, price) VALUES
(1, 'Chana Bhatura', 'شانا باتورا', 1.200),
(1, 'Aloo Paratha', 'ألو باراثا', 0.800),
(1, 'Gobi Paratha', 'جوبي باراثا', 0.800),
(1, 'Paneer Paratha', 'بانير باراثا', 1.000),
(1, 'Puri Bhaji', 'بوري بهاجي', 0.900),
(1, 'Upma', 'أوبما', 0.700),
(1, 'Idli Sambar', 'إيدلي سامبار', 0.800),
(1, 'Masala Dosa', 'دوسا ماسالا', 1.200),
(1, 'Plain Dosa', 'دوسا عادية', 1.000),
(1, 'Vada Sambar', 'فادا سامبار', 0.800);

-- ============================================
-- Grills Items (category_id = 2)
-- ============================================
INSERT INTO items (category_id, name_en, name_ar, price_small, price_large) VALUES
(2, 'Tandoori Chicken Full', 'دجاج تندوري كامل', 2.500, 4.500),
(2, 'Chicken Tikka', 'دجاج تيكا', 1.800, 3.200),
(2, 'Seekh Kebab', 'سيخ كباب', 2.000, 3.500),
(2, 'Boti Kebab', 'بوتي كباب', 2.200, 4.000),
(2, 'Reshmi Kebab', 'ريشمي كباب', 2.000, 3.500),
(2, 'Malai Kebab', 'مالاي كباب', 2.200, 4.000);

-- ============================================
-- Chicken Items (category_id = 3)
-- ============================================
INSERT INTO items (category_id, name_en, name_ar, price) VALUES
(3, 'Butter Chicken', 'دجاج بالزبدة', 2.500),
(3, 'Chicken Tikka Masala', 'دجاج تيكا ماسالا', 2.800),
(3, 'Chicken Korma', 'دجاج كورما', 2.500),
(3, 'Chicken Curry', 'كاري دجاج', 2.200),
(3, 'Kadai Chicken', 'كاداي دجاج', 2.600),
(3, 'Chilli Chicken', 'دجاج تشيلي', 2.400),
(3, 'Chicken Manchurian', 'دجاج منشوريان', 2.400),
(3, 'Chicken 65', 'دجاج 65', 2.300);

-- ============================================
-- Mutton Items (category_id = 4)
-- ============================================
INSERT INTO items (category_id, name_en, name_ar, price) VALUES
(4, 'Mutton Rogan Josh', 'خروف روغان جوش', 3.500),
(4, 'Mutton Korma', 'خروف كورما', 3.200),
(4, 'Mutton Curry', 'كاري خروف', 3.000),
(4, 'Mutton Biryani', 'برياني خروف', 3.800),
(4, 'Mutton Keema', 'خروف كيما', 2.800),
(4, 'Mutton Seekh Kebab', 'سيخ كباب خروف', 3.200);

-- ============================================
-- Beef Items (category_id = 5)
-- ============================================
INSERT INTO items (category_id, name_en, name_ar, price) VALUES
(5, 'Beef Curry', 'كاري لحم بقري', 2.800),
(5, 'Beef Fry', 'لحم بقري مقلي', 3.000),
(5, 'Beef Biryani', 'برياني لحم بقري', 3.200),
(5, 'Beef Keema', 'لحم بقري كيما', 2.500),
(5, 'Chilli Beef', 'تشيلي لحم بقري', 2.800),
(5, 'Beef Manchurian', 'لحم بقري منشوريان', 2.800);

-- ============================================
-- Fish Items (category_id = 6)
-- ============================================
INSERT INTO items (category_id, name_en, name_ar, price) VALUES
(6, 'Fish Curry', 'كاري سمك', 3.200),
(6, 'Fish Fry', 'سمك مقلي', 3.500),
(6, 'Fish Biryani', 'برياني سمك', 3.800),
(6, 'Chilli Fish', 'تشيلي سمك', 3.500),
(7, 'Pomfret Fry', 'بومفريت مقلي', 4.200);

-- ============================================
-- Chinese Items (category_id = 7)
-- ============================================
INSERT INTO items (category_id, name_en, name_ar, price) VALUES
(7, 'Veg Fried Rice', 'أرز مقلي خضار', 1.500),
(7, 'Chicken Fried Rice', 'أرز مقلي دجاج', 1.800),
(7, 'Egg Fried Rice', 'أرز مقلي بيض', 1.600),
(7, 'Prawn Fried Rice', 'أرز مقلي روبيان', 2.200),
(7, 'Veg Noodles', 'نودلز خضار', 1.500),
(7, 'Chicken Noodles', 'نودلز دجاج', 1.800),
(7, 'Hakka Noodles', 'نودلز هاكا', 1.600),
(7, 'Chilli Paneer', 'تشيلي بانير', 2.000),
(7, 'Gobi Manchurian', 'جوبي منشوريان', 1.800),
(7, 'Spring Rolls', 'رولات ربيعية', 1.500);

-- ============================================
-- Biryani & Rice Items (category_id = 8)
-- ============================================
INSERT INTO items (category_id, name_en, name_ar, price) VALUES
(8, 'Chicken Biryani', 'برياني دجاج', 2.500),
(8, 'Mutton Biryani', 'برياني خروف', 3.500),
(8, 'Beef Biryani', 'برياني لحم بقري', 3.000),
(8, 'Fish Biryani', 'برياني سمك', 3.500),
(8, 'Veg Biryani', 'برياني خضار', 2.000),
(8, 'Egg Biryani', 'برياني بيض', 2.200),
(8, 'Jeera Rice', 'أرز جيرا', 1.200),
(8, 'Ghee Rice', 'أرز سمن', 1.500);

-- ============================================
-- Special Meals Items (category_id = 9)
-- ============================================
INSERT INTO items (category_id, name_en, name_ar, price) VALUES
(9, 'Thali Veg', 'ثالي خضار', 2.500),
(9, 'Thali Non-Veg', 'ثالي غير خضار', 3.500),
(9, 'Family Pack Veg', 'باقة عائلية خضار', 8.000),
(9, 'Family Pack Non-Veg', 'باقة عائلية غير خضار', 12.000),
(9, 'Executive Thali', 'ثالي تنفيذي', 4.500);

-- ============================================
-- Veg & Egg Items (category_id = 10)
-- ============================================
INSERT INTO items (category_id, name_en, name_ar, price) VALUES
(10, 'Paneer Butter Masala', 'بانير بالزبدة ماسالا', 2.200),
(10, 'Palak Paneer', 'سبانخ بانير', 2.000),
(10, 'Shahi Paneer', 'شاهي بانير', 2.300),
(10, 'Kadai Paneer', 'كاداي بانير', 2.100),
(10, 'Dal Makhani', 'دال مخاني', 1.800),
(10, 'Dal Tadka', 'دال تادكا', 1.500),
(10, 'Mixed Veg', 'خضار مشكلة', 1.800),
(10, 'Aloo Gobi', 'ألو جوبي', 1.500),
(10, 'Egg Curry', 'كاري بيض', 1.800),
(10, 'Egg Masala', 'بيض ماسالا', 1.600),
(10, 'Egg Bhurji', 'بيض بورجي', 1.500);

-- ============================================
-- Roti Items (category_id = 11)
-- ============================================
INSERT INTO items (category_id, name_en, name_ar, price) VALUES
(11, 'Tandoori Roti', 'روتي تندوري', 0.150),
(11, 'Butter Naan', 'نان بالزبدة', 0.250),
(11, 'Garlic Naan', 'نان ثوم', 0.300),
(11, 'Cheese Naan', 'نان جبن', 0.350),
(11, 'Plain Naan', 'نان عادي', 0.200),
(11, 'Chapati', 'شباتي', 0.100),
(11, 'Paratha', 'باراثا', 0.200),
(11, 'Kulcha', 'كولشا', 0.250);

-- ============================================
-- Beverages Items (category_id = 12)
-- ============================================
INSERT INTO items (category_id, name_en, name_ar, price) VALUES
(12, 'Mango Lassi', 'لاسي مانجو', 0.800),
(12, 'Sweet Lassi', 'لاسي حلو', 0.600),
(12, 'Salt Lassi', 'لاسي ملح', 0.600),
(12, 'Masala Chai', 'شاي ماسالا', 0.300),
(12, 'Plain Chai', 'شاي عادي', 0.200),
(12, 'Coffee', 'قهوة', 0.400),
(12, 'Cold Coffee', 'قهوة باردة', 0.600),
(12, 'Fresh Juice', 'عصير طازج', 0.800),
(12, 'Soft Drink', 'مشروب غازي', 0.250),
(12, 'Mineral Water', 'مياه معدنية', 0.150);

-- ============================================
-- Chef's Recommendation Items (category_id = 13)
-- ============================================
INSERT INTO items (category_id, name_en, name_ar, price) VALUES
(13, 'Special Biryani', 'برياني خاص', 4.500),
(13, 'Chef Special Thali', 'ثالي خاص الشيف', 5.500),
(13, 'Royal Platter', 'طبق ملكي', 8.000),
(13, 'Lamb Kofta Curry', 'كاري كفتة خروف', 4.000),
(13, 'Hyderabadi Dum Biryani', 'برياني حيدر أبادي دم', 4.200);

-- ============================================
-- Update Company Settings for Bawarchi
-- ============================================
UPDATE company_settings 
SET company_name_en = 'Bawarchi Restaurant',
    company_name_ar = 'مطعم بوارتشي',
    address = 'Kuwait',
    phone = '+965 XXXX XXXX',
    email = 'info@bawarchi.com'
WHERE id = 1;
