<?php
  $page_url = '/reports/payables';
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
                <?php /*
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
                </div> */ ?>              
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
                <div class="col-sm-12 col-md-2 col-lg-1">
                  <input placeholder="Days 1" type="text" name="aging1" id="aging1" class="form-control" title="Aging Days 1" />
                </div>
                <div class="col-sm-12 col-md-2 col-lg-1">
                  <input placeholder="Days 2" type="text" name="aging2" id="aging2" class="form-control" title="Aging Days 2"  />
                </div>
                <div class="col-sm-12 col-md-2 col-lg-1">
                  <input placeholder="Days 3" type="text" name="aging3" id="aging3" class="form-control" title="Aging Days 3"  />
                </div>
                <div class="col-sm-12 col-md-2 col-lg-1">
                  <input placeholder="Days 4" type="text" name="aging4" id="aging4" class="form-control" title="Aging Days 4"  />
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