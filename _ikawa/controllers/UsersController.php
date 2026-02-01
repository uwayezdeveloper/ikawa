<?php
namespace Controllers;

// Include dependencies
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/Response.php';
require_once __DIR__ . '/../models/CreateEmail.php';

use Models\User;
use Config\Response;
use Models\CreateEmail;

class UsersController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function login() {
        $data = json_decode( file_get_contents( 'php://input' ), true );

        $username = $data[ 'username' ] ?? '';
        $password = $data[ 'password' ] ?? '';

        $user = $this->userModel->getByUsername( $username );

        if ( !$user ) {
            Response::error( 'User not found', 404 );
        }

        if ( !password_verify( $password, $user[ 'password_hash' ] ) ) {
            Response::error( 'Incorrect password', 401 );
        }
        // Check if user already has an active session
        if ( $this->userModel->hasActiveSession( $user[ 'user_id' ] ) ) {
            Response::error(
                'This account is already logged in on another browser',
                409
            );
        }

        // Session + logging starts here

        // session_start();

        $sessionKey = bin2hex( random_bytes( 32 ) );
        $ipAddress  = $_SERVER[ 'REMOTE_ADDR' ] ?? 'UNKNOWN';
        $userAgent  = $_SERVER[ 'HTTP_USER_AGENT' ] ?? 'UNKNOWN';

        // Save PHP session
        $_SESSION[ 'session_key' ] = $sessionKey;
        $_SESSION[ 'user_id' ]     = $user[ 'user_id' ];
        $_SESSION[ 'username' ]    = $user[ 'username' ];
        $_SESSION[ 'email' ]       = $user[ 'email' ];
        $_SESSION[ 'first_name' ]  = $user[ 'first_name' ];
        $_SESSION[ 'last_name' ]   = $user[ 'last_name' ];
        $_SESSION[ 'role_id' ]     = $user[ 'role_id' ];
        $_SESSION[ 'loc_id' ]     = $user[ 'loc_id' ];
        $_SESSION[ 'status' ]      = $user[ 'status' ];
        $_SESSION[ 'role_name' ]      = $user[ 'role_name' ];
        // Save user_session
        $this->userModel->createSession( [
            'user_id'     => $user[ 'user_id' ],
            'session_key' => $sessionKey,
            'ip_address'  => $ipAddress,
            'user_agent'  => $userAgent
        ] );

        // Save access_logs
        $this->userModel->logAccess( [
            'user_id'    => $user[ 'user_id' ],
            'action'     => 'login',
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent
        ] );

