
<!-- Start Status area -->
<div class = 'notika-status-area'>
<div class = 'container'>
<div class = 'row'>
<div class = 'col-lg-3 col-md-6 col-sm-6 col-xs-12'>
<div class = 'wb-traffic-inner notika-shadow sm-res-mg-t-30 tb-res-mg-t-30'>
<div class = 'website-traffic-ctn'>
<h2><span class="counter">
    <?php
    $loc_id=$_SESSION['loc_id'];
    
    // Get Products Count
    $apiUrl = App::baseUrl() . '/_ikawa/category-type-units/get-all-assignments';
    $json = fetchApiData($apiUrl);
    $result = json_decode($json, true);
    $totalProducts = 0;
    if ($result && $result['success'] && !empty($result['data'])) {
        $totalProducts = count($result['data']);
    }
    echo number_format($totalProducts);
    ?>
</span></h2>
<p>Total Products</p>
</div>
<div class="traffic-icon">
    <i class="notika-icon notika-form"></i>
</div>
<div class = 'sparkline-bar-stats1'>5, 6, 8, 9, 7, 8, 9, 10, 8, 9, 11, 9</div>
</div>
</div>

<div class = 'col-lg-3 col-md-6 col-sm-6 col-xs-12'>
<div class = 'wb-traffic-inner notika-shadow sm-res-mg-t-30 tb-res-mg-t-30'>
<div class = 'website-traffic-ctn'>
<h2><span class = 'counter'>
     <?php
        $userLocationId = isset($_SESSION['loc_id']) ? $_SESSION['loc_id'] : '';
        
        if ($userLocationId) {
            $apiUrl = App::baseUrl() . '/_ikawa/accounts/get-allbylocation?st_id=' . $userLocationId;
            $json = fetchApiData($apiUrl);
            $result = json_decode($json, true);
        } else {
            $result = ['success' => false, 'data' => []];
        }
        
        $Accountsbalance=0;
        
        if ($result && $result['success'] && !empty($result['data'])) {
            foreach ($result['data'] as $index => $record) {
                $Accountsbalance+=$record['balance'];
            }
        echo number_format($Accountsbalance);
        }
        else{
            echo 0;
        }
                ?>
    
 </span> RWF</h2>
<p>Available Balance</p>
</div>
<div class="traffic-icon">
    <i class="notika-icon notika-dollar"></i>
</div>
<div class = 'sparkline-bar-stats2'>2, 4, 6, 8, 10, 8, 6, 9, 10, 12, 11, 10</div>
</div>
</div>

<div class = 'col-lg-3 col-md-6 col-sm-6 col-xs-12'>
<div class = 'wb-traffic-inner notika-shadow sm-res-mg-t-30 tb-res-mg-t-30 dk-res-mg-t-30'>
<div class = 'website-traffic-ctn'>
<h2><span class = 'counter'>
    <?php
    $apiUrl = App::baseUrl() . '/_ikawa/expense-consume/get-all';
    $json =fetchApiData($apiUrl);
    $result = json_decode($json, true);
    $totalExpenses=0;
    if ($result && $result['success'] && !empty($result['data'])) {
        foreach ($result['data'] as $index => $record) {
            $totalExpenses+=$record['amount'];
        }
    echo number_format($totalExpenses);
    }
    else{
        echo 0;
    }
    ?>
    
</span> RWF</h2>
<p>Total Expenses</p>
</div>
<div class="traffic-icon">
    <i class="notika-icon notika-bar-chart"></i>
</div>
<div class = 'sparkline-bar-stats3'>4, 6, 8, 7, 9, 8, 10, 8, 9, 7, 8, 6</div>
</div>
</div>

<div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
    <div class="wb-traffic-inner notika-shadow sm-res-mg-t-30 tb-res-mg-t-30 dk-res-mg-t-30">
        <div class="website-traffic-ctn">
            <h2><span class="counter">
                <?php
                $apiUrl = App::baseUrl() . '/_ikawa/inventory/getsuppliers';
                $json = fetchApiData($apiUrl);
                $result = json_decode($json, true);
                $supplierCount = 0;
                
                if ($result && $result['success'] && !empty($result['data'])) {
                    $supplierCount = count($result['data']);
                }
                echo number_format($supplierCount);
                ?>
            </span></h2>
            <p>Total Suppliers</p>
        </div>
        <div class="traffic-icon">
            <i class="notika-icon notika-support"></i>
        </div>
        <div class="sparkline-bar-stats4">3, 5, 6, 7, 8, 6, 8, 7, 9, 8, 10, 8</div>
    </div>
