<?php 
  extract($view_vars);
  $show_page_name = isset($show_page_name)&&$show_page_name===false ? false : true;
  $role_id = isset($_SESSION['utype'])&&$_SESSION['utype']>0?$_SESSION['utype']:0;
  $bc = isset($_SESSION['bc'])&&$_SESSION['bc']>0?$_SESSION['bc']:0;
  if(
      (isset($disable_sidebar) && $disable_sidebar) ||
      (isset($disable_footer)  && $disable_footer)
    ) {
    $main_content_margin = ' style="margin-left:0"';
    if(isset($body_class_name) && $body_class_name !== '') {
      $body_class_name = $body_class_name;
    } else {
      $body_class_name = '';
    }
    $body_class = ' class="'.$body_class_name.'"';
  } else {
    $main_content_margin = $body_class = '';
  }
?>
<!DOCTYPE html>
<html lang="en">
      
<!--head start-->
<?php include "partials/head.tpl.php" ?>
<!--head end-->

<body<?php echo $body_class ?> id="qbMain">

<?php if(isset($disable_layout) && $disable_layout): ?>

    <?php echo $content ?>

<?php else: ?>

  <div class="se-pre-con"></div>
  <!-- container section start -->
  <section id="container" class="">
    
    <!--header start-->
    <?php include "partials/header.tpl.php" ?>
    <!--header end-->
    
    <?php if( !isset($disable_sidebar) ): ?>
      <!--sidebar start-->
      <?php include "partials/$bc/sidebar-$role_id.tpl.php"; ?>
      <!--sidebar end-->
    <?php endif; ?>

    <form id="bQ"><input type="hidden" name="__bq_pub" id="__bq_pub" value="" /></form>
    
    <!--main content start-->
    <section id="main-content"<?php echo $main_content_margin ?>>
      <section class="wrapper"> 
        <!--content-->
        <?php echo $content ?>
        <!--content--> 
      </section>
      <?php if( !isset($disable_footer) ): ?>      
        <!--footer start-->
        <?php include "partials/footer.tpl.php" ?>
        <!--footer end-->
      <?php endif; ?>
    </section>

    <!--main content end--> 
  </section>
  <!-- container section start --> 
 
  <?php /*include "partials/link-js.tpl.php"*/ ?>

<?php endif; ?>

<?php include "partials/link-js.tpl.php" ?>


</body>
</html>