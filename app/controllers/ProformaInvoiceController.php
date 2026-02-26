<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\ProformaInvoice;
use App\Models\Client;
use App\Models\CurrencyType;
use App\Models\CategoryType;
use App\Models\CategoryTypeUnit;

class ProformaInvoiceController extends Controller
{
    protected ProformaInvoice $invoiceModel;
    protected Client $clientModel;
    protected CurrencyType $currencyModel;
    protected CategoryType $categoryTypeModel;
    protected CategoryTypeUnit $categoryTypeUnitModel;

    public function __construct()
    {
        parent::__construct();
        $this->invoiceModel = new ProformaInvoice();
        $this->clientModel = new Client();
        $this->currencyModel = new CurrencyType();
        $this->categoryTypeModel = new CategoryType();
        $this->categoryTypeUnitModel = new CategoryTypeUnit();
    }

    /**
     * List all invoices
     */
    public function index(Request $request, Response $response)
    {
        try {
            $page = (int) ($_GET['page'] ?? 1);
            $invoices = $this->invoiceModel->getAllWithRelations($page);
            
            $data = [
                'invoices' => $invoices,
                'title' => 'Proforma Invoices',
                'user' => $_SESSION['user'] ?? []
            ];
            
            $this->view('finance/proforma-invoice/index', $data, 'main');
        } catch (\Exception $e) {
            error_log("ProformaInvoiceController::index - Error: " . $e->getMessage());
            $_SESSION['error'] = 'Error loading invoices. Please ensure database tables are created. Error: ' . $e->getMessage();
            $data = [
                'invoices' => [],
                'title' => 'Proforma Invoices',
                'user' => $_SESSION['user'] ?? []
            ];
            $this->view('finance/proforma-invoice/index', $data, 'main');
        }
    }

    /**
     * Show create form
     */
    public function create(Request $request, Response $response)
    {
        try {
            $clients = $this->clientModel->getActive();
            $currencies = $this->currencyModel->getAll();
            $categoryTypes = $this->categoryTypeModel->getActive();
            
            $data = [
                'clients' => $clients,
                'currencies' => $currencies,
                'categoryTypes' => $categoryTypes,
                'title' => 'Create Proforma Invoice',
                'user' => $_SESSION['user'] ?? []
            ];
            
            $this->view('finance/proforma-invoice/create', $data, 'main');
        } catch (\Exception $e) {
            error_log("ProformaInvoiceController::create - Error: " . $e->getMessage());
            $_SESSION['error'] = 'Error loading form data. Please ensure all required tables exist. Error: ' . $e->getMessage();
            return $response->redirect(APP_URL . '/finance/proforma-invoice');
        }
    }

    /**
     * Store new invoice
     */
    public function store(Request $request, Response $response)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $response->redirect(APP_URL . '/finance/proforma-invoice');
        }

        $userId = $_SESSION['user']['id'] ?? null;
        
        if (!$userId) {
            $_SESSION['error'] = 'User session not found';
            return $response->redirect(APP_URL . '/finance/proforma-invoice');
        }

        // Validate input
        $errors = [];
        
        $clientId = (int) ($_POST['client_id'] ?? 0);
        $currencyId = (int) ($_POST['currency_id'] ?? 0);
        $invoiceType = trim($_POST['invoice_type'] ?? 'Proforma');
        
        if (empty($clientId)) {
            $errors[] = 'Please select a client';
        }
        
        if (empty($currencyId)) {
            $errors[] = 'Please select a currency';
        }
        
        // Validate items
        $items = [];
        $totalAmount = 0;
        
        if (isset($_POST['products']) && is_array($_POST['products'])) {
            foreach ($_POST['products'] as $index => $productId) {
                $quantity = (float) ($_POST['quantities'][$index] ?? 0);
                $price = (float) ($_POST['prices'][$index] ?? 0);
                $productName = trim($_POST['product_names'][$index] ?? '');
                $unitId = (int) ($_POST['unit_ids'][$index] ?? 0);
                
                if ($productId > 0 && $quantity > 0 && $price > 0) {
                    $itemTotal = $quantity * $price;
                    $totalAmount += $itemTotal;
                    
                    $items[] = [
                        'product_id' => (int) $productId,
                        'product_name' => $productName,
                        'unit_id' => $unitId,
                        'quantity' => $quantity,
                        'price' => $price,
                        'total' => $itemTotal
                    ];
                }
            }
        }
        
        if (empty($items)) {
            $errors[] = 'Please add at least one product';
        }
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $_POST;
            return $response->redirect(APP_URL . '/finance/proforma-invoice/create');
        }

        try {
            // Create invoice with temporary code
            $invoiceData = [
                'code' => 'TEMP', // Temporary, will be updated after insert
                'client_id' => $clientId,
                'currency_id' => $currencyId,
                'invoice_type' => $invoiceType,
                'prepared_by' => $userId,
                'total_amount' => $totalAmount
            ];
            
            $invoiceId = $this->invoiceModel->createWithItems($invoiceData, $items);
            
            // Generate and update code
            $code = $this->invoiceModel->generateCode($invoiceId);
            $this->invoiceModel->updateCode($invoiceId, $code);
            
            $_SESSION['success'] = 'Proforma invoice created successfully';
            return $response->redirect(APP_URL . '/finance/proforma-invoice/' . $invoiceId);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
            return $response->redirect(APP_URL . '/finance/proforma-invoice/create');
        }
    }

    /**
     * Show invoice details
     */
    public function show(Request $request, Response $response, array $params)
    {
        $id = (int) $params['id'];
        $invoice = $this->invoiceModel->getByIdWithRelations($id);
        
        if (!$invoice) {
            $_SESSION['error'] = 'Invoice not found';
            return $response->redirect(APP_URL . '/finance/proforma-invoice');
        }
        
        $items = $this->invoiceModel->getItems($id);
        
        $data = [
            'invoice' => $invoice,
            'items' => $items,
            'title' => 'Proforma Invoice Details',
            'user' => $_SESSION['user'] ?? []
        ];
        
        $this->view('finance/proforma-invoice/show', $data, 'main');
    }

    /**
     * Get units for a category type (AJAX)
     */
    public function getTypeUnits(Request $request, Response $response)
    {
        $typeId = (int) ($_GET['type_id'] ?? 0);
        
        if (!$typeId) {
            return $this->json(['success' => false, 'message' => 'Type ID required']);
        }
        
        $units = $this->categoryTypeUnitModel->getUnitsByType($typeId);
        return $this->json(['success' => true, 'units' => $units]);
    }
}
