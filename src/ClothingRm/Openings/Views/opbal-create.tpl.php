<?php
  use Atawa\Utilities;
  
  $item_name = isset($submitted_data['itemName']) && $submitted_data['itemName'] !== '' ? $submitted_data['itemName'] : '';
  $opening_rate = isset($submitted_data['openingRate']) && $submitted_data['openingRate'] !== '' ? $submitted_data['openingRate'] : ''; 
  $opening_qty = isset($submitted_data['openingQty']) && $submitted_data['openingQty'] !== '' ? $submitted_data['openingQty'] : '';
  $purchase_rate = isset($submitted_data['purchaseRate']) && $submitted_data['purchaseRate'] !== '' ? $submitted_data['purchaseRate'] : '';
  $tax_percent = isset($submitted_data['taxPercent']) && $submitted_data['taxPercent'] !== '' ? $submitted_data['taxPercent'] + 0 : '';
  $location_code = isset($submitted_data['locationID']) && isset($location_codes[$submitted_data['locationID']]) ? $location_codes[$submitted_data['locationID']] : '';
  $upp = isset($submitted_data['unitsPerPack']) ? $submitted_data['unitsPerPack'] : 1;
  $lot_no = isset($submitted_data['lotNo']) ? $submitted_data['lotNo'] : '';

  $item_name_dis = $update_mode ? ' disabled="disabled"' : '';
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panel">
      <div class="panel-body">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/opbal/list" class="btn btn-default">
              <i class="fa fa-book"></i> Opening Balance List
            </a>
          </div>
        </div>
        <form class="form-validate form-horizontal" method="POST">
          <h2 class="hdg-reports borderBottom">Item Details</h2>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Item name</label>
              <input type="text" class="form-control inameAc noEnterKey" name="itemName" id="itemName" value="<?php echo $item_name ?>" <?php echo $item_name_dis ?>>
              <?php if(isset($errors['itemName'])): ?>
                <span class="error"><?php echo $errors['itemName'] ?></span>
              <?php endif; ?>           
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Opening qty.</label>
              <input type="text" class="form-control noEnterKey" name="opQty" id="opQty" value="<?php echo $opening_qty ?>" />
              <?php if(isset($errors['opQty'])): ?>
                <span class="error"><?php echo $errors['opQty'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Opening rate (in Rs.)</label>
              <input type="text" class="form-control noEnterKey" name="opRate" id="opRate" value="<?php echo $opening_rate ?>">
              <?php if(isset($errors['opRate'])): ?>
                <span class="error"><?php echo $errors['opRate'] ?></span>
              <?php endif; ?>
            </div>                  
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Purchase rate (in Rs.)</label>
              <input type="text" class="form-control noEnterKey" name="purchaseRate" id="purchaseRate" value="<?php echo $purchase_rate ?>" />
              <?php if(isset($errors['purchaseRate'])): ?>
                <span class="error"><?php echo $errors['purchaseRate'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Tax percent</label>
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
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Store name</label>
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
          </div>
          <?php if($update_mode): ?>
            <div class="form-group">
              <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
                <label class="control-label">Units per pack</label>
                <input type="text" class="form-control noEnterKey" name="upp" id="upp" value="<?php echo $upp ?>" disabled="disabled" />
                <?php if(isset($errors['upp'])): ?>
                  <span class="error"><?php echo $errors['upp'] ?></span>
                <?php endif; ?>
              </div>
              <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
                <label class="control-label">Lot no. (auto)</label>
                <input type="text" class="form-control noEnterKey" name="lotNo" id="lotNo" value="<?php echo $lot_no ?>" disabled="disabled" />
              </div>
            </div>
          <?php endif; ?>
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