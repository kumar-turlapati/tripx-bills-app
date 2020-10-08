<?php

  use Atawa\Utilities;

  // dump($sale_details);

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
        
        <form class="form-validate form-horizontal" method="POST" id="salesReturnWindow" autocomplete="off">

          <div class="table-responsive">
            <table class="table table-bordered font12">
              <tr>
                <td colspan="9" style="font-size:18px;font-weight: bold;text-align: center;color: #225992;">Invoice Details</td>
              </tr>
              <tr>
                <td width="8%" class="text-center sr-heading-style valign-middle">Invoice date</td>                  
                <td width="8%" class="text-center sr-heading-style valign-middle">Invoice no.</td>                  
                <td width="20%" class="text-center sr-heading-style valign-middle">Store name</td>
                <td width="10%" class="text-center sr-heading-style valign-middle">Payment method</td>
                <td width="8%" class="text-center sr-heading-style valign-middle">GrossAmt. (Rs.)</td>
                <td width="8%" class="text-center sr-heading-style valign-middle">Discount (Rs.)</td>
                <td width="8%" class="text-center sr-heading-style valign-middle">NetPay (Rs.)</td>
                <td width="8%" class="text-center sr-heading-style valign-middle">Taxable (Rs.)</td>
                <td width="8%" class="text-center sr-heading-style valign-middle">GST (Rs.)</td>
              </tr>
              <tr>
                <td class="text-center sr-value-style valign-middle"><?php echo date("d-m-Y", strtotime($sale_details['invoiceDate'])) ?></td>                  
                <td class="text-center sr-value-style valign-middle"><?php echo $sale_details['billNo'] ?></td>
                <td class="text-center sr-value-style valign-middle"><?php echo $sale_details['locationName'] ?></td>
                <td class="text-center sr-value-style valign-middle"><?php echo isset($payment_methods[$sale_details['paymentMethod']]) ? $payment_methods[$sale_details['paymentMethod']] : ''?></td>
                <td class="text-right sr-value-style valign-middle"><?php echo number_format($sale_details['billAmount'], 2, '.', '') ?></td>
                <td class="text-right sr-value-style valign-middle"><?php echo number_format($sale_details['discountAmount'], 2, '.', '') ?></td>
                <td class="text-right sr-value-style valign-middle" style="color:red; font-size: 18px;"><?php echo number_format($sale_details['netPay'], 2, '.', '') ?></td>
                <td class="text-right sr-value-style valign-middle"><?php echo number_format($sale_details['totalAmount'], 2, '.', '') ?></td>
                <td class="text-right sr-value-style valign-middle"><?php echo number_format($sale_details['taxAmount'], 2, '.', '') ?></td>
              </tr>
              <tr>
                <td colspan="5" class="text-center sr-heading-style valign-middle">Remarks</td>
                <td colspan="4" class="text-center sr-heading-style valign-middle">Customer name</td>
              </tr>
              <tr>
                <td colspan="5" class="text-center sr-value-style valign-middle"><?php echo $sale_details['remarksInvoice'] ?></td>
                <td colspan="4" class="text-center sr-value-style valign-middle"><?php echo $sale_details['customerName'] !== '' ? $sale_details['customerName'] : $sale_details['tmpCustName'] ?></td>                
              </tr>
            </table>
          </div> 


          <div class="form-group">
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label sr-heading-style">Scan barcode</label>
              <input
                type="text"
                id="srBarcode"
                style="font-size:16px;font-weight:bold;border:2px dashed #225992;padding-left:5px;font-weight:bold;background-color:#f7f705;height:35px;"
                maxlength="13"
              />
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label sr-heading-style">Return date</label>
              <?php if(Utilities::is_admin()): ?>
                <div class="input-append date" data-date="<?php echo $current_date ?>" data-date-format="dd-mm-yyyy">
                  <input class="span2" value="<?php echo $current_date ?>" size="16" type="text" readonly name="returnDate" id="returnDate" />
                  <span class="add-on"><i class="fa fa-calendar"></i></span>
                </div>
                <?php if(isset($errors['returnDate'])): ?>
                  <span class="error"><?php echo $errors['returnDate'] ?></span>
                <?php endif; ?>                     
              <?php else: ?>
                <div style="font-size:16px;font-weight:bold;color:#225992;"><?php echo $current_date ?></div>
                <input type="hidden" id="returnDate" name="returnDate" value="<?php echo $current_date ?>" />
              <?php endif; ?>
            </div>
          </div>

          <?php /*
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
                    <?php if(isset($errors['returnDate'])): ?>
                      <span class="error"><?php echo $errors['returnDate'] ?></span>
                    <?php endif; ?>                     
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
          <?php endif; ?> */ ?>

          <div class="table-responsive">
            <table class="table table-striped table-hover font12" id="salesReturnTable">
              <thead>
                <tr>
                  <th width="5%" class="text-center">Sno.</th>                  
                  <th width="30%" class="text-center">Item name</th>
                  <th width="10%" class="text-center">Lot no.</th>
                  <th width="10%" class="text-center">Item rate (Rs.)</th>
                  <th width="10%" class="text-center">Sold qty.</th>
                  <th width="10%" class="text-center">Previous<br />returns</th>
                  <th width="10%" class="text-center">Current<br />returns</th>
                  <th width="10%" class="text-center">Amount (Rs.)</th>
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
                      $return_key = $item_name.'__'.$lot_no;

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

                      // dump($tot_return_qtys, $return_key);

                      if(isset($tot_return_qtys[$return_key]) && $tot_return_qtys[$return_key]>0) {
                        $return_ason_date = $tot_return_qtys[$return_key];
                        $return_allowed_qty = $item_qty - $tot_return_qtys[$return_key];
                      } elseif(isset($tot_return_qtys[$return_key]) && $tot_return_qtys[$return_key]<0) {
                        $return_ason_date = $tot_return_qtys[$return_key];
                        $return_allowed_qty = 0;
                      } else {
                        $return_allowed_qty = $item_qty;
                        $return_ason_date = 0;
                      }

                      $return_qty_a = array_slice($qtys_a,0,($return_allowed_qty+1));
                      $return_value = $item_rate*$return_qty;
                ?>
                  <tr>
                    <td align="right" class="valign-middle"><?php echo $i+1 ?></td>
                    <td class="valign-middle"><?php echo $item_name ?></td>
                    <td align="right" class="valign-middle returnLotNo" id="lotNo_<?php echo $i ?>"><?php echo $lot_no ?></td>                    
                    <td align="right" class="valign-middle" id="returnRate_<?php echo $i ?>">
                      <?php echo $item_rate ?>
                    </td>
                    <td align="right" class="valign-middle" id="sold_<?php echo $i ?>"><?php echo $item_qty ?></td>
                    <td id="returnason_<?php echo $i ?>" align="right" class="itemReturnValueAson valign-middle">
                      <?php echo $return_ason_date ?>
                    </td>                    
                    <td class="valign-middle">
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
                    <td colspan="3" class="text-center sr-value-style">Gross amount (Rs.)</td>
                    <td colspan="3" class="text-center sr-value-style">Round off (Rs.)</td>
                    <td colspan="2" class="text-center sr-value-style">Return value (Rs.)</td>
                  </tr>
                  <tr>
                    <td id="totalAmount" align="center" class="totalAmount" colspan="3" style="font-size: 16px;">
                      <?php echo number_format($totalReturnAmount,2,'.','')?>                      
                    </td>
                    <td id="totalAmount" align="center" class="roundOff" colspan="3" style="font-size: 16px;">
                      <?php echo number_format($totalReturnAmountRound,2,'.','')?>
                    </td>
                    <td id="netPay" align="center" class="netPay" colspan="2" style="font-weight: bold; color:red; font-size: 18px;">
                      <?php echo number_format($returnAmount,2,'.','') ?>
                    </td>
                  </tr>
              </tbody>
            </table>
          </div>

         
          <br />
          <div class="text-center">
            <button class="btn btn-primary" id="srActionButton" <?php echo $disabled ?>>
              <i class="fa fa-save"></i> <?php echo $btn_label ?>
            </button>
          </div>

          <input type="hidden" id="status" name="status" value="1" />
          <input type="hidden" id="locationCode" name="locationCode" value="<?php echo $sale_details['locationCode'] ?>" />

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
