<?php

  // dump($form_data, $form_errors);
  // dump($form_errors);
  // dump($form_data);
  // dump($taxes, $taxes_raw);
  // exit;

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
  if(isset($form_data['poNo'])) {
    $po_no = $form_data['poNo'];
  } else {
    $po_no = '';
  }
  if(isset($form_data['remarks'])) {
    $remarks = $form_data['remarks'];
  } else {
    $remarks = '';
  }
  if(isset($form_data['locationCode'])) {
    $location_code = $form_data['locationCode'];
  } else {
    $location_code = '';
  }
  if(isset($form_data['supplyType'])) {
    $supply_type = $form_data['supplyType'];
  } else {
    $supply_type = '';
  }
  if(isset($form_data['supplierStateID'])) {
    $supplier_state_id = $form_data['supplierStateID'];
  } else {
    $supplier_state_id = '';
  }
  if(isset($form_data['supplierGSTNo'])) {
    $supplier_gst_no = $form_data['supplierGSTNo'];
  } else {
    $supplier_gst_no = '';
  }
  $packing_charges = isset($form_data['packingCharges']) ? $form_data['packingCharges'] : '';
  $shipping_charges = isset($form_data['shippingCharges']) ? $form_data['shippingCharges'] : '';
  $insurance_charges = isset($form_data['insuranceCharges']) ? $form_data['insuranceCharges'] : '';
  $other_charges = isset($form_data['otherCharges']) ? $form_data['otherCharges'] : '';
  $transporter_name = isset($form_data['transporterName']) ? $form_data['transporterName'] : '';
  $lr_no = isset($form_data['lrNo']) ? $form_data['lrNo'] : '';
  $lr_date = isset($form_data['lrDate']) ? $form_data['lrDate'] : '';
  $challan_no = isset($form_data['challanNo']) ? $form_data['challanNo'] : '';  
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panelBox">
      <div class="panelBody">
        <?php echo $utilities->print_flash_message() ?>
        <?php if($api_error !== ''): ?>
          <div class="alert alert-danger" role="alert">
            <strong>Error!</strong> <?php echo $api_error ?> 
          </div>
        <?php endif; ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/inward-entry/list" class="btn btn-default">
              <i class="fa fa-book"></i> Purchase Register
            </a>
            <a href="/inward-entry/bulk-upload" class="btn btn-default">
              <i class="fa fa-upload"></i> Upload Inward Entries
            </a>
          </div>
        </div>

        <form class="form-validate form-horizontal" method="POST" id="inwardEntryForm" autocomplete="off">
          <div class="panel" style="margin-bottom:5px;">
            <div class="panel-body">
              <h2 class="hdg-reports borderBottom">Transaction Details</h2>
              <div class="form-group">
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Date of inward (dd-mm-yyyy)</label>
                  <div class="form-group">
                    <div class="col-lg-12">
                      <div class="input-append date" data-date="<?php echo $purchase_date ?>" data-date-format="dd-mm-yyyy">
                        <input class="span2" value="<?php echo $purchase_date ?>" size="16" type="text" readonly name="purchaseDate" id="purchaseDate" />
                        <span class="add-on"><i class="fa fa-calendar"></i></span>
                      </div>
                      <?php if(isset($form_errors['purchaseDate'])): ?>
                        <span class="error"><?php echo $form_errors['purchaseDate'] ?></span>
                      <?php endif; ?>                  
                    </div>
                  </div>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Supplier name</label>
                  <div class="select-wrap">
                    <select class="form-control" name="supplierID" id="supplierID">
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
                  <?php if(isset($form_errors['supplierID'])): ?>
                    <span class="error"><?php echo $form_errors['supplierID'] ?></span>
                  <?php endif; ?>              
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Store name (against which store this entry effects)</label>
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
                    placeholder="{{ Will be generated automatically }}"
                    readonly
                  >
                  <?php if(isset($form_errors['poNo'])): ?>
                      <span class="error"><?php echo $form_errors['poNo'] ?></span>
                  <?php endif; ?>              
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Payment method</label>
                  <div class="select-wrap">
                    <select class="form-control" name="paymentMethod" id="paymentMethod">
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
                    <?php if(isset($form_errors['paymentMethod'])): ?>
                      <span class="error"><?php echo $form_errors['paymentMethod'] ?></span>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                    <label class="control-label">Credit period (in days)</label>
                    <div class="select-wrap">
                      <select class="form-control" name="creditDays" id="creditDays">
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
                      <?php if(isset($form_errors['creditDays'])): ?>
                        <span class="error"><?php echo $form_errors['creditDays'] ?></span>
                      <?php endif; ?>
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
                          if((int)$supplier_state_id === (int)$key) {
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
                    <?php if(isset($form_errors['supplierState'])): ?>
                      <span class="error"><?php echo $form_errors['supplierState'] ?></span>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Supplier GST No.</label>
                  <input
                    type="text" 
                    class="form-control noEnterKey" 
                    name="supplierGSTNo"
                    id="supplierGSTNo" 
                    value="<?php //echo $po_no ?>"
                    disabled
                  >
                  <?php if(isset($form_errors['supplierGSTNo'])): ?>
                    <span class="error"><?php echo $form_errors['supplierGSTNo'] ?></span>
                  <?php endif; ?>
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
                    <?php if(isset($form_errors['supplyType'])): ?>
                      <span class="error"><?php echo $form_errors['supplyType'] ?></span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <div class="col-sm-12 col-md-3 col-lg-3 m-bot15">
                  <label class="control-label">Packing charges (in Rs.)</label>
                  <input
                    type="text"
                    size="10"
                    id="packingCharges"
                    name="packingCharges"
                    maxlength="10"
                    style="font-weight:bold;font-size:14px;padding-left:5px;border:1px dashed;"
                    value="<?php echo $packing_charges ?>"
                    class="form-control"
                  />
                  <?php if(isset($errors['packingCharges'])): ?>
                    <span class="error"><?php echo $errors['packingCharges'] ?></span>
                  <?php endif; ?>                  
                </div>                
                <div class="col-sm-12 col-md-3 col-lg-3 m-bot15">
                  <label class="control-label">Shipping charges (in Rs.)</label>
                  <input
                    type="text"
                    size="10"
                    id="shippingCharges"
                    name="shippingCharges"
                    maxlength="10"
                    style="font-weight:bold;font-size:14px;padding-left:5px;border:1px dashed;"
                    value="<?php echo $shipping_charges ?>"
                    class="form-control"
                  />
                  <?php if(isset($errors['shippingCharges'])): ?>
                    <span class="error"><?php echo $errors['shippingCharges'] ?></span>
                  <?php endif; ?>                  
                </div>
                <div class="col-sm-12 col-md-3 col-lg-3 m-bot15">
                  <label class="control-label">Insurance charges (in Rs.)</label>
                  <input
                    type="text"
                    size="15"
                    id="insuranceCharges"
                    name="insuranceCharges"
                    style="font-weight:bold;font-size:14px;padding-left:5px;border:1px dashed;"
                    value="<?php echo $insurance_charges ?>"
                    class="form-control"                    
                  />
                  <?php if(isset($errors['insuranceCharges'])): ?>
                    <span class="error"><?php echo $errors['insuranceCharges'] ?></span>
                  <?php endif; ?>                   
                </div>
                <div class="col-sm-12 col-md-3 col-lg-3 m-bot15">
                  <label class="control-label">Other charges (in Rs.)</label>
                  <input
                    type="text"
                    size="15"
                    id="otherCharges"
                    name="otherCharges"
                    style="font-weight:bold;font-size:14px;padding-left:5px;border:1px dashed;"
                    value="<?php echo $other_charges ?>"
                    class="form-control"                    
                  />
                  <?php if(isset($errors['otherCharges'])): ?>
                    <span class="error"><?php echo $errors['otherCharges'] ?></span>
                  <?php endif; ?>                   
                </div>
              </div>
              <div class="form-group">
                <div class="col-sm-12 col-md-3 col-lg-3 m-bot15">
                  <label class="control-label">Transporter name</label>
                  <input 
                    type="text"
                    size="15"
                    id="transporterName"
                    name="transporterName"
                    style="font-weight:bold;font-size:14px;padding-left:5px;border:1px dashed;"
                    value="<?php echo $transporter_name ?>"
                    class="form-control"                    
                  />
                  <?php if(isset($errors['transporterName'])): ?>
                    <span class="error"><?php echo $errors['transporterName'] ?></span>
                  <?php endif; ?>                  
                </div>                
                <div class="col-sm-12 col-md-3 col-lg-3">
                  <label class="control-label">L.R. No(s)</label>
                  <input
                    type="text"
                    size="10"
                    id="lrNos"
                    name="lrNos"
                    style="font-weight:bold;font-size:14px;padding-left:5px;border:1px dashed;"
                    value="<?php echo $lr_no ?>"
                    class="form-control"
                  />
                  <?php if(isset($errors['lrNo'])): ?>
                    <span class="error"><?php echo $errors['lrNo'] ?></span>
                  <?php endif; ?>                  
                </div>
                <div class="col-sm-12 col-md-3 col-lg-3">
                  <label class="control-label">L.R. Date</label>
                  <input
                    type="text"
                    size="15"
                    id="lrDate"
                    name="lrDate"
                    style="font-weight:bold;font-size:14px;padding-left:5px;border:1px dashed;"
                    value="<?php echo $lr_date ?>"
                    class="form-control"                    
                  />
                  <?php if(isset($errors['lrDate'])): ?>
                    <span class="error"><?php echo $errors['lrDate'] ?></span>
                  <?php endif; ?>                   
                </div>
                <div class="col-sm-12 col-md-3 col-lg-3">
                  <label class="control-label">Challan no.</label>
                  <input
                    type="text"
                    size="15"
                    id="challanNo"
                    name="challanNo"
                    style="font-weight:bold;font-size:14px;padding-left:5px;border:1px dashed;"
                    value="<?php echo $challan_no ?>"
                    class="form-control"           
                  />
                  <?php if(isset($errors['challanNo'])): ?>
                    <span class="error"><?php echo $errors['challanNo'] ?></span>
                  <?php endif; ?>                   
                </div>
              </div>                
            </div>
          </div>
          <h2 class="hdg-reports">Item Details</h2>
          <?php if(isset($form_errors['itemDetailsError'])): ?>
            <span class="error"><?php echo $form_errors['itemDetailsError'] ?></span>
          <?php endif; ?>          
          <div class="table-responsive">
            <table class="table table-striped table-hover item-detail-table font11" id="purchaseTable" style="width:1300px;">
              <thead>
                <tr>
                  <th style="width:220px;" class="text-center purItem">Item name</th>
                  <th style="width:80px;"  class="text-center purItem">HSN / SAC Code</th>                  
                  <th style="width:50px;"  class="text-center">Received<br />qty.</th>
                  <th style="width:50px"   class="text-center">Free<br />qty.</th>
                  <th style="width:50px"   class="text-center">Billed<br />qty.</th>
                  <th style="width:60px"   class="text-center">Packed/<br />Unit</th>                  
                  <th style="width:55px"   class="text-center">MRP<br />( in Rs. )</th>
                  <th style="width:55px"   class="text-center">Rate / Unit<br />( in Rs. )</th>
                  <th style="width:55px"   class="text-center">Gross Amt.<br />( in Rs. )</th>
                  <th style="width:55px"   class="text-center">Discount<br />( in Rs. )</th>                  
                  <th style="width:70px"   class="text-center">Taxable Amt.<br />( in Rs. )</th>
                  <th style="width:73px"   class="text-center">G.S.T<br />( in % )</th>
                  <th style="width:50px"   class="text-center">Brand</th>                  
                  <th style="width:50px"   class="text-center">Category</th>
                  <th style="width:20px"   class="text-center">Container<br />No.</th>                  
                  <th style="width:20px"   class="text-center">UOM<br />Name</th>                  
                </tr>
              </thead>
              <tbody>
              <?php
                $items_total =  $total_tax_amount = $items_tot_after_discount = $total_disc_amount = 0;
                $taxable_values = $taxable_gst_value = [];
                for($i=1; $i <= $total_item_rows; $i++):
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
                  if( isset($form_data['packedQty'][$i-1]) && $form_data['packedQty'][$i-1] !== '' ) {
                    $packed_qty = $form_data['packedQty'][$i-1];
                  } else {
                    $packed_qty = '';
                  }
                  if( isset($form_data['categoryName'][$i-1]) && $form_data['categoryName'][$i-1] !== '' ) {
                    $category_name = $form_data['categoryName'][$i-1];
                  } else {
                    $category_name = '';
                  }
                  if( isset($form_data['rackNo'][$i-1]) && $form_data['rackNo'][$i-1] !== '' ) {
                    $rack_no = $form_data['rackNo'][$i-1];
                  } else {
                    $rack_no = '';
                  }
                  if( isset($form_data['brandName'][$i-1]) && $form_data['brandName'][$i-1] !== '' ) {
                    $brand_name = $form_data['brandName'][$i-1];
                  } else {
                    $brand_name = '';
                  }
                  if( isset($form_data['cno'][$i-1]) && $form_data['cno'][$i-1] !== '' ) {
                    $cno = $form_data['cno'][$i-1];
                  } else {
                    $cno = '';
                  }
                  if( isset($form_data['uomName'][$i-1]) && $form_data['uomName'][$i-1] !== '' ) {
                    $uom_name = $form_data['uomName'][$i-1];
                  } else {
                    $uom_name = '';
                  }                                               

                  $billed_qty = $inward_qty-$free_qty;
                  if(is_numeric($packed_qty) && $packed_qty>0) {
                    $gross_amount = $billed_qty*$item_rate*$packed_qty;
                  } else {
                    $gross_amount = $billed_qty*$item_rate;
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
              ?>
                <tr class="purchaseItemRow">
                  <td style="width:450px;">
                    <input 
                      type="text" 
                      name="itemName[]" 
                      class="form-control inameAc noEnterKey purItem"
                      style="font-size:12px;"
                      id="itemName_<?php echo $i ?>"
                      value="<?php echo $item_name ?>"
                    />
                    <?php if( isset($form_errors['itemDetails'][$i-1]['itemName']) ) :?>
                      <span class="error">Invalid</span>
                    <?php endif; ?>
                  </td>
                  <td style="width:80px;">
                    <input 
                      type="text" 
                      name="hsnSacCode[]" 
                      class="form-control noEnterKey hsnSacCode"
                      style="font-size:12px;"
                      id="hsnSacCode_<?php echo $i ?>"
                      value="<?php echo $hsn_code ?>"
                    />
                    <?php if( isset($form_errors['itemDetails'][$i-1]['hsnSacCode']) ) :?>
                      <span class="error">Invalid</span>
                    <?php endif; ?>
                  </td>                  
                  <td style="width:50px;">
                    <input
                      type="text"
                      class="form-control inwRcvdQty noEnterKey"
                      name="inwardQty[]"
                      placeholder="Rcvd."
                      style="width:60px;font-size:12px;"
                      id="inwRcvdQty_<?php echo $i ?>"
                      value="<?php echo $inward_qty ?>"
                      title="Last transaction details for the item will be fetched automatically."                    
                    />
                    <?php if( isset($form_errors['itemDetails'][$i-1]['inwardQty']) ) :?>
                      <span class="error">Invalid</span>
                    <?php endif; ?>                    
                  </td>
                  <td style="width:50px;">
                    <input
                      type="text"
                      class="form-control inwFreeQty noEnterKey" 
                      name="freeQty[]" 
                      placeholder="Free"
                      style="width:60px;font-size:12px;"
                      id="inwFreeQty_<?php echo $i ?>"
                      value="<?php echo $free_qty ?>"                    
                    />
                    <?php if( isset($form_errors['itemDetails'][$i-1]['freeQty']) ) :?>
                      <span class="error">Invalid</span>
                    <?php endif; ?>                     
                  </td>
                  <td style="width:55px;">
                    <input 
                      type="text"
                      id="inwBillQty_<?php echo $i ?>"
                      class="form-control inwBillQty noEnterKey"
                      value="<?php echo $billed_qty ?>"
                      style="font-size:12px;"
                      disabled
                    />
                    <?php if( isset($form_errors['itemDetails'][$i-1]['inwBillQty']) ) :?>
                      <span class="error">Invalid</span>
                    <?php endif; ?>                    
                  </td>
                  <td style="width:60px;">
                    <input 
                      type="text" 
                      name="packedQty[]"
                      placeholder="Pkd.Qty."
                      class="form-control noEnterKey"
                      style="width:60px;font-size:12px;"
                      id="packed_<?php echo $i ?>"
                      value="<?php echo $packed_qty ?>"                      
                    />
                    <?php if( isset($form_errors['itemDetails'][$i-1]['mrp']) ) :?>
                      <span class="error">Invalid</span>
                    <?php endif; ?>               
                  </td>
                  <td style="width:55px;">
                    <input 
                      type="text" 
                      name="mrp[]"
                      placeholder="M.R.P"
                      class="form-control noEnterKey"
                      style="width:60px;font-size:12px;"
                      id="mrp_<?php echo $i ?>"
                      value="<?php echo $mrp ?>"                      
                    />
                    <?php if( isset($form_errors['itemDetails'][$i-1]['mrp']) ) :?>
                      <span class="error">Invalid</span>
                    <?php endif; ?>               
                  </td>
                  <td style="width:55px;">
                    <input 
                      type="text" 
                      name="itemRate[]"
                      id="inwItemRate_<?php echo $i ?>" 
                      class="form-control inwItemRate noEnterKey"
                      placeholder="Rate/Unit"
                      style="width:80px;font-size:12px;"
                      value="<?php echo $item_rate ?>"                      
                    />
                    <?php if( isset($form_errors['itemDetails'][$i-1]['itemRate']) ) :?>
                      <span class="error">Invalid</span>
                    <?php endif; ?>                     
                  </td>
                  <td style="width:80px;">
                    <input
                      type="text"
                      id="inwItemGrossAmount_<?php echo $i ?>"
                      class="form-control inwItemGrossAmount"
                      placeholder="Gross Amount"
                      style="width:70px;font-size:12px;text-align:right;"
                      disabled
                      value="<?php echo round($gross_amount,2) ?>"
                    />
                  </td>
                  <td style="width:80px;">
                    <input 
                      type="text" 
                      name="itemDiscount[]"
                      id="inwItemDiscount_<?php echo $i ?>" 
                      class="form-control inwItemDiscount noEnterKey"
                      placeholder="Discount"
                      style="font-size:12px;"
                      value="<?php echo $item_discount ?>"                      
                    />
                    <?php if( isset($form_errors['itemDetails'][$i-1]['itemDiscount']) ) :?>
                      <span class="error">Invalid</span>
                    <?php endif; ?>
                  </td>
                  <td style="width:70px;" align="right">
                    <input
                      type="text"
                      name="amount[]"
                      id="inwItemAmount_<?php echo $i ?>"
                      class="form-control inwItemAmount"
                      placeholder="Amount"
                      style="width:70px;font-size:12px;text-align:right;"
                      disabled
                      value="<?php echo round($item_amount,2) ?>"
                    />
                  </td>
                  <td style="width:160px;">
                    <div class="select-wrap">                        
                      <select 
                        class="form-control inwItemTax" 
                        id="inwItemTax_<?php echo $i ?>" 
                        name="taxPercent[]"
                        style="font-size:12px;"
                      >
                        <?php 
                          foreach($taxes as $key=>$value):
                            if((float)$value === (float)$tax_percent) {
                              $selected = 'selected="selected"';
                            } else {
                              $selected = '';
                            }
                        ?>
                          <option value="<?php echo number_format((float)$value,2) ?>" <?php echo $selected ?>>
                            <?php echo $value ?>
                          </option>
                        <?php endforeach; ?>                            
                      </select>
                      <?php if( isset($form_errors['itemDetails'][$i-1]['taxPercent']) ) :?>
                      <span class="error">Invalid</span>
                      <?php endif; ?>                       
                    </div>
                  </td>
                  <td style="width:50px;">
                    <input
                      type="text"
                      name="brandName[]"
                      id="brandName_<?php echo $i ?>"
                      class="form-control"
                      placeholder="Brand"
                      style="width:70px;font-size:12px;"
                      value="<?php echo $brand_name ?>"
                    />
                  </td>
                  <td style="width:50px;">
                    <input
                      type="text"
                      name="categoryName[]"
                      id="categoryName_<?php echo $i ?>"
                      class="form-control"
                      placeholder="Category"
                      style="width:70px;font-size:12px;"
                      value="<?php echo $category_name ?>"
                    />
                  </td>
                  <td style="width:20px;">
                    <input
                      type="text"
                      name="cno[]"
                      id="cno_<?php echo $i ?>"
                      class="form-control"
                      placeholder="CNO"
                      style="width:70px;font-size:12px;"
                      value="<?php echo $cno ?>"
                    />
                  </td>
                  <td style="width:20px;">
                    <input
                      type="text"
                      name="uom[]"
                      id="uom_<?php echo $i ?>"
                      class="form-control"
                      placeholder="UOM"
                      style="width:70px;font-size:12px;"
                      value="<?php echo $uom_name ?>"
                    />
                  </td>                                                     
                  <input 
                    type="hidden" 
                    id="inwItemTaxAmt_<?php echo $i ?>"
                    data-rate="<?php echo $tax_percent ?>"
                    value="<?php echo $tax_amount ?>"
                    class="inwItemTaxAmount"
                  />
                </tr>
              <?php 
                endfor;

                // $items_tot_after_discount = round($items_total - $total_disc_amount, 2);
                $grand_total = round($items_total + $total_tax_amount, 2);
                $round_off = round($grand_total) - $grand_total;
                $net_pay = round($grand_total);
              ?>
                <tr>
                  <td colspan="15" align="right" style="vertical-align:middle;font-weight:bold;font-size:14px;">Total Taxable Value</td>
                  <td id="inwItemsTotal" align="right" style="vertical-align:middle;font-weight:bold;font-size:14px;"><?php echo number_format(round($items_total, 2), 2, '.','') ?></td>
                </tr>
                <tr>
                  <td colspan="15" align="right" style="vertical-align:middle;font-weight:bold;font-size:14px;">(+) G.S.T</td>
                  <td align="right" id="inwItemTaxAmount" class="taxAmounts" style="vertical-align:middle;font-weight:bold;font-size:14px;"><?php echo number_format(round($total_tax_amount, 2), 2, '.','') ?></td>
                </tr>
                <tr>
                  <td colspan="15" style="vertical-align:middle;font-weight:bold;font-size:14px;" align="right">(+ or -) Round off</td>
                  <td style="vertical-align:middle;text-align:right;font-size:14px;" id="roundOff"><?php echo number_format($round_off,2,'.','') ?></td>
                </tr>
                <tr>
                  <td colspan="15" style="vertical-align:middle;font-weight:bold;font-size:14px;" align="right">Total Amount</td>
                  <td style="vertical-align:middle;text-align:right;font-size:18px;" id="inwNetPay"><?php echo number_format(round($net_pay,2),2,'.','') ?></td>
                </tr>
                <tr>
                  <td style="vertical-align:middle;font-weight:bold;" align="center">Notes / Comments</td>
                  <td style="vertical-align:middle;text-align:right;" colspan="15">
                    <textarea
                      class="form-control noEnterKey"
                      rows="3"
                      cols="100"
                      id="inwRemarks"
                      name="remarks"
                    ><?php echo $remarks ?></textarea>
                  </td>
                </tr>
                <tr>
                  <td colspan="16" style="text-align:center;font-weight:bold;font-size:16px;">GST Summary</td>
                </tr>
                <tr style="padding:0px;margin:0px;">
                  <td colspan="16" style="padding:0px;margin:0px;">
                    <table class="table table-striped table-hover font12 valign-middle">
                      <thead>
                        <th style="text-align:center;">GST Rate (in %)</th>
                        <th style="text-align:right;">Taxable Amount (in Rs.)</th>
                        <th style="text-align:right;">IGST (in Rs.)</th>
                        <th style="text-align:right;">CGST (in Rs.)</th>
                        <th style="text-align:right;">SGST (in Rs.)</th>
                      </thead>
                      <tbody>
                      <?php
                        $tot_taxable_value = $tot_igst_amount = $tot_cgst_amount = $tot_sgst_amount = 0;
                        foreach($taxes as $tax_code => $tax_percent):
                          $tax_percent = intval($tax_percent);
                          if( isset($taxable_values[$tax_percent]) ) {
                            $taxable_value = $taxable_values[$tax_percent];
                            $tot_taxable_value += $taxable_value;
                          } else {
                            $taxable_value = 0;
                          }

                          if(isset($taxable_gst_value[$tax_percent])) {
                            if($supply_type === 'inter') {
                              $cgst_amount = $sgst_amount = 0;
                              $igst_amount = $taxable_gst_value[$tax_percent];
                              $tot_igst_amount += $igst_amount;
                            } else {
                              $cgst_amount = $sgst_amount = round($taxable_gst_value[$tax_percent]/2,2);
                              $igst_amount = 0;
                              $tot_cgst_amount += $cgst_amount;
                              $tot_sgst_amount += $sgst_amount;                              
                            }
                          } else {
                            $cgst_amount = $sgst_amount = $igst_amount = 0;
                          }                          
                      ?>
                        <tr>
                            <input type="hidden" value="<?php echo $tax_percent ?>" class="inwTaxPercents" id="<?php echo $tax_code ?>" />
                            <input type="hidden" value="" id="taxAmount_<?php echo $tax_code ?>" class="taxAmounts" />
                            <td class="font11" style="text-align:right;font-weight:bold;"><?php echo number_format($tax_percent, 2).' %' ?></td>
                            <td class="font11" style="text-align:right;font-weight:bold;" id="taxable_<?php echo $tax_code ?>_amount"><?php echo number_format($taxable_value,2) ?></td>
                            <td class="font11" style="text-align:right;font-weight:bold;" id="taxable_<?php echo $tax_code ?>_igst_value"><?php echo number_format($igst_amount,2)  ?></td>
                            <td class="font11" style="text-align:right;font-weight:bold;" id="taxable_<?php echo $tax_code ?>_cgst_value"><?php echo number_format($cgst_amount,2) ?></td>
                            <td class="font11" style="text-align:right;font-weight:bold;" id="taxable_<?php echo $tax_code ?>_sgst_value"><?php echo number_format($sgst_amount,2) ?></td>
                        </tr>
                      <?php 
                        endforeach; 
                      ?>
                      </tbody>
                    </table>
                  </td>
                </tr>

              </tbody>
            </table>
          </div>
          <div class="text-center" style="margin-top:10px;">
            <button class="btn btn-danger" id="inwCancel">
              <i class="fa fa-times"></i> Cancel
            </button>
            <button class="btn btn-primary" id="inwSave">
              <i class="fa fa-save"></i> Save
            </button>
          </div>
          <input type = "hidden" id="inwDiscountPercent" name="discountPercent" value="0" />
          <input type = "hidden" id="cs" name="cs" value="<?php echo $client_business_state ?>" />          
        </form>
      </div>
    </section>
  </div>
</div>