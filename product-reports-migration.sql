CREATE TABLE IF NOT EXISTS product_reports (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  product_id BIGINT UNSIGNED NOT NULL,
  reason TEXT NOT NULL,
  visitor_id CHAR(32) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX product_reports_rate (visitor_id, created_at),
  INDEX product_reports_product (product_id, created_at),
  CONSTRAINT product_reports_product_fk FOREIGN KEY (product_id) REFERENCES page_posts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
