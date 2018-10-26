<?php
  if(is_array($view_vars) && count($view_vars)>0) {
    extract($view_vars);
  }
?>
<!DOCTYPE html>
<html lang="en">
<?php include "partials/head.tpl.php" ?>
<body class="loginPage">
  <section id="container" class=""> 
    <?php include "partials/header.tpl.php" ?>
    <section>
      <section class="wrapper">
        <form id="bQ"><input type="hidden" name="__bq_pub" id="__bq_pub" value="" /></form>        
        <form class="login-form" action="/login" method="POST" autocomplete="off">        
          <div class="login-wrap">
            <h2 class="text-center">Login to Your Account</h2>
            <?php if(isset($error) && $error !== ''): ?>
              <div><?php echo $error ?></div>
            <?php endif; ?>  
            <div class="input-group">
              <span class="input-group-addon">
                <i class="icon_profile"></i>
              </span>
              <input type="text" class="form-control" placeholder="Username" autofocus name="userid" id="userid" />
            </div>
            <div class="input-group">
              <span class="input-group-addon"><i class="icon_key_alt"></i></span>
              <input type="password" class="form-control" placeholder="Password" name="pass" id="pass" />
            </div>
            <div class="input-group forgot-pass">
              <a href="/forgot-password">Forgot password?</a>
            </div>
            <div class="g-recaptcha" data-sitekey="<?php echo $site_key ?>"></div>
            <button class="btn btn-primary btn-lg btn-block" type="submit">Login</button>
            <div class="input-group login-copyrights">
              <p>Powered by&nbsp;
                <a href="http://tripexpert.co.in/" target="_blank">
                  <img src="/assets/img/tripexpert-logo.png">
                </a>
              </p>
            </div>
          </div>
        </form>
      </section>
    </section>
  </section>
  <?php include "partials/link-js.tpl.php" ?>
</body>
</html>