-- Migration: Add version tracking and archival system to sop_formats
-- This migration adds support for format versioning and soft-archival

-- Add version column to track format versions (1.0, 2.0, 3.0, etc.)
ALTER TABLE `sop_formats` ADD COLUMN `version` VARCHAR(10) DEFAULT '1.0' AFTER `file_name`;

-- Add status column to track if format is active or archived
ALTER TABLE `sop_formats` ADD COLUMN `status` ENUM('active', 'archived') DEFAULT 'active' AFTER `version`;

-- Add format_number column to track format identifier (e.g., F-001, F-002, etc.)
ALTER TABLE `sop_formats` ADD COLUMN `format_number` VARCHAR(50) DEFAULT NULL AFTER `format_name`;

-- Ensure all existing records have the correct default values
UPDATE `sop_formats` SET `version` = '1.0', `status` = 'active' WHERE `version` IS NULL OR `status` IS NULL;

-- Optional: Create an index on (sop_id, status) for faster queries
ALTER TABLE `sop_formats` ADD INDEX `idx_sop_status` (`sop_id`, `status`);

-- Verify the changes
-- SELECT * FROM `sop_formats`;
