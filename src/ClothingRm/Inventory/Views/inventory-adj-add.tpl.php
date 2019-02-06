<?php

  use Atawa\Utilities;

  if(isset($submitted_data['itemName']) && $submitted_data['itemName'] !== '') {
    $item_name = $submitted_data['itemName'];
  } else {
    $item_name = '';
  }
  if(isset($submitted_data['lotNo']) && $submitted_data['lotNo'] !== '') {
    $lot_no = $submitted_data['lotNo'];
  } else {
    $lot_no = '';
  }
  if(isset($submitted_data['adjQty']) && $submitted_data['adjQty'] !== '') {
    $adj_qty = $submitted_data['adjQty'];
  } else {
    $adj_qty = '';
  }
  if(isset($submitted_data['adjReasonCode']) && $submitted_data['adjReasonCode'] !== '') {
    $adj_reason_code = $submitted_data['adjReasonCode'];
  } else {
    $adj_reason_code = '';
  }
  if(isset($submitted_data['adjDate']) && $submitted_data['adjDate']!=='') {
    $current_date = date("d-m-Y", strtotime($submitted_data['adjDate']));
  } else {
    $current_date = date("d-m-Y");
  }
  if(isset($submitted_data['locationCode']) && $submitted_data['locationCode'] !== '') {
    $location_code = $submitted_data['locationCode'];
  } else {
    $location_code = '';
  }
  // dump($errors);
  // exit;
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panel">
      <h2 class="hdg-reports text-center">Add Stock Adjustment</h2>
      <div class="panel-body">
        <?php echo Utilities::print_flash_message() ?>
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
            <a href="/inventory/stock-adjustments-list" class="btn btn-default">
              <i class="fa fa-book"></i> Stock adjustments register
            </a>
          </div>
        </div>
        <form class="form-validate form-horizontal" method="POST">
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Item name</label>
              <input type="text" class="form-control inameAc" name="itemName" id="itemName" value="<?php echo $item_name ?>">
              <?php if(isset($errors['itemName'])): ?>
                <span class="error"><?php echo $errors['itemName'] ?></span>
              <?php endif; ?>           
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Lot no.</label>
              <input type="text" class="form-control" name="lotNo" id="lotNo" value="<?php echo $lot_no ?>">
              <?php if(isset($errors['lotNo'])): ?>
                <span class="error"><?php echo $errors['lotNo'] ?></span>
              <?php endif; ?>              
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Adjustment qty.</label>
                <input type="text" class="form-control" name="adjQty" id="adjQty" value="<?php echo $adj_qty ?>">
                <?php if(isset($errors['adjQty'])): ?>
                  <span class="error"><?php echo $errors['adjQty'] ?></span>
                <?php endif; ?>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Store name (against which store this entry effects)</label>
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
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Reason for adjustment</label>
              <div class="select-wrap">
                <select class="form-control" name="adjReasonCode" id="adjReasonCode">
                  <?php 
                    foreach($adj_reasons as $key=>$value):
                      $adj_a = explode('_', $value);
                      if($adj_reason_code === $key) {
                        $selected = 'selected="selected"';
                      } else {
                        $selected = '';
                      }
                      if( is_array($adj_a) && isset($adj_a[1])>1 ) {
                        $disabled = 'disabled';
                      } else {
                        $disabled = '';
                      }
                  ?>
                    <option value="<?php echo $key ?>" <?php echo $selected.' '.$disabled ?>><?php echo $adj_a[0] ?></option>
                  <?php endforeach; ?>
                </select>
                <?php if(isset($errors['adjReasonCode'])): ?>
                  <span class="error"><?php echo $errors['adjReasonCode'] ?></span>
                <?php endif; ?>
              </div>             
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Adjustment date (dd-mm-yyyy)</label>
              <div class="form-group">
                <div class="col-lg-12">
                  <div class="input-append date" data-date="<?php echo $current_date ?>" data-date-format="dd-mm-yyyy">
                    <input class="span2" value="<?php echo $current_date ?>" size="16" type="text" readonly name="adjDate" id="adjDate" />
                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                  </div>
                  <?php if(isset($errors['adjDate'])): ?>
                    <span class="error"><?php echo $errors['adjDate'] ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
          <div class="text-center">
            <button class="btn btn-primary" id="Save">
              <i class="fa fa-save"></i> Save
            </button>
          </div>
        </form>
      </div>
    </section>
  </div>
</div>