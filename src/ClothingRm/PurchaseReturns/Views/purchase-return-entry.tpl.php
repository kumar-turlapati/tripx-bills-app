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
  } elseif(isset($form_data['locationID'])) {
    $location_code = $form_data['locationID'];
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
          <?php if(isset($form_errors['itemDetailsError'])): ?>
            <span class="error" style="font-size:18px;font-weight:bold;"><?php echo $form_errors['itemDetailsError'] ?></span>
          <?php endif; ?>          
          <div class="table-responsive">
            <table class="table table-striped table-hover item-detail-table font11" id="purchaseTable">
              <thead>
                <tr>
                  <th style="width:250px;" class="text-center purItem">Item name</th>
                  <th style="width:50px;" class="text-center purItem">Lot No.</th>
                  <th style="width:80px;" class="text-center purItem">HSN / SAC Code</th>                  
                  <th style="width:100px;" class="text-center">Packed<br />/unit</th>                  
                  <th style="width:50px;" class="text-center">Purchased<br />qty.</th>
                  <th style="width:50px" class="text-center">Return<br />qty.</th>
                  <th style="width:55px" class="text-center">MRP<br />( in Rs. )</th>
                  <th style="width:55px" class="text-center">Rate / Unit<br />( in Rs. )</th>
                  <th style="width:55px" class="text-center">Gross Amt.<br />( in Rs. )</th>
                  <th style="width:55px" class="text-center">Discount<br />( in Rs. )</th>                  
                  <th style="width:70px" class="text-center">Taxable Amt.<br />( in Rs. )</th>
                  <th style="width:70px" class="text-center">G.S.T<br />( in % )</th>
                </tr>
              </thead>
              <tbody>
              <?php
                $items_total =  $total_tax_amount = $items_tot_after_discount = $total_disc_amount = 0;
                $taxable_values = $taxable_gst_value = [];
                $total_item_qty = 0;
                for($i=1; $i <= $total_item_rows; $i++):
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
                  if( isset($form_data['lotNos'][$i-1]) && $form_data['lotNos'][$i-1] !== '' ) {
                    $lot_no = $form_data['lotNos'][$i-1];
                  } else {
                    $lot_no = '';
                  }                  
                  if( isset($form_data['inwardQty'][$i-1]) && $form_data['inwardQty'][$i-1] !== '' ) {
                    $inward_qty = $form_data['inwardQty'][$i-1];
                  } else {
                    $inward_qty = '';
                  }
                  if( isset($form_data['returnQty'][$i-1]) && $form_data['returnQty'][$i-1] !== '' ) {
                    $return_qty = $form_data['returnQty'][$i-1];
                  } else {
                    $return_qty = '';
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

                  $billed_qty = $inward_qty - $free_qty;
                  $gross_amount = ($billed_qty*$item_rate)*$form_data['packedQty'][$i-1];
                  $item_amount = $gross_amount - $item_discount;
                  $tax_amount = $item_amount*$tax_percent/100;

                  $items_total += $item_amount;
                  $total_tax_amount += $tax_amount;
                  $total_disc_amount += $item_discount;

                  $inward_qty1 = $inward_qty;
                  $inward_qty = $inward_qty * $packed_qty;

                  $total_item_qty += $inward_qty;

                  if(isset($taxable_values[$tax_percent])) {
                    $taxable = $taxable_values[$tax_percent] + $item_amount;
                    $gst_value = $taxable_gst_value[$tax_percent] + $tax_amount;

                    $taxable_values[$tax_percent] = $taxable;
                    $taxable_gst_value[$tax_percent] = $gst_value;
                  } else {
                    $taxable_values[$tax_percent] = $item_amount;
                    $taxable_gst_value[$tax_percent] = $tax_amount;
                  }
                  $packed_string = $inward_qty1.' * '.$packed_qty;
              ?>
                <tr class="purchaseItemRow">
                  <td style="width:150px;">
                    <input 
                      type="text" 
                      name="itemName[]" 
                      class="form-control inameAc noEnterKey purItem"
                      style="font-size:12px;"
                      id="itemName_<?php echo $i ?>"
                      value="<?php echo $item_name ?>"
                      disabled
                    />
                    <?php if( isset($form_errors['itemDetails'][$i-1]['itemName']) ) :?>
                      <span class="error">Invalid</span>
                    <?php endif; ?>
                  </td>
                  <td style="width:130px;">
                    <input 
                      type="text" 
                      name="lotNo[]" 
                      class="form-control inameAc noEnterKey purItem"
                      style="font-size:12px;"
                      id="lotNo_<?php echo $i ?>"
                      value="<?php echo $lot_no ?>"
                      disabled
                    />
                    <?php if( isset($form_errors['itemDetails'][$i-1]['lotNo']) ) :?>
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
                      disabled
                    />
                    <?php if( isset($form_errors['itemDetails'][$i-1]['hsnSacCode']) ) :?>
                      <span class="error">Invalid</span>
                    <?php endif; ?>
                  </td>
                  <td 
                    style="text-align:right;width:80px;font-weight:bold;vertical-align:middle;"><?php echo $packed_string ?>
                  </td>                  
                  <td style="width:50px;">
                    <input
                      type="text"
                      class="form-control inwRcvdQty noEnterKey"
                      name="inwardQty[]"
                      placeholder="Rcvd."
                      style="width:60px;font-size:12px;text-align:right;"
                      id="inwRcvdQty_<?php echo $i ?>"
                      value="<?php echo $inward_qty ?>"
                      disabled         
                    />
                    <?php if( isset($form_errors['itemDetails'][$i-1]['inwardQty']) ) :?>
                      <span class="error">Invalid</span>
                    <?php endif; ?>                    
                  </td>
                  <td style="width:70px;">
                    <input 
                      type="text"
                      id="returnQty_<?php echo $i ?>"
                      name="returnQty[<?php echo $item_code.'__'.$lot_no ?>]"                      
                      class="form-control returnQty noEnterKey"
                      value="<?php echo $return_qty ?>"
                      style="text-align:right;width:70px;background-color:#f1f442;border:1px dashed #000;font-weight:bold;"
                    />
                    <?php if( isset($form_errors['itemDetails'][$i-1]['returnQty']) ) :?>
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
                      disabled         
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
                      disabled         
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
                      value="<?php echo round($gross_amount,2) ?>"
                      disabled
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
                      disabled    
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
                      value="<?php echo round($item_amount,2) ?>"
                      disabled
                    />
                  </td>
                  <td style="width:80px;">
                    <div class="select-wrap">                        
                      <select 
                        class="form-control inwItemTax" 
                        id="inwItemTax_<?php echo $i ?>" 
                        name="taxPercent[]"
                        style="font-size:12px;"
                        disabled
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
                $items_tot_after_discount = round($items_total - $total_disc_amount, 2);
                $grand_total = round($items_tot_after_discount + $total_tax_amount, 2);
                $round_off = round($grand_total) - $grand_total;
                $net_pay = round($grand_total);
              ?>
                <tr>
                  <td colspan="2" style="font-weight:bold;font-size:16px;text-align:center;">Total Qty.</td>
                  <td colspan="3" style="font-weight:bold;font-size:16px;text-align:center;">Taxable (in Rs.)</td>
                  <td colspan="2" style="font-weight:bold;font-size:16px;text-align:center;">GST (in Rs.)</td>
                  <td colspan="2" style="font-weight:bold;font-size:16px;text-align:center;">Roundoff (in Rs.)</td>
                  <td colspan="3" style="font-weight:bold;font-size:16px;text-align:center;">Net Pay (in Rs.)</td>
                </tr>
                <tr>
                  <td colspan="2" style="font-weight:bold;font-size:18px;text-align:center;color:#225992"><?php echo $total_item_qty ?></td>
                  <td colspan="3" style="font-weight:bold;font-size:18px;text-align:center;color:#225992"><?php echo number_format(round($items_total, 2),'2','.','') ?></td>
                  <td colspan="2" style="font-weight:bold;font-size:18px;text-align:center;color:#225992"><?php echo number_format(round($total_tax_amount, 2),'2','.','') ?></td>
                  <td colspan="2" style="font-weight:bold;font-size:18px;text-align:center;color:#225992"><?php echo number_format(round($round_off, 2),'2','.','') ?></td>
                  <td colspan="3" style="font-weight:bold;font-size:20px;text-align:center;color:#225992"><?php echo number_format(round($net_pay, 2),'2','.','') ?></td>                  
                </tr>
                <tr>
                  <td style="vertical-align:middle;font-weight:bold;font-size:14px;" align="center">Notes / Comments</td>
                  <td colspan="11" style="vertical-align:middle;text-align:left;height:25px;"><?php echo $remarks ?></td>
                </tr>
              </tbody>
            </table>
          </div>
          <input type = "hidden" id="inwDiscountPercent" name="discountPercent" value="0" disabled />
          <input type = "hidden" id="cs" name="cs" value="<?php echo $client_business_state ?>" disabled />
          <div class="panel" style="margin-bottom:20px;">
            <div class="panel-body" style="border: 2px dotted;">
              <div class="form-group">
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Date of return (dd-mm-yyyy)</label>
                  <div class="form-group">
                    <div class="col-lg-12">
                      <div class="input-append date" data-date="<?php echo $purchase_date ?>" data-date-format="dd-mm-yyyy">
                        <input class="span2" value="<?php echo $purchase_date ?>" size="16" type="text" readonly name="returnDate" id="returnDate" />
                        <span class="add-on"><i class="fa fa-calendar"></i></span>
                      </div>
                      <?php if(isset($form_errors['returnDate'])): ?>
                        <span class="error"><?php echo $form_errors['returnDate'] ?></span>
                      <?php endif; ?>                  
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
                  <?php if(isset($form_errors['supplierID'])): ?>
                    <span class="error"><?php echo $form_errors['supplierID'] ?></span>
                  <?php endif; ?>              
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Store name (against which store this entry effects)</label>
                  <div class="select-wrap">
                    <select class="form-control" name="locationCode" id="locationCode" disabled>
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
                    disabled
                  >
                  <?php if(isset($form_errors['poNo'])): ?>
                      <span class="error"><?php echo $form_errors['poNo'] ?></span>
                  <?php endif; ?>              
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
                    <?php if(isset($form_errors['paymentMethod'])): ?>
                      <span class="error"><?php echo $form_errors['paymentMethod'] ?></span>
                    <?php endif; ?>
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
            </div>
          </div>
          <div class="text-center">
            <button class="btn btn-danger" id="inwCancel">
              <i class="fa fa-times"></i> Cancel
            </button>
            <button class="btn btn-primary" id="inwSave">
              <i class="fa fa-save"></i> Save
            </button>
          </div>          
        </form>
      </div>
    </section>
  </div>
</div>