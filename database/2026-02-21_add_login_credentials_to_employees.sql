-- ============================================================
-- Migration: Add login credentials (username + password) to employees
-- Date: 2026-02-21
-- Run in: phpMyAdmin (local Docker or cPanel live hosting)
-- ============================================================

ALTER TABLE `employees`
  ADD COLUMN `username` VARCHAR(100) NULL DEFAULT NULL
    COMMENT 'Login username for mobile app'
    AFTER `is_active`,
  ADD COLUMN `password` VARCHAR(255) NULL DEFAULT NULL
    COMMENT 'bcrypt-hashed login password for mobile app'
    AFTER `username`,
  ADD UNIQUE KEY `employees_username_unique` (`username`);

-- ============================================================
-- Optional: Set default credentials for existing employees
-- Username = emp0001, emp0002 ... (lowercase employee code)
-- Password = "password" (bcrypt hash of the word "password")
-- Admin MUST change these via the employee edit form or
-- POST /api/auth/set-password after going live!
-- ============================================================

UPDATE `employees`
SET
  `username` = LOWER(`employee_code`),
  `password` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uHxL7h4qy'
WHERE `username` IS NULL;

-- ============================================================
-- To ROLLBACK (undo this migration):
-- ALTER TABLE `employees`
--   DROP INDEX `employees_username_unique`,
--   DROP COLUMN `username`,
--   DROP COLUMN `password`;
-- ============================================================
