<?php
  use Atawa\Utilities;

  $location_code = isset($form_data['locationCode']) && $form_data['locationCode'] !== '' ? $form_data['locationCode'] : '';
  $cb_date = isset($form_data['cbDate']) && $form_data['cbDate'] !== '' ? date("d-m-Y", strtotime($form_data['cbDate'])) : date("d-m-Y");
  $audit_start_date = isset($form_data['auditStartDate']) && $form_data['auditStartDate'] !== '' ? date("d-m-Y", strtotime($form_data['auditStartDate'])) : date("d-m-Y");;
  $audit_end_date = isset($form_data['auditEndDate']) && $form_data['auditEndDate'] !== '' ? date("d-m-Y", strtotime($form_data['auditEndDate'])) : '';
  $audit_type = isset($form_data['auditType']) && $form_data['auditType'] !== '' ? $form_data['auditType'] : '';
  $status = isset($form_data['status']) && $form_data['status'] !== '' ? $form_data['status'] : 1;
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panel">
      <div class="panel-body">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/stock-audit/register" class="btn btn-default">
              <i class="fa fa-book"></i> Stock Audit Register
            </a>
          </div>
        </div>
        <form class="form-validate form-horizontal" method="POST">
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Store Name</label>
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
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Audit Type</label>
              <div class="select-wrap">
                <select class="form-control" name="auditType" id="auditType">
                  <?php 
                    foreach($audit_types as $key => $value):
                      if($audit_type === $key) {
                        $selected = 'selected="selected"';
                      } else {
                        $selected = '';
                      }                      
                  ?>
                    <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <?php if(isset($form_errors['auditType'])): ?>
                <span class="error"><?php echo $form_errors['auditType'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Closing Balance Date (dd-mm-yyyy)</label>
              <div class="form-group">
                <div class="col-lg-12">
                  <div class="input-append date" data-date="<?php echo $cb_date ?>" data-date-format="dd-mm-yyyy">
                    <input class="span2" value="<?php echo $cb_date ?>" size="16" type="text" readonly name="cbDate" id="cbDate" />
                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                  </div>
                  <?php if(isset($form_errors['cbDate'])): ?>
                    <span class="error"><?php echo $form_errors['cbDate'] ?></span>
                  <?php endif; ?>                  
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Audit Start Date (dd-mm-yyyy)</label>
              <div class="form-group">
                <div class="col-lg-12">
                  <div class="input-append date" data-date="<?php echo $audit_start_date ?>" data-date-format="dd-mm-yyyy">
                    <input class="span2" value="<?php echo $audit_start_date ?>" size="16" type="text" readonly name="auditStartDate" id="auditStartDate" />
                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                  </div>
                  <?php if(isset($form_errors['auditStartDate'])): ?>
                    <span class="error"><?php echo $form_errors['auditStartDate'] ?></span>
                  <?php endif; ?>                  
                </div>
              </div>
            </div>
            <?php /*
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Audit End Date (dd-mm-yyyy)</label>
              <div class="form-group">
                <div class="col-lg-12">
                  <div class="input-append date" data-date="<?php echo $audit_end_date ?>" data-date-format="dd-mm-yyyy">
                    <input class="span2" value="<?php echo $audit_end_date ?>" size="16" type="text" readonly name="auditEndDate" id="auditEndDate" />
                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                  </div>
                  <?php if(isset($form_errors['auditEndDate'])): ?>
                    <span class="error"><?php echo $form_errors['auditEndDate'] ?></span>
                  <?php endif; ?>                  
                </div>
              </div>
            </div> */ ?>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Status</label>
              <div class="select-wrap">
                <select class="form-control" name="status" id="status">
                  <?php 
                    foreach($status_options as $key => $value):
                      if((int)$status === (int)$key) {
                        $selected = 'selected="selected"';
                      } else {
                        $selected = '';
                      }
                  ?>
                    <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <?php if(isset($form_errors['status'])): ?>
                <span class="error"><?php echo $form_errors['status'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="text-center">
            <button class="btn btn-success" id="Save">Start Audit&nbsp;&nbsp;<i class="fa fa-arrow-circle-right" aria-hidden="true"></i></button>
          </div>
        </form>  
      </div>
    </section>
  </div>
</div>