<?php
  use Atawa\Utilities;

  $location_code = isset($form_data['locationCode']) ? $form_data['locationCode'] : $default_location;
  $combo_name = isset($form_data['comboName']) ? $form_data['comboName'] : '';
  $combo_number = isset($form_data['comboNumber']) ? $form_data['comboNumber'] : '';  
  $status = isset($form_data['status']) ? $form_data['status'] : 99;

  $combo_price1   =   isset($form_data['comboPrice1']) ? $form_data['comboPrice1']: '';
  $combo_price2   =   isset($form_data['comboPrice2']) ? $form_data['comboPrice2']: '';
  $combo_price3   =   isset($form_data['comboPrice3']) ? $form_data['comboPrice3']: '';
  $combo_price4   =   isset($form_data['comboPrice4']) ? $form_data['comboPrice4']: '';
  $combo_price5   =   isset($form_data['comboPrice5']) ? $form_data['comboPrice5']: '';
  $combo_price6   =   isset($form_data['comboPrice6']) ? $form_data['comboPrice6']: '';
  $combo_price7   =   isset($form_data['comboPrice7']) ? $form_data['comboPrice7']: '';
  $combo_price8   =   isset($form_data['comboPrice8']) ? $form_data['comboPrice8']: '';
  $combo_price9   =   isset($form_data['comboPrice9']) ? $form_data['comboPrice9']: '';
  $combo_price10  =   isset($form_data['comboPrice10']) ? $form_data['comboPrice10']: '';
  $combo_price11  =   isset($form_data['comboPrice11']) ? $form_data['comboPrice11']: '';
  $combo_price12  =   isset($form_data['comboPrice12']) ? $form_data['comboPrice12']: '';
  $combo_price13  =   isset($form_data['comboPrice13']) ? $form_data['comboPrice13']: '';  
  $combo_price14  =   isset($form_data['comboPrice14']) ? $form_data['comboPrice14']: '';  
  $combo_price15  =   isset($form_data['comboPrice15']) ? $form_data['comboPrice15']: '';  
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panel">
      <div class="panel-body">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/sales-combo/list" class="btn btn-default">
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
             <label class="control-label  labelStyle">Combo name (max 50 chars.)</label>
              <input 
                type="text"
                class="form-control noEnterKey"
                name="comboName"
                id="comboName"
                value="<?php echo $combo_name ?>"
                style="border:1px dashed; color: #FFA902;text-transform:uppercase"
                maxlength="50"
              >
              <?php if(isset($form_errors['comboName'])): ?>
                <span class="error"><?php echo $form_errors['comboName'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
             <label class="control-label labelStyle">Combo numeric code (max 2 digits)</label>
              <input 
                type="text"
                class="form-control noEnterKey"
                name="comboNumber"
                id="comboNumber"
                value="<?php echo $combo_number ?>"
                style="border:1px dashed; color: #225992;text-transform:uppercase"
                maxlength="2"
              >
              <?php if(isset($form_errors['comboNumber'])): ?>
                <span class="error"><?php echo $form_errors['comboNumber'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-hover font12">
              <tbody>
                <tr>
                  <td class="labelStyle" colspan="3" align="left">Add Combo products below. You can add upto 12 products to a Combo.</td>
                </tr>
                <?php 
                  $cntr = 0;
                  for($i=0;$i<4;$i++): 
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
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
             <label class="control-label  labelStyle">Combo price for 1 unit</label>
              <input 
                type="text"
                class="form-control noEnterKey"
                name="comboPrice1"
                id="comboPrice1"
                value="<?php echo $combo_price1 ?>"
                maxlength="20"
              >
              <?php if(isset($form_errors['comboPrice1'])): ?>
                <span class="error"><?php echo $form_errors['comboPrice1'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
             <label class="control-label  labelStyle">Combo price for 2 units</label>
              <input 
                type="text"
                class="form-control noEnterKey"
                name="comboPrice2"
                id="comboPrice2"
                value="<?php echo $combo_price2 ?>"
                maxlength="20"
              >
              <?php if(isset($form_errors['comboPrice2'])): ?>
                <span class="error"><?php echo $form_errors['comboPrice2'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
             <label class="control-label  labelStyle">Combo price for 3 units</label>
              <input 
                type="text"
                class="form-control noEnterKey"
                name="comboPrice3"
                id="comboPrice3"
                value="<?php echo $combo_price3 ?>"
                maxlength="20"
              >
              <?php if(isset($form_errors['comboPrice3'])): ?>
                <span class="error"><?php echo $form_errors['comboPrice3'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
             <label class="control-label  labelStyle">Combo price for 4 units</label>
              <input 
                type="text"
                class="form-control noEnterKey"
                name="comboPrice4"
                id="comboPrice4"
                value="<?php echo $combo_price4 ?>"
                maxlength="20"
              >
              <?php if(isset($form_errors['comboPrice4'])): ?>
                <span class="error"><?php echo $form_errors['comboPrice4'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
             <label class="control-label  labelStyle">Combo price for 5 units</label>
              <input 
                type="text"
                class="form-control noEnterKey"
                name="comboPrice5"
                id="comboPrice5"
                value="<?php echo $combo_price5 ?>"
                maxlength="20"
              >
              <?php if(isset($form_errors['comboPrice5'])): ?>
                <span class="error"><?php echo $form_errors['comboPrice5'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
             <label class="control-label  labelStyle">Combo price for 6 units</label>
              <input 
                type="text"
                class="form-control noEnterKey"
                name="comboPrice6"
                id="comboPrice6"
                value="<?php echo $combo_price6 ?>"
                maxlength="20"
              >
              <?php if(isset($form_errors['comboPrice6'])): ?>
                <span class="error"><?php echo $form_errors['comboPrice6'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
             <label class="control-label  labelStyle">Combo price for 7 units</label>
              <input 
                type="text"
                class="form-control noEnterKey"
                name="comboPrice7"
                id="comboPrice7"
                value="<?php echo $combo_price7 ?>"
                maxlength="20"
              >
              <?php if(isset($form_errors['comboPrice7'])): ?>
                <span class="error"><?php echo $form_errors['comboPrice7'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
             <label class="control-label  labelStyle">Combo price for 8 units</label>
              <input 
                type="text"
                class="form-control noEnterKey"
                name="comboPrice8"
                id="comboPrice8"
                value="<?php echo $combo_price8 ?>"
                maxlength="20"
              >
              <?php if(isset($form_errors['comboPrice8'])): ?>
                <span class="error"><?php echo $form_errors['comboPrice8'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
             <label class="control-label  labelStyle">Combo price for 9 units</label>
              <input 
                type="text"
                class="form-control noEnterKey"
                name="comboPrice9"
                id="comboPrice9"
                value="<?php echo $combo_price9 ?>"
                maxlength="20"
              >
              <?php if(isset($form_errors['comboPrice9'])): ?>
                <span class="error"><?php echo $form_errors['comboPrice9'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
             <label class="control-label  labelStyle">Combo price for 10 units</label>
              <input 
                type="text"
                class="form-control noEnterKey"
                name="comboPrice10"
                id="comboPrice10"
                value="<?php echo $combo_price10 ?>"
                maxlength="20"
              >
              <?php if(isset($form_errors['comboPrice4=10'])): ?>
                <span class="error"><?php echo $form_errors['comboPrice10'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
             <label class="control-label  labelStyle">Combo price for 11 units</label>
              <input 
                type="text"
                class="form-control noEnterKey"
                name="comboPrice11"
                id="comboPrice11"
                value="<?php echo $combo_price11 ?>"
                maxlength="20"
              >
              <?php if(isset($form_errors['comboPrice11'])): ?>
                <span class="error"><?php echo $form_errors['comboPrice11'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
             <label class="control-label labelStyle">Combo price 12 units</label>
              <input
                type="text"
                class="form-control noEnterKey"
                name="comboPrice12"
                id="comboPrice12"
                value="<?php echo $combo_price12 ?>"
                maxlength="20"
              >
              <?php if(isset($form_errors['comboPrice12'])): ?>
                <span class="error"><?php echo $form_errors['comboPrice12'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
             <label class="control-label  labelStyle">Combo price for 13 units</label>
              <input 
                type="text"
                class="form-control noEnterKey"
                name="comboPrice13"
                id="comboPrice13"
                value="<?php echo $combo_price13 ?>"
                maxlength="20"
              >
              <?php if(isset($form_errors['comboPrice13'])): ?>
                <span class="error"><?php echo $form_errors['comboPrice13'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
             <label class="control-label  labelStyle">Combo price for 14 units</label>
              <input 
                type="text"
                class="form-control noEnterKey"
                name="comboPrice14"
                id="comboPrice14"
                value="<?php echo $combo_price14 ?>"
                maxlength="20"
              >
              <?php if(isset($form_errors['comboPrice14'])): ?>
                <span class="error"><?php echo $form_errors['comboPrice14'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
             <label class="control-label labelStyle">Combo price 15 units</label>
              <input
                type="text"
                class="form-control noEnterKey"
                name="comboPrice15"
                id="comboPrice15"
                value="<?php echo $combo_price15 ?>"
                maxlength="20"
              >
              <?php if(isset($form_errors['comboPrice15'])): ?>
                <span class="error"><?php echo $form_errors['comboPrice15'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label labelStyle">Status</label>
              <div class="select-wrap">
                <select class="form-control" name="status" id="status">
                  <?php 
                    foreach($status_a as $key => $value): 
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

<?php
/*
            
*/?>