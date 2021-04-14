<?php
  if(is_array($view_vars) && count($view_vars)>0) {
    extract($view_vars);
  }
?>
<!DOCTYPE html>
<html lang="en">
<?php include "partials/head.tpl.php" ?>
<body class="loginPage">
  <script type="text/javascript">
    function submitLoginForm() {
      document.getElementById("loginForm").submit();
    }
  </script>
  <section id="container" class=""> 
    <?php include "partials/header.tpl.php" ?>
    <section>
      <section class="wrapper">
        <form id="bQ"><input type="hidden" name="__bq_pub" id="__bq_pub" value="" /></form>        
        <form class="login-form" action="/login" method="POST" autocomplete="off" id="loginForm">
          <div class="login-wrap">
            <h2 class="text-center">Login to Your Account</h2>
            <?php if(isset($error) && $error !== ''): ?>
              <div style="font-size:11px;font-weight:bold;color:red;text-align:left;padding-bottom:10px;"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>&nbsp;<?php echo $error ?></div>
            <?php endif; ?>
            <div class="input-group">
              <span class="input-group-addon">
                <i class="icon_profile"></i>
              </span>
              <input type="text" class="form-control" placeholder="User name" autofocus name="userid" id="userid" />
            </div>
            <div class="input-group">
              <span class="input-group-addon"><i class="icon_key_alt"></i></span>
              <input type="password" class="form-control" placeholder="Password" name="pass" id="pass" />
            </div>
            <div class="input-group forgot-pass">
              <a href="/forgot-password">Forgot password?</a>
            </div>
            <?php /* <div class="g-recaptcha" data-sitekey="<?php echo $site_key ?>"></div> */ ?>

            <div class="whatsappNotifications">
              <span>
                <svg xmlns="http://www.w3.org/2000/svg" width="39" height="39" viewBox="0 0 39 39">
                  <path fill="#00E676" d="M10.7 32.8l.6.3c2.5 1.5 5.3 2.2 8.1 2.2 8.8 0 16-7.2 16-16 0-4.2-1.7-8.3-4.7-11.3s-7-4.7-11.3-4.7c-8.8 0-16 7.2-15.9 16.1 0 3 .9 5.9 2.4 8.4l.4.6-1.6 5.9 6-1.5z"></path><path fill="#FFF" d="M32.4 6.4C29 2.9 24.3 1 19.5 1 9.3 1 1.1 9.3 1.2 19.4c0 3.2.9 6.3 2.4 9.1L1 38l9.7-2.5c2.7 1.5 5.7 2.2 8.7 2.2 10.1 0 18.3-8.3 18.3-18.4 0-4.9-1.9-9.5-5.3-12.9zM19.5 34.6c-2.7 0-5.4-.7-7.7-2.1l-.6-.3-5.8 1.5L6.9 28l-.4-.6c-4.4-7.1-2.3-16.5 4.9-20.9s16.5-2.3 20.9 4.9 2.3 16.5-4.9 20.9c-2.3 1.5-5.1 2.3-7.9 2.3zm8.8-11.1l-1.1-.5s-1.6-.7-2.6-1.2c-.1 0-.2-.1-.3-.1-.3 0-.5.1-.7.2 0 0-.1.1-1.5 1.7-.1.2-.3.3-.5.3h-.1c-.1 0-.3-.1-.4-.2l-.5-.2c-1.1-.5-2.1-1.1-2.9-1.9-.2-.2-.5-.4-.7-.6-.7-.7-1.4-1.5-1.9-2.4l-.1-.2c-.1-.1-.1-.2-.2-.4 0-.2 0-.4.1-.5 0 0 .4-.5.7-.8.2-.2.3-.5.5-.7.2-.3.3-.7.2-1-.1-.5-1.3-3.2-1.6-3.8-.2-.3-.4-.4-.7-.5h-1.1c-.2 0-.4.1-.6.1l-.1.1c-.2.1-.4.3-.6.4-.2.2-.3.4-.5.6-.7.9-1.1 2-1.1 3.1 0 .8.2 1.6.5 2.3l.1.3c.9 1.9 2.1 3.6 3.7 5.1l.4.4c.3.3.6.5.8.8 2.1 1.8 4.5 3.1 7.2 3.8.3.1.7.1 1 .2h1c.5 0 1.1-.2 1.5-.4.3-.2.5-.2.7-.4l.2-.2c.2-.2.4-.3.6-.5s.4-.4.5-.6c.2-.4.3-.9.4-1.4v-.7s-.1-.1-.3-.2z"></path>
                </svg>
              </span>
              <span>
                 I want to receive otps, inventory updates and sales updates on <b>WhatsApp</b>.
              </span>
              <span>
                <input 
                  type="checkbox" 
                  class="form-control" 
                  name="whatsappOptIn" 
                  title="Click here to subscribe for WhatsApp updates."
                />
              </span>
            </div>
            <div align="center">
              <button 
                class="btn btn-warning btn-lg btn-block g-recaptcha" 
                type="submit" 
                style="width: 50%;"
                data-sitekey="<?php echo $site_key ?>" 
                data-callback="submitLoginForm"
                data-action="submit"
              >Login</button>
            </div>
            <div class="input-group login-copyrights">
              <p>Powered by <span style="color: #225992; font-weight: bold;">Octet Logic</span></p>
            </div>
          </div>
        </form>
      </section>
    </section>
  </section>
  <?php include "partials/link-js.tpl.php" ?>
</body>
</html>