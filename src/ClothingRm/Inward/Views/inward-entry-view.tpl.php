<?php

  // dump($form_data, $form_errors);
  // dump($form_errors);
  // dump($form_data);
  // dump($taxes, $taxes_raw);
  // exit;

  $purchase_date = isset($form_data['purchaseDate']) ? date("d-m-Y", strtotime($form_data['purchaseDate'])) : '';
  $credit_days = isset($form_data['creditDays']) ? $form_data['creditDays'] : '';
  $po_no = isset($form_data['poNo']) ? $form_data['poNo'] : '';
  $remarks = isset($form_data['remarks']) ? $form_data['remarks'] : '';
  $supply_type = isset($form_data['supplyType']) ? $form_data['supplyType'] : '';
  $supplier_name = isset($form_data['supplierName']) ? $form_data['supplierName'] : '';
  $store_name = isset($client_locations[$form_data['locationID']]) ? $client_locations[$form_data['locationID']] : '';
  $payment_method = isset($payment_methods[$form_data['paymentMethod']]) ? $payment_methods[$form_data['paymentMethod']] : '';
  $inward_status = isset($form_data['status']) ? $form_data['status'] : 0;

  $packing_charges = isset($form_data['packingCharges']) ? $form_data['packingCharges'] : '';
  $shipping_charges = isset($form_data['shippingCharges']) ? $form_data['shippingCharges'] : '';
  $insurance_charges = isset($form_data['insuranceCharges']) ? $form_data['insuranceCharges'] : '';
  $other_charges = isset($form_data['otherCharges']) ? $form_data['otherCharges'] : '';
  $transporter_name = isset($form_data['transporterName']) ? $form_data['transporterName'] : '-';
  $lr_no = isset($form_data['lrNo']) ? $form_data['lrNo'] : '-';
  $lr_date = isset($form_data['lrDate']) ? $form_data['lrDate'] : '-';
  $challan_no = isset($form_data['challanNo']) ? $form_data['challanNo'] : '-';  
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panelBox">
      <div class="panelBody">
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/inward-entry/list" class="btn btn-default">
              <i class="fa fa-book"></i> Purchase Register
            </a>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-striped table-hover font14" id="owItemsTable" style="margin-bottom:0px;">
            <thead>
              <tr>
                <th width="10%" class="text-center valign-middle">PO No.</th>
                <th width="5%"  class="text-center valign-middle">PO Date</th>             
                <th width="15%" class="text-center valign-middle">Supplier Name</th>
                <th width="10%" class="text-center valign-middle">Store Name</th>
                <th width="5%"  class="text-center valign-middle">Payment Method</th>
                <th width="7%"  class="text-center valign-middle">Credit Period</th>
                <th width="10%" class="text-center valign-middle">Supplier Location</th>
              </tr>
            </thead>
            <tbody>
                <tr>
                  <td class="valign-middle"><?php echo $po_no ?></td>
                  <td style="vertical-align:middle;"><?php echo $purchase_date ?></td>
                  <td style="vertical-align:middle;"><?php echo $supplier_name ?></td>
                  <td style="vertical-align:middle;" align="center"><?php echo $store_name ?></td>
                  <td style="vertical-align:middle;" align="center"><?php echo $payment_method ?></td>
                  <td style="vertical-align:middle;"><?php echo $credit_days ?></td>
                  <td style="vertical-align:middle;text-align:center;"><?php echo $states_a[$form_data['supplierStateID']] ?></td> 
                </tr>
            </tbody>
          </table>
        </div>   
        <div class="table-responsive">
          <table class="table table-striped table-hover item-detail-table font11" id="purchaseTable" style="margin-bottom:0px;width:1700px;">
            <thead>
              <tr>
                <th style="width:220px;" class="text-center purItem">Item name</th>
                <th style="width:80px;"  class="text-center purItem">HSN / SAC Code</th>
                <th style="width:20px;"  class="text-center purItem">Case / <br />Box No.</th>
                <th style="width:20px;"  class="text-center purItem">Lot No.</th>
                <th style="width:50px;"  class="text-center">Received<br />qty.</th>
                <th style="width:50px"   class="text-center">Free<br />qty.</th>
                <th style="width:50px"   class="text-center">Billed<br />qty.</th>
                <th style="width:60px"   class="text-center">Packed/<br />Unit</th>                  
                <th style="width:55px"   class="text-center">MRP<br />( in Rs. )</th>
                <th style="width:55px"   class="text-center">Rate / Unit<br />( in Rs. )</th>
                <th style="width:55px"   class="text-center">Gross Amt.<br />( in Rs. )</th>
                <th style="width:55px"   class="text-center">Discount<br />( in Rs. )</th>                  
                <th style="width:70px"   class="text-center">Taxable Amt.<br />( in Rs. )</th>
                <th style="width:70px"   class="text-center">G.S.T<br />( in % )</th>
                <th style="width:50px"   class="text-center">Brand</th>
                <th style="width:50px"   class="text-center">Category</th>
                <th style="width:20px"   class="text-center">Container<br />No.</th>
                <th style="width:20px"   class="text-center">UOM<br />Name</th>
                <th style="width:20px"   class="text-center">Barcode</th>
                <th style="width:20px"   class="text-center">Item<br />SKU</th>
                <th style="width:20px"   class="text-center">Style<br />Code</th>
                <th style="width:20px"   class="text-center">Size</th>
                <th style="width:20px"   class="text-center">Color</th>
                <th style="width:20px"   class="text-center">Sleeve<br />Type</th>
                <th style="width:20px"   class="text-center">BatchNo.</th>
                <th style="width:20px"   class="text-center">ExpiryDate</th>
                <th style="width:20px"   class="text-center">Wholesale<br />Price (in Rs.)</th>
                <th style="width:20px"   class="text-center">Online<br />Price (in Rs.)</th>
              </tr>
            </thead>
            <tbody>
            <?php
              $items_total =  $total_tax_amount = $items_tot_after_discount = $total_disc_amount = 0;
              $taxable_values = $taxable_gst_value = [];
              $total_qty_inventory = $total_qty_non_inventry = 0;
              for($i=1;$i<=$total_item_rows;$i++):
                if( isset($form_data['itemName'][$i-1]) && $form_data['itemName'][$i-1] !== '' ) {
                  $item_name = $form_data['itemName'][$i-1];
                } else {
                  $item_name = '';
                }
                if( isset($form_data['inwardQty'][$i-1]) && $form_data['inwardQty'][$i-1] !== '' ) {
                  $inward_qty = $form_data['inwardQty'][$i-1];
                } else {
                  $inward_qty = '';
                }
                if( isset($form_data['freeQty'][$i-1]) && $form_data['freeQty'][$i-1] !== '' ) {
                  $free_qty = $form_data['freeQty'][$i-1];
                } else {
                  $free_qty = '';
                }
                if( isset($form_data['billedQty'][$i-1]) && $form_data['billedQty'][$i-1] !== '' ) {
                  $billed_qty = $form_data['billedQty'][$i-1];
                } else {
                  $billed_qty = '';
                }
                if( isset($form_data['billedQty'][$i-1]) && $form_data['billedQty'][$i-1] !== '' ) {
                  $billed_qty = $form_data['billedQty'][$i-1];
                } else {
                  $billed_qty = $inward_qty-$free_qty;
                }
                if( isset($form_data['mrp'][$i-1]) && $form_data['mrp'][$i-1] !== '' ) {
                  $mrp = $form_data['mrp'][$i-1];
                } else {
                  $mrp = '';
                }
                if( isset($form_data['itemRate'][$i-1]) && $form_data['itemRate'][$i-1] !== '' ) {
                  $item_rate = $form_data['itemRate'][$i-1];
                } else {
                  $item_rate = '';
                }
                if( isset($form_data['taxPercent'][$i-1]) && $form_data['taxPercent'][$i-1] !== '' ) {
                  $tax_percent = $form_data['taxPercent'][$i-1];
                } else {
                  $tax_percent = 0;
                }
                if( isset($form_data['itemDiscount'][$i-1]) && $form_data['itemDiscount'][$i-1] !== '' ) {
                  $item_discount = $form_data['itemDiscount'][$i-1];
                } else {
                  $item_discount = '';
                }
                if( isset($form_data['hsnCodes'][$i-1]) && $form_data['hsnCodes'][$i-1] !== '' ) {
                  $hsn_code = $form_data['hsnCodes'][$i-1];
                } else {
                  $hsn_code = '';
                }
                if( isset($form_data['packedQty'][$i-1]) && $form_data['packedQty'][$i-1] !== '' ) {
                  $packed_qty = $form_data['packedQty'][$i-1];
                } else {
                  $packed_qty = '';
                }
                if( isset($form_data['cnos'][$i-1]) && $form_data['cnos'][$i-1] !== '' ) {
                  $cno = $form_data['cnos'][$i-1];
                } else {
                  $cno = '';
                }
                if( isset($form_data['uomName'][$i-1]) && $form_data['uomName'][$i-1] !== '' ) {
                  $uom_name = $form_data['uomName'][$i-1];
                } else {
                  $uom_name = '';
                }
                if( isset($form_data['lotNo'][$i-1]) && $form_data['lotNo'][$i-1] !== '' ) {
                  $lot_no = $form_data['lotNo'][$i-1];
                } else {
                  $lot_no = '';
                }
                if( isset($form_data['itemSku'][$i-1]) && $form_data['itemSku'][$i-1] !== '' ) {
                  $item_sku = $form_data['itemSku'][$i-1];
                } else {
                  $item_sku = '';
                }
                if( isset($form_data['itemStyleCode'][$i-1]) && $form_data['itemStyleCode'][$i-1] !== '' ) {
                  $item_style_code = $form_data['itemStyleCode'][$i-1];
                } else {
                  $item_style_code = '';
                }
                if( isset($form_data['itemSize'][$i-1]) && $form_data['itemSize'][$i-1] !== '' ) {
                  $item_size = $form_data['itemSize'][$i-1];
                } else {
                  $item_size = '';
                }
                if( isset($form_data['itemColor'][$i-1]) && $form_data['itemColor'][$i-1] !== '' ) {
                  $item_color = $form_data['itemColor'][$i-1];
                } else {
                  $item_color = '';
                }
                if( isset($form_data['itemSleeve'][$i-1]) && $form_data['itemSleeve'][$i-1] !== '' ) {
                  $item_sleeve = $form_data['itemSleeve'][$i-1];
                } else {
                  $item_sleeve = '';
                }
                if( isset($form_data['suppBarcode'][$i-1]) && $form_data['suppBarcode'][$i-1] !== '') {
                  $barcode = $form_data['suppBarcode'][$i-1];
                } elseif(isset($form_data['barcode'][$i-1]) && $form_data['barcode'][$i-1] !== '') {
                  $barcode = $form_data['barcode'][$i-1];
                } else {
                  $barcode = '';
                }
                if( isset($form_data['batchNo'][$i-1]) && $form_data['batchNo'][$i-1] !== '' ) {
                  $batch_no = $form_data['batchNo'][$i-1];
                } else {
                  $batch_no = '';
                }
                if( isset($form_data['expiryDate'][$i-1]) && $form_data['expiryDate'][$i-1] !== '' ) {
                  $expiry_date = date("d-m-Y", strtotime($form_data['expiryDate'][$i-1]));
                } else {
                  $expiry_date = '';
                }
                if( isset($form_data['wholesalePrice'][$i-1]) && $form_data['wholesalePrice'][$i-1] !== '' ) {
                  $wholesale_price = $form_data['wholesalePrice'][$i-1];
                } else {
                  $wholesale_price = '';
                }
                if( isset($form_data['onlinePrice'][$i-1]) && $form_data['onlinePrice'][$i-1] !== '' ) {
                  $online_price = $form_data['onlinePrice'][$i-1];
                } else {
                  $online_price = '';
                }
                if( isset($form_data['brandName'][$i-1]) && $form_data['brandName'][$i-1] !== '' ) {
                  $brand_name = $form_data['brandName'][$i-1];
                } else {
                  $brand_name = '';
                }
                if( isset($form_data['categoryName'][$i-1]) && $form_data['categoryName'][$i-1] !== '' ) {
                  $category_name = $form_data['categoryName'][$i-1];
                } else {
                  $category_name = '';
                }
                if( isset($form_data['itemType'][$i-1]) && $form_data['itemType'][$i-1] !== '' ) {
                  $item_type = $form_data['itemType'][$i-1];
                } else {
                  $item_type = '';
                }                
                if(is_numeric($packed_qty) && $packed_qty>0) {
                  $gross_amount = $billed_qty*$item_rate*$packed_qty;
                } else {
                  $gross_amount = $billed_qty*$item_rate;
                }
                if($packed_qty !== '') {
                  $billed_qty = round($billed_qty*$packed_qty,2);
                } else {
                  $billed_qty = round($billed_qty, 2);
                }                
                
                $item_amount = $gross_amount - $item_discount;
                $tax_amount = $item_amount*$tax_percent/100;

                $items_total += $item_amount;
                $total_tax_amount += $tax_amount;
                $total_disc_amount += $item_discount;

                if(isset($taxable_values[$tax_percent])) {
                  $taxable = $taxable_values[$tax_percent] + $item_amount;
                  $gst_value = $taxable_gst_value[$tax_percent] + $tax_amount;

                  $taxable_values[$tax_percent] = $taxable;
                  $taxable_gst_value[$tax_percent] = $gst_value;
                } else {
                  $taxable_values[$tax_percent] = $item_amount;
                  $taxable_gst_value[$tax_percent] = $tax_amount;
                }

                $total_qty_inventory += $item_type === 'p' ? $billed_qty : 0;
                $total_qty_non_inventry += $item_type === 's' ? $billed_qty : 0;
            ?>
              <tr class="purchaseItemRow">
                <td style="width:300px; vertical-align: middle;"><?php echo $item_name ?></td>
                <td style="width:80px; vertical-align: middle;"><?php echo $hsn_code ?></td>                  
                <td style="width:80px; vertical-align: middle;"><?php echo $cno ?></td>                  
                <td style="width:80px; vertical-align: middle;"><?php echo $lot_no ?></td>                  
                <td style="width:50px; vertical-align: middle;" align="right"><?php echo number_format($inward_qty,2,'.','') ?></td>
                <td style="width:50px; vertical-align: middle;" align="right"><?php echo number_format($free_qty,2,'.','') ?></td>
                <td style="width:55px; vertical-align: middle;" align="right"><?php echo number_format($billed_qty,2,'.','') ?></td>
                <td style="width:60px; vertical-align: middle;" align="right"><?php echo number_format($packed_qty,2,'.','') ?></td>                  
                <td style="width:55px; vertical-align: middle;" align="right"><?php echo number_format($mrp,2,'.','') ?></td>
                <td style="width:55px; vertical-align: middle;" align="right"><?php echo number_format($item_rate,2,'.','') ?></td>
                <td style="width:80px; vertical-align: middle;" align="right"><?php echo number_format(round($gross_amount,2),2,'.','') ?></td>
                <td style="width:80px; vertical-align: middle;" align="right"><?php echo number_format($item_discount,2,'.','') ?></td>
                <td style="width:70px; vertical-align: middle;" align="right"><?php echo number_format(round($item_amount,2),2,'.','') ?></td>
                <td style="width:80px; vertical-align: middle;" align="right"><?php echo number_format($tax_percent, 2, '.', '') ?></td>
                <td style="width:50px vertical-align: middle;"  class="text-center"><?php echo '' ?></td>
                <td style="width:50px vertical-align: middle;"  class="text-center"><?php echo '' ?></td>
                <td style="width:20px vertical-align: middle;"  class="text-center"><?php echo $cno ?></td>
                <td style="width:20px vertical-align: middle;"  class="text-center"><?php echo $uom_name ?></td>
                <td style="width:20px vertical-align: middle;"  class="text-center"><?php echo $barcode ?></td>
                <td style="width:20px vertical-align: middle;"  class="text-center"><?php echo $item_sku ?></th>
                <td style="width:20px vertical-align: middle;"  class="text-center"><?php echo $item_style_code ?></td>
                <td style="width:20px vertical-align: middle;"  class="text-center"><?php echo $item_size ?></td>
                <td style="width:20px vertical-align: middle;"  class="text-center"><?php echo $item_color ?></td>
                <td style="width:20px vertical-align: middle;"  class="text-center"><?php echo $item_sleeve ?></td>                
                <td style="width:20px vertical-align: middle;"  class="text-center"><?php echo $batch_no ?></td>                
                <td style="width:20px vertical-align: middle;"  class="text-center"><?php echo $expiry_date ?></td>
                <td style="width:20px vertical-align: middle;"  class="text-center"><?php echo $wholesale_price ?></td>                
                <td style="width:20px vertical-align: middle;"  class="text-center"><?php echo $online_price ?></td>
              </tr>
            <?php 
              endfor;
              $grand_total = round($items_total + $total_tax_amount, 2);
              $grand_total += ($shipping_charges + $packing_charges + $insurance_charges + $other_charges);

              $round_off = round($grand_total) - $grand_total;
              $net_pay = round($grand_total);
            ?>
            </tbody>
          </table>
        </div>
        <div class="table-responsive">
          <table class="table table-striped table-hover font14" id="owItemsTable" style="margin-bottom:0px;">
              <tr>
                <th width="10%" class="text-center valign-middle">PO Qty - Inventory</th>
                <th width="10%" class="text-center valign-middle">PO Qty - Noninventory</th>
                <th width="10%" class="text-center valign-middle">Taxable Value (in Rs.)</th>
                <th width="10%"  class="text-center valign-middle">G.S.T (in Rs.)</th>             
                <th width="10%" class="text-center valign-middle">Packing Charges (in Rs.)</th>
                <th width="10%"  class="text-center valign-middle">Shipping Charges (in Rs.)</th>             
              </tr>
              <tr>
                <td style="vertical-align:middle;text-align:right;color: red; font-weight: bold;font-size: 16px;"><?php echo number_format($total_qty_inventory, 2, '.', '') ?></td>
                <td style="vertical-align:middle;text-align:right;color: red; font-weight: bold;font-size: 16px;"><?php echo number_format($total_qty_non_inventry, 0, '.', '') ?></td>
                <td style="vertical-align:middle;text-align:right;"><?php echo number_format($items_total, 2, '.', '') ?></td>
                <td style="vertical-align:middle;text-align:right;"><?php echo number_format($total_tax_amount, 2, '.', '') ?></td>
                <td style="vertical-align:middle;text-align:right;"><?php echo number_format($packing_charges, 2, '.', '') ?></td>
                <td style="vertical-align:middle;text-align:right;"><?php echo number_format($shipping_charges, 2, '.', '') ?></td>
              </tr>
          </table>
        </div>
        <?php /*
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
              <tr>
                <td style="vertical-align:middle;font-weight:bold;" align="center">Notes / Comments</td>
                <td style="vertical-align:middle;text-align:right;" colspan="3"><?php echo $remarks ?></td>
              </tr>
          </table>
        </div>
        */ ?>
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
          </table>
        </div>
      </div>
    </section>
  </div>
</div>