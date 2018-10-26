<?php
	use Atawa\CrmUtilities;
	use Atawa\Utilities;

  $location_code = isset($form_data['locationCode']) ? $form_data['locationCode'] : '';
  $op = isset($form_data['op']) ? $form_data['op'] : '';  
?>
<div class="row">
  <div class="col-lg-12"> 
    
    <section class="panel">
      <div class="panel-body">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/suppliers/list" class="btn btn-default">
              <i class="fa fa-users"></i> Suppliers List
            </a>&nbsp;&nbsp;&nbsp;
            <a href="/suppliers/create" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> New Supplier 
            </a>
          </div>
        </div>
        <form class="form-validate form-horizontal" method="POST" autocomplete="Off" enctype="multipart/form-data">
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">File name (only .ods, .xlsx formats are allowed)</label>
              <input 
                type="file" 
                class="form-control noEnterKey"
                name="fileName"
                id="fileName"
              >
              <?php if(isset($form_errors['fileName'])): ?>
                <span class="error"><?php echo $form_errors['fileName'] ?></span>
              <?php endif; ?>
            </div>
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
              <?php if(isset($form_errors['locationCode'])): ?>
                <span class="error"><?php echo $form_errors['locationCode'] ?></span>
              <?php endif; ?>
            </div>            
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Upload action</label>
              <div class="select-wrap">
                <select class="form-control" id="op" name="op">
                  <?php foreach($op_a as $key=>$value): ?>
                    <option value="<?php echo $key ?>"><?php echo $value ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <?php if(isset($form_errors['op'])): ?>
                <span class="error"><?php echo $form_errors['op'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="text-center margin-top-20">
            <button class="btn btn-danger cancelButton" id="uploadSuppliers">
              <i class="fa fa-times"></i> Cancel
            </button>&nbsp;&nbsp;
            <button class="btn btn-primary" id="saveForm">
              <i class="fa fa-upload"></i> Upload Data
            </button>
          </div>      
        </form>
        <?php if(count($upload_errors)>0): ?>
        <div class="alert alert-danger" role="alert" style="margin-top:10px;">
          <?php foreach($upload_errors as $key => $error_details): ?>
            <p><?php echo implode(', ',$error_details) ?></p>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </section>
  </div>
</div>