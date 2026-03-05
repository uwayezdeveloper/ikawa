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

        // Per-location stock cards (total amount + total quantity)
        $locationCards = Database::fetchAll(
            "SELECT l.id,
                    l.name as location_name,
                    COALESCE(SUM(ss.total_value), 0) as total_amount,
                    COALESCE(SUM(ss.total_quantity), 0) as total_quantity
             FROM locations l
             LEFT JOIN stock_summary ss ON ss.location_id = l.id
             WHERE l.status = 'active'
             GROUP BY l.id, l.name
             ORDER BY l.name ASC"
        );

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
            'locationCards' => $locationCards
        ], 'main');
    }
}
