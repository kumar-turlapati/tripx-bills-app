<?php
  if(is_array($org_summary) && count($org_summary)>0) {
    $data_records = $org_summary['recordsCount'];
    $locations_count = $org_summary['locationsCount'];
    $db_size = $org_summary['dbSize'];
    $active_users = $org_summary['activeUsers'];
    $active_devices = $org_summary['totalDevices'];
?>
  <div class="col-md-6">
    <div class="widgetSec">
      <div class="widgetHeader"><i class="fa fa-bar-chart"></i> Data Summary</div>
      <div class="widgetContent">
        <table class="table priceTable">
          <tbody>
            <?php foreach($data_records as $key => $data_record): ?>
              <tr>
                <td><?php echo $data_record['feature_type'] ?></td>
                <td align="right"><?php echo $data_record['feature_totals'] ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="widgetSec">
      <div class="widgetHeader"><i class="fa fa-database"></i> Database</div>
      <div class="widgetContent">
        <table class="table priceTable">
          <tbody>
            <tr>
              <td>Active Stores</td>
              <td align="right"><?php echo $locations_count ?></td>
            </tr>
            <tr>
              <td>Total DB Size</td>
              <td align="right"><span style="font-size:30px;"><?php echo $db_size ?></span><span style="font-size:12px;">MB</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="widgetSec">
      <div class="widgetHeader"><i class="fa fa-user-circle"></i> Accounts</div>
      <div class="widgetContent">
        <table class="table priceTable">
          <tbody>
            <tr>
              <td>Active System Users</td>
              <td align="right"><?php echo $active_users ?></td>
            </tr>
            <tr>
              <td>Active Devices</td>
              <td align="right"><?php echo $active_devices ?></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
<?php } else {  ?>
  <div class="row">
    <div class="col-lg-12"> 
      <section class="panel">
        <div class="panel-body">
          <div class="form-group" align="center">
            <h3 style="color:red;font-weight:bold;font-size:14px;"><i class="fa fa-exclamation-triangle"></i>&nbsp;We are unable to retrieve your Org information at this moment. Please try after sometime.</h3>
          </div>
        </div>
      </section>
    </div>
  </div>
<?php } ?>