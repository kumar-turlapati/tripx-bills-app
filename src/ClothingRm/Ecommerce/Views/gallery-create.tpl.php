<?php
  use Atawa\Utilities;
  use Atawa\Constants;
  
  if(isset($search_params['itemName']) && $search_params['itemName'] !== '' ) {
    $item_name = $search_params['itemName'];
  } else {
    $item_name = '';
  }
  if(isset($search_params['itemDescription']) && $search_params['itemDescription'] !== '' ) {
    $item_description = $search_params['itemDescription'];
  } else {
    $item_description = '';
  }
  if(isset($search_params['lotNo']) && $search_params['lotNo'] !== '' ) {
    $lot_no = $search_params['lotNo'];
  } else {
    $lot_no = '';
  }
  if(isset($search_params['itemSku']) && $search_params['itemSku'] !== '' ) {
    $item_sku = $search_params['itemSku'];
  } else {
    $item_sku = '';
  }
  if(isset($search_params['itemStylecode']) && $search_params['itemStylecode'] !== '' ) {
    $item_style_code = $search_params['itemStylecode'];
  } else {
    $item_style_code = '';
  }
  if(isset($search_params['itemColor']) && $search_params['itemColor'] !== '' ) {
    $item_color = $search_params['itemColor'];
  } else {
    $item_color = '';
  }
  if(isset($form_data['billingRate']) && $form_data['billingRate'] !== '' ) {
    $billing_rate = $form_data['billingRate'];
  } else {
    $billing_rate = 'mrp';
  }
  if(isset($form_data['itemRate']) && $form_data['itemRate'] !== '' ) {
    $item_rate = $form_data['itemRate'];
  } else {
    $item_rate = '';
  }
  if(isset($search_params['itemSleeve']) && $search_params['itemSleeve'] !== '' ) {
    $item_sleeve = $search_params['itemSleeve'];
  } else {
    $item_sleeve = '';
  }
  if(isset($submitted_data['locationCode']) && $submitted_data['locationCode'] !== '') {
    $location_code = $submitted_data['locationCode'];
  } else {
    $location_code = $default_location;
  }
  $billing_rate = isset($form_data['billingRate']) ? $form_data['billingRate'] : 'mrp';

  $input_type_a = ['barcode' => 'Barcode', 'item' => 'Item Name'];
  $entry_mode = 'item';

  // dump($form_errors);
