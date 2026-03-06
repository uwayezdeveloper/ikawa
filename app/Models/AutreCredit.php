<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class AutreCredit extends Model
{
    protected string $table = 'tbl_autre_credit';
    protected string $primaryKey = 'id';

    public function getActiveSources(): array
    {
        $sql = "SELECT in_id, in_name
                FROM tbl_source_of_income
                WHERE in_status = 1
                ORDER BY in_name ASC";

        return Database::fetchAll($sql);
    }

    public function getActiveAccounts(): array
    {
        $sql = "SELECT a.id, a.account_name, a.account_number, a.balance,
                       l.name AS location_name,
                       pm.name AS payment_mode_name
                FROM accounts a
                LEFT JOIN locations l ON l.id = a.location_id
                LEFT JOIN payment_modes pm ON pm.id = a.payment_mode_id
                WHERE a.status = 'active'
                ORDER BY a.account_name ASC";

        return Database::fetchAll($sql);
    }

    public function getSourceById(int $sourceId): ?array
    {
        return Database::fetch(
            "SELECT in_id, in_name FROM tbl_source_of_income WHERE in_id = :id LIMIT 1",
            ['id' => $sourceId]
        );
    }

    public function getCreditById(int $id): ?array
    {
        return Database::fetch(
            "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1",
            ['id' => $id]
        );
    }

    public function createCredit(array $data): int
    {
        $sql = "INSERT INTO {$this->table}
                (source_of_money_id, amount, outstanding_amount, account_id, due_date, done_date, status, description, created_by)
                VALUES
                (:source_of_money_id, :amount, :outstanding_amount, :account_id, :due_date, :done_date, :status, :description, :created_by)";

        Database::query($sql, [
            'source_of_money_id' => $data['source_of_money_id'],
            'amount' => $data['amount'],
            'outstanding_amount' => $data['outstanding_amount'],
            'account_id' => $data['account_id'],
            'due_date' => $data['due_date'],
            'done_date' => $data['done_date'],
            'status' => $data['status'],
            'description' => $data['description'],
            'created_by' => $data['created_by'],
        ]);

        return (int)Database::getInstance()->lastInsertId();
    }

    public function updateCreditAfterRepayment(int $creditId, float $newOutstanding, ?string $doneDate, string $status): bool
    {
        $sql = "UPDATE {$this->table}
                SET outstanding_amount = :outstanding_amount,
                    done_date = :done_date,
                    status = :status,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";

        Database::query($sql, [
            'id' => $creditId,
            'outstanding_amount' => $newOutstanding,
            'done_date' => $doneDate,
            'status' => $status,
        ]);

        return true;
    }

    public function addHistory(array $data): int
    {
        $sql = "INSERT INTO tbl_autre_credit_history
                (autre_credit_id, source_of_money_id, amount, account_id, payed_account_id, due_date, done_date, action_type, notes, created_by)
                VALUES
                (:autre_credit_id, :source_of_money_id, :amount, :account_id, :payed_account_id, :due_date, :done_date, :action_type, :notes, :created_by)";

        Database::query($sql, [
            'autre_credit_id' => $data['autre_credit_id'],
            'source_of_money_id' => $data['source_of_money_id'],
            'amount' => $data['amount'],
            'account_id' => $data['account_id'],
            'payed_account_id' => $data['payed_account_id'],
            'due_date' => $data['due_date'],
            'done_date' => $data['done_date'],
            'action_type' => $data['action_type'],
            'notes' => $data['notes'],
            'created_by' => $data['created_by'],
        ]);

        return (int)Database::getInstance()->lastInsertId();
    }

    public function getCredits(): array
    {
        $sql = "SELECT ac.*,
                       soi.in_name AS source_name,
                       a.account_name,
                  a.account_number,
                  l.name AS target_location_name
                FROM {$this->table} ac
                LEFT JOIN tbl_source_of_income soi ON soi.in_id = ac.source_of_money_id
                LEFT JOIN accounts a ON a.id = ac.account_id
              LEFT JOIN locations l ON l.id = a.location_id
                ORDER BY ac.id DESC";

        return Database::fetchAll($sql);
    }

    public function getHistory(): array
    {
        $sql = "SELECT h.*,
                       soi.in_name AS source_name,
                       target.account_name AS target_account_name,
                  payer.account_name AS payed_account_name,
                  target_loc.name AS target_location_name
                FROM tbl_autre_credit_history h
                LEFT JOIN tbl_source_of_income soi ON soi.in_id = h.source_of_money_id
                LEFT JOIN accounts target ON target.id = h.account_id
                LEFT JOIN accounts payer ON payer.id = h.payed_account_id
              LEFT JOIN locations target_loc ON target_loc.id = target.location_id
                ORDER BY h.id DESC";

        return Database::fetchAll($sql);
    }
}
