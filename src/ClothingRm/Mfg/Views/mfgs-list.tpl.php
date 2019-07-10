<?php 
  $query_params = [];  
  if(isset($search_params['locationCode']) && $search_params['locationCode'] !== '') {
    $locationCode = $search_params['locationCode'];
    $query_params[] = 'locationCode='.$locationCode;
  } else {
    $locationCode = '';
  } 
  if($query_params != '') {
    $query_params = '?'.implode('&', $query_params);
  }
  $page_url = $pagination_url = '/mfgs/list';
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panelBox">
      <div class="panelBody">
        <?php echo $flash_obj->print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/mfg/create" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> Create Brand / Manufacturer 
            </a> 
          </div>
        </div>
        <div class="filters-block">
          <div id="filters-form">
            <form class="form-validate form-horizontal" method="POST">
              <div class="form-group">
                <div class="col-sm-12 col-md-1 col-lg-1">Filter by</div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">
                    <select class="form-control" name="locationCode" id="locationCode">
                      <?php 
                        foreach($client_locations as $location_key=>$value):
                          $location_key_a = explode('`', $location_key);
                          if($locationCode === $location_key_a[0]) {
                            $selected = 'selected="selected"';
                          } else {
                            $selected = '';
                          }  
                      ?>
                       <option value="<?php echo $location_key_a[0] ?>" <?php echo $selected ?>>
                          <?php echo $value ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                   </div>
                </div>
                <?php include_once __DIR__."/../../../Layout/helpers/filter-buttons.helper.php" ?>
              </div>
           </form>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-striped table-hover font12">
            <thead>
              <tr>
                <th width="5%" class="text-center">Sno</th>
                <th width="30%" class="text-center">Brand / Manufacturer name</th>
                <th width="5%" class="text-center">Status</th>                
                <th width="10%" class="text-center">Options</th>
              </tr>
            </thead>
            <tbody>
            <?php if(count($mfgs)>0): ?>
                <?php 
                  $cntr = $sl_no;
                  $total_item_count = 0;
                  foreach($mfgs as $mfg_details):
                    if($mfg_details['mfgName'] !== '') {
                      $mfg_name = $mfg_details['mfgName'];
                    } else {
                      $mfg_name = '';
                    }
                    if($mfg_details['mfgCode'] !== '') {
                      $mfg_code = $mfg_details['mfgCode'];
                    } else {
                      $mfg_code = '';
                    }
                    if((int)$mfg_details['status'] === 0) {
                      $status = 'Inactive';
                    } elseif((int)$mfg_details['status'] === 1) {
                      $status = 'Active';
                    }
                    $location_id = $mfg_details['locationID'];
                    $location_code = isset($location_codes[$location_id]) ? $location_codes[$location_id] : '';
                ?>
                  <tr class="text-right font11">
                    <td class="text-right valign-middle"><?php echo $cntr ?></td>
                    <td class="text-left valign-middle"><?php echo $mfg_name ?></td>
                    <td class="text-left valign-middle"><?php echo $status ?></td>
                    <td class="valign-middle">
                      <div class="btn-actions-group text-left" style="padding-left:10px;">
                        <?php if($mfg_code !== ''): ?>
                          <a class="btn btn-primary" href="/mfg/update/<?php echo $mfg_code ?>?lc=<?php echo $location_code ?>" title="Edit this record">
                            <i class="fa fa-pencil"></i>
                          </a>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
              <?php
                $cntr++;
                endforeach; 
              ?>
            <?php else: ?>
                <tr><td colspan="4" align="center" class="font14">No records found.</td></tr>
            <?php endif; ?>
            </tbody>
          </table>
          <?php include_once __DIR__."/../../../Layout/helpers/pagination.helper.php" ?>          
        </div>
      </div>
    </section>
  </div>
</div>
