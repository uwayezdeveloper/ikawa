<?php
namespace Models;

require_once __DIR__ . '/../config/Database.php';

use Config\Database;
use PDO;
use \PDOException;

class CreateEmail {
    public function sendEmail($to, $fullName, $password, $username) {
        $subject = "Welcome to Ikawa System - Your Account is Ready";

        // Email Headers
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: Ikawa System <info@itec.rw>\r\n";
        $headers .= "Reply-To: support@itec.rw\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        // Modern Professional Email Design
        $message = "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Ikawa System Account</title>
            <style>
                @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
                
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                
                body {
                    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
                    background-color: #f8fafc;
                    line-height: 1.6;
                    color: #334155;
                }
                
                .email-container {
                    max-width: 600px;
                    margin: 40px auto;
                    background: white;
                    border-radius: 16px;
                    overflow: hidden;
                    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05), 0 5px 10px rgba(0, 0, 0, 0.02);
                }
                
                .email-header {
                    background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
                    color: white;
                    padding: 40px 30px;
                    text-align: center;
                    position: relative;
                    overflow: hidden;
                }
                
                .email-header::before {
                    content: '';
                    position: absolute;
                    top: -50%;
                    right: -50%;
                    width: 200%;
                    height: 200%;
                    background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
                    background-size: 20px 20px;
                    opacity: 0.2;
                }
                
                .logo {
                    font-size: 28px;
                    font-weight: 700;
                    margin-bottom: 15px;
                    letter-spacing: -0.5px;
                }
                
                .logo-sub {
                    font-size: 15px;
                    opacity: 0.9;
                    font-weight: 400;
                    margin-top: 5px;
                }
                
                .email-title {
                    font-size: 22px;
                    font-weight: 600;
                    margin: 25px 0 10px;
                }
                
                .email-content {
                    padding: 40px 35px;
                }
                
                .greeting {
                    font-size: 17px;
                    margin-bottom: 25px;
                    color: #475569;
                }
                
                .credentials-box {
                    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
                    border: 1px solid #e2e8f0;
                    border-radius: 12px;
                    padding: 25px;
                    margin: 25px 0;
                }
                
                .credential-item {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 12px 0;
                    border-bottom: 1px solid #e2e8f0;
                }
                
                .credential-item:last-child {
                    border-bottom: none;
                }
                
                .credential-label {
                    font-weight: 500;
                    color: #475569;
                    font-size: 15px;
                }
                
                .credential-value {
                    font-weight: 600;
                    color: #1e40af;
                    font-size: 15px;
                    background: white;
                    padding: 8px 15px;
                    border-radius: 8px;
                    border: 1px solid #dbeafe;
                    
                }
                
                .login-btn {
                    display: inline-block;
                    background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
                    color: white;
                    text-decoration: none;
                    padding: 14px 32px;
                    border-radius: 10px;
                    font-weight: 600;
                    font-size: 15px;
                    margin: 20px 0;
                    transition: all 0.3s ease;
                    box-shadow: 0 4px 12px rgba(30, 64, 175, 0.2);
                }
                
                .login-btn:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 6px 18px rgba(30, 64, 175, 0.3);
                }
                
                .security-note {
                    background: #fffbeb;
                    border-left: 4px solid #f59e0b;
                    padding: 18px;
                    border-radius: 8px;
                    margin: 25px 0;
                    font-size: 14px;
                    color: #92400e;
                }
                
                .security-note strong {
                    display: block;
                    margin-bottom: 5px;
                    font-size: 15px;
                }
                
                .footer {
                    text-align: center;
                    padding: 30px;
                    background: #f8fafc;
                    color: #64748b;
                    font-size: 13px;
                    border-top: 1px solid #e2e8f0;
                }
                
                .footer-links {
                    margin-top: 15px;
                }
                
                .footer-links a {
                    color: #1e40af;
                    text-decoration: none;
                    margin: 0 10px;
                    font-size: 13px;
                }
                
                @media (max-width: 600px) {
                    .email-container {
                        margin: 20px 15px;
                        border-radius: 12px;
                    }
                    
                    .email-header, .email-content {
                        padding: 25px 20px;
                    }
                    
                    .credential-item {
                        flex-direction: column;
                        align-items: flex-start;
                        gap: 8px;
                    }
                    
                    .credential-value {
                        width: 100%;
                        text-align: center;
                    }
                }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='email-content'>
                    <div class='greeting'>
                        Hello <strong style='color: #1e40af;'>$fullName</strong>,
                    </div>
                    
                    <p>Welcome to the Ikawa Coffee Management System! Your account has been created and is ready to use.</p>
                    
                    <p>Please use the following credentials to access the system:</p>
                    
                    <div class='credentials-box'>
                        <div class='credential-item'>
                            <span class='credential-label' style='margin-top:7px;'>Username:</span>
                            <span class='credential-value'>$username</span>
                        </div>
                        <div class='credential-item'>
                            <span class='credential-label' style='margin-top:7px;'>Password:</span>
                            <span class='credential-value'>$password</span>
                        </div>
                    </div>
                    
                    <div style='text-align: center;'>
                        <a href='https://ikawa.itectab.rw/' class='login-btn' target='_blank' style='color:white'>Login to Your Account</a>
                    </div>
                    
                    <div class='security-note'>
                        <strong> Security Notice:</strong>
                        For your security, please change your password after your first login and do not share these credentials with anyone.
                    </div>
                    
                    <p style='color: #64748b; font-size: 14px;'>
                        If you have any questions or need assistance, please contact our support team at 
                        <a href='mailto:support@ikawa.itectab.rw' style='color: #1e40af; text-decoration: none;'>support@itec.rw</a>.
                    </p>
                </div>
                
                <div class='footer'>
                    © " . date('Y') . " Ikawa System. All rights reserved.
                    <div class='footer-links'>
                        <a href='https://ikawa.itectab.rw/'>Home</a>
                        <a href='https://ikawa.itectab.rw/support'>Support</a>
                        <a href='https://ikawa.itectab.rw/privacy'>Privacy</a>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";

        return mail($to, $subject, $message, $headers);
    }
}
?>