<?php
namespace Models;

require_once __DIR__ . '/../config/Database.php';

use Config\Database;
use PDO;
use \PDOException;

class Inventory {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function exists( string $phone,  string $email ): ?string {
        $sql = "
            SELECT 
                CASE
                    WHEN phone = :phone THEN 'phone'
                    WHEN email = :email THEN 'email'
                END AS field
            FROM tbl_suppliers
            WHERE phone = :phone or email = :email
            LIMIT 1
        ";

        $stmt = $this->conn->prepare( $sql );
        $stmt->execute( [
            ':phone' => $phone,
            ':email' => $email
        ] );

        $row = $stmt->fetch( \PDO::FETCH_ASSOC );

        return $row[ 'field' ] ?? null;
    }

    public function createSupp( array $data ): bool {
        try {
            $sql = "
                INSERT INTO  tbl_suppliers (
                    full_name,
                    email,
                    phone,
                    address,
                    type
                ) VALUES (
                    :full_name,
                    :email,
                    :phone,
                    :address,
                    :type
                )
            ";

            $stmt = $this->conn->prepare( $sql );

            return $stmt->execute( [
                ':full_name' => $data[ 'full_name' ],
                ':email'  => $data[ 'email' ],
                ':phone'  => $data[ 'phone' ],
                ':address'  => $data[ 'address' ],
                ':type'=>$data[ 'type' ]

            ] );
        } catch ( \PDOException $e ) {
            return false;
        }
    }

    public function getSuppliers() {
        try {
            $query = 'SELECT * FROM tbl_suppliers where status="active"';
            $stmt = $this->conn->prepare( $query );
            $stmt->execute();
            return $stmt->fetchAll( \PDO::FETCH_ASSOC );

        } catch ( \PDOException $e ) {
            return false;
        }
    }

    public function existsUpdate( string $email, string $phone, string $sup_id ): ?string {
        $sql = "
            SELECT 
                CASE
                    WHEN email = :email THEN 'email'
                    WHEN phone = :phone THEN 'phone'
                END AS field
            FROM  tbl_suppliers
            WHERE (email =:email or phone=:phone ) and sup_id!=:sup_id
            LIMIT 1
        ";

        $stmt = $this->conn->prepare( $sql );
        $stmt->execute( [
            ':email' => $email,
            ':phone'=>$phone,
            ':sup_id'    => $sup_id
        ] );

        $row = $stmt->fetch( \PDO::FETCH_ASSOC );

        return $row[ 'field' ] ?? null;
    }

    public function updateSuppl( array $data ): bool {

        try {
            $sql = "UPDATE tbl_suppliers SET full_name=:full_name,email=:email,phone=:phone,address=:address,
            type=:type
            where sup_id=:sup_id";

            $stmt = $this->conn->prepare( $sql );

            return $stmt->execute( [
                ':full_name' => $data[ 'full_name' ],
                ':email'  => $data[ 'email' ],
                ':phone'  => $data[ 'phone' ],
                ':address'  => $data[ 'address' ],
                ':type'  => $data[ 'type' ],
                ':sup_id'   => $data[ 'sup_id' ]
            ] );
        } catch ( \PDOException $e ) {
            return false;
        }
    }

    public function removeSupplier( $sup_id ) {
        $sql = 'UPDATE tbl_suppliers SET status = "inactive" WHERE sup_id = :sup_id';
        $stmt = $this->conn->prepare( $sql );
        return $stmt->execute( [ ':sup_id' => $sup_id ] );
    }

    public function getCountries() {
        try {
            $query = 'SELECT * FROM countries';
            $stmt = $this->conn->prepare( $query );
            $stmt->execute();
            return $stmt->fetchAll( \PDO::FETCH_ASSOC );

        } catch ( \PDOException $e ) {
            return false;
        }
    }

    public function existsClient( string $phone,  string $email ): ?string {
        $sql = "
            SELECT 
                CASE
                    WHEN phone = :phone THEN 'phone'
                    WHEN email = :email THEN 'email'
                END AS field
            FROM tbl_clients
            WHERE phone = :phone or email = :email
            LIMIT 1
        ";

        $stmt = $this->conn->prepare( $sql );
        $stmt->execute( [
            ':phone' => $phone,
            ':email' => $email
        ] );

        $row = $stmt->fetch( \PDO::FETCH_ASSOC );

        return $row[ 'field' ] ?? null;
    }

    public function createClnt( array $data ): bool {
        try {
            $sql = "
                INSERT INTO  tbl_clients (
                    full_name,
                    email,
                    country_id,
                    phone,
                    city,
                    address,
                    client_type
                ) VALUES (
                    :full_name,
                    :email,
                    :country_id,
                    :phone,
                    :city,
                    :address,
                    :client_type

                )
            ";

            $stmt = $this->conn->prepare( $sql );

            return $stmt->execute( [
                ':full_name' => $data[ 'full_name' ],
                ':email'  => $data[ 'email' ],
                ':country_id'  => $data[ 'country_id' ],
                ':phone'  => $data[ 'phone' ],
                ':city'=>$data[ 'city' ],
                ':address'=>$data[ 'address' ],
                ':client_type'=>$data[ 'client_type' ]

            ] );
        } catch ( \PDOException $e ) {
            return false;
        }
    }

    public function getClients() {
        try {
            $query = 'SELECT cntry.name as c_name,cntry.code,cntry.phone_code,
            cntry.region,cl.* FROM tbl_clients cl INNER JOIN countries cntry on cl.country_id=cntry.id where cl.status="active"';
            $stmt = $this->conn->prepare( $query );
            $stmt->execute();
            return $stmt->fetchAll( \PDO::FETCH_ASSOC );

        } catch ( \PDOException $e ) {
            return false;
        }
    }

    public function existsClientUpdate( string $email, string $phone, string $client_id ): ?string {
        $sql = "
            SELECT 
                CASE
                    WHEN email = :email THEN 'email'
                    WHEN phone = :phone THEN 'phone'
                END AS field
            FROM  tbl_clients
            WHERE (email =:email or phone=:phone ) and client_id!=:client_id
            LIMIT 1
        ";

        $stmt = $this->conn->prepare( $sql );
        $stmt->execute( [
            ':email' => $email,
            ':phone'=>$phone,
            ':client_id'    => $client_id
        ] );

        $row = $stmt->fetch( \PDO::FETCH_ASSOC );

        return $row[ 'field' ] ?? null;
    }

    public function updateCompanyClient( array $data ): bool {

        try {
            $sql = "UPDATE tbl_clients SET full_name=:full_name,email=:email,country_id=:country_id,phone=:phone,
            city=:city,address=:address,client_type=:client_type
            where client_id=:client_id";

            $stmt = $this->conn->prepare( $sql );

            return $stmt->execute( [
                ':full_name' => $data[ 'full_name' ],
                ':email'  => $data[ 'email' ],
                ':country_id'  => $data[ 'country_id' ],
                ':phone'  => $data[ 'phone' ],
                ':city'  => $data[ 'city' ],
                ':address'  => $data[ 'address' ],
                ':client_type'  => $data[ 'client_type' ],
                ':client_id'   => $data[ 'client_id' ]
            ] );
        } catch ( \PDOException $e ) {
            return false;
        }
    }

    public function removeClient( $client_id ) {
        $sql = 'UPDATE tbl_clients SET status = "inactive" WHERE client_id = :client_id';
        $stmt = $this->conn->prepare( $sql );
        return $stmt->execute( [ ':client_id' => $client_id ] );
    }

}
