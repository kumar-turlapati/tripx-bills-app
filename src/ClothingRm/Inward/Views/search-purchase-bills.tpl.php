<?php
  use Atawa\Utilities;
  use Atawa\Constants;

  $query_params_a = []; 
  $query_params = '';

  if(isset($form_data['searchBy']) && $form_data['searchBy'] !='') {
    $searchBy = $form_data['searchBy'];
    $query_params_a[] = 'searchBy='.$searchBy;
  } else {
    $searchBy = '';
  }
  if(isset($form_data['searchValue']) && $form_data['searchValue'] !='') {
    $searchValue = $form_data['searchValue'];
    $query_params_a[] = 'searchValue='.$searchValue;    
  } else {
    $searchValue = '';
  }
  if(isset($form_data['locationCode']) && $form_data['locationCode'] !='') {
    $locationCode = $form_data['locationCode'];
    $query_params_a[] = 'locationCode='.$locationCode;    
  } else {
    $locationCode = $default_location;
  }  
  if(is_array($query_params) && count($query_params)>0) {
    $query_params = '?'.implode('&', $query_params_a);
  }

  if($searchBy === 'itemname') {
    $products_style = '';
    $other_style = 'display:none;';
  } else {
    $products_style = 'display:none;';
    $other_style = '';
  }
