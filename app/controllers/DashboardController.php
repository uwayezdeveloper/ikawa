<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\User;
use App\Models\Supplier;
use App\Models\ProductCategory;
use App\Models\ExpenseTransaction;
use App\Models\ProformaInvoice;

/**
 * Dashboard Controller
 */
class DashboardController extends Controller
{
    /**
     * Show dashboard
     */
    public function index(Request $request, Response $response): void
    {
        $user = $_SESSION['user'] ?? null;
        // Gather summary stats for dashboard
        $userModel = new User();
        $supplierModel = new Supplier();
        $categoryModel = new ProductCategory();
        $expenseModel = new ExpenseTransaction();
        $invoiceModel = new ProformaInvoice();

        $userStats = $userModel->getStats();

        // Basic totals
        $totalUsers = (int) ($userStats['total'] ?? 0);
        $activeUsers = (int) ($userStats['active'] ?? 0);
        // Suppliers: count by supplier_type_id. type=2 are farmers per spec.
        $farmersCount = (int) (Database::fetch("SELECT COUNT(*) as total FROM suppliers WHERE supplier_type_id = 2")['total'] ?? 0);
        $otherSuppliersCount = (int) (Database::fetch("SELECT COUNT(*) as total FROM suppliers WHERE supplier_type_id != 2")['total'] ?? 0);
        $totalSuppliers = $otherSuppliersCount;
        $totalCategories = (int) (Database::fetch("SELECT COUNT(*) as total FROM product_categories")['total'] ?? 0);

        // Use suppliers table to compute farmers (supplier_type_id = 2)
        $totalFarmers = $farmersCount;

        // Expense stats
        $expenseStats = $expenseModel->getTransactionStats();
        $totalTransactions = (int) ($expenseStats['total_transactions'] ?? 0);
        $totalExpenseAmount = (float) ($expenseStats['total_amount'] ?? 0);

        // Invoice totals
        $invoiceTotals = Database::fetch("SELECT COUNT(*) as total, COALESCE(SUM(total_amount),0) as total_amount FROM tbl_proforma_invoice");
        $totalInvoices = (int) ($invoiceTotals['total'] ?? 0);
        $totalInvoiceAmount = (float) ($invoiceTotals['total_amount'] ?? 0);

        // Bank accounts totals (company money)
        $totalAccounts = (float) (Database::fetch("SELECT COALESCE(SUM(balance),0) as total FROM accounts")['total'] ?? 0);

        // Account breakdown for chart (top accounts)
        $accountsRows = Database::fetchAll("SELECT account_name, COALESCE(balance,0) as balance FROM accounts WHERE status = 'active' ORDER BY balance DESC LIMIT 8");
        $accountLabels = [];
        $accountBalances = [];
        foreach ($accountsRows as $r) {
            $accountLabels[] = $r['account_name'];
            $accountBalances[] = (float) $r['balance'];
        }

        // Per-location totals cards with cherries, stock value, liabilities, expenses and production cost.
        $locationCards = Database::fetchAll(
            "SELECT l.id,
                    l.name as location_name,
                    (
                    SELECT COALESCE(SUM(sr.quantity_in_kg), 0)
                    FROM stock_receives sr
                    WHERE sr.location_id = l.id
                      AND sr.product_category_id = 1
                      AND sr.status = 'approved'
                    ) as cheries_quantity,
                    COALESCE(SUM(CASE WHEN pc.id = 1 THEN ss.total_value ELSE 0 END), 0) as stock_value,

                    (
                        SELECT COALESCE(SUM(sa.amount), 0)
                        FROM supplier_advances sa
                        WHERE sa.location_id = l.id
                          AND sa.status = 'approved'
                    ) as advances_total,

                    (
                        SELECT COALESCE(SUM(rh.amount), 0)
                        FROM tbl_recharge_history rh
                        INNER JOIN accounts src ON src.id = rh.acc_id
                        INNER JOIN accounts dst ON dst.id = rh.to_account
                        WHERE rh.to_account IS NOT NULL
                          AND dst.location_id = l.id
                          AND src.location_type_id = 3
                    ) as approvisionnement_total,

                    (
                        SELECT COALESCE(SUM(sp.amount - sp.paid_amount), 0)
                        FROM supplier_payables sp
                        WHERE sp.location_id = l.id
                          AND sp.status IN ('pending', 'partial')
                    ) as loan_total,

                                        (
                                                SELECT COALESCE(SUM(a.balance), 0)
                                                FROM accounts a
                                                WHERE a.location_id = l.id
                                                    AND a.identifiers = 1
                                                    AND a.status = 'active'
                                        ) as bank_total,

                                        (
                                                SELECT COALESCE(SUM(a.balance), 0)
                                                FROM accounts a
                                                WHERE a.location_id = l.id
                                                    AND a.identifiers = 2
                                                    AND a.status = 'active'
                                        ) as caisse_total,

                    (
                        SELECT COALESCE(SUM(ec.amount), 0)
                        FROM tbl_expenseconsume ec
                        INNER JOIN tbl_expenses ex ON ex.expense_id = ec.expense_id
                        WHERE ec.station_id = l.id
                          AND ec.status = 1
                          AND ex.categ_id = 1
                    ) as expense_cat_1_total,

                    (
                        SELECT COALESCE(SUM(ec.amount), 0)
                        FROM tbl_expenseconsume ec
                        INNER JOIN tbl_expenses ex ON ex.expense_id = ec.expense_id
                        WHERE ec.station_id = l.id
                          AND ec.status = 1
                          AND ex.categ_id = 2
                    ) as expense_cat_2_total,

                    (
                        SELECT COALESCE(SUM(ec.amount), 0)
                        FROM tbl_expenseconsume ec
                        INNER JOIN tbl_expenses ex ON ex.expense_id = ec.expense_id
                        WHERE ec.station_id = l.id
                          AND ec.status = 1
                          AND ex.categ_id = 3
                    ) as expense_cat_3_total,

                    (
                        SELECT COALESCE(SUM(ec.amount), 0)
                        FROM tbl_expenseconsume ec
                        INNER JOIN tbl_expenses ex ON ex.expense_id = ec.expense_id
                        WHERE ec.station_id = l.id
                          AND ec.status = 1
                          AND ex.categ_id = 4
                    ) as expense_cat_4_total
             FROM locations l
             LEFT JOIN stock_summary ss ON ss.location_id = l.id
            LEFT JOIN product_categories pc ON pc.id = ss.product_category_id
             WHERE l.status = 'active'
             GROUP BY l.id, l.name
             ORDER BY l.name ASC"
        );

        $generalLocationTotals = [
            'locations_count' => 0,
            'cheries_quantity' => 0.0,
            'stock_value' => 0.0,
            'advances_total' => 0.0,
            'approvisionnement_total' => 0.0,
            'loan_total' => 0.0,
            'payed_total' => 0.0,
            'bank_total' => 0.0,
            'caisse_total' => 0.0,
            'expense_cat_1_total' => 0.0,
            'expense_cat_2_total' => 0.0,
            'expense_cat_3_total' => 0.0,
            'expense_cat_4_total' => 0.0,
            'production_cost_per_kg' => 0.0,
        ];

        foreach ($locationCards as &$locationCard) {
            $cheriesQty = (float)($locationCard['cheries_quantity'] ?? 0);
            $stockValueByCat1 = (float)($locationCard['stock_value'] ?? 0);
            $loanTotal = (float)($locationCard['loan_total'] ?? 0);
            $expenseCat1 = (float)($locationCard['expense_cat_1_total'] ?? 0);
            $expenseCat2 = (float)($locationCard['expense_cat_2_total'] ?? 0);
            $productionAmount = $stockValueByCat1 + $expenseCat1 + $expenseCat2;

            $locationCard['production_cost_per_kg'] = $cheriesQty > 0 ? ($productionAmount / $cheriesQty) : 0.0;
            $locationCard['payed_total'] = $stockValueByCat1 - $loanTotal;

            $generalLocationTotals['locations_count'] += 1;
            $generalLocationTotals['cheries_quantity'] += $cheriesQty;
            $generalLocationTotals['stock_value'] += $stockValueByCat1;
            $generalLocationTotals['advances_total'] += (float)($locationCard['advances_total'] ?? 0);
            $generalLocationTotals['approvisionnement_total'] += (float)($locationCard['approvisionnement_total'] ?? 0);
            $generalLocationTotals['loan_total'] += $loanTotal;
            $generalLocationTotals['payed_total'] += (float)($locationCard['payed_total'] ?? 0);
            $generalLocationTotals['bank_total'] += (float)($locationCard['bank_total'] ?? 0);
            $generalLocationTotals['caisse_total'] += (float)($locationCard['caisse_total'] ?? 0);
            $generalLocationTotals['expense_cat_1_total'] += $expenseCat1;
            $generalLocationTotals['expense_cat_2_total'] += $expenseCat2;
            $generalLocationTotals['expense_cat_3_total'] += (float)($locationCard['expense_cat_3_total'] ?? 0);
            $generalLocationTotals['expense_cat_4_total'] += (float)($locationCard['expense_cat_4_total'] ?? 0);
        }
        unset($locationCard);

        $generalProductionAmount =
            $generalLocationTotals['stock_value'] +
            $generalLocationTotals['expense_cat_1_total'] +
            $generalLocationTotals['expense_cat_2_total'];
        $generalLocationTotals['production_cost_per_kg'] =
            $generalLocationTotals['cheries_quantity'] > 0
                ? ($generalProductionAmount / $generalLocationTotals['cheries_quantity'])
                : 0.0;

        // Prepare simple time-series: expenses last 7 days
        $expensesLast7 = Database::fetchAll("SELECT DATE(recorded_date) as d, COALESCE(SUM(amount),0) as total FROM tbl_expenseconsume WHERE recorded_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY DATE(recorded_date) ORDER BY DATE(recorded_date) ASC");
        $expenseDates = [];
        $expenseValues = [];
        $dayMap = [];
        foreach ($expensesLast7 as $r) {
            $dayMap[$r['d']] = (float) $r['total'];
        }
        // ensure all 7 days present
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            $expenseDates[] = date('M j', strtotime($d));
            $expenseValues[] = isset($dayMap[$d]) ? $dayMap[$d] : 0;
        }

