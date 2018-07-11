<?php
  use Atawa\Utilities;
  if(isset($submitted_data['campaignName'])) {
    $campaign_name = $submitted_data['campaignName'];
  } else {
    $campaign_name = '';
  }
  if(isset($submitted_data['campaignDesc'])) {
    $campaign_desc = $submitted_data['campaignDesc'];
  } else {
    $campaign_desc = '';
  }
  if(isset($submitted_data['startDate'])) {
    $start_date = $submitted_data['startDate'];
  } else {
    $start_date = '';
  }
  if(isset($submitted_data['endDate'])) {
    $end_date = $submitted_data['endDate'];
  } else {
    $end_date = '';
  }
  if(isset($submitted_data['status'])) {
    $status = $submitted_data['status'];
  } else {
    $status = '-1';
  }
  if(isset($submitted_data['startDate']) && $submitted_data['startDate'] !== '') {
    $start_date = date("d-m-Y", strtotime($submitted_data['startDate']));
  } else {
    $start_date = date("d-m-Y");
  }
  if(isset($submitted_data['endDate']) && $submitted_data['endDate'] !== '') {
    $end_date = date("d-m-Y", strtotime($submitted_data['endDate']));
  } else {
    $end_date = date("d-m-Y");
  }  
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panel">
      <h2 class="hdg-reports text-center">Create a Campaign</h2>
      <div class="panel-body">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/campaigns/list" class="btn btn-default">
              <i class="fa fa-book"></i> Campaigns List 
            </a> 
          </div>
        </div>
        <form class="form-validate form-horizontal" method="POST">
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Campaign name</label>
              <input
                type="text" 
                class="form-control" 
                name="campaignName" 
                id="campaignName"
                value="<?php echo $campaign_name ?>"
              >
              <?php if(isset($form_errors['campaignName'])): ?>
                <span class="error"><?php echo $form_errors['campaignName'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Start date</label>
              <div class="form-group">
                <div class="col-lg-12">
                  <div class="input-append date" data-date="<?php echo $start_date ?>" data-date-format="dd-mm-yyyy">
                    <input class="span2" value="<?php echo $start_date ?>" size="16" type="text" readonly name="startDate" id="startDate" />
                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                  </div>
                  <?php if(isset($errors['startDate'])): ?>
                    <span class="error"><?php echo $errors['startDate'] ?></span>
                  <?php endif; ?>                  
                </div>
              </div>             
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">End date</label>
              <div class="form-group">
                <div class="col-lg-12">
                  <div class="input-append date" data-date="<?php echo $end_date ?>" data-date-format="dd-mm-yyyy">
                    <input class="span2" value="<?php echo $end_date ?>" size="16" type="text" readonly name="endDate" id="endDate" />
                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                  </div>
                  <?php if(isset($errors['endDate'])): ?>
                    <span class="error"><?php echo $errors['endDate'] ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Status</label>
              <div class="select-wrap">
                <select class="form-control" name="status" id="status">
                  <?php 
                    foreach($yes_no_options as $key => $value): 
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
            <div class="col-sm-12 col-md-8 col-lg-8 m-bot15">
              <label class="control-label">Campaign description</label>
              <textarea
                class="form-control" 
                name="campaignDesc" 
                id="campaignDesc"
              ><?php echo $campaign_desc ?></textarea>
              <?php if(isset($form_errors['campaignDesc'])): ?>
                <span class="error"><?php echo $form_errors['campaignDesc'] ?></span>
              <?php endif; ?>
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