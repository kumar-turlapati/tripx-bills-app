<?php
  use Atawa\Utilities;

  // dump($form_errors);

  # process form data
  $lead_date = isset($form_data['leadDate']) && $form_data['leadDate'] !== '' ? $form_data['leadDate'] : date("d/m/Y");
  $lead_owner_id = '';
  $business_name = isset($form_data['businessName']) && $form_data['businessName'] !== '' ? $form_data['businessName'] : '';
  $first_name = isset($form_data['firstName']) && $form_data['firstName'] !== '' ? $form_data['firstName'] : '';
  $last_name = isset($form_data['lastName']) && $form_data['lastName'] !== '' ? $form_data['lastName'] : '';
  $title = isset($form_data['title']) && $form_data['title'] !== '' ? $form_data['title'] : ''; 
  $lead_industry_id = isset($form_data['leadIndustryId']) && $form_data['leadIndustryId'] !== '' ? $form_data['leadIndustryId'] : '';
  $lead_emprange_id = isset($form_data['leadEmpRangeId']) && $form_data['leadEmpRangeId'] !== '' ? $form_data['leadEmpRangeId'] : '';
  $email = isset($form_data['email']) && $form_data['email'] !== '' ? $form_data['email'] : '';
  $secondary_email = isset($form_data['secondaryEmail']) && $form_data['secondaryEmail'] !== '' ? $form_data['secondaryEmail'] : '';
  $phone = isset($form_data['phone']) && $form_data['phone'] !== '' ? $form_data['phone'] : '';
  $fax = isset($form_data['fax']) && $form_data['fax'] !== '' ? $form_data['fax'] : '';
  $mobile = isset($form_data['mobile']) && $form_data['mobile'] !== '' ? $form_data['mobile'] : '';
  $website = isset($form_data['website']) && $form_data['website'] !== '' ? $form_data['website'] : '';
  $skype_id = isset($form_data['skypeId']) && $form_data['skypeId'] !== '' ? $form_data['skypeId'] : '';
  $address = isset($form_data['address']) && $form_data['address'] !== '' ? $form_data['address'] : '';
  $city_name = isset($form_data['cityName']) && $form_data['cityName'] !== '' ? $form_data['cityName'] : ''; 
  $state_name = isset($form_data['stateName']) && $form_data['stateName'] !== '' ? $form_data['stateName'] : '';
  $country_name = isset($form_data['countryName']) && $form_data['countryName'] !== '' ? $form_data['countryName'] : '';
  $pincode = isset($form_data['pincode']) && $form_data['pincode'] !== '' ? $form_data['pincode'] : '';
  $lead_source = isset($form_data['leadSourceId']) && $form_data['leadSourceId'] !== '' ? $form_data['leadSourceId'] : '';
  $lead_status = isset($form_data['leadStatusId']) && $form_data['leadStatusId'] !== '' ? $form_data['leadStatusId'] : '';
  $lead_rating = isset($form_data['leadRatingId']) && $form_data['leadRatingId'] !== '' ? $form_data['leadRatingId'] : '';
  $email_optout = isset($form_data['emailOptOut']) && $form_data['emailOptOut'] !== '' ? $form_data['emailOptOut'] : '';
  $about = isset($form_data['about']) && $form_data['about'] !== '' ? $form_data['about'] : '';
