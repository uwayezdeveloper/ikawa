-- Account Recharge Tables Migration
-- Run this script to create/update the recharge history table

-- Create tbl_recharge_history table if it doesn't exist
CREATE TABLE IF NOT EXISTS `tbl_recharge_history` (
    `rech_id` int(11) NOT NULL AUTO_INCREMENT,
    `acc_id` int(11) NOT NULL,
    `amount` decimal(15,2) NOT NULL,
    `due_date` timestamp(6) NULL DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`rech_id`),
    KEY `idx_acc_id` (`acc_id`),
    KEY `idx_due_date` (`due_date`),
    KEY `idx_created_at` (`created_at`),
    CONSTRAINT `fk_recharge_account` FOREIGN KEY (`acc_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add index on amount for better performance on queries
CREATE INDEX IF NOT EXISTS `idx_amount` ON `tbl_recharge_history` (`amount`);

-- Add composite index for account and date queries
CREATE INDEX IF NOT EXISTS `idx_acc_date` ON `tbl_recharge_history` (`acc_id`, `created_at`);

-- Make sure accounts table has the correct structure (if it needs updates)
-- Note: This assumes the accounts table already exists from your description

-- Insert sample data for testing (optional - remove in production)
-- INSERT INTO `tbl_recharge_history` (`acc_id`, `amount`, `due_date`) 
-- VALUES 
--     (1, 100.00, DATE_ADD(NOW(), INTERVAL 30 DAY)),
--     (1, 250.50, DATE_ADD(NOW(), INTERVAL 25 DAY)),
--     (2, 500.00, DATE_ADD(NOW(), INTERVAL 20 DAY));

COMMIT;