        // Invoices last 6 months (by month)
        $invoicesLast6 = Database::fetchAll("SELECT DATE_FORMAT(created_at, '%Y-%m') as m, COUNT(*) as cnt, COALESCE(SUM(total_amount),0) as total FROM tbl_proforma_invoice WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH) GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY m ASC");
        $invMonthMap = [];
        foreach ($invoicesLast6 as $r) {
            $invMonthMap[$r['m']] = ['count' => (int)$r['cnt'], 'total' => (float)$r['total']];
        }
        $invLabels = [];
        $invCounts = [];
        $invTotals = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = date('Y-m', strtotime("-{$i} months"));
            $invLabels[] = date('M Y', strtotime($m . '-01'));
            $invCounts[] = $invMonthMap[$m]['count'] ?? 0;
            $invTotals[] = $invMonthMap[$m]['total'] ?? 0;
        }

        $this->view('dashboard/index', [
            'user' => $user,
            'pageTitle' => 'Dashboard',
            'stats' => [
                'totalUsers' => $totalUsers,
                'activeUsers' => $activeUsers,
                'totalSuppliers' => $totalSuppliers,
                'totalCategories' => $totalCategories,
                'totalTransactions' => $totalTransactions,
                'totalExpenseAmount' => $totalExpenseAmount,
                'totalInvoices' => $totalInvoices,
                'totalInvoiceAmount' => $totalInvoiceAmount,
                'totalFarmers' => $totalFarmers,
                'totalAccounts' => $totalAccounts,
            ],
            'charts' => [
                'expense' => [ 'labels' => $expenseDates, 'data' => $expenseValues ],
                'invoices' => [ 'labels' => $invLabels, 'counts' => $invCounts, 'totals' => $invTotals ]
                , 'accounts' => ['labels' => $accountLabels, 'data' => $accountBalances]
            ],
            'locationCards' => $locationCards,
            'generalLocationTotals' => $generalLocationTotals,
        ], 'main');
    }
}
