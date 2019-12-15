<?php
  use Atawa\Utilities;

  // dump($submitted_data);
  // exit;

  if(isset($submitted_data['locationName']) && $submitted_data['locationName'] !== '' ) {
    $location_name = $submitted_data['locationName'];
  } else {
    $location_name = '';
  }
  if(isset($submitted_data['locationNameShort']) && $submitted_data['locationNameShort'] !== '' ) {
    $location_name_short = $submitted_data['locationNameShort'];
  } else {
    $location_name_short = '';
  }  
  if(isset($submitted_data['pincode']) && $submitted_data['pincode'] !== '' ) {
    $pincode = $submitted_data['pincode'];
  } else {
    $pincode = '';
  }
  if(isset($submitted_data['phone']) && $submitted_data['phone'] !== '' ) {
    $phone = $submitted_data['phone'];
  } else {
    $phone = '';
  }
  if(isset($submitted_data['locGstNo']) && $submitted_data['locGstNo'] !== '' ) {
    $gst_no = $submitted_data['locGstNo'];
  } else {
    $gst_no = '';
  }
  if(isset($submitted_data['countryID']) && $submitted_data['countryID'] !== '' ) {
    $country_id = $submitted_data['countryID'];
  } else {
    $country_id = '';
  }
  if(isset($submitted_data['stateID']) && $submitted_data['stateID'] !== '' ) {
    $state_id = $submitted_data['stateID'];
  } else {
    $state_id = '';
  }
  if(isset($submitted_data['cityName']) && $submitted_data['cityName'] !== '' ) {
    $city_name = $submitted_data['cityName'];
  } else {
    $city_name = '';
  }
  if(isset($submitted_data['address1']) && $submitted_data['address1'] !== '' ) {
    $address1 = $submitted_data['address1'];
  } else {
    $address1 = '';
  }
  if(isset($submitted_data['address2']) && $submitted_data['address2'] !== '' ) {
    $address2 = $submitted_data['address2'];
  } else {
    $address2 = '';
  }
  if(isset($submitted_data['smsSenderID']) && $submitted_data['smsSenderID'] !== '' ) {
    $sms_sender_id = $submitted_data['smsSenderID'];
  } else {
    $sms_sender_id = '';
  }
  if(isset($submitted_data['smsCompanyShortName']) && $submitted_data['smsCompanyShortName'] !== '' ) {
    $sms_company_short_name = $submitted_data['smsCompanyShortName'];
  } else {
    $sms_company_short_name = ''; 
  }
  if(isset($submitted_data['smsCompanyShortName']) && $submitted_data['smsCompanyShortName'] !== '' ) {
    $sms_company_short_name = $submitted_data['smsCompanyShortName'];
  } else {
    $sms_company_short_name = ''; 
  }  
  if(isset($submitted_data['allowMrpEditing']) && $submitted_data['allowMrpEditing'] !== '' ) {
    $allow_mrp_editing = (int)$submitted_data['allowMrpEditing'];
  } else {
    $allow_mrp_editing = 0; 
  }
  if(isset($submitted_data['allowManualDiscount']) && $submitted_data['allowManualDiscount'] !== '' ) {
    $allow_man_discount = (int)$submitted_data['allowManualDiscount'];
  } else {
    $allow_man_discount = 1; 
  }  
  if(isset($submitted_data['bankCode']) && $submitted_data['bankCode'] !== '' ) {
    $bank_code = $submitted_data['bankCode'];
  } else {
    $bank_code = ''; 
  }
  if(isset($submitted_data['tacB2B']) && $submitted_data['tacB2B'] !== '' ) {
    $tac_b2b = $submitted_data['tacB2B'];
  } else {
    $tac_b2b = ''; 
  }
  if(isset($submitted_data['tacB2C']) && $submitted_data['tacB2C'] !== '' ) {
    $tac_b2c = $submitted_data['tacB2C'];
  } else {
    $tac_b2c = ''; 
  }  
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panel">
      <?php /*<h2 class="hdg-reports text-center">Create Store</h2> */?>
      <div class="panel-body">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/locations/list" class="btn btn-default">
              <i class="fa fa-location-arrow"></i> Stores List 
            </a> 
          </div>
        </div>
        <form class="form-validate form-horizontal" method="POST">
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Location name (only digits and alphabets allowed)</label>
              <input
                type="text" 
                class="form-control" 
                name="locationName" 
                id="locationName"
                value="<?php echo $location_name ?>"
                maxlength="50"
              >
              <?php if(isset($form_errors['locationName'])): ?>
                <span class="error"><?php echo $form_errors['locationName'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Address1</label>
              <input 
                type="text" 
                class="form-control" 
                name="address1"
                id="address1" 
                value="<?php echo $address1 ?>"
                maxlength="100"
              >
              <?php if(isset($form_errors['address1'])): ?>
                <span class="error"><?php echo $form_errors['address1'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Address2</label>
              <input 
                type="text" 
                class="form-control" 
                name="address2"
                id="address2" 
                value="<?php echo $address2 ?>"
                maxlength="100"
              >
              <?php if(isset($form_errors['address2'])): ?>
                <span class="error"><?php echo $form_errors['address2'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Country name</label>
              <div class="select-wrap">
                <select class="form-control" name="countryID" id="countryID">
                  <?php 
                    foreach($countries as $key=>$value):
                      if((int)$country_id === (int)$key) {
                        $selected = 'selected="selected"';
                      } else {
                        $selected = '';
                      } 
                  ?>
                    <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                  <?php endforeach; ?>
                </select>
                <?php if(isset($form_errors['countryID'])): ?>
                  <span class="error"><?php echo $form_errors['countryID'] ?></span>
                <?php endif; ?>                 
              </div>              
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">State</label>
              <select class="form-control" name="stateID" id="stateID">
                <?php 
                  foreach($states as $key=>$value): 
                    if((int)$state_id === (int)$key) {
                      $selected = 'selected="selected"';
                    } else {
                      $selected = '';
                    }
                ?>
                  <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                <?php endforeach; ?>              
              </select>
              <?php if(isset($form_errors['stateID'])): ?>
                <span class="error"><?php echo $form_errors['stateID'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">City name</label>
              <input 
                type="text" 
                class="form-control" 
                name="cityName" 
                id="cityName" 
                value="<?php echo $city_name ?>"
              >
              <?php if(isset($form_errors['cityName'])): ?>
                <span class="error"><?php echo $form_errors['cityName'] ?></span>
              <?php endif; ?>   
            </div>    
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Pincode</label>
              <input 
                type="text" 
                class="form-control" 
                name="pincode" 
                id="pincode"
                value="<?php echo $pincode ?>"              
              >
              <?php if(isset($form_errors['pincode'])): ?>
                <span class="error"><?php echo $form_errors['pincode'] ?></span>
              <?php endif; ?>              
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Phone</label>
              <input type="text" class="form-control" name="phone" id="phone"
              value="<?php echo $phone ?>"              
              >
              <?php if(isset($form_errors['phone'])): ?>
                <span class="error"><?php echo $form_errors['phone'] ?></span>
              <?php endif; ?>                
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">GST no.</label>
              <input 
                type="text" 
                class="form-control" 
                name="locGstNo" 
                id="locGstNo"
                value="<?php echo $gst_no ?>"      
              >
              <?php if(isset($form_errors['locGstNo'])): ?>
                <span class="error"><?php echo $form_errors['locGstNo'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label" style="font-size:14px;color:#2E1114;font-weight:bold;">Location short name (Printed on Invoices)</label>
              <input
                type="text" 
                class="form-control" 
                name="locationNameShort" 
                id="locationNameShort"
                value="<?php echo $location_name_short ?>"
                maxlength="20"
              >
              <?php if(isset($form_errors['locationNameShort'])): ?>
                <span class="error"><?php echo $form_errors['locationNameShort'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">
                <span style="font-size:14px;color:#2E1114;font-weight:bold;"><i class="fa fa-mobile" aria-hidden="true"></i>&nbsp;SMS Sender ID (Max. 6 chars)</span>
              </label>
              <input
                type="text"
                class="form-control"
                name="smsSenderID"
                id="smsSenderID"
                value="<?php echo $sms_sender_id ?>"
                maxlength="6"
              >
              <?php if(isset($form_errors['smsSenderID'])): ?>
                <span class="error"><?php echo $form_errors['smsSenderID'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">
                <span style="font-size:14px;color:#2E1114;font-weight:bold;"><i class="fa fa-mobile" aria-hidden="true"></i>&nbsp;Company Name in SMS (Max. 20 Chars.)</span>
              </label>
              <input
                type="text" 
                class="form-control"
                name="smsCompanyShortName"
                id="smsCompanyShortName"
                value="<?php echo $sms_company_short_name ?>"
                maxlength="20"
              >
              <?php if(isset($form_errors['smsCompanyShortName'])): ?>
                <span class="error"><?php echo $form_errors['smsCompanyShortName'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">
                <span style="font-size:14px;color:#2E1114;font-weight:bold;"><i class="fa fa-inr" aria-hidden="true"></i>&nbsp;Allow MRP Editing?</span>
              </label>
              <select class="form-control" name="mrpEditing" id="mrpEditing">
                <?php 
                  foreach($mrp_editing_a as $key=>$value):
                    if($allow_mrp_editing === (int)$key) {
                      $selected = 'selected = "selected"';
                    } else {
                      $selected = '';
                    }
                ?>
                  <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                <?php endforeach; ?>
              </select>
              <?php if(isset($errors['mrpEditing'])): ?>
                <span class="error"><?php echo $errors['mrpEditing'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">
                <span style="font-size:14px;color:#2E1114;font-weight:bold;"><i class="fa fa-inr" aria-hidden="true"></i>&nbsp;Allow Manual Sales Discount?</span>
              </label>
              <select class="form-control" name="allowManualDiscount" id="allowManualDiscount">
                <?php 
                  foreach($mrp_editing_a as $key=>$value):
                    if($allow_man_discount === (int)$key) {
                      $selected = 'selected = "selected"';
                    } else {
                      $selected = '';
                    }
                ?>
                  <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                <?php endforeach; ?>
              </select>
              <?php if(isset($errors['allowManualDiscount'])): ?>
                <span class="error"><?php echo $errors['allowManualDiscount'] ?></span>
              <?php endif; ?>
            </div>            
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">
                <span style="font-size:14px;color:#2E1114;font-weight:bold;">Choose a Bank to print details on B2B Invoice</span>
              </label>
              <select class="form-control" name="bankCode" id="bankCode">
                <?php 
                  foreach($banks as $bank_key => $bank_name):
                    if($bank_code === $bank_key) {
                      $selected = 'selected = "selected"';
                    } else {
                      $selected = '';
                    }
                ?>
                  <option value="<?php echo $bank_key ?>" <?php echo $selected ?>><?php echo $bank_name ?></option>
                <?php endforeach; ?>
              </select>
              <?php if(isset($errors['bankCode'])): ?>
                <span class="error"><?php echo $errors['bankCode'] ?></span>
              <?php endif; ?>
            </div>            
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-6 col-lg-6 m-bot15">
              <label class="control-label">
                <span style="font-size:14px;color:#2E1114;font-weight:bold;">Terms &amp; Conditions on B2B Invoice (one per line)</span>
              </label>
              <textarea id="tacB2B" name="tacB2B" rows="5" cols="60"><?php echo $tac_b2b ?></textarea>
            </div>
            <div class="col-sm-12 col-md-6 col-lg-6 m-bot15">
              <label class="control-label">
                <span style="font-size:14px;color:#2E1114;font-weight:bold;">Terms &amp; Conditions on B2C Invoice (one per line)</span>
              </label>
              <textarea id="tacB2C" name="tacB2C" rows="5" cols="60"><?php echo $tac_b2c ?></textarea>
            </div>            
          </div>
          <div class="text-center">
            <button class="btn btn-success" id="Save">
              <i class="fa fa-save"></i> Save
            </button>
          </div>
        </form>
      </div>
    </section>
  </div>
</div>
