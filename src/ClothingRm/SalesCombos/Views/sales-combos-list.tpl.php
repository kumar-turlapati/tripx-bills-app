<?php
  use Atawa\Utilities;
  use Atawa\Constants;
  
  // dump($search_params);
  // dump($location_ids);
  // exit;

  $query_params = '';
  if(isset($search_params['locationCode']) && $search_params['locationCode'] !== '' ) {
    $locationCode = $search_params['locationCode'];
    $query_params[] = 'locationCode='.$locationCode;
  } else {
    $locationCode = '';
  }  

  if(is_array($query_params) && count($query_params)>0) {
    $query_params = '?'.implode('&', $query_params);
  }

  $page_url = '/sales-combo/list';
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
            <a href="/sales-combo/add" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> New Sales Combo 
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
          <?php if(count($combos)>0): ?>
           <table class="table table-striped table-hover">
            <thead>
              <tr class="font12">
                <th width="5%" class="text-center valign-middle">Sno</th>
                <th width="30%" class="text-center valign-middle">Combo name</th>                
                <th width="15%" class="text-center valign-middle">Combo numeric code</th>
                <th width="15%" class="text-center valign-middle">Total items</th>
                <th width="20%" class="text-center valign-middle">Store name</th>                
                <th width="10%" class="text-center valign-middle">Status</span></th>                
                <th width="10%" class="text-center valign-middle">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php 
                $total = $cntr = 0;
                foreach($combos as $combo_details):
                  $cntr++;
                  $combo_name = $combo_details['comboName'];
                  $combo_code = $combo_details['comboCode'];
                  $combo_num_code = $combo_details['comboNumber'];
                  $total_items = $combo_details['noOfProducts'];
                  $status = Constants::$RECORD_STATUS[$combo_details['status']];
                  $location_name = isset($location_ids[$combo_details['locationID']]) ? $location_ids[$combo_details['locationID']] : '';
              ?>
                <tr class="font12">
                  <td align="right" class="valign-middle"><?php echo $cntr ?></td>
                  <td style="font-weight:bold;" class="valign-middle"><?php echo $combo_name ?></td>                  
                  <td align="left" class="valign-middle"><?php echo $combo_num_code ?></td>
                  <td align="left" class="valign-middle"><?php echo $total_items ?></td>
                  <td align="center" class="valign-middle"><?php echo $location_name ?></td>
                  <td align="center" class="valign-middle"><?php echo $status ?></td>
                  <td>
                  <?php if($combo_code !== ''): ?>
                    <div class="btn-actions-group" align="right">                    
                      <a class="btn btn-primary" href="/sales-combo/update/<?php echo $combo_code ?>" title="Edit Sales Combo">
                        <i class="fa fa-pencil"></i>
                      </a>
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