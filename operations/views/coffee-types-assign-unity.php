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
                                    <h2>Assign Units to Category Types</h2>
                                    <p>
                                        Assign units of measurement to different category types.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-right">
                            <div class="breadcomb-report">
                            <button type="button" data-toggle="modal" data-target="#myModalthree" data-placement="left" title="Assign Unit" class="btn"><i class="fa fa-plus"></i></button>
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
                        <h2>Assigned Units</h2>
                    </div>
                    <div class="table-responsive">
                        <table id="data-table-basic" class="table table-striped assignmentdata">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Category</th>
                                    <th>Category Type</th>
                                    <th>Unit</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="assignmentdata">
                              <?php
                                $apiUrl = App::baseUrl() . '/_ikawa/category-type-units/get-all-assignments';
                                $json = fetchApiData($apiUrl);
                                $result = json_decode($json, true);
                                if ($result && $result['success'] && !empty($result['data'])) {
                                    foreach ($result['data'] as $index => $assignment) {
                                        $statusClass = $assignment['status'] === 'active' ? 'text-success' : 'text-danger';
                                        ?>
                                        <tr>
                                            <td><?php echo $index + 1 ?></td>
                                            <td><?php echo htmlspecialchars($assignment['category_name']); ?></td>
                                            <td><?php echo htmlspecialchars($assignment['type_name']); ?></td>
                                            <td><?php echo htmlspecialchars($assignment['unit_name']); ?></td>
                                            <td><span class="<?php echo $statusClass; ?>"><?php echo ucfirst($assignment['status']); ?></span></td>
                                            <td>
                                            <div class="button-icon-btn button-icon-btn-rd">
                                            <button class="btn btn-default btn-icon-notika editassignment"  title="Edit Assignment"
                                              data-id="<?= $assignment['assignment_id'] ?>"
                                              data-type_id="<?= $assignment['type_id'] ?>"
                                              data-unit_id="<?= $assignment['unit_id'] ?>"
                                              data-status="<?= $assignment['status'] ?>">
                                              <i class="notika-icon notika-edit"></i>
                                            </button>
                                        </div>
                                        </td>
                                       </tr>
                                   <?php  }
                                } else {
                                    ?>
                                      <tr><td colspan="6" class="text-center">No assignments found</td></tr>
                                <?php }  ?>
                            </tbody>
                            
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Unit Modal -->
<div class="modal fade" id="myModalthree" role="dialog">
    <div class="modal-dialog modal-large">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Assign Unit to Category Type</h4>
            </div>
            <div class="modal-body">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="chosen-select-act fm-cmp-mg">
                        <select class="chosen" data-placeholder="Choose Category Type..." name="type_id" id="type_id" required>
                            <option value="">Select Category Type</option>
                            <?php
                            $typesUrl = App::baseUrl() . '/_ikawa/category-types/get-all-category-types';
                            $response = fetchApiData($typesUrl);
                            $types = [];
                            if ($response !== false) {
                                $decoded = json_decode($response, true);
                                if ($decoded && isset($decoded['success']) && $decoded['success'] === true) {
                                    $types = $decoded['data'] ?? [];
                                }
                            }
                            ?>
                            <?php if (!empty($types)): ?>
                                <?php foreach ($types as $type): ?>
                                    <option value="<?= htmlspecialchars($type['type_id']) ?>">
                                        <?= htmlspecialchars($type['category_name']) ?> - <?= htmlspecialchars($type['type_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option disabled>No category types found</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="chosen-select-act fm-cmp-mg">
                        <select class="chosen" data-placeholder="Choose Unit..." name="unit_id" id="unit_id" required>
                            <option value="">Select Unit</option>
                            <?php
                            $unitsUrl = App::baseUrl() . '/_ikawa/unity/get-all-unity';
                            $response = fetchApiData($unitsUrl);
                            $units = [];
                            if ($response !== false) {
                                $decoded = json_decode($response, true);
                                if ($decoded && isset($decoded['success']) && $decoded['success'] === true) {
                                    $units = $decoded['data'] ?? [];
                                }
                            }
                            ?>
                            <?php if (!empty($units)): ?>
                                <?php foreach ($units as $unit): ?>
                                    <option value="<?= htmlspecialchars($unit['unit_id']) ?>">
                                        <?= htmlspecialchars($unit['unit_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option disabled>No units found</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
            </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="saveAssignmentBtn" class="btn btn-default">Assign</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Assignment Modal -->
<div class="modal animated bounce" id="myModalfour" role="dialog">
    <div class="modal-dialog modals-default">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Edit Assignment</h4>
            </div>
            <div class="modal-body">
               <div class="row">
                <input type="hidden" name="assignment_id" id="assignment_id">
                
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="chosen-select-act fm-cmp-mg">
                        <label><strong>Category Type:</strong></label>
                        <select class="chosen" data-placeholder="Choose Category Type..." name="edit_type_id" id="edit_type_id" required>
                            <option value="">Select Category Type</option>
                            <?php if (!empty($types)): ?>
                                <?php foreach ($types as $type): ?>
                                    <option value="<?= htmlspecialchars($type['type_id']) ?>">
                                        <?= htmlspecialchars($type['category_name']) ?> - <?= htmlspecialchars($type['type_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option disabled>No category types found</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="chosen-select-act fm-cmp-mg">
                        <label><strong>Unit:</strong></label>
                        <select class="chosen" data-placeholder="Choose Unit..." name="edit_unit_id" id="edit_unit_id" required>
                            <option value="">Select Unit</option>
                            <?php if (!empty($units)): ?>
                                <?php foreach ($units as $unit): ?>
                                    <option value="<?= htmlspecialchars($unit['unit_id']) ?>">
                                        <?= htmlspecialchars($unit['unit_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option disabled>No units found</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="chosen-select-act fm-cmp-mg">
                        <label><strong>Status:</strong></label>
                        <select class="chosen" data-placeholder="Choose Status..." name="edit_status" id="edit_status" required>
                            <option value="">Select Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
               </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="EditAssignmentBtn" class="btn btn-default">Save changes</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
