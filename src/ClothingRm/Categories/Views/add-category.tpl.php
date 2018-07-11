<?php
  if(isset($submitted_data['categoryName'])) {
    $category_name = $submitted_data['categoryName'];
  } else {
    $category_name = '';
  }
  if(isset($submitted_data['status'])) {
    $status = $submitted_data['status'];
  } else {
    $status = '';
  }
?>
<!-- Basic form starts -->
<div class="row">
  <div class="col-lg-12"> 
    <section class="panel">
      <h2 class="hdg-reports text-center">Create Category</h2>
      <div class="panel-body">
        <?php echo $flash_obj->print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/categories/list" class="btn btn-default">
              <i class="fa fa-book"></i> Categories List 
            </a> 
          </div>
        </div>
        <form class="form-validate form-horizontal" method="POST">
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Category name</label>
              <input
                type="text" 
                class="form-control" 
                name="categoryName" 
                id="categoryName"
                value="<?php echo $category_name ?>"
              >
              <?php if(isset($form_errors['categoryName'])): ?>
                <span class="error"><?php echo $form_errors['categoryName'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Status</label>
              <div class="select-wrap">
                <select class="form-control" name="status" id="status">
                  <?php 
                    foreach($status_options as $key=>$value):
                      if((int)$status === (int)$key) {
                        $selected = 'selected="selected"';
                      } else {
                        $selected = '';
                      }                      
                  ?>
                    <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                  <?php endforeach; ?>
                </select>
                <?php if(isset($form_errors['status'])): ?>
                  <span class="error"><?php echo $form_errors['status'] ?></span>
                <?php endif; ?> 
              </div>              
            </div>
          </div>
          <div class="text-center">
            <button class="btn btn-success" id="Save">
              <i class="fa fa-save"></i> Save
            </button>
          </div>
        </form>
      </div>
    </section>
  </div>
</div>