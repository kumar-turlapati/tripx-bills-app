<?php 
  use Atawa\Utilities;

  $current_date = date("d-m-Y");

  // dump($grn_details);
  // exit;

  $grn_item_details = $grn_details['itemDetails'];
  
  $po_no = $grn_details['poNo'];
  $supplier_code = $grn_details['supplierCode'];
  $payment_method = $grn_details['paymentMethod'];
  $credit_period = $grn_details['creditDays'];
  $bill_number = $grn_details['billNo'];
  $grn_date = $grn_details['grnDate'];

  // dump($grn_details);

  $bill_amount = $grn_details['billAmount'];
  $discount_amount = $grn_details['discountAmount'];
  $bill_amount_after_disc = $bill_amount - $discount_amount;

  $tax_amount = $grn_details['taxAmount'];
  $bill_value = $bill_amount_after_disc + $tax_amount;

  $round_off = $grn_details['roundOff'];
  $net_pay = $grn_details['netPay'];
?>

<div class="row">
  <div class="col-lg-12"> 
    
    <section class="panelBox">
      <div class="panelBody">

        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/grn/list" class="btn btn-default"><i class="fa fa-book"></i> GRN Register</a>
            <a href="/inward-entry/list" class="btn btn-default"><i class="fa fa-book"></i> Inward Register</a> 
          </div>
        </div>

        <form class="form-validate form-horizontal" method="POST" id="grnForm">

          <div class="panel">
            <div class="panel-body">
              <h2 class="hdg-reports borderBottom">Transaction Details</h2>
              <div class="form-group">
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Purchaser order (PO) No.</label>
                  <input type="text" class="form-control" name="poNo" id="poNoGrn" 
                  value="<?php echo $po_no ?>" disabled>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Supplier name</label>
                  <div class="select-wrap">
                    <select class="form-control" name="supplierID" id="supplierID" disabled>
                      <?php 
                      	foreach($suppliers as $key=>$value):
                          if($supplier_code === $key) {
                            $selected = 'selected="selected"';
                          } else {
                            $selected = '';
                          }                  		
                      ?>
                        <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>         
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Payment method</label>
                  <div class="select-wrap">
                    <select class="form-control" name="paymentMethod" id="paymentMethod" disabled>
                      <?php 
                      	foreach($payment_methods as $key=>$value):
                          if((int)$payment_method === (int)$key) {
                            $selected = 'selected="selected"';
                          } else {
                            $selected = '';
                          }                   	 
                      ?>
                        <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>        
              </div>
              <div class="form-group">
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Credit period (in days)</label>
                  <div class="select-wrap">
                    <select class="form-control" name="creditDays" id="creditDays" disabled>
                      <?php 
                      	foreach($credit_days_a as $key=>$value): 
                          if((int)$credit_period === (int)$key) {
                            $selected = 'selected="selected"';
                          } else {
                            $selected = '';
                          }                  		
                      ?>
                        <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Bill number</label>
                  <input type="text" class="form-control" name="billNo" id="billNo" value="<?php echo $bill_number ?>" disabled>
                  <?php if(isset($errors['billNo'])): ?>
                    <span class="error"><?php echo $errors['billNo'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">GRN date (dd-mm-yyyy)</label>
                  <div class="form-group">
                    <div class="col-lg-12">
                      <div class="input-append date" data-date="<?php echo $current_date ?>" data-date-format="dd-mm-yyyy">
                        <input class="span2" value="<?php echo $current_date ?>" size="16" type="text" disabled name="grnDate" id="grnDate" />
                        <span class="add-on"><i class="fa fa-calendar"></i></span>
                      </div>                 
                    </div>
                  </div>
                </div>              
              </div>
            </div>
          </div>

          <h2 class="hdg-reports">Item Details</h2>

          <div class="table-responsive">
            <table class="table table-striped table-hover item-detail-table font12" id="purchaseTable">
              <thead>
                <tr>
                  <th style="width:230px;" class="text-center">Item name</th>
                  <th style="width:30px;" class="text-center">Lot no.</th>                  
                  <th style="width:50px;"  class="text-center">Available<br />qty.</th>
                  <th style="width:50px"   class="text-center">Accepted<br />qty.</th>
                  <th style="width:50px"   class="text-center">MRP<br />(Rs.)</th>
                  <th style="width:50px"   class="text-center">Rate / Unit<br />(Rs.)</th>
                  <th style="width:50px"   class="text-center">Gross amt.<br />(Rs.)</th>
                  <th style="width:50px"   class="text-center">Discount<br />(Rs.)</th>
                  <th style="width:50px"   class="text-center">Taxable amt.<br />(Rs.)</th>
                  <th style="width:50px"   class="text-center">G.S.T<br />(in %)</th>                  
                </tr>
              </thead>
              <tbody>
                <?php
                  $tot_acc_qty = 0;
                  for($i=0;$i<count($grn_item_details);$i++):
                  	$item_name = $grn_item_details[$i]['itemName'];
                    $lot_no = $grn_item_details[$i]['lotNo'];
                  	$rec_qty = $grn_item_details[$i]['itemQty'];
                    $free_qty = $grn_item_details[$i]['freeQty'];
                  	$acc_qty = $grn_item_details[$i]['grnQty'];
                  	$mrp = $grn_item_details[$i]['mrp'];
                  	$item_rate = $grn_item_details[$i]['itemRate'];
                  	$tax_percent = $grn_item_details[$i]['taxPercent'];
                    $discount = $grn_item_details[$i]['discountAmount'];
                    $packed_qty = $grn_item_details[$i]['packedQty'];

                    $rec_qty = round(($rec_qty+$free_qty)*$packed_qty,2);
                    $acc_qty = round($acc_qty*$packed_qty, 2);
                  	
                   //  $item_amount = ($item_rate*($acc_qty-$free_qty));
                  	// $tax_amount = ($item_amount*$tax_percent/100);
                  	// $item_amount += $tax_amount;

                    $gross_amount = round(($rec_qty-$free_qty)*$item_rate,2);
                    $taxable_amount = $gross_amount-$discount;

                    $tot_acc_qty += $acc_qty;
                ?>
                    <tr>
                      <td><?php echo $item_name ?></td>
                      <td><?php echo $lot_no ?></td>                      
                      <td class="text-right"><?php echo number_format($rec_qty,2,'.','') ?></td>
                      <td class="text-right"><?php echo number_format($acc_qty,2,'.','') ?></td>
                      <td class="text-right"><?php echo number_format($mrp,2,'.','') ?></td>
                      <td class="text-right"><?php echo number_format($item_rate,2,'.','') ?></td>
                      <td class="text-right"><?php echo number_format($gross_amount,2,'.','') ?></td>
                      <td class="text-right"><?php echo number_format($discount,2,'.','') ?></td>
                      <td class="text-right"><?php echo number_format($taxable_amount,2,'.','') ?></td>                      
                      <td class="text-right"><?php echo number_format($tax_percent,2,'.','') ?></td>
                    </tr>
                <?php endfor; ?>
                  <tr>
                    <td colspan="3" align="right" style="vertical-align:middle;font-size:16px;">TOTALS</td>
                    <td align="right" style="font-size:16px;font-weight:bold;vertical-align:middle;font-size:16px;"><?php echo $tot_acc_qty ?></td>
                    <td colspan="4" style="vertical-align:middle;text-align:right;font-size:16px;font-weight:bold;">Taxable value</td>
                    <td id="inwItemsTotal" style="vertical-align:middle;text-align:right;font-size:16px;font-weight:bold;"><?php echo number_format($bill_amount_after_disc, 2) ?></td>
                  </tr>
                  <tr>
                    <td style="vertical-align:middle;" colspan="9" align="right">G.S.T</td>
                    <td style="vertical-align:middle;text-align:right;"><?php echo number_format($tax_amount,2,'.','') ?></td>
                  </tr>
                  <tr>
                    <td style="vertical-align:middle;font-weight:bold;" colspan="9" align="right">Bill value</td>
                    <td style="vertical-align:middle;text-align:right;"><?php echo number_format($bill_value,2,'.','') ?></td>
                  </tr>
                  <tr>
                    <td style="vertical-align:middle;font-weight:bold;" colspan="9" align="right">Round off</td>
                    <td style="vertical-align:middle;text-align:right;"><?php echo number_format($round_off,2,'.','') ?></td>
                  </tr>
                  <tr>
                    <td style="vertical-align:middle;font-weight:bold;font-size:18px;" colspan="9" align="right">Net pay</td>
                    <td style="vertical-align:middle;text-align:right;font-weight:bold;font-size:18px;"><?php echo number_format($net_pay,2,'.','') ?></td>
                  </tr>                
              </tbody>
            </table>
          </div>   
        </form>  
      </div>
    </section>
  </div>
</div>