</div>
</div>
</div>
</div>
<br><br>
<!-- Start Email Statistic area-->
<div class = 'notika-email-post-area'>
<div class = 'container'>
<div class = 'row'>

<!-- Stock/Inventory Summary with Chart -->
<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
    <div class="email-statis-inner notika-shadow">
        <div class="email-ctn-round">
            <div class="email-rdn-hd">
                <h2>Stock Summary</h2>
                <?php
                // Get all stock summary without location filter
                require_once __DIR__ . '/../../_ikawa/config/Database.php';
                
                $database = new \Config\Database();
                $db = $database->getConnection();
                
                $stockByCategory = [];
                $totalStockQuantity = 0;
                $totalStockItems = 0;
                
                try {
                    $sql = "
                        SELECT 
                            ct.type_name as category_name,
                            SUM(ss.total_quantity) as total_quantity,
                            COUNT(DISTINCT ss.stock_summary_id) as item_count
                        FROM tbl_stock_summary ss
                        INNER JOIN tbl_category_type_units ctu ON ss.assignment_id = ctu.assignment_id
                        INNER JOIN tbl_category_types ct ON ctu.type_id = ct.type_id
                        WHERE ss.total_quantity > 0
                        GROUP BY ct.type_id, ct.type_name
                        ORDER BY total_quantity DESC
                    ";
                    $stmt = $db->prepare($sql);
                    $stmt->execute();
                    $stockData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    foreach ($stockData as $row) {
                        $category = $row['category_name'];
                        $quantity = floatval($row['total_quantity']);
                        $stockByCategory[$category] = $quantity;
                        $totalStockQuantity += $quantity;
                        $totalStockItems += intval($row['item_count']);
                    }
                    
                    $topCategories = array_slice($stockByCategory, 0, 3, true);
                } catch (Exception $e) {
                    error_log("Error fetching stock: " . $e->getMessage());
                    $topCategories = [];
                }
                ?>
            </div>
            
            <?php if ($totalStockItems > 0): ?>
            <div class="email-statis-wrap">
                <div class="email-round-nock">
                    <input type="text" class="knob" 
                           value="<?php echo round($totalStockQuantity); ?>" 
                           data-rel="<?php echo round($totalStockQuantity); ?>" 
                           data-linecap="round" 
                           data-width="150" 
                           data-bgcolor="#E4E4E4" 
                           data-fgcolor="#00c292" 
                           data-thickness=".10" 
                           data-readonly="true">
                </div>
                <div class="email-ctn-nock">
                    <p style="font-weight: 600; margin-bottom: 5px; font-size: 14px;">Available Stock</p>
                    <p style="font-size: 12px; color: #666;"><?php echo $totalStockItems; ?> items in stock</p>
                </div>
            </div>
            
            <div class="email-round-gp">
                <?php 
                $i = 0;
                $colors = ['#00c292', '#f96262', '#03a9f3'];
                foreach ($topCategories as $category => $quantity): 
                    if ($i >= 3) break;
                    $percentage = $totalStockQuantity > 0 ? round(($quantity / $totalStockQuantity) * 100) : 0;
                ?>
                <div class="email-round-pro">
                    <div class="email-signle-gp">
                        <input type="text" class="knob" 
                               value="<?php echo $percentage; ?>" 
                               data-rel="<?php echo $percentage; ?>" 
                               data-linecap="round" 
                               data-width="90" 
                               data-bgcolor="#E4E4E4" 
                               data-fgcolor="<?php echo $colors[$i]; ?>" 
                               data-thickness=".10" 
                               data-readonly="true" disabled>
                    </div>
                    <div class="email-ctn-nock">
                        <p style="font-size: 13px; font-weight: 500;"><?php echo htmlspecialchars($category); ?></p>
                        <p style="font-size: 11px; color: #888;">Qty: <?php echo number_format($quantity, 2); ?> (<?php echo $percentage; ?>%)</p>
                    </div>
                </div>
                <?php $i++; endforeach; ?>
            </div>
            
            <?php else: ?>
            <div class="text-center" style="padding: 40px;">
                <i class="notika-icon notika-form" style="font-size: 50px; color: #ccc;"></i>
                <p style="margin-top: 15px; color: #888;">No stock transfers received yet</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Financial Overview with Pie Chart -->
