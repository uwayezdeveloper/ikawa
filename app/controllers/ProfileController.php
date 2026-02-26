<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;

/**
 * Profile Controller
 * Handles user profile management
 */
class ProfileController extends Controller
{
    protected User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }

    /**
     * View profile
     */
    public function index(Request $request, Response $response): void
    {
        $loggedInUser = $_SESSION['user'] ?? null;
        
        if (!$loggedInUser) {
            $response->redirect(APP_URL . '/login');
            return;
        }
        
        // Get fresh user data with role
        $user = $this->userModel->findWithRole($loggedInUser['id']);
        
        if (!$user) {
            $_SESSION['flash_error'] = 'User not found';
            $response->redirect(APP_URL . '/dashboard');
            return;
        }
        
        $this->view('profile/index', [
            'user' => $user,
            'pageTitle' => 'My Profile'
        ], 'main');
    }

    /**
     * Show edit profile form
     */
    public function edit(Request $request, Response $response): void
    {
        $loggedInUser = $_SESSION['user'] ?? null;
        
        if (!$loggedInUser) {
            $response->redirect(APP_URL . '/login');
            return;
        }
        
        // Get fresh user data
        $user = $this->userModel->find($loggedInUser['id']);
        
        if (!$user) {
            $_SESSION['flash_error'] = 'User not found';
            $response->redirect(APP_URL . '/dashboard');
            return;
        }
        
        $this->view('profile/edit', [
            'user' => $user,
            'pageTitle' => 'Edit Profile'
        ], 'main');
    }

    /**
     * Update profile
     */
    public function update(Request $request, Response $response): void
    {
        $loggedInUser = $_SESSION['user'] ?? null;
        
        if (!$loggedInUser) {
            $response->redirect(APP_URL . '/login');
            return;
        }
        
        $userId = $loggedInUser['id'];
        
        // Get current user
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            $_SESSION['flash_error'] = 'User not found';
            $response->redirect(APP_URL . '/dashboard');
            return;
        }
        
        // Get POST data
        $data = $request->getBody();
        
        // Validate input
        $firstName = trim($data['first_name'] ?? '');
        $lastName = trim($data['last_name'] ?? '');
        $email = trim($data['email'] ?? '');
        $phone = trim($data['phone'] ?? '');
        
        if (empty($firstName) || empty($lastName) || empty($email)) {
            $_SESSION['flash_error'] = 'First name, last name, and email are required';
            $response->redirect(APP_URL . '/profile/edit');
            return;
        }
        
        // Check if email is already taken by another user
        if ($email !== $user['email']) {
            $existingUser = $this->userModel->findByEmail($email);
            if ($existingUser && $existingUser['id'] != $userId) {
                $_SESSION['flash_error'] = 'Email is already taken';
                $response->redirect(APP_URL . '/profile/edit');
                return;
            }
        }
        
        // Update user
        $updated = $this->userModel->update($userId, [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone
        ]);
        
        if ($updated) {
            // Update session data
            $_SESSION['user']['first_name'] = $firstName;
            $_SESSION['user']['last_name'] = $lastName;
            $_SESSION['user']['email'] = $email;
            $_SESSION['user']['phone'] = $phone;
            
            $_SESSION['flash_success'] = 'Profile updated successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to update profile';
        }
        
        $response->redirect(APP_URL . '/profile');
    }

    /**
     * Show change password form
     */
    public function changePassword(Request $request, Response $response): void
    {
        $loggedInUser = $_SESSION['user'] ?? null;
        
        if (!$loggedInUser) {
            $response->redirect(APP_URL . '/login');
            return;
        }
        
        $this->view('profile/change-password', [
            'pageTitle' => 'Change Password'
        ], 'main');
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request, Response $response): void
    {
        $loggedInUser = $_SESSION['user'] ?? null;
        
        if (!$loggedInUser) {
            $response->redirect(APP_URL . '/login');
            return;
        }
        
        $userId = $loggedInUser['id'];
        
        // Get current user
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            $_SESSION['flash_error'] = 'User not found';
            $response->redirect(APP_URL . '/dashboard');
            return;
        }
        
        // Get POST data
        $data = $request->getBody();
        
        // Get input
        $currentPassword = $data['current_password'] ?? '';
        $newPassword = $data['new_password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? '';
        
        // Validate input
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $_SESSION['flash_error'] = 'All fields are required';
            $response->redirect(APP_URL . '/profile/change-password');
            return;
        }
        
        // Verify current password
        if (!password_verify($currentPassword, $user['password'])) {
            $_SESSION['flash_error'] = 'Current password is incorrect';
            $response->redirect(APP_URL . '/profile/change-password');
            return;
        }
        
        // Check if new passwords match
        if ($newPassword !== $confirmPassword) {
            $_SESSION['flash_error'] = 'New passwords do not match';
            $response->redirect(APP_URL . '/profile/change-password');
            return;
        }
        
        // Validate new password strength
        if (strlen($newPassword) < 6) {
            $_SESSION['flash_error'] = 'Password must be at least 6 characters long';
            $response->redirect(APP_URL . '/profile/change-password');
            return;
        }
        
        // Update password
        $updated = $this->userModel->updatePassword($userId, $newPassword);
        
        if ($updated) {
            $_SESSION['flash_success'] = 'Password changed successfully';
            $response->redirect(APP_URL . '/profile');
        } else {
            $_SESSION['flash_error'] = 'Failed to update password';
            $response->redirect(APP_URL . '/profile/change-password');
        }
    }

    /**
     * Update avatar
     */
    public function updateAvatar(Request $request, Response $response): void
    {
        $loggedInUser = $_SESSION['user'] ?? null;
        
        if (!$loggedInUser) {
            $response->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }
        
        $userId = $loggedInUser['id'];
        
        // Check if file was uploaded
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            $response->json(['success' => false, 'message' => 'No file uploaded or upload error'], 400);
            return;
        }
        
        $file = $_FILES['avatar'];
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($file['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            $response->json(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and GIF are allowed'], 400);
            return;
        }
        
        // Validate file size (max 5MB)
        $maxSize = 5 * 1024 * 1024; // 5MB in bytes
        if ($file['size'] > $maxSize) {
            $response->json(['success' => false, 'message' => 'File size exceeds 5MB limit'], 400);
            return;
        }
        
        // Create uploads directory if it doesn't exist
        $uploadsDir = BASE_PATH . '/uploads/avatars';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'avatar_' . $userId . '_' . time() . '.' . $extension;
        $filePath = $uploadsDir . '/' . $filename;
        
        // Get current user to delete old avatar
        $user = $this->userModel->find($userId);
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            // Delete old avatar if exists
            if (!empty($user['avatar']) && file_exists(BASE_PATH . '/' . $user['avatar'])) {
                unlink(BASE_PATH . '/' . $user['avatar']);
            }
            
            // Update user avatar in database
            $avatarPath = 'uploads/avatars/' . $filename;
            $updated = $this->userModel->update($userId, [
                'avatar' => $avatarPath
            ]);
            
            if ($updated) {
                // Update session
                $_SESSION['user']['avatar'] = $avatarPath;
                
                $response->json([
                    'success' => true,
                    'message' => 'Avatar updated successfully',
                    'avatar_url' => APP_URL . '/' . $avatarPath
                ]);
            } else {
                // Delete uploaded file if database update failed
                unlink($filePath);
                $response->json(['success' => false, 'message' => 'Failed to update avatar in database'], 500);
            }
        } else {
            $response->json(['success' => false, 'message' => 'Failed to move uploaded file'], 500);
        }
    }

    /**
     * Remove avatar
     */
    public function removeAvatar(Request $request, Response $response): void
    {
        $loggedInUser = $_SESSION['user'] ?? null;
        
        if (!$loggedInUser) {
            $response->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }
        
        $userId = $loggedInUser['id'];
        
        // Get current user
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            $response->json(['success' => false, 'message' => 'User not found'], 404);
            return;
        }
        
        // Delete avatar file if exists
        if (!empty($user['avatar']) && file_exists(BASE_PATH . '/' . $user['avatar'])) {
            unlink(BASE_PATH . '/' . $user['avatar']);
        }
        
        // Update database
        $updated = $this->userModel->update($userId, [
            'avatar' => null
        ]);
        
        if ($updated) {
            // Update session
            $_SESSION['user']['avatar'] = null;
            
            $response->json([
                'success' => true,
                'message' => 'Avatar removed successfully'
            ]);
        } else {
            $response->json(['success' => false, 'message' => 'Failed to remove avatar'], 500);
        }
    }
}
