<?php 
  use Atawa\Utilities;
  use Atawa\Constants;

  // $current_date = date("d-m-Y");

  // dump($grn_details);
  // exit;

  $grn_item_details = $grn_details['itemDetails'];
  
  $po_no = $grn_details['poNo'];
  $supplier_code = $grn_details['supplierCode'];
  $supplier_name = $grn_details['supplierName'];
  $credit_period = $grn_details['creditDays'];
  $bill_number = $grn_details['billNo'];
  $grn_date = date("d-m-Y", strtotime($grn_details['grnDate']));
  $remarks = $grn_details['remarks'];
  $payment_method_name = Constants::$PAYMENT_METHODS_PURCHASE[$grn_details['paymentMethod']];

  $packing_charges = isset($grn_details['packingCharges']) ? $grn_details['packingCharges'] : '';
  $shipping_charges = isset($grn_details['shippingCharges']) ? $grn_details['shippingCharges'] : '';
  $insurance_charges = isset($grn_details['insuranceCharges']) ? $grn_details['insuranceCharges'] : '';
  $other_charges = isset($grn_details['otherCharges']) ? $grn_details['otherCharges'] : '';
  $transporter_name = isset($grn_details['transporterName']) ? $grn_details['transporterName'] : '';
  $lr_no = isset($grn_details['lrNo']) ? $grn_details['lrNo'] : '';
  $lr_date = isset($grn_details['lrDate']) ? $grn_details['lrDate'] : '';
  $challan_no = isset($grn_details['challanNo']) ? $grn_details['challanNo'] : '';

  // dump($grn_details);

  $bill_amount = $grn_details['billAmount'];
  $discount_amount = $grn_details['discountAmount'];
  $tax_amount = $grn_details['taxAmount'];
  $round_off = $grn_details['roundOff'];
  $net_pay = $grn_details['netPay'];

  $bill_amount_after_disc = $bill_amount - $discount_amount;
  $bill_value = $bill_amount_after_disc + $tax_amount + $packing_charges + $shipping_charges + $insurance_charges + $other_charges;
?>

<div class="row">
  <div class="col-lg-12"> 
    <section class="panelBox">
      <div class="panelBody">
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/grn/list" class="btn btn-default"><i class="fa fa-book"></i> GRN Register</a>&nbsp;
            <a href="/inward-entry/list" class="btn btn-default"><i class="fa fa-book"></i> Purchase Register</a> 
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-striped table-hover item-detail-table font12" id="purchaseTable" style="margin-bottom:0px;">
            <thead>
              <tr>
                <th class="text-center valign-middle">PO No.</th>
                <th class="text-center">Supplier name</th>                  
                <th class="text-center">Payment method</th>
                <th class="text-center">Credit period</th>
                <th class="text-center">Bill number</th>
                <th class="text-center">GRN Date</th>
              </tr>
              <tr>
                <td style="font-weight: bold; vertical-align: middle;"><?php echo $po_no ?></td>
                <td style="font-weight: bold; vertical-align: middle;"><?php echo $supplier_name ?></td>
                <td style="font-weight: bold; vertical-align: middle;"><?php echo $payment_method_name ?></td>
                <td style="font-weight: bold; vertical-align: middle;"><?php echo $credit_period.' days' ?></td>
                <td style="font-weight: bold; vertical-align: middle;"><?php echo $bill_number ?></td>
                <td style="font-weight: bold; vertical-align: middle;"><?php echo $grn_date ?></td>
              </tr>
            </thead>            
          </table>
        </div>        

        <div class="table-responsive">
          <table class="table table-striped table-hover item-detail-table font12" id="purchaseTable" style="margin-bottom:0px;">
            <thead>
              <tr>
                <th style="width:230px;" class="text-center">Item name</th>
                <th style="width:30px;"  class="text-center">Lot no.</th>                  
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
            </tbody>
          </table>
        </div>
        <div class="table-responsive">
          <table class="table table-striped table-hover font14" id="owItemsTable" style="margin-bottom:0px;">
              <tr>
                <th width="10%" class="text-center valign-middle">Taxable Value (in Rs.)</th>
                <th width="10%"  class="text-center valign-middle">G.S.T (in Rs.)</th>             
                <th width="10%" class="text-center valign-middle">Packing Charges (in Rs.)</th>
                <th width="10%"  class="text-center valign-middle">Shipping Charges (in Rs.)</th>             
              </tr>
              <tr>
                <td style="vertical-align:middle;text-align:right;"><?php echo number_format($bill_amount, 2, '.', '') ?></td>
                <td style="vertical-align:middle;text-align:right;"><?php echo number_format($tax_amount, 2, '.', '') ?></td>
                <td style="vertical-align:middle;text-align:right;"><?php echo number_format($packing_charges, 2, '.', '') ?></td>
                <td style="vertical-align:middle;text-align:right;"><?php echo number_format($shipping_charges, 2, '.', '') ?></td>
              </tr>
          </table>
        </div>
        <div class="table-responsive">
          <table class="table table-striped table-hover font14" id="owItemsTable" style="margin-bottom:0px;">
              <tr>
                <th width="10%" class="text-center valign-middle">Insurance Charges (in Rs.)</th>
                <th width="10%" class="text-center valign-middle">Other Charges (in Rs.)</th>
                <th width="10%" class="text-center valign-middle">Round Off (in Rs.)</th>
                <th width="10%" class="text-center valign-middle">Bill Amount (in Rs.)</th>
              </tr>
              <tr>
                <td style="vertical-align:middle;text-align:right;"><?php echo number_format($insurance_charges, 2, '.', '')  ?></td>
                <td style="vertical-align:middle;text-align:right;"><?php echo number_format($other_charges, 2, '.', '') ?></td>
                <td style="vertical-align:middle;text-align:right;"><?php echo number_format($round_off, 2, '.', '')  ?></td>
                <td style="vertical-align:middle;text-align:right;font-size:20px;color:red;font-weight:bold;"><?php echo number_format($net_pay, 2, '.', '') ?></td>
              </tr>
          </table>
        </div>
        <div class="table-responsive">
          <table class="table table-striped table-hover font14" id="owItemsTable" style="margin-bottom:0px;">
              <tr>
                <th width="10%" class="text-center valign-middle">Transport Name</th>
                <th width="10%"  class="text-center valign-middle">L.R. No.</th>             
                <th width="10%" class="text-center valign-middle">L.R. Date</th>
                <th width="10%"  class="text-center valign-middle">Challan No.</th>             
              </tr>
              <tr style="height:30px;">
                <td style="vertical-align:middle;text-align:right;"><?php echo $transporter_name  ?></td>
                <td style="vertical-align:middle;text-align:right;"><?php echo $lr_no ?></td>
                <td style="vertical-align:middle;text-align:right;"><?php echo $lr_date ?></td>
                <td style="vertical-align:middle;text-align:right;"><?php echo $challan_no ?></td>
              </tr>
              <tr>
                <td style="vertical-align:middle;font-weight:bold;color:#2F7192" align="center">Notes / Comments</td>
                <td style="vertical-align:middle;text-align:right;" colspan="3"><?php echo $remarks ?></td>
              </tr>                
          </table>
        </div>
      </div>
    </section>
  </div>
</div>