<?php
  $from_indent_no = isset($submitted_data['fromIndentNo']) && $submitted_data['fromIndentNo'] !== '' ? $submitted_data['fromIndentNo'] : '';
  $to_indent_no = isset($submitted_data['toIndentNo']) && $submitted_data['toIndentNo'] !== '' ? $submitted_data['toIndentNo'] : '';
  $from_date = isset($submitted_data['fromDate']) && $submitted_data['fromDate'] !== '' ? $submitted_data['fromDate'] : date("01-m-Y");
  $to_date = isset($submitted_data['toDate']) && $submitted_data['toDate'] !== '' ? $submitted_data['toDate'] : date("d-m-Y");
?>
<div class="row">
  <div class="col-lg-12">
    <section class="panel">
      <div class="panel-body">
        <?php echo $flash_obj->print_flash_message() ?>
        <form class="form-validate form-horizontal" method="POST" autocomplete="off">
          <div class="form-group">
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot15">
              <label class="control-label labelStyle">From Indent No.</label>
              <input type="text" class="form-control" name="fromIndentNo" id="fromIndentNo" value="<?php echo $from_indent_no ?>" maxlength="20" />
              <?php if(isset($errors['fromIndentNo'])): ?>
                <span class="error"><?php echo $errors['fromIndentNo'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot15">
              <label class="control-label labelStyle">To Indent No.</label>
              <input type="text" class="form-control" name="toIndentNo" id="toIndentNo" value="<?php echo $to_indent_no ?>" maxlength="20" />
              <?php if(isset($errors['toIndentNo'])): ?>
                <span class="error"><?php echo $errors['toIndentNo'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot15">
              <label class="control-label labelStyle">From Indent Date</label>
              <div class="input-append date" data-date="<?php echo $from_date ?>" data-date-format="dd-mm-yyyy">
                <input class="span2" size="16" type="text" readonly name="fromDate" id="fromDate" value="<?php echo $from_date ?>" />
                <span class="add-on"><i class="fa fa-calendar"></i></span>
              </div>
              <?php if(isset($errors['fromDate'])): ?>
                <span class="error"><?php echo $errors['fromDate'] ?></span>
              <?php endif; ?>              
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot15">
              <label class="control-label labelStyle">To Indent Date</label>
              <div class="input-append date" data-date="<?php echo $to_date ?>" data-date-format="dd-mm-yyyy">
                <input class="span2" size="16" type="text" readonly name="toDate" id="toDate" value="<?php echo $to_date ?>" />
                <span class="add-on"><i class="fa fa-calendar"></i></span>
              </div>
              <?php if(isset($errors['toDate'])): ?>
                <span class="error"><?php echo $errors['toDate'] ?></span>
              <?php endif; ?>               
            </div>            
          </div>
          <div class="text-center">
            <button class="btn btn-danger" id="invoiceDelete"><i class="fa fa-level-up"></i> Export Data</button>
          </div>
        </form>
      </div>
    </section>
  </div>
</div>