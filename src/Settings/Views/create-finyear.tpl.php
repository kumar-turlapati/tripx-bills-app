<?php
  $finy_name = isset($submitted_data['finyName']) ? $submitted_data['finyName'] : '';
  $finy_short_name = isset($submitted_data['finyShortName']) ? $submitted_data['finyShortName'] : '';
  $status = isset($submitted_data['status']) ? $submitted_data['status'] : 0;
  $start_date = isset($submitted_data['startDate']) && $submitted_data['startDate'] !== '' ? date("d-m-Y", strtotime($submitted_data['startDate'])) : '';
  $end_date = isset($submitted_data['endDate']) && $submitted_data['endDate'] !== '' ? date("d-m-Y", strtotime($submitted_data['endDate'])) : '';
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panel">
      <h2 class="hdg-reports text-center">Create Financial Year</h2>
      <div class="panel-body">
        <?php echo $flash_obj->print_flash_message(); ?>        
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/finy/list" class="btn btn-default">
              <i class="fa fa-book"></i> Financial Years List
            </a>&nbsp;&nbsp;
            <a href="/finy-slnos/list" class="btn btn-default">
              <i class="fa fa-sort-numeric-asc"></i> FY Serial Numbers List
            </a>            
          </div>
        </div>
        <form class="form-validate form-horizontal" method="POST" autocomplete="off" id="finYearForm">
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Year name</label>
              <input type="text" class="form-control" name="finyName" id="finyName" value="<?php echo $finy_name ?>" maxlength="20" />
              <?php if(isset($errors['finyName'])): ?>
                <span class="error"><?php echo $errors['finyName'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Year short name</label>
              <input type="text" class="form-control" name="finyShortName" id="finyShortName" maxlength="5" value="<?php echo $finy_short_name ?>">
              <?php if(isset($errors['finyShortName'])): ?>
                <span class="error"><?php echo $errors['finyShortName'] ?></span>
              <?php endif; ?> 
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Status</label>
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
                    <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                  <?php endforeach; ?>
                </select>
                <?php if(isset($errors['status'])): ?>
                  <span class="error"><?php echo $errors['status'] ?></span>
                <?php endif; ?>                 
              </div>              
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Year starts from (dd-mm-yyyy)</label>
              <div class="form-group">
                <div class="col-lg-12">
                  <div class="input-append date" data-date="<?php echo $current_date ?>" data-date-format="dd-mm-yyyy">
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
              <label class="control-label">Year ends on (dd-mm-yyyy)</label>
              <div class="form-group">
                <div class="col-lg-12">
                  <div class="input-append date" data-date="<?php echo $current_date ?>" data-date-format="dd-mm-yyyy">
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
          <div class="text-center">
            <button class="btn btn-success" id="Save"><i class="fa fa-save"></i> Save</button>
          </div>
        </form>
      </div>
    </section>
  </div>
</div>