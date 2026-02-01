<?php
namespace Models;

require_once __DIR__ . '/../config/Database.php';

use Config\Database;
use PDO;
use \PDOException;

class Account
 {
    private $conn;

    public function __construct()
 {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAllAccounts()
 {
        try {
            $query = 'SELECT a.*, pm.Mode_names, l.location_name, l.description 
                      FROM tbl_accounts a 
                      JOIN tbl_paymentmodes pm ON a.mode_id = pm.Mode_id 
                      LEFT JOIN tbl_location l ON a.st_id = l.loc_id 
                      WHERE a.status IN (0, 1) 
                      ORDER BY a.acc_id DESC';
            $stmt = $this->conn->prepare( $query );
            $stmt->execute();
            return $stmt->fetchAll( \PDO::FETCH_ASSOC );
        } catch ( \PDOException $e ) {
            return false;
        }
    }

    public function getAccountsByLocation( $st_id )
 {
        try {
            $query = 'SELECT a.*, pm.Mode_names, l.location_name, l.description 
                      FROM tbl_accounts a 
                      JOIN tbl_paymentmodes pm ON a.mode_id = pm.Mode_id 
                      LEFT JOIN tbl_location l ON a.st_id = l.loc_id 
                      WHERE a.status IN (0, 1) AND a.st_id = :st_id 
                      ORDER BY a.acc_id DESC';
            $stmt = $this->conn->prepare( $query );
            $stmt->execute( [ 'st_id' => $st_id ] );
            return $stmt->fetchAll( \PDO::FETCH_ASSOC );
        } catch ( \PDOException $e ) {
            error_log( 'Error getting accounts by location: ' . $e->getMessage() );
            return false;
        }
    }

    public function getAccountById( $acc_id )
 {
        try {
            $query = 'SELECT a.*, pm.Mode_names, l.location_name, l.description 
                      FROM tbl_accounts a 
                      JOIN tbl_paymentmodes pm ON a.mode_id = pm.Mode_id 
                      LEFT JOIN tbl_location l ON a.st_id = l.loc_id 
                      WHERE a.acc_id = :acc_id';
            $stmt = $this->conn->prepare( $query );
            $stmt->execute( [ 'acc_id' => $acc_id ] );
            return $stmt->fetch( \PDO::FETCH_ASSOC );
        } catch ( \PDOException $e ) {
            return false;
        }
    }

    public function accountExists( $acc_name )
 {
        try {
            $query = 'SELECT acc_name FROM tbl_accounts WHERE acc_name = :acc_name LIMIT 1';
            $stmt = $this->conn->prepare( $query );
            $stmt->execute( [ 'acc_name' => $acc_name ] );
            $result = $stmt->fetch( \PDO::FETCH_ASSOC );
            return $result ? $result[ 'acc_name' ] : null;
        } catch ( \PDOException $e ) {
            return null;
        }
    }

    public function referenceNumberExists( $acc_reference_num, $exclude_acc_id = null )
 {
        try {
            if ( $exclude_acc_id ) {
                $query = 'SELECT acc_id FROM tbl_accounts WHERE acc_reference_num = :acc_reference_num AND acc_id != :exclude_acc_id LIMIT 1';
                $stmt = $this->conn->prepare( $query );
                $stmt->execute( [
                    'acc_reference_num' => $acc_reference_num,
                    'exclude_acc_id' => $exclude_acc_id
                ] );
            } else {
                $query = 'SELECT acc_id FROM tbl_accounts WHERE acc_reference_num = :acc_reference_num LIMIT 1';
                $stmt = $this->conn->prepare( $query );
                $stmt->execute( [ 'acc_reference_num' => $acc_reference_num ] );
            }
            $result = $stmt->fetch( \PDO::FETCH_ASSOC );
            return $result !== false;
        } catch ( \PDOException $e ) {
            error_log( 'Error checking reference number: ' . $e->getMessage() );
            return true;
            // Return true on error to prevent duplicate inserts
        }
    }

    public function createAccount( $data )
 {
        try {
            $query = 'INSERT INTO tbl_accounts (mode_id, acc_name, acc_reference_num, st_id, balance, status) 
                      VALUES (:mode_id, :acc_name, :acc_reference_num, :st_id, 0, 1)';
            $stmt = $this->conn->prepare( $query );
            return $stmt->execute( $data );
        } catch ( \PDOException $e ) {
            return false;
        }
    }

    public function updateAccount( $data )
 {
        try {
            $query = 'UPDATE tbl_accounts 
                      SET mode_id = :mode_id, acc_name = :acc_name, acc_reference_num = :acc_reference_num, st_id = :st_id 
                      WHERE acc_id = :acc_id';
            $stmt = $this->conn->prepare( $query );
            return $stmt->execute( $data );
        } catch ( \PDOException $e ) {
            return false;
        }
    }

    public function deleteAccount( $acc_id )
 {
        try {
            $query = 'UPDATE tbl_accounts SET status = 0 WHERE acc_id = :acc_id';
            $stmt = $this->conn->prepare( $query );
            return $stmt->execute( [ 'acc_id' => $acc_id ] );
        } catch ( \PDOException $e ) {
            return false;
        }
    }

    public function reactivateAccount( $acc_id )
 {
        try {
            $query = 'UPDATE tbl_accounts SET status = 1 WHERE acc_id = :acc_id';
            $stmt = $this->conn->prepare( $query );
            return $stmt->execute( [ 'acc_id' => $acc_id ] );
        } catch ( \PDOException $e ) {
            error_log( 'Error reactivating account: ' . $e->getMessage() );
            return false;
        }
    }

    /**
    * Get accounts by payment mode ID
    */

    public function getAccountsByPaymentMode( $mode_id )
 {
        try {
            $query = 'SELECT a.*, pm.Mode_names, l.location_name, l.description 
                      FROM tbl_accounts a 
                      JOIN tbl_paymentmodes pm ON a.mode_id = pm.Mode_id 
                      LEFT JOIN tbl_location l ON a.st_id = l.loc_id 
                      WHERE a.mode_id = :mode_id AND a.status = 1 
                      ORDER BY a.acc_name ASC';
            $stmt = $this->conn->prepare( $query );
            $stmt->execute( [ 'mode_id' => $mode_id ] );
            return $stmt->fetchAll( \PDO::FETCH_ASSOC );
        } catch ( \PDOException $e ) {
            error_log( 'Error getting accounts by payment mode: ' . $e->getMessage() );
            return false;
        }
    }

    /**
    * Check if account has sufficient balance
    */

    public function checkBalance( $acc_id, $required_amount )
 {
        try {
            $query = 'SELECT balance FROM tbl_accounts WHERE acc_id = :acc_id AND status = 1';
            $stmt = $this->conn->prepare( $query );
            $stmt->execute( [ 'acc_id' => $acc_id ] );
            $result = $stmt->fetch( \PDO::FETCH_ASSOC );

            if ( $result ) {
                return $result[ 'balance' ] >= $required_amount;
            }

            return false;
        } catch ( \PDOException $e ) {
            error_log( 'Error checking account balance: ' . $e->getMessage() );
            return false;
        }
    }

    /**
    * Update account balance ( deduct amount )
    */

    public function updateBalance( $acc_id, $amount )
 {
        try {
            $query = 'UPDATE tbl_accounts 
                      SET balance = balance - :amount 
                      WHERE acc_id = :acc_id AND status = 1';
            $stmt = $this->conn->prepare( $query );
            return $stmt->execute( [
                'amount' => $amount,
                'acc_id' => $acc_id
            ] );
        } catch ( \PDOException $e ) {
            error_log( 'Error updating account balance: ' . $e->getMessage() );
            return false;
        }
    }

    /**
    * Get account balance
    */

    public function getBalance( $acc_id )
 {
        try {
            $query = 'SELECT balance FROM tbl_accounts WHERE acc_id = :acc_id AND status = 1';
            $stmt = $this->conn->prepare( $query );
            $stmt->execute( [ 'acc_id' => $acc_id ] );
            $result = $stmt->fetch( \PDO::FETCH_ASSOC );

            return $result ? $result[ 'balance' ] : 0;
        } catch ( \PDOException $e ) {
            error_log( 'Error getting account balance: ' . $e->getMessage() );
            return 0;
        }
    }

    /**
    * Refund balance ( add amount back to account )
    */

    public function refundBalance( $acc_id, $amount )
 {
        try {
            $query = 'UPDATE tbl_accounts 
                      SET balance = balance + :amount 
                      WHERE acc_id = :acc_id';
            $stmt = $this->conn->prepare( $query );
            return $stmt->execute( [
                'amount' => $amount,
                'acc_id' => $acc_id
            ] );
        } catch ( \PDOException $e ) {
            error_log( 'Error refunding account balance: ' . $e->getMessage() );
            return false;
        }
    }
}
