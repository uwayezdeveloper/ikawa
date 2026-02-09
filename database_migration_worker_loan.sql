-- Worker Loan Table Migration
-- This table stores loan requests from workers/employees

CREATE TABLE IF NOT EXISTS `tbl_worker_loan` (
  `l_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `user_id` int(11) NOT NULL COMMENT 'Foreign key to users table - ID of the user requesting the loan',
  `request_amount` int(11) NOT NULL COMMENT 'Amount requested by the worker',
  `payed_amount` int(11) NOT NULL DEFAULT 0 COMMENT 'Amount disbursed/paid to the worker',
  `description` text NOT NULL COMMENT 'Description/purpose of the loan request',
  `status` enum('pending','outstanding','disbursed') NOT NULL DEFAULT 'pending' COMMENT 'Status of the loan: pending (awaiting approval), outstanding (approved but not fully paid), disbursed (fully paid)',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When the loan request was created',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'When the loan record was last updated',
  PRIMARY KEY (`l_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_worker_loan_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Table to store worker loan requests and their status';

-- Sample data (optional - remove if not needed)
-- INSERT INTO `tbl_worker_loan` (`user_id`, `request_amount`, `payed_amount`, `description`, `status`) VALUES
-- (1, 50000, 0, 'Medical emergency - need funds for hospital bills', 'pending'),
-- (2, 100000, 50000, 'Home repair after storm damage', 'outstanding'),
-- (1, 75000, 75000, 'Education fees for professional certification', 'disbursed');

-- Add permissions for loan management (optional - add to permissions table if it exists)
-- INSERT INTO `permissions` (`name`, `description`, `group_name`) VALUES
-- ('view-worker-loans', 'View worker loan requests', 'Finance'),
-- ('manage-worker-loans', 'Manage worker loan requests (approve, update status, delete)', 'Finance');