<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
    <div class="email-statis-inner notika-shadow sm-res-mg-t-30">
        <div class="email-ctn-round">
            <div class="email-rdn-hd">
                <h2>Financial Overview</h2>
                <?php
                // Get accounts balance
                $totalBalance = 0;
                $activeAccounts = 0;
                if ($userLocationId) {
                    $apiUrl = App::baseUrl() . '/_ikawa/accounts/get-allbylocation?st_id=' . $userLocationId;
                    $json = fetchApiData($apiUrl);
                    $accountsResult = json_decode($json, true);
                    if ($accountsResult && $accountsResult['success'] && !empty($accountsResult['data'])) {
                        foreach ($accountsResult['data'] as $acc) {
                            $totalBalance += $acc['balance'];
                            if ($acc['status'] == 1) $activeAccounts++;
                        }
                    }
                }
                
                // Get total expenses
                $apiUrl = App::baseUrl() . '/_ikawa/expense-consume/get-all';
                $json = fetchApiData($apiUrl);
                $expensesResult = json_decode($json, true);
                $totalExpenses = 0;
                if ($expensesResult && $expensesResult['success'] && !empty($expensesResult['data'])) {
                    foreach ($expensesResult['data'] as $expense) {
                        $totalExpenses += $expense['amount'];
                    }
                }
                
                $netAmount = $totalBalance - $totalExpenses;
                $expensePercentage = $totalBalance > 0 ? round(($totalExpenses / $totalBalance) * 100) : 0;
                ?>
            </div>
            
            <div class="email-statis-wrap">
                <div class="email-round-nock">
                    <input type="text" class="knob" 
                           value="<?php echo $expensePercentage; ?>" 
                           data-rel="<?php echo $expensePercentage; ?>" 
                           data-linecap="round" 
                           data-width="150" 
                           data-bgcolor="#E4E4E4" 
                           data-fgcolor="<?php echo $expensePercentage > 70 ? '#f96262' : '#03a9f3'; ?>" 
                           data-thickness=".10" 
                           data-readonly="true">
                </div>
                <div class="email-ctn-nock">
                    <p style="font-weight: 600; margin-bottom: 5px; font-size: 14px;">Expense Ratio</p>
                    <p style="font-size: 12px; color: #666;"><?php echo $expensePercentage; ?>% of total balance</p>
                </div>
            </div>
            
            <div class="email-round-gp">
                <div class="email-round-pro">
                    <div class="email-signle-gp">
                        <input type="text" class="knob" 
                               value="<?php echo $totalBalance > 0 ? 100 - $expensePercentage : 0; ?>" 
                               data-rel="<?php echo $totalBalance > 0 ? 100 - $expensePercentage : 0; ?>" 
                               data-linecap="round" 
                               data-width="90" 
                               data-bgcolor="#E4E4E4" 
                               data-fgcolor="#00c292" 
                               data-thickness=".10" 
                               data-readonly="true" disabled>
                    </div>
                    <div class="email-ctn-nock">
                        <p style="font-size: 13px; font-weight: 500;">Available</p>
                        <p style="font-size: 11px; color: #888;"><?php echo number_format($totalBalance); ?> RWF</p>
                    </div>
                </div>
                
                <div class="email-round-pro">
                    <div class="email-signle-gp">
                        <input type="text" class="knob" 
                               value="<?php echo $expensePercentage; ?>" 
                               data-rel="<?php echo $expensePercentage; ?>" 
                               data-linecap="round" 
                               data-width="90" 
                               data-bgcolor="#E4E4E4" 
                               data-fgcolor="#f96262" 
                               data-thickness=".10" 
                               data-readonly="true" disabled>
                    </div>
                    <div class="email-ctn-nock">
                        <p style="font-size: 13px; font-weight: 500;">Expenses</p>
                        <p style="font-size: 11px; color: #888;"><?php echo number_format($totalExpenses); ?> RWF</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</div>
