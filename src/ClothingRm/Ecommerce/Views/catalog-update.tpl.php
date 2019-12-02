<?php
  use Atawa\Utilities;
  use Atawa\Constants;
  
  if(isset($form_data['catalogName']) && $form_data['catalogName'] !== '' ) {
    $catalog_name = $form_data['catalogName'];
  } elseif(isset($existing_catalog_details['catalogName']) && $existing_catalog_details['catalogName'] !== '' ) {
    $catalog_name = $existing_catalog_details['catalogName'];
  } else {
    $catalog_name = '';
  }
  if(isset($form_data['catalogDesc']) && $form_data['catalogDesc'] !== '' ) {
    $catalog_desc = $form_data['catalogDesc'];
  } elseif(isset($existing_catalog_details['catalogDesc']) && $existing_catalog_details['catalogDesc'] !== '' ) {
    $catalog_desc = $existing_catalog_details['catalogDesc'];
  } else {
    $catalog_desc = '';
  }
  if(isset($form_data['catalogDescShort']) && $form_data['catalogDescShort'] !== '' ) {
    $catalog_desc_short = $form_data['catalogDescShort'];
  } elseif(isset($existing_catalog_details['catalogDescShort']) && $existing_catalog_details['catalogDescShort'] !== '' ) {
    $catalog_desc_short = $existing_catalog_details['catalogDescShort'];
  } else {
    $catalog_desc_short = '';
  }
  if(isset($form_data['status']) && $form_data['status'] !== '' ) {
    $status = $form_data['status'];
  } elseif(isset($existing_catalog_details['status']) && $existing_catalog_details['status'] !== '' ) {
    $status = $existing_catalog_details['status'];
  } else {
    $status = 1;
  }
  if(isset($form_data['isDefault']) && $form_data['isDefault'] !== '' ) {
    $is_default = $form_data['isDefault'];
  } elseif(isset($existing_catalog_details['isDefault']) && $existing_catalog_details['isDefault'] !== '' ) {
    $is_default = $existing_catalog_details['isDefault'];
  } else {
    $is_default = 0;
  }

  $yes_no_options = [0 => 'No', 1 => 'Yes'];
  $act_inact_options = [0 => 'Inactive', 1 => 'Active'];
?>
<div class="row">
  <div class="col-lg-12">
    <section class="panelBox">
      <div class="panelBody">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/catalog/list" class="btn btn-default">
              <i class="fa fa-briefcase"></i> Catalogs
            </a>
          </div>
        </div>
        <form id="galleryForm" method="POST" autocomplete="off">
          <div class="form-group">
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot10">
              <label class="control-label labelStyle">Catalog name*</label>
              <input
                  type="text" 
                  class="form-control" 
                  name="catalogName" 
                  id="catalogName"
                  value="<?php echo $catalog_name ?>"
                  maxlength="30"
                >            
              <?php if(isset($form_errors['catalogName'])): ?>
                <span class="error"><?php echo $form_errors['catalogName'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot10">
              <label class="control-label labelStyle">Make this catalog as default?</label>
              <div class="select-wrap">
                <select class="form-control" name="isDefault" id="isDefault">
                  <?php 
                    foreach($yes_no_options as $key=>$value): 
                      if((int)$is_default === (int)$key) {
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
              <?php if(isset($form_errors['isDefault'])): ?>
                <span class="error"><?php echo $form_errors['isDefault'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot20">
              <label class="control-label labelStyle">Catalog Status</label>
              <div class="select-wrap">
                <select class="form-control" name="status" id="status">
                  <?php 
                    foreach($act_inact_options as $key=>$value): 
                      if((int)$status === (int)$key) {
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
              <?php if(isset($form_errors['status'])): ?>
                <span class="error"><?php echo $form_errors['status'] ?></span>
              <?php endif; ?>   
            </div>
            <div style="clear:both;"></div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-8 col-lg-8">
              <label class="control-label labelStyle">Catalog description short (max 100 chars.)</label>
              <input 
                type="text" 
                class="form-control" 
                name="catalogDescShort" 
                id="catalogDescShort" 
                value="<?php echo $catalog_desc_short ?>"
                maxlength="100"
              >
              <?php if(isset($form_errors['catalogDescShort'])): ?>
                <span class="error"><?php echo $form_errors['catalogDescShort'] ?></span>
              <?php endif; ?>
            </div>
            <div style="clear:both;"></div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-12 col-lg-12 m-bot20">
              <label class="control-label labelStyle">Catalog description long (max 300 chars.)</label>
              <input 
                type="text" 
                class="form-control" 
                name="catalogDesc" 
                id="catalogDesc" 
                value="<?php echo $catalog_desc ?>"
                maxlength="300"
              >
              <?php if(isset($form_errors['catalogDesc'])): ?>
                <span class="error"><?php echo $form_errors['catalogDesc'] ?></span>
              <?php endif; ?>
            </div>
            <div style="clear:both;"></div>
          </div>
          <div class="text-center">
            <button class="btn btn-success">
              <i class="fa fa-edit"></i> Update Catalog
            </button>
            <button class="btn btn-danger cancelButton" id="catalogUpdateCancel">
              <i class="fa fa-times"></i> Reset
            </button>
          </div>
        </form>
      </div>
    </section>
  </div>
</div>