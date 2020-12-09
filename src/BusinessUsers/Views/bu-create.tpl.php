<?php
  
  if(isset($submitted_data['userType']) && $submitted_data['userType'] !== '' ) {
    $bu_type = $submitted_data['userType'];
  } else {
    $bu_type = '';
  }
  if(isset($submitted_data['userName']) && $submitted_data['userName'] !== '' ) {
    $customer_name = $submitted_data['userName'];
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
    $country_id = '';
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
  if(isset($submitted_data['locationID']) && isset($location_ids[$submitted_data['locationID']]) ) {
    $location_code = $location_codes[$submitted_data['locationID']];
  } else {
    $location_code = '';
  }
  if( isset($submitted_data['showAllCustomers']) ) {
    $show_all_customers = $submitted_data['showAllCustomers'];
  } else {
    $show_all_customers = '1';
  } 
  if( isset($submitted_data['status']) ) {
    $status = $submitted_data['status'];
  } else {
    $status = 1;
  }  
?>
<!-- Basic form starts -->
<div class="row">
  <div class="col-lg-12"> 
    <!-- Panel starts -->
    <section class="panel">
      <h2 class="hdg-reports text-center">Create Business User</h2>
      <div class="panel-body">
        <?php echo $flash_obj->print_flash_message(); ?>        
        <?php if($page_error !== ''): ?>
          <div class="alert alert-danger" role="alert">
            <strong>Error!</strong> <?php echo $page_error ?> 
          </div>
        <?php elseif($page_success !== ''): ?>
          <div class="alert alert-success" role="alert">
            <strong>Success!</strong> <?php echo $page_success ?> 
          </div>
        <?php endif; ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/bu/list" class="btn btn-default">
              <i class="fa fa-book"></i> Business Users List
            </a>
          </div>
        </div>
        <form class="form-validate form-horizontal" method="POST" autocomplete="off" id="buForm">
          <div class="form-group">
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">User type</label>
              <div class="select-wrap">
                <select class="form-control" name="userType" id="userType">
                  <?php 
                    foreach($bu_types as $key=>$value):
                      if($bu_type === $key) {
                        $selected = 'selected="selected"';
                      } else {
                        $selected = '';
                      }                      
                  ?>
                    <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <?php if(isset($errors['userType'])): ?>
                <span class="error"><?php echo $errors['userType'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">User name</label>
              <input type="text" class="form-control" name="userName" id="userName" value="<?php echo $customer_name ?>" />
              <?php if(isset($errors['userName'])): ?>
                <span class="error"><?php echo $errors['userName'] ?></span>
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
          </div>
          <div class="form-group">
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
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Pincode</label>
              <input type="text" class="form-control" name="pincode" id="pincode"
              value="<?php echo $pincode ?>"              
              >
              <?php if(isset($errors['pincode'])): ?>
                <span class="error"><?php echo $errors['pincode'] ?></span>
              <?php endif; ?>              
            </div>
          </div>
          <div class="form-group">
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
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Store name (optional)</label>
              <div class="select-wrap">
                <select class="form-control" name="locationCode" id="locationCode">
                  <?php 
                    foreach($client_locations as $key=>$value): 
                      if($location_code === $key) {
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
              <?php if(isset($form_errors['locationCode'])): ?>
                <span class="error"><?php echo $form_errors['locationCode'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Status</label>
              <div class="select-wrap">
                <select class="form-control" name="status" id="status">
                  <?php 
                    foreach($status_a as $key=>$value): 
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
            </div>            
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Customer data</label>
              <div class="select-wrap">
                <select class="form-control" name="showAllCustomers" id="showAllCustomers">
                  <?php 
                    foreach($show_flag_a as $key=>$value): 
                      if((int)$show_all_customers === (int)$key) {
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
              <?php if(isset($form_errors['showAllCustomers'])): ?>
                <span class="error"><?php echo $form_errors['showAllCustomers'] ?></span>
              <?php endif; ?>
            </div>            
          </div><br />
          <div class="text-center">
            <button class="btn btn-success" id="Save"><i class="fa fa-save"></i> Save</button>
          </div>
        </form>
      </div>
    </section>
  </div>
</div>