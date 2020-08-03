<?php
  use Atawa\Utilities;
  use Atawa\Constants;
  use Atawa\Config\Config;

  $s3_config = Config::get_s3_details();

  // dump($form_data, $form_errors);
  // dump($_SESSION);
  // dump($existing_gallery_details);
  
  if(isset($form_data['itemName']) && $form_data['itemName'] !== '' ) {
    $item_name = $form_data['itemName'];
  } elseif(isset($existing_gallery_details['itemName']) && $existing_gallery_details['itemName'] !== '' ) {
    $item_name = $existing_gallery_details['itemName'];
  } else {
    $item_name = '';
  }
  if(isset($form_data['itemDescription']) && $form_data['itemDescription'] !== '' ) {
    $item_description = $form_data['itemDescription'];
  } elseif(isset($existing_gallery_details['itemDescription']) && $existing_gallery_details['itemDescription'] !== '' ) {
    $item_description = $existing_gallery_details['itemDescription'];
  } else {
    $item_description = '';
  }
  if(isset($form_data['itemStylecode']) && $form_data['itemStylecode'] !== '' ) {
    $item_style_code = $form_data['itemStylecode'];
  } elseif(isset($existing_gallery_details['itemStylecode']) && $existing_gallery_details['itemStylecode'] !== '' ) {
    $item_style_code = $existing_gallery_details['itemStylecode'];
  } else {
    $item_style_code = '';
  }
  if(isset($form_data['itemColor']) && $form_data['itemColor'] !== '' ) {
    $item_color = $form_data['itemColor'];
  } elseif(isset($existing_gallery_details['itemColor']) && $existing_gallery_details['itemColor'] !== '' ) {
    $item_color = $existing_gallery_details['itemColor'];
  } else {
    $item_color = '';
  }
  if(isset($form_data['billingRate']) && $form_data['billingRate'] !== '' ) {
    $billing_rate = $form_data['billingRate'];
  } elseif(isset($existing_gallery_details['billingRate']) && $existing_gallery_details['billingRate'] !== '' ) {
    $billing_rate = $existing_gallery_details['billingRate'];
  } else {
    $billing_rate = '';
  }
  if(isset($form_data['itemRate']) && $form_data['itemRate'] !== '' ) {
    $item_rate = $form_data['itemRate'];
  } elseif(isset($existing_gallery_details['itemRate']) && $existing_gallery_details['itemRate'] !== '' ) {
    $item_rate = $existing_gallery_details['itemRate'];
  } else {
    $item_rate = '';
  }  

  if(isset($form_data['locationCode']) && $form_data['locationCode'] !== '') {
    $location_code = $form_data['locationCode'];
  } elseif(isset($existing_gallery_details['locationCode']) && $existing_gallery_details['locationCode'] !== '') {
    $location_code = $existing_gallery_details['locationCode'];
  } else {
    $location_code = $default_location;
  }

  if(isset($form_data['packedQty']) && $form_data['packedQty'] !== '') {
    $packed_qty = $form_data['packedQty'];
  } elseif(isset($existing_gallery_details['packedQty']) && $existing_gallery_details['packedQty'] !== '') {
    $packed_qty = $existing_gallery_details['packedQty'];
  } else {
    $packed_qty = 1;
  }

  $s3_url = 'https://'.$s3_config['BUCKET_NAME'].'.'.$s3_config['END_POINT_FULL'].'/'.$_SESSION['ccode'].'/'.$location_code.'/';

  if(isset($existing_gallery_details['images'][0])) {
    $image_0_name = $existing_gallery_details['images'][0]['imageName'];
    $image_0_date = $existing_gallery_details['images'][0]['dateUploaded'];
    $image_0_type = $existing_gallery_details['images'][0]['type'];
    $image_0_weight = $existing_gallery_details['images'][0]['weight'];
    $image_0_status = $existing_gallery_details['images'][0]['status'];
  } else {
    $image_0_name = '';
    $image_0_date = '';
    $image_0_type = '';
    $image_0_weight = '';
    $image_0_status = '';
  }
  if(isset($existing_gallery_details['images'][1])) {
    $image_1_name = $existing_gallery_details['images'][1]['imageName'];
    $image_1_date = $existing_gallery_details['images'][1]['dateUploaded'];
    $image_1_type = $existing_gallery_details['images'][1]['type'];
    $image_1_weight = $existing_gallery_details['images'][1]['weight'];
    $image_1_status = $existing_gallery_details['images'][1]['status'];
  } else {
    $image_1_name = '';
    $image_1_date = '';
    $image_1_type = '';
    $image_1_weight = '';
    $image_1_status = '';
  }
  if(isset($existing_gallery_details['images'][2])) {
    $image_2_name = $existing_gallery_details['images'][2]['imageName'];
    $image_2_date = $existing_gallery_details['images'][2]['dateUploaded'];
    $image_2_type = $existing_gallery_details['images'][2]['type'];
    $image_2_weight = $existing_gallery_details['images'][2]['weight'];
    $image_2_status = $existing_gallery_details['images'][2]['status'];
  } else {
    $image_2_name = '';
    $image_2_date = '';
    $image_2_type = '';
    $image_2_weight = '';
    $image_2_status = '';
  }
  if(isset($existing_gallery_details['images'][3])) {
    $image_3_name = $existing_gallery_details['images'][3]['imageName'];
    $image_3_date = $existing_gallery_details['images'][3]['dateUploaded'];
    $image_3_type = $existing_gallery_details['images'][3]['type'];
    $image_3_weight = $existing_gallery_details['images'][3]['weight'];
    $image_3_status = $existing_gallery_details['images'][2]['status'];
  } else {
    $image_3_name = '';
    $image_3_date = '';
    $image_3_type = '';
    $image_3_weight = '';
    $image_3_status = '';
  }
  if(isset($existing_gallery_details['images'][4])) {
    $image_4_name = $existing_gallery_details['images'][4]['imageName'];
    $image_4_date = $existing_gallery_details['images'][4]['dateUploaded'];
    $image_4_type = $existing_gallery_details['images'][4]['type'];
    $image_4_weight = $existing_gallery_details['images'][4]['weight'];
    $image_4_status = $existing_gallery_details['images'][2]['status'];
  } else {
    $image_4_name = '';
    $image_4_date = '';
    $image_4_type = '';
    $image_4_weight = '';
    $image_4_status = '';
  }
  if(isset($existing_gallery_details['images'][5])) {
    $image_5_name = $existing_gallery_details['images'][5]['imageName'];
    $image_5_date = $existing_gallery_details['images'][5]['dateUploaded'];
    $image_5_type = $existing_gallery_details['images'][5]['type'];
    $image_5_weight = $existing_gallery_details['images'][5]['weight'];
    $image_5_status = $existing_gallery_details['images'][5]['status'];
  } else {
    $image_5_name = '';
    $image_5_date = '';
    $image_5_type = '';
    $image_5_weight = '';
    $image_5_status = '';
  }
  if(isset($existing_gallery_details['images'][6])) {
    $image_6_name = $existing_gallery_details['images'][6]['imageName'];
    $image_6_date = $existing_gallery_details['images'][6]['dateUploaded'];
    $image_6_type = $existing_gallery_details['images'][6]['type'];
    $image_6_weight = $existing_gallery_details['images'][6]['weight'];
    $image_6_status = $existing_gallery_details['images'][6]['status'];
  } else {
    $image_6_name = '';
    $image_6_date = '';
    $image_6_type = '';
    $image_6_weight = '';
    $image_6_status = '';
  }  
  if(isset($existing_gallery_details['images'][7])) {
    $image_7_name = $existing_gallery_details['images'][7]['imageName'];
    $image_7_date = $existing_gallery_details['images'][7]['dateUploaded'];
    $image_7_type = $existing_gallery_details['images'][7]['type'];
    $image_7_weight = $existing_gallery_details['images'][7]['weight'];
    $image_7_status = $existing_gallery_details['images'][7]['status'];
  } else {
    $image_7_name = '';
    $image_7_date = '';
    $image_7_type = '';
    $image_7_weight = '';
    $image_7_status = '';
  }

  $input_type_a = ['barcode' => 'Barcode', 'item' => 'Item Name'];
  $entry_mode = 'item';

  // dump($existing_gallery_details, $form_data);
