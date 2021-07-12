<?php

  // dump($form_data, $form_errors);
  // dump($form_errors);
  // dump($form_data);
  // dump($taxes, $taxes_raw);
  // exit;

  $location_code = isset($form_data['locationCode']) ? $form_data['locationCode'] : '';
  $billing_rate = isset($form_data['billingRate']) ? $form_data['billingRate'] : '';
  $update_for = isset($form_data['updateFor']) ? $form_data['updateFor'] : '';

  // if(isset($form_errors['itemDetails'])) {
  //   $api_error = '';
  //   foreach($form_errors['itemDetails'] as $key => $item_details) {
  //     $row_no = $key + 1;
  //     $api_error .= "<br />".'Row - '.$row_no.' : ';
  //     foreach($item_details as $field_name => $error_message) {
  //       $api_error .= $field_name.' = '.$error_message." | ";
  //     }
  //   }
  // }
  $billing_rates = [ 
    'all' => 'All Selling Prices', 
    'mrp' => 'M.R.P', 
    'wholesale' => 'Wholesale', 
    'online' => 'Online',
    'exmill' => 'Exmill',
  ];
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panelBox">
      <div class="panel-body">
        <?php echo $utilities->print_flash_message() ?>
        <?php if($api_error !== ''): ?>
          <div class="alert alert-danger" role="alert">
            <strong>You are errors in the uploaded file!</strong><br /><?php echo $api_error ?> 
          </div>
        <?php endif; ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/sales/list" class="btn btn-default">
              <i class="fa fa-book"></i> Sales Register
            </a>&nbsp;
            <a href="/inventory/changed-mrp-register" class="btn btn-default">
              <i class="fa fa-book"></i> Changed MRP Register
            </a>
          </div>
        </div>
        <form class="form-validate form-horizontal" method="POST" id="bulkUpdateSellingPriceFrm" autocomplete="off" enctype="multipart/form-data">
          <div class="panel">
            <div class="panel-body">
              <div class="form-group">
                <div class="col-sm-12 col-md-3 col-lg-3">
                  <label class="control-label labelStyle">Location name</label>
                  <div class="select-wrap">
                    <select class="form-control" name="locationCode" id="locationCode">
                      <?php 
                        foreach($client_locations as $key=>$value): 
                          if($location_code === $key) {
                            $selected = 'selected="selected"';
                          } else {
                            $selected = '';
                          }
                      ?>
                        <option value="<?php echo $key ?>" <?php echo $selected ?>>
                          <?php echo $value ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <?php if(isset($form_errors['locationCode'])): ?>
                    <span class="error"><?php echo $form_errors['locationCode'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-3 col-lg-3">
                  <label class="control-label labelStyle">Rate tobe updated</label>
                  <div class="select-wrap">
                    <select class="form-control" name="billingRate" id="billingRate">
                      <?php
                        foreach($billing_rates as $key=>$value):
                          if($key === $billing_rate) {
                            $selected = 'selected="selected"';
                          } else {
                            $selected = '';
                          }
                      ?>
                       <option value="<?php echo $key ?>" <?php echo $selected ?>>
                          <?php echo $value ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <?php if(isset($form_errors['billingRate'])): ?>
                    <span class="error"><?php echo $form_errors['locationCode'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-3 col-lg-3">
                  <label class="control-label labelStyle">Update for</label>
                  <div class="select-wrap">
                    <select class="form-control" name="updateFor" id="updateFor">
                      <?php
                        foreach($update_for_a as $key=>$value):
                          if($key === $update_for) {
                            $selected = 'selected="selected"';
                          } else {
                            $selected = '';
                          }
                      ?>
                       <option value="<?php echo $key ?>" <?php echo $selected ?>>
                          <?php echo $value ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <?php if(isset($form_errors['updateFor'])): ?>
                    <span class="error"><?php echo $form_errors['updateFor'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-3 col-lg-3">
                  <label class="control-label labelStyle">File name</label>
                  <input 
                    type="file"
                    class="form-control noEnterKey"
                    name="fileName"
                    id="fileName"
                    style="border:1px dashed; color:#225992" 
                  >
                  <?php if(isset($form_errors['fileName'])): ?>
                    <span class="error"><?php echo $form_errors['fileName'] ?></span>
                  <?php endif; ?>
                </div>
              </div>
              <div class="form-group">
                <div class="col-sm-12 col-md-12 col-md-12" id="downloadButton" style="padding-top:15px;">
                  <label class="control-label" style="text-align: center;">
                    <a href="/downloads/Qb_SellingPrice_Update_Format_V.1.1.xlsx" target="_blank">
                      <i class="fa fa-download"></i> Download update format.
                    </a>
                  </label>
                  <p class="red" align="center">Note: For updating selling price only .xlsx format is allowed.</p>
                </div>              
              </div>
            </div>
          </div>
          <div class="text-center">
            <button class="btn btn-danger cancelButton" id="bulkUpdateSellingPrice">
              <i class="fa fa-times"></i> Cancel
            </button>
            <button class="btn btn-primary cancelOp" id="bulkUpdateSellingPriceSubmit">
              <i class="fa fa-edit"></i> Update
            </button>
          </div>
        </form>
      </div>
    </section>
  </div>
</div>