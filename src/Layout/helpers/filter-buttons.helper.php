<div class="container-fluid">
  <button class="btn btn-success">
  	<i class="fa fa-file-text"></i> Filter
  </button>
  <button type="reset" class="btn btn-warning" onclick="javascript:resetFilter(<?php echo (isset($page_url) && $page_url != '' ? "'".$page_url."'" : '#') ?>)">
  	<i class="fa fa-refresh"></i> Reset
  </button>
</div>