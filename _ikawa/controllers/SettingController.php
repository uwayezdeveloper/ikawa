<?php
namespace Controllers;

// Include dependencies
require_once __DIR__ . '/../models/Setting.php';
require_once __DIR__ . '/../config/Response.php';

use Models\Setting;
use Config\Response;

class SettingController {
    private $settingModel;

    public function __construct() {
        $this->settingModel = new Setting();
    }

    public function getAllRoles() {
        $setting = $this->settingModel->getRoles();

        if ( $setting !== false ) {
            Response::success( 'Roles retrieved successfully!', $setting );
        } else {
            Response::error( 'Failed to retrieve users', 500 );
        }
    }

    public function getAllUnits() {
        $units = $this->settingModel->getUnits();

        if ( $units !== false ) {
            Response::success( 'Units retrieved successfully!', $units );
        } else {
            Response::error( 'Failed to retrieve users', 500 );
        }
    }

    public function createHeadQuater() {
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
            'location_name'
        ];

        foreach ( $required as $field ) {
            if ( empty( $input[ $field ] ) ) {
                Response::error( "Missing field: {$field}", 400 );
                return;
            }
        }

        $location_name = trim( $input[ 'location_name' ] );
        //DUPLICATE CHECK
        $duplicate = $this->settingModel->existsHeadQuarter( $location_name );

        if ( $duplicate !== null ) {
            Response::error(
                ucfirst( $duplicate ) . ' already exists',
                409
            );
            return;
        }

        $data = [
            'location_name' => trim( $input[ 'location_name' ] ),
            'description'  => trim( $input[ 'description' ] ),
            'type'=>trim( $input[ 'type' ] )

        ];

