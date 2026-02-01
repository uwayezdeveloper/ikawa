<?php
namespace Controllers;

// Include dependencies
require_once __DIR__ . '/../models/Account.php';
require_once __DIR__ . '/../config/Response.php';

use Models\Account;
use Config\Response;

class AccountController
{
    private $accountModel;

    public function __construct()
    {
        $this->accountModel = new Account();
    }

    public function getAllAccounts()
    {
        $accounts = $this->accountModel->getAllAccounts();

        if ($accounts !== false) {
            Response::success('Accounts retrieved successfully!', $accounts);
        } else {
            Response::error('Failed to retrieve accounts', 500);
        }
    }

    public function getAccountsByLocation()
    {
        // Get st_id from query parameter
        $st_id = isset($_GET['st_id']) ? $_GET['st_id'] : null;
        
        if (empty($st_id)) {
            Response::error('Location ID (st_id) is required', 400);
            return;
        }
        
        $accounts = $this->accountModel->getAccountsByLocation($st_id);

        if ($accounts !== false) {
            Response::success('Accounts retrieved successfully!', $accounts);
        } else {
            Response::error('Failed to retrieve accounts', 500);
        }
    }

    public function getAccountById($acc_id)
    {
        $account = $this->accountModel->getAccountById($acc_id);

        if ($account !== false) {
            Response::success('Account retrieved successfully!', $account);
        } else {
            Response::error('Account not found', 404);
        }
    }

    public function createAccount()
    {
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

        // Get logged-in user's location from session
        session_start();
        if (empty($_SESSION['loc_id'])) {
            Response::error('User location not found in session', 403);
            return;
        }
        $st_id = $_SESSION['loc_id'];

        $required = ['mode_id', 'acc_name', 'acc_reference_num'];

        foreach ($required as $field) {
            if (empty($input[$field])) {
                Response::error("Missing field: {$field}", 400);
                return;
            }
        }

        $acc_name = trim($input['acc_name']);
        $acc_reference_num = trim($input['acc_reference_num']);
        
        // Check if reference number already exists
        if ($this->accountModel->referenceNumberExists($acc_reference_num)) {
            Response::error('Reference number already exists', 409);
            return;
        }
        
        $data = [
            'mode_id' => $input['mode_id'],
            'acc_name' => $acc_name,
            'acc_reference_num' => $acc_reference_num,
            'st_id' => $st_id
        ];

        $result = $this->accountModel->createAccount($data);

        if ($result) {
            Response::success('Account created successfully!', null, 201);
        } else {
            Response::error('Failed to create account', 500);
        }
    }

    public function updateAccount()
    {
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

        $required = ['acc_id', 'mode_id', 'acc_name', 'acc_reference_num', 'st_id'];

        foreach ($required as $field) {
            if (empty($input[$field])) {
                Response::error("Missing field: {$field}", 400);
                return;
            }
        }

        $acc_reference_num = trim($input['acc_reference_num']);
        $acc_id = $input['acc_id'];
        
        // Check if reference number already exists (excluding current account)
        if ($this->accountModel->referenceNumberExists($acc_reference_num, $acc_id)) {
            Response::error('Reference number already exists', 409);
            return;
        }

        $data = [
            'acc_id' => $acc_id,
            'mode_id' => $input['mode_id'],
            'acc_name' => trim($input['acc_name']),
            'acc_reference_num' => $acc_reference_num,
            'st_id' => $input['st_id']
        ];

        $result = $this->accountModel->updateAccount($data);

        if ($result) {
            Response::success('Account updated successfully!');
        } else {
            Response::error('Failed to update account', 500);
        }
    }

    public function deleteAccount($acc_id)
    {
        if (empty($acc_id)) {
            Response::error('Account ID is required', 400);
            return;
        }

        $result = $this->accountModel->deleteAccount($acc_id);

        if ($result) {
            Response::success('Account set to onhold successfully!');
        } else {
            Response::error('Failed to update account status', 500);
        }
    }

    public function reactivateAccount($acc_id)
    {
        if (empty($acc_id)) {
            Response::error('Account ID is required', 400);
            return;
        }

        $result = $this->accountModel->reactivateAccount($acc_id);

        if ($result) {
            Response::success('Account reactivated successfully!');
        } else {
            Response::error('Failed to reactivate account', 500);
        }
    }
}
