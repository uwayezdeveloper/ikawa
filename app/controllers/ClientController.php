<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Database;
use App\Models\Client;

class ClientController extends Controller
{
    private Client $clientModel;

    public function __construct()
    {
        parent::__construct();
        $this->clientModel = new Client();
    }

    /**
     * Check if user has menu access
     */
    protected function hasMenuAccess(string $menuIdentifier): bool
    {
        $user = $_SESSION['user'] ?? null;
        if (!$user) return false;
        
        // Admin always has access
        $isSuperAdmin = ((int)($user['role_id'] ?? 0) === 1);
        if ($isSuperAdmin) return true;
        
        $menuIdentifiers = $user['menu_identifiers'] ?? [];
        return in_array($menuIdentifier, $menuIdentifiers);
    }

    /**
     * Display clients list
     */
    public function index($request, $response)
    {
        $user = $_SESSION['user'] ?? null;
        
        // Check menu access
        if (!$this->hasMenuAccess('clients')) {
            $_SESSION['flash_error'] = 'You do not have permission to access clients';
            return $response->redirect(APP_URL . '/dashboard');
        }

        $clients = $this->clientModel->getAllWithTypes();
        $stats = $this->clientModel->getStats();
        $clientTypes = $this->clientModel->getClientTypesWithIdentifiers();
        $statsByType = $this->clientModel->getStatsByType();

        return View::render('clients/index', [
            'title' => 'Clients',
            'user' => $user,
            'clients' => $clients,
            'stats' => $stats,
            'clientTypes' => $clientTypes,
            'statsByType' => $statsByType
        ], 'main');
    }

