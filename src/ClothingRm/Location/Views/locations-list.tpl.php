<?php
  use Atawa\Utilities;
  use Atawa\Constants;
?>

<div class="row">
  <div class="col-lg-12">
    <section class="panelBox">
      <div class="panelBody">

        <?php echo Utilities::print_flash_message() ?>

        <?php if($page_error !== ''): ?>
          <div class="alert alert-danger" role="alert">
            <strong>Error!</strong> <?php echo $page_error ?> 
          </div>
        <?php endif; ?>

        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/location/create" class="btn btn-default">
              <i class="fa fa-location-arrow"></i> New Store
            </a> 
          </div>
        </div>
        <div class="table-responsive">
          <?php if(count($locations)>0): ?>
           <table class="table table-striped table-hover">
            <thead>
              <tr class="font12">
                <th width="5%"  class="text-center valign-middle">Sno</th>
                <th width="18%" class="text-center valign-middle">Store name</th>
                <th width="15%" class="text-center valign-middle">Address1</th>
                <th width="15%" class="text-center valign-middle">Address2</span></th>
                <th width="20%" class="text-center valign-middle">City &amp; State</span></th>
                <th width="5%" class="text-center valign-middle">Pincode</span></th>
                <th width="5%" class="text-center valign-middle">GST No.</span></th>                
                <th width="9%" class="text-center valign-middle">Status</th>
                <th width="6%" class="text-center valign-middle">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
                $cntr = 1;
                foreach($locations as $location_details):
                  $location_name = $location_details['locationName'];
                  $location_code = $location_details['locationCode'];
                  $address1 = $location_details['address1'];
                  $address2 = $location_details['address2'];
                  $state_id = $location_details['stateID'];
                  if($location_details['cityName'] !== '') {
                    $city_name = $location_details['cityName'].',';
                  } else {
                    $city_name = '';
                  }
                  if($state_id>0) {
                    $state_name = $states_a[$state_id];
                  } else {
                    $state_name = '';
                  }
                  $pincode = $location_details['pincode'];
                  $gst_no = $location_details['gstNo'];
                  if((int)$location_details['status'] === 1) {
                    $location_status = '<span style="color:green; font-weight:bold;"><i class="fa fa-check" aria-hidden="true"></i>&nbsp;Active</span>';
                  } elseif((int)$location_details['status'] === 0) {
                    $location_status = '<span style="color:red; font-weight:bold;"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>&nbsp;Inactive</span>';
                  } elseif((int)$location_details['status'] === 2) {
                    $location_status = '<span style="color:orange; font-weight:bold;"><i class="fa fa-times" aria-hidden="true"></i>&nbsp;Suspended</span>';
                  } else {
                    $location_status = 'Unknown';
                  }
              ?>
                <tr class="font11">
                  <td align="right" class="valign-middle"><?php echo $cntr ?></td>
                  <td class="valign-middle"><?php echo $location_name ?></td>
                  <td class="valign-middle"><?php echo $address1 ?></td>
                  <td class="valign-middle"><?php echo $address2 ?></td>
                  <td class="valign-middle"><?php echo $city_name.$state_name ?></td>                
                  <td class="valign-middle"><?php echo $pincode ?></td>
                  <td class="valign-middle"><?php echo $gst_no ?></td>
                  <td class="valign-middle" style="text-align: left;"><?php echo $location_status ?></td>
                  <td class="valign-middle" style="text-align: center;">
                  <?php if($location_code !== ''): ?>
                    <div class="btn-actions-group" align="right">                    
                      <a class="btn btn-primary" href="/location/update/<?php echo $location_code ?>" title="Edit Store Details">
                        <i class="fa fa-pencil"></i>
                      </a>&nbsp;
                    </div>
                  <?php endif; ?>
                  </td>
                </tr>
              <?php
                $cntr++;
                endforeach; 
              ?>
            </tbody>
          </table>
          <?php endif; ?>
        </div>
      </div>
    </section>
  </div>
</div>