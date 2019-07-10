<?php
  use Atawa\Utilities;
  use Atawa\Constants;
  
  $current_date = date("d-m-Y");

  // dump($search_params);

  $query_params = [];
  if(isset($search_params['startDate']) && $search_params['startDate'] !='') {
    $startDate = $search_params['startDate'];
    $query_params[] = 'startDate='.$startDate;
  } else {
    $startDate = $current_date;
  }
  if(isset($search_params['endDate']) && $search_params['endDate'] !='' ) {
    $endDate = $search_params['endDate'];
    $query_params[] = 'endDate='.$endDate;
  } else {
    $endDate = $current_date;
  }
  if(isset($search_params['offerType']) && $search_params['offerType'] !== '' ) {
    $offerType = $search_params['offerType'];
    $query_params[] = 'offerType='.$offerType;
  } else {
    $offerType = '';
  }
  if(isset($search_params['locationCode']) && $search_params['locationCode'] !== '' ) {
    $locationCode = $search_params['locationCode'];
    $query_params[] = 'locationCode='.$locationCode;
  } else {
    $locationCode = $default_location;
  }  

  if(is_array($query_params) && count($query_params)>0) {
    $query_params = '?'.implode('&', $query_params);
  }

  $page_url = '/promo-offers/list';
?>
<div class="row">
  <div class="col-lg-12">
    <section class="panelBox">
      <h2 class="hdg-reports text-center">List Promo Offers</h2>
      <div class="panelBody">
        <?php echo Utilities::print_flash_message() ?>
        <?php if($page_error !== ''): ?>
          <div class="alert alert-danger" role="alert">
            <strong>Error!</strong> <?php echo $page_error ?> 
          </div>
        <?php endif; ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/promo-offers/entry" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> New Promo Offer 
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
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">
                    <select class="form-control" name="offerType" id="offerType">
                      <?php 
                        foreach($offer_types as $key=>$value): 
                          if((int)$offer_type === (int)$key) {
                            $selected = 'selected="selected"';
                          } else {
                            $selected = '';
                          }                      
                      ?>
                        <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                      <?php endforeach; ?>
                    </select>
                   </div>
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="input-append date" data-date="<?php echo $current_date ?>" data-date-format="dd-mm-yyyy">
                    <input class="span2" size="16" type="text" readonly name="startDate" id="startDate" value="<?php echo $startDate ?>" />
                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                  </div>
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="input-append date" data-date="<?php echo $current_date ?>" data-date-format="dd-mm-yyyy">
                    <input class="span2" size="16" type="text" readonly name="endDate" id="endDate" value="<?php echo $endDate ?>" />
                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                  </div>
                </div>                
                <?php include_once __DIR__."/../../../Layout/helpers/filter-buttons.helper.php" ?>
            </div>
           </form>        
			    </div>
        </div>
        <div class="table-responsive">
          <?php if(count($offers)>0): ?>
           <table class="table table-striped table-hover">
            <thead>
              <tr class="font12">
                <th width="5%" class="text-center valign-middle">Sno</th>
                <th width="10%" class="text-center valign-middle">Offer code</th>                
                <th width="18%" class="text-center valign-middle">Offer description</th>
                <th width="32%" class="text-center valign-middle">Offer type</th>                
                <th width="9%" class="text-center valign-middle">Start date</th>
                <th width="9%" class="text-center valign-middle">End date</span></th>
                <th width="8%" class="text-center valign-middle">Status</span></th>                
                <th width="8%" class="text-center valign-middle">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php 
                $cntr = $sl_no;
                $total = 0;
                foreach($offers as $offer_details):
                  $offer_desc = $offer_details['promoDesc'];
                  $offer_code = $offer_details['promoCode'];
                  $offer_type = Constants::$PROMO_OFFER_CATEGORIES_DIGITS[$offer_details['promoType']];
                  $start_date = date('d-M-Y', strtotime($offer_details['startDate']));
                  $end_date = date('d-M-Y', strtotime($offer_details['endDate']));
                  $status = Constants::$RECORD_STATUS[$offer_details['status']];
                  $location_code = isset($location_codes[$offer_details['locationID']]) ? $location_codes[$offer_details['locationID']] : '';
              ?>
                <tr class="font12">
                  <td align="right" class="valign-middle"><?php echo $cntr ?></td>
                  <td style="font-weight:bold;" class="valign-middle"><?php echo $offer_code ?></td>                  
                  <td align="left" class="valign-middle"><?php echo $offer_desc ?></td>
                  <td align="left" class="valign-middle"><?php echo $offer_type ?></td>
                  <td align="center" class="valign-middle"><?php echo $start_date ?></td>
                  <td align="center" class="valign-middle"><?php echo $end_date ?></td>
                  <td align="center" class="valign-middle"><?php echo $status ?></td>      
                  <td>
                  <?php if($offer_code !== ''): ?>
                    <div class="btn-actions-group" align="right">                    
                      <a class="btn btn-primary" href="/promo-offers/update/<?php echo $offer_code ?>?lc=<?php echo $location_code ?>" title="Edit Promotional Offer">
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
          <?php include_once __DIR__."/../../../Layout/helpers/pagination.helper.php" ?>
        </div>
      </div>
    </section>
  </div>
</div>