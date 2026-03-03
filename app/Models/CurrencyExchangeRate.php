<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class CurrencyExchangeRate extends Model
{
    protected string $table = 'currency_exchange_rates';

    /**
     * Get exchange rate between two currencies
     */
    public function getExchangeRate(int $fromCurrencyId, int $toCurrencyId): ?float
    {
        // If same currency, rate is 1
        if ($fromCurrencyId === $toCurrencyId) {
            return 1.0;
        }

        $sql = "SELECT exchange_rate FROM {$this->table} 
                WHERE from_currency_id = :from_currency AND to_currency_id = :to_currency 
                AND is_active = 1 
                ORDER BY updated_at DESC LIMIT 1";
        
        $result = Database::fetch($sql, [
            'from_currency' => $fromCurrencyId,
            'to_currency' => $toCurrencyId
        ]);

        return $result ? (float)$result['exchange_rate'] : null;
    }

    /**
     * Get all active exchange rates
     */
    public function getAllRates(): array
    {
        $sql = "SELECT cer.*, 
                fc.curre_name as from_currency_name, fc.sign as from_currency_sign,
                tc.curre_name as to_currency_name, tc.sign as to_currency_sign
                FROM {$this->table} cer
                LEFT JOIN tbl_currency_type fc ON cer.from_currency_id = fc.currency_id
                LEFT JOIN tbl_currency_type tc ON cer.to_currency_id = tc.currency_id
                WHERE cer.is_active = 1
                ORDER BY fc.curre_name, tc.curre_name";
        
        return Database::fetchAll($sql);
    }

    /**
     * Create or update exchange rate
     */
    public function setExchangeRate(int $fromCurrencyId, int $toCurrencyId, float $rate): bool
    {
        // Check if rate already exists
        $existing = $this->getExchangeRateRecord($fromCurrencyId, $toCurrencyId);
        
        if ($existing) {
            return $this->updateExchangeRate($existing['id'], $rate);
        } else {
            return $this->createExchangeRate($fromCurrencyId, $toCurrencyId, $rate);
        }
    }

    /**
     * Get exchange rate record
     */
    private function getExchangeRateRecord(int $fromCurrencyId, int $toCurrencyId): ?array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE from_currency_id = :from_currency AND to_currency_id = :to_currency";
        
        return Database::fetch($sql, [
            'from_currency' => $fromCurrencyId,
            'to_currency' => $toCurrencyId
        ]);
    }

    /**
     * Create new exchange rate
     */
    private function createExchangeRate(int $fromCurrencyId, int $toCurrencyId, float $rate): bool
    {
        $sql = "INSERT INTO {$this->table} (from_currency_id, to_currency_id, exchange_rate, effective_date, is_active, created_by) 
                VALUES (:from_currency, :to_currency, :rate, :effective_date, 1, :created_by)";
        
        $userId = $_SESSION['user']['id'] ?? 1;
        
        Database::query($sql, [
            'from_currency' => $fromCurrencyId,
            'to_currency' => $toCurrencyId,
            'rate' => $rate,
            'effective_date' => date('Y-m-d'),
            'created_by' => $userId
        ]);

        return true;
    }

    /**
     * Update existing exchange rate
     */
    private function updateExchangeRate(int $id, float $rate): bool
    {
        $sql = "UPDATE {$this->table} SET exchange_rate = :rate, updated_at = CURRENT_TIMESTAMP 
                WHERE id = :id";
        
        Database::query($sql, [
            'rate' => $rate,
            'id' => $id
        ]);

        return true;
    }

    /**
     * Convert amount between currencies
     */
    public function convertAmount(float $amount, int $fromCurrencyId, int $toCurrencyId): array
    {
        $rate = $this->getExchangeRate($fromCurrencyId, $toCurrencyId);
        
        if ($rate === null) {
            return [
                'success' => false,
                'error' => 'Exchange rate not found',
                'converted_amount' => 0,
                'rate' => 0
            ];
        }

        $convertedAmount = $amount * $rate;

        return [
            'success' => true,
            'original_amount' => $amount,
            'converted_amount' => $convertedAmount,
            'rate' => $rate,
            'from_currency_id' => $fromCurrencyId,
            'to_currency_id' => $toCurrencyId
        ];
    }
}