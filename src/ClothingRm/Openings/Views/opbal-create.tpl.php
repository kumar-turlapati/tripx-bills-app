<?php
  use Atawa\Utilities;

  // dump($submitted_data);

  $batch_no_auto = date("dMy").'_'.Utilities::generate_unique_string(12);

  if(isset($submitted_data['itemName']) && $submitted_data['itemName'] !== '') {
    $item_name = $submitted_data['itemName'];
    $item_name_dis = 'disabled';
  } else {
    $item_name = $item_name_dis = '';
  }

  if(isset($submitted_data['openingRate']) && $submitted_data['openingRate'] !== '') {
    $opening_rate = $submitted_data['openingRate'];
  } else {
    $opening_rate = '';
  }

  if(isset($submitted_data['openingQty']) && $submitted_data['openingQty'] !== '') {
    $opening_qty = $submitted_data['openingQty'];
    $opening_qty_dis = 'disabled';
  } else {
    $opening_qty = $opening_qty_dis = '';
  }
  /*
  if(isset($submitted_data['expiryDateMonth']) && $submitted_data['expiryDateMonth'] !== '') {
    $expiry_date_mon = $submitted_data['expiryDateMonth'];
  } else {
    $expiry_date_mon = '';
  }

  if(isset($submitted_data['expiryDateYear']) && $submitted_data['expiryDateYear'] !== '') {
    $expiry_date_year = $submitted_data['expiryDateYear'];
  } else {
    $expiry_date_year = '';
  }

  if(isset($submitted_data['batchNo']) && $submitted_data['batchNo'] !== '') {
    $batch_no = $submitted_data['batchNo'];
    $batch_no_dis = 'disabled';    
  } else {
    $batch_no = '';
    $batch_no_dis = '';
  }*/

  if(isset($submitted_data['mrp']) && $submitted_data['mrp'] !== '') {
    $mrp = $submitted_data['mrp'];
  } else {
    $mrp = '';
  }

  if(isset($submitted_data['taxPercent']) && $submitted_data['taxPercent'] !== '') {
    $tax_percent = (int)$submitted_data['taxPercent'];
  } else {
    $tax_percent = '';
  }

  // dump($errors);
?>

<!-- Basic form starts -->
<div class="row">
  <div class="col-lg-12"> 
    
    <!-- Panel starts -->
    <section class="panel">
      <div class="panel-body">

        <?php echo Utilities::print_flash_message() ?>

        <!-- Right links starts -->
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/opbal/list" class="btn btn-default">
              <i class="fa fa-book"></i> Opening Balance List
            </a>
          </div>
        </div>
        <!-- Right links ends --> 
        
        <!-- Form starts -->
        <form class="form-validate form-horizontal" method="POST">
          <h2 class="hdg-reports borderBottom">Item Details</h2>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Item name</label>
              <input type="text" class="form-control inameAc noEnterKey" name="itemName" id="itemName" value="<?php echo $item_name ?>" <?php echo $item_name_dis ?>>
              <?php if(isset($errors['itemName'])): ?>
                <span class="error"><?php echo $errors['itemName'] ?></span>
              <?php endif; ?>           
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Opening Qty.</label>
              <div class="select-wrap">
                <select class="form-control" name="opQty" id="opQty" <?php echo $opening_qty_dis ?>>
                  <?php 
                    foreach($qtys as $key=>$value):
                      if((int)$opening_qty === (int)$key) {
                        $selected = 'selected="selected"';
                      } else {
                        $selected = '';
                      }                      
                  ?>
                    <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                  <?php endforeach; ?>
                </select>
                <?php if(isset($errors['opQty'])): ?>
                  <span class="error"><?php echo $errors['opQty'] ?></span>
                <?php endif; ?>
              </div>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Opening Price</label>
                <input type="text" class="form-control" name="opRate" id="opRate" value="<?php echo $opening_rate ?>">
                <?php if(isset($errors['opRate'])): ?>
                  <span class="error"><?php echo $errors['opRate'] ?></span>
                <?php endif; ?>
            </div>                  
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Tax Percent</label>
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
          <div class="text-center">
            <button class="btn btn-primary" id="Save">
              <i class="fa fa-save"></i> <?php echo $btn_label ?>
            </button>
          </div>
            <input type="hidden" name="batchNo" id="batchNo" value="<?php echo $batch_no_auto ?>" />
            <input type="hidden" name="expMonth" id="expMonth" value="12" />
            <input type="hidden" name="expYear" id="expYear" value="2099" />            
        </form>  
      </div>
    </section>
    <!-- Panel ends --> 
  </div>
</div>
<!-- Basic Forms ends -->