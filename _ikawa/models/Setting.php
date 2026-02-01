<?php
namespace Models;

require_once __DIR__ . '/../config/Database.php';

use Config\Database;

class Setting {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getRoles() {
        try {
            $query = 'SELECT * FROM  tbl_roles where role_id !=1 ';
            $stmt = $this->conn->prepare( $query );
            $stmt->execute();
            return $stmt->fetchAll( \PDO::FETCH_ASSOC );

        } catch ( \PDOException $e ) {
            return false;
        }
    }

    public function getUnits() {
        try {
            $query = 'SELECT * FROM  tbl_units ';
            $stmt = $this->conn->prepare( $query );
            $stmt->execute();
            return $stmt->fetchAll( \PDO::FETCH_ASSOC );

        } catch ( \PDOException $e ) {
            return false;
        }
    }

    public function getLocation() {
        try {
            $query = 'SELECT * FROM  tbl_location';
            $stmt = $this->conn->prepare( $query );
            $stmt->execute();
            return $stmt->fetchAll( \PDO::FETCH_ASSOC );

        } catch ( \PDOException $e ) {
            return false;
        }
    }

    public function existsHeadQuarter( string $location_name ): ?string {
        $sql = "
            SELECT 
                CASE
                    WHEN location_name = :location_name THEN 'HeadQuarter'
                END AS field
            FROM tbl_location
            WHERE location_name = :location_name
            LIMIT 1
        ";

        $stmt = $this->conn->prepare( $sql );
        $stmt->execute( [
            ':location_name' => $location_name
        ] );

        $row = $stmt->fetch( \PDO::FETCH_ASSOC );

        return $row[ 'field' ] ?? null;
    }

    public function createHeadQuater( array $data ): bool {
        try {
            $sql = "
                INSERT INTO tbl_location (
                    location_name,
                    description,
                    type
                ) VALUES (
                    :location_name,
                    :description,
                    :type
                )
            ";

            $stmt = $this->conn->prepare( $sql );

            return $stmt->execute( [
                ':location_name' => $data[ 'location_name' ],
                ':description'  => $data[ 'description' ],
                ':type'  => $data[ 'type' ]
            ] );
        } catch ( \PDOException $e ) {
            return false;
        }
    }

    public function existsStation( string $location_name, string $loc_id ): ?string {
        $sql = "
            SELECT 
                CASE
                    WHEN location_name = :location_name THEN 'Station'
                END AS field
            FROM  tbl_location
            WHERE (location_name = :location_name) and loc_id!=:loc_id
            LIMIT 1
        ";

        $stmt = $this->conn->prepare( $sql );
        $stmt->execute( [
            ':location_name' => $location_name,
            ':loc_id'    => $loc_id
        ] );

        $row = $stmt->fetch( \PDO::FETCH_ASSOC );

        return $row[ 'field' ] ?? null;
    }

    public function updateLocat( array $data ): bool {

        try {
            $sql = "UPDATE tbl_location SET location_name=:location_name,description=:description,type=:type
            where loc_id=:loc_id";

            $stmt = $this->conn->prepare( $sql );

            return $stmt->execute( [
                ':location_name' => $data[ 'location_name' ],
                ':description'  => $data[ 'description' ],
                ':type'  => $data[ 'type' ],
                ':loc_id'   => $data[ 'loc_id' ]
            ] );
        } catch ( \PDOException $e ) {
            return false;
        }
    }

    public function exists( string $role_name ): ?string {
        $sql = "
            SELECT 
                CASE
                    WHEN role_name = :role_name THEN 'role_name'
                END AS field
            FROM tbl_roles
            WHERE role_name = :role_name
            LIMIT 1
        ";

        $stmt = $this->conn->prepare( $sql );
        $stmt->execute( [
            ':role_name' => $role_name
        ] );

        $row = $stmt->fetch( \PDO::FETCH_ASSOC );

        return $row[ 'field' ] ?? null;
    }