?>
<div class="row">
  <div class="col-lg-12">
    <section class="panelBox">
      <div class="panelBody">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/galleries/list?locationCode=<?php echo $location_code ?>" class="btn btn-default">
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
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-9 col-lg-9">
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
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Item rate (auto)</label>
              <input 
                type="text" 
                class="form-control" 
                name="itemRate" 
                id="itemRate" 
                value="<?php echo $item_rate ?>"
                maxlength="15"
                readonly
              >
            </div>            
            <div style="clear:both;"></div>            
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot20">
              <label class="control-label labelStyle">Packed qty.</label>
              <input 
                type="text" 
                class="form-control" 
                name="packedQty" 
                id="packedQty" 
                value="<?php echo $packed_qty ?>"
                maxlength="10"
              >
              <?php if(isset($form_errors['packedQty'])): ?>
                <span class="error"><?php echo $form_errors['packedQty'] ?></span>
              <?php endif; ?>
            </div>
            <div style="clear:both;"></div>          
          </div>          
          <h4 class="labelStyleOnlyColor">Item images</h4>
          <div class="table-responsive">

            <?php if(count($form_errors) > 0): ?>
              <span class="error"><i class="fa fa-times" aria-hidden="true"></i>You have errors in the form.</span>
            <?php endif; ?>

            <table class="table table-hover item-detail-table font11">
              <thead>
                <tr class="font12">
                  <th width="10%" class="text-center">Image uploaded</th>
                  <th width="10%" class="text-center">Uploaded on</th>
                  <th width="10%" class="text-center">Type</th>
                  <th width="20%" class="text-center">New image</th>
                  <th width="8%" class="text-center">Weight</th>                                                
                  <th width="10%" class="text-center">Status</th>                                           
                </tr>
              </thead>
              <tbody>
                <?php
                  $weight_a = [-1 => 'Choose'];
                  $status_options_a = [-1 => 'Choose', 0=>'Inactive', 1=>'Active'];

                  for($i=0;$i<8;$i++) {
                    $weight_a[] = $i;
                  }

                  for($i=0; $i<8; $i++):
                    if(${'image_'.$i.'_name'} !== '') {
                      $image_url = $s3_url.${'image_'.$i.'_name'};
                      $image_name = ${'image_'.$i.'_name'};
                      $uploaded_on = date("d-M-Y h:ia", ${'image_'.$i.'_date'});
                      $image_type = ${'image_'.$i.'_type'};
                      $image_weight = ${'image_'.$i.'_weight'};
                      $image_status =  (int)${'image_'.$i.'_status'};
                    } else {
                      $image_url = '';
                      $image_name = '';
                      $uploaded_on = '';
                      $image_type = '';
                      $image_weight = -1;
                      $image_status = -1;
                    }
                    $image_variable_name = 'image_'.$i;
                    $image_variable_id = 'image'.$i;
                ?>
                  <tr>
                    <td align="center" class="valign-middle">
                      <?php if($image_name !== ''): ?>
                        <div id="imagePlaceholder_<?php echo $i ?>">
                          <a href="javascript:void(0)" class="showImage" title="<?php echo $image_name ?>">
                            <img src="<?php echo $image_url ?>" height="100" width="100" alt="Image <?php echo $i ?>" />  
                          </a>
                        </div>
                      <?php else: ?>
                        <p>Not uploaded</p>
                      <?php endif; ?>
                    </td>
                    <td class="valign-middle">
                      <span id="uploaded_<?php echo $i ?>"><?php echo $uploaded_on ?></span>
                    </td>
                    <td class="valign-middle">
                      <span id="imageType_<?php echo $i ?>"><?php echo $image_type ?></span>
                    </td>
                    <td align="center" class="valign-middle">
                      <input 
                        type="file" 
                        class="form-control" 
                        name="<?php echo $image_variable_name ?>"
                        id="<?php echo $image_variable_id ?>"
                      >
                      <?php if(isset($form_errors[$image_variable_name])): ?>
                        <span class="error"><?php echo $form_errors[$image_variable_name] ?></span>
                      <?php endif; ?>
                    </td>
                    <td class="valign-middle" align="center">
                      <select id="weight_<?php echo $i ?>" name="weight[]" style="width: 80px; height: 34px; font-size: 14px;background-color: yellow;padding-left: 5px;">
                        <?php
                          foreach($weight_a as $key => $weight):
                            // var_dump($image_weight, $key, $weight);
                            if($image_weight == $key) {
                              $selected = 'selected="selected"';
                            } else {
                              $selected = '';
                            }
                        ?>
                          <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $weight ?></option>
                        <?php endforeach; ?>
                      </select>
                    </td>
                    <td class="valign-middle" align="center">
                      <?php if($image_name !== ''): ?>
                        <select id="image_del_<?php echo $i ?>" name="delImage[]" class="imageDelete" style="width: 80px; height: 34px; font-size: 14px;background-color: yellow;padding-left: 5px;">
                          <?php 
                            foreach($status_options_a as $key => $value): 
                              if((int)$image_status === (int)$key) {
                                $selected = 'selected="selected"';
                              } else {
                                $selected = '';
                              }
                          ?>
                            <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                          <?php endforeach; ?>
                        </select>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endfor; ?>
              </tbody>
              <input type="hidden" id="lc" value="<?php echo $location_code ?>" />
              <input type="hidden" id="gc" value="<?php echo $existing_gallery_details['galleryCode'] ?>" />
            </table>
          </div>          
          <div class="text-center">
            <button class="btn btn-success cancelOp" id="imgUpload">
              <i class="fa fa-edit"></i> Update Gallery
            </button>
            <button class="btn btn-danger cancelButton" id="imgUpdateCancel">
              <i class="fa fa-times"></i> Reset
            </button>
          </div>
        </form>
      </div>
    </section>
  </div>
</div>


<div class="modal fade" id="imagemodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="modal-title">&nbsp;</h4>
      </div>             
      <div class="modal-body">
        <img id="imagePreview" style="width: 100%;" src="" />
      </div>
    </div>
  </div>
</div>