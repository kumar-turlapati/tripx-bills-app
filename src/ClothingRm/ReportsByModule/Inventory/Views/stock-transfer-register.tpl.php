<?php
  $current_date = $toDate = date("d-m-Y");
  $fromDate = '01-'.date("m-Y");
  $category_code = '';
  $page_url = '/reports/stock-transfer-register';
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
                <div class="col-sm-12 col-md-1 col-lg-1" style="padding-top:5px;text-align:right;font-weight:bold;">Filters</div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="input-append date" data-date="<?php echo $current_date ?>" data-date-format="dd-mm-yyyy">
                    <input class="span2" size="16" type="text" readonly name="fromDate" id="fromDate" value="<?php echo $fromDate ?>" />
                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                  </div>
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="input-append date" data-date="<?php echo $current_date ?>" data-date-format="dd-mm-yyyy">
                    <input class="span2" size="16" type="text" readonly name="toDate" id="toDate" value="<?php echo $toDate ?>" />
                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                  </div>
                </div>                
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">
                    <select class="form-control" name="locationCode" id="locationCode">
                      <?php 
                        foreach($client_locations as $location_key=>$value):
                          $location_key_a = explode('`', $location_key);
                      ?>
                       <option value="<?php echo $location_key_a[0] ?>">
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