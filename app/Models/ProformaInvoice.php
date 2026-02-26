<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class ProformaInvoice extends Model
{
    protected string $table = 'tbl_proforma_invoice';
    protected string $primaryKey = 'id';
    protected string $itemsTable = 'tbl_proforma_invoice_items';

    /**
     * Get all invoices with related data
     */
    public function getAllWithRelations($page = 1, $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT pi.*, 
                    c.name as client_name,
                    ct.curre_name as currency_name,
                    ct.sign as currency_sign,
                    u.first_name, u.last_name
                FROM {$this->table} pi
                LEFT JOIN clients c ON pi.client_id = c.id
                LEFT JOIN tbl_currency_type ct ON pi.currency_id = ct.currency_id
                LEFT JOIN users u ON pi.prepared_by = u.id
                ORDER BY pi.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        return Database::fetchAll($sql, ['limit' => $perPage, 'offset' => $offset]);
    }

    /**
     * Get invoice by ID with relations
     */
    public function getByIdWithRelations($id): ?array
    {
        $sql = "SELECT pi.*, 
                    c.name as client_name, c.phone, c.email, c.address,
                    ct.curre_name as currency_name, ct.sign as currency_sign,
                    u.first_name, u.last_name
                FROM {$this->table} pi
                LEFT JOIN clients c ON pi.client_id = c.id
                LEFT JOIN tbl_currency_type ct ON pi.currency_id = ct.currency_id
                LEFT JOIN users u ON pi.prepared_by = u.id
                WHERE pi.{$this->primaryKey} = :id";
        
        return Database::fetch($sql, ['id' => $id]);
    }

    /**
     * Get invoice items
     */
    public function getItems($invoiceId): array
    {
        $sql = "SELECT pii.*, 
                    mu.name as unit_name, 
                    mu.symbol as unit_symbol
                FROM {$this->itemsTable} pii
                LEFT JOIN measurement_units mu ON pii.unit_id = mu.id
                WHERE pii.invoice_id = :invoice_id 
                ORDER BY pii.id ASC";
        return Database::fetchAll($sql, ['invoice_id' => $invoiceId]);
    }

    /**
     * Create invoice with items
     */
    public function createWithItems(array $data, array $items): int
    {
        Database::getInstance()->beginTransaction();
        
        try {
            // Insert main invoice
            $sql = "INSERT INTO {$this->table} 
                    (code, client_id, currency_id, invoice_type, prepared_by, total_amount)
                    VALUES (:code, :client_id, :currency_id, :invoice_type, :prepared_by, :total_amount)";
            
            Database::query($sql, $data);
            $invoiceId = (int) Database::getInstance()->lastInsertId();
            
            // Insert items
            $itemSql = "INSERT INTO {$this->itemsTable} 
                        (invoice_id, product_id, product_name, unit_id, quantity, price, total)
                        VALUES (:invoice_id, :product_id, :product_name, :unit_id, :quantity, :price, :total)";
            
            foreach ($items as $item) {
                $item['invoice_id'] = $invoiceId;
                Database::query($itemSql, $item);
            }
            
            Database::getInstance()->commit();
            return $invoiceId;
            
        } catch (\Exception $e) {
            Database::getInstance()->rollback();
            throw $e;
        }
    }

    /**
     * Generate invoice code
     */
    public function generateCode($id): string
    {
        return 'GIHACHASHTAG' . $id . '-' . date('Y-m-d');
    }

    /**
     * Update invoice code
     */
    public function updateCode($id, $code): bool
    {
        $sql = "UPDATE {$this->table} SET code = :code WHERE {$this->primaryKey} = :id";
        Database::query($sql, ['id' => $id, 'code' => $code]);
        return true;
    }
}