    public function createRole( array $data ): bool {
        try {
            $sql = "
                INSERT INTO tbl_roles (
                    role_name,
                    description
                ) VALUES (
                    :role_name,
                    :description
                )
            ";

            $stmt = $this->conn->prepare( $sql );

            return $stmt->execute( [
                ':role_name' => $data[ 'role_name' ],
                ':description'  => $data[ 'description' ]
            ] );
        } catch ( \PDOException $e ) {
            return false;
        }
    }

    public function existsUpdate( string $role_name, string $role_id ): ?string {
        $sql = "
            SELECT 
                CASE
                    WHEN role_name = :role_name THEN 'role_name'
                END AS field
            FROM  tbl_roles
            WHERE (role_name = :role_name) and role_id!=:role_id
            LIMIT 1
        ";

        $stmt = $this->conn->prepare( $sql );
        $stmt->execute( [
            ':role_name' => $role_name,
            ':role_id'    => $role_id
        ] );

        $row = $stmt->fetch( \PDO::FETCH_ASSOC );

        return $row[ 'field' ] ?? null;
    }

    public function updateRole( array $data ): bool {

        try {
            $sql = "UPDATE tbl_roles SET role_name=:role_name,description=:description
            where role_id=:role_id";

            $stmt = $this->conn->prepare( $sql );

            return $stmt->execute( [
                ':role_name' => $data[ 'role_name' ],
                ':description'  => $data[ 'description' ],
                ':role_id'   => $data[ 'role_id' ]
            ] );
        } catch ( \PDOException $e ) {
            return false;
        }
    }

    public function existscompany( string $cpy_full_name, string  $phone, string $email ): ?string {
        $sql = "
            SELECT 
                CASE
                    WHEN cpy_full_name = :cpy_full_name THEN 'Company Name'
                    WHEN phone = :phone THEN 'phone'
                    WHEN email = :email THEN 'email'

                END AS field
            FROM  tbl_company
            WHERE cpy_full_name = :cpy_full_name or phone = :phone or email = :email
            LIMIT 1
        ";

        $stmt = $this->conn->prepare( $sql );
        $stmt->execute( [
            ':cpy_full_name' => $cpy_full_name,
            ':phone' => $phone,
            ':email' => $email

        ] );

        $row = $stmt->fetch( \PDO::FETCH_ASSOC );

        return $row[ 'field' ] ?? null;
    }

    public function createCompany( array $data ): bool {
        try {
            $sql = "
                INSERT INTO  tbl_company (
                    cpy_full_name,
                    cpy_short_name,
                    phone,
                    email,
                    address

                ) VALUES (
                    :cpy_full_name,
                    :cpy_short_name,
                    :phone,
                    :email,
                    :address
                )
            ";

            $stmt = $this->conn->prepare( $sql );

            return $stmt->execute( [
                ':cpy_full_name' => $data[ 'cpy_full_name' ],
                ':cpy_short_name'  => $data[ 'cpy_short_name' ],
                ':phone'  => $data[ 'phone' ],
                ':email'  => $data[ 'email' ],
                ':address'  => $data[ 'address' ]

            ] );
        } catch ( \PDOException $e ) {
            return false;
        }
    }

    public function getCompany() {
        try {
            $query = 'SELECT * FROM  tbl_company';
            $stmt = $this->conn->prepare( $query );
            $stmt->execute();
            return $stmt->fetchAll( \PDO::FETCH_ASSOC );

        } catch ( \PDOException $e ) {
            return false;
        }
    }

    public function existsCompanyUpdate( string $cpy_full_name, string $phone, string $email, string $cpy_id ): ?string {
        $sql = "
            SELECT 
                CASE
                    WHEN cpy_full_name = :cpy_full_name THEN 'Company Full Name'
                    WHEN phone = :phone THEN 'Phone'
                    WHEN email = :email THEN 'Email'
                END AS field
            FROM  tbl_company
            WHERE (cpy_full_name = :cpy_full_name OR phone = :phone OR email = :email) and cpy_id!=:cpy_id
            LIMIT 1
        ";

        $stmt = $this->conn->prepare( $sql );
        $stmt->execute( [
            ':cpy_full_name' => $cpy_full_name,
            ':phone' => $phone,
            ':email' => $email,
            ':cpy_id'    => $cpy_id
        ] );

        $row = $stmt->fetch( \PDO::FETCH_ASSOC );

        return $row[ 'field' ] ?? null;
    }

