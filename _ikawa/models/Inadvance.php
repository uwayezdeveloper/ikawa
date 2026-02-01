<?php
namespace Models;

require_once __DIR__ . '/../config/Database.php';

use Config\Database;
use PDO;
use \PDOException;

class Inadvance {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Model fix

    public function getAllSuppliers( $data ) {
        try {
            $query = 'SELECT * FROM tbl_suppliers where status="active" and type=:type';
            $stmt = $this->conn->prepare( $query );
            $stmt->execute( [ ':type' => $data[ 'request_type' ] ] );
            return $stmt->fetchAll( \PDO::FETCH_ASSOC );
        } catch ( \PDOException $e ) {
            return false;
        }
    }

    // public function existsActiveAdvance( string $destination_id ): ?string {
    //     $sql = "
    //     SELECT 
    //         CASE
    //             WHEN status IN ('pending', 'approved', 'outstanding', 'partially_cleared') 
    //             THEN 'There is an active advance that needs to be settled first'
    //         END AS field
    //     FROM tbl_advances
    //     WHERE destination_id = :destination_id 
    //     AND status IN ('pending', 'approved', 'outstanding', 'partially_cleared')
    //     LIMIT 1
    // ";

    //     $stmt = $this->conn->prepare( $sql );
    //     $stmt->execute( [ ':destination_id' => $destination_id ] );
    //     $row = $stmt->fetch( \PDO::FETCH_ASSOC );

    //     return $row[ 'field' ] ?? null;
    // }

    public function createInAdvance( array $data ): bool {
        try {
            $sql = "
                INSERT INTO tbl_advances (
                    destination_id,
                    station_id,
                    created_by,
                    amount,
                    created_at,
                    payment_on,
                    reason,
                    status
                ) VALUES (
                    :destination_id,
                    :station_id,
                    :created_by,
                    :amount,
                    :created_at,
                    :payment_on,
                    :reason,
                    :status
                )
            ";

            $stmt = $this->conn->prepare( $sql );

            return $stmt->execute( [
                ':destination_id' => $data[ 'destination_id' ],
                ':station_id'  => $data[ 'station_id' ],
                ':created_by'  => $data[ 'created_by' ],
                ':amount'  => $data[ 'amount' ],
                ':created_at'=>$data[ 'created_at' ],
                ':payment_on'=>$data[ 'payment_on' ],
                ':reason'=>$data[ 'reason' ],
                ':status'=>$data[ 'status' ]

            ] );
        } catch ( \PDOException $e ) {
            return false;
        }
    }

    public function getAllAdvanceLists( $loc_id ) {
        try {
            $query = 'SELECT sp.*,adv.* FROM tbl_advances adv inner join tbl_suppliers sp
            on sp.sup_id=adv.destination_id where station_id=:station_id order by adv.adv_id DESC';
            $stmt = $this->conn->prepare( $query );
            $stmt->execute( [ ':station_id'=>$loc_id ] );
            return $stmt->fetchAll( \PDO::FETCH_ASSOC );

        } catch ( \PDOException $e ) {
            return false;
        }
    }

    public function getAllAdvanceListsPending( $loc_id ) {
        try {
            $query = 'SELECT sp.*,adv.*,user.first_name,user.last_name FROM tbl_advances adv inner join tbl_suppliers sp
            on sp.sup_id=adv.destination_id
            inner join tbl_users user on user.user_id=adv.created_by where adv.status in ("pending") and adv.station_id=:station_id';
            $stmt = $this->conn->prepare( $query );
            $stmt->execute( [ ':station_id' => $loc_id ] );
            return $stmt->fetchAll( \PDO::FETCH_ASSOC );

        } catch ( \PDOException $e ) {
            return false;
        }
    }

    public function rejectInAdvance( array $data ): bool {

        try {
            $sql = "UPDATE tbl_advances SET approved_by=:approved_by,status=:status,rejected_reason=:rejected_reason
            where adv_id=:adv_id";

            $stmt = $this->conn->prepare( $sql );

            return $stmt->execute( [
                ':approved_by' => $data[ 'approved_by' ],
                ':status'  => $data[ 'status' ],
                ':rejected_reason'  => $data[ 'rejected_reason' ],
                ':adv_id'  => $data[ 'adv_id' ]
            ] );
        } catch ( \PDOException $e ) {
            return false;
        }
    }

