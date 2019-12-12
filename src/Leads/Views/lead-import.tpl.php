<?php
	use Atawa\CrmUtilities;
	use Atawa\Utilities;

	$matching_attrib_style = (int)$remove_duplicates === 1 ? 'style=""' : 'style="display:none;"';
?>
<div class="row">
  <div class="col-lg-12"> 
    
    <!-- Panel starts -->
    <section class="panel">
      <div class="panel-body">

        <?php echo Utilities::print_flash_message() ?>

        <!-- Right links starts -->
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/leads/list" class="btn btn-default">
              <i class="fa fa-users"></i> Leads List
            </a>&nbsp;&nbsp;&nbsp;
            <a href="/lead/create" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> New Lead 
            </a>
          </div>
        </div>
        <!-- Right links ends -->
        
        <!-- Form starts -->
        <form class="form-validate form-horizontal" method="POST" autocomplete="Off" enctype="multipart/form-data">
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
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
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Import action</label>
              <div class="select-wrap">
                <?php echo CrmUtilities::render_dropdown('op', $op_a, $op) ?>
              </div>
              <?php if(isset($form_errors['op'])): ?>
                <span class="error"><?php echo $form_errors['op'] ?></span>
              <?php endif; ?>              
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Remove duplicate values?</label>
              <div class="select-wrap">
								<?php echo CrmUtilities::render_dropdown('removeDuplicates', $remove_duplicates_a, $remove_duplicates) ?>
							</div>
              <?php if(isset($form_errors['removeDuplicates'])): ?>
                <span class="error"><?php echo $form_errors['removeDuplicates'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="form-group" id="duplicatesDiv" <?php echo $matching_attrib_style ?>>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Lead matching attribute</label>
              <div class="select-wrap">
                <?php echo CrmUtilities::render_dropdown('matchingAttribute', $matching_attribs_a, $matching_attribute) ?>
              </div>
              <?php if(isset($form_errors['matchingAttribute'])): ?>
                <span class="error" id="maErrorId"><?php echo $form_errors['matchingAttribute'] ?></span>
              <?php endif; ?>              
            </div>
          </div>
          <div class="text-right margin-top-20">
            <button class="btn btn-danger cancelForm" id="leadImport">
              <i class="fa fa-times"></i> Cancel
            </button>&nbsp;&nbsp;
            <button class="btn btn-primary" id="saveForm">
              <i class="fa fa-download"></i> Import
            </button>
          </div>      
        </form>
        <!-- Form ends -->
      </div>
    </section>
    <!-- Panel ends --> 
  </div>
</div>