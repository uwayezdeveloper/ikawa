<?php
/**
 * Web Routes
 */

use App\Core\Application;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\UserController;
use App\Controllers\RoleController;
use App\Controllers\PermissionController;
use App\Controllers\SettingsController;
use App\Controllers\LocationController;
use App\Controllers\ProductCategoryController;
use App\Controllers\CategoryTypeController;
use App\Controllers\MeasurementUnitController;
use App\Controllers\CategoryTypeUnitController;
use App\Controllers\LocationTypeCategoryController;
use App\Controllers\SupplierTypeController;
use App\Controllers\SupplierController;
use App\Controllers\PaymentModeController;
use App\Controllers\AccountController;
use App\Controllers\AccountRechargeController;
use App\Controllers\ExpenseCategoryController;
use App\Controllers\ExpenseTypeController;
use App\Controllers\ExpenseConsumerController;
use App\Controllers\ExpenseTransactionController;
use App\Controllers\WorkerLoanController;
use App\Controllers\WorkerLoanPaymentController;
use App\Controllers\ProformaInvoiceController;
use App\Controllers\ClientController;
use App\Controllers\ClientTypeController;
use App\Controllers\NonExploitableCategoryController;
use App\Controllers\NonExploitableTransactionController;
use App\Controllers\PropertyController;
use App\Controllers\PropertyTypeController;
use App\Controllers\CertificationController;
use App\Controllers\CertificationTransactionController;
$app = Application::getInstance();
$router = $app->getRouter();

// ============================================
// PUBLIC ROUTES (No authentication required)
// ============================================

// Home page - redirect to login or dashboard
$router->get('/', function($request, $response) {
    if (isset($_SESSION['user'])) {
        $response->redirect(APP_URL . '/dashboard');
    } else {
        $response->redirect(APP_URL . '/login');
    }
});

// ============================================
// GUEST ROUTES (Only for non-authenticated users)
// ============================================

