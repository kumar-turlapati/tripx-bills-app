<?php
?>
<div class="row">
  <div class="col-lg-12">
    <section class="panel">
      <div class="panel-body" align="center">
        <?php if(is_null($refreshed)) : ?>
          <form class="form-validate form-horizontal" method="POST" autocomplete="off" id="refreshCbForm">
            <input type="hidden" name="refreshHidden" value="1" />
            <h3 id="infoText">Are you sure. You want to refresh the closing balances?</h3>
            <div class="text-center">
              <button class="btn btn-primary" name="Refresh" id="refreshCbYes"><i class="fa fa-refresh"></i> Refresh</button>&nbsp;
              <button class="btn btn-danger cancelButton" id="refreshCbWoIndentsCancel"><i class="fa fa-times"></i> Cancel</button>
            </div>
          </form>
        <?php else : ?>
          <?php if($refreshed): ?>
            <h2>Stock refreshed successfully! <i class="fa fa-smile-o" aria-hidden="true"></i></h2>
          <?php else: ?>
            <h2>Unable to refresh Stock <i class="fa fa-frown-o" aria-hidden="true"></i></h2>
          <?php endif; ?>
          <div class="text-center">
            <button class="btn btn-primary" onclick="window.location.href='/dashboard'">
              <i class="fa fa-home"></i> Goto Dashboard
            </button>
          </div>
        <?php endif; ?>
      </div>
    </section>
  </div>
</div>