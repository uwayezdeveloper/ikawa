<?php
namespace Models;

require_once __DIR__ . '/../config/Database.php';

use Config\Database;

class User {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAllUsers($loc_id) {
        try {
            $query = 'SELECT * FROM tbl_users us JOIN  tbl_roles rl ON us.role_id=rl.role_id where us.status="active" and loc_id=:loc_id ORDER BY us.user_id ASC';
            $stmt = $this->conn->prepare( $query );
            $stmt->execute( [ ':loc_id' =>$loc_id ] );
            return $stmt->fetchAll( \PDO::FETCH_ASSOC );

        } catch ( \PDOException $e ) {
            return false;
        }
    }

    public function getByUsername( $username ) {
        try {
            $sql = "
                SELECT u.*, r.role_name, l.location_name
                FROM tbl_users u
                LEFT JOIN tbl_roles r ON u.role_id = r.role_id
                LEFT JOIN tbl_location l ON u.loc_id = l.loc_id
                WHERE u.username = :username
            ";
            $stmt = $this->conn->prepare( $sql );
            $stmt->execute( [ 'username' => $username ] );
            return $stmt->fetch( \PDO::FETCH_ASSOC );
        } catch (\PDOException $e) {
            error_log("Error fetching user: " . $e->getMessage());
            return false;
        }
    }

    public function hasActiveSession( int $userId ): bool {
        $sql = "SELECT 1 
            FROM user_sessions
            WHERE user_id = :user_id
              AND is_active = 1
              AND ended_at > NOW()
            LIMIT 1";

        $stmt = $this->conn->prepare( $sql );
        $stmt->execute( [ 'user_id' => $userId ] );

        return ( bool ) $stmt->fetchColumn();
    }

    public function createSession( array $data ) {
        $sql = "INSERT INTO user_sessions
            (user_id, session_key, ip_address, user_agent, started_at, ended_at, is_active)
            VALUES (
                :user_id,
                :session_key,
                :ip_address,
                :user_agent,
                DATE_ADD(NOW(), INTERVAL 1 HOUR),
                DATE_ADD(NOW(), INTERVAL 2 HOUR),
                1
            )";

        $stmt = $this->conn->prepare( $sql );
        return $stmt->execute( $data );
    }

    public function logAccess( array $data ) {
        $sql = "INSERT INTO access_logs 
            (user_id, action, ip_address, user_agent, created_at)
            VALUES (:user_id, :action, :ip_address, :user_agent, NOW())";

        $stmt = $this->conn->prepare( $sql );
        return $stmt->execute( $data );
    }

    public function deleteSession( string $sessionKey ) {
        $sql = 'DELETE FROM user_sessions WHERE session_key = :session_key';
        $stmt = $this->conn->prepare( $sql );
        return $stmt->execute( [ 'session_key' => $sessionKey ] );
    }

    public function getActiveSession( $userId, $sessionKey ) {
        $sql = 'SELECT * FROM user_sessions WHERE user_id = :user_id AND session_key = :session_key AND is_active = 1';
        $stmt = $this->conn->prepare( $sql );
        $stmt->execute( [ 'user_id' => $userId, 'session_key' => $sessionKey ] );
        return $stmt->fetch( \PDO::FETCH_ASSOC );
    }

    public function getSessionExpiryTime( $userId, $sessionKey ) {
        $sql = "SELECT TIMESTAMPDIFF(SECOND, NOW(), ended_at) as seconds_remaining 
            FROM user_sessions 
            WHERE user_id = :user_id 
            AND session_key = :session_key 
            AND is_active = 1";

        $stmt = $this->conn->prepare( $sql );
        $stmt->execute( [
            ':user_id' => $userId,
            ':session_key' => $sessionKey
        ] );

        $result = $stmt->fetch( \PDO::FETCH_ASSOC );
        return $result ? ( int )$result[ 'seconds_remaining' ] : 0;
    }

    public function extendSession( $userId, $sessionKey ) {
        $sql = "UPDATE user_sessions 
            SET ended_at = DATE_ADD(NOW(), INTERVAL 1 HOUR) 
            WHERE user_id = :user_id 
            AND session_key = :session_key 
            AND is_active = 1";

        $stmt = $this->conn->prepare( $sql );
        return $stmt->execute( [
            ':user_id' => $userId,
            ':session_key' => $sessionKey
        ] );
    }

