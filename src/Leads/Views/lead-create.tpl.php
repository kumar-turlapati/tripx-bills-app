<?php
  use Atawa\Utilities;
  use Atawa\CrmUtilities;

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
<div class="row">
  <div class="col-lg-12"> 
    <section class="panel">
      <div class="panel-body">
        <?php echo Utilities::print_flash_message() ?>

        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/lead/import" class="btn btn-default">
              <i class="fa fa-download"></i> Import Leads 
            </a>&nbsp;&nbsp;&nbsp;            
            <a href="/leads/list" class="btn btn-default">
              <i class="fa fa-users"></i> Leads List
            </a>
          </div>
        </div>
        
        <form class="form-validate form-horizontal" method="POST" autocomplete="Off">
          <h2 class="hdg-reports borderBottom">Business Details</h2>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Lead date (dd-mm-yyyy)</label>
              <div class="form-group">
                <div class="col-lg-12">
                  <div class="input-append date" data-date="<?php echo $lead_date ?>" data-date-format="dd-mm-yyyy">
                    <input class="span2" value="<?php echo $lead_date ?>" size="16" type="text" readonly name="leadDate" id="leadDate" />
                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                  </div>
                  <?php if(isset($form_errors['leadDate'])): ?>
                    <span class="error"><?php echo $form_errors['leadDate'] ?></span>
                  <?php endif; ?>                  
                </div>
              </div>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Referred by</label>
              <div class="select-wrap">
                <select class="form-control" name="leadOwnerId" id="leadOwnerId">
                  <option>Choose</option>
                </select>
              </div>
              <?php if(isset($form_errors['leadOwnerId'])): ?>
                <span class="error"><?php echo $form_errors['leadOwnerId'] ?></span>
              <?php endif; ?>              
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Business name</label>
              <input 
                type="text" 
                class="form-control noEnterKey" 
                name="businessName" 
                id="businessName" 
                value="<?php echo $business_name ?>"
              >
              <?php if(isset($form_errors['businessName'])): ?>
                <span class="error"><?php echo $form_errors['businessName'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Title</label>
              <div class="select-wrap">
                <select class="form-control" name="title" id="title">
                  <option>Choose</option>
                </select>
              </div>
              <?php if(isset($form_errors['title'])): ?>
                <span class="error"><?php echo $form_errors['title'] ?></span>
              <?php endif; ?>              
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">First name</label>
              <input 
                type="text" 
                class="form-control noEnterKey" 
                name="firstName"
                id="firstName"
                value="<?php echo $first_name ?>"
              >
              <?php if(isset($form_errors['firstName'])): ?>
                <span class="error"><?php echo $form_errors['firstName'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Last name</label>
              <input 
                type="text" 
                class="form-control noEnterKey" 
                name="lastName"
                id="lastName"
                value="<?php echo $last_name ?>"
              >
              <?php if(isset($form_errors['lastName'])): ?>
                <span class="error"><?php echo $form_errors['lastName'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Industry</label>
              <div class="select-wrap">
                <?php echo CrmUtilities::render_dropdown('leadIndustryId', $industries_a, $lead_industry_id) ?>
              </div>
              <?php if(isset($form_errors['leadIndustryId'])): ?>
                <span class="error"><?php echo $form_errors['leadIndustryId'] ?></span>
              <?php endif; ?>              
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Employee range</label>
              <div class="select-wrap">
                <?php echo CrmUtilities::render_dropdown('leadEmpRangeId', $emp_ranges_a, $lead_emprange_id) ?>
              </div>
              <?php if(isset($form_errors['leadEmpRangeId'])): ?>
                <span class="error"><?php echo $form_errors['leadEmpRangeId'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <h2 class="hdg-reports borderBottom margin-top-20">Contact Details</h2>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Email</label>
              <input 
                type="text" 
                class="form-control noEnterKey" 
                name="email"
                id="email"
                value="<?php echo $email ?>"
              >
              <?php if(isset($form_errors['email'])): ?>
                <span class="error"><?php echo $form_errors['email'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Secondary email</label>
              <input 
                type="text" 
                class="form-control noEnterKey" 
                name="secondaryEmail"
                id="secondaryEmail"
                value="<?php echo $secondary_email ?>"
              >
              <?php if(isset($form_errors['secondaryEmail'])): ?>
                <span class="error"><?php echo $form_errors['secondaryEmail'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Phone</label>
              <input 
                type="text" 
                class="form-control noEnterKey" 
                name="phone"
                id="phone"
                value="<?php echo $phone ?>"
              >
              <?php if(isset($form_errors['phone'])): ?>
                <span class="error"><?php echo $form_errors['phone'] ?></span>
              <?php endif; ?>
            </div>            
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Mobile</label>
              <input 
                type="text" 
                class="form-control noEnterKey" 
                name="mobile"
                id="mobile"
                value="<?php echo $mobile ?>"
              >
              <?php if(isset($form_errors['mobile'])): ?>
                <span class="error"><?php echo $form_errors['mobile'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Fax</label>
              <input 
                type="text" 
                class="form-control noEnterKey" 
                name="fax"
                id="fax"
                value="<?php echo $fax ?>"
              >
              <?php if(isset($form_errors['fax'])): ?>
                <span class="error"><?php echo $form_errors['fax'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Skype id</label>
              <input 
                type="text" 
                class="form-control noEnterKey" 
                name="fax"
                id="fax"
                value="<?php echo $skype_id ?>"
              >
              <?php if(isset($form_errors['skypeId'])): ?>
                <span class="error"><?php echo $form_errors['skypeId'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <h2 class="hdg-reports borderBottom margin-top-20">Location Details</h2>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Address</label>
              <input 
                type="text" 
                class="form-control noEnterKey" 
                name="address"
                id="address"
                value="<?php echo $address ?>"
              >
              <?php if(isset($form_errors['address'])): ?>
                <span class="error"><?php echo $form_errors['address'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">City name</label>
              <input 
                type="text" 
                class="form-control noEnterKey" 
                name="cityName"
                id="cityName"
                value="<?php echo $city_name ?>"
              >
              <?php if(isset($form_errors['cityName'])): ?>
                <span class="error"><?php echo $form_errors['cityName'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">State name</label>
              <input 
                type="text" 
                class="form-control noEnterKey" 
                name="stateName"
                id="stateName"
                value="<?php echo $state_name ?>"
              >
              <?php if(isset($form_errors['stateName'])): ?>
                <span class="error"><?php echo $form_errors['stateName'] ?></span>
              <?php endif; ?>
            </div>            
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Country name</label>
              <input 
                type="text" 
                class="form-control noEnterKey" 
                name="countryName"
                id="countryName"
                value="<?php echo $mobile ?>"
              >
              <?php if(isset($form_errors['countryName'])): ?>
                <span class="error"><?php echo $form_errors['countryName'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Pincode</label>
              <input 
                type="text" 
                class="form-control noEnterKey" 
                name="pincode"
                id="pincode"
                value="<?php echo $pincode ?>"
              >
              <?php if(isset($form_errors['pincode'])): ?>
                <span class="error"><?php echo $form_errors['pincode'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <h2 class="hdg-reports borderBottom margin-top-20">Lead Details</h2>          
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Lead source</label>
              <div class="select-wrap">
                <?php echo CrmUtilities::render_dropdown('leadSourceId', $lead_sources_a, $lead_source) ?>
              </div>
              <?php if(isset($form_errors['leadSourceId'])): ?>
                <span class="error"><?php echo $form_errors['leadSourceId'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Lead status</label>
              <div class="select-wrap">
                <?php echo CrmUtilities::render_dropdown('leadStatusId', $lead_status_a, $lead_status) ?>
              </div>
              <?php if(isset($form_errors['leadStatusId'])): ?>
                <span class="error"><?php echo $form_errors['leadStatusId'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Lead rating</label>
              <div class="select-wrap">
                <?php echo CrmUtilities::render_dropdown('leadRatingId', $lead_ratings_a, $lead_rating) ?>
              </div>
              <?php if(isset($form_errors['leadRatingId'])): ?>
                <span class="error"><?php echo $form_errors['leadRatingId'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Email opt out?</label>
              <div class="select-wrap">
                <?php echo CrmUtilities::render_dropdown('emailOptOut', $email_optout_a, $email_optout) ?>
              </div>
              <?php if(isset($form_errors['emailOptOut'])): ?>
                <span class="error"><?php echo $form_errors['emailOptOut'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">About lead</label>
              <textarea name="about" id="about" rows="3" cols="39"><?php echo $about ?></textarea>
              <?php if(isset($form_errors['about'])): ?>
                <span class="error"><?php echo $form_errors['about'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="text-right margin-top-20">
            <button class="btn btn-danger cancelForm" id="leadCreate">
              <i class="fa fa-times"></i> Cancel
            </button>&nbsp;&nbsp;
            <button class="btn btn-primary" id="offerSave">
              <i class="fa fa-save"></i> Save
            </button>
          </div>      
        </form>
      </div>
    </section>
  </div>
</div>