<?php
  use Atawa\Utilities;
  $location_code = isset($search_params['locationCode']) ? $search_params['locationCode'] : '';
  $finy_code =  isset($search_params['finyCode']) ? $search_params['finyCode'] : '';
  $page_url = $pagination_url = '/finy-slnos/list';
?>
<div class="row">
  <div class="col-lg-12">
    <section class="panelBox">
      <div class="panelBody">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/finy-slnos/create" class="btn btn-default">
              <i class="fa fa-sort-numeric-asc"></i> New FY Serial Numbers 
            </a>&nbsp;&nbsp;
            <a href="/finy/create" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> New Financial Year 
            </a>            
          </div>
        </div>
        <div class="filters-block">
          <div id="filters-form">
            <form class="form-validate form-horizontal" method="POST" action="<?php echo $page_url ?>" autocomplete="off">
              <div class="form-group">
                <div class="col-sm-12 col-md-1 col-lg-1" style="padding-top:5px;"><b>Filter by</b></div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">
                    <select class="form-control" name="finyCode" id="finyCode">
                      <?php 
                        foreach($finys as $key=>$value):
                          if($finy_code === $key) {
                            $selected = 'selected="selected"';
                          } else {
                            $selected = '';
                          }
                      ?>
                       <option value="<?php echo $key ?>" <?php echo $selected ?>>
                          <?php echo $value ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">
                    <select class="form-control" name="locationCode" id="locationCode">
                      <?php 
                        foreach($client_locations as $location_key=>$value):
                          $location_key_a = explode('`', $location_key);
                          if($location_code === $location_key_a[0]) {
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
                <?php include_once __DIR__."/../../../src/Layout/helpers/filter-buttons.helper.php" ?>
              </div>
            </form>
          </div>
        </div>
        <div class="table-responsive" align="center">
          <table class="table table-striped table-hover font12" style="width:70%;">
            <thead>
              <tr>
                <th width="5%" class="text-left">Sno.</th>
                <th width="5%" class="text-center">Voucher Type</th>
                <th width="10%" class="text-center">Voucher Text</th>
                <th width="10%" class="text-center">Voucher Number</th>
                <th width="10%" class="text-center">Year Short Name</th>                
                <th width="10%" class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
            <?php if( count($slnos) > 0 ) { ?>
              <?php 
                $cntr = 1;
                foreach($slnos as $slno_details):
                  $voc_type = $slno_details['vocType'];
                  $voc_text = $slno_details['slnoText'];
                  $voc_number = $slno_details['slnoAic'];
                  $finy_short_name = $slno_details['yearShotName'];
                  $finy_code = $slno_details['finyCode'];
                  $finy_slno_code = $slno_details['finySlnoCode'];
              ?>
                  <tr class="text-right font12">
                    <td class="text-right valign-middle"><?php echo $cntr ?></td>
                    <td class="text-left  valign-middle"><?php echo $voc_type ?></td>
                    <td class="text-left  valign-middle"><?php echo $voc_text ?></td>
                    <td class="text-right valign-middle"><?php echo $voc_number ?></td>
                    <td class="text-left  valign-middle"><?php echo $finy_short_name ?></td>
                    <td>
                      <div class="btn-actions-group">
                        <?php if($finy_slno_code !== ''): ?>
                          <a class="btn btn-primary" href="/finy-slnos/update/<?php echo $finy_slno_code ?>" title="Edit Financial Year Details">
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
            <?php } else { ?>
              <tr><td colspan="6" align="center"><b>No Slnos are created as of now.</b></td></tr>
            <?php } ?>
            </tbody>
          </table>
          <?php include_once __DIR__."/../../../src/Layout/helpers/pagination.helper.php" ?>
        </div>
      </div>
    </section>
  </div>
</div>