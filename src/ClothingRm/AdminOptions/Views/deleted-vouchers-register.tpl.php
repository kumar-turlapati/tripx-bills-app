<?php
  $current_date = date("d-m-Y");
  $location_code = isset($submitted_data['locationCode']) ? $submitted_data['locationCode'] : $default_location;
  $voc_type = isset($submitted_data['vocType']) ? $submitted_data['vocType'] : '';
  $from_date = isset($submitted_data['fromDate']) ? $submitted_data['fromDate'] : date("01-m-Y");
  $to_date = isset($submitted_data['toDate']) ? $submitted_data['toDate'] : date("d-m-Y");
?>
<div class="row">
  <div class="col-lg-12">
    <section class="panel">
      <div class="panel-body">
        <?php echo $flash_obj->print_flash_message() ?>
        <form class="form-validate form-horizontal" method="POST" autocomplete="off">
          <div class="form-group">
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot15">
              <label class="control-label labelStyle">Store name</label>
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
              <?php if(isset($errors['locationCode'])): ?>
                <span class="error"><?php echo $errors['locationCode'] ?></span>
              <?php endif; ?>
            </div>            
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot15">
              <label class="control-label labelStyle">Voucher type</label>
              <div class="select-wrap">
                <select class="form-control" name="vocType" id="vocType">
                  <?php 
                    foreach($voc_types as $key=>$value): 
                      if($voc_type === $key) {
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
              <?php if(isset($errors['vocType'])): ?>
                <span class="error"><?php echo $errors['vocType'] ?></span>
              <?php endif; ?>              
            </div>
            <div class="col-sm-12 col-md-2 col-lg-3 m-bot15">
              <label class="control-label labelStyle">From date</label>
              <div class="input-append date" data-date="<?php echo $current_date ?>" data-date-format="dd-mm-yyyy">
                <input class="span2" size="16" type="text" readonly name="fromDate" id="fromDate" value="<?php echo $from_date ?>" />
                <span class="add-on"><i class="fa fa-calendar"></i></span>
              </div>
              <?php if(isset($errors['fromDate'])): ?>
                <span class="error"><?php echo $errors['fromDate'] ?></span>
              <?php endif; ?>              
            </div>
            <div class="col-sm-12 col-md-2 col-lg-3 m-bot15">
              <label class="control-label labelStyle">To date</label>
              <div class="input-append date" data-date="<?php echo $current_date ?>" data-date-format="dd-mm-yyyy">
                <input class="span2" size="16" type="text" readonly name="toDate" id="toDate" value="<?php echo $to_date ?>" />
                <span class="add-on"><i class="fa fa-calendar"></i></span>
              </div>
              <?php if(isset($errors['toDate'])): ?>
                <span class="error"><?php echo $errors['toDate'] ?></span>
              <?php endif; ?>              
            </div>
          </div>
          <div class="text-center">
            <button class="btn btn-primary"><i class="fa fa-server"></i> Get Report</button>
          </div>
        </form>
      </div>
    </section>
  </div>
</div>