<?php
  use Atawa\Utilities;
  use Atawa\Constants;
  
  if(isset($form_data['catalogName']) && $form_data['catalogName'] !== '' ) {
    $catalog_name = $form_data['catalogName'];
  } else {
    $catalog_name = '';
  }
  if(isset($form_data['catalogDesc']) && $form_data['catalogDesc'] !== '' ) {
    $catalog_desc = $form_data['catalogDesc'];
  } else {
    $catalog_desc = '';
  }
  if(isset($form_data['catalogDescShort']) && $form_data['catalogDescShort'] !== '' ) {
    $catalog_desc_short = $form_data['catalogDescShort'];
  } else {
    $catalog_desc_short = '';
  }
  if(isset($form_data['status']) && $form_data['status'] !== '' ) {
    $status = $form_data['status'];
  } else {
    $status = 1;
  }
  if(isset($form_data['isDefault']) && $form_data['isDefault'] !== '' ) {
    $is_default = $form_data['isDefault'];
  } else {
    $is_default = 0;
  }
  if(isset($form_data['categoryID']) && $form_data['categoryID'] !== '' ) {
    $category_id = $form_data['categoryID'];
  } else {
    $category_id = 0;
  }
  if(isset($form_data['subCategoryID']) && $form_data['subCategoryID'] !== '' ) {
    $subcategory_id = $form_data['subCategoryID'];
  } else {
    $subcategory_id = 0;
  }
  if(isset($form_data['promote']) && $form_data['promote'] !== '' ) {
    $promote = $form_data['promote'];
  } else {
    $promote = 0;
  }  

  $yes_no_options = [0 => 'No', 1 => 'Yes'];
  $act_inact_options = [0 => 'Inactive', 1 => 'Active'];
  // dump($categories);
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
            <div class="col-sm-12 col-md-6 col-lg-6">
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
            <div class="col-sm-12 col-md-3 col-lg-3">
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
            <div class="col-sm-12 col-md-3 col-lg-3">
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
            <div class="col-sm-12 col-md-6 col-lg-6">
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
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Category</label>
              <div class="select-wrap">
                <select class="form-control" name="categoryID" id="categoryID">
                  <?php 
                    foreach($categories as $key=>$value): 
                      if((int)$category_id === (int)$key) {
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
              <?php if(isset($form_errors['categoryID'])): ?>
                <span class="error"><?php echo $form_errors['categoryID'] ?></span>
              <?php endif; ?>   
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Subcategory</label>
              <div class="select-wrap">
                <select class="form-control" name="subCategoryID" id="subCategoryID">
                  <?php 
                    foreach($subcategories as $key=>$value): 
                      if((int)$subcategory_id === (int)$key) {
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
              <?php if(isset($form_errors['categoryID'])): ?>
                <span class="error"><?php echo $form_errors['categoryID'] ?></span>
              <?php endif; ?>   
            </div>            
            <div style="clear:both;"></div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-9 col-lg-9 m-bot20">
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
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot20">
              <label class="control-label labelStyle">Promote in Catalogs section?</label>
              <div class="select-wrap">
                <select class="form-control" name="promote" id="promote">
                  <?php 
                    foreach($yes_no_options as $key=>$value): 
                      if((int)$promote === (int)$key) {
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
            <div style="clear:both;"></div>
          </div>
          <div class="text-center">
            <button class="btn btn-success">
              <i class="fa fa-plus"></i> Create Catalog
            </button>
            <button class="btn btn-danger cancelButton" id="catalogAddCancel">
              <i class="fa fa-times"></i> Reset
            </button>
          </div>
        </form>
      </div>
    </section>
  </div>
</div>