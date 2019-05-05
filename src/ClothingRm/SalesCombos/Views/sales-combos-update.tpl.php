<?php
  use Atawa\Utilities;

  // dump($form_data);

  if(isset($form_data['locationCode'])) {
    $location_code = $form_data['locationCode'];
  } else {
    $location_code = $default_location;
  }
  if(isset($form_data['comboName'])) {
    $combo_name = $form_data['comboName'];
  } else {
    $combo_name = '';
  }  
  if(isset($form_data['comboPrice'])) {
    $combo_price = $form_data['comboPrice'];
  } else {
    $combo_price = '';
  }  
  if(isset($form_data['status'])) {
    $status = $form_data['status'];
  } else {
    $status = '';
  }
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panel">
      <div class="panel-body">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/promo-offers/list" class="btn btn-default">
              <i class="fa fa-book"></i> Sales Combos List
            </a>
          </div>
        </div>
        <form 
          class="form-validate form-horizontal"
          method="POST"
          id="comboEntryForm"
          autocomplete="off"
        >
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
             <label class="control-label  labelStyle">Combo name (max 20 chars.)</label>
              <input 
                type="text"
                class="form-control noEnterKey"
                name="comboName"
                id="comboName"
                value="<?php echo $combo_name ?>"
                style="border:1px dashed; color: #FFA902;text-transform:uppercase"
                maxlength="20"
              >
              <?php if(isset($form_errors['comboName'])): ?>
                <span class="error"><?php echo $form_errors['comboName'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label labelStyle">Store name</label>
              <div class="select-wrap">
                <select class="form-control" name="locationCode" id="locationCode">
                  <?php 
                    foreach($client_locations as $location_key => $value):
                      $key_a = explode('`', $location_key);
                      if($location_code === $key_a[0]) {
                        $selected = 'selected="selected"';
                      } else {
                        $selected = '';
                      }
                  ?>
                    <option value="<?php echo $key_a[0] ?>" <?php echo $selected ?>>
                      <?php echo $value ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <?php if(isset($form_errors['locationCode'])): ?>
                <span class="error"><?php echo $form_errors['locationCode'] ?></span>
              <?php endif; ?>
            </div>            
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label labelStyle">Status</label>
              <div class="select-wrap">
                <select class="form-control" name="status" id="status">
                  <?php 
                    foreach($status_a as $key=>$value): 
                      if($status === $key) {
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
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
             <label class="control-label  labelStyle">Combo price</label>
              <input 
                type="text"
                class="form-control noEnterKey"
                name="comboPrice"
                id="comboPrice"
                value="<?php echo $combo_price ?>"
                maxlength="20"
              >
              <?php if(isset($form_errors['comboPrice'])): ?>
                <span class="error"><?php echo $form_errors['comboPrice'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-hover font12">
              <tbody>
                <tr>
                  <td class="labelStyle" colspan="3">Add Combo products below. You can add upto 15 products to a Combo.</td>
                </tr>
                <?php 
                  $cntr = 0;
                  for($i=0;$i<5;$i++): 
                ?>
                  <tr>
                    <?php 
                      for($j=0;$j<3;$j++): 
                        $product_name = isset($form_data['itemDetails'][$cntr]) ? $form_data['itemDetails'][$cntr] : '';
                    ?>
                      <td align="center">
                        <input 
                          type="text"
                          name="itemDetails[]"
                          id="iname_<?php echo $j ?>"
                          size="40"
                          class="inameAc saleItem noEnterKey"
                          placeholder="Product name"
                          maxlength="20"
                          value="<?php echo $product_name ?>"
                        /> 
                      </td>
                    <?php
                      $cntr++;
                      endfor; 
                    ?>
                  </tr>
                <?php endfor; ?>

                <?php if(isset($form_errors['itemDetails'])): ?>
                  <tr>
                    <td colspan="9" align="center">
                      <span class="error"><?php echo $form_errors['itemDetails'] ?></span>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>          
          <div class="text-center">
            <button class="btn btn-danger cancelButton" id="comboCancel">
              <i class="fa fa-times"></i> Cancel
            </button>
            <button class="btn btn-primary" id="offerSave">
              <i class="fa fa-save"></i> Save
            </button>
          </div>
        </form>
      </div>
    </section>
  </div>
</div>