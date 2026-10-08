CREATE TABLE IF NOT EXISTS website_projects (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(80) NOT NULL UNIQUE,
  title VARCHAR(140) NOT NULL,
  category VARCHAR(80) NOT NULL,
  summary VARCHAR(500) NOT NULL,
  cover_image_path VARCHAR(255) NULL,
  demo_url VARCHAR(500) NULL,
  live_url VARCHAR(500) NULL,
  status ENUM('draft','published') NOT NULL DEFAULT 'draft',
  sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 100,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX website_projects_public (status,sort_order,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Initial demos. Future admin entries use the same table; drafts stay off the public page.
INSERT INTO website_projects (slug,title,category,summary,cover_image_path,demo_url,status,sort_order)
VALUES
  ('nileteck-marketplace','Nileteck Marketplace','Marketplace platform','A marketplace for discovering local products, exploring categories, and connecting with sellers across Africa.','images/portfolio/marketplace-demo.png','p/marketplace','published',10),
  ('nileteck-evoting','Nileteck E-Voting','Voting platform','An online voting experience for school and university elections, with clear ballots and live results.','images/portfolio/evoting-demo.png','@evoting','published',20)
ON DUPLICATE KEY UPDATE slug=VALUES(slug);
