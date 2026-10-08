ALTER TABLE users ADD COLUMN password_is_set TINYINT(1) NOT NULL DEFAULT 1 AFTER password;
UPDATE users SET password_is_set = 0 WHERE google_sub IS NOT NULL;