// Auth pages
$router->get('/login', [AuthController::class, 'showLogin'], ['GuestMiddleware']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegister'], ['GuestMiddleware']);
$router->post('/register', [AuthController::class, 'register']);

// ============================================
// PROTECTED ROUTES (Authentication required)
// ============================================

// Logout
$router->get('/logout', [AuthController::class, 'logout']);
$router->post('/logout', [AuthController::class, 'logout']);

// Dashboard
$router->get('/dashboard', [DashboardController::class, 'index'], ['AuthMiddleware']);

// Users management
$router->get('/users', [UserController::class, 'index'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/users/create', [UserController::class, 'create'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/users', [UserController::class, 'store'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/users/{id}', [UserController::class, 'show'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/users/{id}/edit', [UserController::class, 'edit'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/users/{id}', [UserController::class, 'update'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/users/{id}/delete', [UserController::class, 'delete'], ['AuthMiddleware', 'AdminMiddleware']);

// Clients management
$router->get('/clients', [ClientController::class, 'index'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/clients/action', [ClientController::class, 'handleAction'], ['AuthMiddleware', 'AdminMiddleware']);

// Client Types management
$router->get('/client-types', [ClientTypeController::class, 'index'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/client-types/action', [ClientTypeController::class, 'handleAction'], ['AuthMiddleware', 'AdminMiddleware']);

// Roles management
$router->get('/roles', [RoleController::class, 'index'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/roles', [RoleController::class, 'store'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/roles/{id}', [RoleController::class, 'update'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/roles/{id}/delete', [RoleController::class, 'delete'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/roles/{id}/toggle-status', [RoleController::class, 'toggleStatus'], ['AuthMiddleware', 'AdminMiddleware']);

// Permissions management
$router->get('/permissions', [PermissionController::class, 'index'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/permissions', [PermissionController::class, 'store'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/permissions/roles', [PermissionController::class, 'rolePermissions'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/permissions/roles', [PermissionController::class, 'saveRolePermissions'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/permissions/{id}', [PermissionController::class, 'update'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/permissions/{id}/delete', [PermissionController::class, 'delete'], ['AuthMiddleware', 'AdminMiddleware']);

// Settings management
$router->get('/settings', [SettingsController::class, 'index'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/settings/company', [SettingsController::class, 'company'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/settings/company', [SettingsController::class, 'updateCompany'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/settings/location-types', [SettingsController::class, 'locationTypes'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/settings/location-types', [SettingsController::class, 'handleLocationType'], ['AuthMiddleware', 'AdminMiddleware']);

// Locations management
$router->get('/locations', [LocationController::class, 'index'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/locations', [LocationController::class, 'handleAction'], ['AuthMiddleware', 'AdminMiddleware']);

// Products management - Categories
$router->get('/products/categories', [ProductCategoryController::class, 'index'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/products/categories', [ProductCategoryController::class, 'handleAction'], ['AuthMiddleware', 'AdminMiddleware']);

// Products management - Category Types
$router->get('/products/category-types', [CategoryTypeController::class, 'index'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/products/category-types', [CategoryTypeController::class, 'handleAction'], ['AuthMiddleware', 'AdminMiddleware']);

// Products management - Measurement Units
$router->get('/products/measurement-units', [MeasurementUnitController::class, 'index'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/products/measurement-units', [MeasurementUnitController::class, 'handleAction'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/products/measurement-units/conversion-table', [MeasurementUnitController::class, 'conversionTable'], ['AuthMiddleware', 'AdminMiddleware']);

// Products management - Type Unit Assignments
$router->get('/products/type-units', [CategoryTypeUnitController::class, 'index'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/products/type-units', [CategoryTypeUnitController::class, 'handleAction'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/products/type-units/get-units', [CategoryTypeUnitController::class, 'getTypeUnits'], ['AuthMiddleware', 'AdminMiddleware']);

// Settings - Location Type Categories (assign product categories to location types)
$router->get('/settings/location-categories', [LocationTypeCategoryController::class, 'index'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/settings/location-categories', [LocationTypeCategoryController::class, 'handleAction'], ['AuthMiddleware', 'AdminMiddleware']);

// Suppliers management - Supplier Types
$router->get('/suppliers/types', [SupplierTypeController::class, 'index'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/suppliers/types', [SupplierTypeController::class, 'handleAction'], ['AuthMiddleware', 'AdminMiddleware']);

// Suppliers management - Suppliers
$router->get('/suppliers', [SupplierController::class, 'index'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/suppliers', [SupplierController::class, 'handleAction'], ['AuthMiddleware', 'AdminMiddleware']);

// Finance management - Payment Modes
$router->get('/finance/payment-modes', [PaymentModeController::class, 'index'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/finance/payment-modes', [PaymentModeController::class, 'handleAction'], ['AuthMiddleware', 'AdminMiddleware']);

// Finance management - Accounts
$router->get('/finance/accounts', [AccountController::class, 'index'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/finance/accounts', [AccountController::class, 'handleAction'], ['AuthMiddleware', 'AdminMiddleware']);

// Finance management - Account Recharge
$router->get('/finance/account-recharge', [AccountRechargeController::class, 'index'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/finance/account-recharge/process', [AccountRechargeController::class, 'recharge'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/finance/account-transfer', [AccountRechargeController::class, 'transfer'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/finance/account-transfer', [AccountRechargeController::class, 'recharge'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/finance/account-recharge/history', [AccountRechargeController::class, 'history'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/api/accounts/{id}/details', [AccountRechargeController::class, 'getAccountDetails'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/api/accounts/transfer-enabled', [AccountRechargeController::class, 'getTransferEnabledAccounts'], ['AuthMiddleware', 'AdminMiddleware']);
// Account activity report
$router->get('/finance/account-activity', [AccountRechargeController::class, 'activityReport'], ['AuthMiddleware', 'AdminMiddleware']);

// ============================================
// EXPENSE MANAGEMENT ROUTES (Under Finance)
// ============================================

// Expense Categories
$router->get('/finance/expense-categories', [ExpenseCategoryController::class, 'index'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/finance/expense-categories/create', [ExpenseCategoryController::class, 'create'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/finance/expense-categories/store', [ExpenseCategoryController::class, 'store'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/finance/expense-categories/{id}/edit', [ExpenseCategoryController::class, 'edit'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/finance/expense-categories/{id}/update', [ExpenseCategoryController::class, 'update'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/finance/expense-categories/{id}/delete', [ExpenseCategoryController::class, 'delete'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/finance/expense-categories/{id}/toggle-status', [ExpenseCategoryController::class, 'toggleStatus'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/api/finance/expense-categories/active', [ExpenseCategoryController::class, 'getActive'], ['AuthMiddleware', 'AdminMiddleware']);

// Expense Types
$router->get('/finance/expense-types', [ExpenseTypeController::class, 'index'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/finance/expense-types/create', [ExpenseTypeController::class, 'create'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/finance/expense-types/store', [ExpenseTypeController::class, 'store'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/finance/expense-types/{id}/edit', [ExpenseTypeController::class, 'edit'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/finance/expense-types/{id}/update', [ExpenseTypeController::class, 'update'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/finance/expense-types/{id}/delete', [ExpenseTypeController::class, 'delete'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/finance/expense-types/{id}/toggle-status', [ExpenseTypeController::class, 'toggleStatus'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/api/finance/expense-types/active', [ExpenseTypeController::class, 'getActive'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/api/finance/expense-types/category/{id}', [ExpenseTypeController::class, 'getByCategory'], ['AuthMiddleware', 'AdminMiddleware']);

// Expense Consumers
$router->get('/finance/expense-consumers', [ExpenseConsumerController::class, 'index'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/finance/expense-consumers/create', [ExpenseConsumerController::class, 'create'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/finance/expense-consumers/store', [ExpenseConsumerController::class, 'store'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/finance/expense-consumers/{id}/edit', [ExpenseConsumerController::class, 'edit'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/finance/expense-consumers/{id}/update', [ExpenseConsumerController::class, 'update'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/finance/expense-consumers/{id}/delete', [ExpenseConsumerController::class, 'delete'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/finance/expense-consumers/{id}/toggle-status', [ExpenseConsumerController::class, 'toggleStatus'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/api/finance/expense-consumers/active', [ExpenseConsumerController::class, 'getActive'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/api/finance/expense-consumers/search', [ExpenseConsumerController::class, 'search'], ['AuthMiddleware', 'AdminMiddleware']);

// Expense Transactions
$router->get('/finance/expense-transactions', [ExpenseTransactionController::class, 'index'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/finance/expense-transactions/create', [ExpenseTransactionController::class, 'create'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/finance/expense-transactions/store', [ExpenseTransactionController::class, 'store'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/finance/expense-transactions/{id}', [ExpenseTransactionController::class, 'show'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/finance/expense-transactions/{id}/status', [ExpenseTransactionController::class, 'updateStatus'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/finance/expense-transactions/export', [ExpenseTransactionController::class, 'export'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/api/finance/expense-types/by-category', [ExpenseTransactionController::class, 'getExpenseTypesByCategory'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/api/finance/consumers/search', [ExpenseTransactionController::class, 'searchConsumers'], ['AuthMiddleware', 'AdminMiddleware']);

// Worker Loan Requests
$router->get('/finance/loans/create', [WorkerLoanController::class, 'create'], ['AuthMiddleware']);
$router->post('/finance/loans/store', [WorkerLoanController::class, 'store'], ['AuthMiddleware']);
$router->get('/finance/loans/my-loans', [WorkerLoanController::class, 'myLoans'], ['AuthMiddleware']);

// Admin Loan Management
$router->get('/finance/loans', [WorkerLoanController::class, 'index'], ['AuthMiddleware', 'AdminMiddleware']);

// Loan Disbursement (must come before {id} route)
$router->get('/finance/loans/disbursement', [WorkerLoanController::class, 'disbursementIndex'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/finance/loans/disbursement/{id}', [WorkerLoanController::class, 'disbursementForm'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/finance/loans/disbursement/process', [WorkerLoanController::class, 'processDisbursement'], ['AuthMiddleware', 'AdminMiddleware']);

// Loan Statement routes (must come before {id} route)
$router->get('/finance/loans/statement', [WorkerLoanController::class, 'statementIndex'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/finance/loans/statement/generate', [WorkerLoanController::class, 'generateStatement'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/finance/loans/statement/{userId}', [WorkerLoanController::class, 'showStatement'], ['AuthMiddleware', 'AdminMiddleware']);

// Individual loan routes (must come after specific routes)
$router->get('/finance/loans/{id}', [WorkerLoanController::class, 'show'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/finance/loans/status', [WorkerLoanController::class, 'updateStatus'], ['AuthMiddleware', 'AdminMiddleware']);

// ============================================
// WORKER LOAN PAYMENT ROUTES (Admin only)
// ============================================
$router->get('/finance/loan-payments', [WorkerLoanPaymentController::class, 'index'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/finance/loan-payments/create', [WorkerLoanPaymentController::class, 'create'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/finance/loan-payments/store', [WorkerLoanPaymentController::class, 'store'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/finance/loan-payments/{id}', [WorkerLoanPaymentController::class, 'show'], ['AuthMiddleware', 'AdminMiddleware']);

// AJAX routes for loan payment functionality
$router->get('/api/loans/{id}/details', [WorkerLoanPaymentController::class, 'getLoanDetails'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/api/loans/{id}/payments', [WorkerLoanPaymentController::class, 'getPaymentsByLoan'], ['AuthMiddleware', 'AdminMiddleware']);

// ============================================
// PROFORMA INVOICE ROUTES
// ============================================
$router->get('/finance/proforma-invoice', [ProformaInvoiceController::class, 'index'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/finance/proforma-invoice/create', [ProformaInvoiceController::class, 'create'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/finance/proforma-invoice/store', [ProformaInvoiceController::class, 'store'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/finance/proforma-invoice/{id}', [ProformaInvoiceController::class, 'show'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/api/proforma/type-units', [ProformaInvoiceController::class, 'getTypeUnits'], ['AuthMiddleware', 'AdminMiddleware']);

// ============================================
// NON-EXPLOITABLE MANAGEMENT ROUTES
// ============================================

// Non-Exploitable Categories
$router->get('/non-exploitable/categories', [NonExploitableCategoryController::class, 'index'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/non-exploitable/categories/create', [NonExploitableCategoryController::class, 'create'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/non-exploitable/categories/store', [NonExploitableCategoryController::class, 'store'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/non-exploitable/categories/{id}/edit', [NonExploitableCategoryController::class, 'edit'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/non-exploitable/categories/{id}/update', [NonExploitableCategoryController::class, 'update'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/non-exploitable/categories/{id}/delete', [NonExploitableCategoryController::class, 'delete'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/non-exploitable/categories/{id}/toggle-status', [NonExploitableCategoryController::class, 'toggleStatus'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/api/non-exploitable/categories/active', [NonExploitableCategoryController::class, 'getActive'], ['AuthMiddleware', 'AdminMiddleware']);

// Non-Exploitable Transactions
$router->get('/non-exploitable/transactions', [NonExploitableTransactionController::class, 'index'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/non-exploitable/transactions/create', [NonExploitableTransactionController::class, 'create'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/non-exploitable/transactions/store', [NonExploitableTransactionController::class, 'store'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/non-exploitable/transactions/{id}', [NonExploitableTransactionController::class, 'show'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/non-exploitable/transactions/{id}/delete', [NonExploitableTransactionController::class, 'delete'], ['AuthMiddleware', 'AdminMiddleware']);

// API route for getting accounts by location
$router->get('/api/non-exploitable/accounts-by-location/{locationId}', [NonExploitableTransactionController::class, 'getAccountsByLocation'], ['AuthMiddleware', 'AdminMiddleware']);

// ============================================
// PROPERTY MANAGEMENT ROUTES
// ============================================

// Property Types
$router->get('/properties/types', [PropertyTypeController::class, 'index'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/properties/types', [PropertyTypeController::class, 'handleAction'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/api/properties/types/active', [PropertyTypeController::class, 'getActive'], ['AuthMiddleware', 'AdminMiddleware']);

// Properties
$router->get('/properties', [PropertyController::class, 'index'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/properties/create', [PropertyController::class, 'create'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/properties', [PropertyController::class, 'handleAction'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/api/properties/{id}', [PropertyController::class, 'getProperty'], ['AuthMiddleware', 'AdminMiddleware']);

// ============================================
// CERTIFICATION MANAGEMENT ROUTES
// ============================================

$router->get('/certification/categories', [CertificationController::class, 'index'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/certification/categories', [CertificationController::class, 'handleAction'], ['AuthMiddleware', 'AdminMiddleware']);
$router->get('/certification/transactions', [CertificationTransactionController::class, 'index'], ['AuthMiddleware', 'AdminMiddleware']);
$router->post('/certification/transactions', [CertificationTransactionController::class, 'handleAction'], ['AuthMiddleware', 'AdminMiddleware']);

// ============================================
// API ROUTES
// ============================================

// Auth API (public)
$router->post('/api/auth/login', [AuthController::class, 'apiLogin']);
$router->post('/api/auth/register', [AuthController::class, 'apiRegister']);
$router->post('/api/auth/refresh', [AuthController::class, 'apiRefresh']);

// Auth API (protected)
$router->post('/api/auth/logout', [AuthController::class, 'apiLogout'], ['ApiAuthMiddleware']);
$router->get('/api/auth/me', [AuthController::class, 'apiMe'], ['ApiAuthMiddleware']);

// Users API (protected)
$router->get('/api/users', [UserController::class, 'apiIndex'], ['ApiAuthMiddleware']);
$router->get('/api/users/stats', [UserController::class, 'apiStats'], ['ApiAuthMiddleware']);
$router->get('/api/users/{id}', [UserController::class, 'apiShow'], ['ApiAuthMiddleware']);
$router->put('/api/users/{id}', [UserController::class, 'apiUpdate'], ['ApiAuthMiddleware']);
$router->delete('/api/users/{id}', [UserController::class, 'apiDelete'], ['ApiAuthMiddleware']);

// Roles API (protected)
$router->get('/api/roles', [RoleController::class, 'apiIndex'], ['ApiAuthMiddleware']);
$router->get('/api/roles/active', [RoleController::class, 'apiActiveRoles'], ['ApiAuthMiddleware']);
$router->get('/api/roles/{id}', [RoleController::class, 'apiShow'], ['ApiAuthMiddleware']);
$router->post('/api/roles', [RoleController::class, 'apiStore'], ['ApiAuthMiddleware']);
$router->put('/api/roles/{id}', [RoleController::class, 'apiUpdate'], ['ApiAuthMiddleware']);
$router->delete('/api/roles/{id}', [RoleController::class, 'apiDelete'], ['ApiAuthMiddleware']);

// Permissions API (protected)
$router->get('/api/permissions', [PermissionController::class, 'apiIndex'], ['ApiAuthMiddleware']);
$router->get('/api/permissions/grouped', [PermissionController::class, 'apiGrouped'], ['ApiAuthMiddleware']);
$router->get('/api/roles/{id}/permissions', [PermissionController::class, 'apiRolePermissions'], ['ApiAuthMiddleware']);
$router->put('/api/roles/{id}/permissions', [PermissionController::class, 'apiUpdateRolePermissions'], ['ApiAuthMiddleware']);