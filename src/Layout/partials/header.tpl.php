<?php 
  if(isset($_SESSION['uname']) && $_SESSION['uname'] !== '') {
    $uname = substr($_SESSION['uname'],0,10);
  } else {
    $uname = 'My Profile';
  }
?>
<nav class="navbar navbar-default">
<section id="container" class="">
  <header class="header dark-bg"> 
      <div class="navbar-header">
        <span type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#user-status" aria-expanded="false"><i class="fa fa-bars"></i></span>
        <a href="/" class="logo">
          <img src="/assets/img/logo.png" alt="Atawa" /> 
        </a>
      </div>
    	<div class="collapse navbar-collapse" id="user-status">
      	<div class="top-nav notification-row">
          <?php if( isset($_SESSION['token_valid']) && $_SESSION['token_valid'] ): ?>
            <div class="pull-right last-seen">
              <?php echo date("dS F, Y | h:ia").' (IST)'; ?>
            </div>
            <ul class="nav pull-right top-menu">
              <li class="dropdown"> <i class="fa fa-info-circle"></i><a data-toggle="dropdown" class="dropdown-toggle" href="#"> <span class="profile-ava"></span> <span class="username">Helpline</span> <b class="caret"></b> </a>
                <ul class="dropdown-menu extended">
                  <div class="log-arrow-up"></div>
                  <li class="eborder-top ff-contact red"><b>Feel free to contact us:</b></li>
                  <li> <a href="#"><i class="fa fa-phone"></i> 91 98490 11005</a> </li>
                  <li> <a href="mailto:support@qwikbills.com"><i class="fa fa-envelope"></i> support@atawa.net</a> </li>
                </ul>
              </li>
              <li id="user-info" class="dropdown"> 
                <i class="fa fa-user"></i>
                <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                  <span class="profile-ava"></span> <span class="username"><?php echo $uname ?></span>
                  <b class="caret"></b>
                </a>
                <ul class="dropdown-menu extended logout">
                  <div class="log-arrow-up"></div>
                  <li> <a href="/device/show-name"><i class="icon_cog"></i> My Device Name</a> </li>                  
                  <li> <a href="/me"><i class="icon_pencil-edit"></i> Edit My Account</a> </li>
                  <li> <a href="/logout"><i class="icon_key_alt"></i> Logout</a> </li>
                </ul>
              </li>
            </ul>
          <?php endif; ?>
      </div>
      </div>
      <div class="theme-name">
      	<h1><?php echo (isset($_SESSION['cname'])&&$_SESSION['cname']!=''?$_SESSION['cname']:'') ?></h1>
      </div>
  </header>
  <?php if( isset($_SESSION['token_valid']) && $_SESSION['token_valid'] && $show_page_name ): ?>
    <div class="pageHeader">
      <h3 class="page-header">
        <i class="<?php echo (isset($icon_name) && $icon_name != '' ? $icon_name : '') ?>"></i> 
        <?php echo (isset($page_title) && $page_title != '' ? $page_title : '') ?>
      </h3>
    </div>
  <?php endif; ?> 
</section>
</nav>