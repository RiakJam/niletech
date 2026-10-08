ALTER TABLE users
  ADD COLUMN country_code CHAR(2) NULL AFTER email,
  ADD COLUMN region VARCHAR(100) NULL AFTER country_code,
  ADD COLUMN locality VARCHAR(100) NULL AFTER region;

ALTER TABLE page_posts
  ADD COLUMN currency_code CHAR(3) NOT NULL DEFAULT 'KES' AFTER price,
  ADD COLUMN country_code CHAR(2) NULL AFTER currency_code,
  ADD COLUMN region VARCHAR(100) NULL AFTER country_code,
  ADD COLUMN locality VARCHAR(100) NULL AFTER region;

CREATE INDEX page_posts_location ON page_posts (country_code, region, locality, status, created_at);
