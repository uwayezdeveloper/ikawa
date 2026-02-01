<?php
namespace Config;

class Response
 {
    /**
    * Send a JSON response
    *
    * @param bool $success
    * @param string $message
    * @param mixed|null $data
    * @param int $statusCode
    */
    public static function send( $success = true, $message = '', $data = null, $statusCode = 200 )
 {
        // Set HTTP status code
        http_response_code( $statusCode );

        // Set JSON header
        header( 'Content-Type: application/json; charset=UTF-8' );

        // Prepare response array
        $response = [
            'success' => $success,
            'message' => $message
        ];

        // Include data if provided
        if ( !is_null( $data ) ) {
            $response[ 'data' ] = $data;
        }

        // Send JSON response
        echo json_encode( $response );
        exit;
    }

    /**
    * Send an error response
    *
    * @param string $message
    * @param int $statusCode
    * @param mixed|null $data
    */
    public static function error( $message = 'An error occurred', $statusCode = 400, $data = null )
 {
        self::send( false, $message, $data, $statusCode );
    }

    /**
    * Send a success response
    *
    * @param string $message
    * @param mixed|null $data
    * @param int $statusCode
    */
    public static function success( $message = 'Success', $data = null, $statusCode = 200 )
 {
        self::send( true, $message, $data, $statusCode );
    }
}
