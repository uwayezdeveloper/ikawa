<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;

/**
 * Setting Model
 * Handles application settings
 */
class Setting extends Model
{
    protected string $table = 'settings';
    protected string $primaryKey = 'id';
    
    protected array $fillable = [
        'setting_key',
        'setting_value',
        'setting_type'
    ];

    /**
     * Get a setting value by key
     */
    public function get(string $key, $default = null): ?string
    {
        $sql = "SELECT setting_value FROM {$this->table} WHERE setting_key = :key";
        $result = Database::fetch($sql, ['key' => $key]);
        
        return $result ? $result['setting_value'] : $default;
    }

    /**
     * Set a setting value
     */
    public function set(string $key, string $value, string $type = 'text'): bool
    {
        $sql = "INSERT INTO {$this->table} (setting_key, setting_value, setting_type) 
                VALUES (:key, :value, :type) 
                ON DUPLICATE KEY UPDATE setting_value = :value2, setting_type = :type2";
        
        $stmt = Database::query($sql, [
            'key' => $key,
            'value' => $value,
            'type' => $type,
            'value2' => $value,
            'type2' => $type
        ]);
        
        return $stmt !== false;
    }

    /**
     * Get multiple settings by keys
     */
    public function getMultiple(array $keys): array
    {
        if (empty($keys)) return [];
        
        $placeholders = implode(',', array_fill(0, count($keys), '?'));
        $sql = "SELECT setting_key, setting_value FROM {$this->table} WHERE setting_key IN ($placeholders)";
        
        $results = Database::fetchAll($sql, $keys);
        
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        
        return $settings;
    }

    /**
     * Get all settings
     */
    public function getAllSettings(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY setting_key ASC";
        $results = Database::fetchAll($sql);
        
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = [
                'value' => $row['setting_value'],
                'type' => $row['setting_type']
            ];
        }
        
        return $settings;
    }

    /**
     * Get company settings
     */
    public function getCompanySettings(): array
    {
        $keys = [
            'company_name',
            'company_short_name',
            'company_email',
            'company_phone',
            'company_address',
            'company_logo'
        ];
        
        return $this->getMultiple($keys);
    }

    /**
     * Update company settings
     */
    public function updateCompanySettings(array $data): bool
    {
        $success = true;
        
        foreach ($data as $key => $value) {
            if (strpos($key, 'company_') === 0) {
                if (!$this->set($key, $value)) {
                    $success = false;
                }
            }
        }
        
        return $success;
    }
}
