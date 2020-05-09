<?php
  use Atawa\Utilities;
  use Atawa\CrmUtilities;

  // process form data
  $appointment_owner_id = isset($form_data['appointmentOwnerId']) && $form_data['appointmentOwnerId'] !== '' ? $form_data['appointmentOwnerId'] : $_SESSION['uid'];
  $appointment_title = isset($form_data['appointmentTitle']) && $form_data['appointmentTitle'] !== '' ? $form_data['appointmentTitle'] : '';
  $appointment_desc = isset($form_data['appointmentDescription']) && $form_data['appointmentDescription'] !== '' ? $form_data['appointmentDescription'] : '';
  $appointment_start_date = isset($form_data['appointmentStartDate']) && $form_data['appointmentStartDate'] !== '' ? $form_data['appointmentStartDate'] : date("d-m-Y");
  $appointment_start_time = isset($form_data['appointmentStartTime']) && $form_data['appointmentStartTime'] !== '' ? $form_data['appointmentStartTime'] : date("Hi");
  $appointment_end_date = isset($form_data['appointmentEndDate']) && $form_data['appointmentEndDate'] !== '' ? $form_data['appointmentEndDate'] : date("d-m-Y");
  $appointment_end_time = isset($form_data['appointmentEndTime']) && $form_data['appointmentEndTime'] !== '' ? $form_data['appointmentEndTime'] : date("Hi");
  $appointment_customer_name = isset($form_data['appointmentCustomerName']) && $form_data['appointmentCustomerName'] !== '' ? $form_data['appointmentCustomerName'] : '';

  $appointment_type = isset($form_data['appointmentTypeId']) && $form_data['appointmentTypeId'] !== '' ? $form_data['appointmentTypeId'] : '';
  $appointment_purpose = isset($form_data['appointmentPurposeId']) && $form_data['appointmentPurposeId'] !== '' ? $form_data['appointmentPurposeId'] : '';
  $appointment_status = isset($form_data['appointmentStatusId']) && $form_data['appointmentStatusId'] !== '' ? $form_data['appointmentStatusId'] : '';
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panelBox">
      <div class="panelBody">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/appointments/list" class="btn btn-default">
              <i class="fa fa-calendar"></i> Appointments List
            </a>
          </div>
        </div>
        <form class="form-validate form-horizontal" method="POST" autocomplete="Off">
          <div class="form-group">
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot15">
              <label class="control-label labelStyle">Appointment owner*</label>
              <div class="select-wrap">
                <?php echo CrmUtilities::render_dropdown('appointmentOwnerId', $users, $appointment_owner_id) ?>
              </div>
              <?php if(isset($form_errors['appointmentOwnerId'])): ?>
                <span class="error"><?php echo $form_errors['appointmentOwnerId'] ?></span>
              <?php endif; ?>              
            </div>
            <div class="col-sm-12 col-md-2 col-lg-2 m-bot15">
              <label class="control-label labelStyle">Appointment start date*</label>
              <div class="form-group">
                <div class="col-lg-12">
                  <div class="input-append date" data-date="<?php echo $appointment_start_date ?>" data-date-format="dd-mm-yyyy">
                    <input class="span2" value="<?php echo $appointment_start_date ?>" size="16" type="text" readonly name="appointmentStartDate" id="appointmentStartDate" />
                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                  </div>
                  <?php if(isset($form_errors['appointmentStartDate'])): ?>
                    <span class="error"><?php echo $form_errors['appointmentStartDate'] ?></span>
                  <?php endif; ?>                  
                </div>
              </div>
            </div>
            <div class="col-sm-12 col-md-2 col-lg-2 m-bot15">
              <label class="control-label labelStyle">Appointment start time*</label>
              <div class="select-wrap">
                <?php echo CrmUtilities::render_dropdown('appointmentStartTime', $time_array_a, $appointment_start_time) ?>
              </div>
              <?php if(isset($form_errors['appointmentStartTime'])): ?>
                <span class="error"><?php echo $form_errors['appointmentStartTime'] ?></span>
              <?php endif; ?>               
            </div>
            <div class="col-sm-12 col-md-2 col-lg-2 m-bot15">
              <label class="control-label labelStyle">Appointment end date*</label>
              <div class="form-group">
                <div class="col-lg-12">
                  <div class="input-append date" data-date="<?php echo $appointment_end_date ?>" data-date-format="dd-mm-yyyy">
                    <input class="span2" value="<?php echo $appointment_end_date ?>" size="16" type="text" readonly name="appointmentEndDate" id="appointmentEndDate" />
                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                  </div>
                  <?php if(isset($form_errors['appointmentEndDate'])): ?>
                    <span class="error"><?php echo $form_errors['appointmentEndDate'] ?></span>
                  <?php endif; ?>                  
                </div>
              </div>
            </div>
            <div class="col-sm-12 col-md-2 col-lg-2 m-bot15">
              <label class="control-label labelStyle">Appointment end time*</label>
              <div class="select-wrap">
                <?php echo CrmUtilities::render_dropdown('appointmentEndTime', $time_array_a, $appointment_end_time) ?>
              </div>
              <?php if(isset($form_errors['appointmentEndTime'])): ?>
                <span class="error"><?php echo $form_errors['appointmentEndTime'] ?></span>
              <?php endif; ?>
            </div>                       
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-12 col-lg-12 m-bot15">
              <label class="control-label labelStyle">Appointment title*</label>
              <input 
                type="text" 
                class="form-control noEnterKey" 
                name="appointmentTitle" 
                id="appointmentTitle"
                value="<?php echo $appointment_title ?>"
              />
              <?php if(isset($form_errors['appointmentTitle'])): ?>
                <span class="error"><?php echo $form_errors['appointmentTitle'] ?></span>
              <?php endif; ?>
            </div>            
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-12 col-lg-12 m-bot15">
              <label class="control-label labelStyle">Appointment description</label>
              <textarea
                class="form-control noEnterKey"
                rows="3"
                cols="100"
                id="appointmentDescription"
                name="appointmentDescription"
              ><?php echo $appointment_desc ?></textarea>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot15">
              <label class="control-label labelStyle">Customer (or) Lead name*</label>
              <input 
                type="text" 
                class="form-control noEnterKey cnameAc" 
                name="appointmentCustomerName" 
                id="appointmentCustomerName"
                value="<?php echo $appointment_customer_name ?>"
              />
              <?php if(isset($form_errors['appointmentCustomerName'])): ?>
                <span class="error"><?php echo $form_errors['appointmentCustomerName'] ?></span>
              <?php endif; ?>
            </div>            
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot15">
              <label class="control-label labelStyle">Appointment type*</label>
              <div class="select-wrap">
                <?php echo CrmUtilities::render_dropdown('appointmentTypeId', $appointment_types_a, $appointment_type) ?>
              </div>
              <?php if(isset($form_errors['appointmentTypeId'])): ?>
                <span class="error"><?php echo $form_errors['appointmentTypeId'] ?></span>
              <?php endif; ?>
            </div>            
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot15">
              <label class="control-label labelStyle">Appointment purpose*</label>
              <div class="select-wrap">
                <?php echo CrmUtilities::render_dropdown('appointmentPurposeId', $appointment_purpose_a, $appointment_purpose) ?>
              </div>
              <?php if(isset($form_errors['appointmentPurposeId'])): ?>
                <span class="error"><?php echo $form_errors['appointmentPurposeId'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot15">
              <label class="control-label labelStyle">Appointment status*</label>
              <div class="select-wrap">
                <?php echo CrmUtilities::render_dropdown('appointmentStatusId', $appointment_status_a, $appointment_status) ?>
              </div>
              <?php if(isset($form_errors['appointmentStatusId'])): ?>
                <span class="error"><?php echo $form_errors['appointmentStatusId'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="text-center" style="margin-top: 20px;">
            <button class="btn btn-primary cancelOp">
              <i class="fa fa-save"></i> Save
            </button>&nbsp;&nbsp;
            <button class="btn btn-danger cancelButton" id="appointmentUpdate">
              <i class="fa fa-times"></i> Cancel
            </button>
          </div>
        </form>
      </div>
    </section>
  </div>
</div>