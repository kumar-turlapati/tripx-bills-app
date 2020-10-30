<?php
  use Atawa\Utilities;

  $query_params = [];
  if(isset($search_params['itemName']) && $search_params['itemName'] !=='') {
    $itemName = $search_params['itemName'];
    $query_params[] = 'itemName='.$itemName;
  } else {
    $itemName = '';
  }
  if(isset($search_params['category']) && $search_params['category'] !=='' ) {
    $category = $search_params['category'];
    $query_params[] = 'category='.$category;
  } else {
    $category = '';
  }
  if(isset($search_params['indexBy']) && $search_params['indexBy'] !=='' ) {
    $index_by = $search_params['indexBy'];
    $query_params[] = 'idx='.$index_by;
  } else {
    $index_by = '';
  }  
  if(isset($search_params['locationCode']) && $search_params['locationCode'] !=='' ) {
    $locationCode = $search_params['locationCode'];
    $query_params[] = 'locationCode='.$locationCode;
  } else {
    $locationCode = '';
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
  if(isset($search_params['brandName']) && $search_params['brandName'] !=='' ) {
    $brand_name = $search_params['brandName'];
    $query_params[] = 'brandName='.$brand_name;
  } else {
    $brand_name = '';
  }

  if(count($query_params)>0) {
    $query_params = '?'.implode('&', $query_params);
  } else {
    $query_params = '';
  }
  $pagination_url = $page_url = '/barcode/opbal';

  $sort_by_a = ['item' => 'Index by Item name', 'upload' => 'Index by Entry time'];
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
        <form id="openingBarcodes" method="POST" class="form-validate form-horizontal">
          <div class="filters-block">
            <div id="filters-form">
              <div class="form-group">
                <div class="col-sm-12 col-md-1 col-lg-1">Filter by</div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <input type="text" placeholder="Item name" name="itemName" id="itemName" class="form-control" value="<?php echo $itemName ?>">
                </div>
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
                  <div class="select-wrap">
                    <select class="form-control" name="idx" id="idx">
                      <?php 
                        foreach($sort_by_a as $sort_key=>$sort_value):
                          if($sort_key === $index_by) {
                            $selected = 'selected="selected"';
                          } else {
                            $selected = '';
                          }
                      ?>
                       <option value="<?php echo $sort_key ?>" <?php echo $selected ?>>
                          <?php echo $sort_value ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                   </div>
                </div>
                <div class="col-sm-12 col-md-3 col-lg-3">
                  <input placeholder="Container/Case/Box No." type="text" name="cnoFilter" id="cnoFilter" class="form-control" value="<?php echo $cno_filter ?>" size="13" />
                </div>                
              </div>
              <div class="form-group" style="margin-left:77px;">
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <input placeholder="Batch No." type="text" name="bnoFilter" id="bnoFilter" class="form-control" value="<?php echo $bno_filter ?>" size="13" />
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <input placeholder="Item SKU" type="text" name="itemSku" id="itemSku" class="form-control" value="<?php echo $item_sku ?>" size="13" />
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <input placeholder="Brand Name" type="text" name="brandName" id="brandName" class="form-control brandAc" value="<?php echo $brand_name ?>" size="13" />
                </div>                
                <div class="col-sm-12 col-md-3 col-lg-3">
                  <?php include_once __DIR__."/../../../Layout/helpers/filter-buttons.helper.php" ?>
                </div>
              </div>              
            </div>
          </div>
          <?php if(count($openings)>0): ?>
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
                    <th width="8%" class="text-center valign-middle">Opening rate<br />(in Rs.)</th>
                    <th width="8%" class="text-center valign-middle">Opening val.<br />(in Rs.)</th>
                    <th width="8%" class="text-center valign-middle">Purch. rate<br />(in Rs.)</th>                
                    <th width="5%"  class="text-center valign-middle">Tax<br />(in %)</th>
                    <th width="20%" class="text-center valign-middle">Barcode</th> 
                    <th width="8%"  class="text-center valign-middle">Options</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    $cntr = $sl_no;
                    $tot_opening_value_pur = $tot_opening_value_sale = 0;
                    foreach($openings as $opening_details):
                      $item_name = $opening_details['itemName'];
                      $category_name = $opening_details['categoryName'];
                      $opening_rate = $opening_details['openingRate']; 
                      $opening_qty = $opening_details['openingQty'];
                      $packed_qty = $opening_details['packedQty'];
                      $opening_value = $opening_qty*$opening_rate;
                      $purchase_rate = $opening_details['purchaseRate'];
                      $tax_percent = $opening_details['taxPercent'];
                      $opening_code = $opening_details['openingCode'];
                      $item_code = $opening_details['itemCode'];
                      $lot_no = $opening_details['lotNo'];
                      $barcode = $opening_details['barcode'];
                      $item_key = $item_code.'__'.$lot_no.'__'.$packed_qty;
                      $item_sku = $opening_details['itemSku'];
                      $mfg_name = $opening_details['mfgName'];
                      $cno = $opening_details['cno'];
                      $uom_name = $opening_details['uomName'];
                      $online_price = $opening_details['onlinePrice'];
                      $wholesale_price = $opening_details['wholesalePrice'];
                      $bno = $opening_details['bno'];

                      $tot_opening_value_pur += ($opening_qty * $purchase_rate);
                      $tot_opening_value_sale += ($opening_qty * $opening_rate);
                      if($packed_qty > 0) {
                        $opening_qty_calc = round($opening_qty/$packed_qty, 2);
                      } else {
                        $opening_qty_calc = $opening_qty;
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
                            value="<?php echo $opening_qty_calc ?>"
                          />
                        </td>
                        <td class="valign-middle text-right text-bold font12"><?php echo number_format($opening_rate,2,'.','') ?></td>
                        <td class="text-bold valign-middle text-right font12"><?php echo number_format($opening_value,2,'.','') ?></td>
                        <td class="text-bold valign-middle text-right font12"><?php echo number_format($purchase_rate,2,'.','') ?></td>                    
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
                        <td align="center">
                          <?php if($opening_code !== ''): ?>
                            <div class="btn-actions-group">
                              <a class="btn btn-primary" href="/opbal/update/<?php echo $opening_code ?>" title="Edit Opening Balance">
                                <i class="fa fa-pencil"></i>
                              </a>  
                            </div>
                          <?php endif; ?>
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
                          value="<?php echo $opening_rate ?>" 
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
                  <tr style="height:30px;">
                    <td colspan="7" class="text-right text-bold">PAGE TOTALS - BY PURCHASE RATE</td>
                    <td align="right" style="font-weight:bold;font-size:14px;"><?php echo number_format($tot_opening_value_pur,2,'.','') ?></td>
                    <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>                   
                  </tr>
                  <tr style="height:30px;">
                    <td colspan="7" class="text-right text-bold">PAGE TOTALS - BY SALE RATE</td>
                    <td align="right" style="font-weight:bold;font-size:14px;"><?php echo number_format($tot_opening_value_sale,2,'.','') ?></td>
                    <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>                   
                  </tr>                  
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
            </div>
            <div class="text-center">
              <button class="btn btn-danger" id="op" name="op" value="save" type="submit" formtarget="_blank" title="Barcodes will be generated in new Tab">
                <i class="fa fa-print"></i> Generate & Print Barcodes
              </button>
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