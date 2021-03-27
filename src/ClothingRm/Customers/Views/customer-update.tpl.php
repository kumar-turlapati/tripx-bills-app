<?php
  
  // dump($submitted_data);
  
  if(isset($submitted_data['locationID'])) {
    $location_id = $submitted_data['locationID'];
  } else {
    $location_id = 0;
  }
  if(isset($submitted_data['customerType']) && $submitted_data['customerType'] !== '' ) {
    $customer_type = $submitted_data['customerType'] === 'b' ? 'b2b' : 'b2c';
    $bio_container_style = ' style="display:none;"';
  } else {
    $bio_container_style = '';
    $customer_type = 'b2c';
  }
  if(isset($submitted_data['customerName']) && $submitted_data['customerName'] !== '' ) {
    $customer_name = $submitted_data['customerName'];
  } else {
    $customer_name = '';
  }
  if(isset($submitted_data['mobileNo']) && $submitted_data['mobileNo'] !== '' ) {
    $mobile_no = $submitted_data['mobileNo'];
  } else {
    $mobile_no = '';
  }
  if(isset($submitted_data['countryID']) && $submitted_data['countryID'] !== '' ) {
    $country_id = $submitted_data['countryID'];
  } else {
    $country_id = 99;
  }
  if(isset($submitted_data['stateID']) && $submitted_data['stateID'] !== '' ) {
    $state_id = $submitted_data['stateID'];
  } else {
    $state_id = $client_business_state;
  }
  if(isset($submitted_data['cityName']) && $submitted_data['cityName'] !== '' ) {
    $city_name = $submitted_data['cityName'];
  } else {
    $city_name = '';
  }
  if(isset($submitted_data['address']) && $submitted_data['address'] !== '' ) {
    $address = $submitted_data['address'];
  } else {
    $address = '';
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
  if(isset($submitted_data['gstNo']) && $submitted_data['gstNo'] !== '' ) {
    $gst_no = $submitted_data['gstNo'];
  } else {
    $gst_no = '';
  }
  if(isset($submitted_data['maExecutive']) && $submitted_data['maExecutive'] !== '') {
    $mexe_code = $submitted_data['maExecutive'];
  } else {
    $mexe_code = '';
  }
  if(isset($submitted_data['agentCode']) && $submitted_data['agentCode'] !== '') {
    $agent_code = $submitted_data['agentCode'];
  } else {
    $agent_code = '';
  }
  if(isset($submitted_data['status']) && $submitted_data['status'] !== '' ) {
    $status = (int)$submitted_data['status'];
  } else {
    $status = 0;
  }  
  $customer_rating = isset($submitted_data['customerRating']) ? $submitted_data['customerRating'] : 0;

  # bio details
  if(isset($submitted_data['age']) && $submitted_data['age'] !== '' ) {
    $age = $submitted_data['age'];
  } else {
    $age = '';
  }
  if(isset($submitted_data['ageCategory']) && $submitted_data['ageCategory'] !== '' ) {
    $age_category = $submitted_data['ageCategory'];
  } else {
    $age_category = '';
  }
  if(isset($submitted_data['gender']) && $submitted_data['gender'] !== '' ) {
    $gender = $submitted_data['gender'];
  } else {
    $gender = '';
  }
  if(isset($submitted_data['dateOfBirth']) && $submitted_data['dateOfBirth'] !== '0000-00-00' && $submitted_data['dateOfBirth'] !== '') {
    $dob = date("d-m-Y", strtotime($submitted_data['dateOfBirth']));
  } else {
    $dob = '';
  }
  if(isset($submitted_data['dateOfMarriage']) && $submitted_data['dateOfMarriage'] !== '0000-00-00' && $submitted_data['dateOfMarriage'] !== '') {
    $dor = date("d-m-Y", strtotime($submitted_data['dateOfMarriage']));
  } else {
    $dor = '';
  }
  if(isset($submitted_data['uuid']) && $submitted_data['uuid'] !== '') {
    $uuid = $submitted_data['uuid'];
  } else {
    $uuid = '';
  }
  if(isset($submitted_data['whatsappOptin']) && $submitted_data['whatsappOptin'] !== '') {
    $whatsapp_optin = $submitted_data['whatsappOptin'];
  } else {
    $whatsapp_optin = 0;
  }
  if(isset($submitted_data['whatsappNumbers']) && $submitted_data['whatsappNumbers'] !== '') {
    $whatsapp_numbers = $submitted_data['whatsappNumbers'];
  } else {
    $whatsapp_numbers = '';
  }
  $executive_id = isset($submitted_data['maExecutive']) ? $submitted_data['maExecutive'] : '';
  $yes_no_options = [0=>'No',1=>'Yes'];
  $status_options = [0=>'Inactive',1=>'Active'];
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panel">
      <div class="panel-body">
        <?php echo $flash_obj->print_flash_message(); ?>        
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/customers/list" class="btn btn-default">
              <i class="fa fa-book"></i> Customers List
            </a>
          </div>
        </div>
        <form class="form-validate form-horizontal" method="POST" id="customerForm" autocomplete="off">
          <div class="form-group">
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Customer type</label>
              <div class="select-wrap">
                <select class="form-control" name="customerType" id="customerType">
                  <?php 
                    foreach($customer_types as $key=>$value):
                      if($customer_type === $key) {
                        $selected = 'selected="selected"';
                      } else {
                        $selected = '';
                      }                      
                  ?>
                    <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                  <?php endforeach; ?>
                </select>
                <?php if(isset($errors['customerType'])): ?>
                  <span class="error"><?php echo $errors['customerType'] ?></span>
                <?php endif; ?>                 
              </div>
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Customer name</label>
              <input type="text" class="form-control" name="customerName" id="customerName" value="<?php echo $customer_name ?>" />
              <?php if(isset($errors['customerName'])): ?>
                <span class="error"><?php echo $errors['customerName'] ?></span>
              <?php endif; ?>              
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Mobile number</label>
              <input type="text" class="form-control" name="mobileNo" id="mobileNo" maxlength="10" value="<?php echo $mobile_no ?>">
              <?php if(isset($errors['mobileNo'])): ?>
                <span class="error"><?php echo $errors['mobileNo'] ?></span>
              <?php endif; ?> 
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">GST No.</label>
              <input 
                type="text" 
                class="form-control" 
                name="gstNo" 
                id="gstNo"
                value="<?php echo $gst_no ?>"      
              >
              <?php if(isset($errors['gstNo'])): ?>
                <span class="error"><?php echo $errors['gstNo'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Country name</label>
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
                <?php if(isset($errors['countryID'])): ?>
                  <span class="error"><?php echo $errors['countryID'] ?></span>
                <?php endif; ?>                 
              </div>              
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">State</label>
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
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">City name</label>
              <input type="text" class="form-control" name="cityName" id="cityName" value="<?php echo $city_name ?>" />
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Address</label>
              <input 
                type="text" 
                class="form-control" 
                name="address" 
                id="address"
                value="<?php echo $address ?>"      
              >
              <?php if(isset($errors['address'])): ?>
                <span class="error"><?php echo $errors['address'] ?></span>
              <?php endif; ?>
            </div>             
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Pincode</label>
              <input type="text" class="form-control" name="pincode" id="pincode"
              value="<?php echo $pincode ?>"              
              >
              <?php if(isset($errors['pincode'])): ?>
                <span class="error"><?php echo $errors['pincode'] ?></span>
              <?php endif; ?>              
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Phone</label>
              <input type="text" class="form-control" name="phone" id="phone"
              value="<?php echo $phone ?>"              
              >
              <?php if(isset($errors['phone'])): ?>
                <span class="error"><?php echo $errors['phone'] ?></span>
              <?php endif; ?>                
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Store / Location name</label>
              <div class="select-wrap">
                <select class="form-control" name="locationCode" id="locationCode">
                  <?php 
                    foreach($client_locations as $location_key => $value):
                      $location_key_a = explode('`', $location_key);
                      if((int)$location_id === (int)$location_key_a[1]) {
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
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Customer rating</label>
              <div class="select-wrap">                        
                <select 
                  class="form-control"
                  id="customerRating" 
                  name="customerRating"
                  style="font-size:12px;"
                >
                  <?php
                    foreach($customer_ratings as $key => $value):
                      if($key === (int)$customer_rating) {
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
            <div class="col-sm-12 col-md-4 col-lg-3">
              <label class="control-label labelStyle">Marketing executive name</label>
              <div class="select-wrap">                        
                <select 
                  class="form-control"
                  id="maExecutive" 
                  name="maExecutive"
                  style="font-size:12px;"
                >
                  <?php
                    foreach($ma_executives as $key=>$value):
                      if($key === $mexe_code) {
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
            <div class="col-sm-12 col-md-4 col-lg-3">
              <label class="control-label labelStyle">Agent / Referral name</label>
              <div class="select-wrap">                        
                <select 
                  class="form-control"
                  id="agentCode" 
                  name="agentCode"
                  style="font-size:12px;"
                >
                  <?php
                    foreach($agents as $key => $value):
                      if($key === $agent_code) {
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
            <div class="col-sm-12 col-md-4 col-lg-3">
              <label class="control-label labelStyle">Push messages on Whatsapp?</label>
              <div class="select-wrap">                        
                <select 
                  class="form-control"
                  id="whatsappOptin" 
                  name="whatsappOptin"
                  style="font-size:12px;"
                >
                  <?php
                    foreach($yes_no_options as $key => $value):
                      if((int)$key === (int)$whatsapp_optin) {
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
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Whatsapp numbers (use , for multiple)</label>
              <input 
                type="text" 
                class="form-control" 
                name="whatsappNumbers" 
                id="whatsappNumbers"
                value="<?php echo $whatsapp_numbers ?>"              
              >
              <?php if(isset($errors['whatsappNumbers'])): ?>
                <span class="error"><?php echo $errors['whatsappNumbers'] ?></span>
              <?php endif; ?>                
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-3 m-bot15">
              <label class="control-label labelStyle">Status</label>
              <div class="select-wrap">                        
                <select 
                  class="form-control"
                  id="whatsappConsent" 
                  name="whatsappConsent"
                  style="font-size:12px;"
                >
                  <?php
                    foreach($status_options as $key => $value):
                      if($key === $status) {
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
          <div id="bioContainer" <?php echo $bio_container_style ?>>
            <h3 class="hdg-reports text-left" style="color:#000;border-bottom: 1px solid #000;padding:10px 0 10px 0;">Personal Details</h3>
            <div class="form-group">
              <div class="col-sm-12 col-md-3 col-lg-3 m-bot15">
                <label class="control-label labelStyle">Customer age</label>
                <div class="row">
                  <div class="col-sm-6 col-md-6 col-lg-6">
                    <div class="select-wrap">
                      <select class="form-control" name="age" id="age">
                        <?php 
                          foreach($ages as $key=>$value):
                            if((int)$age === (int)$key) {
                              $selected = 'selected="selected"';
                            } else {
                              $selected = '';
                            }                          
                        ?>
                          <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                        <?php endforeach; ?>
                      </select>
                      <?php if(isset($errors['age'])): ?>
                        <span class="error"><?php echo $errors['age'] ?></span>
                      <?php endif; ?>                    
                    </div>
                  </div>
                  <div class="col-sm-6 col-md-5 col-lg-5"> 
                    <div class="select-wrap">
                      <select class="form-control" name="ageCategory" id="ageCategory">
                        <?php 
                          foreach($age_categories as $key=>$value):
                            if($age_category === $key) {
                              $selected = 'selected="selected"';
                            } else {
                              $selected = '';
                            }                           
                        ?>
                          <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                        <?php endforeach; ?>
                      </select>
                      <?php if(isset($errors['ageCategory'])): ?>
                        <span class="error"><?php echo $errors['ageCategory'] ?></span>
                      <?php endif; ?>                     
                    </div> 
                  </div>
                </div>
              </div>
              <div class="col-sm-12 col-md-3 col-lg-3 m-bot15">
                <label class="control-label labelStyle">Gender</label>
                <div class="select-wrap">
                  <select class="form-control" name="gender" id="gender">
                    <?php 
                      foreach($genders as $key=>$value):
                        if($gender === $key) {
                          $selected = 'selected="selected"';
                        } else {
                          $selected = '';
                        }                      
                    ?>
                      <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                    <?php endforeach; ?>
                  </select>
                  <?php if(isset($errors['gender'])): ?>
                    <span class="error"><?php echo $errors['gender'] ?></span>
                  <?php endif; ?>                 
                </div>
              </div>
              <div class="col-sm-12 col-md-3 col-lg-3 m-bot15">
                <label class="control-label labelStyle">Date of birth</label>
                <div class="form-group">
                  <div class="col-lg-12">
                    <div class="input-append date" data-date="<?php echo $dob ?>" data-date-format="dd-mm-yyyy">
                      <input class="span2" value="<?php echo $dob ?>" size="16" type="text" readonly name="dob" id="dob" />
                      <span class="add-on"><i class="fa fa-calendar"></i></span>
                    </div>
                    <?php if(isset($errors['dob'])): ?>
                      <span class="error"><?php echo $errors['dob'] ?></span>
                    <?php endif; ?>                  
                  </div>
                </div>
              </div>
              <div class="col-sm-12 col-md-3 col-lg-3 m-bot15">
                <label class="control-label labelStyle">Date of marriage</label>
                <div class="form-group">
                  <div class="col-lg-12">
                    <div class="input-append date" data-date="<?php echo $dor ?>" data-date-format="dd-mm-yyyy">
                      <input class="span2" value="<?php echo $dor ?>" size="16" type="text" readonly name="dor" id="dor" />
                      <span class="add-on"><i class="fa fa-calendar"></i></span>
                    </div>
                    <?php if(isset($errors['dor'])): ?>
                      <span class="error"><?php echo $errors['dor'] ?></span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="text-center">
            <button class="btn btn-success" id="Save">
              <i class="fa fa-save"></i> Update
            </button>           
          </div>
        </form>
      </div>
    </section>
  </div>
</div>