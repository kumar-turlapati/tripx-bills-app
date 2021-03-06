<?php
	use Atawa\Utilities;
?>
<div class="row">
  <div class="col-lg-12"> 
    
    <section class="panel">
      <div class="panel-body">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/products/list" class="btn btn-default">
              <i class="fa fa-users"></i> Products List
            </a>&nbsp;&nbsp;&nbsp;
            <a href="/products/create" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> New Product 
            </a>
          </div>
        </div>
        <form class="form-validate form-horizontal" method="POST" autocomplete="Off" enctype="multipart/form-data">
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label labelStyle">Store / Location name</label>
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
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label labelStyle">File name</label>
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
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label labelStyle">Upload type</label>
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
          <div class="form-group">
            <div class="col-sm-12 col-md-12 col-md-12 m-bot15" id="downloadButton" style="padding-top:15px;">
              <label class="control-label" style="text-align: center;">
                <a href="/downloads/Qb_OpeningBalances_Upload_Format_V.1.0.xlsx" target="_blank">
                  <i class="fa fa-download"></i> Download opening balances upload format.
                </a>
              </label>
              <p class="red" align="center">Note: Only .xlsx format is allowed.</p>
            </div>              
          </div>          
          <div class="text-center margin-top-20">
            <button class="btn btn-danger cancelForm" id="inventoryImport">
              <i class="fa fa-times"></i> Cancel
            </button>&nbsp;&nbsp;
            <button class="btn btn-primary" id="saveForm">
              <i class="fa fa-upload"></i> Upload
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