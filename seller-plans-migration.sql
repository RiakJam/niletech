-- Run once after database.sql. Existing published listings remain published.
ALTER TABLE page_posts MODIFY status ENUM('draft','pending','published','rejected') NOT NULL DEFAULT 'draft';

CREATE TABLE IF NOT EXISTS seller_plan_catalog (
  tier ENUM('basic','premium','platinum') NOT NULL PRIMARY KEY,
  amount_subunit INT UNSIGNED NOT NULL,
  currency_code CHAR(3) NOT NULL DEFAULT 'KES',
  paystack_plan_code VARCHAR(64) NULL UNIQUE,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS seller_plan_checkouts (
  reference VARCHAR(80) NOT NULL PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  tier ENUM('basic','premium','platinum') NOT NULL,
  amount_subunit INT UNSIGNED NOT NULL,
  currency_code CHAR(3) NOT NULL,
  paystack_plan_code VARCHAR(64) NOT NULL,
  status ENUM('pending','paid') NOT NULL DEFAULT 'pending',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  paid_at DATETIME NULL,
  INDEX seller_plan_checkouts_user (user_id, created_at),
  CONSTRAINT seller_plan_checkouts_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS seller_memberships (
  user_id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
  tier ENUM('basic','premium','platinum') NOT NULL,
  paystack_plan_code VARCHAR(64) NOT NULL,
  paystack_subscription_code VARCHAR(64) NULL,
  paystack_email_token VARCHAR(128) NULL,
  paystack_customer_code VARCHAR(64) NULL,
  current_period_end DATETIME NOT NULL,
  auto_renew TINYINT(1) NOT NULL DEFAULT 1,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY seller_memberships_subscription (paystack_subscription_code),
  CONSTRAINT seller_memberships_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS seller_plan_charges (
  reference VARCHAR(80) NOT NULL PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  amount_subunit INT UNSIGNED NOT NULL,
  currency_code CHAR(3) NOT NULL,
  paid_at DATETIME NOT NULL,
  INDEX seller_plan_charges_user (user_id, paid_at),
  CONSTRAINT seller_plan_charges_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS seller_plan_subscription_links (
  subscription_code VARCHAR(64) NOT NULL PRIMARY KEY,
  customer_email VARCHAR(254) NOT NULL,
  plan_code VARCHAR(64) NOT NULL,
  email_token VARCHAR(128) NULL,
  customer_code VARCHAR(64) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX seller_plan_links_lookup (customer_email,plan_code,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