    public function updateRequestAdvance( $data ) {
        $sql = 'UPDATE  tbl_advances SET status = "approved",approved_on=NOW(),approved_by=:approved_by WHERE adv_id = :adv_id';
        $stmt = $this->conn->prepare( $sql );
        return $stmt->execute( [
            ':adv_id'      => $data[ 'adv_id' ],
            ':approved_by'=> $data[ 'user_id' ]
        ] );
    }

    public function getAllAdvanceListsApproved( $loc_id ) {
        try {
            $query = 'SELECT sp.*,adv.*,user.first_name,user.last_name FROM tbl_advances adv inner join tbl_suppliers sp
            on sp.sup_id=adv.destination_id
            inner join tbl_users user on user.user_id=adv.approved_by where adv.status in ("approved") and adv.station_id=:station_id';
            $stmt = $this->conn->prepare( $query );
            $stmt->execute( [ ':station_id' =>$loc_id ] );
            return $stmt->fetchAll( \PDO::FETCH_ASSOC );

        } catch ( \PDOException $e ) {
            return false;
        }
    }

    public function disburseInAdvance( array $data ): bool {
        try {
            $this->conn->beginTransaction();

            // Update advance status
            $updateAdvance = "UPDATE tbl_advances SET 
                         status = 'outstanding', 
                         disburse_date = NOW(),
                         disburse_by = :user_id 
                         WHERE adv_id = :adv_id";
            $stmtAdvance = $this->conn->prepare( $updateAdvance );
            $stmtAdvance->execute( [
                ':user_id' => $data[ 'user_id' ],
                ':adv_id' => $data[ 'adv_id' ]
            ] );

            // Calculate total amount across all accounts
            $totalAmount = 0;
            foreach ( $data[ 'disbursements' ] as $disbursement ) {
                $totalAmount += $disbursement[ 'amount' ];
                // Sum only amounts, not charges
            }

            // 1. Insert amount
            $insertDisburse = "INSERT INTO tbl_advance_disbursement 
                  (adv_id,station_id, amount, receipt_account, additional_info, disbursed_by) 
                  VALUES (:adv_id,:station_id, :amount, :receipt_account, :additional_info, :user_id)";
            $stmt1 = $this->conn->prepare( $insertDisburse );
            $stmt1->execute( [
                ':adv_id' => $data[ 'adv_id' ],
                ':station_id' =>$data[ 'station_id' ],
                ':amount' => $totalAmount,
                ':receipt_account' => $data[ 'receipt_account' ],
                ':additional_info' => $data[ 'additional_info' ],
                ':user_id' => $data[ 'user_id' ]
            ] );

            $disburseId = $this->conn->lastInsertId();

            // 2. Process each account
            foreach ( $data[ 'disbursements' ] as $disbursement ) {
                $totalDebit = $disbursement[ 'amount' ] + $disbursement[ 'charge' ];

                // Debit from account
                $debitAccount = 'UPDATE tbl_accounts SET balance = balance - :total WHERE acc_id = :account_id';
                $stmt2 = $this->conn->prepare( $debitAccount );
                $stmt2->execute( [
                    ':total' => $totalDebit,
                    ':account_id' => $disbursement[ 'account_id' ]
                ] );

                // Journal entry for amount
                $journalAmount = "INSERT INTO tbl_journal_entries 
                    (entry_date, debit_account_id, amount, reference_id, user_id,action) 
                    VALUES (NOW(), :account_id, :amount, :ref_id, :user_id, 'ADVANCE_DISBURSEMENT')";
                $stmt3 = $this->conn->prepare( $journalAmount );
                $stmt3->execute( [
                    ':account_id' => $disbursement[ 'account_id' ],
                    ':amount' => $disbursement[ 'amount' ],
                    ':ref_id' => $disburseId,
                    ':user_id' => $data[ 'user_id' ]

                ] );

                $journalCharge = "INSERT INTO tbl_journal_entries 
                        (entry_date, debit_account_id, charges, reference_id, user_id,action) 
                        VALUES (NOW(), :account_id, :charge,:ref_id,:user_id,'ADVANCE_CHARGE')";
                $stmt4 = $this->conn->prepare( $journalCharge );
                $stmt4->execute( [
                    ':account_id' => $disbursement[ 'account_id' ],
                    ':charge' => $disbursement[ 'charge' ],
                    ':ref_id' => $disburseId,
                    ':user_id' => $data[ 'user_id' ]

                ] );

            }

            $this->conn->commit();
            return true;

        } catch ( \PDOException $e ) {
            $this->conn->rollBack();
            error_log( 'Disburse Error: ' . $e->getMessage() );
            return false;
        }
    }

}
