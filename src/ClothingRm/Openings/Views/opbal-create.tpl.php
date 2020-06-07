<?php

  use Atawa\Utilities;

  $item_name_dis = $update_mode ? ' disabled="disabled"' : '';

  // dump($submitted_data);
  // exit;

  $item_name = isset($submitted_data['itemName']) && $submitted_data['itemName'] !== '' ? $submitted_data['itemName'] : '';
  $opening_rate = isset($submitted_data['opRate']) && $submitted_data['opRate'] !== '' ? $submitted_data['opRate'] : ''; 
  $opening_qty = isset($submitted_data['opQty']) && $submitted_data['opQty'] !== '' ? $submitted_data['opQty'] : '';
  $purchase_rate = isset($submitted_data['purchaseRate']) && $submitted_data['purchaseRate'] !== '' ? $submitted_data['purchaseRate'] : '';
  $tax_percent = isset($submitted_data['taxPercent']) && $submitted_data['taxPercent'] !== '' ? $submitted_data['taxPercent'] : '';
  $location_code = isset($submitted_data['locationID']) && isset($location_codes[$submitted_data['locationID']]) ? $location_codes[$submitted_data['locationID']] : '';
  $packed_qty = isset($submitted_data['packedQty']) ? $submitted_data['packedQty'] : 1;
  $cno = isset($submitted_data['cno']) ? $submitted_data['cno'] : '';
  $lot_no = isset($submitted_data['lotNo']) ? $submitted_data['lotNo'] : '';
  $item_sku = isset($submitted_data['itemSku']) ? $submitted_data['itemSku'] : '';
  $item_style_code = isset($submitted_data['itemStylecode']) ? $submitted_data['itemStylecode'] : '';
  $item_size = isset($submitted_data['itemSize']) ? $submitted_data['itemSize'] : '';
  $item_color = isset($submitted_data['itemColor']) ? $submitted_data['itemColor'] : '';
  $item_sleeve = isset($submitted_data['itemSleeve']) ? $submitted_data['itemSleeve'] : '';
  $batch_no = isset($submitted_data['batchNo']) ? $submitted_data['batchNo'] : '';
  $expiry_date = isset($submitted_data['expiryDate']) ? date('d-m-Y', strtotime($submitted_data['expiryDate'])) : '';
  $wholesale_price = isset($submitted_data['wholesalePrice']) ? $submitted_data['wholesalePrice'] : '';
  $online_price = isset($submitted_data['onlinePrice']) ? $submitted_data['onlinePrice'] : '';
  $barcode = isset($submitted_data['barcode']) ? $submitted_data['barcode'] : '';

  if($location_code === '') {
    $location_code = isset($submitted_data['locationCode']) ? $submitted_data['locationCode'] : '';
  }
  $current_date = isset($submitted_data['expiryDate']) && $submitted_data['expiryDate']!=='' ? date("d-m-Y", strtotime($submitted_data['expiryDate'])) : '';
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panel">
      <div class="panel-body">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/opbal/list" class="btn btn-default">
              <i class="fa fa-book"></i> Opening Balances List
            </a>
          </div>
        </div>
        <form class="form-validate form-horizontal" method="POST">
          <div class="form-group">
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot15">
              <label class="control-label labelStyle">Store / Location name*</label>
              <div class="select-wrap">
                <select class="form-control" name="locationCode" id="locationCode">
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
              </div>
              <?php if(isset($form_errors['locationCode'])): ?>
                <span class="error"><?php echo $form_errors['locationCode'] ?></span>
              <?php endif; ?>
            </div>
            <?php if($update_mode): ?>
              <div class="col-sm-12 col-md-3 col-lg-3 m-bot15">
                <label class="control-label labelStyle">Lot No. (auto generated)</label>
                <input type="text" class="form-control noEnterKey" name="lotNo" id="lotNo" value="<?php echo $lot_no ?>" disabled="disabled" />
              </div>
            <?php endif; ?>            
          </div>          
          <div class="form-group">
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Item name*</label>
              <input type="text" class="form-control inameAc noEnterKey" name="itemName" id="itemName" value="<?php echo $item_name ?>" <?php echo $item_name_dis ?>>
              <?php if(isset($errors['itemName'])): ?>
                <span class="error"><?php echo $errors['itemName'] ?></span>
              <?php endif; ?>           
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Opening qty.*</label>
              <input type="text" class="form-control noEnterKey" name="opQty" id="opQty" value="<?php echo $opening_qty ?>" />
              <?php if(isset($errors['opQty'])): ?>
                <span class="error"><?php echo $errors['opQty'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Packed qty. / unit*</label>
              <input type="text" class="form-control noEnterKey" name="packedQty" id="packedQty" value="<?php echo $packed_qty ?>" />
              <?php if(isset($errors['packed_qty'])): ?>
                <span class="error"><?php echo $errors['packed_qty'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Tax percent*</label>
              <div class="select-wrap">
                <select class="form-control" name="taxPercent" id="taxPercent">
                  <?php 
                    foreach($vat_percents as $key=>$value):
                      if((int)$tax_percent === (int)$value) {
                        $selected = 'selected="selected"';
                      } else {
                        $selected = '';
                      }                      
                  ?>
                    <option value="<?php echo $value ?>" <?php echo $selected ?>><?php echo $value ?></option>
                  <?php endforeach; ?>
                </select>
                <?php if(isset($errors['taxPercent'])): ?>
                  <span class="error"><?php echo $errors['taxPercent'] ?></span>
                <?php endif; ?>
              </div>             
            </div>            
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Container/Box/Case No.</label>
              <input type="text" class="form-control noEnterKey" name="cno" id="cno" value="<?php echo $cno ?>" />
              <?php if(isset($errors['cno'])): ?>
                <span class="error"><?php echo $errors['cno'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Expiry date (dd-mm-yyyy)</label>
              <div class="form-group">
                <div class="col-lg-12">
                  <div class="input-append date" data-date="<?php echo $current_date ?>" data-date-format="dd-mm-yyyy">
                    <input class="span2" value="<?php echo $current_date ?>" size="16" type="text" readonly name="expiryDate" id="expiryDate" />
                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                  </div>
                  <?php if(isset($errors['expiryDate'])): ?>
                    <span class="error"><?php echo $errors['expiryDate'] ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </div>            
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Item SKU</label>
              <input type="text" class="form-control noEnterKey" name="itemSku" id="itemSku" value="<?php echo $item_sku ?>" />
              <?php if(isset($errors['itemSku'])): ?>
                <span class="error"><?php echo $errors['itemSku'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Item stylecode</label>
              <input type="text" class="form-control noEnterKey" name="itemStylecode" id="itemStylecode" value="<?php echo $item_style_code ?>" />
              <?php if(isset($errors['itemStylecode'])): ?>
                <span class="error"><?php echo $errors['itemStylecode'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Item size</label>
              <input type="text" class="form-control noEnterKey" name="itemSize" id="itemSize" value="<?php echo $item_size ?>" />
              <?php if(isset($errors['itemSize'])): ?>
                <span class="error"><?php echo $errors['itemSize'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Item color</label>
              <input type="text" class="form-control noEnterKey" name="itemColor" id="itemColor" value="<?php echo $item_color ?>" />
              <?php if(isset($errors['itemColor'])): ?>
                <span class="error"><?php echo $errors['itemColor'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Item sleeve</label>
              <input type="text" class="form-control noEnterKey" name="itemSleeve" id="itemSleeve" value="<?php echo $item_sleeve ?>" />
              <?php if(isset($errors['itemSleeve'])): ?>
                <span class="error"><?php echo $errors['itemSleeve'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Batch no.</label>
              <input type="text" class="form-control noEnterKey" name="batchNo" id="batchNo" value="<?php echo $batch_no ?>" />
              <?php if(isset($errors['batchNo'])): ?>
                <span class="error"><?php echo $errors['batchNo'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot15">
              <label class="control-label labelStyle">Barcode</label>
              <input type="text" class="form-control noEnterKey" name="barcode" id="barcode" value="<?php echo $barcode ?>" />
              <?php if(isset($errors['barcode'])): ?>
                <span class="error"><?php echo $errors['barcode'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot15">
              <label class="control-label labelStyle">Purchase rate (in Rs.)*</label>
              <input type="text" class="form-control noEnterKey" name="purchaseRate" id="purchaseRate" value="<?php echo $purchase_rate ?>" />
              <?php if(isset($errors['purchaseRate'])): ?>
                <span class="error"><?php echo $errors['purchaseRate'] ?></span>
              <?php endif; ?>
            </div>            
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot15">
              <label class="control-label labelStyle">M.R.P (in Rs.)*</label>
              <input type="text" class="form-control noEnterKey" name="opRate" id="opRate" value="<?php echo $opening_rate ?>" />
              <?php if(isset($errors['openingRate'])): ?>
                <span class="error"><?php echo $errors['openingRate'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot15">
              <label class="control-label labelStyle">Wholesale price (in Rs.)</label>
              <input type="text" class="form-control noEnterKey" name="wholesalePrice" id="wholesalePrice" value="<?php echo $wholesale_price ?>" />
              <?php if(isset($errors['wholesalePrice'])): ?>
                <span class="error"><?php echo $errors['wholesalePrice'] ?></span>
              <?php endif; ?>
            </div>            
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot15">
              <label class="control-label labelStyle">Online price (in Rs.)</label>
              <input type="text" class="form-control noEnterKey" name="onlinePrice" id="onlinePrice" value="<?php echo $online_price ?>" />
              <?php if(isset($errors['onlinePrice'])): ?>
                <span class="error"><?php echo $errors['onlinePrice'] ?></span>
              <?php endif; ?>
            </div>
          </div>          
          <div class="text-center">
            <button class="btn btn-primary" id="Save">
              <i class="fa fa-save"></i> <?php echo $btn_label ?>
            </button>
          </div>
        </form>  
      </div>
    </section>
  </div>
</div>