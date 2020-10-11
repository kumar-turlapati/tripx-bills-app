<?php
  $tot_products = isset($form_data['itemDetails']['itemName']) ? count($form_data['itemDetails']['itemName']) : 0;
  $current_date = isset($form_data['indentDate']) && $form_data['indentDate']!=='' ? date("d-m-Y", strtotime($form_data['indentDate'])) : date("d-m-Y");
  $primary_mobile_no = isset($form_data['primaryMobileNo']) ? $form_data['primaryMobileNo'] : '';
  $alter_mobile_no = isset($form_data['alternativeMobileNo']) ? $form_data['alternativeMobileNo'] : '';  
  $customer_name = isset($form_data['name']) ? $form_data['name'] : '';
  $remarks = isset($form_data['remarks']) ? $form_data['remarks'] : '';
  $indent_status = isset($form_data['indentStatus']) ? $form_data['indentStatus'] : -1;
  $agent_name = isset($form_data['agentName']) ? $form_data['agentName'] : '';
  $executive_name = isset($form_data['executiveName']) ? $form_data['executiveName'] : '';
  $campaign_name = isset($form_data['campaignName']) ? $form_data['campaignName'] : '';
  $remarks2 = isset($form_data['remarks2']) ? $form_data['remarks2'] : '';
  if($remarks2 !== '') {
    $indent_remarks = $remarks2;
  } elseif($remarks !== '') {
    $indent_remarks = $remarks;
  } else {
    $indent_remarks = '';
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
        <form id="outwardEntryForm" method="POST">
          <div class="table-responsive">
            <table class="table table-striped table-hover font12" id="owItemsTable">
              <thead>
                <tr>
                  <th width="5%"  class="text-center">Date of Indent</th>                  
                  <th width="12%" class="text-center">Wholesaler / Agent name</th>
                  <th width="11%" class="text-center">Campaign Name</th>
                  <th width="11%" class="text-center">Customer Name</th>
                  <th width="8%"  class="text-center">Contact No. (P)</th>
                  <th width="8%"  class="text-center">Contact No. (A)</th>
                  <th width="10%" class="text-center">Executive Name</th>
                </tr>
              </thead>
              <tbody>
                  <tr>
                    <td align="right" style="vertical-align:middle;" class="itemSlno"><?php echo $current_date ?></td>
                    <td style="vertical-align:middle;"><?php echo $agent_name ?></td>
                    <td style="vertical-align:middle;"><?php echo $campaign_name ?></td>
                    <td style="vertical-align:middle;" align="center"><?php echo $customer_name ?></td>
                    <td style="vertical-align:middle;" align="center"><?php echo $primary_mobile_no ?></td>
                    <td style="vertical-align:middle;"><?php echo $alter_mobile_no ?></td>
                    <td style="vertical-align:middle;text-align:center;"><?php echo $executive_name ?></td> 
                  </tr>
              </tbody>
            </table>
          </div>
          <div class="table-responsive">
            <table class="table table-striped table-hover font12" id="owItemsTable">
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
                  <tr>
                    <td align="right" style="vertical-align:middle;" class="itemSlno"><?php echo $i+1 ?></td>
                    <td style="vertical-align:middle;"><?php echo $item_name ?></td>
                    <td style="vertical-align:middle;"><?php echo $lot_no ?></td>
                    <td style="vertical-align:middle;text-align:right;"><?php echo $item_qty ?></td>
                    <td style="vertical-align:middle;text-align:right;"><?php echo $item_rate ?></td>
                    <td style="vertical-align:middle;text-align:right;"><?php echo number_format($item_amount,2,'.','') ?></td>
                    <td style="vertical-align:middle;">&nbsp;</td> 
                  </tr>
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
          <div class="panel" style="margin-bottom:0px;">
            <div class="panel-body" style="border: 1px dotted;">
              <div class="form-group">
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Indent Status</label>
                  <div class="select-wrap">                        
                    <select 
                      class="form-control"
                      id="arStatus"
                      name="arStatus"
                    >
                      <?php
                        foreach($indent_status_a as $key=>$value):
                          if((int)$key === (int)$indent_status) {
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
                  <?php if(isset($errors['arStatus'])): ?>
                    <span class="error"><?php echo $errors['arStatus'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-8 col-lg-8">
                  <label class="control-label">Notes (not more than 300 characters)</label>
                  <textarea name="arRemarks" id="arRemarks" class="form-control noEnterKey" maxlength="300"><?php echo $indent_remarks ?></textarea>
                  <?php if(isset($errors['arRemarks'])): ?>
                    <span class="error"><?php echo $errors['arRemarks'] ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
          <input type="hidden" name="ic" id="ic" value="<?php echo $indent_code ?>" />
          <input type="hidden" name="in" id="in" value="<?php echo $indent_number ?>" />
          <div class="text-center" style="margin-top: 20px;">
            <button class="btn btn-primary">
              <i class="fa fa-save"></i> Update Indent Status
            </button>
           <button class="btn btn-danger cancelButton" id="arIndentStatus">
              <i class="fa fa-times"></i> Cancel
           </button>
          </div>
        </form>
      </div>
    </section>
  </div>
</div>