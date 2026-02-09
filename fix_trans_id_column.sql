-- Fix trans_id column size
-- Run this to expand the trans_id column to accommodate longer transaction IDs

ALTER TABLE `tbl_expenseconsume` MODIFY COLUMN `trans_id` varchar(25) NULL COMMENT 'Transaction ID - expanded to 25 chars';

-- Update any existing truncated transaction IDs if needed
-- (Optional - only if you have existing data with truncated IDs)