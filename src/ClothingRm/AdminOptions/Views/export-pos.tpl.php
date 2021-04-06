<?php
  $from_po_no = isset($submitted_data['fromPoNo']) && $submitted_data['fromPoNo'] !== '' ? $submitted_data['fromPoNo'] : '';
  $to_po_no = isset($submitted_data['toPoNo']) && $submitted_data['toPoNo'] !== '' ? $submitted_data['toPoNo'] : '';
  $from_date = isset($submitted_data['fromDate']) && $submitted_data['fromDate'] !== '' ? $submitted_data['fromDate'] : date("01-m-Y");
  $to_date = isset($submitted_data['toDate']) && $submitted_data['toDate'] !== '' ? $submitted_data['toDate'] : date("d-m-Y");
  $location_code = isset($submitted_data['locationCode']) && $submitted_data['locationCode'] !== '' ? $submitted_data['locationCode'] : '';
?>
<div class="row">
  <div class="col-lg-12">
    <section class="panel">
      <div class="panel-body">
        <?php echo $flash_obj->print_flash_message() ?>
        <form class="form-validate form-horizontal" method="POST" autocomplete="off" id="exportPOsForm">
          <div class="col-sm-12 col-md-3 col-lg-3 m-bot15">
            <label class="control-label labelStyle">Store / Location name</label>
            <div class="select-wrap">
              <select class="form-control" name="locationCode">
                <?php 
                  foreach($client_locations as $key => $value):
                    if($location_code === $key) {
                      $selected = 'selected="selected"';
                    } else {
                      $selected = '';
                    }                      
                ?>
                  <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <?php if(isset($errors['locationCode'])): ?>
              <span class="error"><?php echo $errors['locationCode'] ?></span>
            <?php endif; ?>
          </div>          
          <div class="form-group">
            <div class="col-sm-12 col-md-2 col-lg-2 m-bot15">
              <label class="control-label labelStyle">From PO No.</label>
              <input type="text" class="form-control" name="fromPoNo" id="fromPoNo" value="<?php echo $from_po_no ?>" maxlength="20" />
              <?php if(isset($errors['fromPoNo'])): ?>
                <span class="error"><?php echo $errors['fromPoNo'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-2 col-lg-2 m-bot15">
              <label class="control-label labelStyle">To PO No.</label>
              <input type="text" class="form-control" name="toPoNo" id="toPoNo" value="<?php echo $to_po_no ?>" maxlength="20" />
              <?php if(isset($errors['toPoNo'])): ?>
                <span class="error"><?php echo $errors['toPoNo'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-2 col-lg-2 m-bot15">
              <label class="control-label labelStyle">From PO Date</label>
              <div class="input-append date" data-date="<?php echo $from_date ?>" data-date-format="dd-mm-yyyy">
                <input class="span2" size="16" type="text" readonly name="fromDate" id="fromDate" value="<?php echo $from_date ?>" />
                <span class="add-on"><i class="fa fa-calendar"></i></span>
              </div>
              <?php if(isset($errors['fromDate'])): ?>
                <span class="error"><?php echo $errors['fromDate'] ?></span>
              <?php endif; ?>              
            </div>
            <div class="col-sm-12 col-md-2 col-lg-2 m-bot15">
              <label class="control-label labelStyle">To PO Date</label>
              <div class="input-append date" data-date="<?php echo $to_date ?>" data-date-format="dd-mm-yyyy">
                <input class="span2" size="16" type="text" readonly name="toDate" id="toDate" value="<?php echo $to_date ?>" />
                <span class="add-on"><i class="fa fa-calendar"></i></span>
              </div>
              <?php if(isset($errors['toDate'])): ?>
                <span class="error"><?php echo $errors['toDate'] ?></span>
              <?php endif; ?>               
            </div>
          </div>
          <div class="text-center">
            <button class="btn btn-primary cancelOp" id="downloadPOsAction"><i class="fa fa-level-down"></i> Download Data</button>
            <button class="btn btn-danger cancelButton" id="downloadPO"><i class="fa fa-times"></i> Reset</button>
          </div>
        </form>
      </div>
    </section>
  </div>
</div>