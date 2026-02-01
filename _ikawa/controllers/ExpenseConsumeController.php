<?php
namespace Controllers;

// Include dependencies
require_once __DIR__ . '/../models/ExpenseConsume.php';
require_once __DIR__ . '/../models/Account.php';
require_once __DIR__ . '/../models/JournalEntry.php';
require_once __DIR__ . '/../config/Response.php';

use Models\ExpenseConsume;
use Models\Account;
use Models\JournalEntry;
use Config\Response;

class ExpenseConsumeController
{
    private $expenseConsumeModel;
    private $accountModel;
    private $journalEntryModel;

    public function __construct()
    {
        $this->expenseConsumeModel = new ExpenseConsume();
        $this->accountModel = new Account();
        
        $db = (new \Config\Database())->getConnection();
        $this->journalEntryModel = new JournalEntry($db);
    }

    public function getAllExpenseConsumes()
    {
        $expenseConsumes = $this->expenseConsumeModel->getAllExpenseConsumes();

        if ($expenseConsumes !== false) {
            // Add charges from journal entries for each expense consume
            foreach ($expenseConsumes as &$expenseConsume) {
                $con_id = $expenseConsume['con_id'];
                
                // Get journal entries for this expense consume
                $journalEntries = $this->journalEntryModel->getEntriesByReferenceId($con_id);
                
                // Sum all charges from journal entries
                $total_charges = 0;
                foreach ($journalEntries as $entry) {
                    if (isset($entry['charges']) && $entry['charges'] > 0) {
                        $total_charges += (float)$entry['charges'];
                    }
                }
                
                // Add charges to the expense consume record
                $expenseConsume['charges'] = $total_charges;
            }
            
            Response::success('Expense consumes retrieved successfully!', $expenseConsumes);
        } else {
            Response::error('Failed to retrieve expense consumes', 500);
        }
    }

    public function getExpenseConsumeById($con_id)
    {
        $expenseConsume = $this->expenseConsumeModel->getExpenseConsumeById($con_id);

        if ($expenseConsume !== false) {
            // Get journal entries using con_id as reference_id
            $journalEntries = $this->journalEntryModel->getEntriesByReferenceId($con_id);
            
            // Sum all charges from journal entries
            $total_charges = 0;
            foreach ($journalEntries as $entry) {
                if (isset($entry['charges']) && $entry['charges'] > 0) {
                    $total_charges += (float)$entry['charges'];
                }
            }
            
            // Add charges to the response
            $expenseConsume['charges'] = $total_charges;
            
            Response::success('Expense consume retrieved successfully!', $expenseConsume);
        } else {
            Response::error('Expense consume not found', 404);
        }
    }

    public function createExpenseConsume()
    {
        // Clean any output buffer to ensure clean JSON response
        if (ob_get_level()) {
            ob_clean();
        }
        
        // POST METHOD ONLY
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Invalid request method', 405);
            return;
        }

