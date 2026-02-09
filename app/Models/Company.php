<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Company extends Model
{
    protected string $table = 'company';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'full_name',
        'short_name',
        'email',
        'phone',
        'address',
        'logo'
    ];

    /**
     * Get company information (always returns first record)
     */
    public function getCompany(): ?array
    {
        $sql = "SELECT * FROM {$this->table} LIMIT 1";
        return Database::fetch($sql);
    }

    /**
     * Update company information
     */
    public function updateCompany(array $data): bool
    {
        // Check if company record exists
        $company = $this->getCompany();
        
        if ($company) {
            // Update existing record
            $sql = "UPDATE {$this->table} SET 
                    full_name = :full_name,
                    short_name = :short_name,
                    email = :email,
                    phone = :phone,
                    address = :address,
                    updated_at = NOW()
                    WHERE id = :id";
            
            $stmt = Database::query($sql, [
                'full_name' => $data['full_name'],
                'short_name' => $data['short_name'] ?? null,
                'email' => $data['email'],
                'phone' => $data['phone'],
                'address' => $data['address'] ?? null,
                'id' => $company['id']
            ]);
            return $stmt->rowCount() >= 0;
        } else {
            // Insert new record
            $sql = "INSERT INTO {$this->table} (full_name, short_name, email, phone, address) 
                    VALUES (:full_name, :short_name, :email, :phone, :address)";
            
            $stmt = Database::query($sql, [
                'full_name' => $data['full_name'],
                'short_name' => $data['short_name'] ?? null,
                'email' => $data['email'],
                'phone' => $data['phone'],
                'address' => $data['address'] ?? null
            ]);
            return $stmt->rowCount() > 0;
        }
    }

    /**
     * Update company logo
     */
    public function updateLogo(string $logoFilename): bool
    {
        $company = $this->getCompany();
        
        if ($company) {
            $sql = "UPDATE {$this->table} SET logo = :logo, updated_at = NOW() WHERE id = :id";
            $stmt = Database::query($sql, [
                'logo' => $logoFilename,
                'id' => $company['id']
            ]);
            return $stmt->rowCount() >= 0;
        }
        
        return false;
    }

    /**
     * Get company logo filename
     */
    public function getLogo(): ?string
    {
        $company = $this->getCompany();
        return $company['logo'] ?? null;
    }
}
