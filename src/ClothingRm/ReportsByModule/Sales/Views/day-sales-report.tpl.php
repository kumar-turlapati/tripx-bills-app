<?php
  $current_date = date("d-m-Y");
  $query_params = '';
  if(isset($search_params['saleDate']) && $search_params['saleDate'] !=='') {
    $saleDate = $search_params['saleDate'];
    $query_params[] = 'saleDate='.$saleDate;
  } else {
    $saleDate = $current_date;
  }
  if(is_array($query_params) && count($query_params)>0) {
    $query_params = '?'.implode('&', $query_params);
  }
  $page_url = '/reports/day-sales';
?>
<div class="row">
  <div class="col-lg-12">
    <section class="panelBox">
      <div class="panelBody">
        <?php echo $flash_obj->print_flash_message() ?>
  		  <div class="filters-block">
    			<div id="filters-form">
    			  <form class="form-validate form-horizontal" method="POST">
      				<div class="form-group">
                <div class="col-sm-12 col-md-1 col-lg-1">Filters</div>
      				  <div class="col-sm-12 col-md-2 col-lg-2">
      						<div class="input-append date" data-date="<?php echo $current_date ?>" data-date-format="dd-mm-yyyy">
      						  <input class="span2" size="16" type="text" readonly name="saleDate" id="saleDate" value="<?php echo $saleDate ?>" />
      						  <span class="add-on"><i class="fa fa-calendar"></i></span>
      					  </div>
      				  </div>
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
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">
                    <select class="form-control" name="format" id="format">
                      <?php 
                        foreach($format_options as $key=>$value):
                      ?>
                        <option value="<?php echo $key ?>"><?php echo $value ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
      				</div>
              <div class="form-group text-center">
                <?php include_once __DIR__."/../../../../Layout/helpers/filter-buttons-reports.helper.php" ?>
              </div>
    			  </form>
    			</div>
        </div>
      </div>
    </section>
  </div>
</div>