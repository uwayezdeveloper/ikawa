<?php
namespace Controllers;

require_once __DIR__ . '/../models/Driver.php';
require_once __DIR__ . '/../config/Response.php';

use Models\Driver;
use Config\Response;

class DriverController
{
    private $driver;

    public function __construct()
    {
        $this->driver = new Driver();
    }

    /**
     * Get all drivers
     */
    public function getAllDrivers(): void
    {
        $drivers = $this->driver->getAllDrivers();
        
        if ($drivers === false) {
            Response::error('Failed to fetch drivers');
        }
        
        Response::success('Drivers retrieved successfully', $drivers);
    }

    /**
     * Get driver by ID
     */
    public function getDriverById(): void
    {
        $driver_id = $_GET['driver_id'] ?? null;
        
        if (!$driver_id) {
            Response::error('Driver ID is required');
        }

        $driver = $this->driver->getDriverById($driver_id);
        
        if (!$driver) {
            Response::error('Driver not found');
        }
        
        Response::success('Driver retrieved successfully', $driver);
    }

    /**
     * Create new driver
     */
    public function createDriver(): void
    {
        $data = [
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'email' => $_POST['email'] ?? '',
            'license_number' => $_POST['license_number'] ?? ''
        ];

        // Validate required fields
        if (empty($data['first_name']) || empty($data['last_name']) || 
            empty($data['phone']) || empty($data['license_number'])) {
            Response::error('First name, last name, phone and license number are required');
        }

        // Check if driver exists
        $exists = $this->driver->exists($data['phone'], $data['email'], $data['license_number']);
        if ($exists) {
            $fieldNames = [
                'phone' => 'Phone number',
                'email' => 'Email',
                'license_number' => 'License number'
            ];
            Response::error($fieldNames[$exists] . ' already exists');
        }

        $result = $this->driver->createDriver($data);
        
        if (!$result) {
            Response::error('Failed to create driver');
        }
        
        Response::success('Driver created successfully');
    }

    /**
     * Update driver
     */
    public function updateDriver(): void
    {
        $data = [
            'driver_id' => $_POST['driver_id'] ?? '',
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'email' => $_POST['email'] ?? '',
            'license_number' => $_POST['license_number'] ?? ''
        ];

        // Validate required fields
        if (empty($data['driver_id']) || empty($data['first_name']) || 
            empty($data['last_name']) || empty($data['phone']) || empty($data['license_number'])) {
            Response::error('Driver ID, first name, last name, phone and license number are required');
        }

        // Check if driver exists for update
        $exists = $this->driver->existsUpdate($data['phone'], $data['email'], $data['license_number'], $data['driver_id']);
        if ($exists) {
            $fieldNames = [
                'phone' => 'Phone number',
                'email' => 'Email',
                'license_number' => 'License number'
            ];
            Response::error($fieldNames[$exists] . ' already exists');
        }

        $result = $this->driver->updateDriver($data);
        
        if (!$result) {
            Response::error('Failed to update driver');
        }
        
        Response::success('Driver updated successfully');
    }

    /**
     * Delete driver
     */
    public function deleteDriver(): void
    {
        $driver_id = $_POST['driver_id'] ?? null;
        
        if (!$driver_id) {
            Response::error('Driver ID is required');
        }

        $result = $this->driver->deleteDriver($driver_id);
        
        if (!$result) {
            Response::error('Failed to delete driver');
        }
        
        Response::success('Driver deleted successfully');
    }
}
