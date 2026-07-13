-- =====================================================================
-- FILE: database/seed.sql
-- PROJECT: Lumiere Cookies
-- DESKRIPSI: Data awal/contoh agar fitur bisa langsung didemonstrasikan
-- Login admin default -> username: admin | password: admin123
-- =====================================================================
USE lumiere_cookies;

-- Admin (password = "admin123", di-hash dengan password_hash / bcrypt)
INSERT INTO admins (name, username, password, role) VALUES
('Friza Admin', 'admin', '$2y$10$5/A08x1URPUtKPCde0MaMOplq0zxTTsyQIsw.8afzAZhBjGf8KHgC', 'superadmin');

-- WhatsApp (tidak di-hardcode; diambil dari tabel)
INSERT INTO whatsapp_numbers (label, number, is_active, is_primary) VALUES
('Friza', '6287878130970', 1, 1),
('Naila', '6281274072060', 1, 0);

-- Settings situs
INSERT INTO settings (setting_key, setting_value) VALUES
('store_name', 'Lumiere Cookies'),
('tagline', 'Freshly Baked Happiness'),
('instagram', '@cookieslumiere_'),
('instagram_url', 'https://instagram.com/cookieslumiere_'),
('address', 'Jl. Merapi 2 Cengklik RT 03 RW 19, Nusukan, Banjarsari, Surakarta'),
('maps_embed', 'https://www.google.com/maps?q=-7.5498306,110.8278841&z=17&output=embed'),
('maps_link', 'https://www.google.com/maps?q=-7.5498306,110.8278841&z=17&hl=id'),
('about', 'Lumiere Cookies adalah UMKM rumahan asal Surakarta yang menghadirkan kukis, hampers, dan dessert box yang dipanggang segar setiap hari. Berdiri sejak 2025, kami percaya setiap gigitan membawa kebahagiaan kecil.'),
('vision', 'Menjadi UMKM kukis rumahan kebanggaan Surakarta yang dikenal karena kualitas dan kehangatannya.'),
('mission', 'Menyajikan kudapan berbahan premium, melayani dengan tulus, dan tumbuh bersama pelanggan.'),
('shipping_cost', '5000');

-- Kategori
INSERT INTO categories (name, slug, description) VALUES
('Cookies', 'cookies', 'Kukis renyah & lembut aneka rasa'),
('Hampers', 'hampers', 'Paket hadiah cantik untuk orang tersayang'),
('Dessert Box', 'dessert-box', 'Dessert box manis dalam kemasan praktis');

-- Produk (FK ke categories: 1=Cookies, 2=Hampers, 3=Dessert Box)
INSERT INTO products (category_id, name, slug, description, price, stock, image, badge, is_best_seller, is_active) VALUES
(1, 'Choco Chunk Cookies', 'choco-chunk-cookies', 'Kukis cokelat dengan potongan dark chocolate melimpah, renyah di luar lembut di dalam.', 28000, 40, NULL, 'best_seller', 1, 1),
(1, 'Red Velvet Cookies', 'red-velvet-cookies', 'Kukis red velvet dengan white chocolate yang lumer di mulut.', 30000, 35, NULL, 'new', 1, 1),
(1, 'Matcha White Cookies', 'matcha-white-cookies', 'Perpaduan matcha premium dan white chocolate yang seimbang.', 32000, 25, NULL, 'none', 0, 1),
(1, 'Double Choco Oreo', 'double-choco-oreo', 'Cokelat ganda dengan remahan biskuit oreo di setiap gigitan.', 30000, 30, NULL, 'limited', 0, 1),
(1, 'Cornflakes Almond', 'cornflakes-almond', 'Kukis renyah bertabur cornflakes dan almond panggang.', 27000, 20, NULL, 'none', 0, 1),
(2, 'Hampers Lebaran Klasik', 'hampers-lebaran-klasik', 'Paket 3 toples kukis pilihan dalam kemasan hampers elegan.', 145000, 15, NULL, 'best_seller', 1, 1),
(2, 'Hampers Mini Sweet', 'hampers-mini-sweet', 'Hampers mungil isi 2 toples, cocok untuk hadiah ucapan.', 95000, 18, NULL, 'none', 0, 1),
(2, 'Hampers Premium Gold', 'hampers-premium-gold', 'Paket premium 5 varian kukis + kartu ucapan custom.', 235000, 8, NULL, 'limited', 0, 1),
(3, 'Choco Lava Dessert Box', 'choco-lava-dessert-box', 'Dessert box cokelat dengan lelehan lava manis.', 38000, 22, NULL, 'best_seller', 1, 1),
(3, 'Tiramisu Dessert Box', 'tiramisu-dessert-box', 'Tiramisu lembut bertabur bubuk kakao premium.', 40000, 16, NULL, 'new', 0, 1),
(3, 'Biscoff Dessert Box', 'biscoff-dessert-box', 'Dessert box dengan krim biscoff dan remahan lotus.', 42000, 0, NULL, 'pre_order', 0, 1);

