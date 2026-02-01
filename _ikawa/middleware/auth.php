<?php
if ( session_status() === PHP_SESSION_NONE ) {
    session_start();
}

require_once __DIR__ . '/../models/User.php';
$userModel = new Models\User();

// Check if user is logged in
if ( !isset( $_SESSION[ 'user_id' ] ) ) {
    header( 'Location: ' . App::baseUrl() . '/public/' );
    exit;
}

// Check session in DB
$sessionKey = $_SESSION[ 'session_key' ] ?? null;
$userId = $_SESSION[ 'user_id' ] ?? null;

if ( !$sessionKey || !$userId ) {
    session_destroy();
    header( 'Location: ' . App::baseUrl() . '/public/' );
    exit;
}

$sessionData = $userModel->getActiveSession( $userId, $sessionKey );

if ( !$sessionData ) {
    // Session expired or deleted
    session_destroy();
    header( 'Location: ' . App::baseUrl() . '/public/' );
    exit;
}

// Get remaining session time
$remainingSeconds = $userModel->getSessionExpiryTime( $userId, $sessionKey );

// Store in session for JavaScript access
$_SESSION[ 'session_expires_in' ] = $remainingSeconds;
$_SESSION[ 'session_key' ] = $sessionKey;

