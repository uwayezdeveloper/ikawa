<?php
namespace Models;

require_once __DIR__ . '/../config/Database.php';

use Config\Database;
use PDO;

class Investment
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /**
     * Create a recharge record with pending status (sts=1)
     * Balance will be added only when approved (sts=2)
     * @return true|string  true on success, error message on failure
     */
    public function createInvestment($in_amount, $done_by, $account_id, $loc_id, $description = null, $reciept = null, $source = null, $action = 'recharge')
    {
        try {
            $this->conn->beginTransaction();

            // Ensure account exists and lock row
            $check = $this->conn->prepare('SELECT acc_id FROM tbl_accounts WHERE acc_id = ? FOR UPDATE');
            $check->execute([$account_id]);
            $acc = $check->fetch(\PDO::FETCH_ASSOC);
            if (!$acc) {
                $this->conn->rollBack();
                return 'Account not found';
            }

            // Insert into tbl_investiments with sts=1 (pending approval)
            $sql = "INSERT INTO tbl_investiments (in_amount, done_by, account_id, loc_id, description, reciept, source, action, sts) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                (int)$in_amount,
                (int)$done_by,
                (int)$account_id,
                (int)$loc_id,
                $description,
                $reciept,
                $source,
                $action
            ]);

            // DO NOT update account balance here - will be done on approval

            $this->conn->commit();
            return true;
        } catch (\Exception $e) {
            try { $this->conn->rollBack(); } catch (\Exception $_) {}
            return $e->getMessage();
        }
    }

    /**
     * Simple fetch by location for UI listing (optional)
     */
    public function getInvestmentsByLocation($loc_id)
    {
        $sql = "SELECT i.*, a.acc_name, u.username, l.location_name FROM tbl_investiments i LEFT JOIN tbl_accounts a ON i.account_id = a.acc_id LEFT JOIN tbl_users u ON i.done_by = u.user_id LEFT JOIN tbl_location l ON i.loc_id = l.loc_id WHERE i.loc_id = ? ORDER BY i.done_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$loc_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get pending investments (sts=1) for approval
     */
    public function getPendingInvestments()
    {
        $sql = "SELECT i.*, 
                       a.acc_name, 
                       u.username, 
                       l.location_name
                FROM tbl_investiments i 
                LEFT JOIN tbl_accounts a ON i.account_id = a.acc_id 
                LEFT JOIN tbl_users u ON i.done_by = u.user_id 
                LEFT JOIN tbl_location l ON i.loc_id = l.loc_id 
                WHERE i.sts = 1 AND i.rejectorComment IS NULL
                ORDER BY i.done_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get rejected investments (sts=3) by user
     */
    public function getRejectedInvestmentsByUser($user_id)
    {
        $sql = "SELECT i.*, 
                       a.acc_name, 
                       l.location_name
                FROM tbl_investiments i 
                LEFT JOIN tbl_accounts a ON i.account_id = a.acc_id 
                LEFT JOIN tbl_location l ON i.loc_id = l.loc_id 
                WHERE i.sts = 1 AND i.rejectorComment IS NOT NULL AND i.done_by = ? 
                ORDER BY i.done_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$user_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Approve investment (sts=2) and add to account balance
     */
    public function approveInvestment($in_id, $approved_by)
    {
        try {
            $this->conn->beginTransaction();

            // Get investment details - check sts=1 AND rejectorComment IS NULL (not rejected)
            $sql = "SELECT * FROM tbl_investiments WHERE in_id = ? AND sts = 1 AND rejectorComment IS NULL FOR UPDATE";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([(int)$in_id]);
            $investment = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$investment) {
                $this->conn->rollBack();
                return 'Investment not found or already processed';
            }

            // Update investment status to approved (sts=2)
            $sql2 = "UPDATE tbl_investiments SET sts = 2 WHERE in_id = ?";
            $stmt2 = $this->conn->prepare($sql2);
            $stmt2->execute([(int)$in_id]);

            // Add amount to account balance
            $sql3 = "UPDATE tbl_accounts SET balance = balance + ? WHERE acc_id = ?";
            $stmt3 = $this->conn->prepare($sql3);
            $stmt3->execute([(int)$investment['in_amount'], (int)$investment['account_id']]);

            $this->conn->commit();
            return true;
        } catch (\Exception $e) {
            try { $this->conn->rollBack(); } catch (\Exception $_) {}
            return $e->getMessage();
        }
    }

    /**
     * Reject investment (sts=1 with rejector comment)
     */
    public function rejectInvestment($in_id, $rejected_by, $rejector_comment)
    {
        try {
            $sql = "UPDATE tbl_investiments SET sts = 1, rejectorComment = ? WHERE in_id = ? AND sts = 1";
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([$rejector_comment, (int)$in_id]);

            if ($stmt->rowCount() === 0) {
                return 'Investment not found or already processed';
            }

            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
