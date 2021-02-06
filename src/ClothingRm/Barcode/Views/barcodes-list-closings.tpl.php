<?php
  use Atawa\Utilities;

  $query_params = [];
  if(isset($search_params['itemName']) && $search_params['itemName'] !=='') {
    $itemName = $search_params['itemName'];
    $query_params[] = 'itemName='.$itemName;
  } else {
    $itemName = '';
  }
  if(isset($search_params['brandName']) && $search_params['brandName'] !=='' ) {
    $brand_name = $search_params['brandName'];
    $query_params[] = 'brandName='.$brand_name;
  } else {
    $brand_name = '';
  }  
  if(isset($search_params['locationCode']) && $search_params['locationCode'] !=='' ) {
    $locationCode = $search_params['locationCode'];
    $query_params[] = 'locationCode='.$locationCode;
  } else {
    $locationCode = '';
  }
  if(isset($search_params['barcode']) && $search_params['barcode'] !=='' ) {
    $barcode = $search_params['barcode'];
    $query_params[] = 'barcode='.$barcode;
  } else {
    $barcode = '';
  }
  if(isset($search_params['lotNo']) && $search_params['lotNo'] !=='' ) {
    $lot_no = $search_params['lotNo'];
    $query_params[] = 'lotNo='.$lot_no;
  } else {
    $lot_no = '';
  }
  if(isset($search_params['cnoFilter']) && $search_params['cnoFilter'] !=='' ) {
    $cno_filter = $search_params['cnoFilter'];
    $query_params[] = 'cnoFilter='.$cno_filter;
  } else {
    $cno_filter = '';
  }
  if(isset($search_params['bnoFilter']) && $search_params['bnoFilter'] !=='' ) {
    $bno_filter = $search_params['bnoFilter'];
    $query_params[] = 'bnoFilter='.$bno_filter;
  } else {
    $bno_filter = '';
  }
  if(isset($search_params['itemSku']) && $search_params['itemSku'] !=='' ) {
    $item_sku = $search_params['itemSku'];
    $query_params[] = 'itemSku='.$item_sku;
  } else {
    $item_sku = '';
  }  
  if(count($query_params)>0) {
    $query_params = '?'.implode('&', $query_params);
  } else {
    $query_params = '';
  }

  $pagination_url = $page_url = '/barcode/cbbal';
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panelBox">
      <div class="panelBody">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/opbal/add" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> New opening balance
            </a>&nbsp;
            <a href="/barcodes/list" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> Barcodes register
            </a>            
          </div>
        </div>
        <form 
          id="openingBarcodes" 
          method="POST" 
          class="form-validate form-horizontal"
          action="<?php echo $page_url ?>"
        >
          <div class="filters-block">
            <div id="filters-form">
              <div class="form-group">
                <div class="col-sm-12 col-md-1 col-lg-1">Filter by</div>
                <div class="col-sm-12 col-md-3 col-lg-3">
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
                  <input type="text" placeholder="Item name" name="itemName" id="itemName" class="form-control inameAc" value="<?php echo $itemName ?>">
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <input placeholder="Brand" type="text" name="brandName" id="brandName" class="form-control brandAc" value="<?php echo $brand_name ?>">
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <input placeholder="Barcode" type="text" name="barcode" id="barcode" class="form-control" value="<?php echo $barcode ?>">
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <input placeholder="Lot No." type="text" name="lotNo" id="lotNo" class="form-control" value="<?php echo $lot_no ?>">
                </div>
              </div>
              <div class="form-group" style="margin-left:77px;">
                <div class="col-sm-12 col-md-3 col-lg-3">
                  <input placeholder="Container/Case/Box No." type="text" name="cnoFilter" id="cnoFilter" class="form-control" value="<?php echo $cno_filter ?>" size="13" />
                </div>                
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <input placeholder="Batch No." type="text" name="bnoFilter" id="bnoFilter" class="form-control" value="<?php echo $bno_filter ?>" size="13" />
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <input placeholder="Item SKU" type="text" name="itemSku" id="itemSku" class="form-control" value="<?php echo $item_sku ?>" size="13" />
                </div>
                <div class="col-sm-12 col-md-3 col-lg-3">
                  <?php include_once __DIR__."/../../../Layout/helpers/filter-buttons.helper.php" ?>
                </div>
              </div>              
            </div>
          </div>
          <?php if(count($closings)>0): ?>
            <div class="table-responsive">
              <table class="table table-striped table-hover font12">
                <thead>
                  <tr>
                    <th width="5%" class="text-center valign-middle">
                      <input 
                        type="checkbox"
                        id="checkAllOpBarcodes"
                        name="checkAllOpBarcodes"
                        style="visibility:visible;text-align:center;margin:0px;position:relative;vertical-align:middle;margin-top:10px;"
                        title="Select all items in this page"
                      />
                    </th>
                    <th width="5%"  class="text-center valign-middle">Sno.</th>
                    <th width="20%" class="text-center valign-middle">Item name</th>
                    <th width="5%" class="text-center valign-middle">Lot no.</th>
                    <th width="15%" class="text-left valign-middle">Category</th> 
                    <th width="8%"  class="text-center valign-middle">Sticker qty.</th>
                    <th width="8%" class="text-center valign-middle">M.R.P<br />(in Rs.)</th>
                    <th width="8%" class="text-center valign-middle">Wholesale price<br />(in Rs.)</th>
                    <th width="8%" class="text-center valign-middle">Online price<br />(in Rs.)</th>                
                    <th width="5%"  class="text-center valign-middle">Tax<br />(in %)</th>
                    <th width="20%" class="text-center valign-middle">Barcode</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    $cntr = $sl_no;
                    foreach($closings as $opening_details):
                      $packed_qty = $opening_details['packedQty'];
                      $item_name = $opening_details['itemName'];
                      $category_name = $opening_details['categoryName'];
                      $mrp = $opening_details['mrp'];
                      $online_price = is_numeric($opening_details['onlinePrice']) ? $opening_details['onlinePrice'] : 0;
                      $wholesale_price = is_numeric($opening_details['wholesalePrice']) ? $opening_details['wholesalePrice'] : 0;
                      $closing_qty = $opening_details['closingQty'];
                      $tax_percent = $opening_details['taxPercent'];
                      $item_code = $opening_details['itemCode'];
                      $lot_no = $opening_details['lotNo'];
                      $item_key = $item_code.'__'.$lot_no.'__'.$packed_qty;
                      $item_sku = $opening_details['itemSku'];
                      $mfg_name = $opening_details['brandName'];
                      $cno = $opening_details['cno'];
                      $uom_name = $opening_details['uomName'];
                      $bno = $opening_details['bno'];
                      if($opening_details['defBarcode'] !== '') {
                        $barcode = $opening_details['defBarcode'];
                      } elseif($opening_details['createdBarcode'] !== '') {
                        $barcode = $opening_details['createdBarcode'];
                      } else {
                        $barcode = '';
                      }
                      if($closing_qty >= $packed_qty) {
                        $sticker_qty = round($closing_qty/$packed_qty, 2);
                      } else {
                        $sticker_qty = $closing_qty;
                      }
                  ?>
                      <tr class="font11">
                        <td align="center">
                          <input
                            type="checkbox"
                            id="requestedItem_<?php echo $item_key ?>"
                            name="requestedItems[]"
                            value="<?php echo $item_key ?>"
                            style="visibility:visible;text-align:center;margin:0px;position:relative;vertical-align:middle;margin-top:10px;"
                            class="requestedItem"
                          />
                        </td>
                        <td class="valign-middle text-right"><?php echo $cntr ?></td>
                        <td class="text-left med-name valign-middle"><?php echo $item_name ?></td>
                        <td class="text-left med-name valign-middle"><?php echo $lot_no ?></td>                      
                        <td class="text-left med-name valign-middle"><?php echo $category_name ?></td>                    
                        <td class="valign-middle text-right">
                          <input
                            type="text"
                            class="form-control stickerQty noEnterKey" 
                            name="stickerQty[<?php echo $item_key ?>]" 
                            style="background-color:#f1f442;border:1px solid #000;font-weight:bold;text-align:right;"
                            id="stickerQty_<?php echo $item_key ?>"
                            value="<?php echo $sticker_qty ?>"
                          />
                        </td>
                        <td class="valign-middle text-right text-bold font12"><?php echo number_format($mrp,2,'.','') ?></td>
                        <td class="text-bold valign-middle text-right font12"><?php echo number_format($wholesale_price,2,'.','') ?></td>
                        <td class="text-bold valign-middle text-right font12"><?php echo number_format($online_price,2,'.','') ?></td>                    
                        <td class="text-right valign-middle text-right"><?php echo number_format($tax_percent,2,'.','') ?></td>
                        <td class="text-center valign-middle text-bold">
                          <input
                            type="text"
                            class="form-control opBarcode noEnterKey" 
                            name="opBarcode[<?php echo $item_key ?>]" 
                            style="font-weight:bold;text-align:left;font-size:12px;"
                            id="opBarcode_<?php echo $item_key ?>"
                            value="<?php echo $barcode ?>"
                            readonly
                          />
                        </td>
                        <input
                          type="hidden" 
                          name="itemNames[<?php echo $item_key ?>]" 
                          id="itemName_<?php echo $item_key ?>" 
                          value="<?php echo $item_name ?>" 
                        />
                        <input
                          type="hidden" 
                          name="itemRates[<?php echo $item_key ?>]" 
                          id="itemRate_<?php echo $item_key ?>" 
                          value="<?php echo $mrp ?>" 
                        />
                        <input
                          type="hidden" 
                          name="cno[<?php echo $item_key ?>]" 
                          id="cno_<?php echo $item_key ?>" 
                          value="<?php echo $cno ?>" 
                        />
                        <input
                          type="hidden" 
                          name="mfgNames[<?php echo $item_key ?>]" 
                          id="mfgNames_<?php echo $item_key ?>" 
                          value="<?php echo $mfg_name ?>" 
                        />
                        <input
                          type="hidden" 
                          name="uomNames[<?php echo $item_key ?>]" 
                          id="uomNames_<?php echo $item_key ?>" 
                          value="<?php echo $uom_name ?>" 
                        />
                        <input
                          type="hidden" 
                          name="onlinePrices[<?php echo $item_key ?>]" 
                          id="onlinePrice_<?php echo $item_key ?>" 
                          value="<?php echo $online_price ?>" 
                        />       
                        <input
                          type="hidden" 
                          name="wholesalePrices[<?php echo $item_key ?>]" 
                          id="wholesalePrice_<?php echo $item_key ?>" 
                          value="<?php echo $wholesale_price ?>" 
                        />
                        <input
                          type="hidden" 
                          name="bnos[<?php echo $item_key ?>]" 
                          id="bno_<?php echo $item_key ?>" 
                          value="<?php echo $bno ?>" 
                        />                                            
                      </tr>
                <?php
                  $cntr++;
                  endforeach; 
                ?>
                </tbody>
              </table>
              <?php include_once __DIR__."/../../../Layout/helpers/pagination.helper.php" ?>          
            </div>
            <div class="form-group">
              <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
                <label class="control-label">Sticker format</label>
                <div class="select-wrap">
                  <select class="form-control" name="indentFormat" id="indentFormat">
                    <?php foreach($sticker_print_type_a as $key => $value): ?>
                      <option value="<?php echo $key ?>"><?php echo $value ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
                <label class="control-label">Rate tobe printed</label>
                <div class="select-wrap">
                  <select class="form-control" name="rateType" id="rateType">
                    <?php foreach($rate_types as $key=>$value): ?>
                      <option value="<?php echo $key ?>"><?php echo $value ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <?php if(isset($form_errors['rateType'])): ?>
                  <span class="error"><?php echo $form_errors['rateType'] ?></span>
                <?php endif; ?>                  
              </div>              
            </div>
            <div class="text-center">
              <button class="btn btn-success" id="op" name="op" value="save" type="submit" formtarget="_blank" title="Barcodes will be printed in new Tab">
                <i class="fa fa-print"></i> Print Barcodes
              </button>
              <button class="btn btn-danger" type="submit" title="Cancel" onclick="javascript: window.location.href='/barcode/cbbal'"><i class="fa fa-times"></i>&nbsp;Cancel</button>
            </div>
          <?php endif; ?>
        </form>
      </div>
    </section>
  </div>
