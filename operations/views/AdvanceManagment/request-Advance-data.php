
<!-- Add this CSS for tooltip -->
<style>
.has-tooltip {
    position: relative;
    display: inline-block;
}

.has-tooltip:hover::after {
    content: attr(title);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background-color: #333;
    color: #fff;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 12px;
    white-space: nowrap;
    z-index: 1000;
    margin-bottom: 5px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.has-tooltip:hover::before {
    content: '';
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    border-width: 5px;
    border-style: solid;
    border-color: #333 transparent transparent transparent;
    z-index: 1000;
    margin-bottom: -5px;
}
</style>

<div class="breadcomb-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="breadcomb-list">
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <div class="breadcomb-wp">
                                <div class="breadcomb-icon">
                                    <i class="notika-icon notika-support"></i>
                                </div>
                                <div class="breadcomb-ctn">
                                    <h2>Request Advance</h2>
                                    <p>
                                        Management Payment In Advance.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-right">
                            <div class="breadcomb-report">
                            <button type="button" data-toggle="modal" data-target="#myModalthree" data-placement="left" title="Create New Role" class="btn"><i class="fa fa-plus"></i></button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    
   

<div class="data-table-area">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="data-table-list">
                        <div class="basic-tb-hd">
                            <h2>Latest Requests</h2>
                        </div>
                        <div class="table-responsive">
                            <table id="data-table-basic" class="table table-striped usersdata">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Full Name</th>
                                        <th>Phone</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Created At</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                            <tbody id="usersdata">
                            <?php
                            $loc_id=$_SESSION['loc_id'];
                            $apiUrl = App::baseUrl() . '/_ikawa/inadvance/advancelist?loc_id='.$loc_id;
                            $json = fetchApiData($apiUrl);
                            $result = json_decode($json, true);
                            
                            // Status color mapping
                            $statusColors = [
                                'pending' => '#fff3cd',
                                'approved' => '#d1ecf1',
                                'outstanding' => '#f8d7da',
                                'partially_cleared' => '#d4edda',
                                'cleared' => '#28a745',
                                'rejected' => '#e2e3e5'
                            ];
                            
                            // Status text colors for contrast
                            $statusTextColors = [
                                'pending' => '#856404',
                                'approved' => '#0c5460',
                                'outstanding' => '#721c24',
                                'partially_cleared' => '#155724',
                                'cleared' => '#ffffff',
                                'rejected' => '#383d41'
                            ];
                            
                            if ($result && $result['success'] && !empty($result['data'])) {
                                foreach ($result['data'] as $index => $record) {
                                    $status = $record['status'];
                                    $bgColor = $statusColors[$status] ?? '#f8f9fa';
                                    $textColor = $statusTextColors[$status] ?? '#212529';
                                    $rejectedReason = $record['rejected_reason'] ?? '';
                                    
                                    // Prepare tooltip for rejected status
                                    $tooltipAttr = '';
                                    $tooltipClass = '';
                                    if ($status === 'rejected' && !empty($rejectedReason)) {
                                        $tooltipAttr = 'title="' . htmlspecialchars($rejectedReason, ENT_QUOTES) . '"';
                                        $tooltipClass = 'has-tooltip';
                                    }
                            ?>
                            <tr>
                                <td><?php echo $index + 1 ?></td>
                                <td><?php echo $record['full_name']; ?></td>
                                <td><?php echo $record['phone']; ?></td>
                                <td><?php echo $record['type']; ?></td>
                                <td><?php echo number_format($record['amount']); ?> RWF</td>
                                <td><?php echo $record['created_at']; ?></td>
                                <td>
                                    <span class="badge <?php echo $tooltipClass; ?>" 
                                        style="background-color: <?php echo $bgColor; ?>; color: <?php echo $textColor; ?>; padding: 5px 10px; border-radius: 12px; font-weight: 500; border: 1px solid #dee2e6; cursor: pointer;"
                                        <?php echo $tooltipAttr; ?>>
                                        <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php 
                                }
                            } else { 
                            ?>
                            <tr>
                                <td colspan="7" class="text-center">No advances found</td>
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


     <!-- create new model -->
    <div class="modal fade" id="myModalthree" role="dialog">
        <div class="modal-dialog modal-large">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                <div class="row">
                    <input type="hidden" name="station_id" id="station_id" value="<?php echo $_SESSION['loc_id'];?>">
                    <input type="hidden" name="created_by" id="created_by" value="<?php echo $_SESSION['user_id'];?>">
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-support"></i>
                                    </div>
                                    <div class="chosen-select-act fm-cmp-mg">
                                    <select class="chosen" data-placeholder="Choose Requesting..." name="request_type" id="request_type">
                                            <option></option>
											<option value="Supplier">Supplier</option>
											<option value="Farmer">Farmer</option>
									</select>
                                     </div>
                                </div>
                            </div>
                             <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-world"></i>
                                    </div>
                                        <div class="chosen-select-act fm-cmp-mg">
                                            <select class = 'chosen' data-placeholder = 'Choose Requestor...' name="destination_id" id="destination_id">
                                          <option>Select Requestor</option>
                                     
                                </select>
                                </div>
                            </div>
                        </div>
                         </div>

                            <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-dollar"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="text" name="amount" id="amount" class="form-control" placeholder="In advance Amount">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-tax"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="number" name="n_days" id="n_days" class="form-control" placeholder="Expected payment days">
                                    </div>
                                </div>
                            </div>
                          
                        </div>

                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-edit"></i>
                                    </div>
                                    <div class="nk-int-st">
                                         <textarea class="form-control" rows="5" placeholder="Type a reason for providing this advance...."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <div class="modal-footer">
                    <button type="button" id="saveinadvancePaymentBtn" class="btn btn-default">Save</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>