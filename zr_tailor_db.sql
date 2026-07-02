-- =====================================================================
--  ZR CREATION — TAILOR FOR GENTS
--  Tailor Shop Management — MySQL / MariaDB Database
--  Engine: InnoDB   Charset: utf8mb4 (required for Urdu terms)
--  Tested on: MySQL 8.0+ / MariaDB 10.4+
-- ---------------------------------------------------------------------
--  RELATIONSHIPS (overview)
--    customers 1───* orders
--    orders    1───* order_garments ───* order_measurements
--    orders    *───* design_options  (via order_design_options)
--    orders    1───* payments
--    garment_types 1───* measurement_points
--    users     1───* orders (created_by)
--
--  NOTE: measurement value is stored as VARCHAR to keep the tailor
--        notation e.g. "20.1.4" (20 inches + 1/4 etc.) intact.
-- =====================================================================

SET NAMES utf8mb4;
SET time_zone = '+05:00';                 -- Pakistan Standard Time
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS order_design_options;
DROP TABLE IF EXISTS order_measurements;
DROP TABLE IF EXISTS order_garments;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS measurement_points;
DROP TABLE IF EXISTS design_options;
DROP TABLE IF EXISTS garment_types;
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- 1. USERS  (shop staff / login)
-- =====================================================================
CREATE TABLE users (
  id            INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  username      VARCHAR(50)    NOT NULL,
  full_name     VARCHAR(100)   NULL,
  password_hash VARCHAR(255)   NULL,                  -- store bcrypt/argon hash
  role          ENUM('admin','tailor','counter') NOT NULL DEFAULT 'counter',
  is_active     TINYINT(1)     NOT NULL DEFAULT 1,
  created_at    TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 2. CUSTOMERS
-- =====================================================================
CREATE TABLE customers (
  id         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  name       VARCHAR(120)  NOT NULL,
  phone      VARCHAR(20)   NULL,
  reference  VARCHAR(120)  NULL,                       -- S/O  (ولدیت / حوالہ)
  address    VARCHAR(255)  NULL,
  notes      VARCHAR(255)  NULL,
  created_at TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_customers_phone (phone),
  KEY idx_customers_name  (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 3. GARMENT TYPES  (Kameez / Waistcoat …)
-- =====================================================================
CREATE TABLE garment_types (
  id         TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  code       VARCHAR(30)  NOT NULL,
  name_en    VARCHAR(60)  NOT NULL,
  name_ur    VARCHAR(60)  NOT NULL,
  icon       VARCHAR(40)  NULL,                         -- UI icon id e.g. i-kameez
  sort_order TINYINT      NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_garment_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 4. MEASUREMENT POINTS  (the ناپ rows for each garment)
-- =====================================================================
CREATE TABLE measurement_points (
  id              SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  garment_type_id TINYINT UNSIGNED  NOT NULL,
  code            VARCHAR(40)  NOT NULL,
  name_en         VARCHAR(60)  NOT NULL,
  name_ur         VARCHAR(60)  NOT NULL,
  icon            VARCHAR(40)  NULL,                     -- UI icon id e.g. i-len
  sort_order      SMALLINT     NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_point (garment_type_id, code),
  CONSTRAINT fk_point_garment FOREIGN KEY (garment_type_id)
    REFERENCES garment_types (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 5. DESIGN OPTIONS  (stitching, cuff & kaaj, extras, collar/cuff style, buttons)
-- =====================================================================
CREATE TABLE design_options (
  id         SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  category   ENUM('stitch','cuff_kaaj','extra','style','button') NOT NULL,
  code       VARCHAR(40)  NOT NULL,
  name_en    VARCHAR(80)  NULL,
  name_ur    VARCHAR(80)  NOT NULL,
  icon       VARCHAR(40)  NULL,
  is_default TINYINT(1)   NOT NULL DEFAULT 0,            -- pre-ticked on new order
  sort_order SMALLINT     NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_option (category, code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 6. ORDERS  (one suit / booking)
-- =====================================================================
CREATE TABLE orders (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_no      VARCHAR(20)  NOT NULL,                   -- suit number e.g. 6617
  customer_id   INT UNSIGNED NOT NULL,
  booking_date  DATE         NULL,
  delivery_date DATE         NULL,
  quantity      SMALLINT     NOT NULL DEFAULT 1,
  price         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  colour_note   VARCHAR(150) NULL,                       -- "Write for colour"
  extra_notes   TEXT         NULL,                       -- extra design notes
  status        ENUM('pending','stitching','ready','delivered','returned','cancelled')
                NOT NULL DEFAULT 'pending',
  created_by    INT UNSIGNED NULL,
  created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_order_no (order_no),
  KEY idx_orders_customer (customer_id),
  KEY idx_orders_status   (status),
  KEY idx_orders_delivery (delivery_date),
  CONSTRAINT fk_order_customer FOREIGN KEY (customer_id)
    REFERENCES customers (id) ON DELETE RESTRICT,
  CONSTRAINT fk_order_user FOREIGN KEY (created_by)
    REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 7. ORDER GARMENTS  (which garments belong to an order)
-- =====================================================================
CREATE TABLE order_garments (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id        INT UNSIGNED NOT NULL,
  garment_type_id TINYINT UNSIGNED NOT NULL,
  quantity        SMALLINT NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uq_order_garment (order_id, garment_type_id),
  KEY idx_og_garment (garment_type_id),
  CONSTRAINT fk_og_order FOREIGN KEY (order_id)
    REFERENCES orders (id) ON DELETE CASCADE,
  CONSTRAINT fk_og_garment FOREIGN KEY (garment_type_id)
    REFERENCES garment_types (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 8. ORDER MEASUREMENTS  (value per point per garment of an order)
-- =====================================================================
CREATE TABLE order_measurements (
  id                   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_garment_id     INT UNSIGNED NOT NULL,
  measurement_point_id SMALLINT UNSIGNED NOT NULL,
  value                VARCHAR(20) NULL,                 -- keeps "20.1.4" notation
  PRIMARY KEY (id),
  UNIQUE KEY uq_meas (order_garment_id, measurement_point_id),
  KEY idx_meas_point (measurement_point_id),
  CONSTRAINT fk_meas_og FOREIGN KEY (order_garment_id)
    REFERENCES order_garments (id) ON DELETE CASCADE,
  CONSTRAINT fk_meas_point FOREIGN KEY (measurement_point_id)
    REFERENCES measurement_points (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 9. ORDER DESIGN OPTIONS  (selected checkboxes per order)
-- =====================================================================
CREATE TABLE order_design_options (
  order_id         INT UNSIGNED NOT NULL,
  design_option_id SMALLINT UNSIGNED NOT NULL,
  PRIMARY KEY (order_id, design_option_id),
  KEY idx_odo_option (design_option_id),
  CONSTRAINT fk_odo_order FOREIGN KEY (order_id)
    REFERENCES orders (id) ON DELETE CASCADE,
  CONSTRAINT fk_odo_option FOREIGN KEY (design_option_id)
    REFERENCES design_options (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 10. PAYMENTS  (advance / installments / balance)
-- =====================================================================
CREATE TABLE payments (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id   INT UNSIGNED NOT NULL,
  amount     DECIMAL(10,2) NOT NULL,
  kind       ENUM('advance','installment','balance','refund') NOT NULL DEFAULT 'advance',
  method     ENUM('cash','card','bank','easypaisa','jazzcash','other') NOT NULL DEFAULT 'cash',
  paid_on    DATE         NULL,
  note       VARCHAR(150) NULL,
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_pay_order (order_id),
  CONSTRAINT fk_pay_order FOREIGN KEY (order_id)
    REFERENCES orders (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================================
--  SEED DATA — LOOKUPS
-- =====================================================================

-- Garment types -------------------------------------------------------
INSERT INTO garment_types (id, code, name_en, name_ur, icon, sort_order) VALUES
  (1, 'kameez',    'Kameez (Suit)', 'قمیض',  'i-kameez', 1),
  (2, 'waistcoat', 'Waistcoat',     'واسکٹ', 'i-vest',   2);

-- Measurement points : KAMEEZ (garment 1) ----------------------------
-- (shalwar + pancha are kept with the suit, matching the worksheet)
INSERT INTO measurement_points (garment_type_id, code, name_en, name_ur, icon, sort_order) VALUES
  (1, 'length',   'Length',   'لمبائی', 'i-len',      1),
  (1, 'shoulder', 'Shoulder', 'تیرہ',   'i-shoulder', 2),
  (1, 'sleeve',   'Sleeve',   'بازو',   'i-sleeve',   3),
  (1, 'chest',    'Chest',    'چھاتی',  'i-chest',    4),
  (1, 'waist',    'Waist',    'کمر',    'i-waist',    5),
  (1, 'daman',    'Daman',    'دامن',   'i-daman',    6),
  (1, 'collar',   'Collar',   'کالر',   'i-collar',   7),
  (1, 'shalwar',  'Shalwar',  'شلوار',  'i-shalwar',  8),
  (1, 'pancha',   'Pancha',   'پانچہ',  'i-pancha',   9);

-- Measurement points : WAISTCOAT (garment 2) -------------------------
INSERT INTO measurement_points (garment_type_id, code, name_en, name_ur, icon, sort_order) VALUES
  (2, 'length',   'Length',   'لمبائی', 'i-len',      1),
  (2, 'shoulder', 'Shoulder', 'تیرہ',   'i-shoulder', 2),
  (2, 'sleeve',   'Sleeve',   'بازو',   'i-sleeve',   3),
  (2, 'chest',    'Chest',    'چھاتی',  'i-chest',    4),
  (2, 'waist',    'Waist',    'کمر',    'i-waist',    5),
  (2, 'bais',     'Bais',     'بیس',    'i-bais',     6),
  (2, 'collar',   'Collar',   'کالر',   'i-collar',   7);

-- Design options ------------------------------------------------------
INSERT INTO design_options (category, code, name_en, name_ur, icon, is_default, sort_order) VALUES
  -- Stitch type (سلائی کی قسم)
  ('stitch',    'silky_single', 'Silky thread single', 'سلکی تار سنگل', NULL, 0, 1),
  ('stitch',    'silky_double', 'Silky thread double', 'سلکی تار ڈبل',  NULL, 0, 2),
  ('stitch',    'chowka',       'Chowka stitch',       'چوکا سلائی',   NULL, 0, 3),
  ('stitch',    'double',       'Double stitch',       'ڈبل سلائی',    NULL, 0, 4),
  ('stitch',    'zanjeeri',     'Zanjeeri stitch',     'زنجیری سلائی', NULL, 1, 5),
  ('stitch',    'pair',         'Pair stitch',         'پیر سلائی',    NULL, 0, 6),
  -- Cuff & Kaaj (کف و کاج)
  ('cuff_kaaj', 'cuff_1pleat',  'Cuff one pleat',      'کف میں ایک پلیٹ', 'i-cuff', 1, 1),
  ('cuff_kaaj', 'cuff_nopleat', 'No cuff pleat',       'کف پلیٹ نہیں',    'i-cuff', 0, 2),
  ('cuff_kaaj', 'chaak_kaaj',   'Chaak butti kaaj',    'چاک بٹی کاج',     NULL,     1, 3),
  ('cuff_kaaj', 'kaaj_5',       '5 kaaj on butti',     'بٹی میں 5 کاج',   NULL,     1, 4),
  -- Extras (اضافی)
  ('extra',     'shalwar_pocket','Shalwar pocket',     'شلوار جیب',    NULL, 1, 1),
  ('extra',     'btn_from_shop', 'Buttons from shop',  'بٹن دکان سے',  NULL, 0, 2),
  ('extra',     'no_name',       'No name tag',        'نام نہیں',     NULL, 0, 3),
  ('extra',     'make_drawing',  'Make drawing',       'ڈرائنگ کرنا',  'i-pen', 0, 4),
  -- Collar & cuff style (کالر و کف ڈیزائن)
  ('style',     'khal_been',     'Khal been collar',   'خل بین',       'i-collar', 1, 1),
  ('style',     'half_cuff',     'Half cuff',          'ھاف کف',       'i-cuff',   0, 2),
  ('style',     'single_bais',   'Single bais',        'شنگل بیس',     'i-bais',   1, 3),
  ('style',     'round_side',    'Round side',         'گول سھے',      'i-daman',  0, 4),
  ('style',     'seedha',        'Straight',           'سیدھا',        'i-len',    1, 5),
  ('style',     'zail_patti',    'Zail patti',         'ذیل پٹی',      'i-vest',   0, 6),
  ('style',     'round_sleeve',  'Round sleeve',       'گول بازو',     'i-sleeve', 0, 7),
  ('style',     'cup_sleeve',    'Cup sleeve',         'کپ بازو',      'i-sleeve', 0, 8),
  -- Buttons (بٹن)
  ('button',    'double_chaak',  'Double chaak',       'ڈبل چاک',      NULL, 1, 1),
  ('button',    'two_button',    'Two buttons',        'دو بٹن',       NULL, 1, 2),
  ('button',    'three_button',  'Three buttons',      'تین بٹن',      NULL, 0, 3);


-- =====================================================================
--  SAMPLE DATA — one full order (matches the worksheet: Hamza Khan #6617)
-- =====================================================================

INSERT INTO users (id, username, full_name, role) VALUES
  (1, 'admin', 'Shop Admin', 'admin');

INSERT INTO customers (id, name, phone, reference) VALUES
  (1, 'Hamza Khan', '03100924747', NULL);

INSERT INTO orders (id, order_no, customer_id, booking_date, delivery_date, quantity, price, status, created_by)
VALUES
  (1, '6617', 1, '2026-06-19', '2026-06-20', 1, 0.00, 'pending', 1);

-- garments on this order: 1 = Kameez, 2 = Waistcoat
INSERT INTO order_garments (id, order_id, garment_type_id, quantity) VALUES
  (1, 1, 1, 1),
  (2, 1, 2, 1);

-- Kameez measurements (order_garment 1) -------------------------------
INSERT INTO order_measurements (order_garment_id, measurement_point_id, value) VALUES
  (1, (SELECT id FROM measurement_points WHERE garment_type_id=1 AND code='length'),   '43.5'),
  (1, (SELECT id FROM measurement_points WHERE garment_type_id=1 AND code='shoulder'), '20.1.4'),
  (1, (SELECT id FROM measurement_points WHERE garment_type_id=1 AND code='sleeve'),   '23.5'),
  (1, (SELECT id FROM measurement_points WHERE garment_type_id=1 AND code='chest'),    '38'),
  (1, (SELECT id FROM measurement_points WHERE garment_type_id=1 AND code='waist'),    '21.5'),
  (1, (SELECT id FROM measurement_points WHERE garment_type_id=1 AND code='daman'),    '22'),
  (1, (SELECT id FROM measurement_points WHERE garment_type_id=1 AND code='collar'),   '15.5'),
  (1, (SELECT id FROM measurement_points WHERE garment_type_id=1 AND code='shalwar'),  '38.5'),
  (1, (SELECT id FROM measurement_points WHERE garment_type_id=1 AND code='pancha'),   '7');

-- Waistcoat measurements (order_garment 2) ----------------------------
INSERT INTO order_measurements (order_garment_id, measurement_point_id, value) VALUES
  (2, (SELECT id FROM measurement_points WHERE garment_type_id=2 AND code='length'),   '28'),
  (2, (SELECT id FROM measurement_points WHERE garment_type_id=2 AND code='shoulder'), '19.1.4'),
  (2, (SELECT id FROM measurement_points WHERE garment_type_id=2 AND code='sleeve'),   '23.5'),
  (2, (SELECT id FROM measurement_points WHERE garment_type_id=2 AND code='chest'),    '38'),
  (2, (SELECT id FROM measurement_points WHERE garment_type_id=2 AND code='waist'),    '35'),
  (2, (SELECT id FROM measurement_points WHERE garment_type_id=2 AND code='bais'),     '39'),
  (2, (SELECT id FROM measurement_points WHERE garment_type_id=2 AND code='collar'),   '15.5');

-- selected design options for this order ------------------------------
INSERT INTO order_design_options (order_id, design_option_id)
SELECT 1, id FROM design_options
WHERE (category='stitch'    AND code='zanjeeri')
   OR (category='cuff_kaaj' AND code='cuff_1pleat')
   OR (category='cuff_kaaj' AND code='chaak_kaaj')
   OR (category='cuff_kaaj' AND code='kaaj_5')
   OR (category='extra'     AND code='shalwar_pocket')
   OR (category='button'    AND code='double_chaak')
   OR (category='button'    AND code='two_button');

-- a sample advance payment --------------------------------------------
INSERT INTO payments (order_id, amount, kind, method, paid_on, note) VALUES
  (1, 1000.00, 'advance', 'cash', '2026-06-19', 'Booking advance');


-- =====================================================================
--  HELPER VIEWS
-- =====================================================================

-- Order summary with customer + balance --------------------------------
CREATE OR REPLACE VIEW v_order_summary AS
SELECT
  o.id,
  o.order_no,
  c.name              AS customer_name,
  c.phone             AS customer_phone,
  o.booking_date,
  o.delivery_date,
  o.quantity,
  o.price,
  COALESCE(p.paid, 0)               AS total_paid,
  (o.price - COALESCE(p.paid, 0))   AS balance_due,
  o.status,
  o.colour_note
FROM orders o
JOIN customers c ON c.id = o.customer_id
LEFT JOIN (
  SELECT order_id,
         SUM(CASE WHEN kind='refund' THEN -amount ELSE amount END) AS paid
  FROM payments
  GROUP BY order_id
) p ON p.order_id = o.id;

-- Pending deliveries (not yet delivered/returned/cancelled) -----------
CREATE OR REPLACE VIEW v_pending_deliveries AS
SELECT order_no, customer_name, customer_phone, delivery_date, balance_due, status
FROM v_order_summary
WHERE status IN ('pending','stitching','ready')
ORDER BY delivery_date ASC;

-- Full measurement sheet for an order (long format) -------------------
CREATE OR REPLACE VIEW v_order_measurements AS
SELECT
  o.order_no,
  g.name_en  AS garment_en,
  g.name_ur  AS garment_ur,
  mp.name_en AS point_en,
  mp.name_ur AS point_ur,
  om.value
FROM order_measurements om
JOIN order_garments og     ON og.id = om.order_garment_id
JOIN orders o              ON o.id  = og.order_id
JOIN garment_types g       ON g.id  = og.garment_type_id
JOIN measurement_points mp ON mp.id = om.measurement_point_id
ORDER BY o.order_no, g.sort_order, mp.sort_order;

-- =====================================================================
--  QUICK TEST QUERIES (run after import)
-- ---------------------------------------------------------------------
--  SELECT * FROM v_order_summary;
--  SELECT * FROM v_pending_deliveries;
--  SELECT * FROM v_order_measurements WHERE order_no = '6617';
--  SELECT do.name_ur FROM order_design_options odo
--    JOIN design_options do ON do.id = odo.design_option_id
--    WHERE odo.order_id = 1;
-- =====================================================================