-- Testimoni
INSERT INTO testimonials (customer_name, content, rating, is_active) VALUES
('Dina A.', 'Choco chunk-nya juara! Renyah dan cokelatnya banyak. Pasti repeat order.', 5, 1),
('Rama P.', 'Pesan hampers buat lebaran, packaging-nya cantik banget. Recommended!', 5, 1),
('Sinta W.', 'Dessert box tiramisu-nya lembut, manisnya pas. Pengiriman cepat.', 4, 1),
('Bayu K.', 'Pelayanan ramah, kukisnya fresh. Worth it harganya.', 5, 1);

-- FAQ
INSERT INTO faqs (question, answer, sort_order, is_active) VALUES
('Apakah produk bisa dikirim ke luar kota?', 'Untuk saat ini kami melayani pengiriman area Surakarta dan sekitarnya, serta ambil di tempat. Hubungi kami via WhatsApp untuk pengiriman khusus.', 1, 1),
('Berapa lama kukis bisa bertahan?', 'Kukis kami tanpa pengawet, idealnya dikonsumsi dalam 2-3 minggu dan disimpan di wadah kedap udara.', 2, 1),
('Apakah menerima pesanan custom hampers?', 'Tentu! Kami menerima custom isi dan kartu ucapan. Silakan chat WhatsApp kami.', 3, 1),
('Bagaimana cara memesan?', 'Tambahkan produk ke keranjang, lakukan checkout, lalu pesanan otomatis terkirim ke WhatsApp kami untuk konfirmasi pembayaran.', 4, 1);

-- Artikel / Blog
INSERT INTO articles (title, slug, excerpt, content, image, is_published) VALUES
('Rahasia Kukis Renyah Tahan Lama', 'rahasia-kukis-renyah', 'Tips menyimpan kukis agar tetap renyah berminggu-minggu.', 'Menyimpan kukis dengan benar adalah kunci. Gunakan wadah kedap udara, jauhkan dari kelembapan, dan tambahkan sepotong roti tawar untuk menjaga tekstur. Di Lumiere Cookies, setiap batch dipanggang segar agar Anda mendapatkan kerenyahan terbaik.', NULL, 1),
('Ide Hampers untuk Setiap Momen', 'ide-hampers-setiap-momen', 'Bingung memberi hadiah? Hampers kukis solusinya.', 'Dari ulang tahun, lebaran, hingga ucapan terima kasih, hampers kukis selalu jadi pilihan hangat. Lumiere Cookies menyediakan berbagai paket yang bisa dikustom sesuai momen spesial Anda.', NULL, 1),
('Mengenal Bahan Premium Kami', 'bahan-premium-kami', 'Kualitas rasa berasal dari kualitas bahan.', 'Kami menggunakan cokelat couverture, butter berkualitas, dan bahan pilihan lainnya. Komitmen pada bahan premium inilah yang membuat setiap gigitan terasa istimewa.', NULL, 1);

-- Galeri
INSERT INTO gallery (title, image) VALUES
('Proses Memanggang', NULL),
('Display Toko', NULL),
('Hampers Siap Kirim', NULL),
('Varian Cookies', NULL);

-- Contoh order (untuk demo dashboard & statistik)
INSERT INTO orders (invoice_code, customer_name, customer_phone, delivery_method, address, maps_link, notes, subtotal, shipping_cost, total, status, created_at) VALUES
('INV-20260520-0001', 'Dina A.', '6281200000001', 'delivery', 'Jl. Slamet Riyadi No.10, Solo', 'https://maps.app.goo.gl/exampleDina', 'Tolong dibungkus rapi', 86000, 5000, 91000, 'done', '2026-05-20 10:15:00'),
('INV-20260524-0002', 'Rama P.', '6281200000002', 'pickup', NULL, NULL, NULL, 145000, 0, 145000, 'done', '2026-05-24 13:40:00'),
('INV-20260528-0003', 'Sinta W.', '6281200000003', 'delivery', 'Jl. Adi Sucipto No.5, Solo', 'https://maps.app.goo.gl/exampleSinta', NULL, 70000, 5000, 75000, 'processing', '2026-05-28 09:05:00'),
('INV-20260601-0004', 'Bayu K.', '6281200000004', 'pickup', NULL, NULL, 'Ambil sore', 38000, 0, 38000, 'pending', '2026-06-01 16:20:00');

INSERT INTO order_items (order_id, product_id, product_name, price, qty, subtotal) VALUES
(1, 1, 'Choco Chunk Cookies', 28000, 2, 56000),
(1, 2, 'Red Velvet Cookies', 30000, 1, 30000),
(2, 6, 'Hampers Lebaran Klasik', 145000, 1, 145000),
(3, 9, 'Choco Lava Dessert Box', 38000, 1, 38000),
(3, 3, 'Matcha White Cookies', 32000, 1, 32000),
(4, 9, 'Choco Lava Dessert Box', 38000, 1, 38000);
