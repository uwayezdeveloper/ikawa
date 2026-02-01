<?php
namespace Models;

class JournalEntry {
    private $conn;
    private $table_name = 'tbl_journal_entries';

    public function __construct( $db ) {
        $this->conn = $db;
    }

    /**
    * Create a new journal entry
    */

    public function createEntry( $entry_date, $debit_account_id, $amount, $charges, $method_id, $reference_id, $description, $user_id, $action ) {
        try {
            // Convert charges to integer as per database schema
            $charges_int = ( int )$charges;

            $query = 'INSERT INTO ' . $this->table_name . " 
                      (entry_date, debit_account_id, credit_account_id, amount, charges, method_id, reference_id, description, user_id, action)
                      VALUES (:entry_date, :debit_account_id, NULL, :amount, :charges, :method_id, :reference_id, :description, :user_id, :action)";

            $stmt = $this->conn->prepare( $query );

            $stmt->bindParam( ':entry_date', $entry_date, \PDO::PARAM_STR );
            $stmt->bindParam( ':debit_account_id', $debit_account_id, \PDO::PARAM_INT );
            $stmt->bindParam( ':amount', $amount, \PDO::PARAM_STR );
            $stmt->bindParam( ':charges', $charges_int, \PDO::PARAM_INT );
            $stmt->bindParam( ':method_id', $method_id, \PDO::PARAM_INT );
            $stmt->bindParam( ':reference_id', $reference_id, \PDO::PARAM_INT );

            if ( $description === null || $description === '' ) {
                $stmt->bindValue( ':description', null, \PDO::PARAM_NULL );
            } else {
                $stmt->bindParam( ':description', $description, \PDO::PARAM_STR );
            }

            $stmt->bindParam( ':user_id', $user_id, \PDO::PARAM_INT );
            $stmt->bindParam( ':action', $action, \PDO::PARAM_STR );

            if ( $stmt->execute() ) {
                $lastId = $this->conn->lastInsertId();
                return $lastId;
            }

            return false;
        } catch ( \PDOException $e ) {
            return false;
        }
    }

    /**
    * Create both expense and charges journal entries for a transaction
    */

    public function createExpenseTransaction( $entry_date, $debit_account_id, $expense_amount, $charges_amount, $method_id, $reference_id, $description, $user_id ) {
        try {
            // Create expense entry first ( amount = expense amount, charges = 0 )
            $expenseEntryId = $this->createEntry(
                $entry_date,
                $debit_account_id,
                $expense_amount,
                0, // Charges = 0 for expense row
                $method_id,
                $reference_id,
                $description,
                $user_id,
                'expense'
            );

            if ( !$expenseEntryId ) {
                return false;
            }

            // Create charges entry second if charges amount > 0 ( amount = 0, charges = charges amount )
            if ( $charges_amount > 0 ) {
                $chargesEntryId = $this->createEntry(
                    $entry_date,
                    $debit_account_id,
                    0, // Amount = 0 for charges row
                    $charges_amount, // Charges field for charges entry
                    $method_id,
                    $reference_id,
                    $description,
                    $user_id,
                    'charges'
                );

                if ( !$chargesEntryId ) {
                    return false;
                }
            }

            return true;

        } catch ( \PDOException $e ) {
            return false;
        }
    }

    /**
    * Get all journal entries with account details
    */

    public function getAllEntries() {
        try {
            $query = "SELECT 
                        je.*,
                        da.acc_name as debit_account_name,
                        e.name as expense_name,
                        u.username
                    FROM " . $this->table_name . " je
                    LEFT JOIN tbl_accounts da ON je.debit_account_id = da.acc_id
                    LEFT JOIN tbl_expenses e ON je.reference_id = e.id
                    LEFT JOIN tbl_users u ON je.user_id = u.user_id
                    ORDER BY je.created_at DESC";

            $stmt = $this->conn->prepare( $query );
            $stmt->execute();

            return $stmt->fetchAll( \PDO::FETCH_ASSOC );
        } catch ( \PDOException $e ) {
            return [];
        }
    }

    /**
    * Get journal entries by reference ID ( expense_id )
    */

    public function getEntriesByReference( $reference_id ) {
        try {
            $query = "SELECT 
                        je.*,
                        da.acc_name as debit_account_name,
                        e.name as expense_name,
                        u.username
                    FROM " . $this->table_name . " je
                    LEFT JOIN tbl_accounts da ON je.debit_account_id = da.acc_id
                    LEFT JOIN tbl_expenses e ON je.reference_id = e.id
                    LEFT JOIN tbl_users u ON je.user_id = u.user_id
                    WHERE je.reference_id = :reference_id
                    ORDER BY je.created_at DESC";

            $stmt = $this->conn->prepare( $query );
            $stmt->bindParam( ':reference_id', $reference_id );
            $stmt->execute();

            return $stmt->fetchAll( \PDO::FETCH_ASSOC );
        } catch ( \PDOException $e ) {
            return [];
        }
    }

    /**
    * Get journal entries by reference ID ( con_id from expenseconsume )
    */

    public function getEntriesByReferenceId( $reference_id ) {
        try {
            $query = 'SELECT * FROM ' . $this->table_name . " 
                      WHERE reference_id = :reference_id";

            $stmt = $this->conn->prepare( $query );
            $stmt->execute( [ 'reference_id' => $reference_id ] );

            return $stmt->fetchAll( \PDO::FETCH_ASSOC );
        } catch ( \PDOException $e ) {
            return [];
        }
    }

    /**
    * Cancel journal entries by reference ID ( set action to 'canceled' )
    */

    public function cancelEntriesByReferenceId( $reference_id, $user_id ) {
        try {
            $query = 'UPDATE ' . $this->table_name . " 
                      SET action = 'canceled'
                      WHERE reference_id = :reference_id";

            $stmt = $this->conn->prepare( $query );
            $result = $stmt->execute( [ 'reference_id' => $reference_id ] );

            return $result;
        } catch ( \PDOException $e ) {
            return false;
        }
    }
}