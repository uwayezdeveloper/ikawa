<?php
session_start();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Response.php';
require_once __DIR__ . '/../controllers/UsersController.php';
require_once __DIR__ . '/../controllers/SettingController.php';
require_once __DIR__ . '/../controllers/InventoryController.php';
require_once __DIR__ . '/../controllers/AccountController.php';
require_once __DIR__ . '/../controllers/ExpenseCategoryController.php';
require_once __DIR__ . '/../controllers/ExpenseController.php';
require_once __DIR__ . '/../controllers/ReceiptTypeController.php';
require_once __DIR__ . '/../controllers/ExpenseConsumeController.php';
require_once __DIR__ . '/../controllers/ExpenseConsumerController.php';
require_once __DIR__ . '/../controllers/SellizeController.php';
require_once __DIR__ . '/../controllers/CategoryController.php';
require_once __DIR__ . '/../controllers/CategoryTypeController.php';
require_once __DIR__ . '/../controllers/UnityController.php';
require_once __DIR__ . '/../controllers/CategoryTypeUnityController.php';
require_once __DIR__ . '/../controllers/FinancialController.php';
require_once __DIR__ . '/../controllers/InadvanceController.php';
require_once __DIR__ . '/../controllers/InvestmentController.php';
require_once __DIR__ . '/../controllers/StockController.php';
require_once __DIR__ . '/../controllers/ProductionTransferController.php';
require_once __DIR__ . '/../controllers/ProductMixingController.php';
require_once __DIR__ . '/../controllers/SellingController.php';


require_once __DIR__ . '/../controllers/TransferProductController.php';
require_once __DIR__ . '/../controllers/DriverController.php';
use Controllers\DriverController;
use Controllers\TransferProductController;

use Controllers\SellizeController;
use Controllers\CategoryController;
use Controllers\CategoryTypeController;
use Controllers\UnityController;
use Controllers\CategoryTypeUnityController;
use Controllers\ExpenseController;
use Controllers\ExpenseConsumeController;
use Controllers\ExpenseConsumerController;
use Controllers\ExpenseCategoryController;
use Controllers\InvestmentController;
use Controllers\ReportsController;
use Controllers\AccountController;
use Controllers\UsersController;
use Controllers\SettingController;
use Config\Response;
use Controllers\InventoryController;
use Controllers\FinancialController;
use Controllers\InadvanceController;
use Controllers\ProductionTransferController;
use Controllers\StockController;
use Controllers\ProductMixingController;
use Controllers\SellingController;

// Get request URI
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove base path dynamically - CHANGE THIS LINE FOR PRODUCTION
$basePath = '/ikawa.itectab.rw/_ikawa'; // Full path for localhost setup
$route = str_replace($basePath, '', $requestUri);
$route = rtrim($route, '/');
$route = $route === '' ? '/' : $route;