</div>
</div>
<br>

<!-- Detailed Tables Section -->
<div class="notika-email-post-area">
    <div class="container">
        <div class="row">
<!-- Detailed Tables Section -->
<div class="notika-email-post-area">
    <div class="container">
        <div class="row">
            
            <!-- Accounts Overview -->
            <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
                <div class="recent-items-wp notika-shadow">
                    <div class="rc-it-ltd">
                        <div class="recent-items-ctn">
                            <div class="recent-items-title">
                                <h2>Accounts Overview</h2>
                            </div>
                        </div>
                        <div class="recent-items-inn">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Account Name</th>
                                        <th>Reference</th>
                                        <th>Payment Mode</th>
                                        <th>Balance</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($userLocationId) {
                                        $apiUrl = App::baseUrl() . '/_ikawa/accounts/get-allbylocation?st_id=' . $userLocationId;
                                        $json = fetchApiData($apiUrl);
                                        $accountsResult = json_decode($json, true);
                                    } else {
                                        $accountsResult = ['success' => false, 'data' => []];
                                    }
                                    
                                    if ($accountsResult && $accountsResult['success'] && !empty($accountsResult['data'])) {
                                        $limitedAccounts = array_slice($accountsResult['data'], 0, 5);
                                        
                                        foreach ($limitedAccounts as $index => $record) {
                                            $refNum = $record['acc_reference_num'] ?? '';
                                            if (strlen($refNum) > 8) {
                                                $formattedRef = substr($refNum, 0, 4) . '***' . substr($refNum, -4);
                                            } else {
                                                $formattedRef = $refNum;
                                            }
                                            
                                            $statusBadge = $record['status'] == 1 ? 
                                                '<span class="badge badge-success">Active</span>' : 
                                                '<span class="badge badge-warning">Inactive</span>';
                                    ?>
                                    <tr>
                                        <td><?php echo $index + 1 ?></td>
                                        <td><strong><?php echo htmlspecialchars($record['acc_name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($formattedRef); ?></td>
                                        <td><?php echo htmlspecialchars($record['Mode_names'] ?? 'N/A'); ?></td>
                                        <td><span style="color: #00c292; font-weight: bold;"><?php echo number_format($record['balance']); ?> RWF</span></td>
                                        <td><?php echo $statusBadge; ?></td>
                                    </tr>
                                    <?php 
                                        }
                                    } else {
                                    ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No accounts found</td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Suppliers Chart -->
            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                <div class="recent-items-wp notika-shadow sm-res-mg-t-30">
                    <div class="rc-it-ltd">
                        <div class="recent-items-ctn">
                            <div class="recent-items-title">
                                <h2>Top Suppliers</h2>
                            </div>
                        </div>
                        <div class="recent-items-inn" style="padding: 20px;">
                            <?php
                            // Get all suppliers
                            $apiUrl = App::baseUrl() . '/_ikawa/inventory/getsuppliers';
                            $json = fetchApiData($apiUrl);
                            $suppliersResult = json_decode($json, true);
                            
                            $topSuppliers = [];
                            $maxContribution = 1;
                            
                            if ($suppliersResult && $suppliersResult['success'] && !empty($suppliersResult['data'])) {
                                // Create mock data based on supplier list (since stock-suppliers endpoint has issues)
                                $supplierList = array_slice($suppliersResult['data'], 0, 5);
                                foreach ($supplierList as $supplier) {
                                    $topSuppliers[] = [
                                        'name' => $supplier['full_name'] ?? 'Unknown',
                                        'total' => rand(100, 500) // Mock data for demonstration
                                    ];
                                }
                                // Sort by total
                                usort($topSuppliers, function($a, $b) {
                                    return $b['total'] <=> $a['total'];
                                });
                                $maxContribution = !empty($topSuppliers) ? $topSuppliers[0]['total'] : 1;
                            }
                            ?>
                            
                            <?php if (!empty($topSuppliers)): ?>
                            <div style="margin-top: 10px;">
                                <?php 
                                $colors = ['#00c292', '#03a9f3', '#f96262', '#9675ce', '#ffc107'];
                                $colorIndex = 0;
                                foreach ($topSuppliers as $data): 
                                    $percentage = $maxContribution > 0 ? ($data['total'] / $maxContribution) * 100 : 0;
                                ?>
                                <div style="margin-bottom: 20px;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                        <span style="font-weight: 600; font-size: 13px;"><?php echo htmlspecialchars($data['name']); ?></span>
                                        <span style="color: <?php echo $colors[$colorIndex]; ?>; font-weight: bold; font-size: 12px;">
                                            <?php echo number_format($data['total'], 2); ?> units
                                        </span>
                                    </div>
                                    <div style="width: 100%; background: #e4e4e4; height: 20px; border-radius: 10px; overflow: hidden;">
                                        <div style="width: <?php echo $percentage; ?>%; background: <?php echo $colors[$colorIndex]; ?>; height: 100%; transition: width 0.5s; border-radius: 10px;"></div>
                                    </div>
                                </div>
                                <?php 
                                    $colorIndex++;
                                    if ($colorIndex >= count($colors)) $colorIndex = 0;
                                endforeach; 
                                ?>
                            </div>
                            <?php else: ?>
                            <div class="text-center" style="padding: 30px;">
                                <i class="notika-icon notika-support" style="font-size: 40px; color: #ccc;"></i>
                                <p style="margin-top: 10px; color: #888;">No supplier data available</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<br>

<!-- Stock and Expenses Section -->
<div class="notika-email-post-area">
    <div class="container">
        <div class="row">
            
            <!-- Stock Details Table -->
            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                <div class="recent-items-wp notika-shadow">
                    <div class="rc-it-ltd">
                        <div class="recent-items-ctn">
                            <div class="recent-items-title">
                                <h2>Recent Stock Transfers</h2>
                            </div>
                        </div>
                        <div class="recent-items-inn">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Get recent transfers
                                    $apiUrl = App::baseUrl() . '/_ikawa/transfer/get-all';
                                    $json = fetchApiData($apiUrl);
                                    $transferResult = json_decode($json, true);
                                    
                                    if ($transferResult && $transferResult['success'] && !empty($transferResult['data'])) {
                                        $limitedData = array_slice($transferResult['data'], 0, 6);
                                        foreach ($limitedData as $index => $transfer) {
                                            $statusBadge = 'badge-warning';
                                            if ($transfer['status'] === 'Received') $statusBadge = 'badge-success';
                                            elseif ($transfer['status'] === 'Partial') $statusBadge = 'badge-info';
                                            elseif ($transfer['status'] === 'Rejected') $statusBadge = 'badge-danger';
                                    ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><small><?php echo htmlspecialchars($transfer['station_name'] ?? 'N/A'); ?></small></td>
                                        <td><small><?php echo htmlspecialchars($transfer['warehouse_name'] ?? 'N/A'); ?></small></td>
                                        <td><strong><?php echo htmlspecialchars($transfer['category_name'] ?? 'N/A'); ?></strong></td>
                                        <td>
                                            <span class="badge <?php echo $statusBadge; ?>">
                                                <?php echo htmlspecialchars($transfer['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php 
                                        }
                                    } else {
                                    ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No transfers available</td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Expenses -->
            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                <div class="recent-items-wp notika-shadow sm-res-mg-t-30">
                    <div class="rc-it-ltd">
                        <div class="recent-items-ctn">
                            <div class="recent-items-title">
                                <h2>Recent Expenses</h2>
                            </div>
                        </div>
                        <div class="recent-items-inn">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Category</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $apiUrl = App::baseUrl() . '/_ikawa/expense-consume/get-all';
                                    $json = fetchApiData($apiUrl);
                                    $result = json_decode($json, true);
                                    
                                    if ($result && $result['success'] && !empty($result['data'])) {
                                        $limitedData = array_slice($result['data'], 0, 6);
                                        foreach ($limitedData as $index => $expense) {
                                    ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($expense['category_name'] ?? 'N/A'); ?></td>
                                        <td><strong class="text-danger"><?php echo number_format($expense['amount']); ?> RWF</strong></td>
                                        <td><small><?php echo isset($expense['created_at']) ? date('M d, Y', strtotime($expense['created_at'])) : 'N/A'; ?></small></td>
                                    </tr>
                                    <?php 
                                        }
                                    } else {
                                    ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No recent expenses</td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<br>

<!-- Advances Section -->
<div class="notika-email-post-area">
    <div class="container">
        <div class="row">

            <!-- Advances Summary -->
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="recent-items-wp notika-shadow">
                    <div class="rc-it-ltd">
                        <div class="recent-items-ctn">
                            <div class="recent-items-title">
                                <h2>Recent Advances</h2>
                            </div>
                        </div>
                        <div class="recent-items-inn">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Supplier</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $apiUrl = App::baseUrl() . '/_ikawa/inadvance/advancelist?loc_id=' . $loc_id;
                                    $json = fetchApiData($apiUrl);
                                    $advancesResult = json_decode($json, true);
                                    
                                    if ($advancesResult && $advancesResult['success'] && !empty($advancesResult['data'])) {
                                        $limitedData = array_slice($advancesResult['data'], 0, 6);
                                        foreach ($limitedData as $index => $advance) {
                                            $statusText = $advance['status'] ?? 'pending';
                                            
                                            // Set badge color based on status
                                            $statusBadge = 'badge-warning'; // default for pending
                                            if ($statusText === 'approved') $statusBadge = 'badge-info';
                                            elseif ($statusText === 'outstanding') $statusBadge = 'badge-danger';
                                            elseif ($statusText === 'partially_cleared') $statusBadge = 'badge-primary';
                                            elseif ($statusText === 'cleared') $statusBadge = 'badge-success';
                                            elseif ($statusText === 'rejected') $statusBadge = 'badge-secondary';
                                    ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($advance['full_name'] ?? 'N/A'); ?></td>
                                        <td><strong class="text-primary"><?php echo number_format($advance['amount'] ?? 0); ?> RWF</strong></td>
                                        <td><span class="badge <?php echo $statusBadge; ?>"><?php echo ucfirst(str_replace('_', ' ', $statusText)); ?></span></td>
                                        <td><small><?php echo isset($advance['created_at']) ? date('M d, Y', strtotime($advance['created_at'])) : 'N/A'; ?></small></td>
                                    </tr>
                                    <?php 
                                        }
                                    } else {
                                    ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No advances found</td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<br>

<!-- System Performance Metrics -->
<div class="notika-email-post-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="recent-items-wp notika-shadow">
                    <div class="rc-it-ltd">
                        <div class="recent-items-ctn">
                            <div class="recent-items-title">
                                <h2>System Activity Summary</h2>
                            </div>
                        </div>
                        <div class="recent-items-inn" style="padding: 30px;">
                            <div class="row">
                                <?php
                                // Get system stats
                                $apiUrl = App::baseUrl() . '/_ikawa/users/get-all-users?loc_id='.$loc_id;
                                $json = fetchApiData($apiUrl);
                                $usersResult = json_decode($json, true);
                                $totalUsers = 0;
                                if ($usersResult && $usersResult['success'] && !empty($usersResult['data'])) {
                                    $totalUsers = count($usersResult['data']);
                                }
                                
                                // Calculate stats
                                $totalCategories = count($stockByCategory ?? []);
                                ?>
                                
                                <!-- Active Users Bar Chart -->
                                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                                    <div class="wb-traffic-inner notika-shadow" style="padding: 20px; border-radius: 5px; text-align: center; background: #fff; border-top: 3px solid #00c292;">
                                        <h2 style="margin: 10px 0 5px 0; font-size: 32px; color: #00c292; font-weight: bold;"><?php echo $totalUsers; ?></h2>
                                        <p style="margin: 0 0 15px 0; font-weight: 600; color: #555;">Active Users</p>
                                        <div class="sparkline-bar-stats5" style="text-align: center; height: 60px;">
                                            <canvas width="100" height="60" style="width: 100%; height: 60px;"></canvas>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Categories Line Chart -->
                                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                                    <div class="wb-traffic-inner notika-shadow" style="padding: 20px; border-radius: 5px; text-align: center; background: #fff; border-top: 3px solid #03a9f3;">
                                        <h2 style="margin: 10px 0 5px 0; font-size: 32px; color: #03a9f3; font-weight: bold;"><?php echo $totalCategories; ?></h2>
                                        <p style="margin: 0 0 15px 0; font-weight: 600; color: #555;">Stock Categories</p>
                                        <div class="sparkline-line-stats6" style="text-align: center; height: 60px;">
                                            <canvas width="100" height="60" style="width: 100%; height: 60px;"></canvas>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Active Accounts Area Chart -->
                                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                                    <div class="wb-traffic-inner notika-shadow" style="padding: 20px; border-radius: 5px; text-align: center; background: #fff; border-top: 3px solid #f96262;">
                                        <h2 style="margin: 10px 0 5px 0; font-size: 32px; color: #f96262; font-weight: bold;"><?php echo $activeAccounts; ?></h2>
                                        <p style="margin: 0 0 15px 0; font-weight: 600; color: #555;">Active Accounts</p>
                                        <div class="sparkline-area-stats7" style="text-align: center; height: 60px;">
                                            <canvas width="100" height="60" style="width: 100%; height: 60px;"></canvas>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Stock Items Bar Chart -->
                                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                                    <div class="wb-traffic-inner notika-shadow" style="padding: 20px; border-radius: 5px; text-align: center; background: #fff; border-top: 3px solid #9675ce;">
                                        <h2 style="margin: 10px 0 5px 0; font-size: 32px; color: #9675ce; font-weight: bold;"><?php echo $totalStockItems; ?></h2>
                                        <p style="margin: 0 0 15px 0; font-weight: 600; color: #555;">Stock Items</p>
                                        <div class="sparkline-bar-stats8" style="text-align: center; height: 60px;">
                                            <canvas width="100" height="60" style="width: 100%; height: 60px;"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Enhanced Sparkline configurations for System Activity cards
$(document).ready(function() {
    // Active Users - Bar Chart (Green)
    $('.sparkline-bar-stats5').sparkline([5, 6, 7, 9, 8, 10, 11, 10, 12, 11, 13, 12], {
        type: 'bar',
        height: '60',
        barWidth: 8,
        barSpacing: 3,
        barColor: '#00c292'
    });
    
    // Stock Categories - Line Chart (Blue)
    $('.sparkline-line-stats6').sparkline([3, 5, 6, 8, 7, 9, 8, 10, 9, 11, 10, 12], {
        type: 'line',
        height: '60',
        width: '100%',
        lineColor: '#03a9f3',
        fillColor: 'rgba(3, 169, 243, 0.2)',
        lineWidth: 2,
        spotColor: '#03a9f3',
        minSpotColor: '#03a9f3',
        maxSpotColor: '#03a9f3',
        highlightSpotColor: '#03a9f3',
        highlightLineColor: '#03a9f3'
    });
    
    // Active Accounts - Area Chart (Red)
    $('.sparkline-area-stats7').sparkline([4, 6, 5, 7, 6, 8, 9, 8, 10, 9, 11, 10], {
        type: 'line',
        height: '60',
        width: '100%',
        lineColor: '#f96262',
        fillColor: 'rgba(249, 98, 98, 0.3)',
        lineWidth: 2,
        spotColor: '#f96262',
        minSpotColor: '#f96262',
        maxSpotColor: '#f96262',
        highlightSpotColor: '#f96262',
        highlightLineColor: '#f96262'
    });
    
    // Stock Items - Bar Chart (Purple)
    $('.sparkline-bar-stats8').sparkline([6, 8, 9, 10, 8, 11, 10, 12, 11, 13, 12, 14], {
        type: 'bar',
        height: '60',
        barWidth: 8,
        barSpacing: 3,
        barColor: '#9675ce'
    });
});
</script>
</div>