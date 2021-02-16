<?php

  if(isset($form_data['locationCode'])) {
    $location_code = $form_data['locationCode'];
  } elseif($default_location !== '') {
    $location_code = $default_location;
  } else {
    $location_code = '';
  }

  // dump($location_code);
  // exit;

  $tot_products = isset($form_data['itemDetails']['itemName']) ? count($form_data['itemDetails']['itemName']) : 0;
  $current_date = isset($form_data['indentDate']) && $form_data['indentDate']!=='' ? date("d-m-Y", strtotime($form_data['indentDate'])) : date("d-m-Y");
  $primary_mobile_no = isset($form_data['primaryMobileNo']) ? $form_data['primaryMobileNo'] : '';
  $alter_mobile_no = isset($form_data['alternativeMobileNo']) ? $form_data['alternativeMobileNo'] : '';  
  $customer_name = isset($form_data['name']) ? $form_data['name'] : '';
  $agent_code = isset($form_data['agentCode']) ? $form_data['agentCode'] : '';
  $executive_code = isset($form_data['executiveCode']) ? $form_data['executiveCode'] : '';  
  $campaign_code = isset($form_data['campaignCode']) ? $form_data['campaignCode'] : '';
  $remarks = isset($form_data['remarks']) ? $form_data['remarks'] : '';
  $billing_rate = isset($form_data['billingRate']) ? $form_data['billingRate'] : '';

  $ow_items_class = $tot_products > 0 ? '' : 'style="display:none;"';

  $last_indent_no = isset($last_indent_no) ? (int)$last_indent_no : false;
  if($last_indent_no > 0) {
    if($indent_print_option === 'printWithOutRate') {
      $indent_print_url = '/print-indent-wor?indentNo='.$last_indent_no;
    } elseif($indent_print_option === 'printWithRate') {
      $indent_print_url = '/print-indent?indentNo='.$last_indent_no;
    }
  }

  // dump($form_data);
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panelBox">
      <div class="panelBody">
        <?php echo $flash_obj->print_flash_message(); ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/sales-indents/list" class="btn btn-default">
              <i class="fa fa-book"></i> Sales Indent Register 
            </a> 
          </div>
        </div>        
        <form id="salesIndentForm" method="POST">
          <div class="table-responsive">
            <table class="table table-hover font12" style="border-top:none;border-left:none;border-right:none;border-bottom:1px solid; margin-bottom: 0px;">
              <thead>
                <tr>
                  <td style="vertical-align:middle;font-size:15px;font-weight:bold;border-right:none;border-left:none;border-top:none;text-align:left;width:10%;padding-left:5px;">Scan Barcode</td>
                  <td style="vertical-align:middle;border-right:none;border-left:none;border-top:none;width:10%;">
                    <input
                      type="text"
                      id="indentBarcode"
                      class="saleItem"
                      style="font-size:16px;font-weight:bold;border:1px dashed #225992;padding-left:5px;font-weight:bold;width:150px;"
                      maxlength="15"
                    />
                  </td>

                  <td style="vertical-align:middle;font-size:15px;font-weight:bold;border-right:none;border-left:none;border-top:none;text-align:right;width:8%;padding-left:5px;">Scanned Qty.</td>
                  <td id="indentScannedQty" style="width:8%;border-right:none;border-left:none;border-top:none;font-size:20px;font-weight:bold;vertical-align:middle;color:#225992;">&nbsp;</td>

                  <td style="vertical-align:middle;font-size:16px;font-weight:bold;border-right:none;border-left:none;border-top:none;text-align:right;width:8%;padding-left:5px;">Store name</td>
                  <td style="vertical-align:middle;border-right:none;border-left:none;border-top:none;width:19%;text-align:left;">
                    <select class="form-control" name="locationCode" id="locationCode" disabled>
                      <?php 
                        foreach($client_locations as $location_key=>$value):
                          $location_key_a = explode('`', $location_key);
                          if($location_code === $location_key_a[0]) {
                            $selected = 'selected="selected"';
                          } else {
                            $selected = '';
                          }
                      ?>
                       <option value="<?php echo $location_key_a[0] ?>" <?php echo $selected ?>>
                          <?php echo $value ?>
                        </option>
                      <?php endforeach; ?>
                    </select>                    
                  </td>

                  <td style="vertical-align:middle;font-size:16px;font-weight:bold;border-right:none;border-left:none;border-top:none;text-align:right;width:10%;padding-left:5px;">Billing rate</td>
                  <td style="vertical-align:middle;border-right:none;border-left:none;border-top:none;width:12%;text-align:left;">
                    <?php echo ucwords($billing_rate) ?>
                  </td>
                </tr>
              </thead>
            </table>
          </div>
          <div class="table-responsive">
            <table class="table font12" style="border:none;">
              <tr>
                <td style="border-top:none; border-left:none; border-right:none; border-bottom:none; text-align: right; width: 40%; color: #225992; font-weight: bold; font-size: 14px; vertical-align: middle;">Last item scanned:</td>
                <td  style="border-top:none; border-left:none; border-right:none; border-bottom: 2px dotted; text-align: left; width: 60%; color: #4ab033; font-weight: bold; font-size: 16px; vertical-align: middle;" id="lastScannedSaleItem">&nbsp;</td>
              </tr>
            </table>
          </div>          
          <div class="table-responsive">
            <table <?php echo $ow_items_class ?> class="table table-striped table-hover font12" id="owItemsTable">
              <thead>
                <tr>
                  <th width="5%"  class="text-center">Sno.</th>                  
                  <th width="12%" class="text-center">Item name</th>
                  <th width="11%" class="text-center">Lot no.</th>
                  <th width="11%" class="text-center">Ordered<br />qty.</th>
                  <th width="8%"  class="text-center">Item rate<br />( Rs. )</th>
                  <th width="8%"  class="text-center">Amount<br />( Rs. )</th>
                  <th width="10%" class="text-center">Options</th>                
                </tr>
              </thead>
              <tbody id="tBodyowItems">
                <?php
                  $tot_item_amount = $tot_taxable_amount = $tot_tax_amount = $tot_discount = 0;
                  $tot_bill_qty = $netpay = $netpay_actual = $round_off = 0;               
                  if($tot_products > 0):
                    for($i=0;$i<$tot_products;$i++):
                      $item_name = isset($form_data['itemDetails']['itemName'][$i]) ? $form_data['itemDetails']['itemName'][$i] : '';
                      $lot_no = isset($form_data['itemDetails']['lotNo'][$i]) ? $form_data['itemDetails']['lotNo'][$i] : '';
                      $item_qty = isset($form_data['itemDetails']['itemSoldQty'][$i]) ? $form_data['itemDetails']['itemSoldQty'][$i] : 0;
                      $item_rate = isset($form_data['itemDetails']['itemRate'][$i]) ? $form_data['itemDetails']['itemRate'][$i] : '';
                      $barcode = isset($form_data['itemDetails']['barcode'][$i]) ? $form_data['itemDetails']['barcode'][$i] : '';
                      if($item_qty > 0 && $item_rate > 0) {
                        $item_amount = round($item_qty*$item_rate,2);
                        $tot_item_amount += $item_amount;
                        $tot_bill_qty += $item_qty;
                      } else {
                        $item_amount = 0;
                      }
                  ?>
                  <tr id="tr_<?php echo $barcode.'_'.$lot_no ?>" class="bcrow" index="<?php echo $i ?>">
                    <td align="right" style="vertical-align:middle;" class="itemSlno"><?php echo $i+1 ?></td>
                    <td style="vertical-align:middle;">
                      <input 
                        type="text" 
                        name="itemDetails[itemName][]" 
                        id="iname_<?php echo $i ?>" 
                        size="30" 
                        class="saleItem noEnterKey" 
                        index="<?php echo $i ?>" 
                        value="<?php echo $item_name ?>"
                        readonly
                      />
                    </td>
                    <td style="vertical-align:middle;">
                      <input 
                        class="form-control lotNo"
                        name="itemDetails[lotNo][]"
                        id="lotNo_<?php echo $i ?>"
                        index="<?php echo $i ?>"
                        value="<?php echo $lot_no ?>"
                        readonly
                      />
                    </td>
                    <td style="vertical-align:middle;" align="center">
                      <input 
                        class="form-control saleItemQty"
                        name="itemDetails[itemSoldQty][]"
                        id="qty_<?php echo $i ?>"
                        index="<?php echo $i ?>"
                        value="<?php echo $item_qty ?>"
                        style="text-align:right;"
                      />
                    </td>
                    <td style="vertical-align:middle;" align="center">
                      <input
                        readonly
                        class = "mrp text-right noEnterKey"
                        id = "mrp_<?php echo $i ?>"
                        index = "<?php echo $i ?>"
                        size = "10"
                        value = "<?php echo $item_rate ?>"
                        name = "itemDetails[itemRate][]"
                        readonly
                      />
                    </td>
                    <td class="grossAmount" id="grossAmount_<?php echo $i ?>" index="<?php echo $i ?>" style="vertical-align:middle;text-align:right;">
                      <?php echo number_format($item_amount,2,'.','') ?>
                    </td>
                    <td style="vertical-align:middle;text-align:center;">
                      <div class="btn-actions-group">
                        <a class="btn btn-danger deleteOwItem" href="javascript:void(0)" title="Delete Row" id="delrow_<?php echo $barcode.'_'.$lot_no ?>">
                          <i class="fa fa-times"></i>
                        </a>
                      </div>
                    </td> 
                  </tr>

                  <?php 
                    /* Show error tr if there are any errors in the line item */
                    if( isset($errors['itemDetails']['itemName'][$i]) ) {
                  ?>
                      <tr>
                        <td style="border:none;">&nbsp;</td>
                        <td style="border:none;text-align:center;"><?php echo isset($errors['itemDetails']['itemName'][$i]) ? '<span class="error">Invalid</span>': '' ?></td>
                        <td style="border:none;text-align:center;"><?php echo isset($errors['itemDetails']['lotNo'][$i]) ? '<span class="error">Invalid</span>': '' ?></td>                        
                        <td style="border:none;text-align:center;"><?php echo isset($errors['itemDetails']['itemSoldQty'][$i]) ? '<span class="error">Invalid</span>': '' ?></td>
                        <td style="border:none;text-align:center;"><?php echo isset($errors['itemDetails']['itemRate'][$i]) ? '<span class="error">Invalid</span>': '' ?></td>
                      </tr>
                  <?php } ?>

                  <?php endfor; ?>
                <?php endif; ?>
              </tbody>
              <tfoot id="tFootowItems">
                <?php
                  if(isset($form_data['taxCalcOption']) && $form_data['taxCalcOption'] === 'i') {
                    $tot_tax_amount = 0;
                  }
                  $netpay_actual = $tot_taxable_amount + $tot_tax_amount;
                  $round_off = round($netpay_actual,0) - $netpay_actual;
                  $netpay = round($netpay_actual,0);
                ?>
                <tr>
                  <td colspan="3" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;">Total Bill Qty.</td>
                  <td id="totalItems" name="totalItems" style="vertical-align:middle;font-weight:bold;font-size:18px;text-align:right;"><?php echo $tot_bill_qty > 0 ? $tot_bill_qty : '' ?></td>
                  <td style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;">Gross Amount</td>
                  <td id="grossAmount" class="" style="font-size:16px;text-align:right;font-weight:bold;"><?php echo $tot_item_amount > 0 ? number_format($tot_item_amount, 2, '.', '') : '' ?></td>
                  <td>&nbsp;</td>
                </tr>
              </tfoot>
            </table>
          </div>
          <div class="panel" style="margin-bottom:10px;<?php echo $tot_products > 0 ? '' : 'display:none;' ?>" id="customerWindow">
            <div class="panel-body" style="border: 1px dotted;">
              <div class="form-group">
                <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
                  <label class="control-label">Date of indent (dd-mm-yyyy)</label>
                  <div class="form-group">
                    <div class="col-lg-12" style="padding-left:0px;">
                      <div class="input-append date" data-date="<?php echo $current_date ?>" data-date-format="dd-mm-yyyy">
                        <input class="span2" value="<?php echo $current_date ?>" size="16" type="text" readonly name="indentDate" id="indentDate" />
                        <span class="add-on"><i class="fa fa-calendar"></i></span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
                  <label class="control-label">Wholesaler / Agent name</label>
                  <div class="select-wrap">                        
                    <select 
                      class="form-control"
                      id="agentCode" 
                      name="agentCode"
                    >
                      <?php
                        foreach($agents as $key=>$value):
                          if($key === $agent_code) {
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
                <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
                  <label class="control-label">Campaign name</label>
                  <div class="select-wrap">                        
                    <select 
                      class="form-control"
                      id="campaignCode"
                      name="campaignCode"
                    >
                      <?php
                        foreach($campaigns as $key=>$value):
                          if($key === $campaign_code) {
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
                <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
                  <label class="control-label">Customer name</label>
                  <input type="text" class="form-control noEnterKey cnameAc" name="name" id="name" value="<?php echo $customer_name ?>" />
                  <?php if(isset($errors['name'])): ?>
                    <span class="error"><?php echo $errors['name'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
                  <label class="control-label">Primary mobile number</label>
                  <input type="text" class="form-control noEnterKey" name="primaryMobileNo" id="primaryMobileNo" maxlength="10" value="<?php echo $primary_mobile_no ?>">
                  <?php if(isset($errors['primaryMobileNo'])): ?>
                    <span class="error"><?php echo $errors['primaryMobileNo'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
                  <label class="control-label">Alternative mobile number</label>
                  <input type="text" class="form-control noEnterKey" name="alterMobileNo" id="alterMobileNo" maxlength="10" value="<?php echo $alter_mobile_no ?>">
                  <?php if(isset($errors['alterMobileNo'])): ?>
                    <span class="error"><?php echo $errors['alterMobileNo'] ?></span>
                  <?php endif; ?>
                </div>
              </div>
              <div class="form-group">
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Marketing executive name</label>
                  <div class="select-wrap"> 
                    <select 
                      class="form-control"
                      id="executiveCode" 
                      name="executiveCode"
                    >
                      <?php
                        foreach($executives as $key=>$value):
                          if($key === $executive_code) {
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
                <div class="col-sm-12 col-md-8 col-lg-8">
                  <label class="control-label">Remarks (not more than 300 characters)</label>
                  <textarea name="remarks" id="remarks" class="form-control noEnterKey" maxlength="300"><?php echo $remarks ?></textarea>
                </div>
              </div>
            </div>
          </div>
          <input type="hidden" name="ic" id="ic" value="<?php echo $indent_code ?>" />
          <input type="hidden" name="in" id="in" value="<?php echo $indent_number ?>" />
          <input type="hidden" name="billingRate" id="billingRate" value="<?php echo $billing_rate ?>" />
          <div class="text-center" id="saveWindow" style="margin-top: 20px; <?php echo $tot_products > 0 ? '' : 'display:none;' ?>">
            <button class="btn btn-primary saveUpdateIndent" id="SaveInvoiceWr" name="op" value="printWithOutRate">
              <i class="fa fa-edit"></i> Update Indent
            </button>&nbsp;
           <button class="btn btn-danger cancelButton" id="ieWithBarcode">
              <i class="fa fa-times"></i> Cancel
           </button>
          </div>
          <?php if(count($location_codes)>0) :?>
            <?php foreach($location_codes as $location_id => $location_code): ?>
              <input type="hidden" value="<?php echo $location_code ?>" id="loc_<?php echo $location_id ?>" />
            <?php endforeach; ?>
          <?php endif; ?>
        </form>
      </div>
    </section>
  </div>
</div>

<div class="modal fade" id="dualLotModalIndent" tabindex="-1" role="dialog" aria-labelledby="dualLotModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" align="center">
        <h5 class="modal-title" id="dualLotNosTitleIndent" style="font-size: 18px; font-weight: bold; color: #225992;"></h5>
      </div>
      <p style="margin: 0;text-align: center;color: red;font-weight: bold;font-size: 16px;">Multiple entries found. Select Lot No. to continue</p>
      <div class="modal-body" id="dualLotsIndent" style="padding:0px;"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="selectedDualLotNoIndentCancel">Cancel</button>
        <button type="button" class="btn btn-primary" id="selectedDualLotNoIndent">Select</button>
      </div>
    </div>
  </div>
</div>

<?php if($last_indent_no>0) : ?>
  <script>
    (function() {
      var printUrl = '<?php echo $indent_print_url ?>';
      var printWindow = window.open(printUrl, "_blank", "left=0,top=0,width=300,height=300,toolbar=0,scrollbars=0,status=0");
    })();
  </script>
<?php endif; ?>