// Routing
switch ( true ) {
    case $route === '/users/login':
    $controller = new UsersController();
    $controller->login();
    break;
    case $route === '/users/get-all-users':
    $controller = new UsersController();
    $loc_id = $_GET[ 'loc_id' ];
    $controller->getUsers($loc_id);
    break;
    case $route === '/users/logout':
    $controller = new UsersController();
    $controller->logout();
    break;
    case $route === '/users/extend-session':
    $controller = new UsersController();
    $controller->extendSession();
    break;

    case $route === '/users/create':
    $controller = new UsersController();
    $controller->createUser();
    break;
    case $route === '/users/update':
    $controller = new UsersController();
    $controller->updateUser();
    break;
    case preg_match( '#^/users/delete/(\d+)$#', $route, $matches ):
    $controller = new UsersController();
    $controller->deleteUser( $matches[ 1 ] );
    break;

    // roles
    case $route === '/settings/createrole':
    $settingcontroller = new SettingController();
    $settingcontroller->CreateRole();
    break;

    case $route === '/settings/roles':
    $settingcontroller = new SettingController();
    $settingcontroller->getAllRoles();
    break;
    case $route === '/settings/updateroles':
    $settingcontroller = new SettingController();
    $settingcontroller->UpdateRole();
    break;

    //location
    case $route === '/settings/createlocation':
    $settingcontroller = new SettingController();
    $settingcontroller->createHeadQuater();
    break;

    case $route === '/settings/location':
    $settingcontroller = new SettingController();
    $settingcontroller->getAllLocation();
    break;

    case $route === '/settings/updatelocation':
    $settingcontroller = new SettingController();
    $settingcontroller->UpdateLocation();
    break;



    // receipt types
    case $route === '/receipt-types/get-all':
    $receiptTypeController = new ReceiptTypeController();
    $receiptTypeController->getAllReceiptTypes();
    break;

    case $route === '/receipt-types/get-by-id':
    $receiptTypeController = new ReceiptTypeController();
    $receiptTypeController->getReceiptTypeById();
    break;

    case $route === '/receipt-types/create':
    $receiptTypeController = new ReceiptTypeController();
    $receiptTypeController->createReceiptType();
    break;

    case $route === '/receipt-types/update':
    $receiptTypeController = new ReceiptTypeController();
    $receiptTypeController->updateReceiptType();
    break;

    case $route === '/receipt-types/delete':
    $receiptTypeController = new ReceiptTypeController();
    $receiptTypeController->deleteReceiptType();
    break;


    case preg_match('#^/category-type-units/get-type-unity-by-category/(\d+)$#', $route, $matches):
    $categoryTypeUnityController = new CategoryTypeUnityController();
    $categoryTypeUnityController->getTypeUnityByCategory($matches[1]);
    break;




    //inventory
    case $route === '/inventory/getsuppliers':
    $inventorycontroller = new InventoryController();
    $inventorycontroller->SupplierList();
    break;
    case $route === '/inventory/createsupplier':
    $inventorycontroller = new InventoryController();
    $inventorycontroller->createSupplier();
    break;
    case $route === '/inventory/updatesupplier':
    $inventorycontroller = new InventoryController();
    $inventorycontroller->UpdateSupplier();
    break;

    case preg_match( '#^/inventory/deletesupplier/(\d+)$#', $route, $matches ):
    $inventorycontroller = new InventoryController();
    $inventorycontroller->deleteSupplier( $matches[ 1 ] );
    break;

    // company
    case $route === '/settings/createcompany':
    $settingcontroller = new SettingController();
    $settingcontroller->CreateCompany();
    break;

    case $route === '/settings/getcompany':
    $settingcontroller = new SettingController();
    $settingcontroller->getCompanyInfo();
    break;

    case $route === '/settings/updatecompany':
    $settingcontroller = new SettingController();
    $settingcontroller->UpdateCompanyData();
    break;

    // accounts
    case $route === '/accounts/get-all':
    $accountController = new AccountController();
    $accountController->getAllAccounts();
    break;

    case $route === '/accounts/get-allbylocation':
    $accountController = new AccountController();
    $accountController->getAccountsByLocation();
    break;

    case $route === '/accounts/create':
    $accountController = new AccountController();
    $accountController->createAccount();
    break;

    case $route === '/accounts/update':
    $accountController = new AccountController();
    $accountController->updateAccount();
    break;

    case preg_match( '#^/accounts/delete/(\d+)$#', $route, $matches ):
    $accountController = new AccountController();
    $accountController->deleteAccount( $matches[ 1 ] );
    break;

    case preg_match( '#^/accounts/reactivate/(\d+)$#', $route, $matches ):
    $accountController = new AccountController();
    $accountController->reactivateAccount( $matches[ 1 ] );
    break;
    
    
  // investments (account recharge)
    case $route === '/investments/create':
    $investmentController = new InvestmentController();
    $investmentController->createInvestment();
    break;

    case $route === '/investments/get-by-location':
    $investmentController = new InvestmentController();
    $investmentController->getInvestmentsByLocation();
    break;

    case $route === '/investments/pending':
    $investmentController = new InvestmentController();
    $investmentController->getPendingInvestments();
    break;

    case $route === '/investments/approve':
    $investmentController = new InvestmentController();
    $investmentController->approveInvestment();
    break;

    case $route === '/investments/reject':
    $investmentController = new InvestmentController();
    $investmentController->rejectInvestment();
    break;

    case $route === '/investments/my-rejected':
    $investmentController = new InvestmentController();
    $investmentController->getMyRejectedInvestments();
    break;

    


    
    
    // payment modes
    case $route === '/settings/paymentmodes':
    $settingcontroller = new SettingController();
    $settingcontroller->getPaymentModes();
    break;

    // expenses
    case $route === '/expenses/get-all':
    $expenseController = new ExpenseController();
    $expenseController->getAllExpenses();
    break;

    case $route === '/expenses/create':
    $expenseController = new ExpenseController();
    $expenseController->createExpense();
    break;

    case $route === '/expenses/update':
    $expenseController = new ExpenseController();
    $expenseController->updateExpense();
    break;

    case preg_match( '#^/expenses/delete/(\d+)$#', $route, $matches ):
    $expenseController = new ExpenseController();
    $expenseController->deleteExpense( $matches[ 1 ] );
    break;

    case preg_match( '#^/expenses/by-category/(\d+)$#', $route, $matches ):
    $expenseController = new ExpenseController();
    $expenseController->getExpensesByCategory( $matches[ 1 ] );
    break;

    // expense categories
    case $route === '/expense-categories/get-all':
    $expenseCategoryController = new ExpenseCategoryController();
    $expenseCategoryController->getAllCategories();
    break;

    case $route === '/expense-categories/create':
    $expenseCategoryController = new ExpenseCategoryController();
    $expenseCategoryController->createCategory();
    break;

    case $route === '/expense-categories/update':
    $expenseCategoryController = new ExpenseCategoryController();
    $expenseCategoryController->updateCategory();
    break;

    case $route === '/expense-categories/delete':
    $expenseCategoryController = new ExpenseCategoryController();
    $expenseCategoryController->deleteCategory();
    break;

    case preg_match( '#^/expense-categories/check-in-use/(\d+)$#', $route, $matches ):
    $expenseCategoryController = new ExpenseCategoryController();
    $expenseCategoryController->checkCategoryInUse( $matches[ 1 ] );
    break;

  // Get expense consumed statement
    case $route === '/expense-consume/statement':
    $expenseConsumeController = new ExpenseConsumeController();
    $expenseConsumeController->getExpenseStatement();
    break;
    // expense consumers
    case $route === '/expense-consumers/get-all':
    $expenseConsumerController = new ExpenseConsumerController();
    $expenseConsumerController->getAllConsumers();
    break;

    case $route === '/expense-consumers/create':
    $expenseConsumerController = new ExpenseConsumerController();
    $expenseConsumerController->createConsumer();
    break;

    case $route === '/expense-consumers/update':
    $expenseConsumerController = new ExpenseConsumerController();
    $expenseConsumerController->updateConsumer();
    break;

    case $route === '/expense-consumers/delete':
    $expenseConsumerController = new ExpenseConsumerController();
    $expenseConsumerController->deleteConsumer();
    break;

    case preg_match( '#^/expense-consumers/check-in-use/(\d+)$#', $route, $matches ):
    $expenseConsumerController = new ExpenseConsumerController();
    $expenseConsumerController->checkConsumerInUse( $matches[ 1 ] );
    break;

    // expense consume
    case $route === '/expense-consume/get-all':
    $expenseConsumeController = new ExpenseConsumeController();
    $expenseConsumeController->getAllExpenseConsumes();
    break;

    case $route === '/expense-consume/create':
    $expenseConsumeController = new ExpenseConsumeController();
    $expenseConsumeController->createExpenseConsume();
    break;

    case $route === '/expense-consume/update':
    $expenseConsumeController = new ExpenseConsumeController();
    $expenseConsumeController->updateExpenseConsume();
    break;

    case $route === '/expense-consume/delete':
    $expenseConsumeController = new ExpenseConsumeController();
    $expenseConsumeController->deleteExpenseConsume();
    break;

    case preg_match( '#^/expense-consume/by-station/(\d+)$#', $route, $matches ):
    $expenseConsumeController = new ExpenseConsumeController();
    $expenseConsumeController->getExpensesByStation( $matches[ 1 ] );
    break;

    case $route === '/expense-consume/total':
    $expenseConsumeController = new ExpenseConsumeController();
    $expenseConsumeController->getTotalExpensesByPeriod();
    break;

    // Get accounts by payment mode
    case preg_match( '#^/accounts/by-mode/(\d+)$#', $route, $matches ):
    $expenseConsumeController = new ExpenseConsumeController();
    $expenseConsumeController->getAccountsByPaymentMode( $matches[ 1 ] );
    break;

    // Categories
    case $route === '/categories/create':
    $categoryController = new CategoryController();
    $categoryController->create();
    break;
    case $route === '/categories/get-all-categories':
    $categoryController = new CategoryController();
    $categoryController->getAllCategories();
    break;
    case $route === '/categories/update':
    $categoryController = new CategoryController();

    $categoryController->update();
    break;
    case $route === '/categories/delete':
    $categoryController = new CategoryController();
    $categoryController->delete();
    break;

    // Sellize
    case $route === '/sallize/create':
    $sellizeController = new SellizeController();
    $sellizeController->create();
    break;
    case $route === '/sallize/get-all-sallize':
    $sellizeController = new SellizeController();
    $sellizeController->getAllSallize();
    break;
    case $route === '/sallize/update':
    $sellizeController = new SellizeController();

    $sellizeController->update();
    break;
    case $route === '/sallize/delete':
    $sellizeController = new SellizeController();
    $sellizeController->delete();
    break;

    // Category Types
    case $route === '/category-types/create':
    $categoryTypeController = new CategoryTypeController();
    $categoryTypeController->create();
    break;
    case $route === '/category-types/get-all-category-types':
    $categoryTypeController = new CategoryTypeController();
    $categoryTypeController->getAllCategoryTypes();
    break;
    case $route === '/category-types/update':
    $categoryTypeController = new CategoryTypeController();

    $categoryTypeController->update();
    break;
    case $route === '/category-types/delete':
    $categoryTypeController = new CategoryTypeController();
    $categoryTypeController->delete();
    break;
    case $route === '/categories/get-active-categories':
    $categoryController = new CategoryController();
    $categoryController->getAllCategories();
    // You may want to create a specific method for active categories
    break;

    // Unity
    case $route === '/unity/create':
    $unityController = new UnityController();
    $unityController->create();
    break;
    case $route === '/unity/get-all-unity':
    $unityController = new UnityController();
    $unityController->getAllUnity();
    break;
    case $route === '/unity/update':
    $unityController = new UnityController();

    $unityController->update();
    break;
    case $route === '/unity/delete':
    $unityController = new UnityController();
    $unityController->delete();
    break;

    // Category Type Units
    case $route === '/category-type-units/create':
    $categoryTypeUnityController = new CategoryTypeUnityController();
    $categoryTypeUnityController->create();
    break;
    case $route === '/category-type-units/get-all-assignments':
    $categoryTypeUnityController = new CategoryTypeUnityController();
    $categoryTypeUnityController->getAllAssignments();
    break;
    case $route === '/category-type-units/update':
    $categoryTypeUnityController = new CategoryTypeUnityController();
    $categoryTypeUnityController->update();
    break;
    case $route === '/category-type-units/delete':
    $categoryTypeUnityController = new CategoryTypeUnityController();
    $categoryTypeUnityController->delete();
    break;
    // countries
    case $route === '/inventory/getcountries':
    $inventorycontroller = new InventoryController();
    $inventorycontroller->CountryLists();
    break;
    case $route === '/inventory/createclient':
    $inventorycontroller = new InventoryController();
    $inventorycontroller->CreateClient();
    break;
    case $route === '/inventory/get-all-clients':
    $inventorycontroller = new InventoryController();
    $inventorycontroller->ClientsList();
    break;
    case $route === '/inventory/upadteclient':
    $inventorycontroller = new InventoryController();
    $inventorycontroller->ClientUpdate();
    break;

    case preg_match( '#^/inventory/deleteclient/(\d+)$#', $route, $matches ):
    $inventorycontroller = new InventoryController();
    $inventorycontroller->deleteClient( $matches[ 1 ] );
    break;
    // Accounts Transfers
    case $route === '/financial/createtransfer':
    $financialcontroller = new FinancialController();
    $financialcontroller->CreateTransfers();
    break;

    // Get Suppliersfor type
    case $route === '/inadvance/get-suppliers':
    $inadvancecontroller = new InadvanceController();
    $inadvancecontroller->getSupplier();
    break;
       // Request Inadvance

    case $route === '/inadvance/create':
    $inadvancecontroller = new InadvanceController();
    $inadvancecontroller->createInAdvance();
    break;

    case $route === '/inadvance/advancelist':
    $inadvancecontroller = new InadvanceController();
    $loc_id = $_GET[ 'loc_id' ];
    $inadvancecontroller->getAllAdvances( $loc_id );
    break;

    case $route === '/inadvance/advancelistpending':
    $inadvancecontroller = new InadvanceController();
    $loc_id = $_GET[ 'loc_id' ];
    $inadvancecontroller->getAllAdvancesPending( $loc_id );
    break;
    case $route === '/inadvance/rejectadvance':
    $inadvancecontroller = new InadvanceController();
    $inadvancecontroller->rejectAdvancePending();
    break;

    case $route === '/inadvance/approveadvancerequest':
    $inadvancecontroller = new InadvanceController();
    $inadvancecontroller->approveinadvancerequest();
    break;

    case $route === '/inadvance/advancelistapproved':
    $inadvancecontroller = new InadvanceController();
    $loc_id = $_GET[ 'loc_id' ];
    $inadvancecontroller->getAllAdvancesApproved( $loc_id );
    break;

    case $route === '/inadvance/disburse':
    $inadvancecontroller = new InadvanceController();
    $inadvancecontroller->disburseAdvancesApproved();
    break;
    
   // access menus
    case $route === '/settings/menus':
    $settingcontroller = new SettingController();
    $settingcontroller->getAllMenus();
    break;

    case $route === '/settings/createaccess':
    $settingcontroller = new SettingController();
    $settingcontroller->createAccessUsers();
    break;

    case $route === '/settings/getaccessdata':
    $settingcontroller = new SettingController();
    $settingcontroller->getAllAccessUsers();
    break;

    case preg_match( '#^/settings/removeaccessonrole/(\d+)$#', $route, $matches ):
    $settingcontroller = new SettingController();
    $settingcontroller->removeAccessRole( $matches[ 1 ] );
    break;
    
    
    ############################################################# TRANSFER STAFF STARTS HERE #########################################
    
    
    
    // Transfer Products (Station to Warehouse)
    case $route === '/transfer/get-stations':
    $transferController = new TransferProductController();
    $transferController->getStations();
    break;

    case $route === '/transfer/get-warehouses':
    $transferController = new TransferProductController();
    $transferController->getWarehouses();
    break;

    case $route === '/transfer/get-categories':
    $transferController = new TransferProductController();
    $transferController->getActiveCategories();
    break;

    case preg_match( '#^/transfer/get-category-types/(\d+)$#', $route, $matches ):
    $transferController = new TransferProductController();
    $transferController->getCategoryTypesByCategory( $matches[ 1 ] );
    break;

    case preg_match( '#^/transfer/get-units-by-type/(\d+)$#', $route, $matches ):
    $transferController = new TransferProductController();
    $transferController->getUnitsByTypeId( $matches[ 1 ] );
    break;

    case $route === '/transfer/create':
    $transferController = new TransferProductController();
    $transferController->createTransfer();
    break;

    case $route === '/transfer/get-all':
    $transferController = new TransferProductController();
    $transferController->getAllTransfers();
    break;

    case preg_match( '#^/transfer/get-items/(\d+)$#', $route, $matches ):
    $transferController = new TransferProductController();
    $transferController->getTransferItems( $matches[ 1 ] );
    break;

    case preg_match( '#^/transfer/get/(\d+)$#', $route, $matches ):
    $transferController = new TransferProductController();
    $transferController->getTransferById( $matches[ 1 ] );
    break;

    case $route === '/transfer/receive':
    $transferController = new TransferProductController();
    $transferController->receiveTransfer();
    break;

    case $route === '/transfer/reject':
    $transferController = new TransferProductController();
    $transferController->rejectTransfer();
    break;

    // Stock-based transfer endpoints
    case preg_match( '#^/transfer/stock-types/(\d+)$#', $route, $matches ):
    $transferController = new TransferProductController();
    $transferController->getStockCategoryTypes( $matches[ 1 ] );
    break;

    case preg_match( '#^/transfer/stock-units/(\d+)$#', $route, $matches ):
    $transferController = new TransferProductController();
    $transferController->getStockUnits( $matches[ 1 ] );
    break;

    case preg_match( '#^/transfer/available-quantity/(\d+)$#', $route, $matches ):
    $transferController = new TransferProductController();
    $transferController->getAvailableQuantity( $matches[ 1 ] );
    break;

    // Driver routes
    case $route === '/drivers/get-all':
    $driverController = new DriverController();
    $driverController->getAllDrivers();
    break;

    case $route === '/drivers/get':
    $driverController = new DriverController();
    $driverController->getDriverById();
    break;

    case $route === '/drivers/create':
    $driverController = new DriverController();
    $driverController->createDriver();
    break;

    case $route === '/drivers/update':
    $driverController = new DriverController();
    $driverController->updateDriver();
    break;

    case $route === '/drivers/delete':
    $driverController = new DriverController();
    $driverController->deleteDriver();
    break;
    
    
    
    // suppluier payment
    case preg_match('#^/stock/supplier-statement/(\d+)$#', $route, $matches):
    $stockController = new StockController();
    $stockController->supplierStatement($matches[1]);
    break;

    case $route === '/stock/get-stock-suppliers':
    $stockController = new StockController();
    $stockController->getStockSuppliers();
    break;

    case $route === '/stock/process-supplier-payment':
    $stockController = new StockController();
    $stockController->processSupplierPayment();
    break;

    
    
    
    // Production Transfers
    case $route === '/transfers/get-available-stock':
    $transferController = new ProductionTransferController();
    $transferController->getAvailableStock();
    break;

    case $route === '/transfers/create-multiple':
    $transferController = new ProductionTransferController();
    $transferController->createMultiple();
    break;

    case $route === '/transfers/get-all':
    $transferController = new ProductionTransferController();
    $transferController->getTransfers();
    break;

    case preg_match('#^/transfers/get-details/(\d+)$#', $route, $matches):
    $transferController = new ProductionTransferController();
    $transferController->getTransferDetails($matches[1]);
    break;

    case preg_match('#^/transfers/get-detail-info/(\d+)$#', $route, $matches):
    $transferController = new ProductionTransferController();
    $transferController->getTransferDetailInfo($matches[1]);
    break;

    case $route === '/transfers/approve':
    $transferController = new ProductionTransferController();
    $transferController->approveTransfer();
    break;

    case $route === '/transfers/return':
    $transferController = new ProductionTransferController();
    $transferController->returnTransfer();
    break;

    case $route === '/transfers/receive-production':
    $transferController = new ProductionTransferController();
    $transferController->receiveProduction();
    break;
    
    // Stock Management
    case $route === '/stock/create':
    $stockController = new StockController();
    $stockController->create();
    break;

    case $route === '/stock/create-multiple':
    $stockController = new StockController();
    $stockController->createMultiple();
    break;

    case $route === '/stock/get-detailed-stock':
    $stockController = new StockController();
    $stockController->getDetailedStock();
    break;

    case $route === '/stock/get-summary-stock':
    $stockController = new StockController();
    $stockController->getSummaryStock();
    break;
    
    case $route === '/stock/get-pending':
    $stockController = new StockController();
    $stockController->getPendingStock();
    break;

    case $route === '/stock/approve-price':
    $stockController = new StockController();
    $stockController->approvePrice();
    break;

    case $route === '/stock/approve-multiple':
    $stockController = new StockController();
    $stockController->approveMultiple();
    break;
    
    // Product Mixing
    case $route === '/mixing/get-available-stock':
    $mixingController = new ProductMixingController();
    $mixingController->getAvailableStock();
    break;

    case $route === '/mixing/create':
    $mixingController = new ProductMixingController();
    $mixingController->create();
    break;

    case $route === '/mixing/get-history':
    $mixingController = new ProductMixingController();
    $mixingController->getHistory();
    break;

    case preg_match('#^/mixing/get-details/(\d+)$#', $route, $matches):
    $mixingController = new ProductMixingController();
    $mixingController->getDetails($matches[1]);
    break;
    
    // Selling Management
    case $route === '/selling/test':
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Test endpoint working', 'route' => $route]);
    exit;
    
    case $route === '/selling/get-available-stock':
    error_log("Route hit: /selling/get-available-stock");
    $sellingController = new SellingController();
    $sellingController->getAvailableStock();
    break;

    case $route === '/selling/create-sale':
    $sellingController = new SellingController();
    $sellingController->createSale();
    break;

    case $route === '/selling/get-sales-history':
    $sellingController = new SellingController();
    $sellingController->getSalesHistory();
    break;

    case preg_match('#^/selling/get-sale-details/(\d+)$#', $route, $matches):
    $sellingController = new SellingController();
    $sellingController->getSaleDetails($matches[1]);
    break;

    // Client Management
    case $route === '/selling/get-clients':
    $sellingController = new SellingController();
    $sellingController->getClients();
    break;

    case $route === '/selling/create-client':
    $sellingController = new SellingController();
    $sellingController->createClient();
    break;

    // Sales Price Approval
    case $route === '/selling/get-pending-sales':
    $sellingController = new SellingController();
    $sellingController->getPendingSales();
    break;

    case $route === '/selling/update-sale-prices':
    $sellingController = new SellingController();
    $sellingController->updateSalePrices();
    break;

    // Get sale details (alternative route without ID in URL)
    case $route === '/selling/get-sale-details':
    $sellingController = new SellingController();
    $sale_id = isset($_GET['sale_id']) ? intval($_GET['sale_id']) : 0;
    $sellingController->getSaleDetails($sale_id);
    break;


    ############################################################# TRANSFER STAFF ENDS HERE ###########################################

    default:
    Response::error( 'Endpoint not found', 404 );
    break;
}
