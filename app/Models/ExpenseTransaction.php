<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class ExpenseTransaction extends Model
{
    protected string $table = 'tbl_expenseconsume';
    protected string $primaryKey = 'con_id';
    protected string $detailsTable = 'tbl_expense_conume_details';
    protected array $fillable = [
        'expense_id', 'station_id', 'amount', 'pay_mode', 'trans_id', 
        'payer_name', 'description', 'pay_date', 'recorded_date', 'receipt_type', 'status'
    ];

    /**
     * Get paginated expense transactions with related data
     */
    public function getPaginatedTransactions($page = 1, $perPage = 20, $search = '', $userId = null, $dateFrom = null, $dateTo = null): array
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT 
                    ec.*, 
                    et.expense_name,
                    ecr.cons_name as consumer_name,
                    ecr.phone as consumer_phone,
                    l.name as location_name,
                    rt.rec_name as receipt_type_name
                FROM {$this->table} ec
                LEFT JOIN tbl_expenses et ON ec.expense_id = et.expense_id
                LEFT JOIN tbl_expenseconsumer ecr ON ec.payer_name = ecr.cons_id
                LEFT JOIN locations l ON ec.station_id = l.id
                LEFT JOIN tbl_receipttype rt ON ec.receipt_type = rt.rec_id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($userId)) {
            $userLocationId = $this->getUserLocationId((int)$userId);
            if ($userLocationId > 0) {
                $sql .= " AND ec.station_id = :location_id";
                $params['location_id'] = $userLocationId;
            }
        }

        if (!empty($search)) {
            $sql .= " AND (et.expense_name LIKE :search 
                      OR ecr.cons_name LIKE :search 
                      OR ec.trans_id LIKE :search 
                      OR ec.description LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }

        if (!empty($dateFrom)) {
            $sql .= " AND ec.recorded_date >= :date_from";
            $params['date_from'] = $dateFrom;
        }
        if (!empty($dateTo)) {
            $sql .= " AND ec.recorded_date <= :date_to";
            $params['date_to'] = $dateTo;
        }
        
        // Get total count
        $countSql = str_replace('SELECT ec.*, et.expense_name, ecr.cons_name as consumer_name, ecr.phone as consumer_phone, l.name as location_name, rt.rec_name as receipt_type_name', 'SELECT COUNT(*) as total', $sql);
        $result = Database::fetchAll($countSql, $params);
        $total = $result[0]['total'] ?? 0;
        
        // Get paginated results
        $sql .= " ORDER BY ec.pay_date DESC LIMIT :limit OFFSET :offset";
        $params['limit'] = $perPage;
        $params['offset'] = $offset;
        
        $transactions = Database::fetchAll($sql, $params);

        // Gather all account IDs from pay_mode JSONs
        $allAccountIds = [];
        foreach ($transactions as $tr) {
            if (!empty($tr['pay_mode'])) {
                $pmArr = json_decode($tr['pay_mode'], true);
                if (is_array($pmArr)) {
                    foreach ($pmArr as $pm) {
                        if (isset($pm['account_id'])) {
                            $allAccountIds[] = (int)$pm['account_id'];
                        }
                    }
                }
            }
        }
        $allAccountIds = array_values(array_unique($allAccountIds));

        // Fetch all account names in one query
        $accountMap = [];
        if (!empty($allAccountIds)) {
            $in = implode(',', array_fill(0, count($allAccountIds), '?'));
            $accRows = Database::fetchAll("SELECT id, account_name FROM accounts WHERE id IN ($in)", array_values($allAccountIds));
            foreach ($accRows as $acc) {
                $accountMap[$acc['id']] = $acc['account_name'];
            }
        }

        // Fetch charges for each transaction from details table and map account names
        foreach ($transactions as &$transaction) {
            // Fetch all details for this transaction
            $detailsSql = "SELECT * FROM {$this->detailsTable} WHERE trans_code = :trans_code";
            $detailsResult = Database::fetchAll($detailsSql, ['trans_code' => $transaction['trans_id']]);
            $transaction['charges'] = 0;
            $transaction['account_charges'] = [];
            $chargesTotal = 0;
            // Map account_id to charges for CHARGES rows
            foreach ($detailsResult as $detail) {
                if ($detail['action'] === 'CHARGES' && isset($detail['account_id'])) {
                    $transaction['account_charges'][$detail['account_id']] = (float)$detail['charges'];
                    $chargesTotal += (float)$detail['charges'];
                }
            }
            $transaction['charges'] = $chargesTotal;

            // Map account names for pay_mode
            $transaction['account_names'] = [];
            $transaction['account_ids'] = [];
            if (!empty($transaction['pay_mode'])) {
                $pmArr = json_decode($transaction['pay_mode'], true);
                if (is_array($pmArr)) {
                    foreach ($pmArr as $pm) {
                        if (isset($pm['account_id'])) {
                            $aid = (int)$pm['account_id'];
                            $transaction['account_names'][] = $accountMap[$aid] ?? $aid;
                            $transaction['account_ids'][] = $aid;
                        }
                    }
                }
            }
        }
        unset($transaction);

        return [
            'data' => $transactions,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }

    /**
     * Create new expense transaction with multi-payment support
     */
    public function createTransaction(array $data, $createdBy): bool
    {
        try {
            Database::getInstance()->beginTransaction();

            // Generate unique transaction ID
            $transId = $this->generateTransactionId();

            // Handle multi-payment data
            $payModeData = $data['pay_mode'];
            if (is_array($payModeData)) {
                // Multi-payment: store as JSON and deduct from multiple accounts
                $payModeJson = json_encode($payModeData);
                $this->deductFromMultipleAccounts($payModeData, $data['amount'] + ($data['charges'] ?? 0));
            } else {
                // Single payment: store as single account ID and deduct from one account
                $payModeJson = json_encode([['account_id' => $payModeData, 'amount' => $data['amount'] + ($data['charges'] ?? 0)]]);
                $this->deductFromAccount($payModeData, $data['amount'] + ($data['charges'] ?? 0));
            }

            // Insert main transaction
            $mainSql = "INSERT INTO {$this->table} 
                       (expense_id, station_id, amount, pay_mode, trans_id, payer_name, 
                        description, pay_date, recorded_date, receipt_type, status) 
                       VALUES 
                       (:expense_id, :station_id, :amount, :pay_mode, :trans_id, :payer_name, 
                        :description, :pay_date, :recorded_date, :receipt_type, :status)";

            $mainParams = [
                'expense_id' => $data['expense_id'],
                'station_id' => $data['station_id'],
                'amount' => $data['amount'], // Only amount, not charges
                'pay_mode' => $payModeJson,
                'trans_id' => $transId,
                'payer_name' => $data['payer_name'],
                'description' => $data['description'] ?? null,
                'pay_date' => date('Y-m-d H:i:s'),
                'recorded_date' => $data['recorded_date'] ?? date('Y-m-d'),
                'receipt_type' => $data['receipt_type'] ?? null,
                'status' => $data['status'] ?? 1
            ];

            Database::query($mainSql, $mainParams);

            // Insert details - Amount row
            $detailsSql = "INSERT INTO {$this->detailsTable} 
                          (trans_code, amount, charges, created_by, action, account_id) 
                          VALUES (:trans_code, :amount, :charges, :created_by, :action, :account_id)";

            Database::query($detailsSql, [
                'trans_code' => $transId,
                'amount' => $data['amount'],
                'charges' => 0,
                'created_by' => $createdBy,
                'action' => 'AMOUNT',
                'account_id' => null
            ]);

            // Insert details - Charges row (if charges exist, single payment mode)
            if (!empty($data['charges']) && $data['charges'] > 0 && empty($data['per_account_charges'])) {
                Database::query($detailsSql, [
                    'trans_code' => $transId,
                    'amount' => 0,
                    'charges' => $data['charges'],
                    'created_by' => $createdBy,
                    'action' => 'CHARGES',
                    'account_id' => null
                ]);
            }

            // Insert per-account charges if present (multi-payment mode)
            if (!empty($data['per_account_charges']) && is_array($data['per_account_charges'])) {
                foreach ($data['per_account_charges'] as $chargeRow) {
                    if (!empty($chargeRow['charges']) && !empty($chargeRow['account_id'])) {
                        Database::query($detailsSql, [
                            'trans_code' => $transId,
                            'amount' => 0,
                            'charges' => $chargeRow['charges'],
                            'created_by' => $createdBy,
                            'action' => 'CHARGES',
                            'account_id' => $chargeRow['account_id']
                        ]);
                    }
                }
            }

            Database::getInstance()->commit();
            return true;

        } catch (\Exception $e) {
            Database::getInstance()->rollback();
            throw $e;
        }
    }

    /**
     * Find transaction by ID with related data
     */
    public function findByIdWithDetails($id): ?array
    {
        try {
            error_log("ExpenseTransaction::findByIdWithDetails - Finding transaction with ID: " . $id);
            
            $sql = "SELECT 
                        ec.*, 
                        et.expense_name,
                        ecr.cons_name as consumer_name,
                        ecr.phone as consumer_phone,
                        l.name as location_name,
                        a.account_name,
                        rt.rec_name as receipt_type_name
                    FROM {$this->table} ec
                    LEFT JOIN tbl_expenses et ON ec.expense_id = et.expense_id
                    LEFT JOIN tbl_expenseconsumer ecr ON ec.payer_name = ecr.cons_id
                    LEFT JOIN locations l ON ec.station_id = l.id
                    LEFT JOIN accounts a ON ec.pay_mode = a.id
                    LEFT JOIN tbl_receipttype rt ON ec.receipt_type = rt.rec_id
                    WHERE ec.{$this->primaryKey} = :id";
            
            error_log("ExpenseTransaction::findByIdWithDetails - SQL: " . $sql);
            
            $result = Database::fetchAll($sql, ['id' => $id]);
            error_log("ExpenseTransaction::findByIdWithDetails - Result count: " . count($result));
            
            return !empty($result) ? $result[0] : null;
            
        } catch (\Exception $e) {
            error_log("ExpenseTransaction::findByIdWithDetails - Exception: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get transaction details
     */
    public function getTransactionDetails($transCode): array
    {
        try {
            error_log("ExpenseTransaction::getTransactionDetails - Getting details for trans_code: " . $transCode);
            
            $sql = "SELECT d.*, u.first_name, u.last_name 
                    FROM {$this->detailsTable} d
                    LEFT JOIN users u ON d.created_by = u.id
                    WHERE d.trans_code = :trans_code
                    ORDER BY d.action ASC";
            
            error_log("ExpenseTransaction::getTransactionDetails - SQL: " . $sql);
            
            $result = Database::fetchAll($sql, ['trans_code' => $transCode]);
            error_log("ExpenseTransaction::getTransactionDetails - Result count: " . count($result));
            
            return $result;
            
        } catch (\Exception $e) {
            error_log("ExpenseTransaction::getTransactionDetails - Exception: " . $e->getMessage());
            return []; // Return empty array on error to prevent further issues
        }
    }

    /**
     * Generate unique transaction ID (max 20 chars)
     */
    private function generateTransactionId(): string
    {
        $prefix = 'EX';
        $timestamp = date('ymdHis'); // Use 2-digit year to save space
        $random = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT); // Use 3 digits
        
        return $prefix . $timestamp . $random; // EX + 6 + 6 + 3 = 17 characters
    }

    /**
     * Get accounts by user location
     */
    public function getAccountsByUserLocation($userId): array
    {
        $sql = "SELECT a.* 
                FROM accounts a
                INNER JOIN locations l ON a.location_id = l.id
                WHERE a.status = 'active'
                ORDER BY a.account_name ASC";
        
        return Database::fetchAll($sql);
    }

    /**
     * Get user's location
     */
    public function getUserLocation($userId): ?array
    {
        // Since locations table doesn't have created_by column,
        // return null or implement different logic
        return null;
    }

    /**
     * Update transaction status
     */
    public function updateStatus($id, $status): bool
    {
        $sql = "UPDATE {$this->table} SET status = :status WHERE {$this->primaryKey} = :id";
        return Database::query($sql, ['id' => $id, 'status' => $status]) !== false;
    }

    /**
     * Get transaction statistics
     */
    public function getTransactionStats($userId = null, $dateFrom = null, $dateTo = null): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_transactions,
                    SUM(amount) as total_amount,
                    AVG(amount) as avg_amount,
                    COUNT(CASE WHEN status = 1 THEN 1 END) as active_transactions
                FROM {$this->table} ec";
        
        $params = [];
        $conditions = [];
        
        if (!empty($userId)) {
            $userLocationId = $this->getUserLocationId((int)$userId);
            if ($userLocationId > 0) {
                $conditions[] = "ec.station_id = :location_id";
                $params['location_id'] = $userLocationId;
            }
        }
        
        if ($dateFrom) {
            $conditions[] = "ec.recorded_date >= :date_from";
            $params['date_from'] = $dateFrom;
        }
        
        if ($dateTo) {
            $conditions[] = "ec.recorded_date <= :date_to";
            $params['date_to'] = $dateTo;
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $result = Database::fetchAll($sql, $params);
        return !empty($result) ? $result[0] : [
            'total_transactions' => 0,
            'total_amount' => 0,
            'avg_amount' => 0,
            'active_transactions' => 0
        ];
    }

    private function getUserLocationId(int $userId): int
    {
        $sessionLocationId = (int)($_SESSION['user']['location_id'] ?? 0);
        if ($sessionLocationId > 0) {
            return $sessionLocationId;
        }

        $row = Database::fetch("SELECT location_id FROM users WHERE id = :id", ['id' => $userId]);
        return (int)($row['location_id'] ?? 0);
    }

    /**
     * Get transactions where a specific account was used, with allocated amount and charges.
     */
    public function getTransactionsForAccount(int $accountId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $sql = "SELECT trans_id, pay_mode, recorded_date, pay_date
                FROM {$this->table}
                WHERE status = 1";

        $params = [];

        if (!empty($dateFrom)) {
            $sql .= " AND recorded_date >= :date_from";
            $params['date_from'] = $dateFrom;
        }

        if (!empty($dateTo)) {
            $sql .= " AND recorded_date <= :date_to";
            $params['date_to'] = $dateTo;
        }

        $sql .= " ORDER BY pay_date DESC";

        $transactions = Database::fetchAll($sql, $params);
        $result = [];

        foreach ($transactions as $transaction) {
            $payments = json_decode($transaction['pay_mode'] ?? '[]', true);
            if (!is_array($payments) || empty($payments)) {
                continue;
            }

            $allocatedAmount = 0.0;
            foreach ($payments as $payment) {
                if ((int) ($payment['account_id'] ?? 0) === $accountId) {
                    $allocatedAmount += (float) ($payment['amount'] ?? 0);
                }
            }

            if ($allocatedAmount <= 0) {
                continue;
            }

            $detailsSql = "SELECT account_id, charges
                           FROM {$this->detailsTable}
                           WHERE trans_code = :trans_code AND action = 'CHARGES'";
            $details = Database::fetchAll($detailsSql, ['trans_code' => $transaction['trans_id']]);

            $accountCharges = 0.0;
            $genericCharges = 0.0;

            foreach ($details as $detail) {
                $detailAccountId = $detail['account_id'] ?? null;
                $charge = (float) ($detail['charges'] ?? 0);

                if ($detailAccountId === null || $detailAccountId === '' || (int) $detailAccountId === 0) {
                    $genericCharges += $charge;
                    continue;
                }

                if ((int) $detailAccountId === $accountId) {
                    $accountCharges += $charge;
                }
            }

            if ($genericCharges > 0) {
                $accountsInPayment = array_values(array_filter(array_map(
                    static fn ($row) => (int) ($row['account_id'] ?? 0),
                    $payments
                )));

                $uniqueAccounts = array_values(array_unique(array_filter($accountsInPayment)));
                if (count($uniqueAccounts) === 1 && (int) $uniqueAccounts[0] === $accountId) {
                    $accountCharges += $genericCharges;
                }
            }

            $result[] = [
                'trans_id' => $transaction['trans_id'],
                'recorded_date' => $transaction['recorded_date'],
                'pay_date' => $transaction['pay_date'],
                'allocated_amount' => $allocatedAmount,
                'account_charges' => $accountCharges
            ];
        }

        return $result;
    }

    /**
     * Deduct amount from single account
     */
    private function deductFromAccount(int $accountId, float $amount): void
    {
        // Check account balance first
        $balanceCheck = Database::fetchAll("SELECT balance FROM accounts WHERE id = :id", ['id' => $accountId]);
        if (empty($balanceCheck)) {
            throw new \Exception("Account not found");
        }
        
        $currentBalance = (float) $balanceCheck[0]['balance'];
        if ($currentBalance < $amount) {
            throw new \Exception("Insufficient balance in account. Available: " . number_format($currentBalance, 2) . ", Required: " . number_format($amount, 2));
        }
        
        // Deduct the amount
        $sql = "UPDATE accounts SET balance = balance - :amount WHERE id = :account_id";
        Database::query($sql, ['amount' => $amount, 'account_id' => $accountId]);
    }

    /**
     * Deduct amounts from multiple accounts
     */
    private function deductFromMultipleAccounts(array $payments, float $totalAmount): void
    {
        $totalPayments = 0;
        
        // First validate all payments and check balances
        foreach ($payments as $payment) {
            if (!isset($payment['account_id']) || !isset($payment['amount'])) {
                throw new \Exception("Invalid payment data format");
            }
            
            $accountId = (int) $payment['account_id'];
            $amount = (float) $payment['amount'];
            
            if ($amount <= 0) {
                throw new \Exception("Payment amount must be greater than 0");
            }
            
            // Check account exists and has sufficient balance
            $balanceCheck = Database::fetchAll("SELECT account_name, balance FROM accounts WHERE id = :id", ['id' => $accountId]);
            if (empty($balanceCheck)) {
                throw new \Exception("Account ID $accountId not found");
            }
            
            $currentBalance = (float) $balanceCheck[0]['balance'];
            if ($currentBalance < $amount) {
                throw new \Exception("Insufficient balance in " . $balanceCheck[0]['account_name'] . ". Available: " . number_format($currentBalance, 2) . ", Required: " . number_format($amount, 2));
            }
            
            $totalPayments += $amount;
        }
        
        // Verify total payments match transaction amount
        if (abs($totalPayments - $totalAmount) > 0.01) {
            throw new \Exception("Total payments (" . number_format($totalPayments, 2) . ") must equal transaction amount (" . number_format($totalAmount, 2) . ")");
        }
        
        // All validations passed, now deduct from accounts
        foreach ($payments as $payment) {
            $accountId = (int) $payment['account_id'];
            $amount = (float) $payment['amount'];
            
            $sql = "UPDATE accounts SET balance = balance - :amount WHERE id = :account_id";
            Database::query($sql, ['amount' => $amount, 'account_id' => $accountId]);
        }
    }

    /**
     * Get payment details from JSON pay_mode
     */
    public function getPaymentDetails(string $payModeJson): array
    {
        $payments = json_decode($payModeJson, true);
        if (!is_array($payments)) {
            return [];
        }
        
        $paymentDetails = [];
        foreach ($payments as $payment) {
            if (isset($payment['account_id'])) {
                $accountData = Database::fetchAll("SELECT account_name, account_number FROM accounts WHERE id = :id", ['id' => $payment['account_id']]);
                if (!empty($accountData)) {
                    $paymentDetails[] = [
                        'account_id' => $payment['account_id'],
                        'account_name' => $accountData[0]['account_name'],
                        'account_number' => $accountData[0]['account_number'] ?? '',
                        'amount' => $payment['amount']
                    ];
                }
            }
        }
        
        return $paymentDetails;
    }
}