<?php
namespace Controllers;

// Include dependencies
require_once __DIR__ . '/../models/Inventory.php';
require_once __DIR__ . '/../config/Response.php';

use Models\Inventory;
use Config\Response;

class InventoryController {
    private $inventoryModel;

    public function __construct() {
        $this->inventoryModel = new Inventory();
    }

    public function createSupplier() {
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
            'phone', 'full_name'
        ];

        foreach ( $required as $field ) {
            if ( empty( $input[ $field ] ) ) {
                Response::error( "Missing field: {$field}", 400 );
                return;
            }
        }

        $full_name = trim( $input[ 'full_name' ] );
        $phone = trim( $input[ 'phone' ] );
        $email = trim( $input[ 'email' ] );
        //DUPLICATE CHECK
        $duplicate = $this->inventoryModel->exists( $phone, $email );

        if ( $duplicate !== null ) {
            Response::error(
                ucfirst( $duplicate ) . ' already exists',
                409
            );
            return;
        }

        $data = [
            'full_name' => $full_name,
            'email'  => $email,
            'phone'=>$phone,
            'address'=>trim( $input[ 'address' ] ),
            'type'=>trim( $input[ 'type' ] )

        ];

        if ( $this->inventoryModel->createSupp( $data ) ) {
            Response::success( 'Supplier created successfully', [
                'full_name' => $full_name,
                'phone'=>$phone
            ] );
        } else {
            Response::error( 'Failed to create user', 500 );
        }
    }

    public function SupplierList() {
        $suppliers = $this->inventoryModel->getSuppliers();

        if ( $suppliers !== false ) {
            Response::success( 'Suppliers retrieved successfully!', $suppliers );
        } else {
            Response::error( 'Failed to retrieve suppliers', 500 );
        }
    }

    public function UpdateSupplier() {
        // POST METHOD ONLY
        if ( $_SERVER[ 'REQUEST_METHOD' ] !== 'PUT' ) {
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
            'full_name', 'sup_id', 'phone'
        ];

        foreach ( $required as $field ) {
            if ( empty( $input[ $field ] ) ) {
                Response::error( "Missing field: {$field}", 400 );
                return;
            }
        }

        $email = trim( $input[ 'email' ] );
        $phone   = trim( $input[ 'phone' ] );
        $sup_id   = trim( $input[ 'sup_id' ] );

        //DUPLICATE CHECK
        $duplicate = $this->inventoryModel->existsUpdate( $email, $phone, $sup_id );

        if ( $duplicate !== null ) {
            Response::error(
                ucfirst( $duplicate ) . ' already exists',
                409
            );
            return;
        }

        $data = [
            'full_name'=> trim( $input[ 'full_name' ] ),
            'email'=> $email,
            'phone'=>$phone,
            'address'=>trim( $input[ 'address' ] ),
            'type'=>trim( $input[ 'type' ] ),
            'sup_id' =>$sup_id
        ];

        if ( $this->inventoryModel->updateSuppl( $data ) ) {
            Response::success( 'Supplier updated successfully', [
                'full_name' => trim( $input[ 'full_name' ] ),
                'phone' => $phone
            ] );
        } else {
            Response::error( 'Failed to update role', 500 );
        }
    }

    public function deleteSupplier( $sup_id ) {
        if ( $this->inventoryModel->removeSupplier( $sup_id ) ) {
            Response::success( 'Supplier deleted successfully' );
        } else {
            Response::error( 'Failed to delete user', 500 );
        }
    }
    // countries

    public function CountryLists() {
        $countries = $this->inventoryModel->getCountries();

        if ( $countries !== false ) {
            Response::success( 'Countries retrieved successfully!', $countries );
        } else {
            Response::error( 'Failed to retrieve countries', 500 );
        }
    }

    public function CreateClient() {
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
            'full_name', 'phone'
        ];

        foreach ( $required as $field ) {
            if ( empty( $input[ $field ] ) ) {
                Response::error( "Missing field: {$field}", 400 );
                return;
            }
        }

        $full_name = trim( $input[ 'full_name' ] );
        $phone = trim( $input[ 'phone' ] );
        $email = trim( $input[ 'email' ] );
        //DUPLICATE CHECK
        $duplicate = $this->inventoryModel->existsClient( $phone, $email );

        if ( $duplicate !== null ) {
            Response::error(
                ucfirst( $duplicate ) . ' already exists',
                409
            );
            return;
        }

        $data = [
            'full_name' => $full_name,
            'email'  => $email,
            'phone'=>$phone,
            'country_id'=>trim( $input[ 'country_id' ] ),
            'city'=>trim( $input[ 'city' ] ),
            'address'=>trim( $input[ 'address' ] ),
            'client_type'=>trim( $input[ 'client_type' ] )
        ];

        if ( $this->inventoryModel->createClnt( $data ) ) {
            Response::success( 'Client created successfully', [
                'full_name' => $full_name,
                'phone'=>$phone
            ] );
        } else {
            Response::error( 'Failed to create user', 500 );
        }
    }

    public function ClientsList() {
        $clients = $this->inventoryModel->getClients();

        if ( $clients !== false ) {
            Response::success( 'Clients retrieved successfully!', $clients );
        } else {
            Response::error( 'Failed to retrieve clients', 500 );
        }
    }

    public function ClientUpdate() {
        // POST METHOD ONLY
        if ( $_SERVER[ 'REQUEST_METHOD' ] !== 'PUT' ) {
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
            'full_name', 'client_id', 'phone'
        ];

        foreach ( $required as $field ) {
            if ( empty( $input[ $field ] ) ) {
                Response::error( "Missing field: {$field}", 400 );
                return;
            }
        }

        $email = trim( $input[ 'email' ] );
        $phone   = trim( $input[ 'phone' ] );
        $client_id  = trim( $input[ 'client_id' ] );

        //DUPLICATE CHECK
        $duplicate = $this->inventoryModel->existsClientUpdate( $email, $phone, $client_id );

        if ( $duplicate !== null ) {
            Response::error(
                ucfirst( $duplicate ) . ' already exists',
                409
            );
            return;
        }

        $data = [
            'full_name'=> trim( $input[ 'full_name' ] ),
            'email'=> $email,
            'phone'=>$phone,
            'country_id'=>trim( $input[ 'country_id' ] ),
            'city'=>trim( $input[ 'city' ] ),
            'address'=>trim( $input[ 'address' ] ),
            'client_type'=>trim( $input[ 'client_type' ] ),
            'client_id' =>$client_id
        ];

        if ( $this->inventoryModel->updateCompanyClient( $data ) ) {
            Response::success( 'Client updated successfully', [
                'full_name' => trim( $input[ 'full_name' ] ),
                'phone' => $phone
            ] );
        } else {
            Response::error( 'Failed to update client', 500 );
        }
    }

    public function deleteClient( $client_id ) {
        if ( $this->inventoryModel->removeClient( $client_id ) ) {
            Response::success( 'Client deleted successfully' );
        } else {
            Response::error( 'Failed to delete user', 500 );
        }
    }

}
