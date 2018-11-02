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
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">File name</label>
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
            <input type="hidden" name="fileUpload" value="catBrand" />
          </div>
          <div class="text-center margin-top-20">
            <button class="btn btn-danger cancelForm" id="inventoryImport">
              <i class="fa fa-times"></i> Cancel
            </button>&nbsp;&nbsp;
            <button class="btn btn-primary" id="saveForm">
              <i class="fa fa-edit"></i> Update
            </button>
          </div>      
        </form>
      </div>
    </section>
  </div>
</div>