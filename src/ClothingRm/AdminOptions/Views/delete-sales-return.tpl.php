<?php
  $voc_no = isset($submitted_data['vocNo']) ? $submitted_data['vocNo'] : '';
  $location_code = isset($submitted_data['locationCode']) ? $submitted_data['locationCode'] : '';
  $delete_reason = isset($submitted_data['deleteReason']) ? $submitted_data['deleteReason'] : '';
?>
<div class="row">
  <div class="col-lg-12">
    <section class="panel">
      <div class="panel-body">
        <?php echo $flash_obj->print_flash_message() ?>
        <form class="form-validate form-horizontal" method="POST" autocomplete="off" id="editPOAfterGRN">
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Store name</label>
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
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Voucher No. (Auto increment number)</label>
              <input type="text" class="form-control" name="vocNo" id="vocNo" value="<?php echo $voc_no ?>" maxlength="20" />
              <?php if(isset($errors['vocNo'])): ?>
                <span class="error"><?php echo $errors['vocNo'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Reason for Deletion (100 characters maximum)</label>
              <input type="text" class="form-control noEnterKey" name="deleteReason" id="deleteReason" maxlength="200" value="<?php echo $delete_reason ?>">
              <?php if(isset($errors['deleteReason'])): ?>
                <span class="error"><?php echo $errors['deleteReason'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="text-center">
            <button class="btn btn-danger" id="srDelete"><i class="fa fa-share"></i> I know what i am doing. Continue</button>
          </div>
        </form>
      </div>
    </section>
  </div>
</div>