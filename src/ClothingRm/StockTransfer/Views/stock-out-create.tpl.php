<?php
  if(isset($form_data['invoiceDate']) && $form_data['invoiceDate']!=='') {
    $current_date = date("d-m-Y", strtotime($form_data['invoiceDate']));
  } else {
    $current_date = date("d-m-Y");
  }
  if(isset($form_data['taxCalcOption'])) {
    $tax_calc_option = $form_data['taxCalcOption'];
  } else {
    $tax_calc_option = 'i';
  }  
?>

<div class="row">
  <div class="col-lg-12"> 
    
    <section class="panelBox">
      <div class="panelBody">

        <?php echo $flash_obj->print_flash_message(); ?>
       
        <div class="global-links actionButtons clearfix"> 
          <div class="pull-right text-right">
            <a href="/stock-transfer/register" class="btn btn-default"><i class="fa fa-book"></i> Stock Transfer Register</a>
            <a href="/sales/entry" class="btn btn-default"><i class="fa fa-file-text-o"></i> New Sales Entry </a> 
          </div>
        </div>
        
        <form class="form-validate form-horizontal" method="POST" autocomplete="off" id="stockOutForm">
          <?php /*<h2 class="hdg-reports" id="hdg-reports">Transaction Details</h2> */ ?>
          <div class="panel" style="margin-bottom:0px;">
            <div class="panel-body">
              <div class="form-group">
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">From location</label>
                  <div class="select-wrap">
                    <select class="form-control" name="fromLocation" id="fromLocation">
                      <?php 
                        foreach($client_locations as $key=>$value): 
                          $location_key_a = explode('`', $key);
                          if($from_location === $location_key_a[0]) {
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
                  </div>
                  <?php if(isset($form_errors['fromLocation'])): ?>
                    <span class="error"><?php echo $form_errors['fromLocation'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">To location</label>
                  <div class="select-wrap">
                    <select class="form-control" name="toLocation" id="toLocation">
                      <?php 
                        foreach($client_locations as $key=>$value): 
                          $location_key_a = explode('`', $key);
                          if($to_location === $location_key_a[0]) {
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
                  </div>
                  <?php if(isset($form_errors['toLocation'])): ?>
                    <span class="error"><?php echo $form_errors['toLocation'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Transfer date (dd-mm-yyyy)</label>
                  <div class="form-group">
                    <div class="col-lg-12">
                      <div class="input-append date" data-date="<?php echo $current_date ?>" data-date-format="dd-mm-yyyy">
                        <input class="span2" value="<?php echo $current_date ?>" size="16" type="text" readonly name="transferDate" id="transferDate" />
                        <span class="add-on"><i class="fa fa-calendar"></i></span>
                      </div>
                      <?php if(isset($errors['transferDate'])): ?>
                        <span class="error"><?php echo $errors['transferDate'] ?></span>
                      <?php endif; ?>
                      <input type="hidden" id="taxCalcOption" name="taxCalcOption" value="i" />
                    </div>
                  </div>
                </div>                
              </div>
              <?php /*
              <div class="form-group">
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Tax calculation method</label>
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
              </div> */ ?>
            </div>
          </div>
          <?php /*<h2 class="hdg-reports">Item Details</h2> */ ?>
          <div class="table-responsive">
            <table class="table table-striped table-hover font12">
              <thead>
                <tr>
                  <th width="4%"  class="text-center">Sno.</th>
                  <th width="10%"  class="text-center">Barcode</th>                                    
                  <th width="18%" class="text-center">Item name</th>
                  <th width="12%" class="text-center">Lot No</th>
                  <th width="7%" class="text-center">Case/Box No.</th>                  
                  <th width="5%"  class="text-center">Available<br />qty.</th>
                  <th width="11%" class="text-center">Transfer<br />qty.</th>
                  <th width="8%"  class="text-center">M.R.P<br />( in Rs. )</th>
                  <th width="10%" class="text-center">GST<br />( in % )</th>
                  <th width="10%" class="text-center">Amount<br />( in Rs. )</th>
                </tr>
              </thead>
              <tr>
                <td colspan="5" class="labelStyle" style="font-size: 18px;text-align: right;">QTY. TOTALS</td>
                <td class="stAvaQty" style="text-align: right;font-weight:bold;font-size:16px;font-weight: bold; color: green;">&nbsp;</td>
                <td class="stTraQty" style="text-align: right;font-weight:bold;font-size:16px;font-weight: bold; color: green;">&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
              </tr>
              <tbody>
                <?php
                  for($i=1;$i<=12;$i++):
                    $bill_amount = $taxable_amount = $tax_amount = $item_total = 0;
                    $ex_index = $i-1;
                    if(isset($form_data['itemDetails'])) {
                      $item_name = $form_data['itemDetails']['itemName'][$ex_index];
                      $item_qty_available = $form_data['itemDetails']['itemAvailQty'][$ex_index];
                      $item_qty = $form_data['itemDetails']['itemSoldQty'][$ex_index];
                      $item_rate = $form_data['itemDetails']['itemRate'][$ex_index];
                      $tax_percent = $form_data['itemDetails']['itemTaxPercent'][$ex_index];
                      $item_discount = 0;
                    } else {
                      $item_name = '';
                      $item_qty_available = '';
                      $item_qty = 0;
                      $item_rate = 0;
                      $tax_percent = 0;
                      $item_discount = 0;
                    }

                    if($item_qty && $item_rate>0) {
                      $bill_amount = $item_qty * $item_rate;
                      $taxable_amount = $bill_amount - $item_discount;
                      $tax_amount = $tax_percent !== '' ? round($taxable_amount*$tax_percent/100,2):'';
                      $item_total = $taxable_amount + $tax_amount;
                    }
                ?>
                  <tr>
                    <td align="right" style="vertical-align:middle;"><?php echo $i ?></td>
                    <td align="left" style="vertical-align:middle;" title="Info: Remove the barcode to type Item name">
                      <input 
                        type="text" 
                        name="itemDetails[barcode][]" 
                        id="barcode_<?php echo $i-1 ?>" 
                        size="13"
                        class="transBarcode" 
                        index="<?php echo $i-1 ?>"
                        style="border:1px dashed #00AEFF;font-weight:bold;color:#AA3E39;"
                      />
                    </td>
                    <td style="vertical-align:middle;">
                      <input 
                        type="text" 
                        name="itemDetails[itemName][]" 
                        id="iname_<?php echo $i-1 ?>" 
                        size="20" 
                        class="inameAc saleItem noEnterKey" 
                        index="<?php echo $i-1 ?>" 
                        value="<?php echo $item_name ?>"
                        style="width:200px;"
                      />
                    </td>
                    <td style="vertical-align:middle;">
                      <div class="select-wrap">
                        <select 
                          class="form-control lotNo"
                          name="itemDetails[lotNo][]"
                          id="lotNo_<?php echo $i-1 ?>"
                          index="<?php echo $i-1 ?>"              
                        >
                          <option value="">Choose</option>
                        </select>
                      </div>
                     
                    </td>
                    <td id="cno_<?php echo $i-1 ?>" style="vertical-align:middle;" align="right"></td>
                    <td style="vertical-align:middle;">
                      <input
                        type="text"
                        class="qtyAvailable text-right noEnterKey"
                        id="qtyava_<?php echo $i-1 ?>"
                        name="itemDetails[itemAvailQty][]"
                        index="<?php echo $i-1 ?>"
                        value="<?php echo $item_qty_available ?>"
                        size="10"
                        readonly
                      />
                     
                    </td>
                    <td style="vertical-align:middle;" align="center">
                      <input
                        type="text"
                        id="qty_<?php echo $i-1 ?>"
                        name="itemDetails[itemSoldQty][]"
                        size="10"
                        value="<?php echo $item_qty ?>"
                        class="form-control saleItemQty"
                        index="<?php echo $i-1 ?>"                          
                      />
                    
                    </td>
                    <td style="vertical-align:middle;" align="center">
                      <input 
                        readonly
                        class = "mrp text-right noEnterKey"
                        id = "mrp_<?php echo $i-1 ?>"
                        index = "<?php echo $i-1 ?>"
                        size = "10"
                        value = "<?php echo $item_rate ?>"
                        name = "itemDetails[itemRate][]"
                      />
                      
                    </td>
                    <td
                      style="vertical-align:middle;"
                    >
                      <div class="select-wrap">
                        <select 
                          class="form-control saItemTax"
                          id="saItemTax_<?php echo $i-1 ?>" 
                          name="itemDetails[itemTaxPercent][]"
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
                            <option value='<?php echo number_format($value,2) ?>' <?php echo $selected ?>>
                              <?php echo number_format($value,2) ?>
                            </option>
                          <?php endforeach; ?>                            
                        </select>
                      </div>
                     
                      <input type="hidden" class="taxAmount" id="taxAmount_<?php echo $i-1 ?>" />
                      <input type="hidden" class="itemType" id="itemType_<?php echo $i-1 ?>" />                      
                    </td>
                    <td 
                      class="grossAmount" 
                      id="grossAmount_<?php echo $i-1 ?>" 
                      index="<?php echo $i-1 ?>"
                      style="vertical-align:middle;text-align:right;"
                    >
                    </td>                    
                  </tr>
                  <?php 
                    /* Show error tr if there are any errors in the line item */
                    if( isset($errors['itemDetails']['itemName'][$i-1]) ) {
                  ?>
                      <tr>
                        <td style="border:none;">&nbsp;</td>
                        <td style="border:none;text-align:center;"><?php echo isset($errors['itemDetails']['itemName'][$i-1]) ? '<span class="error">Invalid</span>': '' ?></td>
                        <td style="border:none;text-align:center;"><?php echo isset($errors['itemDetails']['itemAvailQty'][$i-1]) ? '<span class="error">Invalid</span>': '' ?></td>
                        <td style="border:none;text-align:center;"><?php echo isset($errors['itemDetails']['itemSoldQty'][$i-1]) ? '<span class="error">Invalid</span>': '' ?></td>
                        <td style="border:none;text-align:center;"><?php echo isset($errors['itemDetails']['itemRate'][$i-1]) ? '<span class="error">Invalid</span>': '' ?></td>
                        <td style="border:none;">&nbsp;</td>
                        <td style="border:none;text-align:center;"><?php echo isset($errors['itemDetails']['itemDiscount'][$i-1]) ? '<span class="error">Invalid</span>': '' ?></td>
                        <td style="border:none;">&nbsp;</td>
                        <td style="border:none;text-align:center;"><?php echo isset($errors['itemDetails']['itemTaxPercent'][$i-1]) ? '<span class="error">Invalid</span>': '' ?></td>
                      </tr>
                  <?php } ?>
                <?php endfor; ?>
                  <tr>
                    <td colspan="5" class="labelStyle" style="font-size: 18px;text-align: right;">QTY. TOTALS</td>
                    <td class="stAvaQty" style="text-align: right;font-weight:bold;font-size:16px;font-weight: bold; color: green;">&nbsp;</td>
                    <td class="stTraQty" style="text-align: right;font-weight:bold;font-size:16px;font-weight: bold; color: green;">&nbsp;</td>
                    <td colspan="2" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;">Gross Amount</td>
                    <td id="grossAmount" class="" style="font-size:16px;text-align:right;font-weight:bold;"></td>
                  </tr>
                  <tr>
                    <td colspan="9" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;">(+) GST</td>
                    <td id="gstAmount" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;" class="gstAmount"></td>
                  </tr>
                  <tr>
                    <td colspan="9" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;">(+/-) Round off</td>
                    <td id="roundOff" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;" class="roundOff"></td>
                  </tr>
                  <tr>
                    <td colspan="9" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;">Net Pay</td>
                    <td id="netPayBottom" class="netPay" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;"></td>
                  </tr>       
              </tbody>
            </table>
          </div>
          <div class="text-center">
            <button class="btn btn-primary" name="op" value="Save" id="transferSubmitBtn">
              <i class="fa fa-save"></i> <?php echo $btn_label ?>
            </button>
            <button class="btn btn-danger cancelButton" id="stransfer">
              <i class="fa fa-times"></i> Cancel
            </button>            
          </div>
        </form>  
      </div>
    </section>
  </div>
</div>

<div class="modal fade" id="modalStockTransfer" tabindex="-1" role="dialog" aria-labelledby="dualLotModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" align="center">
        <h5 class="modal-title" id="dualLotNosTitle" style="font-size: 18px; font-weight: bold; color: #225992;"></h5>
      </div>
      <p style="margin: 0;text-align: center;color: red;font-weight: bold;font-size: 16px;">Multiple entries found. Select Lot No. to continue</p>
      <div class="modal-body" id="modalStockTransferLotNos" style="padding:0px;"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="modalStockTransferCancel">Cancel</button>
        <button type="button" class="btn btn-primary" id="modalStockTransferSelect">Select</button>
      </div>
    </div>
  </div>
</div>


<?php /*
                  <tr>
                    <td colspan="9" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;">Gross Amount</td>
                    <td id="grossAmount" class="" style="font-size:16px;text-align:right;font-weight:bold;"></td>
                  </tr>
                  <tr>
                    <td colspan="9" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;">(-) Discount</td>
                    <td style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;" id="totDiscount"></td>
                  </tr>
                  <tr>
                    <td colspan="9" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;">Taxable Amount</td>
                    <td id="taxableAmount" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;" class="taxableAmount"></td>
                  </tr>
                  <tr>
                    <td colspan="9" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;">(+) GST</td>
                    <td id="gstAmount" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;" class="gstAmount"></td>
                  </tr>
                  <tr>
                    <td colspan="9" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;">(+/-) Round off</td>
                    <td id="roundOff" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;" class="roundOff"></td>
                  </tr>
                  <tr>
                    <td colspan="9" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;">Net Pay</td>
                    <td id="netPayBottom" class="netPay" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;"></td>
                  </tr>

            <?php /*
            <button class="btn btn-primary" id="SaveandPrint" name="op" value="SaveandPrint">
              <i class="fa fa-print"></i> Save &amp; Print
            </button>
            <button class="btn btn-danger btn-sm" id="SaveandPrintBill" name="op" value="SaveandPrintBill">
              <i class="fa fa-files-o"></i> Save &amp; Bill Print
            </button>
          <?php 
            if( isset($offers_raw) && count($offers_raw) > 0 ):
              foreach($offers_raw as $offer_details):
                $offer_props = [];
                if( (int)$offer_details['promoType'] === 1) {
                  $offer_props['total'] = $offer_details['totalQty'];
                  $offer_props['free'] = $offer_details['freeQty'];
                } elseif( (int)$offer_details['promoType'] === 2) {
                  $offer_props['bv'] = $offer_details['billValue'];
                  $offer_props['dp'] = $offer_details['discountPercent'];
                } elseif( (int)$offer_details['promoType'] === 0) {
                }
          ?>
              <input 
                type="hidden" 
                id="<?php echo $offer_details['promoCode'] ?>" 
                name="<?php echo $offer_details['promoCode'] ?>"
                <?php echo http_build_query($offer_props, '', ' ') ?>
              />

          <?php 
              endforeach;
            endif; 
          ?>
                      <div class="select-wrap">
                        <select 
                          class="form-control saleItemQty"
                          name="itemDetails[itemSoldQty][]"
                          id="qty_<?php echo $i-1 ?>"
                          index="<?php echo $i-1 ?>"             
                        >
                          <?php 
                            foreach($qtys_a as $key=>$value):
                               if((int)$item_qty === (int)$key) {
                                $selected = 'selected="selected"';
                               } else {
                                $selected = '';
                               }                                 
                          ?>
                            <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                      */ ?>
