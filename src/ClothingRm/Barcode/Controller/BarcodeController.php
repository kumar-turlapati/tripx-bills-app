<?php 

namespace ClothingRm\Barcode\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;

use ClothingRm\Barcode\Model\Barcode;
use ClothingRm\Grn\Model\GrnNew;
use ClothingRm\Inward\Model\Inward;
use ClothingRm\Suppliers\Model\Supplier;
use Taxes\Model\Taxes;
use ClothingRm\Openings\Model\Openings;
use ClothingRm\Inventory\Model\Inventory;

class BarcodeController {

  protected $views_path;

  public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->bc_model = new Barcode;
    $this->flash = new Flash;
    $this->inward_model = new Inward;
    $this->supplier_model = new Supplier;
    $this->taxes_model = new Taxes;
    $this->openings_model = new Openings;
    $this->inven_api = new Inventory;
  }

  public function generateBarcodeAction(Request $request) {
    $purchase_details = $suppliers_a = $form_data = $location_ids = [];
    $location_codes = $form_errors = [];
    $taxes = $taxes_raw = [];
    $rate_types = ['mrp' => 'M.R.P', 'wholesale' => 'Wholesale Price', 'online' => 'Online Price'];

    $page_error = $inward_entry_no = $mfg_date = $po_location_code = '';

    if(isset($_SESSION['utype']) && (int)$_SESSION['utype'] !== 3 && 
      (int)$_SESSION['utype'] !== 7 && (int)$_SESSION['utype'] !== 19) {
      $this->flash->set_flash_message("Permission Error: You are not authorized to generate Barcodes against this PO", 1);
      Utilities::redirect('/inward-entry/list');
    }    

    # ---------- get location codes from api -----------------------
    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }

    if(is_null($request->get('purchaseCode'))) {
      $this->flash->set_flash_message('PO Number is mandatory for generating barcode.', 1);
      Utilities::redirect('/barcodes/list');
    } else {

      $suppliers = $this->supplier_model->get_suppliers(0,0,[]);
      if($suppliers['status']) {
        $suppliers_a += $suppliers['suppliers'];
      }

      $taxes_a = $this->taxes_model->list_taxes();
      if($taxes_a['status'] && count($taxes_a['taxes'])>0 ) {
        $taxes_raw = $taxes_a['taxes'];
        foreach($taxes_a['taxes'] as $tax_details) {
          $taxes[$tax_details['taxCode']] = $tax_details['taxPercent'];
        }
      }

      $purchase_code = Utilities::clean_string($request->get('purchaseCode'));
      $purchase_response = $this->inward_model->get_purchase_details($purchase_code);
      // dump($purchase_response, $location_codes, $location_ids);
      // exit;
      if($purchase_response['status'] === false) {
        $this->flash->set_flash_message('Invalid PO Number (or) PO does not exists.', 1);
        Utilities::redirect('/barcodes/list');
      } elseif($purchase_response['purchaseDetails']['grnFlag'] !== 'yes') {
        $this->flash->set_flash_message('Barcodes can be Printed for a PO only when the GRN is done.', 1);
        Utilities::redirect('/barcodes/list');
      } else {
        $purchase_details = $purchase_response['purchaseDetails'];
        $total_item_rows = count($purchase_details['itemDetails']);
        for($i=1;$i<=365;$i++) {
          $credit_days_a[$i] = $i;
        }
        $client_details = Utilities::get_client_details();
        $client_business_state = $client_details['locState'];
        $inward_entry_no =  ' - PO No. { '. $purchase_details['poNo'] .' }';
        $mfg_date = $purchase_details['purchaseDate'];
        $po_location_code = $purchase_details['locationID'];

        // convert received item details to template item details.
        $item_names = array_column($purchase_details['itemDetails'],'itemName');
        $item_codes = array_column($purchase_details['itemDetails'],'itemCode');        
        $inward_qtys = array_column($purchase_details['itemDetails'],'itemQty');
        $free_qtys = array_column($purchase_details['itemDetails'],'freeQty');
        $lot_nos = array_column($purchase_details['itemDetails'],'lotNo');
        $mrps = array_column($purchase_details['itemDetails'],'mrp');
        $item_rates = array_column($purchase_details['itemDetails'],'itemRate');
        $discounts = array_column($purchase_details['itemDetails'],'discountAmount');                
        $tax_percents = array_column($purchase_details['itemDetails'],'taxPercent');
        $hsn_codes = array_column($purchase_details['itemDetails'],'hsnSacCode');
        $barcodes = array_column($purchase_details['itemDetails'],'barcode');
        $packed_qtys = array_column($purchase_details['itemDetails'],'packedQty');
        $uom_names = array_column($purchase_details['itemDetails'],'uomName');
        $wholesale_prices = array_column($purchase_details['itemDetails'],'wholesalePrice');
        $online_prices = array_column($purchase_details['itemDetails'],'onlinePrice');
        $batch_nos = array_column($purchase_details['itemDetails'], 'batchNo');
        $mfg_names = array_column($purchase_details['itemDetails'], 'mfgName');

        $submitted_item_details = $purchase_details['itemDetails'];
        $po_store_name = array_key_exists($purchase_response['purchaseDetails']['locationIDNumeric'], $location_ids) ? $location_ids[$purchase_response['purchaseDetails']['locationIDNumeric']] : 'Invalid Store';

        // unset item details from api data.
        $purchase_item_details = $purchase_details['itemDetails'];
        foreach($purchase_item_details as $key => $purchase_items) {
          $item_names_a[$purchase_items['itemCode'].'__'.$purchase_items['lotNo']] = $purchase_items['itemName'];
          $mrps_a[$purchase_items['itemCode'].'__'.$purchase_items['lotNo']] = $purchase_items['mrp'];
          $packed_qtys_a[$purchase_items['itemCode'].'__'.$purchase_items['lotNo']] = $purchase_items['packedQty'];
          $uom_names_a[$purchase_items['itemCode'].'__'.$purchase_items['lotNo']] = $purchase_items['uomName'];
          $cno_a[$purchase_items['itemCode'].'__'.$purchase_items['lotNo']] =  $purchase_items['cno'];
          $mfg_names_a[$purchase_items['itemCode'].'__'.$purchase_items['lotNo']] = $purchase_items['mfgName'];
          $online_prices_a[$purchase_items['itemCode'].'__'.$purchase_items['lotNo']] = $purchase_items['onlinePrice'];
          $wholesale_prices_a[$purchase_items['itemCode'].'__'.$purchase_items['lotNo']] = $purchase_items['wholesalePrice'];
          $batch_no_a[$purchase_items['itemCode'].'__'.$purchase_items['lotNo']] = $purchase_items['batchNo'];
          $lot_no_a[$purchase_items['itemCode'].'__'.$purchase_items['lotNo']] = $purchase_items['lotNo'];
          $mfg_name_a[$purchase_items['itemCode'].'__'.$purchase_items['lotNo']] = $purchase_items['mfgName'];
        }
        unset($purchase_details['itemDetails']);

        // create form data variable.
        $form_data = $purchase_details;
        $form_data['itemName'] = $item_names;
        $form_data['inwardQty'] = $inward_qtys;
        $form_data['freeQty'] = $free_qtys;
        $form_data['lotNo'] = $lot_nos;
        $form_data['itemRate'] = $item_rates;
        $form_data['taxPercent'] = $tax_percents;
        $form_data['mrp'] = $mrps;
        $form_data['itemDiscount'] = $discounts;  
        $form_data['hsnCodes'] = $hsn_codes;
        $form_data['itemCode'] = $item_codes;
        $form_data['barcode'] = $barcodes;
        $form_data['packedQty'] = $packed_qtys;
        $form_data['uomNames'] = $uom_names;
        $form_data['wholesalePrices'] = $wholesale_prices;
        $form_data['onlinePrices'] = $online_prices;
      }
    }

    if(count($request->request->all()) > 0) {
      $form_data = $request->request->all();

      // validate form data using checkboxes.
      if(isset($form_data['requestedItems']) && count($form_data['requestedItems']) > 0) {
        foreach($form_data['stickerQty'] as $key => $value) {
          if(in_array($key, $form_data['requestedItems']) === false) {
            $form_data['stickerQty'][$key] = 0;
          }
        }
      } else {
        $this->flash->set_flash_message('Please choose an item name to generate / print Barcode.',1);
        Utilities::redirect('/barcode/generate/'.$purchase_code);         
      }

      // process form data on submit
      $form_processing = $this->_process_submitted_data($form_data, $submitted_item_details);
      if($form_processing['status'] === false) {
        $form_errors = json_encode($form_processing['form_errors']);
        $this->flash->set_flash_message($form_errors, 1);
        Utilities::redirect('/barcode/generate/'.$purchase_code);        
      } else {
        $new_barcodes = $form_processing['new_barcodes'];
        $print_barcodes = $form_processing['print_barcodes'];
        $cleaned_params = $form_processing['cleaned_params'];
      }

      // dump($new_barcodes, $print_barcodes, $cleaned_params);
      // exit;

      if(is_array($new_barcodes['items']) && count($new_barcodes['items']) > 0) {
        # hit api and create barcodes.
        $api_response = $this->bc_model->generate_barcode($purchase_code, $new_barcodes, $po_location_code);
        if($api_response['status'] && count($api_response['barcodes'])>0) {
          $print_array = [];
          foreach($cleaned_params as $key => $print_qty) {
            if($print_qty > 0) {
              $print_array[$api_response['barcodes'][$key]] = [$print_qty, $item_names_a[$key], $mrps_a[$key], $mfg_date, $packed_qtys_a[$key], $cno_a[$key], $mfg_names_a[$key], $uom_names_a[$key], $wholesale_prices_a[$key], $online_prices_a[$key], $batch_no_a[$key], $lot_no_a[$key], $mfg_name_a[$key]];
            }
          }
        } else {
          $message = $api_response['apierror'];
          $this->flash->set_flash_message($message,1);
          Utilities::redirect('/barcode/generate/'.$purchase_code);
        }
      } else {
        $print_array = [];
        $index_key = 0;
        foreach($cleaned_params as $key => $print_qty) {
          if($print_qty > 0) {
            $print_array[$print_barcodes[$index_key]] = [$print_qty, $item_names_a[$key], $mrps_a[$key], $mfg_date, $packed_qtys_a[$key], $cno_a[$key], $mfg_names_a[$key], $uom_names_a[$key], $wholesale_prices_a[$key], $online_prices_a[$key], $batch_no_a[$key], $lot_no_a[$key], $mfg_name_a[$key]];
            $index_key++;
          }
        }
      }

      // dump($print_array);
      // exit;

      $format = $form_processing['format'];
      $rate_type = $form_processing['r'];

      if(count($print_array)>0) {
        if(isset($_SESSION['printBarCodes'])) {
          unset($_SESSION['printBarCodes']);
        }
        $_SESSION['printBarCodes'] = $print_array;
        Utilities::redirect('/barcodes/print?format='.$format.'&r='.$rate_type);
      }
    }

    // prepare form variables.
    $template_vars = array(
      'utilities' => new Utilities,
      'credit_days_a' => array(0=>'Choose')+$credit_days_a,
      'suppliers' => array(''=>'Choose')+$suppliers_a,
      'payment_methods' => Constants::$PAYMENT_METHODS_PURCHASE,
      'taxes' => $taxes,
      'taxes_raw' => $taxes_raw,
      'form_errors' => $form_errors,
      'form_data' => $form_data,
      'total_item_rows' => $total_item_rows,
      'page_error' => $page_error,
      'states_a' => array(0=>'Choose') + Constants::$LOCATION_STATES,
      'supply_type_a' => array('' => 'Choose', 'inter' => 'Interstate', 'intra' => 'Intrastate'),
      'client_business_state' => $client_business_state,
      'sticker_print_type_a' => ['' => 'Choose'] + Utilities::get_barcode_sticker_print_formats(),
      'rate_types' => $rate_types,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Generate Barcodes'.$inward_entry_no.' @ '.$po_store_name,
      'icon_name' => 'fa fa-barcode',      
    );

    // render template
    return array($this->template->render_view('barcode-generate', $template_vars), $controller_vars);    
  }

  public function printBarcodesAction(Request $request) {
    if(!isset($_SESSION['printBarCodes'])) {
      $this->flash->set_flash_message('Invalid purchase code.', 1);      
      Utilities::redirect('/barcodes/list');
    }

    $print_format = !is_null($request->get('format')) && $request->get('format') !== '' ? Utilities::clean_string($request->get('format')) : 'indent';
    $rate_type = !is_null($request->get('r')) && $request->get('r') !== '' ? Utilities::clean_string($request->get('r')) : 'mrp';
    switch ($print_format) {
      case 'indent':
        $print_tpl = 'barcode-print-stickers-html';
        break;
      case 'indent2':
        $print_tpl = 'barcode-print-stickers-item-html';
        break;
      case 'mrp':
        $print_tpl = 'barcode-print-stickers-mrp-html';
        break;
      case 'worate':
        $print_tpl = 'barcode-print-stickers-wor-html';
        break;
      case 'worate-sim':
        $print_tpl = 'barcode-print-stickers-wor-item-html';
        break;        
      case 'sku-small':
        $print_tpl = 'barcode-print-stickers-sku-small-html';
        break;        
      case 'wh-large':
        $print_tpl = 'barcode-print-stickers-wh-large-html';
        break;
      case 'indent2-bprinter':
        $print_tpl = 'barcode-print-stickers-item-bprinter-html';
        break;
      case 'sku-small-bprinter':
        $print_tpl = 'barcode-print-stickers-sku-small-bprinter-html';
        break;
      case 'wh-large-bprinter':
        $print_tpl = 'barcode-print-stickers-wh-large-bprinter-html';
        break;        
    }

    // build variables
    $controller_vars = array(
      'page_title' => 'Print Barcodes',
      'icon_name' => 'fa fa-print',
    );

    // render template
    return array($this->template->render_view($print_tpl, ['rate_type' => $rate_type]), $controller_vars);     
  }

  public function barcodesListAction(Request $request) {
    $barcodes = $search_params = $barcodes_a = [];
    $page_error = '';
    
    $total_pages = $total_records = $record_count = $page_no = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;

    # ---------- get location codes from api -----------------------
    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }

    // parse request parameters.
    $per_page = 100;
    $page_no = $request->get('pageNo') !== null ? Utilities::clean_string($request->get('pageNo')):1;
    $barcode = $request->get('barcode') !== null ? Utilities::clean_string($request->get('barcode')):'';
    $po_no = $request->get('poNo') !== null ? Utilities::clean_string($request->get('poNo')):'';
    $lot_no = $request->get('lotNo') !== null ? Utilities::clean_string($request->get('lotNo')) : '';
    $item_name = $request->get('itemName') !== null ? Utilities::clean_string($request->get('itemName')) : '';
    $location_code = $request->get('locationCode') !== null ? Utilities::clean_string($request->get('locationCode')) : $_SESSION['lc'];
    $cno = $request->get('cno') !== null ? Utilities::clean_string($request->get('cno')) : '';
    $bno = $request->get('bno') !== null ? Utilities::clean_string($request->get('bno')) : '';
    $itemSku = $request->get('itemSku')!== null ? Utilities::clean_string($request->get('itemSku')) : '';

    $search_params = array(
      'barcode' => $barcode,
      'poNo' => $po_no,
      'itemName' => $item_name,
      'lotNo' => $lot_no,
      'locationCode' => $location_code,
      'pageNo' => $page_no,
      'perPage' => $per_page,
      'cno' => $cno,
      'bno' => $bno,
      'itemSku' => $itemSku,
    );

    if( count($request->request->all()) > 0) {
      $search_params['pageNo'] = 1;
      $page_no = 1;
    }

    // dump($search_params);
    // exit;

    $api_response = $this->bc_model->get_barcodes($search_params);
    if($api_response['status']) {
      if(count($api_response['response']['barcodes'])>0) {
        $slno = Utilities::get_slno_start(count($api_response['response']['barcodes']),$per_page,$page_no);
        $to_sl_no = $slno+$per_page;
        $slno++;
        if($page_no<=3) {
          $page_links_to_start = 1;
          $page_links_to_end = 10;
        } else {
          $page_links_to_start = $page_no-3;
          $page_links_to_end = $page_links_to_start+10;        
        }
        if($api_response['response']['total_pages']<$page_links_to_end) {
          $page_links_to_end = $api_response['response']['total_pages'];
        }
        if($api_response['response']['this_page'] < $per_page) {
          $to_sl_no = ($slno+$api_response['response']['this_page'])-1;
        }
        $barcodes_a = $api_response['response']['barcodes'];
        $total_pages = $api_response['response']['total_pages'];
        $total_records = $api_response['response']['total_records'];
        $record_count = $api_response['response']['this_page'];
      } else {
        $page_error = $api_response['apierror'];
      }
    } else {
      $page_error = $api_response['apierror'];
    }

     // prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'barcodes' => $barcodes_a,
      'total_pages' => $total_pages ,
      'total_records' => $total_records,
      'record_count' => $record_count,
      'sl_no' => $slno,
      'to_sl_no' => $to_sl_no,
      'page_links_to_start' => $page_links_to_start,
      'page_links_to_end' => $page_links_to_end,
      'current_page' => $page_no,
      'search_params' => $search_params,
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,
      'client_locations' => array(''=>'All Locations') + $client_locations,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Barcodes List',
      'icon_name' => 'fa fa-barcode',
    );

    // render template
    return array($this->template->render_view('barcodes-list', $template_vars), $controller_vars);    
  }

  public function generateBarcodesOpbalAction(Request $request) {
    $search_params = $openings_a = $print_array = [];
    $print_array_final = [];

    $total_pages = $total_records = $record_count = $page_no = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;
    $page_success = $page_error = '';
    $categories_a = [''=>'Choose'];

    $client_locations = Utilities::get_client_locations(true);

    $page_no = $request->get('pageNo') !== null ? Utilities::clean_string($request->get('pageNo')):1;
    $per_page = 50;
    if( count($request->request->all()) > 0) {
      $form_data = $request->request->all();
      // dump($form_data);
      // exit;
      // check whether the form is submitted or filtered.
      if(isset($form_data['op']) && $form_data['op'] === 'save') {
        $location_code = $form_data['locationCode'];
        $item_names = $form_data['itemNames'];
        $cnos_list = $form_data['cno'];
        $item_mfgs = $form_data['mfgNames'];
        $item_rates = $form_data['itemRates'];
        $barcodes_a = $form_data['opBarcode'];
        $sticker_qtys = $form_data['stickerQty'];
        $indent_format = $form_data['indentFormat'];
        $mfg_names = $form_data['mfgNames'];
        $uom_names = $form_data['uomNames'];
        $wholesale_prices = $form_data['wholesalePrices'];
        $online_prices = $form_data['onlinePrices'];
        $batch_nos = $form_data['bnos'];

        $form_validation = $this->_validate_op_barcode_form($form_data);
        if($form_validation['status']) {
          $new_barcodes = $form_validation['cleaned_params']['new_barcodes'];
          $existing_barcodes = $form_validation['cleaned_params']['print_barcodes'];
          if(count($new_barcodes)>0) {
            $api_response = $this->bc_model->generate_barcode_opening($new_barcodes, $location_code);
            if($api_response['status'] && count($api_response['barcodes'])>0) {
              foreach($api_response['barcodes'] as $item_key => $new_barcode) {
                if(isset($sticker_qtys[$item_key])) {
                  if(isset($item_names[$item_key])) {
                    $item_name = $item_names[$item_key];
                  } else {
                    $item_name = 'Invalid';
                  }
                  if(isset($cnos_list[$item_key])) {
                    $cno = $cnos_list[$item_key];
                  } else {
                    $cno = '';
                  }
                  if(isset($mfg_names[$item_key])) {
                    $mfg_name = $mfg_names[$item_key];
                  } else {
                    $mfg_name = '';
                  }                  
                  if(isset($item_rates[$item_key])) {
                    $item_rate = $item_rates[$item_key];
                  } else {
                    $item_rate = 'Invalid';
                  }
                  if(isset($sticker_qtys[$item_key])) {
                    $print_qty = $sticker_qtys[$item_key];
                  } else {
                    $print_qty = 0;
                  }
                  if(isset($uom_names[$item_key])) {
                    $uom_name = $uom_names[$item_key];
                  } else {
                    $uom_name = '';
                  } 
                  if(isset($wholesale_prices[$item_key])) {
                    $wholesale_price = $wholesale_prices[$item_key];
                  } else {
                    $wholesale_price = '';
                  }
                  if(isset($online_prices[$item_key])) {
                    $online_price = $online_prices[$item_key];
                  } else {
                    $online_price = '';
                  }                  
                  if(isset($batch_nos[$item_key])) {
                    $batch_no = $batch_nos[$item_key];
                  } else {
                    $batch_no = '';
                  }
                  $item_key_a = explode("__", $item_key);
                  $print_array_final[$new_barcode] = [$print_qty, $item_name, $item_rate, date("Y-m-d"), $item_key_a[2], $cno, $mfg_name, $uom_name, $wholesale_price, $online_price, $batch_no, '', $mfg_name]; 
                }
              }
            } else {
              $message = $result['apierror'];
              $this->flash->set_flash_message($message,1);
              Utilities::redirect('/barcode/opbal');          
            }
          }
          if(count($existing_barcodes)>0) {
            $existing_barcodes_a = array_combine(array_column($existing_barcodes, 0), array_column($existing_barcodes, 1));
            $print_array += $existing_barcodes_a;
            foreach($print_array as $item_key => $print_qty) {
              if(isset($item_names[$item_key])) {
                $item_name = $item_names[$item_key];
              } else {
                $item_name = 'Invalid';
              }
              if(isset($item_rates[$item_key])) {
                $item_rate = $item_rates[$item_key];
              } else {
                $item_rate = 'Invalid';
              }
              if(isset($barcodes_a[$item_key])) {
                $barcode = $barcodes_a[$item_key];
              } else {
                $barcode = 'Invalid';
              }
              if(isset($cnos_list[$item_key])) {
                $cno = $cnos_list[$item_key];
              } else {
                $cno = '';
              }
              if(isset($mfg_names[$item_key])) {
                $mfg_name = $mfg_names[$item_key];
              } else {
                $mfg_name = '';
              }
              if(isset($uom_names[$item_key])) {
                $uom_name = $uom_names[$item_key];
              } else {
                $uom_name = '';
              }
              if(isset($wholesale_prices[$item_key])) {
                $wholesale_price = $wholesale_prices[$item_key];
              } else {
                $wholesale_price = '';
              }
              if(isset($online_prices[$item_key])) {
                $online_price = $online_prices[$item_key];
              } else {
                $online_price = '';
              }                  
              if(isset($batch_nos[$item_key])) {
                $batch_no = $batch_nos[$item_key];
              } else {
                $batch_no = '';
              }              
              $item_key_a = explode("__", $item_key);
              $print_array_final[$barcode] = [$print_qty, $item_name, $item_rate, date("Y-m-d"), $item_key_a[2], $cno, $mfg_name, $uom_name, $wholesale_price, $online_price, $batch_no, '', $mfg_name];
            }
          }
          if(count($print_array_final)>0) {
            if(isset($_SESSION['printBarCodes'])) {
              unset($_SESSION['printBarCodes']);
            }
            $_SESSION['printBarCodes'] = $print_array_final;
            Utilities::redirect('/barcodes/print?format='.$indent_format);
          }

        } else {
          $this->flash->set_flash_message(json_encode($form_validation['errors']),1);
        }

      } else {
        $item_name = isset($form_data['itemName']) ? Utilities::clean_string($form_data['itemName']) : '';
        $index_by =  isset($form_data['idx']) ? Utilities::clean_string($form_data['idx']) : 'item';        
        $location_code = isset($form_data['locationCode']) ? Utilities::clean_string($form_data['locationCode']) : $_SESSION['lc'];
        $cno_filter = !is_null($request->get('cnoFilter')) ? Utilities::clean_string($request->get('cnoFilter')) : '';
        $bno_filter = !is_null($request->get('bnoFilter')) ? Utilities::clean_string($request->get('bnoFilter')) : '';
        $item_sku = !is_null($request->get('itemSku')) ? Utilities::clean_string($request->get('itemSku')) : '';
        $brand_name = !is_null($request->get('brandName')) ? Utilities::clean_string($request->get('brandName')) : '';
        $search_params = array(
          'itemName' => $item_name,
          'locationCode' => $location_code,
          'pageNo' => $page_no,
          'perPage' => $per_page,
          'indexBy' => $index_by,
          'cnoFilter' => $cno_filter,
          'bnoFilter' => $bno_filter,
          'itemSku' => $item_sku,
          'brandName' => $brand_name,
        );
      }
    } else {
      if(!is_null($request->get('locationCode')) && $request->get('locationCode') !== '') {
        $location_code = $request->get('locationCode');
      } else {
        $location_code = isset($_SESSION['lc']) ? $_SESSION['lc'] : '';
      }
      if(!is_null($request->get('idx')) && $request->get('idx') !== '') {
        $index_by = $request->get('idx');
      } else {
        $index_by = 'item';
      }
      $cno_filter = !is_null($request->get('cnoFilter')) ? Utilities::clean_string($request->get('cnoFilter')) : '';
      $bno_filter = !is_null($request->get('bnoFilter')) ? Utilities::clean_string($request->get('bnoFilter')) : '';
      $item_sku = !is_null($request->get('itemSku')) ? Utilities::clean_string($request->get('itemSku')) : '';
      $brand_name = !is_null($request->get('brandName')) ? Utilities::clean_string($request->get('brandName')) : '';
      $search_params = array(
        'pageNo' => $page_no,
        'perPage' => $per_page,
        'locationCode' => $location_code,
        'indexBy' => $index_by,
        'cnoFilter' => $cno_filter,
        'bnoFilter' => $bno_filter,
        'itemSku' => $item_sku,
        'brandName' => $brand_name,
      );
    }

    // dump

    $openings = $this->openings_model->opbal_list($search_params);
    // dump($openings);
    // exit;
    // check api status
    if($openings['status']) {
      // check whether we got products or not.
      if(count($openings['openings'])>0) {
        $slno = Utilities::get_slno_start(count($openings['openings']), $per_page, $page_no);
        $to_sl_no = $slno+$per_page;
        $slno++;
        if($page_no<=3) {
          $page_links_to_start = 1;
          $page_links_to_end = 10;
        } else {
          $page_links_to_start = $page_no-3;
          $page_links_to_end = $page_links_to_start+10;
        }
        if($openings['total_pages']<$page_links_to_end) {
          $page_links_to_end = $openings['total_pages'];
        }
        if($openings['record_count'] < $per_page) {
          $to_sl_no = ($slno+$openings['record_count'])-1;
        }
        $openings_a = $openings['openings'];
        $total_pages = $openings['total_pages'];
        $total_records = $openings['total_records'];
        $record_count = $openings['record_count'];
      } else {
        $page_error = $openings['apierror'];
      }
    } else {
      $this->flash->set_flash_message($openings['apierror'],1);      
    }

   // prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'page_success' => $page_success,
      'openings' => $openings_a,
      'total_pages' => $total_pages ,
      'total_records' => $total_records,
      'record_count' =>  $record_count,
      'sl_no' => $slno,
      'to_sl_no' => $to_sl_no,
      'search_params' => $search_params,            
      'page_links_to_start' => $page_links_to_start,
      'page_links_to_end' => $page_links_to_end,
      'current_page' => $page_no,
      'categories' => $categories_a,
      'client_locations' => $client_locations,
      'location_code' => $location_code,
      'sticker_print_type_a' => ['' => 'Choose'] + Utilities::get_barcode_sticker_print_formats(),      
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Generate Barcodes for Opening Balances',
      'icon_name' => 'fa fa-barcode',             
    );

    // render template
    return array($this->template->render_view('barcodes-list-openings', $template_vars), $controller_vars);
  }

  public function generateBarcodesClosingbalAction(Request $request) {
    $search_params = $closing_balances = $print_array = [];
    $print_array_final = $closings_a = [];

    $total_pages = $total_records = $record_count = $page_no = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;
    $page_success = $page_error = '';
    $categories_a = [''=>'Choose'];
    $rate_types = ['mrp' => 'M.R.P', 'wholesale' => 'Wholesale Price', 'online' => 'Online Price'];

    $client_locations = Utilities::get_client_locations(true);

    $page_no = $request->get('pageNo') !== null ? Utilities::clean_string($request->get('pageNo')):1;
    $per_page = 50;
    $from_date = $to_date = date("d-m-Y");
    $group_by = 'lot';
    $balance_type = 'cbg0';
    $inven_type = 'product';

    if( count($request->request->all()) > 0) {
      $form_data = $request->request->all();
      // check whether the form is submitted or filtered.
      if(isset($form_data['op']) && $form_data['op'] === 'save') {
        $location_code = $form_data['locationCode'];
        $item_names = $form_data['itemNames'];
        $cnos_list = $form_data['cno'];
        $item_mfgs = $form_data['mfgNames'];
        $item_rates = $form_data['itemRates'];
        $barcodes_a = $form_data['opBarcode'];
        $sticker_qtys = $form_data['stickerQty'];
        $indent_format = $form_data['indentFormat'];
        $mfg_names = $form_data['mfgNames'];
        $uom_names = $form_data['uomNames'];
        $wholesale_prices = $form_data['wholesalePrices'];
        $online_prices = $form_data['onlinePrices'];
        $batch_nos = $form_data['bnos'];

        $form_validation = $this->_validate_op_barcode_form($form_data);
        if($form_validation['status']) {
          $existing_barcodes = $form_validation['cleaned_params']['print_barcodes'];
          if(count($existing_barcodes)>0) {
            $existing_barcodes_a = array_combine(array_column($existing_barcodes, 0), array_column($existing_barcodes, 1));
            $print_array += $existing_barcodes_a;
            foreach($print_array as $item_key => $print_qty) {
              if(isset($item_names[$item_key])) {
                $item_name = $item_names[$item_key];
              } else {
                $item_name = 'Invalid';
              }
              if(isset($item_rates[$item_key])) {
                $item_rate = $item_rates[$item_key];
              } else {
                $item_rate = 'Invalid';
              }
              if(isset($barcodes_a[$item_key])) {
                $barcode = $barcodes_a[$item_key];
              } else {
                $barcode = 'Invalid';
              }
              if(isset($cnos_list[$item_key])) {
                $cno = $cnos_list[$item_key];
              } else {
                $cno = '';
              }
              if(isset($mfg_names[$item_key])) {
                $mfg_name = $mfg_names[$item_key];
              } else {
                $mfg_name = '';
              }
              if(isset($uom_names[$item_key])) {
                $uom_name = $uom_names[$item_key];
              } else {
                $uom_name = '';
              }
              if(isset($wholesale_prices[$item_key])) {
                $wholesale_price = $wholesale_prices[$item_key];
              } else {
                $wholesale_price = '';
              }
              if(isset($online_prices[$item_key])) {
                $online_price = $online_prices[$item_key];
              } else {
                $online_price = '';
              }                  
              if(isset($batch_nos[$item_key])) {
                $batch_no = $batch_nos[$item_key];
              } else {
                $batch_no = '';
              }              
              $item_key_a = explode("__", $item_key);
              $print_array_final[$barcode] = [$print_qty, $item_name, $item_rate, date("Y-m-d"), $item_key_a[2], $cno, $mfg_name, $uom_name, $wholesale_price, $online_price, $batch_no, '', $mfg_name];
            }

          } else {
            $this->flash->set_flash_message('<i class="fa fa-window-close" aria-hidden="true"></i>&nbsp;The selected items have no Barcodes. Please generate barcodes from Purchase register or Opening balances.',1);
            Utilities::redirect('/barcode/cbbal?locationCode='.$form_data['locationCode']);
          }
          if(count($print_array_final)>0) {
            if(isset($_SESSION['printBarCodes'])) {
              unset($_SESSION['printBarCodes']);
            }
            $_SESSION['printBarCodes'] = $print_array_final;
            $rate_type = isset($form_data['rateType']) && $form_data['rateType'] !== '' ? Utilities::clean_string($form_data['rateType']) : ''; 
            $redirect_url = $rate_type !== '' ? '/barcodes/print?format='.$indent_format.'&r='.$rate_type : '/barcodes/print?format='.$indent_format;
            Utilities::redirect($redirect_url);
          }
        } else {
          $this->flash->set_flash_message(json_encode($form_validation['errors']),1);
          Utilities::redirect('/barcode/cbbal?locationCode='.$form_data['locationCode']);
        }
      } else {
        $location_code = isset($form_data['locationCode']) ? Utilities::clean_string($form_data['locationCode']) : $_SESSION['lc'];
        $item_name = isset($form_data['itemName']) ? Utilities::clean_string($form_data['itemName']) : '';
        $brand_name = isset($form_data['brandName']) ? Utilities::clean_string($form_data['brandName']) : '';
        $barcode = isset($form_data['barcode']) ? Utilities::clean_string($form_data['barcode']) : '';
        $lot_no = isset($form_data['lotNo']) ? Utilities::clean_string($form_data['lotNo']) : '';
        $cno_filter = !is_null($request->get('cnoFilter')) ? Utilities::clean_string($request->get('cnoFilter')) : '';
        $bno_filter = !is_null($request->get('bnoFilter')) ? Utilities::clean_string($request->get('bnoFilter')) : '';
        $item_sku = !is_null($request->get('itemSku')) ? Utilities::clean_string($request->get('itemSku')) : '';

        $search_params = array(
          'itemName' => $item_name,
          'locationCode' => $location_code,
          'pageNo' => $page_no,
          'perPage' => $per_page,
          'fromDate' => $from_date,
          'toDate' => $to_date,
          'groupBy' => $group_by,
          'balanceType' => $balance_type,
          'invenType' => $inven_type,
          'brandName' => $brand_name,
          'barcode' => $barcode,
          'lotNo' => $lot_no,
          'cnoFilter' => $cno_filter,
          'bnoFilter' => $bno_filter,
          'itemSku' => $item_sku,
        );
      }
    } else {
      if(!is_null($request->get('locationCode')) && $request->get('locationCode') !== '') {
        $location_code = $request->get('locationCode');
      } else {
        $location_code = isset($_SESSION['lc']) ? $_SESSION['lc'] : '';
      }
      $item_name = !is_null($request->get('itemName')) ? Utilities::clean_string($request->get('itemName')) : '';
      $brand_name = !is_null($request->get('brandName')) ? Utilities::clean_string($request->get('brandName')) : '';
      $barcode = !is_null($request->get('barcode')) ? Utilities::clean_string($request->get('barcode')) : '';
      $lot_no = !is_null($request->get('lotNo')) ? Utilities::clean_string($request->get('lotNo')) : '';
      $cno_filter = !is_null($request->get('cnoFilter')) ? Utilities::clean_string($request->get('cnoFilter')) : '';
      $bno_filter = !is_null($request->get('bnoFilter')) ? Utilities::clean_string($request->get('bnoFilter')) : '';
      $item_sku = !is_null($request->get('itemSku')) ? Utilities::clean_string($request->get('itemSku')) : '';

      $search_params = array(
        'pageNo' => $page_no,
        'perPage' => $per_page,
        'locationCode' => $location_code,
        'fromDate' => $from_date,
        'toDate' => $to_date,
        'groupBy' => $group_by,
        'balanceType' => $balance_type,
        'invenType' => $inven_type,
        'itemName' => $item_name,
        'brandName' => $brand_name,
        'barcode' => $barcode,
        'lotNo' => $lot_no,
        'cnoFilter' => $cno_filter,
        'bnoFilter' => $bno_filter,
        'itemSku' => $item_sku,
      );
      // dump($search_params, 'search params.....');
    }

    $closing_balances = $this->inven_api->get_stock_report($search_params);
    // dump($closing_balances);
    // exit;
    // check api status
    if($closing_balances['status']) {
      // check whether we got products or not.
      if(count($closing_balances['results']['results'])>0) {
        $slno = Utilities::get_slno_start(count($closing_balances['results']['results']), $per_page, $page_no);
        $to_sl_no = $slno+$per_page;
        $slno++;
        if($page_no<=3) {
          $page_links_to_start = 1;
          $page_links_to_end = 10;
        } else {
          $page_links_to_start = $page_no-3;
          $page_links_to_end = $page_links_to_start+10;
        }
        if($closing_balances['results']['total_pages']<$page_links_to_end) {
          $page_links_to_end = $closing_balances['results']['total_pages'];
        }
        if($closing_balances['results']['total_records'] < $per_page) {
          $to_sl_no = ($slno+$closing_balances['results']['total_records'])-1;
        }
        $closings_a = $closing_balances['results']['results'];
        $total_pages = $closing_balances['results']['total_pages'];
        $total_records = $closing_balances['results']['total_records'];
        $record_count = $closing_balances['results']['total_records'];
      } else {
        $page_error = $closing_balances['apierror'];
      }
    } else {
      $this->flash->set_flash_message($closing_balances['apierror'],1);      
    }

   // prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'page_success' => $page_success,
      'closings' => $closings_a,
      'total_pages' => $total_pages ,
      'total_records' => $total_records,
      'record_count' =>  $record_count,
      'sl_no' => $slno,
      'to_sl_no' => $to_sl_no,
      'search_params' => $search_params,            
      'page_links_to_start' => $page_links_to_start,
      'page_links_to_end' => $page_links_to_end,
      'current_page' => $page_no,
      'categories' => $categories_a,
      'client_locations' => $client_locations,
      'location_code' => $location_code,
      'sticker_print_type_a' => ['' => 'Choose'] + Utilities::get_barcode_sticker_print_formats(),
      'rate_types' => $rate_types,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Print Barcodes for Closing Balances',
      'icon_name' => 'fa fa-barcode',             
    );

    // render template
    return array($this->template->render_view('barcodes-list-closings', $template_vars), $controller_vars);
  }

  public function generateBarcodesClosingbalItemwiseAction(Request $request) {
    $search_params = $closing_balances = $print_array = [];
    $print_array_final = $closings_a = [];

    $total_pages = $total_records = $record_count = $page_no = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;
    $page_success = $page_error = '';
    $categories_a = [''=>'Choose'];
    $rate_types = ['mrp' => 'M.R.P', 'wholesale' => 'Wholesale Price', 'online' => 'Online Price'];

    $client_locations = Utilities::get_client_locations(true);

    $page_no = $request->get('pageNo') !== null ? Utilities::clean_string($request->get('pageNo')):1;
    $per_page = 50;
    $from_date = $to_date = date("d-m-Y");
    $group_by = 'item';
    $balance_type = 'cbg0';
    $inven_type = 'product';

    if( count($request->request->all()) > 0) {
      $form_data = $request->request->all();
      // check whether the form is submitted or filtered.
      if(isset($form_data['op']) && $form_data['op'] === 'save') {
        $location_code = $form_data['locationCode'];
        $item_names = $form_data['itemNames'];
        $cnos_list = $form_data['cno'];
        $item_mfgs = $form_data['mfgNames'];
        $item_rates = $form_data['itemRates'];
        $barcodes_a = $form_data['opBarcode'];
        $sticker_qtys = $form_data['stickerQty'];
        $indent_format = $form_data['indentFormat'];
        $mfg_names = $form_data['mfgNames'];
        $uom_names = $form_data['uomNames'];
        $wholesale_prices = $form_data['wholesalePrices'];
        $online_prices = $form_data['onlinePrices'];
        $batch_nos = $form_data['bnos'];

        $form_validation = $this->_validate_op_barcode_form($form_data);
        if($form_validation['status']) {
          $existing_barcodes = $form_validation['cleaned_params']['print_barcodes'];
          if(count($existing_barcodes)>0) {
            $existing_barcodes_a = array_combine(array_column($existing_barcodes, 0), array_column($existing_barcodes, 1));
            $print_array += $existing_barcodes_a;
            foreach($print_array as $item_key => $print_qty) {
              if(isset($item_names[$item_key])) {
                $item_name = $item_names[$item_key];
              } else {
                $item_name = 'Invalid';
              }
              if(isset($item_rates[$item_key])) {
                $item_rate = $item_rates[$item_key];
              } else {
                $item_rate = 'Invalid';
              }
              if(isset($barcodes_a[$item_key])) {
                $barcode = $barcodes_a[$item_key];
              } else {
                $barcode = 'Invalid';
              }
              if(isset($cnos_list[$item_key])) {
                $cno = $cnos_list[$item_key];
              } else {
                $cno = '';
              }
              if(isset($mfg_names[$item_key])) {
                $mfg_name = $mfg_names[$item_key];
              } else {
                $mfg_name = '';
              }
              if(isset($uom_names[$item_key])) {
                $uom_name = $uom_names[$item_key];
              } else {
                $uom_name = '';
              }
              if(isset($wholesale_prices[$item_key])) {
                $wholesale_price = $wholesale_prices[$item_key];
              } else {
                $wholesale_price = '';
              }
              if(isset($online_prices[$item_key])) {
                $online_price = $online_prices[$item_key];
              } else {
                $online_price = '';
              }                  
              if(isset($batch_nos[$item_key])) {
                $batch_no = $batch_nos[$item_key];
              } else {
                $batch_no = '';
              }              
              $item_key_a = explode("__", $item_key);
              $print_array_final[$barcode] = [$print_qty, $item_name, $item_rate, date("Y-m-d"), $item_key_a[2], $cno, $mfg_name, $uom_name, $wholesale_price, $online_price, $batch_no, '', $mfg_name];
            }

          } else {
            $this->flash->set_flash_message('<i class="fa fa-window-close" aria-hidden="true"></i>&nbsp;The selected items have no Barcodes. Please generate barcodes from Purchase register or Opening balances.',1);
            Utilities::redirect('/barcode/cbbal-itemwise?locationCode='.$form_data['locationCode']);
          }
          if(count($print_array_final)>0) {
            if(isset($_SESSION['printBarCodes'])) {
              unset($_SESSION['printBarCodes']);
            }
            $_SESSION['printBarCodes'] = $print_array_final;
            $rate_type = isset($form_data['rateType']) && $form_data['rateType'] !== '' ? Utilities::clean_string($form_data['rateType']) : ''; 
            $redirect_url = $rate_type !== '' ? '/barcodes/print?format='.$indent_format.'&r='.$rate_type : '/barcodes/print?format='.$indent_format;
            Utilities::redirect($redirect_url);
          }
        } else {
          $this->flash->set_flash_message(json_encode($form_validation['errors']),1);
          Utilities::redirect('/barcode/cbbal-itemwise?locationCode='.$form_data['locationCode']);
        }
      } else {
        $location_code = isset($form_data['locationCode']) ? Utilities::clean_string($form_data['locationCode']) : $_SESSION['lc'];
        $item_name = isset($form_data['itemName']) ? Utilities::clean_string($form_data['itemName']) : '';
        $brand_name = isset($form_data['brandName']) ? Utilities::clean_string($form_data['brandName']) : '';
        $barcode = isset($form_data['barcode']) ? Utilities::clean_string($form_data['barcode']) : '';
        $lot_no = isset($form_data['lotNo']) ? Utilities::clean_string($form_data['lotNo']) : '';
        $cno_filter = !is_null($request->get('cnoFilter')) ? Utilities::clean_string($request->get('cnoFilter')) : '';
        $bno_filter = !is_null($request->get('bnoFilter')) ? Utilities::clean_string($request->get('bnoFilter')) : '';
        $item_sku = !is_null($request->get('itemSku')) ? Utilities::clean_string($request->get('itemSku')) : '';

        $search_params = array(
          'itemName' => $item_name,
          'locationCode' => $location_code,
          'pageNo' => $page_no,
          'perPage' => $per_page,
          'fromDate' => $from_date,
          'toDate' => $to_date,
          'groupBy' => $group_by,
          'balanceType' => $balance_type,
          'invenType' => $inven_type,
          'brandName' => $brand_name,
          'barcode' => $barcode,
          'lotNo' => $lot_no,
          'cnoFilter' => $cno_filter,
          'bnoFilter' => $bno_filter,
          'itemSku' => $item_sku,
        );
      }
    } else {
      if(!is_null($request->get('locationCode')) && $request->get('locationCode') !== '') {
        $location_code = $request->get('locationCode');
      } else {
        $location_code = isset($_SESSION['lc']) ? $_SESSION['lc'] : '';
      }
      $item_name = !is_null($request->get('itemName')) ? Utilities::clean_string($request->get('itemName')) : '';
      $brand_name = !is_null($request->get('brandName')) ? Utilities::clean_string($request->get('brandName')) : '';
      $barcode = !is_null($request->get('barcode')) ? Utilities::clean_string($request->get('barcode')) : '';
      $lot_no = !is_null($request->get('lotNo')) ? Utilities::clean_string($request->get('lotNo')) : '';
      $cno_filter = !is_null($request->get('cnoFilter')) ? Utilities::clean_string($request->get('cnoFilter')) : '';
      $bno_filter = !is_null($request->get('bnoFilter')) ? Utilities::clean_string($request->get('bnoFilter')) : '';
      $item_sku = !is_null($request->get('itemSku')) ? Utilities::clean_string($request->get('itemSku')) : '';

      $search_params = array(
        'pageNo' => $page_no,
        'perPage' => $per_page,
        'locationCode' => $location_code,
        'fromDate' => $from_date,
        'toDate' => $to_date,
        'groupBy' => $group_by,
        'balanceType' => $balance_type,
        'invenType' => $inven_type,
        'itemName' => $item_name,
        'brandName' => $brand_name,
        'barcode' => $barcode,
        'lotNo' => $lot_no,
        'cnoFilter' => $cno_filter,
        'bnoFilter' => $bno_filter,
        'itemSku' => $item_sku,
      );
      // dump($search_params, 'search params.....');
    }

    $closing_balances = $this->inven_api->get_stock_report($search_params);
    // dump($closing_balances);
    // exit;
    // check api status
    if($closing_balances['status']) {
      // check whether we got products or not.
      if(count($closing_balances['results']['results'])>0) {
        $slno = Utilities::get_slno_start(count($closing_balances['results']['results']), $per_page, $page_no);
        $to_sl_no = $slno+$per_page;
        $slno++;
        if($page_no<=3) {
          $page_links_to_start = 1;
          $page_links_to_end = 10;
        } else {
          $page_links_to_start = $page_no-3;
          $page_links_to_end = $page_links_to_start+10;
        }
        if($closing_balances['results']['total_pages']<$page_links_to_end) {
          $page_links_to_end = $closing_balances['results']['total_pages'];
        }
        if($closing_balances['results']['total_records'] < $per_page) {
          $to_sl_no = ($slno+$closing_balances['results']['total_records'])-1;
        }
        $closings_a = $closing_balances['results']['results'];
        $total_pages = $closing_balances['results']['total_pages'];
        $total_records = $closing_balances['results']['total_records'];
        $record_count = $closing_balances['results']['total_records'];
      } else {
        $page_error = $closing_balances['apierror'];
      }
    } else {
      $this->flash->set_flash_message($closing_balances['apierror'],1);      
    }

   // prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'page_success' => $page_success,
      'closings' => $closings_a,
      'total_pages' => $total_pages ,
      'total_records' => $total_records,
      'record_count' =>  $record_count,
      'sl_no' => $slno,
      'to_sl_no' => $to_sl_no,
      'search_params' => $search_params,            
      'page_links_to_start' => $page_links_to_start,
      'page_links_to_end' => $page_links_to_end,
      'current_page' => $page_no,
      'categories' => $categories_a,
      'client_locations' => $client_locations,
      'location_code' => $location_code,
      'sticker_print_type_a' => ['' => 'Choose'] + Utilities::get_barcode_sticker_print_formats(),
      'rate_types' => $rate_types,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Print Barcodes for Closing Balances - Itemwise',
      'icon_name' => 'fa fa-barcode',             
    );

    // render template
    return array($this->template->render_view('barcodes-list-closings-itemwise', $template_vars), $controller_vars);
  }  

  private function _process_submitted_data($form_data=[], $submitted_item_details=[]) {
    $new_barcodes = $form_errors = $print_barcodes = [];
    $sticker_format_types = array_keys(Utilities::get_barcode_sticker_print_formats());
    $print_format = isset($form_data['format']) ? Utilities::clean_string($form_data['format']) : '';
    $rate_type = isset($form_data['rateType']) ? Utilities::clean_string($form_data['rateType']) : '';

    if($print_format === '' || !in_array($print_format, $sticker_format_types)) {
      $form_errors['format'] = 'Please choose a sticker print format';
    }

    # check whether we received correct data or not.
    foreach($submitted_item_details as $item_details) {
      $item_code = $item_details['itemCode'];
      $lot_no = $item_details['lotNo'];
      $item_key = $item_code.'__'.$lot_no;
      if(isset($form_data['stickerQty'][$item_key]) && isset($form_data['genBarcodes'][$item_key])) {
        $cleaned_params[$item_key] = $form_data['stickerQty'][$item_key];
      } else {
        $form_errors[$item_key] = 'Invalid item details.';
      }
    }
    if( count($form_errors)>0 ) {
      return ['status' => false, 'form_errors' => $form_errors];
    }

    # process submitted data
    foreach($form_data['stickerQty'] as $sticker_key => $sticker_qty) {
      if(isset($form_data['genBarcodes'][$sticker_key]) && 
         $form_data['genBarcodes'][$sticker_key] === '' &&
         $form_data['stickerQty'][$sticker_key] > 0
        ) 
      {
        $new_barcodes[] = $sticker_key;
      } elseif(
                is_numeric($form_data['genBarcodes'][$sticker_key]) && 
                $form_data['stickerQty'][$sticker_key] > 0
              ) {
        $print_barcodes[] = $form_data['genBarcodes'][$sticker_key];
      }
    }

    return ['status' => true, 'new_barcodes' => ['items' => $new_barcodes], 'print_barcodes' => $print_barcodes, 'cleaned_params' => $cleaned_params, 'format' => $print_format, 'r' => $rate_type];
  }

  private function _validate_op_barcode_form($form_data = []) {
    $form_errors = $cleaned_params = [];
    $new_barcodes = $print_barcodes = [];

    $sticker_qtys = isset($form_data['stickerQty']) ? $form_data['stickerQty'] : [];
    $avail_barcodes = isset($form_data['opBarcode']) ? $form_data['opBarcode'] : [];
    $requested_qtys = isset($form_data['requestedItems']) ? $form_data['requestedItems'] : [];

    // dump($sticker_qtys, $avail_barcodes, $requested_qtys);
    // exit;

    if(count($requested_qtys)>0) {
      foreach($requested_qtys as $index => $item_key) {
        if( isset($avail_barcodes[$item_key]) && isset($sticker_qtys[$item_key]) &&
            is_numeric($sticker_qtys[$item_key]) && $sticker_qtys[$item_key] > 0) {
          if($avail_barcodes[$item_key] === '' && strlen(Utilities::clean_string($avail_barcodes[$item_key])) === 0) {
            $new_barcodes[] = $item_key;
          } elseif(is_numeric(Utilities::clean_string($avail_barcodes[$item_key]))) {
            $print_barcodes[] = [$item_key, $sticker_qtys[$item_key]];
          }
        }
      }
      if(count($new_barcodes) <= 0 && count($print_barcodes) <= 0) {
        $form_errors['itemDetails'] = 'Invalid inputs.';
      } else {
        $cleaned_params['new_barcodes'] = $new_barcodes;
        $cleaned_params['print_barcodes'] = $print_barcodes;
      }
    } else {
      $form_errors['itemDetails'] = 'Please check items using Checkboxes for Printing barcodes.';
    }

    // dump($cleaned_params);
    // exit;

    if(count($form_errors)>0) {
      return [
        'status' => false,
        'errors' => $form_errors,
      ];
    } else {
      return [
        'status' => true,
        'cleaned_params' => $cleaned_params,
      ];      
    }
  }
}
