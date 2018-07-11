<?php
  use Atawa\Utilities;
  if(isset($form_data['locState'])) {
    $state_id = $form_data['locState'];
  } else {
    $state_id = 0;
  }  
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panel">
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
            <a href="/suppliers/list" class="btn btn-default">
              <i class="fa fa-book"></i> Suppliers List
            </a>
          </div>
        </div>
        <form class="form-validate form-horizontal" method="POST" autocomplete="off">
          <h2 class="hdg-reports borderBottom">Supplier Details</h2>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Supplier name</label>
              <input 
                type="text" 
                class="form-control" 
                name="supplierName" 
                id="supplierName" 
                value="<?php echo (isset($submitted_data['supplierName'])?$submitted_data['supplierName']:'') ?>"
              />
              <?php if(isset($errors['supplierName'])): ?>
                <span class="error"><?php echo $errors['supplierName'] ?></span>
              <?php endif; ?>              
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">GST No.</label>
              <input 
                type="text" 
                class="form-control" 
                name="tinNo" 
                id="tinNo"
                value="<?php echo (isset($submitted_data['tinNo'])?$submitted_data['tinNo']:'') ?>"
                maxlength="15"
              >
              <?php if(isset($errors['tinNo'])): ?>
                  <span class="error"><?php echo $errors['tinNo'] ?></span>
              <?php endif; ?>              
            </div>            
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Status</label>
              <div class="select-wrap">
                <select class="form-control" name="status" id="status">
                  <?php foreach($status as $key=>$value): ?>
                    <option value="<?php echo $key ?>"><?php echo $value ?></option>
                  <?php endforeach; ?>
                </select>
                <?php if(isset($errors['status'])): ?>
                  <span class="error"><?php echo $errors['status'] ?></span>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <h2 class="hdg-reports borderBottom">Location Details</h2>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Address-1</label>
              <input type="text" class="form-control" name="address1" id="address1"
              value="<?php echo (isset($submitted_data['address1'])?$submitted_data['address1']:'') ?>"      
              >
              <?php if(isset($errors['address1'])): ?>
                <span class="error"><?php echo $errors['address1'] ?></span>
              <?php endif; ?>              
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Address-2</label>
              <input type="text" class="form-control" name="address2" id="address2"
              value="<?php echo (isset($submitted_data['address2'])?$submitted_data['address2']:'') ?>"              
              >
              <?php if(isset($errors['address2'])): ?>
                <span class="error"><?php echo $errors['address2'] ?></span>
              <?php endif; ?>              
            </div>          
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Pincode</label>
              <input type="text" class="form-control" name="pincode" id="pincode"
              value="<?php echo (isset($submitted_data['pincode'])?$submitted_data['pincode']:'') ?>"              
              >
              <?php if(isset($errors['pincode'])): ?>
                <span class="error"><?php echo $errors['pincode'] ?></span>
              <?php endif; ?>              
            </div>              
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Country name</label>
              <input type="text" class="form-control" name="countryID" id="countryID" value="India" readonly>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">State</label>
              <select class="form-control" name="locState" id="locState">
                <?php 
                  foreach($states as $key=>$value): 
                    if((int)$state_id === $key) {
                      $selected = 'selected="selected"';
                    } else {
                      $selected = '';
                    }
                ?>
                  <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                <?php endforeach; ?>              
              </select>
              <?php if(isset($form_errors['locState'])): ?>
                <span class="error"><?php echo $form_errors['locState'] ?></span>
              <?php endif; ?>
            </div>          
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">City name</label>
              <input type="text" class="form-control" name="cityID" id="cityID">
            </div>              
          </div>
          <h2 class="hdg-reports borderBottom">Contact Details</h2>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Phone-1</label>
              <input type="text" class="form-control" name="phone1" id="phone1"
              value="<?php echo (isset($submitted_data['phone1'])?$submitted_data['phone1']:'') ?>"              
              >
              <?php if(isset($errors['phone1'])): ?>
                <span class="error"><?php echo $errors['phone1'] ?></span>
              <?php endif; ?>                
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Phone-2</label>
              <input type="text" class="form-control" name="phone2" id="phone2"
              value="<?php echo (isset($submitted_data['phone2'])?$submitted_data['phone2']:'') ?>"
              >
              <?php if(isset($errors['phone2'])): ?>
                <span class="error"><?php echo $errors['phone2'] ?></span>
              <?php endif; ?>               
            </div>          
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Mobile</label>
              <input type="text" class="form-control" name="mobileNo" id="mobileNo"
              value="<?php echo (isset($submitted_data['mobileNo'])?$submitted_data['mobileNo']:'') ?>"
              >
              <?php if(isset($errors['mobileNo'])): ?>
                <span class="error"><?php echo $errors['mobileNo'] ?></span>
              <?php endif; ?>                
            </div>              
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Email ID</label>
              <input type="text" class="form-control" name="email" id="email"
              value="<?php echo (isset($submitted_data['email'])?$submitted_data['email']:'') ?>"              
              >
              <?php if(isset($errors['email'])): ?>
                <span class="error"><?php echo $errors['email'] ?></span>
              <?php endif; ?>     
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Website</label>
              <input type="text" class="form-control" name="website" id="website"
              value="<?php echo (isset($submitted_data['website'])?$submitted_data['website']:'') ?>"              
              >
              <?php if(isset($errors['website'])): ?>
                <span class="error"><?php echo $errors['website'] ?></span>
              <?php endif; ?>               
            </div>          
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Contact person name</label>
              <input type="text" class="form-control" name="contactPersonName" id="contactPersonName"
              value="<?php echo (isset($submitted_data['contactPersonName'])?$submitted_data['contactPersonName']:'') ?>"
              >
              <?php if(isset($errors['contactPersonName'])): ?>
                <span class="error"><?php echo $errors['contactPersonName'] ?></span>
              <?php endif; ?>               
            </div>              
          </div>
          <div class="text-center">
            <button class="btn btn-success" id="Save">
              <i class="fa fa-save"></i> <?php echo $btn_label ?>
            </button>
          </div>
          <input type="hidden" name="supplierType" id="supplierType" value="gene" />
          <input type="hidden" name="dlNo" id="dlNo" value="" />          
        </form>
      </div>
    </section>
  </div>
</div>