        Response::success( 'Login successful', [
            'user_id' => $user[ 'user_id' ],
            'role_id' => $user[ 'role_id' ]
        ] );
    }

    public function getUsers($loc_id) {
        $users = $this->userModel->getAllUsers($loc_id);

        if ( $users !== false ) {
            Response::success( 'Users retrieved successfully!', $users );
        } else {
            Response::error( 'Failed to retrieve users', 500 );
        }
    }

    public function logout() {
        // session_start();

        if ( !isset( $_SESSION[ 'session_key' ] ) ) {
            Response::error( 'No active session', 400 );
        }

        $sessionKey = $_SESSION[ 'session_key' ];
        $userId     = $_SESSION[ 'user_id' ] ?? null;

        // Delete session from DB
        $this->userModel->deleteSession( $sessionKey );

        // Log logout action
        if ( $userId ) {
            $this->userModel->logAccess( [
                'user_id'    => $userId,
                'action'     => 'logout',
                'ip_address' => $_SERVER[ 'REMOTE_ADDR' ] ?? 'UNKNOWN',
                'user_agent' => $_SERVER[ 'HTTP_USER_AGENT' ] ?? 'UNKNOWN'
            ] );
        }

        // Destroy PHP session
        session_unset();
        session_destroy();

        Response::success( 'Logged out successfully' );
    }

    public function extendSession() {
        // session_start();

        if ( !isset( $_SESSION[ 'user_id' ] ) || !isset( $_SESSION[ 'session_key' ] ) ) {
            echo json_encode( [ 'success' => false, 'message' => 'Not logged in' ] );
            exit;
        }
        $this->userModel->extendSessionModel( $_SESSION[ 'user_id' ], $_SESSION[ 'session_key' ] );

        if ( $_SESSION[ 'user_id' ] ) {
            $_SESSION[ 'session_expires_in' ] = 3600;
            // 1 hour in seconds
            echo json_encode( [ 'success' => true, 'message' => 'Session extended' ] );
        } else {
            echo json_encode( [ 'success' => false, 'message' => 'Failed to extend session' ] );
        }
    }

   public function createUser() {
    // POST METHOD ONLY
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        Response::error('Invalid request method', 405);
        return;
    }

    // READ JSON INPUT
    $input = json_decode(file_get_contents('php://input'), true);

    if (!is_array($input)) {
        Response::error('Invalid JSON payload', 400);
        return;
    }

    $required = ['username', 'role_id', 'loc_id', 'first_name', 'last_name'];

    foreach ($required as $field) {
        if (empty($input[$field])) {
            Response::error("Missing field: {$field}", 400);
            return;
        }
    }

    $username = trim($input['username']);
    $email    = trim($input['email'] ?? '');
    $phone    = trim($input['phone'] ?? '');
    $firstName = trim($input['first_name']);
    $lastName = trim($input['last_name']);
    $fullName = $firstName . ' ' . $lastName;

    // DUPLICATE CHECK
    $duplicate = $this->userModel->exists($username, $email, $phone);

    if ($duplicate !== null) {
        Response::error(
            ucfirst($duplicate) . ' already exists',
            409
        );
        return;
    }

    // Generate random 6-digit password
    $plainPassword  = random_int(100000, 999999);
    $hashedPassword = password_hash($plainPassword, PASSWORD_BCRYPT);

    $data = [
        'first_name' => $firstName,
        'last_name'  => $lastName,
        'email'      => $email,
        'username'   => $username,
        'phone'      => $phone,
        'role_id'    => (int)$input['role_id'],
        'gender'     => trim($input['gender'] ?? ''),
        'nid'        => trim($input['nid'] ?? ''),
        'loc_id'     => (int)$input['loc_id'],
        'password'   => $hashedPassword
    ];

    $userId = $this->userModel->create($data);

    if ($userId) {
        // Send email notification if email is provided
        $emailSent = false;
        if (!empty($email)) {
            try {
                $emailService = new CreateEmail();
                $emailSent = $emailService->sendEmail($email, $fullName, $plainPassword, $username);
            } catch (Exception $e) {
                error_log('Email sending failed: ' . $e->getMessage());
            }
        }

        Response::success('User created successfully credentials sent via email', [
            'user_id' => $userId,
            'username' => $username,
            'password' => $plainPassword,
            'email_sent' => $emailSent,
            'email' => $email
        ]);
    } else {
        Response::error('Failed to create user', 500);
    }
}

    public function updateUser() {
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
            'username', 'role_id', 'loc_id', 'user_id'
        ];

        foreach ( $required as $field ) {
            if ( empty( $input[ $field ] ) ) {
                Response::error( "Missing field: {$field}", 400 );
                return;
            }
        }

        $username = trim( $input[ 'username' ] );
        $email    = trim( $input[ 'email' ] );
        $phone    = trim( $input[ 'phone' ] );
        $user_id = trim( $input[ 'user_id' ] );

        //DUPLICATE CHECK
        $duplicate = $this->userModel->existsUpdate( $username, $email, $phone, $user_id );

        if ( $duplicate !== null ) {
            Response::error(
                ucfirst( $duplicate ) . ' already exists',
                409
            );
            return;
        }

        $data = [
            'first_name' => trim( $input[ 'first_name' ] ),
            'last_name'  => trim( $input[ 'last_name' ] ),
            'email'      => $email,
            'username'   => $username,
            'phone'      => $phone,
            'role_id'    => ( int ) $input[ 'role_id' ],
            'gender'     => trim( $input[ 'gender' ] ),
            'nid'        => trim( $input[ 'nid' ] ),
            'loc_id'     => ( int ) $input[ 'loc_id' ],
            'user_id'=>( int )$input[ 'user_id' ]
        ];

        if ( $this->userModel->update( $data ) ) {
            Response::success( 'User updated successfully', [
                'username' => $username,
                'user_id' => $user_id
            ] );
        } else {
            Response::error( 'Failed to update user', 500 );
        }
    }

    public function deleteUser( $user_id ) {
        if ( $this->userModel->removeUser( $user_id ) ) {
            Response::success( 'User deleted successfully' );
        } else {
            Response::error( 'Failed to delete user', 500 );
        }
    }
}