    public function exists( string $username, string $email, string $phone ): ?string {
        $sql = "
            SELECT 
                CASE
                    WHEN username = :username THEN 'username'
                    WHEN email = :email THEN 'email'
                    WHEN phone = :phone THEN 'phone'
                END AS field
            FROM tbl_users
            WHERE username = :username
               OR email = :email
               OR phone = :phone
            LIMIT 1
        ";

        $stmt = $this->conn->prepare( $sql );
        $stmt->execute( [
            ':username' => $username,
            ':email'    => $email,
            ':phone'    => $phone
        ] );

        $row = $stmt->fetch( \PDO::FETCH_ASSOC );

        return $row[ 'field' ] ?? null;
    }

    public function existsUpdate( string $username, string $email, string $phone, string $user_id ): ?string {
        $sql = "
            SELECT 
                CASE
                    WHEN username = :username THEN 'username'
                    WHEN email = :email THEN 'email'
                    WHEN phone = :phone THEN 'phone'
                END AS field
            FROM tbl_users
            WHERE (username = :username
               OR email = :email
               OR phone = :phone) and user_id!=:user_id
            LIMIT 1
        ";

        $stmt = $this->conn->prepare( $sql );
        $stmt->execute( [
            ':username' => $username,
            ':email'    => $email,
            ':phone'    => $phone,
            ':user_id'=> $user_id
        ] );

        $row = $stmt->fetch( \PDO::FETCH_ASSOC );

        return $row[ 'field' ] ?? null;
    }

    public function create( array $data ): bool {
        try {
            $sql = "
                INSERT INTO tbl_users (
                    first_name,
                    last_name,
                    email,
                    username,
                    phone,
                    role_id,
                    gender,
                    nid,
                    loc_id,
                    password_hash,
                    created_at
                ) VALUES (
                    :first_name,
                    :last_name,
                    :email,
                    :username,
                    :phone,
                    :role_id,
                    :gender,
                    :nid,
                    :loc_id,
                    :password,
                    NOW()
                )
            ";

            $stmt = $this->conn->prepare( $sql );

            return $stmt->execute( [
                ':first_name' => $data[ 'first_name' ],
                ':last_name'  => $data[ 'last_name' ],
                ':email'      => $data[ 'email' ],
                ':username'   => $data[ 'username' ],
                ':phone'      => $data[ 'phone' ],
                ':role_id'    => $data[ 'role_id' ],
                ':gender'     => $data[ 'gender' ],
                ':nid'        => $data[ 'nid' ],
                ':loc_id'     => $data[ 'loc_id' ],
                ':password'   => $data[ 'password' ]
            ] );
        } catch ( \PDOException $e ) {
            return false;
        }
    }

    public function update( array $data ): bool {

        try {
            $sql = "UPDATE tbl_users SET first_name=:first_name,last_name=:last_name,role_id=:role_id,
            loc_id=:loc_id,email=:email,username=:username ,phone=:phone,gender=:gender,nid=:nid 
            where user_id =:user_id";

            $stmt = $this->conn->prepare( $sql );

            return $stmt->execute( [
                ':first_name' => $data[ 'first_name' ],
                ':last_name'  => $data[ 'last_name' ],
                ':role_id'    => $data[ 'role_id' ],
                ':loc_id'     => $data[ 'loc_id' ],
                ':email'      => $data[ 'email' ],
                ':username'   => $data[ 'username' ],
                ':phone'      => $data[ 'phone' ],
                ':gender'     => $data[ 'gender' ],
                ':nid'        => $data[ 'nid' ],
                ':user_id'   => $data[ 'user_id' ]
            ] );
        } catch ( \PDOException $e ) {
            return false;
        }
    }

    public function extendSessionModel( $userId, $sessionKey ) {
        $sql = "UPDATE user_sessions 
            SET ended_at = DATE_ADD(NOW(), INTERVAL 1 HOUR) 
            WHERE user_id = :user_id 
            AND session_key = :session_key 
            AND is_active = 1";

        $stmt = $this->conn->prepare( $sql );
        return $stmt->execute( [
            ':user_id' => $userId,
            ':session_key' => $sessionKey
        ] );
    }

    public function removeUser( $user_id ) {
        $sql = 'UPDATE tbl_users SET status = "inactive" WHERE user_id = :user_id';
        $stmt = $this->conn->prepare( $sql );
        return $stmt->execute( [ ':user_id' => $user_id ] );
    }
}
