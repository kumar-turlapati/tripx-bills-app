<?php

  use Atawa\Constants;

  if(isset($template_vars) && is_array($template_vars)) {
    extract($template_vars);
  }

  /************************************ Extract Form data ***************************/

  if(isset($submitted_data['returnDate']) && $submitted_data['returnDate']!=='') {
    $current_date = date("d-m-Y", strtotime($submitted_data['returnDate']));
  } else {
    $current_date = date("d-m-Y");
  }

  if(isset($submitted_data['mrnNo']) && $submitted_data['mrnNo']!=='') {
    $mrn_no       = $submitted_data['mrnNo'];
  } else {
    $mrn_no       = '';
  }

  if(isset($submitted_data['totalReturnAmount']) && $submitted_data['totalReturnAmount']>0) {
    $totalReturnAmount = $submitted_data['totalReturnAmount'];
  } else {
    $totalReturnAmount = 0;
  }

  if(isset($submitted_data['totalReturnAmountRound']) && $submitted_data['totalReturnAmountRound']>0) {
    $totalReturnAmountRound = $submitted_data['totalReturnAmountRound'];
  } else {
    $totalReturnAmountRound = 0;
  }

  if(isset($submitted_data['returnAmount']) && $submitted_data['returnAmount']>0) {
    $returnAmount = $submitted_data['returnAmount'];
  } else {
    $returnAmount = 0;
  }

  $total_amount = $net_pay = 0;
  /************************************ End of Form data ***************************/
  if($mrn_no !== '') {
    $disable_form_data = 'disabled';
  } else {
    $disable_form_data = '';
  }
?>

<!-- Basic form starts -->
<div class="row">
  <div class="col-lg-12"> 
    
    <!-- Panel starts -->
    <section class="panelBox">
      <div class="panelBody">
        <?php echo $flash_obj->print_flash_message(); ?>

        <?php if($page_error !== ''): ?>
          <div class="alert alert-danger" role="alert">
            <strong>Error!</strong> <?php echo $page_error ?> 
          </div>
        <?php elseif($page_success !== ''): ?>
          <div class="alert alert-success" role="alert">
            <strong>Success!</strong> <?php echo $page_success ?> 
          </div>
        <?php endif; ?>
        
        <!-- Right links starts -->
        <div class="global-links actionButtons clearfix"> 
          <!-- Button style -->
          <div class="pull-right text-right">
            <a href="/sales-return/list" class="btn btn-default"><i class="fa fa-book"></i> Daywise Sales Return List</a>
          </div>
          <!-- Button style --> 
        </div>
        
        <!-- Right links ends --> 
        
        <!-- Form starts -->
        <form class="form-validate form-horizontal" method="POST">
          <div class="panel">
          <div class="panel-body">
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Return date</label>
              <div class="input-append date" data-date="<?php echo $current_date ?>" data-date-format="dd-mm-yyyy">
                <input class="span2" value="<?php echo $current_date ?>" size="16" type="text" readonly name="returnDate" id="returnDate" />
                <span class="add-on"><i class="fa fa-calendar"></i></span>
              </div>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">MRN No. (Auto)</label>
              <input type="text" class="form-control" name="mrnNo" id="mrnNo" value="<?php echo $mrn_no ?>" disabled>              
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Status</label>
              <div class="select-wrap">
                <select class="form-control" name="status" id="status" <?php echo $disable_form_data ?>>
                  <?php foreach($status as $key=>$value): ?>
                    <option value="<?php echo $key ?>"><?php echo $value ?></option>
                  <?php endforeach; ?>
                </select>
                <?php if(isset($errors['status'])): ?>
                  <span class="error"><?php echo $errors['status'] ?></span>
                <?php endif; ?>
              </div>              
            </div>                      
          </div>
          </div>
          </div>
          <div class="table-responsive">
            <table class="table table-striped table-hover font12">
              <thead>
                <tr>
                  <th width="5%" class="text-center">Sl.No.</th>                  
                  <th width="30%" class="text-center">Item Name</th>
                  <th width="10%" class="text-center">Lot No.</th>
                  <th width="10%" class="text-center">Sold<br />Qty.</th>
                  <th width="10%" class="text-center">Item Rate</th>
                  <th width="10%" class="text-center">Return<br />Qty.</th>
                  <th width="10%" class="text-center">Amount</th>
                </tr>
              </thead>
              <tbody>               
                <?php
                  $item_total = $total_amount = $item_total = $i= 0;
                  foreach($return_items as $return_item_name => $return_qty):
                      $i++;
                      $sale_item_key = array_search($return_item_name, array_column($sale_item_details,'itemName'));
                      if($sale_item_key !== false) {
                        $item_code = $sale_item_details[$sale_item_key]['itemCode'];
                        $item_name = $sale_item_details[$sale_item_key]['itemName'];
                        $item_qty = $sale_item_details[$sale_item_key]['itemQty'];
                        $item_rate = $sale_item_details[$sale_item_key]['itemRate'];
                        $igst_amount = $sale_item_details[$sale_item_key]['igstAmount'];
                        $sgst_amount = $sale_item_details[$sale_item_key]['sgstAmount'];
                        $cgst_amount = $sale_item_details[$sale_item_key]['cgstAmount'];
                        if($igst_amount > 0) {
                          $item_rate += $igst_amount;
                        } else {
                          $item_rate += $cgst_amount + $sgst_amount;
                        }
                        $lot_no = $sale_item_details[$sale_item_key]['lotNo'];
                      } else {
                        $item_code = '';
                        $item_name = '***Invalid***';
                        $item_qty = 0;
                        $item_rate = 0;
                        $lot_no = '*******';
                      }

                      $item_string = $item_name.'$'.$item_code.'$';

                      if(isset($return_items[$item_name]) && $return_items[$item_name]>0) {
                        $return_qty = $return_items[$item_name];
                        $disabled = 'disabled="disabled"';
                      } else {
                        $return_qty = 0;
                        $disabled = '';
                      }

                      $return_qty_a = array_slice($qtys_a,0,($item_qty+1));
                      $return_value = $item_rate*$return_qty;
                ?>
                  <tr>
                    <td align="right"><?php echo $i ?></td>
                    <td><?php echo $item_name ?></td>
                    <td align="right"><?php echo $lot_no ?></td>                    
                    <td align="right"><?php echo $item_qty ?></td>
                    <td align="right" id="returnRate_<?php echo $i ?>"><?php echo $item_rate ?></td>
                    <td align="right"><?php echo $return_qty ?></td>
                    <td id="returnValue_<?php echo $i ?>" align="right" class="itemReturnValue">
                      <?php echo number_format($return_value,2,'.','') ?>
                    </td>                  
                  </tr>
                <?php endforeach; ?>
                  <tr>
                    <td colspan="6" align="right">Total Amount</td>
                    <td id="totalAmount" align="right" class="totalAmount">
                      <?php echo number_format($totalReturnAmount,2,'.','')?>                      
                    </td>
                  </tr>
                  <tr>
                    <td colspan="6" align="right">Round off</td>
                    <td id="totalAmount" align="right" class="roundOff">
                      <?php echo number_format($totalReturnAmountRound,2,'.','')?>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="6" align="right">Total Return Value</td>
                    <td id="netPay" align="right" class="netPay"><?php echo number_format($returnAmount,2,'.','') ?></td>
                  </tr>                      
              </tbody>
            </table>
          </div>
        </form>  
      </div>
    </section>
    <!-- Panel ends --> 
  </div>
</div>