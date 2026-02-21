-- ============================================================
-- Migration: Add allow_anywhere_attendance flag to employees
-- Date: 2026-02-21
-- Run in: phpMyAdmin (local Docker or cPanel live hosting)
-- ============================================================

ALTER TABLE `employees`
  ADD COLUMN `allow_anywhere_attendance` TINYINT(1) NOT NULL DEFAULT '0'
  COMMENT 'If 1, employee can mark attendance anywhere without geofence flagged warning'
  AFTER `is_active`;

-- ============================================================
-- To ROLLBACK (undo this migration):
-- ALTER TABLE `employees` DROP COLUMN `allow_anywhere_attendance`;
-- ============================================================
