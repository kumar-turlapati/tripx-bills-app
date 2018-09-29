<?php
  if(isset($form_data['purchaseDate'])) {
    $purchase_date = date("d-m-Y", strtotime($form_data['purchaseDate']));
  } else {
    $purchase_date = date("d-m-Y");
  }
  if(isset($form_data['creditDays'])) {
    $creditDays = $form_data['creditDays'];
  } else {
    $creditDays = 0;
  }
  if(isset($form_data['paymentMethod'])) {
    $paymentMethod = $form_data['paymentMethod'];
  } else {
    $paymentMethod = 0;
  }
  if(isset($form_data['supplierID'])) {
    $supplierCode = $form_data['supplierID'];
  } elseif(isset($form_data['supplierCode'])) {
    $supplierCode = $form_data['supplierCode'];
  } else {
    $supplierCode = '';
  }
  if( isset($form_data['discountPercent']) ) {
    $discount_percent = $form_data['discountPercent'];
  } else {
    $discount_percent = 0;
  }
  if(isset($form_data['otherTaxes'])) {
    $other_taxes = $form_data['otherTaxes'];
  } else {
    $other_taxes = 0;
  }
  if(isset($form_data['adjustment'])) {
    $adjustment = $form_data['adjustment'];
  } else {
    $adjustment = 0;
  }
  if(isset($form_data['shippingCharges'])) {
    $shipping_charges = $form_data['shippingCharges'];
  } else {
    $shipping_charges = 0;
  }
  if(isset($form_data['roundOff'])) {
    $round_off = $form_data['roundOff'];
  } else {
    $round_off = 0;
  }
  if(isset($form_data['poNo'])) {
    $po_no = $form_data['poNo'];
  } else {
    $po_no = '';
  }
  if(isset($form_data['indentNo'])) {
    $indent_no = $form_data['indentNo'];
  } else {
    $indent_no = '';
  }
  if(isset($form_data['remarks'])) {
    $remarks = $form_data['remarks'];
  } else {
    $remarks = '';
  }
  if((int)$form_data['supplierStateID'] === (int)$client_business_state) {
    $supply_type = 'inter';
  } else {
    $supply_type = 'intra';
  }
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panelBox">
      <div class="panelBody">
        <?php echo $utilities->print_flash_message() ?>
        <?php if($page_error !== ''): ?>
          <div class="alert alert-danger" role="alert">
            <strong>Error!</strong> <?php echo $page_error ?> 
          </div>
        <?php endif; ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/barcodes/list" class="btn btn-default">
              <i class="fa fa-book"></i> Barcode Register
            </a>
          </div>
        </div>
        <form class="form-validate form-horizontal" method="POST">
          <div class="table-responsive">
            <table class="table table-striped table-hover item-detail-table font12" id="purchaseTable">
              <thead>
                <tr>
                  <th style="width:35%" class="text-center valign-middle">Item name</th>
                  <th style="width:15%;" class="text-center valign-middle">HSN / SAC Code</th>                  
                  <th style="width:10%;" class="text-center valign-middle">Lot no.</th>
                  <th style="width:10%;" class="text-center valign-middle">Qty.</th>
                  <th style="width:10%;" class="text-center valign-middle">MRP<br />( in Rs. )</th>
                  <th style="width:10%;" class="text-center valign-middle">No. of Stickers<br />required</th>                  
                  <th style="width:10%;" class="text-center valign-middle">Barcode</th>
                </tr>
              </thead>
              <tbody>
              <?php
                $items_total =  $total_tax_amount = $items_tot_after_discount = 0;
                $taxable_values = $taxable_gst_value = [];
                $items_total_qty = 0;
                $barcodes_generated = 0;
                for($i=1;$i<=$total_item_rows;$i++):
                  if( isset($form_data['itemCode'][$i-1]) && $form_data['itemCode'][$i-1] !== '' ) {
                    $item_code = $form_data['itemCode'][$i-1];
                  } else {
                    $item_code = '';
                  }
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
                  if( isset($form_data['lotNo'][$i-1]) &&  $form_data['lotNo'][$i-1] !== '' ) {
                    $lot_no = $form_data['lotNo'][$i-1];
                  } else {
                    $lot_no = '';
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
                  if( isset($form_data['barcode'][$i-1]) && $form_data['barcode'][$i-1] !== '' ) {
                    $barcode = $barcode_text = $form_data['barcode'][$i-1];
                    $barcodes_generated++;
                  } else {
                    $barcode_text = '<span style="color:red;font-size:12px;">- Not Generated -</span>';
                    $barcode = '';
                  }

                  $billed_qty = $inward_qty-$free_qty;
                  $gross_amount = $billed_qty*$item_rate;
                  $item_amount = $gross_amount-$item_discount;
                  $tax_amount = round($item_amount*$tax_percent/100,2);

                  $items_total += $item_amount;
                  $total_tax_amount += $tax_amount;

                  if(isset($taxable_values[$tax_percent])) {
                    $taxable = $taxable_values[$tax_percent] + $item_amount;
                    $gst_value = $taxable_gst_value[$tax_percent] + $tax_amount;

                    $taxable_values[$tax_percent] = $taxable;
                    $taxable_gst_value[$tax_percent] = $gst_value;
                  } else {
                    $taxable_values[$tax_percent] = $item_amount;
                    $taxable_gst_value[$tax_percent] = $tax_amount;
                  }

                  $items_total_qty += $billed_qty;
              ?>
                <tr class="purchaseItemRow font12">
                  <td align="left" class="valign-middle"><?php echo $item_name ?></td>
                  <td align="left" class="valign-middle"><?php echo $hsn_code ?></td>                  
                  <td class="valign-middle"><?php echo $lot_no ?></td>
                  <td align="right" class="valign-middle"><?php echo number_format($billed_qty,2) ?></td>
                  <td align="right" class="valign-middle"><?php echo number_format($mrp,2) ?></td>
                  <td class="valign-middle">
                    <input
                      type="text"
                      class="form-control stickerQty noEnterKey valign-middle" 
                      name="stickerQty[<?php echo $item_code.'__'.$lot_no ?>]" 
                      style="background-color:#f1f442;border:1px solid #000;font-weight:bold;"
                      id="stickerQty_<?php echo $i ?>"
                      value="<?php echo $billed_qty ?>"
                    />
                    <?php if( isset($form_errors['itemDetails'][$i-1]['stickerQty']) ) :?>
                      <span class="error">Invalid</span>
                    <?php endif; ?>
                  </td>        
                  <td align="right" class="valign-middle" style="font-weight:bold;font-size:16px;"><?php echo $barcode_text ?></td>
                  <input
                    type="hidden"
                    class="form-control genBarcode valign-middle" 
                    name="genBarcodes[<?php echo $item_code.'__'.$lot_no ?>]" 
                    id="genBarcode_<?php echo $i ?>"
                    value="<?php echo $barcode ?>"
                  />                  
                </tr>
              <?php 
                endfor;

                $items_tot_after_discount = $items_total-($items_total*$discount_percent)/100;
                $grand_total = $items_tot_after_discount+$total_tax_amount+
                               $other_taxes+$shipping_charges;

                $net_pay = $grand_total+$adjustment+$round_off;
              ?>
                <?php /*
                <tr>
                  <td align="right" style="vertical-align:middle;font-size:14px;">Notes:</td>
                  <td style="vertical-align:middle;text-align:right;" colspan="6"><?php echo $remarks ?></td>
                </tr> */ 
                  if((int)$barcodes_generated !== (int)$total_item_rows) {
                    $button_text = 'Save &amp; Print';
                    $icon_name = 'fa fa-save';
                  } else {
                    $button_text = 'Print';
                    $icon_name = 'fa fa-print';
                  }
                ?>
                <tr>
                  <td colspan="2" style="font-weight:bold;font-size:14px;text-align:center;">Total Qty.</td>
                  <td style="font-weight:bold;font-size:14px;text-align:center;">Taxable (Rs.)</td>
                  <td style="font-weight:bold;font-size:14px;text-align:center;">GST (Rs.)</td>
                  <td style="font-weight:bold;font-size:14px;text-align:center;">Round off (Rs.)</td>
                  <td style="font-weight:bold;font-size:14px;text-align:center;" colspan="2">Total Amount (Rs.)</td>
                </tr>
                <tr>
                  <td colspan="2" style="font-weight:bold;font-size:18px;text-align:center;"><?php echo $items_total_qty ?></td>
                  <td id="inwItemsTotal" align="center" style="vertical-align:middle;font-weight:bold;font-size:18px;"><?php echo number_format(round($items_total, 2),2) ?></td>
                  <td id="inwItemTaxAmount" align="center" class="taxAmounts" style="vertical-align:middle;font-weight:bold;font-size:18px;"><?php echo number_format(round($total_tax_amount,2),2) ?></td>
                  <td style="vertical-align:middle;text-align:center;font-size:18px;" id="roundOff"><?php echo round($round_off,2) ?></td>
                  <td colspan="2" style="vertical-align:middle;text-align:center;font-size:18px;font-weight:bold;" id="inwNetPay"><?php echo number_format(round($net_pay,2),2) ?></td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="panel" style="border:1px dashed #225992;">
            <div class="panel-body">
              <div class="form-group">
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Date of purchase (dd-mm-yyyy)</label>
                  <div class="form-group">
                    <div class="col-lg-12">
                      <div class="input-append date" data-date="<?php echo $purchase_date ?>" data-date-format="dd-mm-yyyy">
                        <input class="span2" value="<?php echo $purchase_date ?>" size="16" type="text" name="purchaseDate" id="purchaseDate" disabled />
                        <span class="add-on"><i class="fa fa-calendar"></i></span>
                      </div>                  
                    </div>
                  </div>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Supplier name</label>
                  <div class="select-wrap">
                    <select class="form-control" name="supplierID" id="supplierID" disabled>
                      <?php 
                        foreach($suppliers as $key=>$value): 
                          if($supplierCode === $key) {
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
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Indent number</label>
                  <input 
                    type="text" 
                    class="form-control noEnterKey" 
                    name="indentNo" 
                    id="indentNo" 
                    value="<?php echo $indent_no ?>"
                    disabled
                  >
                </div>           
              </div>
              <div class="form-group">
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Purchaser order (PO) No.</label>
                  <input 
                    type="text" 
                    class="form-control noEnterKey" 
                    name="poNo" 
                    id="poNo" 
                    value="<?php echo $po_no ?>"
                    disabled
                  >            
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Payment method</label>
                  <div class="select-wrap">
                    <select class="form-control" name="paymentMethod" id="paymentMethod" disabled>
                      <?php 
                        foreach($payment_methods as $key=>$value): 
                          if((int)$paymentMethod === (int)$key) {
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
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Credit period (in days)</label>
                  <div class="select-wrap">
                    <select class="form-control" name="creditDays" id="creditDays" disabled>
                      <?php 
                        foreach($credit_days_a as $key=>$value):
                          if((int)$creditDays === $key) {
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
                </div>
              </div>
              <div class="form-group">
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Supplier location</label>
                  <div class="select-wrap">
                    <select class="form-control" name="supplierState" id="supplierState" disabled>
                      <?php 
                        foreach($states_a as $key=>$value): 
                          if((int)$form_data['supplierStateID'] === (int)$key) {
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
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Supplier GST No.</label>
                  <input 
                    type="text" 
                    class="form-control noEnterKey" 
                    name="supplierGSTNo"
                    id="supplierGSTNo" 
                    value="<?php echo $form_data['supplierGSTNo'] ?>"
                    disabled
                  >              
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                    <label class="control-label">Supply type</label>
                    <div class="select-wrap">
                      <select class="form-control" name="supplyType" id="supplyType" disabled>
                        <?php 
                          foreach($supply_type_a as $key=>$value):
                            if($supply_type === $key) {
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
                </div>
              </div>
              <div class="form-group">
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Sticker print type</label>
                  <div class="select-wrap">
                    <select class="form-control" name="format" id="format">
                      <?php foreach($sticker_print_type_a as $key=>$value): ?>
                        <option value="<?php echo $key ?>"><?php echo $value ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <?php if(isset($form_errors['format'])): ?>
                    <span class="error"><?php echo $form_errors['format'] ?></span>
                  <?php endif; ?>                  
                </div>
              </div>
            </div>
          </div>
          <div class="text-center">
            <button class="btn btn-danger" id="op" name="op" value="saveBarcodes">
              <i class="<?php echo $icon_name ?>"></i> <?php echo $button_text ?>
            </button>
          </div>
        </form>
      </div>
    </section>
  </div>
</div>