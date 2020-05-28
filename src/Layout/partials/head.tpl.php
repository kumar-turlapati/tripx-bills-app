<?php 
  if(isset($view_vars['page_title']) && $view_vars['page_title'] !== '') {
    $page_title_browser = $view_vars['page_title'].' - QwikBills';
  } else {
    $page_title_browser = 'QwikBills';
  }
?>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $page_title_browser ?></title>
<link href="/assets/css/bootstrap-theme.css" rel="stylesheet">
<link rel="icon" href="../assets/img/favicon.ico" />
<link href="/assets/css/elegant-icons-style.css" rel="stylesheet" />
<link href="/assets/css/font-awesome.min.css" rel="stylesheet" />
<link href="/assets/css/style.css<?php echo '?'.mt_rand() ?>" rel="stylesheet">
<link href="/assets/css/style-responsive.css<?php echo '?'.mt_rand() ?>" rel="stylesheet">
<link href="/assets/css/jquery-ui-1.10.4.min.css" rel="stylesheet">
<link href="/assets/datetime/datepicker/css/datepicker.css" rel="stylesheet" />
<link href="/assets/datetime/timepicker/css/timepicker.css" rel="stylesheet" />
<link href="/assets/js/jauto/styles.css" rel="stylesheet" />

<?php if(isset($path_url) && $path_url === '/dashboard'): ?>
  <link href="/assets/js/jqplot1.0.9/jquery.jqplot.min.css" rel="stylesheet" />
<?php endif; ?>

<?php if(isset($path_url) && $path_url === '/login'): ?>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif; ?>
<!-- HTML5 shim and Respond.js IE8 support of HTML5 -->
<!--[if lt IE 9]>
      <script src="js/html5shiv.js"></script>
      <script src="js/respond.min.js"></script>
      <script src="js/lte-ie7.js"></script>
    <![endif]-->
</head>
