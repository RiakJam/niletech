ALTER TABLE page_posts ADD COLUMN category VARCHAR(32) NOT NULL DEFAULT 'other' AFTER price, ADD COLUMN image_path VARCHAR(255) NULL AFTER category;
CREATE INDEX page_posts_category ON page_posts (category, status, created_at);
