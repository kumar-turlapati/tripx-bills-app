<?php 

  if(isset($submitted_data['itemStatus'])) {
    $sel_status = (int)$submitted_data['itemStatus'];
  } else {
    $sel_status = 1;
  }
  if(isset($submitted_data['mrp'])) {
    $sel_mrp = $submitted_data['mrp'];
  } else {
    $sel_mrp = '';
  }
  if(isset($submitted_data['catCode'])) {
    $sel_cat = $submitted_data['catCode'];
  } else {
    $sel_cat = '';
  }
  if(isset($submitted_data['itemType'])) {
    $item_type = $submitted_data['itemType'];
  } else {
    $item_type = 'i';
  }
  if(isset($submitted_data['taxPercent'])) {
    $tax_rate = $submitted_data['taxPercent'];
  } else {
    $tax_rate = '';
  }
  if(isset($submitted_data['hsnSacCode'])) {
    $hsn_sac_code = $submitted_data['hsnSacCode'];
  } else {
    $hsn_sac_code = '';
  }
  if(isset($submitted_data['itemSku'])) {
    $item_sku = $submitted_data['itemSku'];
  } else {
    $item_sku = '';
  }
  if(isset($submitted_data['rackNo']) && $submitted_data['rackNo'] !== '') {
    $rack_no = $submitted_data['rackNo'];
  } else {
    $rack_no = '';
  }
  if(isset($submitted_data['mfgName']) && $submitted_data['mfgName'] !== '') {
    $brand_code = $submitted_data['mfgName'];
  } else {
    $brand_code = '';
  }
  if(isset($submitted_data['locationID']) && (int)$submitted_data['locationID'] > 0) {
    $location_code = $location_codes[$submitted_data['locationID']];
  } else {
    $location_code = '';
  }

  // dump($submitted_data, $location_ids, $location_codes);
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panel">
      <div class="panel-body">
        <?php echo $flash_obj->print_flash_message(); ?>
        <?php if($page_error !== ''): ?>
          <div class="alert alert-danger" role="alert">
            <strong>Error!</strong> <?php echo $page_error ?> 
          </div>
        <?php elseif($page_success !== ''): ?>
          <div class="alert alert-success" role="alert">
            <strong>Success!</strong> <?php echo $page_success ?> 
          </div>
        <?php endif; ?>        
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/products/list" class="btn btn-default">
              <i class="fa fa-book"></i> Products (or) Services List
            </a>
          </div>
        </div>
        <form class="form-validate form-horizontal" method="POST" autocomplete="off">
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Choose Product or Service</label>
              <div class="select-wrap">
                <select class="form-control" name="itemType" id="itemType">
                  <?php 
                    foreach($item_types_a as $key=>$value):
                      if($item_type === $key) {
                        $selected = 'selected="selected"';
                      } else {
                        $selected = '';
                      }                       
                  ?>
                    <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                  <?php endforeach; ?>
                </select>
                <?php if(isset($errors['itemType'])): ?>
                  <span class="error"><?php echo $errors['itemType'] ?></span>
                <?php endif; ?>
              </div>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Product / Service name</label>
              <input 
                type="text" 
                class="form-control noEnterKey" 
                name="itemName" 
                id="itemName" 
                value="<?php echo (isset($submitted_data['itemName'])?$submitted_data['itemName']:'') ?>"
              >
              <?php if(isset($errors['itemName'])): ?>
                <span class="error"><?php echo $errors['itemName'] ?></span>
              <?php endif; ?>              
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Category name</label>
              <div class="select-wrap">
                <select class="form-control" name="categoryID" id="categoryID">
                  <?php 
                    foreach($categories as $key=>$value): 
                      if($sel_cat == $key) {
                        $selected = 'selected="selected"';
                      } else {
                        $selected = '';
                      }                       
                  ?>
                    <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                  <?php endforeach; ?>
                </select>
                <?php if(isset($errors['categoryID'])): ?>
                  <span class="error"><?php echo $errors['categoryID'] ?></span>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">HSN / SAC code</label>
              <input 
                type="text" 
                class="form-control noEnterKey" 
                name="hsnSacCode" 
                id="hsnSacCode" 
                value="<?php echo $hsn_sac_code ?>"
                maxlength=8
              >
              <?php if(isset($errors['hsnSacCode'])): ?>
                <span class="error"><?php echo $errors['hsnSacCode'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">SKU</label>
              <input
                type="text" class="form-control noEnterKey" name="itemSku" id="itemSku" 
                value="<?php echo $item_sku ?>"
              >
              <?php if(isset($errors['itemSku'])): ?>
                <span class="error"><?php echo $errors['itemSku'] ?></span>
              <?php endif; ?>              
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Tax rate</label>
              <div class="select-wrap">
                <select class="form-control" name="taxPercent" id="taxPercent">
                  <?php 
                    foreach($tax_rates_a as $key=>$value):
                      if($tax_rate == $value) {
                        $selected = 'selected="selected"';
                      } else {
                        $selected = '';
                      }                       
                  ?>
                    <option value="<?php echo $value ?>" <?php echo $selected ?>><?php echo $value ?></option>
                  <?php endforeach; ?>
                </select>
                <?php if(isset($errors['taxRate'])): ?>
                  <span class="error"><?php echo $errors['taxRate'] ?></span>
                <?php endif; ?>
              </div>              
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Status</label>
              <div class="select-wrap">
                <select class="form-control" name="itemStatus" id="itemStatus">
                  <?php 
                    foreach($status as $key=>$value): 
                      if($sel_status === $key) {
                        $selected = 'selected="selected"';
                      } else {
                        $selected = '';
                      }                       
                  ?>
                    <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <?php if(isset($errors['status'])): ?>
                <span class="error"><?php echo $errors['status'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Brand name</label>
              <input 
                type="text" 
                class="form-control noEnterKey brandAc"
                name="brandCode"
                id="brandCode"
                value="<?php echo $brand_code ?>"
              >
              <?php if(isset($errors['brandCode'])): ?>
                <span class="error"><?php echo $errors['brandCode'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Rack no.</label>
              <input
                type="text" 
                class="form-control noEnterKey"
                name="rackNo"
                id="rackNo"
                value="<?php echo $rack_no ?>"
              >
              <?php if(isset($errors['rack_no'])): ?>
                <span class="error"><?php echo $errors['rack_no'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Store name</label>
              <?php if($update_flag === false): ?>
                <div class="select-wrap">
                  <select class="form-control" name="locationCode" id="locationCode">
                    <?php 
                      foreach($client_locations as $key=>$value): 
                        $location_key_a = explode('`', $key);
                        if($location_code === $location_key_a[0]) {
                          $selected = 'selected="selected"';
                        } else {
                          $selected = '';
                        }
                    ?>
                      <option value="<?php echo $location_key_a[0] ?>" <?php echo $selected ?>><?php echo $value ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <?php if(isset($form_errors['locationCode'])): ?>
                  <span class="error"><?php echo $form_errors['locationCode'] ?></span>
                <?php endif; ?>
              <?php else: ?>
                <p style="font-size:16px;font-weight:bold;color:#225992;"><?php echo isset($location_ids[$submitted_data['locationID']]) ? $location_ids[$submitted_data['locationID']] : 'Invalid Store'?> <span style="color:red;font-size:11px;">[ Not editable ]</span></p>
                <input type="hidden" id="locationCode" name="locationCode" value="<?php echo $product_location ?>" />
              <?php endif; ?>
            </div>
          </div>
          <div class="text-center">
            <button class="btn btn-success" id="Save">
              <i class="fa fa-save"></i> <?php echo $btn_label ?>
            </button>
          </div>
        </form>  
      </div>
    </section>
  </div>
</div>