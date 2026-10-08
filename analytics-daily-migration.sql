CREATE TABLE IF NOT EXISTS store_analytics_daily (
  page_id BIGINT UNSIGNED NOT NULL,
  event_day DATE NOT NULL,
  visits BIGINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (page_id, event_day),
  CONSTRAINT store_analytics_daily_page_fk FOREIGN KEY (page_id) REFERENCES creator_pages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS store_daily_visitors (
  page_id BIGINT UNSIGNED NOT NULL,
  visitor_id CHAR(32) NOT NULL,
  event_day DATE NOT NULL,
  PRIMARY KEY (page_id, visitor_id, event_day),
  INDEX store_daily_visitors_day (page_id, event_day),
  CONSTRAINT store_daily_visitors_page_fk FOREIGN KEY (page_id) REFERENCES creator_pages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_analytics_daily (
  product_id BIGINT UNSIGNED NOT NULL,
  event_day DATE NOT NULL,
  impressions BIGINT UNSIGNED NOT NULL DEFAULT 0,
  clicks BIGINT UNSIGNED NOT NULL DEFAULT 0,
  views BIGINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (product_id, event_day),
  INDEX product_analytics_daily_day (event_day),
  CONSTRAINT product_analytics_daily_product_fk FOREIGN KEY (product_id) REFERENCES page_posts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