        if ( $this->settingModel->createHeadQuater( $data ) ) {
            Response::success( 'Station created successfully', [
                'location_name' => $location_name
            ] );
        } else {
            Response::error( 'Failed to create user', 500 );
        }
    }

    public function UpdateLocation() {
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
            'location_name'
        ];

        foreach ( $required as $field ) {
            if ( empty( $input[ $field ] ) ) {
                Response::error( "Missing field: {$field}", 400 );
                return;
            }
        }

        $location_name = trim( $input[ 'location_name' ] );
        $loc_id   = trim( $input[ 'loc_id' ] );

        //DUPLICATE CHECK
        $duplicate = $this->settingModel->existsStation( $location_name, $loc_id );

        if ( $duplicate !== null ) {
            Response::error(
                ucfirst( $duplicate ) . ' already exists',
                409
            );
            return;
        }

        $data = [
            'location_name' =>$location_name,
            'description'  => trim( $input[ 'description' ] ),
            'type'  => trim( $input[ 'type' ] ),
            'loc_id'      => $loc_id
        ];

        if ( $this->settingModel->updateLocat( $data ) ) {
            Response::success( 'Station updated successfully', [
                'location_name' => $location_name,
                'loc_id' => $loc_id
            ] );
        } else {
            Response::error( 'Failed to update role', 500 );
        }
    }

    public function getAllLocation() {
        $setting = $this->settingModel->getLocation();

        if ( $setting !== false ) {
            Response::success( 'Location retrieved successfully!', $setting );
        } else {
            Response::error( 'Failed to retrieve users', 500 );
        }
    }

    public function CreateRole() {
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
            'role_name'
        ];

        foreach ( $required as $field ) {
            if ( empty( $input[ $field ] ) ) {
                Response::error( "Missing field: {$field}", 400 );
                return;
            }
        }

        $role_name = trim( $input[ 'role_name' ] );
        //DUPLICATE CHECK
        $duplicate = $this->settingModel->exists( $role_name );

        if ( $duplicate !== null ) {
            Response::error(
                ucfirst( $role_name ) . ' already exists',
                409
            );
            return;
        }

        $data = [
            'role_name' => trim( $input[ 'role_name' ] ),
            'description'  => trim( $input[ 'description' ] )
        ];

        if ( $this->settingModel->createRole( $data ) ) {
            Response::success( 'Role created successfully', [
                'role_name' => $role_name
            ] );
        } else {
            Response::error( 'Failed to create user', 500 );
        }
    }

    public function UpdateRole() {
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
            'role_name', 'role_id'
        ];

        foreach ( $required as $field ) {
            if ( empty( $input[ $field ] ) ) {
                Response::error( "Missing field: {$field}", 400 );
                return;
            }
        }

        $role_name = trim( $input[ 'role_name' ] );
        $role_id   = trim( $input[ 'role_id' ] );

        //DUPLICATE CHECK
        $duplicate = $this->settingModel->existsUpdate( $role_name, $role_id );

        if ( $duplicate !== null ) {
            Response::error(
                ucfirst( $duplicate ) . ' already exists',
                409
            );
            return;
        }

        $data = [
            'role_name' =>$role_name,
            'description'  => trim( $input[ 'description' ] ),
            'role_id'      => $role_id
        ];

        if ( $this->settingModel->updateRole( $data ) ) {
            Response::success( 'Role updated successfully', [
                'role_name' => $role_name,
                'role_id' => $role_id
            ] );
        } else {
            Response::error( 'Failed to update role', 500 );
        }
    }

    public function CreateCompany() {
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
            'cpy_full_name', 'phone'
        ];

        foreach ( $required as $field ) {
            if ( empty( $input[ $field ] ) ) {
                Response::error( "Missing field: {$field}", 400 );
                return;
            }
        }

        $cpy_full_name = trim( $input[ 'cpy_full_name' ] );
        $phone = trim( $input[ 'phone' ] );
        $email = trim( $input[ 'email' ] );
        //DUPLICATE CHECK
        $duplicate = $this->settingModel->existscompany( $cpy_full_name, $phone, $email );

        if ( $duplicate !== null ) {
            Response::error(
                ucfirst( $duplicate ) . ' already exists',
                409
            );
            return;
        }

        $data = [
            'cpy_full_name' => $cpy_full_name,
            'cpy_short_name'  => trim( $input[ 'cpy_short_name' ] ),
            'phone'  =>$phone,
            'email'  => $email,
            'address'  => trim( $input[ 'address' ] )

        ];

        if ( $this->settingModel->createCompany( $data ) ) {
            Response::success( 'Company created successfully', [
                'cpy_full_name' => $cpy_full_name
            ] );
        } else {
            Response::error( 'Failed to create user', 500 );
        }
    }

    public function getCompanyInfo() {
        $company = $this->settingModel->getCompany();

        if ( $company !== false ) {
            Response::success( 'Company retrieved successfully!', $company );
        } else {
            Response::error( 'Failed to retrieve users', 500 );
        }
    }

    public function UpdateCompanyData() {
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
            'cpy_full_name', 'phone'
        ];

        foreach ( $required as $field ) {
            if ( empty( $input[ $field ] ) ) {
                Response::error( "Missing field: {$field}", 400 );
                return;
            }
        }

        $cpy_full_name = trim( $input[ 'cpy_full_name' ] );
        $phone = trim( $input[ 'phone' ] );
        $email = trim( $input[ 'email' ] );
        $cpy_id   = trim( $input[ 'cpy_id' ] );
        //DUPLICATE CHECK
        $duplicate = $this->settingModel->existsCompanyUpdate( $cpy_full_name, $phone, $email, $cpy_id );

        if ( $duplicate !== null ) {
            Response::error(
                ucfirst( $duplicate ) . ' already exists',
                409
            );
            return;
        }

        $data = [
            'cpy_full_name' => $cpy_full_name,
            'cpy_short_name'  => trim( $input[ 'cpy_short_name' ] ),
            'phone'  =>$phone,
            'email'  => $email,
            'address'  => trim( $input[ 'address' ] ),
            'cpy_id'      => $cpy_id
        ];

        if ( $this->settingModel->updateCompany( $data ) ) {
            Response::success( 'Company updated successfully', [
                'cpy_full_name' =>$cpy_full_name,
                'cpy_id' => $cpy_id
            ] );
        } else {
            Response::error( 'Failed to update role', 500 );
        }
    }
    //payments mode

    public function getPaymentModes() {
        $paymentModes = $this->settingModel->getPaymentModes();

        if ( $paymentModes !== false ) {
            Response::success( 'Payment modes retrieved successfully!', $paymentModes );
        } else {
            Response::error( 'Failed to retrieve payment modes', 500 );
        }
    }
    
    // access menus
    
    public function getAllMenus() {
        $menus = $this->settingModel->getMenus();

        if ( $menus !== false ) {
            Response::success( 'Menus retrieved successfully!', $menus );
        } else {
            Response::error( 'Failed to retrieve menus', 500 );
        }
    }

    public function createAccessUsers() {
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

        $required = [ 'role_id', 'menu_ids' ];

        foreach ( $required as $field ) {
            if ( empty( $input[ $field ] ) ) {
                Response::error( "Missing field: {$field}", 400 );
                return;
            }
        }

        $roleId = trim( $input[ 'role_id' ] );
        $menuIds = $input[ 'menu_ids' ];
        $roleId = ( int )$roleId;

        // Validate menu_ids is an array
        if ( !is_array( $menuIds ) || empty( $menuIds ) ) {
            Response::error( 'menu must be a non-empty', 400 );
            return;
        }

        // Validate each menu_id is numeric and convert to int
        $uniqueMenuIds = [];
        foreach ( $menuIds as $menuId ) {
            $menuId = trim( $menuId );
            if ( !is_numeric( $menuId ) ) {
                Response::error( 'Invalid menu value', 400 );
                return;
            }
            $menuId = ( int )$menuId;

            // Add to unique array to avoid duplicates in same request
            if ( !in_array( $menuId, $uniqueMenuIds ) ) {
                $uniqueMenuIds[] = $menuId;
            }
        }

        // Check for existing permissions and filter them out
        $existingMenus = $this->settingModel->getExistingRoleMenus( $roleId, $uniqueMenuIds );

        // Filter out menus that already exist
        $newMenuIds = array_diff( $uniqueMenuIds, $existingMenus );

        if ( empty( $newMenuIds ) ) {
            Response::error( 'All selected menus already have permissions for this role', 400 );
            return;
        }

        $data = [
            'role_id' => $roleId,
            'menu_ids' => $newMenuIds
        ];

        if ( $this->settingModel->createAccessUsers( $data ) ) {
            Response::success( 'Access permissions created successfully', [
                'role_id' => $roleId,
                'total_menus_selected' => count( $uniqueMenuIds ),
                'new_permissions_added' => count( $newMenuIds ),
                'existing_menus_skipped' => count( $existingMenus )
            ] );
        } else {
            Response::error( 'Failed to create access permissions', 500 );
        }
    }

    public function getAllAccessUsers() {
        $accessusersall = $this->settingModel->getAccessUsers();

        if ( $accessusersall !== false ) {
            Response::success( 'Menus retrieved successfully!', $accessusersall );
        } else {
            Response::error( 'Failed to retrieve menus', 500 );
        }
    }

    public function removeAccessRole( $menu_id ) {
        if ( $this->settingModel->removeAccessfromRole( $menu_id ) ) {
            Response::success( 'Menu removed successfully' );
        } else {
            Response::error( 'Failed to remove menu', 500 );
        }
    }

}
