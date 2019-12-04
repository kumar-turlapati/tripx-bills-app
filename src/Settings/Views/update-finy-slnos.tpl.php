<?php
  // dump($form_data);
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panel">
      <h2 class="hdg-reports text-center">Update Voucher Serial Numbers in Financial Year</h2>
      <div class="panel-body">
        <?php echo $flash_obj->print_flash_message(); ?>        
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/finy-slnos/list" class="btn btn-default">
              <i class="fa fa-sort-numeric-asc"></i> Financial Year Slnos List
            </a>
          </div>
        </div>
        <form class="form-validate form-horizontal" method="POST" autocomplete="off" id="finYearSlnoForm">
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label"><b>Choose Financial Year</b></label>
              <div class="select-wrap">
                <select class="form-control" name="finyCodeDrop" id="finyCode" disabled>
                  <?php 
                    foreach($finys as $key=>$value):
                      if($selected_finy_code === $key) {
                        $selected = 'selected="selected"';
                      } else {
                        $selected = '';
                      } 
                  ?>
                    <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <?php if(isset($errors['finyCode'])): ?>
                <span class="error"><?php echo $errors['finyCode'] ?></span>
              <?php endif; ?>               
              <input type="hidden" name="finyCode" value="<?php echo $selected_finy_code ?>" />
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label"><b>Store Name</b></label>              
              <div class="select-wrap">
                <select class="form-control" name="locationCodeDrop" id="locationCode" disabled>
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
            <input type="hidden" name="locationCode" value="<?php echo $location_code ?>" />
          </div>
          <div class="table-responsive">
            <table class="table table-striped table-hover item-detail-table font12" id="finySlnosTable">
              <thead>
                <tr>
                  <th style="width: 5%"  class="text-center valign-middle">Sno.</th>
                  <th style="width:25%"  class="text-center valign-middle">Voucher Name</th>
                  <th style="width:20%;" class="text-center valign-middle">Text Portion</th>
                  <th style="width:20%;" class="text-center valign-middle">Starting Serial<br />( Auto Increment )</th>
                  <th style="width:30%;" class="text-center valign-middle">Voucher Description</th>
                </tr>
              </thead>
              <tbody>
                <?php
                  $cntr = $place_holder_inc = 0;
                  foreach($voc_types as $voc_code => $voc_name):
                    $var_name_txt = $voc_code.'_text';
                    $var_name_aic = $voc_code.'_aic';

                    $text_value = isset($form_data['vocCodesText'][$var_name_txt]) ? $form_data['vocCodesText'][$var_name_txt] : '';
                    $aic_value = isset($form_data['vocCodesAic'][$var_name_aic]) ? $form_data['vocCodesAic'][$var_name_aic] : '';

                    $place_holder_inc += 1000; 
                    $cntr++;
                ?>
                  <tr>
                    <td class="text-right valign-middle" style="padding-right:10px;"><?php echo $cntr ?></td>
                    <td class="text-left valign-middle" style="font-size:16px;font-weight:bold;padding-left:20px;"><?php echo $voc_name ?></td>
                    <td class="text-center valign-middle">
                      <input 
                        type = "text"
                        name = "vocCodesText[<?php echo $voc_code ?>_text]"
                        id = "<?php echo $voc_code ?>_text"
                        value = "<?php echo $text_value ?>"
                        class = "form-control noEnterKey valign-middle"
                        style = "border:1px dashed #225992"
                        maxlength = "14"
                        placeholder = "ex: abcd/99-99/<?php echo $voc_code ?>"
                      />
                      <?php if( isset($form_errors['vocCodesText'][$var_name_txt]) ) :?>
                        <span class="error" style="font-size:9px;"><?php echo $form_errors['vocCodesText'][$var_name_txt] ?></span>
                      <?php endif; ?>
                    </td>
                    <td class="text-center valign-middle">
                      <input 
                        type = "text"
                        name = "vocCodesAic[<?php echo $voc_code ?>_aic]"
                        id = "<?php echo $voc_code ?>_aic"
                        value = "<?php echo $aic_value ?>"
                        class="form-control noEnterKey valign-middle"
                        style="border:1px dotted #225992"
                        maxlength = "6"
                        placeholder="ex: <?php echo $place_holder_inc ?>"
                        readonly
                        title="Not editable"
                      />
                      <?php if( isset($form_errors['vocCodesAic'][$var_name_aic]) ) :?>
                        <span class="error" style="font-size:9px;"><?php echo $form_errors['vocCodesAic'][$var_name_aic] ?></span>
                      <?php endif; ?>
                    </td>
                    <td class="text-left valign-middle"><?php echo '' ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div class="text-center">
            <button class="btn btn-success" id="Save"><i class="fa fa-save"></i> Save</button>
          </div>
        </form>
      </div>
    </section>
  </div>
</div>