-- แซ่บกลางซอย — MySQL Schema + Seed Data
-- วิธีใช้: import ไฟล์นี้ใน phpMyAdmin หรือ mysql -u root -p zab < install.sql

CREATE DATABASE IF NOT EXISTS zab CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE zab;

-- ─── Tables ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS restaurant_tables (
  id        VARCHAR(10)  PRIMARY KEY,
  zone      CHAR(1)      NOT NULL,
  seats     INT          NOT NULL DEFAULT 2,
  status    ENUM('available','active','preparing','billing') NOT NULL DEFAULT 'available',
  opened_at VARCHAR(10)  NULL,
  guests    INT          NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Categories ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS menu_categories (
  name       VARCHAR(100) PRIMARY KEY,
  sort_order INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Menu Items ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS menu_items (
  id          VARCHAR(100) PRIMARY KEY,
  name        VARCHAR(255) NOT NULL,
  description TEXT,
  price       DECIMAL(10,2) NOT NULL,
  category    VARCHAR(100) NOT NULL,
  tag         VARCHAR(20)  NULL,
  image       VARCHAR(500)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Orders ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS orders (
  id         VARCHAR(50) PRIMARY KEY,
  table_id   VARCHAR(10) NOT NULL,
  ordered_at VARCHAR(10),
  created_at BIGINT      NOT NULL,
  printed    TINYINT(1)  DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS order_items (
  id       INT AUTO_INCREMENT PRIMARY KEY,
  order_id VARCHAR(50)  NOT NULL,
  menu_id  VARCHAR(100),
  name     VARCHAR(255) NOT NULL,
  price    DECIMAL(10,2) NOT NULL,
  quantity INT          NOT NULL,
  note     TEXT         NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Bills ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS bills (
  id             VARCHAR(50) PRIMARY KEY,
  table_id       VARCHAR(10),
  closed_at      VARCHAR(10),
  closed_at_ms   BIGINT,
  subtotal       DECIMAL(10,2),
  vat_rate       DECIMAL(5,2),
  vat            DECIMAL(10,2),
  service_charge DECIMAL(5,2),
  service_amt    DECIMAL(10,2),
  total          DECIMAL(10,2),
  guests         INT          NULL,
  payment_method VARCHAR(20)  NULL,
  cash_received  DECIMAL(10,2) NULL,
  change_amt     DECIMAL(10,2) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bill_items (
  id       INT AUTO_INCREMENT PRIMARY KEY,
  bill_id  VARCHAR(50)  NOT NULL,
  menu_id  VARCHAR(100),
  name     VARCHAR(255) NOT NULL,
  price    DECIMAL(10,2) NOT NULL,
  quantity INT          NOT NULL,
  note     TEXT         NULL,
  FOREIGN KEY (bill_id) REFERENCES bills(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Settings ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS app_settings (
  key_name VARCHAR(100) PRIMARY KEY,
  value    TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ═══════════════════════════════════════════════════════════
--  SEED DATA
-- ═══════════════════════════════════════════════════════════

-- Tables (โต๊ะ)
INSERT IGNORE INTO restaurant_tables (id, zone, seats) VALUES
  ('A1','A',2),('A2','A',2),('A3','A',2),('A4','A',4),
  ('B1','B',4),('B2','B',4),('B3','B',4),('B4','B',4),
  ('C1','C',6),('C2','C',6),('C3','C',6),('C4','C',6),
  ('D1','D',2),('D2','D',2),('D3','D',4),('D4','D',4);

-- Categories (หมวดอาหาร)
INSERT IGNORE INTO menu_categories (name, sort_order) VALUES
  ('ยอดฮิต',1),('ทะเล',2),('ทานเล่น',3),('เนื้อสัตว์',4),
  ('ข้าว/เส้น',5),('เครื่องดื่ม',6);

-- Menu Items (เมนู)
INSERT IGNORE INTO menu_items (id, name, description, price, category, tag, image) VALUES
  ('tom-yum-goong','ต้มยำกุ้งน้ำข้น','กุ้งแม่น้ำ น้ำข้นหอมมัน เผ็ดกำลังดี',189,'ยอดฮิต','เผ็ด','https://placehold.co/160x160/FADADD/E12717?text=🍲'),
  ('moo-kratha','หมูกระทะรวมมิตร','หมูสไลซ์ หมึก กุ้ง ผักสด พร้อมน้ำจิ้ม',259,'ยอดฮิต','ฮิต','https://placehold.co/160x160/FAE0C8/C45C00?text=🥘'),
  ('seafood-set','ชุดซีฟู้ดแซ่บ','กุ้ง หอย ปลาหมึก เสิร์ฟพร้อมน้ำจิ้มซีฟู้ด',899,'ยอดฮิต','โปร','https://placehold.co/160x160/D0EAF8/0066AA?text=🦐'),
  ('tilapia-steam','ปลากะพงนึ่งมะนาว','ปลาสดนึ่งร้อน ราดน้ำมะนาวพริกสด',320,'ทะเล',NULL,'https://placehold.co/160x160/D5F0D5/2E7D32?text=🐟'),
  ('seafood-hotpot','หม้อไฟทะเล','รวมอาหารทะเลสดในน้ำซุปแซ่บ',459,'ทะเล',NULL,'https://placehold.co/160x160/FAE8D0/C45C00?text=🍜'),
  ('salt-fish','ปลาเผาเกลือ','ปลาสดเผาเกลือ หอมกรอบนอกนุ่มใน',380,'ทะเล',NULL,'https://placehold.co/160x160/F5F0DC/8D6E00?text=🐠'),
  ('grilled-shrimp','กุ้งเผา','กุ้งสดเผาไฟ เสิร์ฟพร้อมน้ำจิ้มซีฟู้ด',420,'ทะเล',NULL,'https://placehold.co/160x160/FADADD/E12717?text=🦞'),
  ('somtum','ส้มตำไทย','ส้มตำสูตรต้นตำรับ เผ็ดถึงใจ',80,'ทานเล่น','เผ็ด','https://placehold.co/160x160/EAF5DA/4CAF50?text=🥗'),
  ('stir-veg','ผัดผักรวมมิตร','ผักสดหลากหลาย ผัดน้ำมันหอย',120,'ทานเล่น',NULL,'https://placehold.co/160x160/E8F5E9/388E3C?text=🥦'),
  ('grilled-chicken','ไก่ย่างสมุนไพร','ไก่หมักน้ำมัน เครื่องเทศ ย่างหอมอบอุ่น',220,'เนื้อสัตว์',NULL,'https://placehold.co/160x160/FFF3CD/FF8F00?text=🍗'),
  ('crab-rice','ข้าวผัดปู','ข้าวผัดแห้งกับ เนื้อปูก้อน ไข่ไก่สด',189,'ข้าว/เส้น',NULL,'https://placehold.co/160x160/FEF9E0/F57F17?text=🍚'),
  ('shrimp-rice','ข้าวผัดกุ้ง','ข้าวผัดหอม กุ้งสด ไข่ดาวด้านบน',160,'ข้าว/เส้น',NULL,'https://placehold.co/160x160/FFF8E1/F9A825?text=🍛'),
  ('water-melon','น้ำมะนาวโซดา','มะนาวสด ซ่าเย็น เติมมิ้นต์โซดา',65,'เครื่องดื่ม',NULL,'https://placehold.co/160x160/E8F5E9/1B5E20?text=🍋'),
  ('cold-water','น้ำเปล่าเย็น','บรรจุขวด เย็นพร้อมน้ำแข็ง',35,'เครื่องดื่ม',NULL,'https://placehold.co/160x160/E3F2FD/1565C0?text=💧');

-- Settings
INSERT IGNORE INTO app_settings (key_name, value) VALUES
  ('restaurantName','แซ่บกลางซอย'),
  ('cuisine','อีสาน · ซีฟู้ด · หมูกระทะ'),
  ('openTime','11:00'),
  ('closeTime','22:00'),
  ('vatRate','7'),
  ('serviceCharge','0');
