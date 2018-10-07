<?php
  //dump($form_data);
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panel">
      <h2 class="hdg-reports text-center">Set Active Financial Year</h2>
      <div class="panel-body">
        <?php echo $flash_obj->print_flash_message(); ?>        
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/finy/list" class="btn btn-default">
              <i class="fa fa-book"></i> Financial Years List
            </a>&nbsp;&nbsp;
            <a href="/finy-slnos/list" class="btn btn-default">
              <i class="fa fa-sort-numeric-asc"></i> FY Serial Numbers List
            </a>            
          </div>
        </div>
        <form class="form-validate form-horizontal" method="POST" autocomplete="off" id="finYearSlnoForm">
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label"><b>Financial Year</b></label>
              <div class="select-wrap">
                <select class="form-control" name="finyCode" id="finyCode">
                  <?php 
                    foreach($finys as $key=>$value):
                      if($finy_code === $key) {
                        $selected = 'selected="selected"';
                      } else {
                        $selected = '';
                      } 
                  ?>
                    <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <?php if(isset($errors['finyCode'])): ?>
                <span class="error"><?php echo $errors['finyCode'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="text-center">
            <button class="btn btn-success" id="Save"><i class="fa fa-check"></i> Make Active</button>
          </div>
        </form>
      </div>
    </section>
  </div>
</div>