    public function updateCompany( array $data ): bool {

        try {
            $sql = "UPDATE tbl_company SET cpy_full_name=:cpy_full_name,cpy_short_name=:cpy_short_name,
            phone=:phone,email=:email,address=:address
            where cpy_id =:cpy_id";

            $stmt = $this->conn->prepare( $sql );

            return $stmt->execute( [
                ':cpy_full_name' => $data[ 'cpy_full_name' ],
                ':cpy_short_name' => $data[ 'cpy_short_name' ],
                ':phone' => $data[ 'phone' ],
                ':email' => $data[ 'email' ],
                ':address'  => $data[ 'address' ],
                ':cpy_id'   => $data[ 'cpy_id' ]
            ] );
        } catch ( \PDOException $e ) {
            return false;
        }
    }

    // payment mode

    public function getPaymentModes() {
        try {
            $query = 'SELECT * FROM tbl_paymentmodes WHERE status = 1 ORDER BY Mode_id DESC';
            $stmt = $this->conn->prepare( $query );
            $stmt->execute();
            return $stmt->fetchAll( \PDO::FETCH_ASSOC );
        } catch ( \PDOException $e ) {
            return false;
        }
    }
    
    // access menus

    public function getMenus() {
        try {
            $query = 'SELECT * FROM menus WHERE is_active = 1';
            $stmt = $this->conn->prepare( $query );
            $stmt->execute();
            return $stmt->fetchAll( \PDO::FETCH_ASSOC );
        } catch ( \PDOException $e ) {
            return false;
        }
    }

    public function getExistingRoleMenus( $roleId, $menuIds ) {
        $placeholders = implode( ',', array_fill( 0, count( $menuIds ), '?' ) );
        $sql = "SELECT menu_id FROM role_menus 
            WHERE role_id = ? AND menu_id IN ($placeholders)";

        $stmt = $this->conn->prepare( $sql );

        // Bind parameters: first param is role_id, rest are menu_ids
        $params = array_merge( [ $roleId ], $menuIds );
        $stmt->execute( $params );

        $existing = $stmt->fetchAll( \PDO::FETCH_COLUMN, 0 );
        return $existing;
    }

    public function createAccessUsers( $data ) {
        $roleId = $data[ 'role_id' ];
        $menuIds = $data[ 'menu_ids' ];

        $sql = 'INSERT IGNORE INTO role_menus (role_id, menu_id) VALUES ';
        $values = [];
        $params = [];

        foreach ( $menuIds as $menuId ) {
            $values[] = '(?, ?)';
            $params[] = $roleId;
            $params[] = $menuId;
        }

        $sql .= implode( ', ', $values );
        $stmt = $this->conn->prepare( $sql );
        return $stmt->execute( $params );
    }

    public function getAccessUsers() {
        try {
            $query = 'SELECT r.*,m.*,rm.role_menu_id FROM  role_menus rm  INNER JOIN tbl_roles r 
            ON r.role_id=rm.role_id INNER JOIN menus m on m.menu_id=rm.menu_id ';
            $stmt = $this->conn->prepare( $query );
            $stmt->execute();
            return $stmt->fetchAll( \PDO::FETCH_ASSOC );
        } catch ( \PDOException $e ) {
            return false;
        }
    }

    public function removeAccessfromRole( $menu_id ) {
        $sql = 'DELETE FROM role_menus WHERE role_menu_id = :role_menu_id';
        $stmt = $this->conn->prepare( $sql );
        return $stmt->execute( [ 'role_menu_id' => $menu_id ] );
    }


}
