<?php
  
  // dump($location_ids, $location_codes);

  if(isset($submitted_data['itemType'])) {
    $item_type = $submitted_data['itemType'];
  } else {
    $item_type = 'i';
  }
  if(isset($submitted_data['locationID']) && (int)$submitted_data['locationID'] > 0) {
    $location_code = $location_codes[$submitted_data['locationID']];
  } elseif(isset($submitted_data['locationCode']) && $submitted_data['locationCode'] !== '') {
    $location_code = $submitted_data['locationCode'];
  } else {
    $location_code = '';
  }
  if(isset($submitted_data['itemName']) && $submitted_data['itemName'] !== '') {
    $item_name = $submitted_data['itemName'];
  } else {
    $item_name = '';
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
  if(isset($submitted_data['uom'])) {
    $uom = $submitted_data['uom'];
  } else {
    $uom = '';
  }
  if(isset($submitted_data['uomSub'])) {
    $uom_sub = $submitted_data['uomSub'];
  } else {
    $uom_sub = '';
  }  
  if(isset($submitted_data['itemStatus'])) {
    $sel_status = (int)$submitted_data['itemStatus'];
  } else {
    $sel_status = 1;
  }
  if(isset($submitted_data['categoryID']) && $submitted_data['categoryID'] !== '') {
    $cat_name = $submitted_data['categoryID'];
  } elseif(isset($submitted_data['catName']) && $submitted_data['catName'] !== '') {
    $cat_name = $submitted_data['catName'];
  } else {
    $cat_name = '';
  }
  if(isset($submitted_data['rackNo']) && $submitted_data['rackNo'] !== '') {
    $rack_no = $submitted_data['rackNo'];
  } else {
    $rack_no = '';
  }
  if(isset($submitted_data['brandCode']) && $submitted_data['brandCode'] !== '') {
    $brand_code = $submitted_data['brandCode'];
  } elseif(isset($submitted_data['mfgName']) && $submitted_data['mfgName'] !== '') {
    $brand_code = $submitted_data['mfgName'];
  } else {
    $brand_code = '';
  }
  if(isset($submitted_data['comboCode']) && $submitted_data['comboCode'] !== '') {
    $combo_code = $submitted_data['comboCode'];
  } else {
    $combo_code = '';
  }

  $readonly = isset($_SESSION['utype']) && (int)$_SESSION['utype'] === 15 ? 'readonly' : '';
  // dump($errors);
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
              <?php if(isset($_SESSION['utype']) && (int)$_SESSION['utype'] === 15):  ?>
                <?php echo $item_type === 'p' ? 'Product' : 'Service' ?>
                <input type="hidden" id="itemType" name="itemType" value="<?php echo $item_type ?>">
              <?php else: ?>
                <div class="select-wrap">
                  <select class="form-control" name="itemType" id="itemType" <?php echo $readonly ?>>
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
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Store name</label>
              <?php if($update_flag === false): ?>
                <div class="select-wrap">
                  <select class="form-control" name="locationCode" id="locationCode" <?php echo $readonly ?>>
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
                <?php if(isset($errors['locationCode'])): ?>
                  <span class="error"><?php echo $errors['locationCode'] ?></span>
                <?php endif; ?>
              <?php else: ?>
                <p style="font-size:16px;font-weight:bold;color:#225992;">
                  <?php 
                    if( isset($submitted_data['locationID']) && 
                        isset($location_ids[$submitted_data['locationID']]) 
                      ) {
                      $location_name = $location_ids[$submitted_data['locationID']];
                    } elseif( isset($submitted_data['locationCode']) ) {
                      $location_code = $submitted_data['locationCode'];
                      $location_id = array_search($location_code, $location_codes);
                      $location_name = $location_ids[$location_id];
                    } else {
                      $location_name = 'Invalid Store';
                    }
                  ?><?php echo $location_name ?> <span style="color:red;font-size:11px;">[ Not editable ]</span>
                </p>
                <input type="hidden" id="locationCode" name="locationCode" value="<?php echo $product_location ?>" />
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Product / Service name</label>
              <input 
                type="text" 
                class="form-control noEnterKey" 
                name="itemName" 
                id="itemName" 
                value="<?php echo $item_name ?>"
                <?php echo $readonly ?>                
              >
              <?php if(isset($errors['itemName'])): ?>
                <span class="error"><?php echo $errors['itemName'] ?></span>
              <?php endif; ?>              
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
                <?php echo $readonly ?>                
              >
              <?php if(isset($errors['hsnSacCode'])): ?>
                <span class="error"><?php echo $errors['hsnSacCode'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">SKU</label>
              <input
                type="text" 
                class="form-control noEnterKey" 
                name="itemSku" 
                id="itemSku" 
                value="<?php echo $item_sku ?>"
                <?php echo $readonly ?>                
              >
              <?php if(isset($errors['itemSku'])): ?>
                <span class="error"><?php echo $errors['itemSku'] ?></span>
              <?php endif; ?>              
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Measurement unit name (Max. 5 characters)</label>
              <input
                type="text" 
                class="form-control noEnterKey uomAc"
                name="uom"
                id="uom"
                value="<?php echo $uom ?>"
                maxlength="5"
                <?php echo $readonly ?>                
              >
              <?php if(isset($errors['uom'])): ?>
                <span class="error"><?php echo $errors['uom'] ?></span>
              <?php endif; ?>
            </div>
            <input type="hidden" name="taxPercent" id="taxPercent" value="" />
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Sub measurement unit name (Max. 5 characters)</label>
              <input
                type="text" 
                class="form-control noEnterKey"
                name="uomSub"
                id="uomSub"
                value="<?php echo $uom_sub ?>"
                maxlength="5"
                <?php echo $readonly ?>                
              >
              <?php if(isset($errors['uomSub'])): ?>
                <span class="error"><?php echo $errors['uomSub'] ?></span>
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
                <?php echo $readonly ?>                
              >
              <?php if(isset($errors['brandCode'])): ?>
                <span class="error"><?php echo $errors['brandCode'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Category name</label>
              <input 
                type="text"
                class="form-control noEnterKey catAc"
                name="categoryID"
                id="categoryID"
                value="<?php echo $cat_name ?>"
                <?php echo $readonly ?>                
              >
              <?php if(isset($errors['categoryID'])): ?>
                <span class="error"><?php echo $errors['categoryID'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Rack no.</label>
              <input
                type="text" 
                class="form-control noEnterKey"
                name="rackNo"
                id="rackNo"
                value="<?php echo $rack_no ?>"
                maxlength="15"
              >
              <?php if(isset($errors['rack_no'])): ?>
                <span class="error"><?php echo $errors['rack_no'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Combo code</label>
              <input
                type="text" 
                class="form-control noEnterKey"
                name="comboCode"
                id="comboCode"
                value="<?php echo $combo_code ?>"
                maxlength="2"
                title="This code is used in Combo Billing"
                <?php echo $readonly ?>                
              >
              <?php if(isset($errors['comboCode'])): ?>
                <span class="error"><?php echo $errors['comboCode'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Status</label>
                <?php if(isset($_SESSION['utype']) && (int)$_SESSION['utype'] === 15):  ?>
                  <?php echo (int)$sel_status === 1 ? 'Active' : 'Inactive' ?>
                  <input type="hidden" id="itemStatus" name="itemStatus" value="<?php echo $sel_status ?>">
                <?php else: ?>
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
                <?php endif; ?>
              <?php if(isset($errors['status'])): ?>
                <span class="error"><?php echo $errors['status'] ?></span>
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


            <?php /*
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
/*if(isset($submitted_data['mrp'])) {
    $sel_mrp = $submitted_data['mrp'];
  } else {
    $sel_mrp = '';
  }
  if(isset($submitted_data['taxPercent'])) {
    $tax_rate = $submitted_data['taxPercent'];
  } else {
    $tax_rate = '';
  }
*/?>