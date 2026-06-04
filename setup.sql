-- ============================================================
--  แซ่บกลางซอย · Full Database Setup
--  รันไฟล์นี้ใน phpMyAdmin หรือ MySQL CLI เพื่อตั้งค่า DB
--  ใช้ INSERT IGNORE → รันซ้ำได้โดยไม่ error
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- ────────────────────────────────────────────────────────────
--  1. app_settings
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `app_settings` (
  `key_name` varchar(100) NOT NULL,
  `value`    text DEFAULT NULL,
  PRIMARY KEY (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `app_settings` (`key_name`, `value`) VALUES
('restaurantName', 'แซ่บกลางซอย'),
('cuisine',        'อีสาน · ซีฟู้ด · หมูกระทะ'),
('openTime',       '11:00'),
('closeTime',      '22:00'),
('vatRate',        '7'),
('serviceCharge',  '0'),
('promptPayQr',    'https://img2.pic.in.th/image0579923c74d6b95e6.jpg');

-- ────────────────────────────────────────────────────────────
--  2. menu_categories
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `menu_categories` (
  `name`       varchar(100) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `menu_categories` (`name`, `sort_order`) VALUES
('ยอดฮิต',    1),
('ทะเล',      2),
('ทานเล่น',   3),
('เนื้อสัตว์', 4),
('ข้าว/เส้น', 5),
('เครื่องดื่ม', 6);

-- ────────────────────────────────────────────────────────────
--  3. menu_items
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `menu_items` (
  `id`          varchar(100) NOT NULL,
  `name`        varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price`       decimal(10,2) NOT NULL,
  `category`    varchar(100) NOT NULL,
  `tag`         varchar(20) DEFAULT NULL,
  `image`       varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `menu_items` (`id`, `name`, `description`, `price`, `category`, `tag`, `image`) VALUES
-- ยอดฮิต
('menu-1780487481033', 'เเจ่วฮ้อนรวมหมู',                   '',                       299.00, 'ยอดฮิต', NULL, 'https://img1.pic.in.th/images/714826228_874603195670360_1500764760018646176_n.jpg'),
('menu-1780487691790', 'เเจ่วฮ้อนรวมเนื้อ',                  '',                       399.00, 'ยอดฮิต', NULL, ''),
('menu-1780487749030', 'เเจ่วฮ้อนรวมหมู+ทะเล',               '',                       399.00, 'ยอดฮิต', NULL, ''),
('menu-1780488100371', 'เเจ่วฮ้อนรวมเนื้อ+ทะเล',             '',                       399.00, 'ยอดฮิต', NULL, ''),
('menu-1780488494996', 'ชุดผักรวม',                           '',                        30.00, 'ยอดฮิต', NULL, ''),
('menu-1780488535388', 'ลวกจิ้มเเจ่วหมู',                    '',                       120.00, 'ยอดฮิต', NULL, ''),
('menu-1780488573908', 'ลวกจิ้มเเจ่วเนื้อ',                   '',                       150.00, 'ยอดฮิต', NULL, ''),
('menu-1780488637016', 'ลวกจิ้มเเจ่วทะเล',                   '',                       150.00, 'ยอดฮิต', NULL, ''),
('menu-1780488843512', 'แกงอ่อมหมู',                          '',                       100.00, 'ยอดฮิต', NULL, ''),
('menu-1780488896910', 'แกงอ่อมเนื้อ',                        '',                       150.00, 'ยอดฮิต', NULL, ''),
('menu-1780488930927', 'ลาบหมู',                              '',                       100.00, 'ยอดฮิต', NULL, ''),
('menu-1780488950636', 'ลาบเนื้อ',                            '',                       120.00, 'ยอดฮิต', NULL, ''),
('menu-1780488973306', 'ลาบปลาดุก',                          '',                       120.00, 'ยอดฮิต', NULL, ''),
('menu-1780489003632', 'น้ำตกหมู',                            '',                       120.00, 'ยอดฮิต', NULL, ''),
('menu-1780489277243', 'ก้อยหมู-สุก-ขม',                     '',                       100.00, 'ยอดฮิต', NULL, ''),
('menu-1780489302865', 'ก้อยหมู-สุก-เปรี้ยว',                '',                       100.00, 'ยอดฮิต', NULL, ''),
('menu-1780489321734', 'ก้อยหมู-ดิบ-ขม',                     '',                       100.00, 'ยอดฮิต', NULL, ''),
('menu-1780489340143', 'ก้อยหมู-ดิบ-เปรี้ยว',                '',                       100.00, 'ยอดฮิต', NULL, ''),
('menu-1780489355731', 'ก้อยเนื้อ-สุก-ขม',                   '',                       120.00, 'ยอดฮิต', NULL, ''),
('menu-1780489376306', 'ก้อยเนื้อ-สุก-เปรี้ยว',              '',                       120.00, 'ยอดฮิต', NULL, ''),
('menu-1780489398048', 'ก้อยเนื้อ-ดิบ-ขม',                   '',                       120.00, 'ยอดฮิต', NULL, ''),
('menu-1780489421579', 'ก้อยเนื้อ-ดิบ-เปรี้ยว',              '',                       120.00, 'ยอดฮิต', NULL, ''),
('menu-1780489438375', 'ตับหวาน',                             '',                       100.00, 'ยอดฮิต', NULL, ''),
('menu-1780489477523', 'ต้มเเซ่บกระดูกอ่อน หมู',             '',                       120.00, 'ยอดฮิต', NULL, ''),
('menu-1780489514949', 'ตีนไก่ซุปเปอร์',                     '',                       120.00, 'ยอดฮิต', NULL, ''),
('menu-1780489602526', 'ตำปูปลาร้า',                         '',                        50.00, 'ยอดฮิต', NULL, ''),
('menu-1780489616942', 'ตำซั่ว',                              '',                        50.00, 'ยอดฮิต', NULL, ''),
('menu-1780489640562', 'ตำปู',                                '',                        50.00, 'ยอดฮิต', NULL, ''),
('menu-1780489650594', 'ตำไทย',                               '',                        50.00, 'ยอดฮิต', NULL, ''),
('menu-1780489667453', 'ตำถั่ว',                              '',                        50.00, 'ยอดฮิต', NULL, ''),
('menu-1780489676369', 'ตำเเตง',                              '',                        50.00, 'ยอดฮิต', NULL, ''),
('menu-1780489709028', 'ตำไทย ไข่เค็ม',                      '',                        70.00, 'ยอดฮิต', NULL, ''),
('menu-1780489731694', 'ตำข้าวโพด ไข่เค็ม',                  '',                        70.00, 'ยอดฮิต', NULL, ''),
('menu-1780489754507', 'ตำตีนไก่ ใส่ปลาร้า',                 '',                        70.00, 'ยอดฮิต', NULL, ''),
('menu-1780489789814', 'ตำทะเล ไทย',                         '',                       100.00, 'ยอดฮิต', NULL, ''),
('menu-1780489805554', 'ตำทะเล ปลาร้า',                      '',                       100.00, 'ยอดฮิต', NULL, ''),
('menu-1780489834887', 'ตำเหลา ไหลบัว กุ้ง+หมึก',            '',                       120.00, 'ยอดฮิต', NULL, ''),
('menu-1780489883003', 'ตำไหลบัว กุ้งสุก',                   '',                       100.00, 'ยอดฮิต', NULL, ''),
('menu-1780489896686', 'ตำไหลบัว กุ้งสด',                    '',                       100.00, 'ยอดฮิต', NULL, ''),
('menu-1780489943474', 'ตำแซ่บกลางซอย (ใส่ทุกอย่าง)',        'ใส่ทุกอย่างกินให้ตุย',   250.00, 'ยอดฮิต', NULL, ''),
('menu-1780490174817', 'สะโพกไก่ ย่าง',                      '',                        80.00, 'ยอดฮิต', NULL, ''),
('menu-1780490420522', 'ยำวุ้นเส้น โบราณ',                   '',                       100.00, 'ยอดฮิต', NULL, ''),
('menu-1780490444357', 'ยำเล็บมือนาง',                       '',                       100.00, 'ยอดฮิต', NULL, ''),
('menu-1780490460640', 'ยำม่าม่า หมูสับ',                    '',                       100.00, 'ยอดฮิต', NULL, ''),
('menu-1780490502472', 'ยำหมูยอ',                             '',                       100.00, 'ยอดฮิต', NULL, ''),
('menu-1780490521075', 'ยำไข่ดาว',                            '',                       100.00, 'ยอดฮิต', NULL, ''),
('menu-1780490552071', 'ยำทะเล',                              '',                       120.00, 'ยอดฮิต', NULL, ''),
('menu-1780490576551', 'ต้มม่าม่า แซ่บกลางซอย',              '',                       120.00, 'ยอดฮิต', NULL, ''),
('menu-1780493107319', 'ไข่ดาว',                              '',                        10.00, 'ยอดฮิต', NULL, ''),
('menu-1780493117663', 'ไข่เจียว',                            '',                        20.00, 'ยอดฮิต', NULL, ''),
('menu-1780493179330', 'ไข่เจียวหมูสับ',                     '',                        30.00, 'ยอดฮิต', NULL, ''),
-- เนื้อสัตว์
('menu-1780488137775', 'สามชั้นไสล์',                         '',                       100.00, 'เนื้อสัตว์', NULL, ''),
('menu-1780488157012', 'ตับหมู',                              '',                       100.00, 'เนื้อสัตว์', NULL, ''),
('menu-1780488245203', 'เนื้อหมู',                            '',                       100.00, 'เนื้อสัตว์', NULL, ''),
('menu-1780488374727', 'เนื้อวัว',                            '',                       100.00, 'เนื้อสัตว์', NULL, ''),
('menu-1780488408859', 'สันคอไลด์',                          '',                       100.00, 'เนื้อสัตว์', NULL, ''),
('menu-1780488426068', 'ตับวัว',                              '',                       100.00, 'เนื้อสัตว์', NULL, ''),
('menu-1780488441137', 'สไบนาง',                              '',                       100.00, 'เนื้อสัตว์', NULL, ''),
-- ทะเล
('menu-1780488462563', 'กุ้ง',                                '',                       100.00, 'ทะเล', NULL, ''),
('menu-1780488472551', 'หมึก',                                '',                       100.00, 'ทะเล', NULL, ''),
-- ทานเล่น
('menu-1780490041390', 'เฟรนฟราย',                           '',                       100.00, 'ทานเล่น', NULL, ''),
('menu-1780490055583', 'นักเก็ต',                             '',                       100.00, 'ทานเล่น', NULL, ''),
('menu-1780490076167', 'ปีกไก่ทอด',                          '',                       100.00, 'ทานเล่น', NULL, ''),
('menu-1780490098402', 'หมูแดดเดียว',                        '',                       100.00, 'ทานเล่น', NULL, ''),
('menu-1780490118923', 'เนื้อเเดดเดียว',                     '',                       120.00, 'ทานเล่น', NULL, ''),
('menu-1780490137363', 'คอหมูย่าง',                          '',                       100.00, 'ทานเล่น', NULL, ''),
('menu-1780490226947', 'ปลาดุก ย่าง',                        '',                        80.00, 'ทานเล่น', NULL, ''),
('menu-1780490255421', 'กุ้งเเช่น้ำปลา',                     '',                       120.00, 'ทานเล่น', NULL, ''),
('menu-1780490363640', 'หมูมะนาว',                           '',                       100.00, 'ทานเล่น', NULL, ''),
-- ข้าว/เส้น
('menu-1780489531843', 'ข้าวสวย',                            '',                        15.00, 'ข้าว/เส้น', NULL, ''),
('menu-1780489544507', 'ข้าวเหนียว',                         '',                        15.00, 'ข้าว/เส้น', NULL, ''),
('menu-1780489555659', 'ขนมจีน',                             '',                        15.00, 'ข้าว/เส้น', NULL, ''),
('menu-1780489995226', 'กากหมู',                             '',                        20.00, 'ข้าว/เส้น', NULL, ''),
('menu-1780491201003', 'ข้าวราดไข่เจียว',                    '',                        50.00, 'ข้าว/เส้น', NULL, ''),
('menu-1780491220994', 'ข้าวราดไข่เจียว หมูสับ',             '',                        60.00, 'ข้าว/เส้น', NULL, ''),
('menu-1780491305786', 'ข้าวผัดหมู',                         '',                        60.00, 'ข้าว/เส้น', NULL, ''),
('menu-1780491332084', 'ข้าวผัดหมู ใหญ่',                    '',                       120.00, 'ข้าว/เส้น', NULL, ''),
('menu-1780491350535', 'ข้าวผัดไก่',                         '',                        60.00, 'ข้าว/เส้น', NULL, ''),
('menu-1780491366788', 'ข้าวผัดไก่ ใหญ่',                    '',                       120.00, 'ข้าว/เส้น', NULL, ''),
('menu-1780491406937', 'ข้าวผัดเนื้อ',                       '',                        70.00, 'ข้าว/เส้น', NULL, ''),
('menu-1780491437683', 'ข้าวผัดกุ้ง',                        '',                        70.00, 'ข้าว/เส้น', NULL, ''),
('menu-1780491452717', 'ข้าวผัดหมึก',                        '',                        70.00, 'ข้าว/เส้น', NULL, ''),
('menu-1780491467331', 'ข้าวผัดทะเล',                        '',                        70.00, 'ข้าว/เส้น', NULL, ''),
('menu-1780491548836', 'ข้าวผัดทะเล ใหญ่',                   '',                       150.00, 'ข้าว/เส้น', NULL, ''),
('menu-1780491589364', 'ข้าวราดกะเพรา ไก่',                  '',                        60.00, 'ข้าว/เส้น', NULL, ''),
('menu-1780491609275', 'ข้าวราดกะเพรา หมู',                  '',                        60.00, 'ข้าว/เส้น', NULL, ''),
('menu-1780491682515', 'ข้าวราดกะเพรา เนื้อ',                '',                        70.00, 'ข้าว/เส้น', NULL, ''),
('menu-1780491750979', 'ข้าวราดกะเพรา กุ้ง',                 '',                        70.00, 'ข้าว/เส้น', NULL, ''),
('menu-1780491773760', 'ข้าวราดกะเพรา หมึก',                 '',                        70.00, 'ข้าว/เส้น', NULL, ''),
('menu-1780491838418', 'ข้าวราดกะเพรา ทะเล',                 '',                        70.00, 'ข้าว/เส้น', NULL, ''),
('menu-1780493025547', 'ข้าวราดผัดพริกเเกงไก่',              '',                        60.00, 'ข้าว/เส้น', NULL, ''),
('menu-1780493061911', 'ข้าวราดผัดพริกเเกง หมู',             '',                        60.00, 'ข้าว/เส้น', NULL, ''),
-- เครื่องดื่ม
('menu-1780490593700', 'น้ำเปล่า',                           '',                        10.00, 'เครื่องดื่ม', NULL, ''),
('menu-1780490748723', 'เบียร์สิงห์',                        '',                        90.00, 'เครื่องดื่ม', NULL, ''),
('menu-1780490769052', 'เบียร์ลีโอ',                         '',                        80.00, 'เครื่องดื่ม', NULL, ''),
('menu-1780490786459', 'เบียร์ช้าง',                         '',                        80.00, 'เครื่องดื่ม', NULL, ''),
('menu-1780490892999', 'รีเจนซี่ แบน เเช่เย็นๆ',            '',                       490.00, 'เครื่องดื่ม', NULL, ''),
('menu-1780490920551', 'หงส์ แบน เเช่เย็นๆ',                '',                       290.00, 'เครื่องดื่ม', NULL, ''),
('menu-1780490951105', 'โค้ก',                               '',                        20.00, 'เครื่องดื่ม', NULL, ''),
('menu-1780490962721', 'แดง',                                '',                        20.00, 'เครื่องดื่ม', NULL, ''),
('menu-1780490972782', 'เขียว',                              '',                        20.00, 'เครื่องดื่ม', NULL, ''),
('menu-1780491003108', 'สิงห์เลม่อนโซดา',                   '',                        25.00, 'เครื่องดื่ม', NULL, '');

-- ────────────────────────────────────────────────────────────
--  4. menu_options  (ตาราง NEW — สร้างด้วย PHP ใหม่)
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `menu_options` (
  `id`         varchar(64)  NOT NULL,
  `menu_id`    varchar(64)  NOT NULL,
  `group_name` varchar(100) NOT NULL,
  `opt_name`   varchar(100) NOT NULL,
  `price`      decimal(8,2) NOT NULL DEFAULT 0,
  `required`   tinyint(1)   NOT NULL DEFAULT 0,
  `sort_order` smallint     NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `idx_mo_menu` (`menu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ────────────────────────────────────────────────────────────
--  5. restaurant_tables
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `restaurant_tables` (
  `id`        varchar(10) NOT NULL,
  `zone`      char(1)     NOT NULL,
  `seats`     int(11)     NOT NULL DEFAULT 2,
  `status`    enum('available','active','preparing','billing') NOT NULL DEFAULT 'available',
  `opened_at` varchar(10) DEFAULT NULL,
  `guests`    int(11)     DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `restaurant_tables` (`id`, `zone`, `seats`, `status`, `opened_at`, `guests`) VALUES
('แอร์1', 'A', 4, 'available', NULL, NULL),
('แอร์2', 'A', 4, 'available', NULL, NULL),
('แอร์3', 'A', 4, 'available', NULL, NULL),
('แอร์4', 'A', 4, 'available', NULL, NULL),
('ไม้1',  'B', 4, 'available', NULL, NULL),
('ไม้2',  'B', 4, 'available', NULL, NULL),
('ไม้3',  'B', 4, 'available', NULL, NULL),
('CAMP1', 'C', 4, 'available', NULL, NULL),
('CAMP2', 'C', 4, 'available', NULL, NULL),
('CAMP3', 'C', 4, 'available', NULL, NULL),
('CAMP4', 'C', 4, 'available', NULL, NULL),
('CAMP5', 'C', 4, 'available', NULL, NULL),
('เสริม', 'D', 4, 'available', NULL, NULL);

-- ────────────────────────────────────────────────────────────
--  6. orders + order_items
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `orders` (
  `id`         varchar(50) NOT NULL,
  `table_id`   varchar(10) NOT NULL,
  `ordered_at` varchar(10) DEFAULT NULL,
  `created_at` bigint(20)  NOT NULL,
  `printed`    tinyint(1)  DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `order_items` (
  `id`       int(11)      NOT NULL AUTO_INCREMENT,
  `order_id` varchar(50)  NOT NULL,
  `menu_id`  varchar(100) DEFAULT NULL,
  `name`     varchar(255) NOT NULL,
  `price`    decimal(10,2) NOT NULL,
  `quantity` int(11)      NOT NULL,
  `note`     text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1`
  FOREIGN KEY IF NOT EXISTS (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

-- ────────────────────────────────────────────────────────────
--  7. bills + bill_items
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `bills` (
  `id`             varchar(50)   NOT NULL,
  `table_id`       varchar(10)   DEFAULT NULL,
  `closed_at`      varchar(10)   DEFAULT NULL,
  `closed_at_ms`   bigint(20)    DEFAULT NULL,
  `subtotal`       decimal(10,2) DEFAULT NULL,
  `vat_rate`       decimal(5,2)  DEFAULT NULL,
  `vat`            decimal(10,2) DEFAULT NULL,
  `service_charge` decimal(5,2)  DEFAULT NULL,
  `service_amt`    decimal(10,2) DEFAULT NULL,
  `total`          decimal(10,2) DEFAULT NULL,
  `guests`         int(11)       DEFAULT NULL,
  `payment_method` varchar(20)   DEFAULT NULL,
  `cash_received`  decimal(10,2) DEFAULT NULL,
  `change_amt`     decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `bill_items` (
  `id`       int(11)      NOT NULL AUTO_INCREMENT,
  `bill_id`  varchar(50)  NOT NULL,
  `menu_id`  varchar(100) DEFAULT NULL,
  `name`     varchar(255) NOT NULL,
  `price`    decimal(10,2) NOT NULL,
  `quantity` int(11)      NOT NULL,
  `note`     text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bill_id` (`bill_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `bill_items`
  ADD CONSTRAINT `bill_items_ibfk_1`
  FOREIGN KEY IF NOT EXISTS (`bill_id`) REFERENCES `bills` (`id`) ON DELETE CASCADE;

-- ประวัติบิลจริง
INSERT IGNORE INTO `bills`
  (`id`, `table_id`, `closed_at`, `closed_at_ms`, `subtotal`, `vat_rate`, `vat`, `service_charge`, `service_amt`, `total`, `guests`, `payment_method`, `cash_received`, `change_amt`)
VALUES
('BILL-1780493891405', 'CAMP3', '20:38', 1780493891405, 1447.00, 7.00, 101.00, 0.00, 0.00, 1548.00, 1, 'transfer', NULL, NULL),
('BILL-1780502104666', 'CAMP2', '22:55', 1780502104666, 1097.00, 7.00,  77.00, 0.00, 0.00, 1174.00, 1, 'transfer', NULL, NULL);

INSERT IGNORE INTO `bill_items` (`bill_id`, `menu_id`, `name`, `price`, `quantity`, `note`) VALUES
('BILL-1780493891405', 'menu-1780487481033', 'เเจ่วฮ้อนรวมหมู',             299.00, 1, NULL),
('BILL-1780493891405', 'menu-1780487691790', 'เเจ่วฮ้อนรวมเนื้อ',            399.00, 1, NULL),
('BILL-1780493891405', 'menu-1780488100371', 'เเจ่วฮ้อนรวมเนื้อ+ทะเล',      399.00, 1, NULL),
('BILL-1780493891405', 'menu-1780488843512', 'แกงอ่อมหมู',                   100.00, 1, NULL),
('BILL-1780493891405', 'menu-1780489943474', 'ตำแซ่บกลางซอย (ใส่ทุกอย่าง)', 250.00, 1, NULL),
('BILL-1780502104666', 'menu-1780487691790', 'เเจ่วฮ้อนรวมเนื้อ',            399.00, 1, NULL),
('BILL-1780502104666', 'menu-1780487481033', 'เเจ่วฮ้อนรวมหมู',             299.00, 1, NULL),
('BILL-1780502104666', 'menu-1780487749030', 'เเจ่วฮ้อนรวมหมู+ทะเล',        399.00, 1, NULL);

-- ────────────────────────────────────────────────────────────
SET FOREIGN_KEY_CHECKS = 1;
COMMIT;

-- ============================================================
--  ✅ Setup เสร็จสิ้น
--  ทำต่อ: ตั้งค่า db.php ให้ตรงกับ MySQL ของคุณ
--         DB_HOST / DB_USER / DB_PASS / DB_NAME
-- ============================================================