    /**
     * Handle client actions
     */
    public function handleAction($request, $response)
    {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'create':
                if (!$this->hasMenuAccess('clients')) {
                    $_SESSION['flash_error'] = 'You do not have permission to create clients';
                    return $response->redirect(APP_URL . '/clients');
                }
                return $this->createClient($request, $response);

            case 'update':
                if (!$this->hasMenuAccess('clients')) {
                    $_SESSION['flash_error'] = 'You do not have permission to edit clients';
                    return $response->redirect(APP_URL . '/clients');
                }
                return $this->updateClient($request, $response);

            case 'delete':
                if (!$this->hasMenuAccess('clients')) {
                    $_SESSION['flash_error'] = 'You do not have permission to delete clients';
                    return $response->redirect(APP_URL . '/clients');
                }
                return $this->deleteClient($request, $response);

            case 'toggle_status':
                if (!$this->hasMenuAccess('clients')) {
                    $_SESSION['flash_error'] = 'You do not have permission to change client status';
                    return $response->redirect(APP_URL . '/clients');
                }
                return $this->toggleStatus($request, $response);

            case 'get_client':
                return $this->getClient($request, $response);

            case 'get_type_identifiers':
                return $this->getTypeIdentifiers($request, $response);

            case 'search':
                return $this->searchClients($request, $response);

            default:
                $_SESSION['flash_error'] = 'Invalid action';
                return $response->redirect(APP_URL . '/clients');
        }
    }

    /**
     * Create new client
     */
    private function createClient($request, $response)
    {
        $name = trim($_POST['name'] ?? '');
        $clientType = $_POST['client_type'] ?? 'individual';
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $contactPerson = trim($_POST['contact_person'] ?? '');
        $contactPhone = trim($_POST['contact_phone'] ?? '');
        $tinNumber = trim($_POST['tin_number'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $status = $_POST['status'] ?? 'active';

        // Validation
        if (empty($name)) {
            $_SESSION['flash_error'] = 'Client name is required';
            return $response->redirect(APP_URL . '/clients');
        }

        // Check if name exists
        if ($this->clientModel->nameExists($name)) {
            $_SESSION['flash_error'] = 'A client with this name already exists';
            return $response->redirect(APP_URL . '/clients');
        }

        try {
            $clientId = $this->clientModel->createClient([
                'name' => $name,
                'client_type' => $clientType,
                'phone' => $phone,
                'email' => $email,
                'address' => $address,
                'contact_person' => $contactPerson,
                'contact_phone' => $contactPhone,
                'tin_number' => $tinNumber,
                'notes' => $notes,
                'status' => $status
            ]);

            // Save identifier values
            $identifierValues = $_POST['identifiers'] ?? [];
            if (!empty($identifierValues)) {
                $this->clientModel->saveIdentifierValues($clientId, $identifierValues);
            }

            $_SESSION['flash_success'] = 'Client created successfully';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to create client: ' . $e->getMessage();
        }

        return $response->redirect(APP_URL . '/clients');
    }

    /**
     * Update existing client
     */
    private function updateClient($request, $response)
    {
        $id = intval($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $clientType = $_POST['client_type'] ?? 'individual';
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $contactPerson = trim($_POST['contact_person'] ?? '');
        $contactPhone = trim($_POST['contact_phone'] ?? '');
        $tinNumber = trim($_POST['tin_number'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $status = $_POST['status'] ?? 'active';

        if (!$id || empty($name)) {
            $_SESSION['flash_error'] = 'Invalid client data';
            return $response->redirect(APP_URL . '/clients');
        }

        // Check if name exists (excluding current)
        if ($this->clientModel->nameExists($name, $id)) {
            $_SESSION['flash_error'] = 'A client with this name already exists';
            return $response->redirect(APP_URL . '/clients');
        }

        try {
            $this->clientModel->updateClient($id, [
                'name' => $name,
                'client_type' => $clientType,
                'phone' => $phone,
                'email' => $email,
                'address' => $address,
                'contact_person' => $contactPerson,
                'contact_phone' => $contactPhone,
                'tin_number' => $tinNumber,
                'notes' => $notes,
                'status' => $status
            ]);

            // Save identifier values
            $identifierValues = $_POST['identifiers'] ?? [];
            $this->clientModel->saveIdentifierValues($id, $identifierValues);

            $_SESSION['flash_success'] = 'Client updated successfully';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to update client: ' . $e->getMessage();
        }

        return $response->redirect(APP_URL . '/clients');
    }

    /**
     * Delete client
     */
    private function deleteClient($request, $response)
    {
        $id = intval($_POST['id'] ?? 0);

        if (!$id) {
            $_SESSION['flash_error'] = 'Invalid client ID';
            return $response->redirect(APP_URL . '/clients');
        }

        try {
            $this->clientModel->deleteClient($id);
            $_SESSION['flash_success'] = 'Client deleted successfully';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to delete client: ' . $e->getMessage();
        }

        return $response->redirect(APP_URL . '/clients');
    }

    /**
     * Toggle client status
     */
    private function toggleStatus($request, $response)
    {
        $id = intval($_POST['id'] ?? 0);

        if (!$id) {
            $_SESSION['flash_error'] = 'Invalid client ID';
            return $response->redirect(APP_URL . '/clients');
        }

        try {
            $this->clientModel->toggleStatus($id);
            $_SESSION['flash_success'] = 'Client status updated';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to update status: ' . $e->getMessage();
        }

        return $response->redirect(APP_URL . '/clients');
    }

    /**
     * Get single client (AJAX)
     */
    private function getClient($request, $response)
    {
        header('Content-Type: application/json');
        
        $id = intval($_POST['id'] ?? 0);
        
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Invalid ID']);
            exit;
        }

        $client = $this->clientModel->findById($id);
        
        if ($client) {
            // Get identifier values for this client
            $client['identifier_values'] = $this->clientModel->getIdentifierValues($id);
            echo json_encode(['success' => true, 'data' => $client]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Client not found']);
        }
        exit;
    }

    /**
     * Get identifiers for a client type (AJAX)
     */
    private function getTypeIdentifiers($request, $response)
    {
        header('Content-Type: application/json');
        
        $typeIdentifier = trim($_POST['type_identifier'] ?? '');
        
        if (empty($typeIdentifier)) {
            echo json_encode(['success' => false, 'message' => 'Type identifier required']);
            exit;
        }

        $identifiers = $this->clientModel->getIdentifiersByTypeIdentifier($typeIdentifier);
        echo json_encode(['success' => true, 'identifiers' => $identifiers]);
        exit;
    }

    /**
     * Search clients (AJAX)
     */
    private function searchClients($request, $response)
    {
        header('Content-Type: application/json');
        
        $query = trim($_POST['query'] ?? '');
        
        if (empty($query)) {
            $clients = $this->clientModel->getAll();
        } else {
            $clients = $this->clientModel->search($query);
        }

        echo json_encode(['success' => true, 'data' => $clients]);
        exit;
    }
}
