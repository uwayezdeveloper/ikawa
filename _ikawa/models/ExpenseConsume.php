<?php
namespace Models;

require_once __DIR__ . '/../config/Database.php';

use Config\Database;
use PDO;
use \PDOException;

class ExpenseConsume
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /**
     * Get next transaction ID (exptr1, exptr2, etc.)
     */
    public function getNextTransId()
    {
        try {
            $query = 'SELECT trans_id FROM tbl_expenseconsume 
                      WHERE trans_id IS NOT NULL 
                      ORDER BY con_id DESC LIMIT 1';
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($result && $result['trans_id']) {
                // Extract number from exptr1, exptr2, etc.
                $lastNumber = intval(str_replace('exptr', '', $result['trans_id']));
                return 'exptr' . ($lastNumber + 1);
            }
            
            return 'exptr1';
        } catch (\PDOException $e) {
            error_log("Error getting next trans_id: " . $e->getMessage());
            return 'exptr1';
        }
    }

    public function getAllExpenseConsumes()
    {
        try {
            $query = 'SELECT ec.*, 
                      e.expense_name,
                      l.location_name as st_name,
                      l.description as st_location,
                      a.acc_name as payment_mode_name,
                      cons.cons_name as consumer_name,
                      cons.phone as consumer_phone
                      FROM tbl_expenseconsume ec
                      LEFT JOIN tbl_expenses e ON ec.expense_id = e.expense_id
                      LEFT JOIN tbl_location l ON ec.station_id = l.loc_id
                      LEFT JOIN tbl_accounts a ON ec.pay_mode = a.acc_id
                      LEFT JOIN tbl_expenseconsumer cons ON ec.payer_name = cons.cons_id
                      WHERE ec.status = 1 
                      ORDER BY ec.con_id DESC';
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching expense consumes: " . $e->getMessage());
            return false;
        }
    }

    public function getExpenseConsumeById($con_id)
    {
        try {
            $query = 'SELECT ec.*, 
                      e.expense_name,
                      l.location_name as st_name,
                      l.description as st_location,
                      a.acc_name as payment_mode_name,
                      cons.cons_name as consumer_name,
                      cons.phone as consumer_phone
                      FROM tbl_expenseconsume ec
                      LEFT JOIN tbl_expenses e ON ec.expense_id = e.expense_id
                      LEFT JOIN tbl_location l ON ec.station_id = l.loc_id
                      LEFT JOIN tbl_accounts a ON ec.pay_mode = a.acc_id
                      LEFT JOIN tbl_expenseconsumer cons ON ec.payer_name = cons.cons_id
                      WHERE ec.con_id = :con_id';
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['con_id' => $con_id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching expense consume by ID: " . $e->getMessage());
            return false;
        }
    }

    public function createExpenseConsume($data)
    {
        try {
            $query = 'INSERT INTO tbl_expenseconsume 
                      (expense_id, co_id, station_id, amount, pay_mode, trans_id, payer_name, description, recorded_date, receipt_type, status) 
                      VALUES 
                      (:expense_id, :co_id, :station_id, :amount, :pay_mode, :trans_id, :payer_name, :description, :recorded_date, :receipt_type, 1)';
            
            $stmt = $this->conn->prepare($query);
            $success = $stmt->execute([
                'expense_id' => $data['expense_id'],
                'co_id' => 1, // Always 1 as per requirement
                'station_id' => $data['station_id'],
                'amount' => $data['amount'],
                'pay_mode' => $data['pay_mode'],
                'trans_id' => $data['trans_id'] ?? null,
                'payer_name' => $data['payer_name'] ?? null,
                'description' => $data['description'] ?? null,
                'recorded_date' => $data['recorded_date'],
                'receipt_type' => $data['receipt_type'] ?? null
            ]);

            // Return the last inserted con_id instead of just success
            if ($success) {
                return $this->conn->lastInsertId();
            }
            return false;
        } catch (\PDOException $e) {
            error_log("Error creating expense consume: " . $e->getMessage());
            return false;
        }
    }

    public function updateExpenseConsume($data)
    {
        try {
            $query = 'UPDATE tbl_expenseconsume 
                      SET expense_id = :expense_id,
                          station_id = :station_id,
                          amount = :amount,
                          pay_mode = :pay_mode,
                          payer_name = :payer_name,
                          description = :description,
                          recorded_date = :recorded_date,
                          receipt_type = :receipt_type
                      WHERE con_id = :con_id';
            
            $stmt = $this->conn->prepare($query);
            $success = $stmt->execute([
                'expense_id' => $data['expense_id'],
                'station_id' => $data['station_id'],
                'amount' => $data['amount'],
                'pay_mode' => $data['pay_mode'],
                'payer_name' => $data['payer_name'] ?? null,
                'description' => $data['description'] ?? null,
                'recorded_date' => $data['recorded_date'],
                'receipt_type' => $data['receipt_type'] ?? null,
                'con_id' => $data['con_id']
            ]);

            return $success;
        } catch (\PDOException $e) {
            error_log("Error updating expense consume: " . $e->getMessage());
            return false;
        }
    }

    public function deleteExpenseConsume($con_id)
    {
        try {
            // Soft delete - set status to 0
            $query = 'UPDATE tbl_expenseconsume SET status = 0 WHERE con_id = :con_id';
            $stmt = $this->conn->prepare($query);
            return $stmt->execute(['con_id' => $con_id]);
        } catch (\PDOException $e) {
            error_log("Error deleting expense consume: " . $e->getMessage());
            return false;
        }
    }

    public function cancelExpenseConsume($con_id)
    {
        try {
            // Set status to 11 (cancelled)
            $query = 'UPDATE tbl_expenseconsume SET status = 11 WHERE con_id = :con_id';
            $stmt = $this->conn->prepare($query);
            return $stmt->execute(['con_id' => $con_id]);
        } catch (\PDOException $e) {
            error_log("Error cancelling expense consume: " . $e->getMessage());
            return false;
        }
    }

    public function getTotalExpensesByPeriod($start_date, $end_date)
    {
        try {
            $query = 'SELECT SUM(amount) as total 
                      FROM tbl_expenseconsume 
                      WHERE status = 1 
                      AND recorded_date BETWEEN :start_date AND :end_date';
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['start_date' => $start_date, 'end_date' => $end_date]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (\PDOException $e) {
            error_log("Error getting total expenses: " . $e->getMessage());
            return false;
        }
    }

    public function getExpensesByStation($station_id)
    {
        try {
            $query = 'SELECT ec.*, e.expense_name
                      FROM tbl_expenseconsume ec
                      LEFT JOIN tbl_expenses e ON ec.expense_id = e.expense_id
                      WHERE ec.station_id = :station_id AND ec.status = 1
                      ORDER BY ec.recorded_date DESC';
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['station_id' => $station_id]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching expenses by station: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get expense statement with filters
     * Excludes status 11 (canceled) in consume and canceled in journal entries
     * Retrieves charges from journal entries matching reference_id
     */
    public function getExpenseStatement($filters = [])
    {
        try {
            $query = 'SELECT 
                        ec.con_id,
                        ec.recorded_date,
                        ec.pay_date,
                        ec.amount,
                        ec.description,
                        e.expense_name,
                        l.location_name as st_name,
                        a.acc_name as payment_mode_name,
                        cons.cons_name as consumer_name,
                        cons.phone as consumer_phone,
                        COALESCE(
                            (SELECT SUM(je.charges) 
                             FROM tbl_journal_entries je 
                             WHERE je.reference_id = ec.con_id 
                             AND je.action != "canceled"
                             AND je.charges > 0
                            ), 0
                        ) as charges
                      FROM tbl_expenseconsume ec
                      LEFT JOIN tbl_expenses e ON ec.expense_id = e.expense_id
                      LEFT JOIN tbl_location l ON ec.station_id = l.loc_id
                      LEFT JOIN tbl_accounts a ON ec.pay_mode = a.acc_id
                      LEFT JOIN tbl_expenseconsumer cons ON ec.payer_name = cons.cons_id
                      WHERE ec.status != 11';

            $params = [];

            // Filter by consumer
            if (!empty($filters['consumer_id'])) {
                $query .= ' AND ec.payer_name = :consumer_id';
                $params['consumer_id'] = $filters['consumer_id'];
            }

            // Filter by expense type
            if (!empty($filters['expense_id'])) {
                $query .= ' AND ec.expense_id = :expense_id';
                $params['expense_id'] = $filters['expense_id'];
            }

            // Filter by receipt type
            if (!empty($filters['receipt_type'])) {
                $query .= ' AND ec.receipt_type = :receipt_type';
                $params['receipt_type'] = $filters['receipt_type'];
            }

            // Filter by date range using pay_date (or recorded_date if pay_date is null)
            if (!empty($filters['date_from'])) {
                $query .= ' AND DATE(COALESCE(ec.pay_date, ec.recorded_date)) >= :date_from';
                $params['date_from'] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $query .= ' AND DATE(COALESCE(ec.pay_date, ec.recorded_date)) <= :date_to';
                $params['date_to'] = $filters['date_to'];
            }

            $query .= ' ORDER BY COALESCE(ec.pay_date, ec.recorded_date) DESC';

            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching expense statement: " . $e->getMessage());
            return false;
        }
    }
}