?>
<div class="row">
  <div class="col-lg-12">
    <section class="panelBox">
      <div class="panelBody">
        <?php echo Utilities::print_flash_message() ?>
        <div class="filters-block">
          <div id="filters-form">
            <form class="form-validate form-horizontal" method="POST" autocomplete="off" id="searchPurchaseBills">
              <div class="form-group">
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <label class="control-label">Search by</label>
                  <div class="select-wrap m-bot15">
                    <select class="form-control" name="searchBy" id="searchBy">
                      <?php 
                        foreach($search_by_a as $key=>$value): 
                          if($key===$searchBy) {
                            $selected = 'selected="selected"';
                          } else {
                            $selected = '';
                          }
                      ?>
                      <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                      <?php endforeach; ?>
                    </select>
                   </div>
                  <?php if(isset($form_errors['searchBy'])): ?>
                    <span class="error"><?php echo $form_errors['searchBy'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2" style="<?php echo $other_style ?>" id="svAll">
                  <label class="control-label">Search value</label>
                  <input type="text" name="searchValue" id="searchValue" class="form-control" value="<?php echo $searchValue ?>" />
                  <?php if(isset($form_errors['searchValue'])): ?>
                    <span class="error"><?php echo $form_errors['searchValue'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2" id="svProducts" style="<?php echo $products_style ?>">
                  <label class="control-label">Search value</label>
                  <input type="text" name="searchValueP" id="searchValueP" class="form-control inameAc" value="<?php echo $searchValue ?>" />
                  <?php if(isset($form_errors['searchValue'])): ?>
                    <span class="error"><?php echo $form_errors['searchValue'] ?></span>
                  <?php endif; ?>
                </div>                
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <label class="control-label">Store name</label>                  
                  <div class="select-wrap">
                    <select class="form-control" name="locationCode" id="locationCode">
                      <?php 
                        foreach($client_locations as $key=>$value):
                          if($locationCode === $key) {
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
                  <label class="control-label">&nbsp;</label>
                  <button class="btn btn-success"><i class="fa fa-file-text"></i> Search</button>
                  <button type="reset" class="btn btn-warning" onclick="javascript:resetFilter('/purchases/search-bills')"><i class="fa fa-refresh"></i> Reset </button>
                </div>
              </div>
            </form>
          </div>
        </div>
        <?php if( count($bills)>0 ) { ?>
          <div class="table-responsive">
            <table class="table table-striped table-hover font12">
              <thead>
                <tr>
                  <th width="3%"  class="text-center">Sno.</th>
                  <th width="24%" class="text-center">Supplier<br />Name</th>
                  <th width="5%" class="text-center">Payment<br />Method</th>
                  <th width="5%" class="text-center">Credit<br />Days</th>                  
                  <th width="15%" class="text-center">Bill No.</th>
                  <th width="10%" class="text-center">Bill Amount<br />(in Rs.)</th>
                  <th width="15%" class="text-center">GRN No &amp; Date</th>
                  <th width="7%" class="text-center">Status</th>
                  <th width="20%" class="text-center">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php
                  $cntr = $sl_no;
                  $tot_net_pay = 0;
                  foreach($bills as $purchase_details):
                    $supplier_name = $purchase_details['supplierName'];
                    if((int)$purchase_details['paymentMethod'] === 0) {
                      $payment_method = 'Cash';
                    } elseif((int)$purchase_details['paymentMethod'] === 1) {
                      $payment_method = 'Credit';
                    }
                    $credit_days = $purchase_details['creditDays'];
                    $bill_no = $purchase_details['billNo'];
                    $bill_amount = $purchase_details['netpay'];
                    $grn_no = $purchase_details['grnNo'].'___'.date("d-M-Y", strtotime($purchase_details['grnDate']));
                    if((int)$purchase_details['status'] === 0) {
                      $status_text = '<span style="color:brown;font-weight:bold;font-size:12px;">Pending</span>';
                    } elseif((int)$purchase_details['status'] === 1) {
                      $status_text = '<span style="color:green;font-weight:bold;font-size:12px;">Approved</span>';
                    } elseif((int)$purchase_details['status'] === 2) {
                      $status_text = '<span style="color:red;font-weight:bold;font-size:12px;">Rejected</span>';
                    } else {
                      $status_text = 'Invalid';
                    }
                    $purchase_code = $purchase_details['purchaseCode'];
                    $grn_code = $purchase_details['grnCode'];
                    $tot_net_pay += $bill_amount;
                ?>
                    <tr class="text-uppercase text-right font11">
                      <td class="valign-middle"><?php echo $cntr ?></td>
                      <td class="text-left valign-middle"><?php echo $supplier_name ?></td>
                      <td class="text-left valign-middle"><span style="font-weight:bold;"><?php echo $payment_method ?></span></td>
                      <td class="valign-middle"><?php echo $credit_days ?></td>
                      <td class="text-right valign-middle"><?php echo $bill_no ?></td>
                      <td class="text-right valign-middle"><?php echo number_format($bill_amount, 2, '.', '') ?></td>
                      <td class="text-right valign-middle"><?php echo $grn_no ?></td>                    
                      <td class="text-right valign-middle"><?php echo $status_text ?></td>
                      <td class="valign-middle">
                        <div class="btn-actions-group">
                          <?php if($grn_code === ''): ?>
                            <?php if((int)$purchase_details['status'] === 1) : ?>                      
                              <a class="btn btn-warning" href="/grn/create?poNo=<?php echo $purchase_details['poNo'] ?>" title="Create GRN for this PO">
                                <i class="fa fa-list-ol"></i>
                              </a>
                            <?php endif; ?>
                            <?php if((int)$purchase_details['status'] === 0): ?>
                              <a class="btn btn-primary" href="/inward-entry/update/<?php echo $purchase_code ?>" title="Edit this purchase order">
                                <i class="fa fa-pencil"></i>
                              </a>
                              <a class="btn btn-danger" href="/inward-entry/update-status/<?php echo $purchase_code ?>" title="Approve / Reject PO">
                                <i class="fa fa-check"></i>
                              </a>
                            <?php endif; ?>
                          <?php else: ?>
                            <a class="btn btn-danger" href="/barcode/generate/<?php echo $purchase_code ?>" title="Generate Barcodes">
                              <i class="fa fa-bars"></i>
                            </a>                        
                            <a class="btn btn-primary" href="/inward-entry/view/<?php echo $purchase_code ?>" title="View purchase order">
                              <i class="fa fa-eye"></i>
                            </a>
                            <a class="btn btn-warning" href="/purchase-return/entry?pc=<?php echo $purchase_code ?>" title="Purchase Return and Auto Debit Note.">
                              <i class="fa fa-undo"></i>
                            </a>                          
                          <?php endif; ?>
                        </div>
                      </td>
                    </tr>
                <?php
                  $cntr++;
                  endforeach; 
                ?>
                <tr class="text-uppercase">
                  <td colspan="5" align="right">PAGE TOTALS</td>
                  <td class="text-bold text-right"><?php echo number_format($tot_net_pay,2,'.','') ?></td>
                  <td>&nbsp;</td>
                </tr>
              </tbody>
            </table>
          </div>
        <?php } ?>
      </div>
    </section>
  </div>
</div>