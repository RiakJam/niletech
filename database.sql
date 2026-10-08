CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(254) NOT NULL UNIQUE,
  google_sub VARCHAR(255) NULL UNIQUE,
  country_code CHAR(2) NULL,
  region VARCHAR(100) NULL,
  locality VARCHAR(100) NULL,
  password VARCHAR(255) NOT NULL,
  password_is_set TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS entries (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  type ENUM('blog', 'event') NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  event_date DATETIME NULL,
  venue VARCHAR(255) NULL,
  status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX entries_user_id (user_id),
  CONSTRAINT entries_user_fk FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS creator_pages (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  section ENUM('blog', 'events', 'marketplace', 'ebook', 'elearning') NOT NULL,
  slug VARCHAR(60) NOT NULL,
  title VARCHAR(120) NOT NULL,
  description VARCHAR(500) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY creator_pages_section_slug (section, slug),
  INDEX creator_pages_user_id (user_id),
  CONSTRAINT creator_pages_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS page_posts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  page_id BIGINT UNSIGNED NOT NULL,
  slug VARCHAR(80) NOT NULL,
  title VARCHAR(160) NOT NULL,
  body TEXT NOT NULL,
  price VARCHAR(20) NULL,
  currency_code CHAR(3) NOT NULL DEFAULT 'KES',
  country_code CHAR(2) NULL,
  region VARCHAR(100) NULL,
  locality VARCHAR(100) NULL,
  category VARCHAR(32) NOT NULL DEFAULT 'other',
  image_path VARCHAR(255) NULL,
  event_date DATETIME NULL,
  venue VARCHAR(200) NULL,
  status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY page_posts_page_slug (page_id, slug),
  INDEX page_posts_public (page_id, status, created_at),
  INDEX page_posts_location (country_code, region, locality, status, created_at),
  CONSTRAINT page_posts_page_fk FOREIGN KEY (page_id) REFERENCES creator_pages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_images (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  product_id BIGINT UNSIGNED NOT NULL,
  image_path VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX product_images_order (product_id, id),
  CONSTRAINT product_images_product_fk FOREIGN KEY (product_id) REFERENCES page_posts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS forum_posts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(160) NOT NULL,
  body TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX forum_posts_created (created_at, id),
  CONSTRAINT forum_posts_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS forum_comments (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  post_id BIGINT UNSIGNED NOT NULL,
  parent_id BIGINT UNSIGNED NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  body TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX forum_comments_post (post_id, created_at, id),
  INDEX forum_comments_parent (parent_id),
  CONSTRAINT forum_comments_post_fk FOREIGN KEY (post_id) REFERENCES forum_posts(id) ON DELETE CASCADE,
  CONSTRAINT forum_comments_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT forum_comments_parent_fk FOREIGN KEY (parent_id) REFERENCES forum_comments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS forum_likes (
  post_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (post_id, user_id),
  INDEX forum_likes_user (user_id),
  CONSTRAINT forum_likes_post_fk FOREIGN KEY (post_id) REFERENCES forum_posts(id) ON DELETE CASCADE,
  CONSTRAINT forum_likes_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS forum_saves (
  post_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (post_id, user_id),
  INDEX forum_saves_user (user_id),
  CONSTRAINT forum_saves_post_fk FOREIGN KEY (post_id) REFERENCES forum_posts(id) ON DELETE CASCADE,
  CONSTRAINT forum_saves_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS forum_follows (
  follower_id BIGINT UNSIGNED NOT NULL,
  followed_id BIGINT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (follower_id, followed_id),
  INDEX forum_follows_followed (followed_id),
  CONSTRAINT forum_follows_follower_fk FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT forum_follows_followed_fk FOREIGN KEY (followed_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS forum_comment_likes (
  comment_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (comment_id,user_id),
  INDEX forum_comment_likes_user (user_id),
  CONSTRAINT forum_comment_likes_comment_fk FOREIGN KEY (comment_id) REFERENCES forum_comments(id) ON DELETE CASCADE,
  CONSTRAINT forum_comment_likes_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS business_domains (
  slug VARCHAR(60) NOT NULL PRIMARY KEY,
  page_id BIGINT UNSIGNED NOT NULL UNIQUE,
  CONSTRAINT business_domains_page_fk FOREIGN KEY (page_id) REFERENCES creator_pages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS email_contacts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  business_id BIGINT UNSIGNED NOT NULL,
  email VARCHAR(254) NOT NULL,
  unsubscribe_token CHAR(48) NOT NULL UNIQUE,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY email_contacts_business_email (business_id,email),
  CONSTRAINT email_contacts_business_fk FOREIGN KEY (business_id) REFERENCES creator_pages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS email_campaigns (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  business_id BIGINT UNSIGNED NOT NULL,
  subject VARCHAR(200) NOT NULL,
  body TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT email_campaigns_business_fk FOREIGN KEY (business_id) REFERENCES creator_pages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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

CREATE TABLE IF NOT EXISTS store_analytics_daily (
  page_id BIGINT UNSIGNED NOT NULL, event_day DATE NOT NULL, visits BIGINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (page_id, event_day),
  CONSTRAINT store_analytics_daily_page_fk FOREIGN KEY (page_id) REFERENCES creator_pages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS store_daily_visitors (
  page_id BIGINT UNSIGNED NOT NULL, visitor_id CHAR(32) NOT NULL, event_day DATE NOT NULL,
  PRIMARY KEY (page_id, visitor_id, event_day), INDEX store_daily_visitors_day (page_id, event_day),
  CONSTRAINT store_daily_visitors_page_fk FOREIGN KEY (page_id) REFERENCES creator_pages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_analytics_daily (
  product_id BIGINT UNSIGNED NOT NULL, event_day DATE NOT NULL,
  impressions BIGINT UNSIGNED NOT NULL DEFAULT 0, clicks BIGINT UNSIGNED NOT NULL DEFAULT 0, views BIGINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (product_id, event_day), INDEX product_analytics_daily_day (event_day),
  CONSTRAINT product_analytics_daily_product_fk FOREIGN KEY (product_id) REFERENCES page_posts(id) ON DELETE CASCADE
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
