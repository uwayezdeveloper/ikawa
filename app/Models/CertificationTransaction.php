<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class CertificationTransaction extends Model
{
    protected string $table = 'tbl_certification_transactions';
    protected string $primaryKey = 'trans_id';

    public function getAllWithRelations(): array
    {
        $sql = "SELECT ct.*, c.cert_name,
                       CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) AS done_by_name
                FROM {$this->table} ct
                LEFT JOIN tbl_certification c ON ct.cert_id = c.cert_id
                LEFT JOIN users u ON ct.done_by = u.id
                ORDER BY ct.trans_id DESC";

        $rows = Database::fetchAll($sql);

        foreach ($rows as &$row) {
            $decoded = json_decode($row['payment_accounts'] ?? '', true);
            if (is_array($decoded)) {
                $parts = [];
                foreach ($decoded as $payment) {
                    if (is_array($payment) && isset($payment['account_id'])) {
                        $parts[] = 'Account #' . (int) $payment['account_id'] . ': RWF ' . number_format((float) ($payment['amount'] ?? 0));
                    }
                }
                $row['payment_accounts_display'] = !empty($parts) ? implode(', ', $parts) : ($row['payment_accounts'] ?? '');
            } else {
                $row['payment_accounts_display'] = $row['payment_accounts'] ?? '';
            }
        }

        return $rows;
    }

    public function createTransaction(array $data): int
    {
        $sql = "INSERT INTO {$this->table}
                    (cert_id, done_by, date_done, payment_accounts, coments, status, amount)
                VALUES
                    (:cert_id, :done_by, :date_done, :payment_accounts, :coments, :status, :amount)";

        Database::query($sql, [
            'cert_id' => $data['cert_id'],
            'done_by' => $data['done_by'],
            'date_done' => $data['date_done'],
            'payment_accounts' => json_encode($data['payment_accounts'] ?? []),
            'coments' => $data['coments'] ?? null,
            'status' => $data['status'] ?? 1,
            'amount' => $data['amount']
        ]);

        return (int) Database::getInstance()->lastInsertId();
    }

    public function updateTransaction(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table}
                SET cert_id = :cert_id,
                    done_by = :done_by,
                    date_done = :date_done,
                    payment_accounts = :payment_accounts,
                    coments = :coments,
                    status = :status,
                    amount = :amount
                WHERE trans_id = :trans_id";

        $stmt = Database::query($sql, [
            'cert_id' => $data['cert_id'],
            'done_by' => $data['done_by'],
            'date_done' => $data['date_done'],
            'payment_accounts' => json_encode($data['payment_accounts'] ?? []),
            'coments' => $data['coments'] ?? null,
            'status' => $data['status'] ?? 1,
            'amount' => $data['amount'],
            'trans_id' => $id
        ]);

        return $stmt->rowCount() >= 0;
    }

    public function deleteTransaction(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE trans_id = :trans_id";
        $stmt = Database::query($sql, ['trans_id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
