<?php
  use Atawa\Utilities;
  use Atawa\Constants;
  use Atawa\Config\Config;

  $s3_config = Config::get_s3_details();
  
  if(isset($form_data['contentTitle']) && $form_data['contentTitle'] !== '' ) {
    $content_title = $form_data['contentTitle'];
  } else {
    $content_title = '';
  }
  if(isset($form_data['enableRedirection']) && $form_data['enableRedirection'] !== '' ) {
    $enable_redirection = $form_data['enableRedirection'];
  } else {
    $enable_redirection = 0;
  }
  if(isset($form_data['contentCategory']) && $form_data['contentCategory'] !== '' ) {
    $content_category = $form_data['contentCategory'];
  } else {
    $content_category = '';
  }
  if(isset($form_data['status']) && $form_data['status'] !== '' ) {
    $status = $form_data['status'];
  } else {
    $status = 1;
  }
  if(isset($form_data['catalogName']) && $form_data['catalogName'] !== '' ) {
    $catalog_name = $form_data['catalogName'];
  } else {
    $catalog_name = '';
  }
  if(isset($form_data['itemName']) && $form_data['itemName'] !== '' ) {
    $item_name = $form_data['itemName'];
  } else {
    $item_name = '';
  }
  if(isset($form_data['weight']) && $form_data['weight'] !== '' ) {
    $weight = $form_data['weight'];
  } else {
    $weight = 0;
  }  
  $show_redirection = (int)$enable_redirection === 1 ? '' : 'display: none;';
  $s3_url = 'https://'.$s3_config['BUCKET_NAME'].'.'.$s3_config['END_POINT_FULL'].'/'.$client_code.'/categories/'.$existing_data['imageName'];
  $act_inact_options = [0 => 'Inactive', 1 => 'Active'];  
?>
<div class="row">
  <div class="col-lg-12">
    <section class="panelBox">
      <div class="panelBody">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/ecom/app-content/list" class="btn btn-default">
              <i class="fa fa-mobile"></i> Content List
            </a>&nbsp;
          </div>
        </div>
        <form id="galleryForm" method="POST" autocomplete="off" enctype="multipart/form-data">
          <div class="form-group">
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Content title*</label>
              <input
                  type="text" 
                  class="form-control" 
                  name="contentTitle" 
                  id="contentTitle"
                  value="<?php echo $content_title ?>"
                  maxlength="100"
                >            
              <?php if(isset($form_errors['contentTitle'])): ?>
                <span class="error"><?php echo $form_errors['contentTitle'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Content category*</label>
              <div class="select-wrap">
                <select class="form-control" id="contentCategory" name="contentCategory">
                  <?php 
                    foreach($categories as $key=>$value): 
                      if($key === $content_category) {
                        $selected = 'selected="selected"';
                      } else {
                        $selected = '';
                      }
                  ?>
                    <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <?php if(isset($form_errors['contentCategory'])): ?>
                <span class="error"><?php echo $form_errors['contentCategory'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Enable redirection?</label>
              <div class="select-wrap">
                <select class="form-control" id="enableRedirection" name="enableRedirection">
                  <?php 
                    foreach($redirection_a as $key=>$value): 
                      if((int)$key === (int)$enable_redirection) {
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
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Status</label>
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
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Image*</label>
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
            <div id="appContentRedirectionContainer" style="<?php echo $show_redirection ?>">
              <div class="col-sm-12 col-md-3 col-lg-3">
                <label class="control-label labelStyle">Catalog name</label>
                <input
                    type="text" 
                    class="form-control catalogAc" 
                    name="catalogName" 
                    id="catalogName"
                    value="<?php echo $catalog_name ?>"
                    maxlength="100"
                  >            
                <?php if(isset($form_errors['catalogName'])): ?>
                  <span class="error"><?php echo $form_errors['catalogName'] ?></span>
                <?php endif; ?>
              </div>
              <div class="col-sm-12 col-md-3 col-lg-3 m-bot20">
                <label class="control-label labelStyle">Item name</label>
                <input
                    type="text" 
                    class="form-control catalogItemAc" 
                    name="itemName" 
                    id="itemName"
                    value="<?php echo $item_name ?>"
                    maxlength="100"
                  >            
                <?php if(isset($form_errors['itemName'])): ?>
                  <span class="error"><?php echo $form_errors['itemName'] ?></span>
                <?php endif; ?>
              </div>
            </div>
            <div style="clear:both;"></div>          
          </div>
          <div class="form-group">
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
            <button class="btn btn-success cancelOp" id="ecomCreateAppContent">
              <i class="fa fa-save"></i> Update App Content
            </button>&nbsp;
            <button class="btn btn-danger cancelButton" id="ecomCreateAppCancel">
              <i class="fa fa-times"></i> Reset
            </button>
          </div>
        </form>
      </div>
    </section>
  </div>
</div>