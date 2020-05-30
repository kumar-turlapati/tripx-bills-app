<?php
  use Atawa\Utilities;
  use Atawa\Constants;

  $query_params = [];
  if(isset($search_params['itemName']) && $search_params['itemName'] !='') {
    $itemName = $search_params['itemName'];
    $query_params[] = 'itemName='.$itemName;
  } else {
    $itemName = '';
  }  
  if(isset($search_params['lotNo']) && $search_params['lotNo'] !='') {
    $lotNo = $search_params['lotNo'];
    $query_params[] = 'lotNo='.$lotNo;
  } else {
    $lotNo = '';
  }
  if(isset($search_params['poNo']) && $search_params['poNo'] !='' ) {
    $poNo = $search_params['poNo'];
    $query_params[] = 'poNo='.$poNo;
  } else {
    $poNo = '';
  }
  if(isset($search_params['barcode']) && $search_params['barcode'] !== '' ) {
    $barcode = $search_params['barcode'];
    $query_params[] = 'barcode='.$barcode;
  } else {
    $barcode = '';
  }
  if(isset($search_params['bno']) && $search_params['bno'] !== '' ) {
    $bno = $search_params['bno'];
    $query_params[] = 'bno='.$bno;
  } else {
    $bno = '';
  }
  if(isset($search_params['cno']) && $search_params['cno'] !== '' ) {
    $cno = $search_params['cno'];
    $query_params[] = 'cno='.$bno;
  } else {
    $cno = '';
  }
  if(isset($search_params['itemSku']) && $search_params['itemSku'] !== '' ) {
    $itemSku = $search_params['itemSku'];
    $query_params[] = 'itemSku='.$itemSku;
  } else {
    $itemSku = '';
  }
  if(isset($search_params['locationCode']) && $search_params['locationCode'] !== '' ) {
    $locationCode = $search_params['locationCode'];
    $query_params[] = 'locationCode='.$locationCode;
  } else {
    $locationCode = '';
  }  
  if(is_array($query_params) && count($query_params)>0) {
    $query_params = '?'.implode('&', $query_params);
  }
  $pagination_url = $page_url = '/barcodes/list';
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
  		  <div class="filters-block">
    		  <div id="filters-form">
            <form class="form-validate form-horizontal" method="POST" autocomplete="off">
              <div class="form-group">
                <div class="col-sm-12 col-md-1 col-lg-1"><b>Filter by</b></div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <input placeholder="Barcode" type="text" name="barcode" id="barcode" class="form-control" value="<?php echo $barcode ?>" size="13" />
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <input placeholder="Item name" type="text" name="itemName" id="itemName" class="form-control inameAc" value="<?php echo $itemName ?>" size="10" />
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <input placeholder="Lot No." type="text" name="lotNo" id="lotNo" class="form-control" value="<?php echo $lotNo ?>"  size="13" />
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <input placeholder="PO No." type="text" name="poNo" id="poNo" class="form-control" value="<?php echo $poNo ?>" size="10" />
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
              </div>
              <div class="form-group" style="margin-left:77px;">
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <input placeholder="Container/Case/Box No." type="text" name="cno" id="cno" class="form-control" value="<?php echo $cno ?>" size="13" />
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <input placeholder="Batch No." type="text" name="bno" id="bno" class="form-control" value="<?php echo $bno ?>" size="13" />
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <input placeholder="Item SKU" type="text" name="itemSku" id="itemSku" class="form-control" value="<?php echo $itemSku ?>" size="13" />
                </div>
              </div>
              <div class="form-group text-center">
                <?php include_once __DIR__."/../../../Layout/helpers/filter-buttons.helper.php" ?>
              </div>
            </form>
			    </div>
        </div>
        <div class="table-responsive">
          <?php if(count($barcodes)>0): ?>
           <table class="table table-striped table-hover">
            <thead>
              <tr class="font11">
                <th width="5%" class="text-center valign-middle">Sno</th>
                <th width="5%" class="text-center valign-middle">Barcode</th>
                <?php /*<th width="8%" class="text-center valign-middle">Location Name</th>*/ ?>
                <th width="15%" class="text-center valign-middle">Supplier Name</th>                
                <th width="15%" class="text-center valign-middle">Item Name</th>
                <th width="8%" class="text-center valign-middle">Lot No.</span></th>
                <th width="7%" class="text-center valign-middle">Case/Box<br />No.</th>
                <th width="12%" class="text-center valign-middle">PO No. &amp; Date</span></th>                
                <th width="6%" class="text-center valign-middle">Available<br />Qty.</th>
                <th width="7%" class="text-center valign-middle">MRP<br />(Rs.)</th>
                <th width="5%" class="text-center valign-middle">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
                $cntr = $sl_no;
                $total = 0;
                foreach($barcodes as $barcode_details):
                  $barcode = $barcode_details['barcode'];
                  $item_name = substr($barcode_details['itemName'], 0, 20);
                  $supp_name = substr($barcode_details['supplierName'], 0, 25);
                  $lot_no = $barcode_details['lotNo'];
                  $cno = $barcode_details['cno'];
                  $purchase_code = $barcode_details['purchaseCode'];
                  $available_qty = $barcode_details['availableQty'];
                  $mrp = $barcode_details['mrp'];
                  $location_id = $barcode_details['locationID'];
                  $location_code = isset($location_codes[$location_id]) ? $location_codes[$location_id] : '';
                  $location_name = isset($location_ids[$location_id]) ? $location_ids[$location_id] : '';
                  if($barcode_details['poNo'] !== '' && !is_null($barcode_details['poNo'])) {
                    $po_string = $barcode_details['poNo'].', '.date("d-m-Y", strtotime($barcode_details['purchaseDate']));
                  } else {
                    $po_string = '';
                  }

                  if($purchase_code !== '' && !is_null($purchase_code)) {
                    $print_url = '/barcode/generate/'.$purchase_code;
                  } else {
                    $print_url = '/barcode/opbal';
                  }
              ?>
                <tr class="font11">
                  <td align="right" class="valign-middle"><?php echo $cntr ?></td>
                  <td align="right" class="valign-middle" style="font-weight:bold;font-size:14px;"><?php echo $barcode ?></td>
                  <?php /*<td align="left" class="valign-middle" ><?php echo $location_name ?></td>*/ ?>
                  <td align="left" class="valign-middle" title="<?php echo $barcode_details['supplierName'] ?>"><?php echo $supp_name ?></td>                  
                  <td class="valign-middle" align="left"><?php echo $item_name ?></td>
                  <td align="right" class="valign-middle"><?php echo $lot_no ?></td>
                  <td align="right" class="valign-middle"><?php echo $cno ?></td>
                  <td align="left" class="valign-middle">
                    <a href="/inward-entry/view/<?php echo $purchase_code ?>" title="View Purchase Order" class="hyperlink" target="_blank"><?php echo $po_string ?></a>
                  </td>
                  <td class="valign-middle" align="right"><?php echo $available_qty ?></td>
                  <td class="valign-middle" align="right"><?php echo $mrp ?></td>
                  <td class="valign-middle">
                    <div class="btn-actions-group" align="right">                    
                      <a class="btn btn-success" href="<?php echo $print_url ?>" title="Print Barcodes" target="_blank">
                        <i class="fa fa-barcode"></i>
                      </a>
                    </div>
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