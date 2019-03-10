<?php
  use Atawa\Utilities;
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panel">
      <div class="panel-body">
        <?php echo Utilities::print_flash_message() ?>
        <div class="form-group" align="center">
          <h3 style="color:#cb5249;font-weight:bold;"><?php echo $device_name ?></h3>
          <p style="color: #225992;font-weight:bold;font-size:14px;">Send the above device name to your Administrator for accessing this app further. Without white-listing your device, you can not proceed.<br />Ignore if already registered.</p>
        </div>
      </div>
    </section>
  </div>
</div>