?>
<div class="row">
  <div class="col-lg-12">
    <section class="panelBox">
      <div class="panelBody">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/galleries/list" class="btn btn-default">
              <i class="fa fa-file-image-o"></i> Product Galleries
            </a>
          </div>
        </div>
        <form id="galleryForm" method="POST" autocomplete="off" enctype="multipart/form-data">
          <div class="form-group">
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot10" style="padding-left:0px;">
              <label class="control-label labelStyle">Store name</label>
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
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot10">
              <label class="control-label labelStyle">Entry mode</label>
              <div class="select-wrap">
                <select class="form-control" id="inputType">
                  <?php 
                    foreach($input_type_a as $key=>$value): 
                      if($key === $entry_mode) {
                        $selected = 'selected="selected"';
                      } else {
                        $selected = '';
                      }
                  ?>
                    <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot10" id="barcodeInput" style="display: <?php echo $entry_mode === 'barcode' ? 'block' : 'none'; ?>">
              <label class="control-label labelStyle">Barcode</label>
              <input
                type="text"
                class="form-control"
                id="imgBarcode"
                maxlength="13"
                style="font-size:16px;font-weight:bold;border:1px dashed #225992;padding-left:5px;font-weight:bold;"
              /> 
            </div>
            <div style="clear:both;"></div>          
          </div>
          <h4 class="labelStyleOnlyColor">Item details</h4>
          <div class="form-group">
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot10">
              <label class="control-label labelStyle">Item name*</label>
              <input
                  type="text" 
                  class="form-control inameAc" 
                  name="itemName" 
                  id="itemName"
                  value="<?php echo $item_name ?>"
                  maxlength="50"
                >            
              <?php if(isset($form_errors['itemName'])): ?>
                <span class="error"><?php echo $form_errors['itemName'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot10">
              <label class="control-label labelStyle">Item stylecode*</label>
              <input 
                type="text" 
                class="form-control" 
                name="itemStylecode" 
                id="itemStylecode" 
                value="<?php echo $item_style_code ?>"
                maxlength="25"
              >
              <?php if(isset($form_errors['itemStylecode'])): ?>
                <span class="error"><?php echo $form_errors['itemStylecode'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot20">
              <label class="control-label labelStyle">Item color</label>
              <input 
                type="text" 
                class="form-control" 
                name="itemColor" 
                id="itemColor" 
                value="<?php echo $item_color ?>"
                maxlength="50"
              >
              <?php if(isset($form_errors['itemColor'])): ?>
                <span class="error"><?php echo $form_errors['itemColor'] ?></span>
              <?php endif; ?>   
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot20">
              <label class="control-label labelStyle">Billing type</label>
              <div class="select-wrap">
                <select class="form-control" name="billingRate" id="billingRate">
                  <?php 
                    foreach($billing_rates as $key=>$value):
                      if($key === $billing_rate) {
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



            <?php /*
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot20">
              <label class="control-label labelStyle">Item sleeve</label>
              <input 
                type="text" 
                class="form-control" 
                name="itemSleeve" 
                id="itemSleeve" 
                value="<?php echo $item_sleeve ?>"
                maxlength="10"
              >
              <?php if(isset($form_errors['itemSleeve'])): ?>
                <span class="error"><?php echo $form_errors['itemSleeve'] ?></span>
              <?php endif; ?>
            </div>*/ ?>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-12 col-lg-12 m-bot20">
              <label class="control-label labelStyle">Item description* (max 200 chars.)</label>
              <input 
                type="text" 
                class="form-control" 
                name="itemDescription" 
                id="itemDescription" 
                value="<?php echo $item_description ?>"
                maxlength="200"
              >
              <?php if(isset($form_errors['itemDescription'])): ?>
                <span class="error"><?php echo $form_errors['itemDescription'] ?></span>
              <?php endif; ?>
            </div>
            <div style="clear:both;"></div>          
          </div>
          <h4 class="labelStyleOnlyColor">Item images</h4>
          <?php if(count($form_errors) > 0): ?>
            <div align="center">
              <span class="error"><i class="fa fa-times" aria-hidden="true"></i>You have errors in the form.</span>
            </div>
          <?php endif; ?>
          <div class="form-group">
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Upload image - 1</label>
              <input 
                type="file" 
                class="form-control" 
                name="image_0"
                id="image0"
              >
              <?php if(isset($form_errors['image_0'])): ?>
                <span class="error"><?php echo $form_errors['image_0'] ?></span>
              <?php endif; ?>   
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Upload image - 2</label>
              <input 
                type="file" 
                class="form-control" 
                name="image_1"
                id="image1"
              >
              <?php if(isset($form_errors['image_1'])): ?>
                <span class="error"><?php echo $form_errors['image_1'] ?></span>
              <?php endif; ?>   
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Upload image - 3</label>
              <input 
                type="file" 
                class="form-control" 
                name="image_2"
                id="image2"
              >
              <?php if(isset($form_errors['image_2'])): ?>
                <span class="error"><?php echo $form_errors['image_2'] ?></span>
              <?php endif; ?>   
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Upload image - 4</label>
              <input 
                type="file" 
                class="form-control" 
                name="image_3"
                id="image3"
              >
              <?php if(isset($form_errors['image_3'])): ?>
                <span class="error"><?php echo $form_errors['image_3'] ?></span>
              <?php endif; ?>
            </div>
            <div style="clear:both;"></div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot20">
              <label class="control-label labelStyle">Upload image - 5</label>
              <input 
                type="file" 
                class="form-control" 
                name="image_4"
                id="image4"
              >
              <?php if(isset($form_errors['image_4'])): ?>
                <span class="error"><?php echo $form_errors['image_4'] ?></span>
              <?php endif; ?>   
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot20">
              <label class="control-label labelStyle">Upload image - 6</label>
              <input 
                type="file" 
                class="form-control" 
                name="image_5"
                id="image5"
              >
              <?php if(isset($form_errors['image_5'])): ?>
                <span class="error"><?php echo $form_errors['image_5'] ?></span>
              <?php endif; ?>   
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot20">
              <label class="control-label labelStyle">Upload image - 7</label>
              <input 
                type="file" 
                class="form-control" 
                name="image_6"
                id="image6"
              >
              <?php if(isset($form_errors['image_6'])): ?>
                <span class="error"><?php echo $form_errors['image_6'] ?></span>
              <?php endif; ?>   
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot20">
              <label class="control-label labelStyle">Upload image - 8</label>
              <input 
                type="file" 
                class="form-control" 
                name="image_7"
                id="image7"
              >
              <?php if(isset($form_errors['image_7'])): ?>
                <span class="error"><?php echo $form_errors['image_7'] ?></span>
              <?php endif; ?>
            </div>
            <div style="clear:both;"></div>          
          </div>
          <div class="text-center">
            <button class="btn btn-success cancelOp" id="imgUpload">
              <i class="fa fa-plus"></i> Create Gallery
            </button>
            <button class="btn btn-danger cancelButton" id="imgUploadCancel">
              <i class="fa fa-times"></i> Reset
            </button>
          </div>
        </form>
      </div>
    </section>
  </div>
</div>


<?php /*
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot10">
              <label class="control-label labelStyle">Lot no.</label>
              <input 
                type="text" 
                class="form-control" 
                name="lotNo" 
                id="lotNo" 
                value="<?php echo $lot_no ?>"
              >
              <?php if(isset($form_errors['lotNo'])): ?>
                <span class="error"><?php echo $form_errors['lotNo'] ?></span>
              <?php endif; ?>   
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot10">
              <label class="control-label labelStyle">Item SKU</label>
              <input 
                type="text" 
                class="form-control" 
                name="itemSku" 
                id="itemSku" 
                value="<?php echo $item_sku ?>"
                maxlength="25"
              >
              <?php if(isset($form_errors['itemSku'])): ?>
                <span class="error"><?php echo $form_errors['itemSku'] ?></span>
              <?php endif; ?>   
            </div>*/ ?>