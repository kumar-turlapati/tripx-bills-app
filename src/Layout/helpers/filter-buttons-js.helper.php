<?php
  if(!isset($formAction)) {
    $formAction = '';
  }
?>
<div class="clearfix"></div>
<div class="container-fluid text-right">
  <button class="btn btn-success" id="reportsFilter"><i class="fa fa-file-text"></i> Get Report</button>
  <button class="btn btn-warning" id="reportsReset" onclick="window.location.href='<?php echo $formAction ?>';return false;"><i class="fa fa-refresh"></i> Reset </button>
</div>