?>
<div class="container">
  <h2 class="mainheading">Create Lead</h2>
  <form class="mainform" id="leadForm" method="POST" autocomplete="off">
    <div class="mainbodyhdr">
      <div class="col-sm-12 col-md-3">
        <h2 class="formsection">Business Details</h2>
      </div>
      <div class="col-sm-12 col-md-9">
        <div>
          <div class="col-xs-12 col-sm-6">
            <div class="formfld">
              <input
                type="text" 
                class="pickDate" 
                name="leadDate"
                id="leadDate"
                value="<?php echo $lead_date ?>"
              />
            </div>
          </div>
          <div class="col-xs-12 col-sm-6">
            <div class="formfld">
              <div class="selectwrp">
                <i class="fa fa-angle-down"></i>
                <select name="leadOwnerId">
                  <option value="">Select</option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xs-12">
      <div class="row">
        <div class="col-sm-12 col-md-3 leftFormMenu">
          <ul id="tabsList">
            <li><a href="#business-details" class="active" id="a_business-details">Business Details</a></li>
            <li><a href="#contact-details" id="a_contact-details">Contact Details</a></li>
            <li><a href="#address-details" id="a_address-details">Address Details</a></li>
            <li><a href="#lead-details" id="a_lead-details">Lead Details </a></li>
          </ul>
        </div>
        <div class="col-sm-12 col-md-9 rightFormMenu">
          <div class="eachsection active" id="business-details">
            <div class="col-xs-12 col-sm-12">
              <div class="formfld">
                <label>Business name</label>
                <input type="text" name="businessName" id="businessName" value="<?php echo $business_name ?>">
                <?php if(isset($form_errors['businessName'])): ?>

                <?php endif; ?>
              </div>
            </div>
            <div class="col-xs-12 col-sm-6">
              <div class="formfld">
                <label>First name</label>
                <input type="text" name="firstName" id="firstName" value="<?php echo $first_name ?>">
                <?php if(isset($form_errors['firstName'])): ?>
                
                <?php endif; ?>                
              </div>
            </div>
            <div class="col-xs-12 col-sm-6">
              <div class="formfld">
                <label>Last name</label>
                <input type="text" name="lastName" id="lastName" value="<?php echo $last_name ?>">
                <?php if(isset($form_errors['lastName'])): ?>
                
                <?php endif; ?>                
              </div>
            </div>
            <div class="col-xs-12 col-sm-12">
              <div class="formfld">
                <label>Title</label>
                <div class="selectwrp">
                  <input type="text" name="title" id="title" value="<?php echo $title ?>">
                  <?php if(isset($form_errors['title'])): ?>
                
                  <?php endif; ?>                   
                </div>
              </div>
            </div>
            <div class="col-xs-12 col-sm-6">
              <div class="formfld">
                <label>Industry</label>
                <div class="selectwrp">
                  <i class="fa fa-angle-down"></i>
                  <?php echo Utilities::render_dropdown('leadIndustryId', $industries_a, $lead_industry_id) ?>
                  <?php if(isset($form_errors['leadIndustryId'])): ?>
                
                  <?php endif; ?>                   
                </div>
              </div>
            </div>
            <div class="col-xs-12 col-sm-6">
              <div class="formfld">
                <label>Total employees</label>
                <div class="selectwrp">
                  <i class="fa fa-angle-down"></i>
                  <?php echo Utilities::render_dropdown('leadEmpRangeId', $emp_ranges_a, $lead_emprange_id) ?>
                  <?php if(isset($form_errors['leadEmpRangeId'])): ?>
                
                  <?php endif; ?>                   
                </div>
              </div>
            </div>
            <div class="clearfix"></div>
            <div class="formbottom">
              <button id="section-1" class="tabFlow">Next</button>
              <button id="cancel-1" class="cancelLeadForm">Cancel</button>
            </div>
          </div>
          <div class="eachsection" id="contact-details">
            <div class="col-xs-12 col-sm-6">
              <div class="formfld">
                <label>Email</label>
                <input type="text" name="email" id="email" value="<?php echo $email ?>">
                <?php if(isset($form_errors['email'])): ?>
                
                <?php endif; ?>                 
              </div>
            </div>
            <div class="col-xs-12 col-sm-6">
              <div class="formfld">
                <label>Secondary Email</label>
                <input type="text" name="secondaryEmail" id="secondaryEmail" value="<?php echo $secondary_email ?>">
                <?php if(isset($form_errors['secondaryEmail'])): ?>
                
                <?php endif; ?>                
              </div>
            </div>
            <div class="col-xs-12 col-sm-6">
              <div class="formfld">
                <label>Phone</label>
                <input type="text" name="phone" id="phone" value="<?php echo $phone ?>">
                <?php if(isset($form_errors['phone'])): ?>
                
                <?php endif; ?>                
              </div>
            </div>
            <div class="col-xs-12 col-sm-6">
              <div class="formfld">
                <label>Fax</label>
                <input type="text" name="fax" id="fax" value="<?php echo $fax ?>">
                <?php if(isset($form_errors['fax'])): ?>
                
                <?php endif; ?>                  
              </div>
            </div>
            <div class="col-xs-12 col-sm-6">
                <div class="formfld">
                  <label>Mobile</label>
                  <input type="text" name="mobile" id="mobile" value="<?php echo $mobile ?>">
                  <?php if(isset($form_errors['mobile'])): ?>
                
                  <?php endif; ?>                   
                </div>
            </div>
            <div class="col-xs-12 col-sm-6">
              <div class="formfld">
                <label>Website</label>
                <input type="text" name="website" id="website" value="<?php echo $website ?>">
                <?php if(isset($form_errors['website'])): ?>
                
                <?php endif; ?>                 
              </div>
            </div>
            <div class="col-xs-12 col-sm-6">
              <div class="formfld">
                <label>SkypeId</label>
                <input type="text" name="skypeId" id="skypeId" value="<?php echo $skype_id ?>">
                <?php if(isset($form_errors['skypeId'])): ?>
                
                <?php endif; ?>                
              </div>
            </div>
            <div class="clearfix"></div>
            <div class="formbottom">
              <button id="section-2" class="tabFlow">Next</button>
              <button id="cancel-2" class="cancelLeadForm">Cancel</button>
            </div>
          </div>
          <div class="eachsection" id="address-details">
            <div class="col-xs-12 col-sm-12">
              <div class="formfld">
                <label>Address</label>
                <input type="text" name="address" id="address" value="<?php echo $address ?>">
                <?php if(isset($form_errors['address'])): ?>
                
                <?php endif; ?>                
              </div>
            </div>
            <div class="col-xs-12 col-sm-6">
              <div class="formfld">
                <label>City name</label>
                <input type="text" name="cityName" id="cityName" value="<?php echo $city_name ?>">
                <?php if(isset($form_errors['cityName'])): ?>
                
                <?php endif; ?>                
              </div>
            </div>
            <div class="col-xs-12 col-sm-6">
              <div class="formfld">
                <label>State name</label>
                <input type="text" name="stateName" id="stateName" value="<?php echo $state_name ?>">
                <?php if(isset($form_errors['stateName'])): ?>
                
                <?php endif; ?>                
              </div>
            </div>
            <div class="col-xs-12 col-sm-6">
              <div class="formfld">
                <label>Country name</label>
                <input type="text" name="countryName" id="countryName" value="<?php echo $country_name ?>">
                <?php if(isset($form_errors['countryName'])): ?>
                
                <?php endif; ?>                
              </div>
            </div>
            <div class="col-xs-12 col-sm-6">
              <div class="formfld">
                <label>Pincode</label>
                <input type="text" name="pincode" id="pincode" value="<?php echo $pincode ?>">
                <?php if(isset($form_errors['pincode'])): ?>
                
                <?php endif; ?>
              </div>
            </div>
            <div class="clearfix"></div>
            <div class="formbottom">
              <button id="section-3" class="tabFlow">Next</button>
              <button id="cancel-3" class="cancelLeadForm">Cancel</button>
            </div>
          </div>
          <div class="eachsection" id="lead-details">
            <div class="col-xs-12 col-sm-4">
              <div class="formfld">
                <label>Lead source</label>
                <div class="selectwrp">
                  <i class="fa fa-angle-down"></i>
                  <?php echo Utilities::render_dropdown('leadSourceId', $lead_sources_a, $lead_source) ?>
                  <?php if(isset($form_errors['leadSourceId'])): ?>
                
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <div class="col-xs-12 col-sm-4">
              <div class="formfld">
                <label>Lead status</label>
                <div class="selectwrp">
                  <i class="fa fa-angle-down"></i>
                  <?php echo Utilities::render_dropdown('leadStatusId', $lead_status_a, $lead_status) ?>
                  <?php if(isset($form_errors['leadStatusId'])): ?>
                
                  <?php endif; ?>                   
                </div>
              </div>
            </div>
            <div class="col-xs-12 col-sm-4">
              <div class="formfld">
                <label>Lead rating</label>
                <div class="selectwrp">
                  <i class="fa fa-angle-down"></i>
                  <?php echo Utilities::render_dropdown('leadRatingId', $lead_ratings_a, $lead_rating) ?>
                  <?php if(isset($form_errors['leadRatingId'])): ?>
                
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <div class="col-xs-12">
              <div class="formfld">
                <label>Email opt out?</label>
                <div class="radiowrp">
                  <input type="radio" name="emailOptOut"><span class="radiolabel">Yes</span>
                  <input type="radio" name="emailOptOut"><span class="radiolabel">No</span>
                </div>
              </div>
            </div>
            <div class="col-xs-12 col-sm-12">
              <div class="formfld">
                <label>About</label>
                <textarea name="about" id="about"><?php echo $about ?></textarea>
                <?php if(isset($form_errors['about'])): ?>
                
                <?php endif; ?>                
              </div>
            </div>           
            <div class="clearfix"></div>
            <div class="formbottom">
              <button id="section-4">Save</button>
              <button id="cancel-4" class="cancelLeadForm">Cancel</button>
            </div>
          </div>
          <div class="formerrorwrp">
            <?php $flash->print_flash_message(false, true) ?>
          </div>
          <div class="clearfix"></div>
        </div>
      </div>
    </div>
  </form>
</div>