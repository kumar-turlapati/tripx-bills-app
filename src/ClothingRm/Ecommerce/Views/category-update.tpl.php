<?php
  use Atawa\Utilities;
  use Atawa\Constants;
  use Atawa\Config\Config;

  $s3_config = Config::get_s3_details();

  // dump($form_data);  
  // dump($form_errors);
  // exit;
  
  if(isset($form_data['categoryName']) && $form_data['categoryName'] !== '' ) {
    $category_name = $form_data['categoryName'];
  } else {
    $category_name = '';
  }
  if(isset($form_data['categoryDescShort']) && $form_data['categoryDescShort'] !== '' ) {
    $category_desc_short = $form_data['categoryDescShort'];
  } else {
    $category_desc_short = '';
  }
  if(isset($form_data['categoryDescLong']) && $form_data['categoryDescLong'] !== '' ) {
    $category_desc_long = $form_data['categoryDescLong'];
  } else {
    $category_desc_long = '';
  }
  if(isset($form_data['weight']) && $form_data['weight'] !== '' ) {
    $weight = $form_data['weight'];
  } else {
    $weight = 0;
  }
  if(isset($form_data['parentID']) && $form_data['parentID'] !== '' ) {
    $parent = $form_data['parentID'];
  } else {
    $parent = '';
  }

  $s3_url = 'https://'.$s3_config['BUCKET_NAME'].'.'.$s3_config['END_POINT_FULL'].'/'.$client_code.'/categories/'.$existing_data['imageName'];
?>
<div class="row">
  <div class="col-lg-12">
    <section class="panelBox">
      <div class="panelBody">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/ecom/categories/list" class="btn btn-default">
              <i class="fa fa-copy"></i> Categories
            </a>&nbsp;
            <?php if($mod === 'Subcategory'): ?>
              <a href="/ecom/subcategories/<?php echo $parentID ?>" class="btn btn-default">
                <i class="fa fa-files-o"></i> Subcategories
              </a>
            <?php endif; ?>
          </div>
        </div>
        <form id="galleryForm" method="POST" autocomplete="off" enctype="multipart/form-data">
          <div class="form-group">
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle"><?php echo $mod ?> name*</label>
              <input
                  type="text" 
                  class="form-control" 
                  name="categoryName" 
                  id="categoryName"
                  value="<?php echo $category_name ?>"
                  maxlength="50"
                >            
              <?php if(isset($form_errors['categoryName'])): ?>
                <span class="error"><?php echo $form_errors['categoryName'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Parent</label>
              <div class="select-wrap">
                <select class="form-control" id="parent" name="parent">
                  <?php 
                    foreach($categories as $key=>$value): 
                      if((int)$key === (int)$parent) {
                        $selected = 'selected="selected"';
                      } else {
                        $selected = '';
                      }
                  ?>
                    <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <?php if(isset($form_errors['parent'])): ?>
                <span class="error"><?php echo $form_errors['parent'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Weight</label>
              <div class="select-wrap">
                <select class="form-control" id="weight" name="weight">
                  <?php 
                    foreach($weights as $key=>$value): 
                      if((int)$key === (int)$weight) {
                        $selected = 'selected="selected"';
                      } else {
                        $selected = '';
                      }
                  ?>
                    <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <?php if(isset($form_errors['weight'])): ?>
                <span class="error"><?php echo $form_errors['weight'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle"><?php echo $mod ?> image*</label>
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
            <div style="clear:both;"></div>          
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-12 col-lg-12 m-bot15">
              <label class="control-label labelStyle"><?php echo $mod ?> description - Short (max 100 chars.)</label>
              <input 
                type="text" 
                class="form-control" 
                name="categoryDescShort" 
                id="categoryDescShort" 
                value="<?php echo $category_desc_short ?>"
                maxlength="100"
              >
              <?php if(isset($form_errors['categoryDescShort'])): ?>
                <span class="error"><?php echo $form_errors['categoryDescShort'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-8 col-lg-8">
              <label class="control-label labelStyle"><?php echo $mod ?> description - Long (max 1000 chars.)</label>
              <textarea 
                class="form-control" 
                name="categoryDescLong" 
                id="categoryDescLong" 
                maxlength="1000"
                rows="6"
                cols="300"
              ><?php echo $category_desc_long ?></textarea>
              <?php if(isset($form_errors['categoryDescLong'])): ?>
                <span class="error"><?php echo $form_errors['categoryDescLong'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label labelStyle">Existing image (resized to 200x200 here)</label>
              <table>
                <tr>
                  <td>
                    <img src="<?php echo $s3_url ?>" height="200" width="200" />
                  </td>
                </tr>
              </table>
            </div>            
            <div style="clear:both;"></div>          
          </div>
          <div class="text-center">
            <button class="btn btn-success cancelOp" id="ecomCatCreate">
              <i class="fa fa-save"></i> Update <?php echo $mod ?>
            </button>
            <button class="btn btn-danger cancelButton" id="ecomCatCancel">
              <i class="fa fa-times"></i> Reset
            </button>
          </div>
        </form>
      </div>
    </section>
  </div>
</div>