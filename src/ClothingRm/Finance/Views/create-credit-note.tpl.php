<?php
  use Atawa\Utilities;

  $credit_note_types = ['wo' => 'Without Items', 'w' => 'With Items'];
  $current_date = isset($form_data['cnDate']) && $form_data['cnDate']!=='' ? date("d-m-Y", strtotime($form_data['cnDate'])) : date("d-m-Y");

  $tax_calc_option = isset($form_data['taxCalcOption']) ? $form_data['taxCalcOption'] : 'i';
  $location_code = isset($form_data['locationCode']) && $form_data['locationCode'] !== '' ? $form_data['locationCode'] : '';
  $cn_value = isset($form_data['cnValue']) && $form_data['cnValue'] !== '' ? $form_data['cnValue'] : '';
  $customer_name = isset($form_data['customerName']) && $form_data['customerName'] !== '' ? $form_data['customerName'] : '';
  $adj_reason_code = isset($form_data['adjReasonCode']) && $form_data['adjReasonCode'] !== '' ? $form_data['adjReasonCode'] : '';
  $m_cn_type = isset($form_data['mCreditNoteType']) && $form_data['mCreditNoteType'] !== '' ? $form_data['mCreditNoteType'] : 'wo';
  $bill_no = isset($form_data['billNo']) ? $form_data['billNo'] : '';
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panel">
      <div class="panel-body">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/fin/credit-notes" class="btn btn-default">
              <i class="fa fa-sign-out"></i> Credit Notes Register 
            </a> 
          </div>
        </div>
        <form class="form-validate form-horizontal" method="POST" autocomplete="off" id="manCreditNoteForm">
          
          <div class="form-group">
            <div class="col-sm-12 col-md-2 col-lg-2">
              <label class="control-label labelStyle">Credit note type</label>
              <div class="select-wrap">
                <select 
                  class="form-control mCreditNoteType"
                  style="font-size:12px; border: 2px dotted;color: blue;"
                  name="mCreditNoteType" 
                >
                  <?php
                    foreach($credit_note_types as $key=>$value):
                      if($key === $m_cn_type) {
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
            <div class="col-lg-12 col-md-2 col-lg-2" style="padding-left:0px;">
              <label class="control-label labelStyle">Date</label>
              <div class="input-append date" data-date="<?php echo $current_date ?>" data-date-format="dd-mm-yyyy">
                <input class="span2" value="<?php echo $current_date ?>" size="16" type="text" readonly name="cnDate" id="cnDate" style="font-size: 13px;" />
                <span class="add-on"><i class="fa fa-calendar"></i></span>
              </div>
              <?php if(isset($errors['cnDate'])): ?>
                <span class="error"><?php echo $errors['cnDate'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3" style="padding-left:0px;">
              <label class="control-label labelStyle">Customer name</label>
              <input 
                type="text"
                id="customerName"
                name="customerName"
                style="font-weight:bold;font-size:14px;padding-left:5px;"
                value="<?php echo $customer_name ?>"
                class="form-control cnameAc noEnterKey"                    
              />
              <?php if(isset($form_errors['customerName'])): ?>
                <span class="error"><?php echo $form_errors['customerName'] ?></span>
              <?php endif; ?>                  
            </div>
            <div class="col-sm-12 col-md-2 col-lg-2" style="padding-left:0px;">
              <label class="control-label labelStyle">Bill no.</label>
              <input 
                type="text"
                id="billNo"
                name="billNo"
                style="font-weight:bold;font-size:14px;padding-left:5px;"
                value="<?php echo $bill_no ?>"
                class="form-control noEnterKey"
                maxlength="50"
              />
              <?php if(isset($form_errors['billNo'])): ?>
                <span class="error"><?php echo $form_errors['billNo'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3" style="padding-left:0px;">
              <label class="control-label labelStyle">Store name</label>
              <div class="select-wrap">
                <select 
                  class="form-control" 
                  name="locationCode" 
                  id="locationCode"                 
                  style="font-weight:bold;font-size:14px;padding-left:5px;border:1px dashed;"
                >
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
            <div class="col-sm-12 col-md-2 col-lg-2">
              <label class="control-label labelStyle">Credit note value (in Rs.)</label>
              <input 
                type="text" 
                class="form-control" 
                name="cnValue" 
                id="cnValue" 
                value="<?php echo $cn_value ?>"
                style="font-weight:bold;font-size:14px;padding-left:5px;border:1px dashed;"
              >
              <?php if(isset($form_errors['cnValue'])): ?>
                <span class="error"><?php echo $form_errors['cnValue'] ?></span>
              <?php endif; ?>   
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3" style="padding-left:0px;">
              <label class="control-label labelStyle">Reason</label>
              <div class="select-wrap">
                <select
                  class="form-control"
                  id="adjReasonCode" 
                  name="adjReasonCode"
                >
                  <?php
                    foreach($adj_reasons as $key=>$value):
                      $adj_a = explode('_', $value);
                      if($adj_reason_code === $key) {
                        $selected = 'selected="selected"';
                      } else {
                        $selected = '';
                      }
                      if( is_array($adj_a) && isset($adj_a[1])>1 ) {
                        $disabled = 'disabled';
                      } else {
                        $disabled = '';
                      }                      
                  ?>
                    <option value="<?php echo $key ?>"  <?php echo $selected.' '.$disabled ?>>
                      <?php echo $adj_a[0] ?>
                    </option>
                  <?php endforeach; ?>                            
                </select>
              </div>
            </div>
            <div id="mCnOptionalFields" style="<?php echo $m_cn_type === 'wo' ? 'display: none;' : '' ?>">
              <div class="col-sm-12 col-md-2 col-lg-2" style="padding-left:0px;">
                <label class="control-label labelStyle">Tax calculation method</label>
                <div class="select-wrap">                        
                  <select 
                    class="form-control taxCalcOption"
                    id="taxCalcOption" 
                    name="taxCalcOption"
                    style="font-size:12px;"
                  >
                    <?php
                      foreach($taxcalc_opt_a as $key=>$value):
                        if($key === $tax_calc_option) {
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
          </div>

          <div class="table-responsive" id="mCnItems" style="<?php echo $m_cn_type === 'wo' ? 'display: none;' : '' ?>">
            <table class="table table-striped table-hover font12">
              <thead>
                <tr>
                  <th width="4%"   class="text-center">Sno.</th>                  
                  <th width="8%"  class="text-center">Item name</th>
                  <th width="10%"  class="text-center">Returned qty.</th>
                  <th width="10%"  class="text-center">M.R.P<br />( in Rs. )</th>
                  <th width="10%"  class="text-center">Purchase rate<br />( in Rs. )</th>
                  <th width="10%"  class="text-center">GST<br />( in % )</th>
                  <th width="10%"  class="text-center">Taxable<br />( Rs. )</th>
                  <th width="10%"  class="text-center">GST Value<br />( Rs. )</th>
                  <th width="10%"  class="text-center">Item Total<br />( Rs. )</th>
                </tr>
              </thead>
              <tbody id="tBodyowItems">
                <tr>
                  <td style="vertical-align:middle;font-weight:bold;font-size:18px;text-align:right;" colspan="6">T O T A L S</td>
                  <td style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;" class="totalTaxable"><?php // echo $tot_bill_qty > 0 ? $tot_bill_qty : '' ?></td>
                  <td style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;" class="totalGST">&nbsp;</td>
                  <td style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;" class="totalAmount"><?php //echo $tot_item_amount > 0 ? number_format($tot_item_amount, 2, '.', '') : '' ?></td>
                </tr>                
                <?php 
                  $tot_item_amount = $tot_taxable_amount = $tot_tax_amount = $tot_discount = 0;
                  $tot_bill_qty = $netpay = $netpay_actual = $round_off = 0;
                  for($i=0;$i<15;$i++):
                    $item_name = isset($form_data['itemDetails']['itemName'][$i]) ? $form_data['itemDetails']['itemName'][$i] : '';
                    $item_qty = isset($form_data['itemDetails']['itemSoldQty'][$i]) && is_numeric($form_data['itemDetails']['itemSoldQty'][$i]) ? $form_data['itemDetails']['itemSoldQty'][$i] : 0;
                    $purchase_rate = isset($form_data['itemDetails']['purchaseRate'][$i]) && is_numeric($form_data['itemDetails']['purchaseRate'][$i]) ? $form_data['itemDetails']['purchaseRate'][$i] : 0;
                    $item_rate = isset($form_data['itemDetails']['itemRate'][$i]) && is_numeric($form_data['itemDetails']['itemRate'][$i]) ? $form_data['itemDetails']['itemRate'][$i] : 0;
                    $tax_percent = isset($form_data['itemDetails']['itemTaxPercent'][$i]) && is_numeric($form_data['itemDetails']['itemTaxPercent'][$i]) ? $form_data['itemDetails']['itemTaxPercent'][$i] : 0;
                    if($tax_calc_option === 'i') {
                      // $item_base_price = round($item_rate - (($item_rate*$tax_percent)/(100+$tax_percent)), 2);
                      // $item_tax_amount = round(($item_rate - $item_base_price) * $item_qty, 2);
                      // $item_total = round(($item_base_price*$item_qty)+$item_tax_amount, 2);
                      $item_total = round($item_rate*$item_qty, 2);
                      $item_tax_amount = 0;
                    } else {
                      $item_base_price = round($item_rate,2);
                      $item_tax_amount = round(round(($item_rate*$tax_percent)/100, 2)*$item_qty,2);
                      $item_total = round( ($item_base_price*$item_qty)+$item_tax_amount, 2);
                    }

                    $tot_item_amount += $item_total;
                    $tot_taxable_amount += $item_tax_amount;
                    $tot_bill_qty += $item_qty;
                ?>
                  <tr>
                    <td align="center" style="vertical-align:middle;" class="itemSlno"><?php echo $i+1 ?></td>
                    <td style="vertical-align:middle;text-align: center;">
                      <input 
                        type="text" 
                        name="itemDetails[itemName][]" 
                        id="iname_<?php echo $i+1 ?>" 
                        size="25" 
                        class="noEnterKey inameAc" 
                        index="<?php echo $i+1 ?>" 
                        value="<?php echo $item_name ?>"
                      />
                      <?php if(isset($form_errors['itemDetails']['itemName'][$i])): ?>
                        <span class="error">Invalid</span>
                      <?php endif; ?>
                    </td>
                    <td style="vertical-align:middle;" align="center">
                      <input
                        type="text"
                        class="saleItemQty noEnterKey"
                        id="qty_<?php echo $i+1 ?>"
                        name="itemDetails[itemSoldQty][]"
                        index="<?php echo $i+1 ?>"
                        value="<?php echo $item_qty > 0 ? $item_qty : '' ?>"
                        size="10"
                      />
                      <?php if(isset($form_errors['itemDetails']['itemSoldQty'][$i])): ?>
                        <span class="error">Invalid</span>
                      <?php endif; ?>                      
                    </td>
                    <td style="vertical-align:middle;" align="center">
                      <input 
                        class = "mrp noEnterKey"
                        id = "mrp_<?php echo $i+1 ?>"
                        index = "<?php echo $i+1 ?>"
                        size = "10"
                        value = "<?php echo $item_rate > 0 ? $item_rate : ''  ?>"
                        name = "itemDetails[itemRate][]"
                      />
                      <?php if(isset($form_errors['itemDetails']['itemRate'][$i])): ?>
                        <span class="error">Invalid</span>
                      <?php endif; ?>                      
                    </td>
                    <td style="vertical-align:middle;">
                      <input 
                        class = "purchaseRate noEnterKey"
                        id = "purchaseRate_<?php echo $i+1 ?>"
                        index = "<?php echo $i+1 ?>"
                        size = "10"
                        value = "<?php echo $purchase_rate > 0 ? $purchase_rate : '' ?>"
                        name = "itemDetails[purchaseRate][]"
                      />
                      <?php if(isset($form_errors['itemDetails']['purchaseRate'][$i])): ?>
                        <span class="error">Invalid</span>
                      <?php endif; ?>                       
                    </td>
                    <td style="vertical-align:middle;">
                      <div class="select-wrap">
                        <select 
                          class="form-control saItemTax"
                          id="saItemTax_<?php echo $i+1 ?>" 
                          name="itemDetails[itemTaxPercent][]"
                          index = "<?php echo $i+1 ?>"
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
                            <option value='<?php echo $value ?>' <?php echo $selected ?>>
                              <?php echo $value ?>
                            </option>
                          <?php endforeach; ?>                            
                        </select>
                      </div>
                      <?php if(isset($form_errors['itemDetails']['itemTaxPercent'][$i])): ?>
                        <span class="error">Invalid</span>
                      <?php endif; ?>                      
                    </td>
                    <td id="taxable_<?php echo $i+1 ?>" align="right" class="valign-middle rowTaxable"><?php echo $item_rate > 0 ? number_format($item_rate, 2, '.', '') : '' ?></td>
                    <td id="taxAmount_<?php echo $i+1 ?>" align="right" class="valign-middle rowTaxAmount"><?php echo $item_tax_amount > 0 ? number_format($item_tax_amount, 2, '.', '') : '' ?></td>
                    <td id="totalAmount_<?php echo $i+1 ?>" align="right" class="valign-middle  rowTotalAmount"><?php echo $item_total > 0 ? number_format($item_total, 2, '.', '') : '' ?></td>
                  </tr>
                <?php endfor; ?>
              </tbody>
              <tfoot id="tFootowItems">
                <tr>
                  <td style="vertical-align:middle;font-weight:bold;font-size:18px;text-align:right;" colspan="6">T O T A L S</td>
                  <td style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;" class="totalTaxable"><?php echo $tot_bill_qty > 0 ? $tot_bill_qty : '' ?></td>
                  <td style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;" class="totalGST">&nbsp;</td>
                  <td style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;" class="totalAmount"><?php echo $tot_item_amount > 0 ? number_format($tot_item_amount, 2, '.', '') : '' ?></td>
                </tr>
              </tfoot>
            </table>
          </div>
          <br />
          <div class="text-center">
            <button class="btn btn-success cancelOp" id="manCreditNoteSubmit">
              <i class="fa fa-save"></i> Save
            </button>&nbsp;&nbsp;
            <button class="btn btn-danger cancelButton" id="manCreditNoteCancel">
              <i class="fa fa-times"></i> Cancel
            </button>            
          </div>
        </form>
      </div>
    </section>
  </div>
</div>