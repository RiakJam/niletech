CREATE TABLE IF NOT EXISTS store_contact_settings (
  page_id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
  whatsapp_phone VARCHAR(24) NULL,
  call_phone VARCHAR(24) NULL,
  CONSTRAINT store_contact_page_fk FOREIGN KEY (page_id) REFERENCES creator_pages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS store_analytics (
  page_id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
  visits BIGINT UNSIGNED NOT NULL DEFAULT 0,
  CONSTRAINT store_analytics_page_fk FOREIGN KEY (page_id) REFERENCES creator_pages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS store_visitors (
  page_id BIGINT UNSIGNED NOT NULL,
  visitor_id CHAR(32) NOT NULL,
  first_seen DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_seen DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (page_id, visitor_id),
  CONSTRAINT store_visitors_page_fk FOREIGN KEY (page_id) REFERENCES creator_pages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_analytics (
  product_id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
  impressions BIGINT UNSIGNED NOT NULL DEFAULT 0,
  clicks BIGINT UNSIGNED NOT NULL DEFAULT 0,
  views BIGINT UNSIGNED NOT NULL DEFAULT 0,
  CONSTRAINT product_analytics_product_fk FOREIGN KEY (product_id) REFERENCES page_posts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_impressions (
  product_id BIGINT UNSIGNED NOT NULL,
  visitor_id CHAR(32) NOT NULL,
  event_day DATE NOT NULL,
  PRIMARY KEY (product_id, visitor_id, event_day),
  CONSTRAINT product_impressions_product_fk FOREIGN KEY (product_id) REFERENCES page_posts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS store_messages (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  page_id BIGINT UNSIGNED NOT NULL,
  product_id BIGINT UNSIGNED NULL,
  sender_name VARCHAR(120) NOT NULL,
  sender_email VARCHAR(254) NOT NULL,
  body TEXT NOT NULL,
  visitor_id CHAR(32) NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX store_messages_inbox (page_id, is_read, created_at),
  INDEX store_messages_rate (page_id, visitor_id, created_at),
  CONSTRAINT store_messages_page_fk FOREIGN KEY (page_id) REFERENCES creator_pages(id) ON DELETE CASCADE,
  CONSTRAINT store_messages_product_fk FOREIGN KEY (product_id) REFERENCES page_posts(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
