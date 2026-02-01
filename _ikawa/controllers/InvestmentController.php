<?php
namespace Controllers;

require_once __DIR__ . '/../config/Response.php';
require_once __DIR__ . '/../models/Investment.php';

use Models\Investment;
use Config\Response;

class InvestmentController
{
    private $investmentModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->investmentModel = new Investment();
    }

    // POST /investments/create
    public function createInvestment()
    {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['loc_id'])) {
            Response::error('Unauthorized', 401);
            return;
        }

        $in_amount = isset($_POST['in_amount']) ? (int)$_POST['in_amount'] : 0;
        $account_id = isset($_POST['account_id']) ? (int)$_POST['account_id'] : 0;
        $description = $_POST['description'] ?? null;
        $reciept = $_POST['reciept'] ?? null;
        $source = $_POST['source'] ?? null;
        $action = $_POST['action'] ?? 'recharge';

        if ($in_amount <= 0 || $account_id <= 0) {
            Response::error('Invalid input: amount and account are required', 400);
            return;
        }

        $result = $this->investmentModel->createInvestment(
            $in_amount,
            $_SESSION['user_id'],
            $account_id,
            $_SESSION['loc_id'],
            $description,
            $reciept,
            $source,
            $action
        );

        if ($result === true) {
            Response::success('Investment submitted successfully. Awaiting approval.');
        } else {
            Response::error('Failed to create investment: ' . $result, 500);
        }
    }

    // GET /investments/get-by-location
    public function getInvestmentsByLocation()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['loc_id'])) {
            Response::error('Location not set', 401);
            return;
        }
        $data = $this->investmentModel->getInvestmentsByLocation($_SESSION['loc_id']);
        Response::success('Success', $data);
    }

    /**
     * Get all pending investments (sts=1) for approval
     */
    public function getPendingInvestments()
    {
        $result = $this->investmentModel->getPendingInvestments();
        Response::success('Pending investments retrieved', $result);
    }

    /**
     * Approve an investment
     */
    public function approveInvestment()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Invalid request method', 405);
            return;
        }

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            Response::error('User not authenticated', 401);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['in_id'])) {
            Response::error('Investment ID is required', 400);
            return;
        }

        $result = $this->investmentModel->approveInvestment($input['in_id'], $_SESSION['user_id']);

        if ($result === true) {
            Response::success('Investment approved and amount added to account');
        } else {
            Response::error($result, 500);
        }
    }

    /**
     * Reject an investment
     */
    public function rejectInvestment()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Invalid request method', 405);
            return;
        }

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            Response::error('User not authenticated', 401);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['in_id']) || !isset($input['rejector_comment'])) {
            Response::error('Investment ID and rejection comment are required', 400);
            return;
        }

        $result = $this->investmentModel->rejectInvestment(
            $input['in_id'], 
            $_SESSION['user_id'],
            $input['rejector_comment']
        );

        if ($result === true) {
            Response::success('Investment rejected');
        } else {
            Response::error($result, 500);
        }
    }

    /**
     * Get rejected investments for current user
     */
    public function getMyRejectedInvestments()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            Response::error('User not authenticated', 401);
            return;
        }

        $result = $this->investmentModel->getRejectedInvestmentsByUser($_SESSION['user_id']);
        Response::success('Rejected investments retrieved', $result);
    }}