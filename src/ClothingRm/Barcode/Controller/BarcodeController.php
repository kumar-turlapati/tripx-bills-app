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
use ClothingRm\Purchases\Model\Purchases;
use ClothingRm\Inward\Model\Inward;
use ClothingRm\Suppliers\Model\Supplier;
use ClothingRm\Taxes\Model\Taxes;
use ClothingRm\Openings\Model\Openings;

class BarcodeController
{
  protected $views_path;

  public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->bc_model = new Barcode;
    $this->flash = new Flash;
    $this->purchase_model = new Purchases;
    $this->supplier_model = new Supplier;
    $this->taxes_model = new Taxes;
    $this->openings_model = new Openings;
  }

  public function generateBarcodeAction(Request $request) {
    $purchase_details = $suppliers_a = $form_data = [];
    $form_errors = [];

    $page_error = $inward_entry_no = $mfg_date = '';

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
      $purchase_response = $this->purchase_model->get_purchase_details($purchase_code);

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
        $inward_entry_no =  ' - Inward Entry No. { '. $purchase_details['poNo'] .' }';
        $mfg_date = $purchase_details['purchaseDate'];

        # convert received item details to template item details.
        $item_names = array_column($purchase_details['itemDetails'],'itemName');
        $item_codes = array_column($purchase_details['itemDetails'],'itemCode');        
        $inward_qtys = array_column($purchase_details['itemDetails'],'itemQty');
        $free_qtys = array_column($purchase_details['itemDetails'],'freeQty');
        $lot_nos = array_column($purchase_details['itemDetails'],'lotNo');
        $mrps = array_column($purchase_details['itemDetails'],'mrp');
        $item_rates = array_column($purchase_details['itemDetails'],'itemRate');
        $discounts = array_column($purchase_details['itemDetails'],'discount');                
        $tax_percents = array_column($purchase_details['itemDetails'],'taxPercent');
        $hsn_codes = array_column($purchase_details['itemDetails'],'hsnSacCode');
        $barcodes = array_column($purchase_details['itemDetails'],'barcode');

        $submitted_item_details = $purchase_details['itemDetails'];

        # unser item details from api data.
        unset($purchase_details['itemDetails']);

        # create form data variable.
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
      }
    }

    if(count($request->request->all()) > 0) {
      $form_data = $request->request->all();
      
      # process form data on submit
      $form_processing = $this->_process_submitted_data($form_data, $submitted_item_details);
      if($form_processing['status'] === false) {
        $this->flash->set_flash_message('Invalid form submission.', 1);
        Utilities::redirect('/barcodes/list');        
      } else {
        $new_barcodes = $form_processing['new_barcodes'];
        $print_barcodes = $form_processing['print_barcodes'];
        $cleaned_params = $form_processing['cleaned_params'];
      }

      // dump($new_barcodes, $print_barcodes, $cleaned_params);
      // exit;

      if(is_array($new_barcodes['items']) && count($new_barcodes['items']) > 0) {
        # hit api and create barcodes.
        $api_response = $this->bc_model->generate_barcode($purchase_code, $new_barcodes);
        if($api_response['status'] && count($api_response['barcodes'])>0) {
          $print_array = [];
          $index_key = 0;
          foreach($cleaned_params as $key => $print_qty) {
            $print_array[$api_response['barcodes'][$index_key]] = $print_qty;
            $index_key++;
          }
        } else {
          $message = $result['apierror'];
          $this->flash->set_flash_message($message,1);
          Utilities::redirect('/barcodes/list');          
        }
      } else {
        $print_array = [];
        $index_key = 0;
        foreach($cleaned_params as $key => $print_qty) {
          $print_array[$print_barcodes[$index_key]] = [$print_qty, $item_names[$index_key], $mrps[$index_key], $mfg_date];
          $index_key++;
        }
        // dump($print_array, $item_names, $mrps);
        // exit;
      }

      if(count($print_array)>0) {
        if(isset($_SESSION['printBarCodes'])) {
          unset($_SESSION['printBarCodes']);
        }
        $_SESSION['printBarCodes'] = $print_array;
        Utilities::redirect('/barcodes/print');
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
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Generate Barcodes'.$inward_entry_no,
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

    if(!is_null($request->get('format')) && $request->get('format') === 'indent') {
      $print_tpl = 'barcode-print-stickers-html';
    } else {
      $print_tpl = 'barcode-print-stickers-mrp-html';
    }

    // build variables
    $controller_vars = array(
      'page_title' => 'Print Barcodes',
      'icon_name' => 'fa fa-print',
    );

    // render template
    return array($this->template->render_view($print_tpl, []), $controller_vars);     
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
    $lot_no = $request->get('lotNo')!== null ? Utilities::clean_string($request->get('lotNo')) : '';
    $item_name = $request->get('itemName')!== null ? Utilities::clean_string($request->get('itemName')) : '';
    $location_code = $request->get('locationCode')!== null ? Utilities::clean_string($request->get('locationCode')) : '';
    $search_params = array(
      'barcode' => $barcode,
      'poNo' => $po_no,
      'itemName' => $item_name,
      'lotNo' => $lot_no,
      'locationCode' => $location_code,
      'pageNo' => $page_no,
      'perPage' => $per_page,
    );

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
    $per_page = 100;
    if( count($request->request->all()) > 0) {
      $form_data = $request->request->all();
      # check whether the form is submitted or filtered.
      if(isset($form_data['op']) && $form_data['op'] === 'save') {
        $location_code = $form_data['locationCode'];
        $item_names = $form_data['itemNames'];
        $item_rates = $form_data['itemRates'];
        $barcodes_a = $form_data['opBarcode'];
        $sticker_qtys = $form_data['stickerQty'];
        $indent_format = $form_data['indentFormat'];

        $form_validation = $this->_validate_op_barcode_form($form_data);
        if($form_validation['status']) {
          $new_barcodes = $form_validation['cleaned_params']['new_barcodes'];
          $existing_barcodes = $form_validation['cleaned_params']['print_barcodes'];
          if(count($new_barcodes)>0) {
            $api_response = $this->bc_model->generate_barcode_opening($new_barcodes);
            if($api_response['status'] && count($api_response['barcodes'])>0) {
              foreach($api_response['barcodes'] as $item_key => $new_barcode) {
                if(isset($sticker_qtys[$item_key])) {
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
                  if(isset($sticker_qtys[$item_key])) {
                    $print_qty = $sticker_qtys[$item_key];
                  } else {
                    $print_qty = 0;
                  } 
                  $print_array_final[$new_barcode] = [$print_qty, $item_name, $item_rate, date("d-m-Y")]; 
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
              $print_array_final[$barcode] = [$print_qty, $item_name, $item_rate, date("d-m-Y")];
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
          $this->flash->set_flash_message($form_validation['errors'],1);
        }

      } else {
        $item_name = isset($form_data['itemName']) ? Utilities::clean_string($form_data['itemName']) : '';
        $location_code = isset($form_data['locationCode']) ? Utilities::clean_string($form_data['locationCode']) : $_SESSION['lc'];
        $search_params = array(
          'itemName' => $item_name,
          'locationCode' => $location_code,
          'pageNo' => $page_no,
          'perPage' => $per_page,
        );
      }
    } else {
      if(!is_null($request->get('locationCode')) && $request->get('locationCode') !== '') {
        $location_code = $request->get('locationCode');
      } else {
        $location_code = isset($_SESSION['lc']) ? $_SESSION['lc'] : '';
      }
      $search_params = array(
        'pageNo' => $page_no,
        'perPage' => $per_page,
        'locationCode' => $location_code,
      );
    }

    $openings = $this->openings_model->opbal_list($search_params);
    # check api status
    if($openings['status']) {
      # check whether we got products or not.
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
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Generate Barcodes for Opening Balances',
      'icon_name' => 'fa fa-barcode',             
    );

    // render template
    return array($this->template->render_view('barcodes-list-openings', $template_vars), $controller_vars);
  }

  private function _process_submitted_data($form_data=[], $submitted_item_details=[]) {
    $new_barcodes = $form_errors = $print_barcodes = [];

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
         $form_data['genBarcodes'][$sticker_key] === '') 
      {
        $new_barcodes[] = $sticker_key;
      } elseif(is_numeric($form_data['genBarcodes'][$sticker_key])) {
        $print_barcodes[] = $form_data['genBarcodes'][$sticker_key];
      }
    }

    return ['status' => true, 'new_barcodes' => ['items' => $new_barcodes], 'print_barcodes' => $print_barcodes, 'cleaned_params' => $cleaned_params];
  }

  private function _validate_op_barcode_form($form_data = []) {
    $form_errors = $cleaned_params = [];
    $new_barcodes = $print_barcodes = [];

    $sticker_qtys = isset($form_data['stickerQty']) ? $form_data['stickerQty'] : [];
    $avail_barcodes = isset($form_data['opBarcode']) ? $form_data['opBarcode'] : [];
    $requested_qtys = isset($form_data['requestedItems']) ? $form_data['requestedItems'] : [];

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