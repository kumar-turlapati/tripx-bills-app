<?php

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
  if($mrn_no !== '') {
    $disable_form_data = 'disabled';
  } else {
    $disable_form_data = '';
  }
?>

<div class="row">
  <div class="col-lg-12"> 
    
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

        <div class="global-links actionButtons clearfix"> 
          <div class="pull-right text-right">
            <a href="/sales-return/list" class="btn btn-default"><i class="fa fa-book"></i> Daywise Sales Return List</a>
          </div>
        </div>
        
        <form class="form-validate form-horizontal" method="POST" id="salesReturnWindow">
          <div class="panel">
            <div class="panel-body">          
              <h2 class="hdg-reports borderBottom">Return Details</h2>
              <div class="form-group">
                  <div class="col-sm-12 col-md-4 col-lg-4">
                    <label class="control-label">Return date</label>
                    <div class="input-append date" data-date="<?php echo $current_date ?>" data-date-format="dd-mm-yyyy">
                      <input class="span2" value="<?php echo $current_date ?>" size="16" type="text" readonly name="returnDate" id="returnDate" />
                      <span class="add-on"><i class="fa fa-calendar"></i></span>
                    </div>
                  </div>
                  <div class="col-sm-12 col-md-4 col-lg-4">
                    <label class="control-label">MRN No. (Auto)</label>
                    <input type="text" class="form-control" name="mrnNo" id="mrnNo" value="<?php echo $mrn_no ?>" disabled>
                  </div>                     
              </div>
            </div>
          </div>
          <h2 class="hdg-reports borderBottom">Return Item Details</h2>
          <?php if(isset($errors['itemDetails'])): ?>
            <span class="error"><?php echo $errors['itemDetails'] ?></span>
          <?php endif; ?>          
          <div class="table-responsive">
            <table class="table table-striped table-hover">
              <thead>
                <tr>
                  <th width="5%" class="text-center">Sno.</th>                  
                  <th width="30%" class="text-center">Item name</th>
                  <th width="10%" class="text-center">Lot no.</th>
                  <th width="10%" class="text-center">Item rate</th>
                  <th width="10%" class="text-center">Sold<br />qty.</th>
                  <th width="10%" class="text-center">Previous<br />returns</th>
                  <th width="10%" class="text-center">Current<br />returns</th>
                  <th width="10%" class="text-center">Amount</th>
                </tr>
              </thead>
              <tbody>               
                <?php
                  $item_total = $total_amount = $item_total = 0;
                  for($i=0;$i<count($sale_item_details);$i++):
                      $item_code = $sale_item_details[$i]['itemCode'];
                      $item_name = $sale_item_details[$i]['itemName'];
                      $item_qty = $sale_item_details[$i]['itemQty'];
                      $lot_no = $sale_item_details[$i]['lotNo'];
                      $discount_amount = $sale_item_details[$i]['discountAmount'];
                      $mrp = $sale_item_details[$i]['mrp'];

                      $item_rate = round( (($item_qty*$mrp)-$discount_amount)/$item_qty, 2);

                      $item_string = $item_name.'$'.$item_code.'$';

                      if(isset($return_items[$item_name]) && $return_items[$item_name]>0) {
                        $return_qty = $return_items[$item_name];
                        $disabled = 'disabled="disabled"';
                      } else {
                        $return_qty = 0;
                        $disabled = '';
                      }

                      if(isset($tot_return_qtys[$item_code]) && $tot_return_qtys[$item_code]>0) {
                        $return_ason_date = $tot_return_qtys[$item_code];
                        $return_allowed_qty = $item_qty - $tot_return_qtys[$item_code];
                      } elseif(isset($tot_return_qtys[$item_code]) && $tot_return_qtys[$item_code]<0) {
                        $return_ason_date = $tot_return_qtys[$item_code];
                        $return_allowed_qty = 0;
                      } else {
                        $return_allowed_qty = $item_qty;
                        $return_ason_date = 0;
                      }
                      $return_qty_a = array_slice($qtys_a,0,($return_allowed_qty+1));
                      $return_value = $item_rate*$return_qty;
                ?>
                  <tr>
                    <td align="right" style="vertical-align:middle;"><?php echo $i+1 ?></td>
                    <td style="vertical-align:middle;"><?php echo $item_name ?></td>
                    <td align="right" style="vertical-align:middle;"><?php echo $lot_no ?></td>                    
                    <td align="right" style="vertical-align:middle;" id="returnRate_<?php echo $i ?>">
                      <?php echo $item_rate ?>
                    </td>
                    <td align="right" style="vertical-align:middle;"><?php echo $item_qty ?></td>
                    <td id="returnason_<?php echo $i ?>" align="right" class="itemReturnValueAson" style="vertical-align:middle;">
                      <?php echo $return_ason_date ?>
                    </td>                    
                    <td style="vertical-align:middle;">
                      <input type="hidden" name="itemInfo[]" id="<?php echo $item_code ?>" value="<?php echo $item_string ?>" />
                      <input 
                        type="text" 
                        class="form-control returnQty" 
                        name="returnQty_<?php echo $item_code.'_'.$i ?>" 
                        id="returnQty_<?php echo $i ?>" <?php echo $disabled ?>
                      />
                      <?php if(isset($errors['returnQty'])): ?>
                        <span class="error"><?php echo $errors['returnQty'] ?></span>
                      <?php endif; ?>                      
                    </td>
                    <td id="returnValue_<?php echo $i ?>" align="right" class="itemReturnValue" style="vertical-align:middle;">
                      <?php echo number_format($return_value,2) ?>
                    </td>                  
                  </tr>
                <?php endfor; ?>
                  <tr>
                    <td colspan="7" align="right">Total Amount</td>
                    <td id="totalAmount" align="right" class="totalAmount">
                      <?php echo number_format($totalReturnAmount,2)?>                      
                    </td>
                  </tr>
                  <tr>
                    <td colspan="7" align="right">Round off</td>
                    <td id="totalAmount" align="right" class="roundOff">
                      <?php echo number_format($totalReturnAmountRound,2)?>
                    </td>
                  </tr>                  
                  <tr>
                    <td colspan="7" align="right">Total Return Value</td>
                    <td id="netPay" align="right" class="netPay"><?php echo number_format($returnAmount,2) ?></td>
                  </tr>                                 
              </tbody>
            </table>
          </div>
          <div class="text-center">
            <button class="btn btn-primary" id="Save" <?php echo $disabled ?>>
              <i class="fa fa-save"></i> <?php echo $btn_label ?>
            </button>
          </div>
          <input type="hidden" id="status" name="status" value="1" />          
        </form>  
      </div>
    </section>
  </div>
</div>

<?php /*                      
<div class="select-wrap">
  <select class="form-control returnQty" name="returnQty_<?php echo $item_code.'_'.$i ?>" id="returnQty_<?php echo $i ?>" <?php echo $disabled ?>>
    <?php 
      foreach($return_qty_a as $key=>$value): 
        if((int)$value===(int)$return_qty) {
          $selected = 'selected="selected"';
        } else {
          $selected = '';
        }
    ?>
      <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
    <?php endforeach; ?>
  </select>
</div>*/ ?>