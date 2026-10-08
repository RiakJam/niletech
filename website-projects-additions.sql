-- Nileteck client websites. Safe to run more than once.
INSERT INTO website_projects (slug,title,category,summary,cover_image_path,live_url,status,sort_order)
VALUES
  ('educomrade','Educomrade','Education platform','A fundraising platform that helps students in Kenya raise support for school fees and essential needs.','images/portfolio/educomrade.png','https://educomrade.com/','published',30),
  ('savanna-roots','Savanna Roots','Online store','A storefront for authentic Maasai crafts, connecting Kenyan artisans with shoppers abroad.','images/portfolio/savanna-roots.png','https://savannaroots.africa/','published',40),
  ('afro-bio-market','Afro Bio Market','Online marketplace','An online food store featuring fish from East Africa and other African groceries for customers in Canada.','images/portfolio/afro-bio-market.png','https://afrobiomarketonline.com/','published',50)
ON DUPLICATE KEY UPDATE
  title=VALUES(title),category=VALUES(category),summary=VALUES(summary),
  cover_image_path=VALUES(cover_image_path),live_url=VALUES(live_url),
  status=VALUES(status),sort_order=VALUES(sort_order);
