<div class="container-fluid">
  <button class="btn btn-success" formtarget="_blank">
  	<i class="fa fa-file-text"></i> Get Report in New Window
  </button>
  <button type="reset" class="btn btn-warning" onclick="javascript:resetFilter(<?php echo (isset($page_url) && $page_url != '' ? "'".$page_url."'" : '#') ?>)">
  	<i class="fa fa-refresh"></i> Reset
  </button>
</div>