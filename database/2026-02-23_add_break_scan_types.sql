-- ============================================================
--  Migration: Add break scan types to attendance table
--  Date: 2026-02-23
--  Description:
--    1. Extends the `type` ENUM to include break_start / break_end
--    2. Adds a `scan_label` VARCHAR column for human-readable labels
-- ============================================================

-- Step 1: Extend the ENUM for the `type` column
ALTER TABLE `attendance`
    MODIFY COLUMN `type`
        ENUM('check_in', 'break_start', 'break_end', 'check_out')
        NOT NULL DEFAULT 'check_in';

-- Step 2: Add scan_label column (stores display text like "Shift Start", "Break End", etc.)
ALTER TABLE `attendance`
    ADD COLUMN `scan_label` VARCHAR(50) NULL DEFAULT NULL
        AFTER `type`;

-- ============================================================
--  How the scan cycle works after this migration:
--
--  Scan #1 in a day  → check_in    (Shift Start)   e.g. 10:00 AM
--  Scan #2 in a day  → break_start (Break Start)   e.g.  1:00 PM
--  Scan #3 in a day  → break_end   (Break End)     e.g.  1:45 PM
--  Scan #4 in a day  → check_out   (Shift End)     e.g.  6:30 PM
--
--  Net work hours = (check_out - check_in) - (break_end - break_start)
--  For the example above: (6:30 - 10:00) - (1:45 - 1:00) = 8.5h - 0.75h = 7.75h
-- ============================================================
