<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class ProcessingStep extends Model
{
    protected string $table = 'processing_steps';
    
    protected array $fillable = [
        'name',
        'description',
        'step_order',
        'status'
    ];

    /**
     * Get all processing steps ordered by step_order
     */
    public function getAll(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY step_order ASC, id ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Get only active processing steps ordered by step_order
     */
    public function getActive(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY step_order ASC, id ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Get the first active processing step (lowest step_order)
     */
    public function getFirstStep(): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY step_order ASC LIMIT 1";
        return Database::fetch($sql);
    }

    /**
     * Get next order number
     */
    public function getNextOrder(): int
    {
        $sql = "SELECT MAX(step_order) as max_order FROM {$this->table}";
        $result = Database::fetch($sql);
        return ($result['max_order'] ?? 0) + 1;
    }

    /**
     * Move step up in order
     */
    public function moveUp(int $id): bool
    {
        $current = $this->find($id);
        if (!$current) return false;
        
        // Find the step with the next lower order
        $sql = "SELECT * FROM {$this->table} WHERE step_order < :order ORDER BY step_order DESC LIMIT 1";
        $previous = Database::fetch($sql, ['order' => $current['step_order']]);
        
        if (!$previous) return false;
        
        // Swap orders
        $this->update($id, ['step_order' => $previous['step_order']]);
        $this->update($previous['id'], ['step_order' => $current['step_order']]);
        
        return true;
    }

    /**
     * Move step down in order
     */
    public function moveDown(int $id): bool
    {
        $current = $this->find($id);
        if (!$current) return false;
        
        // Find the step with the next higher order
        $sql = "SELECT * FROM {$this->table} WHERE step_order > :order ORDER BY step_order ASC LIMIT 1";
        $next = Database::fetch($sql, ['order' => $current['step_order']]);
        
        if (!$next) return false;
        
        // Swap orders
        $this->update($id, ['step_order' => $next['step_order']]);
        $this->update($next['id'], ['step_order' => $current['step_order']]);
        
        return true;
    }

    /**
     * Change step order and swap with existing step at that position
     */
    public function changeOrder(int $id, int $newOrder): bool
    {
        $current = $this->find($id);
        if (!$current) return false;
        
        // If same order, nothing to do
        if ($current['step_order'] == $newOrder) return true;
        
        // Find step currently at the target order
        $sql = "SELECT * FROM {$this->table} WHERE step_order = :order AND id != :id";
        $existing = Database::fetch($sql, ['order' => $newOrder, 'id' => $id]);
        
        if ($existing) {
            // Swap: give the existing step the current step's order
            $this->update($existing['id'], ['step_order' => $current['step_order']]);
        }
        
        // Set the new order for current step
        $this->update($id, ['step_order' => $newOrder]);
        
        return true;
    }

    /**
     * Normalize order numbers to be sequential (1, 2, 3...)
     */
    public function normalizeOrder(): void
    {
        $steps = $this->getAll();
        $order = 1;
        foreach ($steps as $step) {
            if ($step['step_order'] != $order) {
                $this->update($step['id'], ['step_order' => $order]);
            }
            $order++;
        }
    }

    /**
     * Get total count of steps
     */
    public function getCount(): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $result = Database::fetch($sql);
        return $result['count'] ?? 0;
    }

    /**
     * Get processing step by name
     */
    public function findByName(string $name): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE name = :name";
        return Database::fetch($sql, ['name' => $name]);
    }

    /**
     * Check if name exists (excluding a specific ID for updates)
     */
    public function nameExists(string $name, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE name = :name";
        $params = ['name' => $name];
        
        if ($excludeId) {
            $sql .= " AND id != :id";
            $params['id'] = $excludeId;
        }
        
        $result = Database::fetch($sql, $params);
        return ($result['count'] ?? 0) > 0;
    }

    /**
     * Toggle status
     */
    public function toggleStatus(int $id): bool
    {
        $step = $this->find($id);
        if (!$step) return false;
        
        $newStatus = $step['status'] === 'active' ? 'inactive' : 'active';
        return $this->update($id, ['status' => $newStatus]);
    }
}
