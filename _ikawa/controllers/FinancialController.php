<?php
namespace Controllers;
// Include dependencies
require_once __DIR__ . '/../models/Financial.php';
require_once __DIR__ . '/../config/Response.php';

use Models\Financial;
use Config\Response;

class FinancialController {
    private $financialModel;

    public function __construct() {
        $this->financialModel = new Financial();
    }

    public function CreateTransfers() {
        // POST METHOD ONLY
        if ( $_SERVER[ 'REQUEST_METHOD' ] !== 'POST' ) {
            Response::error( 'Invalid request method', 405 );
            return;
        }

        // READ JSON INPUT
        $input = json_decode( file_get_contents( 'php://input' ), true );

        if ( !is_array( $input ) ) {
            Response::error( 'Invalid JSON payload', 400 );
            return;
        }

        $required = [
            'debit_account_id', 'credit_account_id'
        ];

        foreach ( $required as $field ) {
            if ( empty( $input[ $field ] ) ) {
                Response::error( "Missing field: {$field}", 400 );
                return;
            }
        }

        $debit_account_id = trim( $input[ 'debit_account_id' ] );
        $credit_account_id = trim( $input[ 'credit_account_id' ] );
        $amount = trim( $input[ 'amount_to_transfer' ] );
        $charges = trim( $input[ 'trans_charges' ] ?? '0' );
        $total = $amount+$charges;
        $user_id = trim( $input[ 'user_id' ] );
        //DUPLICATE CHECK
        $checkbalance = $this->financialModel->checkbalance( $debit_account_id, $total );

        if ( $checkbalance !== null ) {
            Response::error(
                ucfirst( $checkbalance ),
                409
            );
            return;
        }

        $data = [
            'debit_account_id' => $debit_account_id,
            'credit_account_id'  => $credit_account_id,
            'amount'=>$amount,
            'charges'=>$charges,
            'total'=>$total,
            'user_id'=>$user_id

        ];

        if ( $this->financialModel->CreateTransfer( $data ) ) {
            Response::success( 'Transfer created successfully', [
                'amount' => $amount,
                'debit_account_id'=>$debit_account_id,
                'credit_account_id'=>$credit_account_id,

            ] );
        } else {
            Response::error( 'Failed to create transfer', 500 );
        }
    }

}