</div>
<?php
/*
        <div class="panel">
          <div class="panel-body">
           <div id="filters-form">
              <form class="form-validate form-horizontal" method="POST">
                <div class="form-group">
                  <div class="col-sm-12 col-md-2 col-lg-1">Filter by</div>
                  <div class="col-sm-12 col-md-2 col-lg-2">
                  </div>
                  <?php /*
                  <div class="col-sm-12 col-md-2 col-lg-2">
                    <input type="text" placeholder="Batch no." name="batchNo" id="batchNo" class="form-control" value="<?php echo $batchNo ?>">
                  </div>
                  <div class="col-sm-12 col-md-2 col-lg-2">
                    <div class="select-wrap">
                      <select class="form-control" name="category" id="category">
                        <?php foreach($categories as $key=>$value): ?>
                           <option value="<?php echo $key ?>"><?php echo $value ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                  <div class="col-sm-12 col-md-2 col-lg-3">
                    <div class="col-sm-12"> 
                      <button class="btn btn-success"><i class="fa fa-file-text"></i> Filter</button>
                      <button type="reset" class="btn btn-warning" onclick="javascript:resetFilter('/opbal/list')"><i class="fa fa-refresh"></i> Reset </button>
                    </div>
                  </div>
                </div>
              </form>        
           </div>
          </div>
        </div>*/ ?>