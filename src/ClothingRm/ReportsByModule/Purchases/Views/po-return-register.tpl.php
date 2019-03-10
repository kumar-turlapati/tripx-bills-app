<?php
  $from_date = date('01-m-Y');
  $to_date = $current_date = date('d-m-Y');
  $page_url = '/reports/po-return-register';
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
      						  <input class="span2" size="16" type="text" readonly name="fromDate" id="fromDate" value="<?php echo $from_date ?>" />
      						  <span class="add-on"><i class="fa fa-calendar"></i></span>
      					  </div>
      				  </div>
      				  <div class="col-sm-12 col-md-2 col-lg-2">
      						<div class="input-append date" data-date="<?php echo $current_date ?>" data-date-format="dd-mm-yyyy">
      						  <input class="span2" size="16" type="text" readonly name="toDate" id="toDate" value="<?php echo $to_date ?>" />
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
                    <select class="form-control" name="supplierCode" id="supplierCode">
                      <?php 
                        foreach($suppliers as $key=>$value):  
                      ?>
                       <option value="<?php echo $key ?>">
                          <?php echo $value ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                   </div>
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">
                    <select class="form-control" name="groupBy" id="groupBy">
                      <?php foreach($group_by_a as $key => $value): ?>
                        <option value="<?php echo $key ?>"><?php echo $value ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>                
                <?php /*
                <div class="col-sm-12 col-md-1 col-lg-1">
                  <input placeholder="Item" type="text" name="itemName" id="itemName" class="form-control inameAc" />
                </div>
                <div class="col-sm-12 col-md-1 col-lg-1">
                  <input placeholder="Brand" type="text" name="brandName" id="brandName" class="form-control brandAc" />
                </div> */ ?>
                <div class="col-sm-12 col-md-1 col-lg-1">
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