<?php
namespace Models;

require_once __DIR__ . '/../config/Database.php';

use Config\Database;
use PDO;
use \PDOException;

class Financial {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function checkbalance( string $debit_account_id,  string $total ): ?string {
        $sql = "
            SELECT 
                CASE
                    WHEN balance < :amount THEN 'Insufficient Balance'
                END AS field
            FROM tbl_accounts
            WHERE acc_id = :acc_id 
            LIMIT 1
        ";

        $stmt = $this->conn->prepare( $sql );
        $stmt->execute( [
            ':amount' => $total,
            ':acc_id' => $debit_account_id
        ] );

        $row = $stmt->fetch( \PDO::FETCH_ASSOC );

        return $row[ 'field' ] ?? null;
    }

    public function CreateTransfer( array $data ): bool {
        try {
            $this->conn->beginTransaction();

            // UPDATE DEBIT ACCOUNT
            $minusQuery = 'UPDATE tbl_accounts SET balance = balance - :total WHERE acc_id = :acc_id';
            $stmt1 = $this->conn->prepare( $minusQuery );
            $stmt1->execute( [
                ':total' => $data[ 'total' ],
                ':acc_id' => $data[ 'debit_account_id' ]
            ] );

            // UPDATE CREDIT ACCOUNT
            $plusQuery = 'UPDATE tbl_accounts SET balance = balance + :amount WHERE acc_id = :acc_id';
            $stmt2 = $this->conn->prepare( $plusQuery );
            $stmt2->execute( [
                ':amount' => $data[ 'amount' ],
                ':acc_id' => $data[ 'credit_account_id' ]
            ] );

            // JOURNAL ENTRY ( Debit )
            $journalDebit = "INSERT INTO tbl_journal_entries (entry_date, debit_account_id, amount, user_id, action) VALUES (NOW(), :debit, :amount, :user_id, 'DEBIT')";
            $stmt3 = $this->conn->prepare( $journalDebit );
            $stmt3->execute( [
                ':debit' => $data[ 'debit_account_id' ],
                ':amount' => $data[ 'amount' ],
                ':user_id' => $data[ 'user_id' ]
            ] );

            $journalCharges = "
            INSERT INTO tbl_journal_entries
            (entry_date, debit_account_id, charges, user_id, action)
            VALUES
            (NOW(), :debit, :charges ,:user_id, 'Transfer Fee')
        ";

            $stmt = $this->conn->prepare( $journalCharges );
            $stmt->execute( [
                ':debit'       => $data[ 'debit_account_id' ],   //DEBITED
                ':charges' =>$data[ 'charges' ],
                ':user_id' => $data[ 'user_id' ]
            ] );

            // JOURNAL ENTRY ( Credit )
            $journalCredit = "INSERT INTO tbl_journal_entries (entry_date, credit_account_id, amount, user_id, action) VALUES (NOW(), :credit, :amount, :user_id, 'CREDIT')";
            $stmt4 = $this->conn->prepare( $journalCredit );
            $stmt4->execute( [
                ':credit' => $data[ 'credit_account_id' ],
                ':amount' => $data[ 'amount' ],
                ':user_id' => $data[ 'user_id' ]
            ] );

            $this->conn->commit();
            return true;

        } catch ( \PDOException $e ) {
            $this->conn->rollBack();
            return false;
        }
    }

}
