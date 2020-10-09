<div class="container-fluid">
  <button class="btn btn-success" name="op" value="filterData" id="filterSubmit">
  	<i class="fa fa-filter"></i> Filter
  </button>
  <button type="reset" class="btn btn-warning" onclick="javascript:resetFilter(<?php echo (isset($page_url) && $page_url != '' ? "'".$page_url."'" : '#') ?>)" id="filterReset">
  	<i class="fa fa-refresh"></i> Reset
  </button>
</div>