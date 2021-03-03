<?php
  use Atawa\Utilities;
  if(isset($submitted_data['taxLabel'])) {
    $taxName = $submitted_data['taxLabel'];
  } else {
    $taxName = '';
  }
  if(isset($submitted_data['taxPercent'])) {
    $taxPercent = $submitted_data['taxPercent'];
  } else {
    $taxPercent = '';
  }
  if(isset($submitted_data['isCompound'])) {
    $isCompound = $submitted_data['isCompound'];
  } else {
    $isCompound = '';
  }
?>
<!-- Basic form starts -->
<div class="row">
  <div class="col-lg-12"> 
    
    <!-- Panel starts -->
    <section class="panel">
      <h2 class="hdg-reports text-center">Update Tax Rate</h2>
      <div class="panel-body">

        <?php echo Utilities::print_flash_message() ?>

        <!-- Right links starts -->
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/taxes/list" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> Tax Rates 
            </a> 
          </div>
        </div>
        <!-- Right links ends -->        
        
        <!-- Form starts -->
        <form class="form-validate form-horizontal" method="POST">

          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Tax name</label>
              <input
                type="text" 
                class="form-control" 
                name="taxName" 
                id="taxName"
                value="<?php echo $taxName ?>"
              >
              <?php if(isset($form_errors['taxName'])): ?>
                <span class="error"><?php echo $form_errors['taxName'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Tax percent</label>
              <input 
                type="text" 
                class="form-control" 
                name="taxPercent" 
                id="taxes_taxPercent" 
                value="<?php echo number_format($taxPercent,2) ?>"
                maxlength="5"
              >
              <?php if(isset($form_errors['taxPercent'])): ?>
                <span class="error"><?php echo $form_errors['taxPercent'] ?></span>
              <?php endif; ?>
            </div>
            <?php /*
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Is compound (Tax on tax)?</label>
              <div class="select-wrap">
                <select class="form-control" name="isCompound" id="isCompound">
                  <?php 
                    foreach($yes_no_options as $key=>$value):
                      if($isCompound === $key) {
                        $selected = 'selected="selected"';
                      } else {
                        $selected = '';
                      }                      
                  ?>
                    <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                  <?php endforeach; ?>
                </select>
                <?php if(isset($form_errors['isCompound'])): ?>
                  <span class="error"><?php echo $form_errors['isCompound'] ?></span>
                <?php endif; ?> 
              </div>              
            </div>*/ ?>
          </div>
          <div class="text-center">
            <button class="btn btn-success" id="Save">
              <i class="fa fa-save"></i> Update
            </button>
          </div>
        </form>

        <!-- Form ends -->
      </div>
    </section>
    <!-- Panel ends --> 
  </div>
</div>
<!-- Basic Forms ends -->