        // Start session to get user_id and loc_id
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            Response::error('User not authenticated', 401);
            return;
        }

        if (!isset($_SESSION['loc_id'])) {
            Response::error('User location not set in session', 400);
            return;
        }

        $user_id = $_SESSION['user_id'];
        $station_id = $_SESSION['loc_id'];

        // READ JSON INPUT
        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input)) {
            Response::error('Invalid JSON payload', 400);
            return;
        }

        $required = [
            'expense_id',
            'recorded_date',
            'payment_entries'
        ];

        foreach ($required as $field) {
            if (!isset($input[$field]) || $input[$field] === '') {
                Response::error("Missing field: {$field}", 400);
                return;
            }
        }

        // Validate payment_entries is an array with at least one entry
        if (!is_array($input['payment_entries']) || count($input['payment_entries']) === 0) {
            error_log("Payment entries validation failed. Type: " . gettype($input['payment_entries']) . ", Count: " . (is_array($input['payment_entries']) ? count($input['payment_entries']) : 'N/A'));
            error_log("Full input data: " . json_encode($input));
            Response::error('At least one payment entry is required', 400);
            return;
        }

        // Validate each payment entry
        $total_expense_amount = 0;
        $total_charges_amount = 0;
        foreach ($input['payment_entries'] as $index => $entry) {
            if (!isset($entry['account_id']) || !isset($entry['amount']) || !isset($entry['charges'])) {
                error_log("Payment entry #{$index} validation failed: " . json_encode($entry));
                Response::error('Invalid payment entry: missing required fields (account_id, amount, or charges)', 400);
                return;
            }

            if (!is_numeric($entry['amount']) || $entry['amount'] <= 0) {
                error_log("Payment entry #{$index} amount validation failed: " . $entry['amount']);
                Response::error('Each payment entry amount must be a positive number', 400);
                return;
            }

            if (!is_numeric($entry['charges']) || $entry['charges'] < 0) {
                error_log("Payment entry #{$index} charges validation failed: " . $entry['charges']);
                Response::error('Each payment entry charges must be non-negative', 400);
                return;
            }

            $total_expense_amount += (float)$entry['amount'];
            $total_charges_amount += (float)$entry['charges'];
        }

        error_log("Validation passed. Processing " . count($input['payment_entries']) . " payment entries. Total amount: {$total_expense_amount}, Total charges: {$total_charges_amount}");

        // VALIDATE ALL ACCOUNT BALANCES BEFORE STARTING ANY TRANSACTION
        // This prevents partial transactions when later entries fail balance check
        foreach ($input['payment_entries'] as $index => $entry) {
            $acc_id = (int)$entry['account_id'];
            $amount = (float)$entry['amount'];
            $charges = (float)$entry['charges'];
            $total_required = $amount + $charges;

            // Check if account has sufficient balance
            if (!$this->accountModel->checkBalance($acc_id, $total_required)) {
                $current_balance = $this->accountModel->getBalance($acc_id);
                Response::error("Insufficient balance in account ID {$acc_id}. Required: {$total_required}, Available: {$current_balance}. Transaction rejected - no partial payments will be processed.", 400);
                return;
            }
        }

        // All balance checks passed - now generate transaction ID and proceed
        $trans_id = $this->expenseConsumeModel->getNextTransId();

        // Get database connection for transaction
        $db = (new \Config\Database())->getConnection();

        try {
            // Begin database transaction
            $db->beginTransaction();

            // Process each payment entry
            foreach ($input['payment_entries'] as $entry) {
                $acc_id = (int)$entry['account_id'];
                $amount = (float)$entry['amount'];
                $charges = (float)$entry['charges'];
                $total_required = $amount + $charges;

                // Create expense consume record for this entry
                // Save only the amount (not including charges) in tbl_expenseconsume
                $data = [
                    'expense_id' => (int)$input['expense_id'],
                    'station_id' => (int)$station_id,
                    'amount' => $amount,  // Only the expense amount, not charges
                    'pay_mode' => $acc_id,
                    'trans_id' => $trans_id,
                    'payer_name' => isset($input['payer_name']) ? trim($input['payer_name']) : null,
                    'description' => isset($input['description']) ? trim($input['description']) : null,
                    'recorded_date' => trim($input['recorded_date']),
                    'receipt_type' => isset($input['receipt_type']) ? (int)$input['receipt_type'] : null
                ];

                $con_id = $this->expenseConsumeModel->createExpenseConsume($data);
                
                if (!$con_id) {
                    $db->rollBack();
                    Response::error('Failed to record expense consume', 500);
                    return;
                }

                // Create journal entries (expense + charges) for this payment
                // Use con_id as reference_id in journal entries
                $journal_success = $this->journalEntryModel->createExpenseTransaction(
                    $data['recorded_date'],
                    $acc_id,
                    $amount,
                    $charges,
                    $acc_id,
                    $con_id,  // Use the con_id as reference_id
                    $data['description'],
                    $user_id
                );

                if (!$journal_success) {
                    $db->rollBack();
                    Response::error('Failed to create journal entries', 500);
                    return;
                }

                // Update account balance (deduct total amount + charges)
                $balance_updated = $this->accountModel->updateBalance($acc_id, $total_required);

                if (!$balance_updated) {
                    $db->rollBack();
                    Response::error('Failed to update account balance', 500);
                    return;
                }
            }

            // Commit all changes
            $db->commit();

            Response::success('Expense consume recorded successfully', [
                'trans_id' => $trans_id,
                'total_amount' => $total_expense_amount,
                'total_charges' => $total_charges_amount,
                'grand_total' => $total_expense_amount + $total_charges_amount,
                'entries_count' => count($input['payment_entries'])
            ]);

        } catch (\Exception $e) {
            $db->rollBack();
            error_log("Error in createExpenseConsume: " . $e->getMessage());
            Response::error('An error occurred while processing the transaction: ' . $e->getMessage(), 500);
        }
    }

    public function updateExpenseConsume()
    {
        // POST METHOD ONLY
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Invalid request method', 405);
            return;
        }

        // Start session to get loc_id and user
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['loc_id'])) {
            Response::error('User location not set in session', 400);
            return;
        }

        if (!isset($_SESSION['user_id'])) {
            Response::error('User not authenticated', 401);
            return;
        }

        $station_id = $_SESSION['loc_id'];
        $user_id = $_SESSION['user_id'];

        // READ JSON INPUT
        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input)) {
            Response::error('Invalid JSON payload', 400);
            return;
        }

        $required = [
            'con_id',
            'expense_id',
            'amount',
            'pay_mode',
            'recorded_date'
        ];

        foreach ($required as $field) {
            if (!isset($input[$field]) || $input[$field] === '') {
                Response::error("Missing field: {$field}", 400);
                return;
            }
        }

        // Validate amount is numeric and positive
        if (!is_numeric($input['amount']) || $input['amount'] <= 0) {
            Response::error('Amount must be a positive number', 400);
            return;
        }

        $con_id = (int)$input['con_id'];
        $new_amount = (float)$input['amount'];
        $new_pay_mode = (int)$input['pay_mode'];
        $new_charges = isset($input['charges']) ? (float)$input['charges'] : null; // optional
        $recorded_date = trim($input['recorded_date']);
        $description = isset($input['description']) ? trim($input['description']) : null;

        // Fetch existing record
        $existing = $this->expenseConsumeModel->getExpenseConsumeById($con_id);
        if (!$existing) {
            Response::error('Expense consume not found', 404);
            return;
        }

        $old_amount = (float)$existing['amount'];
        $old_pay_mode = (int)$existing['pay_mode'];

        // Get old journal entries and compute old charges
        $journalEntries = $this->journalEntryModel->getEntriesByReferenceId($con_id);
        $old_charges = 0;
        foreach ($journalEntries as $je) {
            if (!empty($je['charges'])) {
                $old_charges += (float)$je['charges'];
            }
        }

        $old_total_required = $old_amount + $old_charges;
        $new_charges = $new_charges === null ? $old_charges : $new_charges;
        $new_total_required = $new_amount + $new_charges;

        // Get database connection and a journal model bound to it (for consistent cancellation/creation)
        $db = (new \Config\Database())->getConnection();
        $journalModel = new JournalEntry($db);

        try {
            $db->beginTransaction();

            // CASE 1: same account
            if ($new_pay_mode === $old_pay_mode) {
                if ($new_total_required > $old_total_required) {
                    // Need extra funds from same account
                    $extra = $new_total_required - $old_total_required;
                    if (!$this->accountModel->checkBalance($new_pay_mode, $extra)) {
                        $db->rollBack();
                        $current_balance = $this->accountModel->getBalance($new_pay_mode);
                        Response::error("Insufficient balance in account ID {$new_pay_mode}. Needed additional: {$extra}, Available: {$current_balance}", 400);
                        return;
                    }
                    if (!$this->accountModel->updateBalance($new_pay_mode, $extra)) {
                        $db->rollBack();
                        Response::error('Failed to deduct additional amount from account', 500);
                        return;
                    }
                } elseif ($new_total_required < $old_total_required) {
                    // Refund difference back to same account
                    $refund = $old_total_required - $new_total_required;
                    if (!$this->accountModel->refundBalance($new_pay_mode, $refund)) {
                        $db->rollBack();
                        Response::error('Failed to refund difference to account', 500);
                        return;
                    }
                }
            } else {
                // CASE 2: account changed - refund old, charge new
                // Refund old account first
                if ($old_pay_mode) {
                    if (!$this->accountModel->refundBalance($old_pay_mode, $old_total_required)) {
                        $db->rollBack();
                        Response::error('Failed to refund amount to original account', 500);
                        return;
                    }
                }

                // Ensure new account has enough balance for the new total
                if (!$this->accountModel->checkBalance($new_pay_mode, $new_total_required)) {
                    // Since we already refunded original, attempt to rollback refund
                    if ($old_pay_mode) {
                        $this->accountModel->updateBalance($old_pay_mode, $old_total_required);
                    }
                    $db->rollBack();
                    $current_balance = $this->accountModel->getBalance($new_pay_mode);
                    Response::error("Insufficient balance in new account ID {$new_pay_mode}. Required: {$new_total_required}, Available: {$current_balance}", 400);
                    return;
                }

                // Deduct from new account
                if (!$this->accountModel->updateBalance($new_pay_mode, $new_total_required)) {
                    // Try to rollback refund
                    if ($old_pay_mode) {
                        $this->accountModel->updateBalance($old_pay_mode, $old_total_required);
                    }
                    $db->rollBack();
                    Response::error('Failed to deduct amount from new account', 500);
                    return;
                }
            }

            // Update existing journal entries (expense + charges) for this reference_id.
            // Some deployments may have an older JournalEntry model without
            // `updateEntriesByReferenceId`. Use it when available; otherwise
            // fallback to canceling existing entries and recreating them.
            if (method_exists($journalModel, 'updateEntriesByReferenceId')) {
                $journal_success = $journalModel->updateEntriesByReferenceId(
                    $con_id,
                    $recorded_date,
                    $new_pay_mode,
                    $new_amount,
                    $new_charges,
                    $new_pay_mode,
                    $description,
                    $user_id
                );
            } else {
                error_log('JournalEntry::updateEntriesByReferenceId not found - using safe in-place update fallback');
                $journal_success = false;

                try {
                    // Load existing entries for this reference
                    $entries = [];
                    if (method_exists($journalModel, 'getEntriesByReferenceId')) {
                        $entries = $journalModel->getEntriesByReferenceId($con_id);
                    }

                    $expenseEntry = null;
                    $chargesEntry = null;
                    foreach ($entries as $e) {
                        if (isset($e['action']) && $e['action'] === 'expense') {
                            $expenseEntry = $e;
                        } elseif (isset($e['action']) && $e['action'] === 'charges') {
                            $chargesEntry = $e;
                        }
                    }

                    // Require existing expense entry to update in-place
                    if (!$expenseEntry || !isset($expenseEntry['entry_id'])) {
                        $db->rollBack();
                        Response::error('Expense journal entry not found for this record (cannot safely update)', 500);
                        return;
                    }

                    // Update expense entry row
                    $updateExpenseSql = "UPDATE tbl_journal_entries SET entry_date = :entry_date, debit_account_id = :debit_account_id, amount = :amount, method_id = :method_id, description = :description, user_id = :user_id WHERE entry_id = :entry_id";
                    $stmtExp = $db->prepare($updateExpenseSql);
                    $stmtExp->execute([
                        ':entry_date' => $recorded_date,
                        ':debit_account_id' => $new_pay_mode,
                        ':amount' => $new_amount,
                        ':method_id' => $new_pay_mode,
                        ':description' => $description,
                        ':user_id' => $user_id,
                        ':entry_id' => $expenseEntry['entry_id']
                    ]);

                    // Update charges entry if exists; otherwise insert only if new_charges > 0
                    if ($chargesEntry && isset($chargesEntry['entry_id'])) {
                        $updateChargesSql = "UPDATE tbl_journal_entries SET entry_date = :entry_date, debit_account_id = :debit_account_id, charges = :charges, method_id = :method_id, description = :description, user_id = :user_id WHERE entry_id = :entry_id";
                        $stmtCh = $db->prepare($updateChargesSql);
                        $stmtCh->execute([
                            ':entry_date' => $recorded_date,
                            ':debit_account_id' => $new_pay_mode,
                            ':charges' => (int)$new_charges,
                            ':method_id' => $new_pay_mode,
                            ':description' => $description,
                            ':user_id' => $user_id,
                            ':entry_id' => $chargesEntry['entry_id']
                        ]);
                    } else {
                        if ((float)$new_charges > 0) {
                            // Insert a single charges row (only if none existed before)
                            $insertSql = "INSERT INTO tbl_journal_entries (entry_date, debit_account_id, credit_account_id, amount, charges, method_id, reference_id, description, user_id, action) VALUES (:entry_date, :debit_account_id, NULL, 0, :charges, :method_id, :reference_id, :description, :user_id, 'charges')";
                            $stmtIns = $db->prepare($insertSql);
                            $stmtIns->execute([
                                ':entry_date' => $recorded_date,
                                ':debit_account_id' => $new_pay_mode,
                                ':charges' => (int)$new_charges,
                                ':method_id' => $new_pay_mode,
                                ':reference_id' => $con_id,
                                ':description' => $description,
                                ':user_id' => $user_id
                            ]);
                            error_log('Inserted missing charges journal entry for reference_id: ' . $con_id);
                        }
                    }

                    $journal_success = true;
                } catch (\Exception $ex) {
                    error_log('Fallback journal update error: ' . $ex->getMessage());
                    $journal_success = false;
                }
            }

            if (!$journal_success) {
                // Attempt to rollback any balance adjustments done above
                if ($new_pay_mode === $old_pay_mode) {
                    if ($new_total_required > $old_total_required) {
                        // refund the extra
                        $this->accountModel->refundBalance($new_pay_mode, $new_total_required - $old_total_required);
                    } elseif ($new_total_required < $old_total_required) {
                        // re-deduct the refunded amount
                        $this->accountModel->updateBalance($new_pay_mode, $old_total_required - $new_total_required);
                    }
                } else {
                    // refund new and re-deduct old
                    $this->accountModel->refundBalance($new_pay_mode, $new_total_required);
                    if ($old_pay_mode) {
                        $this->accountModel->updateBalance($old_pay_mode, $old_total_required);
                    }
                }
                $db->rollBack();
                Response::error('Failed to update journal entries', 500);
                return;
            }

            // Update the expense consume record
            $updateData = [
                'con_id' => $con_id,
                'expense_id' => (int)$input['expense_id'],
                'station_id' => (int)$station_id,
                'amount' => $new_amount,
                'pay_mode' => $new_pay_mode,
                'payer_name' => isset($input['payer_name']) ? trim($input['payer_name']) : null,
                'description' => $description,
                'recorded_date' => $recorded_date,
                'receipt_type' => isset($input['receipt_type']) ? (int)$input['receipt_type'] : null
            ];

            if (!$this->expenseConsumeModel->updateExpenseConsume($updateData)) {
                // Try to revert balances and journal entries minimally
                $db->rollBack();
                Response::error('Failed to update expense consume record', 500);
                return;
            }

            $db->commit();
            Response::success('Expense consume updated successfully', $updateData);

        } catch (\Exception $e) {
            $db->rollBack();
            error_log('Error in updateExpenseConsume: ' . $e->getMessage());
            Response::error('An error occurred while updating expense consume: ' . $e->getMessage(), 500);
        }
    }

    public function deleteExpenseConsume()
    {
        // POST METHOD ONLY
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Invalid request method', 405);
            return;
        }

        // Start session to get user_id
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            Response::error('User not authenticated', 401);
            return;
        }

        $user_id = $_SESSION['user_id'];

        // READ JSON INPUT
        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input)) {
            Response::error('Invalid JSON payload', 400);
            return;
        }

        if (empty($input['con_id'])) {
            Response::error('Missing con_id field', 400);
            return;
        }

        $con_id = (int)$input['con_id'];

        // Get expense consume details before deletion
        $expenseConsume = $this->expenseConsumeModel->getExpenseConsumeById($con_id);
        
        if (!$expenseConsume) {
            Response::error('Expense consume not found', 404);
            return;
        }

        // Get the account and amounts to refund
        $acc_id = $expenseConsume['pay_mode'];
        
        // Get all journal entries for this con_id to calculate total refund (amount + charges)
        $journalEntries = $this->journalEntryModel->getEntriesByReferenceId($con_id);
        
        $total_refund = 0;
        
        foreach ($journalEntries as $entry) {
            $total_refund += (float)$entry['amount'];
        }

        // Get database connection for transaction
        $db = (new \Config\Database())->getConnection();

        try {
            // Begin database transaction
            $db->beginTransaction();

            // 1. Refund amount back to account (add back the total amount + charges)
            $refund_success = $this->accountModel->refundBalance($acc_id, $total_refund);
            
            if (!$refund_success) {
                $db->rollBack();
                Response::error('Failed to refund amount to account', 500);
                return;
            }

            // 2. Update journal entries action to 'canceled'
            $cancel_journal_success = $this->journalEntryModel->cancelEntriesByReferenceId($con_id, $user_id);
            
            if (!$cancel_journal_success) {
                $db->rollBack();
                Response::error('Failed to cancel journal entries', 500);
                return;
            }

            // 3. Set expense consume status to 11 (cancelled)
            $cancel_success = $this->expenseConsumeModel->cancelExpenseConsume($con_id);
            
            if (!$cancel_success) {
                $db->rollBack();
                Response::error('Failed to cancel expense consume', 500);
                return;
            }

            // Commit all changes
            $db->commit();

            Response::success('Expense cancelled and amount refunded successfully', [
                'con_id' => $con_id,
                'refunded_amount' => $total_refund,
                'account_id' => $acc_id
            ]);

        } catch (\Exception $e) {
            $db->rollBack();
            $errorMsg = $e->getMessage();
            error_log("Error in deleteExpenseConsume: " . $errorMsg);
            error_log("Stack trace: " . $e->getTraceAsString());
            Response::error('Error: ' . $errorMsg, 500);
        }
    }

    public function getExpensesByStation($station_id)
    {
        $expenses = $this->expenseConsumeModel->getExpensesByStation($station_id);

        if ($expenses !== false) {
            Response::success('Station expenses retrieved successfully!', $expenses);
        } else {
            Response::error('Failed to retrieve station expenses', 500);
        }
    }

    public function getTotalExpensesByPeriod()
    {
        $start_date = $_GET['start_date'] ?? date('Y-m-01');
        $end_date = $_GET['end_date'] ?? date('Y-m-t');

        $total = $this->expenseConsumeModel->getTotalExpensesByPeriod($start_date, $end_date);

        if ($total !== false) {
            Response::success('Total expenses retrieved successfully!', ['total' => $total]);
        } else {
            Response::error('Failed to retrieve total expenses', 500);
        }
    }

    /**
     * Get accounts by payment mode ID
     */
    public function getAccountsByPaymentMode($mode_id)
    {
        if (empty($mode_id)) {
            Response::error('Payment mode ID is required', 400);
            return;
        }

        $accounts = $this->accountModel->getAccountsByPaymentMode($mode_id);

        if ($accounts !== false) {
            Response::success('Accounts retrieved successfully!', $accounts);
        } else {
            Response::error('Failed to retrieve accounts', 500);
        }
    }

    /**
     * Get expense statement with filters
     * Excludes status 11 and canceled journal entries
     */
    public function getExpenseStatement()
    {
        // Clean any output buffer to ensure clean JSON response
        if (ob_get_level()) {
            ob_clean();
        }

        // POST METHOD ONLY
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Invalid request method', 405);
            return;
        }

        // READ JSON INPUT
        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input)) {
            Response::error('Invalid JSON payload', 400);
            return;
        }

        // Prepare filters
        $filters = [
            'consumer_id' => $input['consumer_id'] ?? '',
            'expense_id' => $input['expense_id'] ?? '',
            'receipt_type' => $input['receipt_type'] ?? '',
            'date_from' => $input['date_from'] ?? '',
            'date_to' => $input['date_to'] ?? '',
            'display' => $input['display'] ?? 'both'
        ];

        // Validate date range
        if (empty($filters['date_from']) || empty($filters['date_to'])) {
            Response::error('Date range is required', 400);
            return;
        }

        // Get statement data
        $data = $this->expenseConsumeModel->getExpenseStatement($filters);

        if ($data !== false) {
            Response::success('Statement retrieved successfully!', $data);
        } else {
            Response::error('Failed to retrieve statement data', 500);
        }
    }
}
