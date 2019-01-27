<?php
  $bc = isset($_SESSION['bc']) && $_SESSION['bc'] > 0 ? (int)$_SESSION['bc'] : 0;
  $scripts_file_name = 'scripts-'.$bc.'.js';
  $prod_file_name = 'qb-'.$bc.'.js';
?>
<script src="/assets/js/jquery.js"></script> 
<script src="/assets/js/jquery-ui-1.10.4.min.js"></script> 
<script src="/assets/js/bootstrap.min.js"></script> 
<script src="/assets/js/jquery.scrollTo.min.js"></script> 
<script src="/assets/js/jquery.nicescroll.js" type="text/javascript"></script> 
<script src="/assets/datetime/datepicker/js/bootstrap-datepicker.js"></script> 
<script src="/assets/datetime/timepicker/js/bootstrap-timepicker.js"></script>
<script src="/assets/js/bootbox.min.js"></script>
<script src="/assets/js/jauto/jquery.autocomplete.js"></script>
<script src="/assets/js/bqfp.min.js"></script>
<script src="/assets/js/jauto/jquery-migrate-1.0.0.js"></script>
<script src="/assets/js/jquery.tabledit.min.js"></script>
<script src="/assets/js/jquery.floatThead.min.js"></script>
<?php if(isset($path_url) && $path_url === '/dashboard'): ?>
  <script src="/assets/js/jqplot1.0.9/jquery.jqplot.min.js"></script>
  <script src="/assets/js/jqplot1.0.9/plugins/jqplot.barRenderer.js"></script>
  <script src="/assets/js/jqplot1.0.9/plugins/jqplot.dateAxisRenderer.js"></script>
  <script src="/assets/js/jqplot1.0.9/plugins/jqplot.categoryAxisRenderer.js"></script>
  <script src="/assets/js/jqplot1.0.9/plugins/jqplot.pieRenderer.js"></script>
  <script src="/assets/js/jqplot1.0.9/plugins/jqplot.pointLabels.js"></script>
<?php endif; ?>
<!--[if lt IE 9]><script src="/assets/js/jqplot1.0.9/excanvas.js"></script><![endif]-->
<?php if(isset($_SERVER['appEnvironment']) && $_SERVER['appEnvironment'] === 'prod') : ?>
  <script src="/assets/js/<?php echo $prod_file_name.'?'.mt_rand() ?>"></script>
<?php else : ?>
  <script src="/assets/js/<?php echo $scripts_file_name.'?'.mt_rand() ?>"></script>
<?php endif; ?>
<?php /*
<script src="/assets/js/jquerymask/jquery.inputmask.bundle.js"></script> */ ?>