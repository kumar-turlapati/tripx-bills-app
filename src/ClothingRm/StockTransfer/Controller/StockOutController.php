<?php 

namespace ClothingRm\StockTransfer\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;

use ClothingRm\StockTransfer\Model\StockOut;
use ClothingRm\Inventory\Model\Inventory;
use ClothingRm\Products\Model\Products;
use Taxes\Model\Taxes;

class StockOutController
{
  protected $views_path,$finmodel;

  public function __construct() {
    $this->views_path = __DIR__.'/../Views/';
    $this->sto_model = new StockOut;
    $this->inv_model = new Inventory;
    $this->flash = new Flash;
    $this->products_model = new Products;    
    $this->taxes_model = new Taxes;
  }

  // Stock transfer create action.
  public function stockOutCreateAction(Request $request) {

    $page_error = $page_success = $from_location = $to_location = '';
    $qtys_a = $form_data = $form_errors = $taxes = [];

    for($i=1;$i<=500;$i++) {
      $qtys_a[$i] = $i;
    }

    // ---------- get location codes from api -----------------------
    $client_locations = Utilities::get_client_locations(true, true, true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];
      $location_names[$location_key_a[0]] = $location_value;
    }

    // ---------- get tax percents from api ----------------------
    $taxes_a = $this->taxes_model->list_taxes();
    if($taxes_a['status'] && count($taxes_a['taxes'])>0 ) {
      $taxes_raw = $taxes_a['taxes'];
      foreach($taxes_a['taxes'] as $tax_details) {
        $taxes[$tax_details['taxCode']] = $tax_details['taxPercent'];
      }
    }    

    if(count($request->request->all()) > 0) {
      $form_data = $request->request->all();
      $validate_form = $this->_validate_form_data($form_data, $location_names);
      // dump($validate_form);
      // exit;
      $status = $validate_form['status'];
      if($status) {
        $cleaned_params = $validate_form['cleaned_params'];
        $result = $this->sto_model->create_stock_out_entry($cleaned_params);
        if($result['status']) {
          $message = 'Stock transfer successfully completed with Voucher No. ` '.$result['billNo'].' `';
          $this->flash->set_flash_message($message);
          Utilities::redirect('/stock-transfer/register');
        } else {
          $this->flash->set_flash_message($result['apierror'],1);
        }
      } else {
        $form_errors = $validate_form['errors'];
      }
      $from_location = Utilities::clean_string($request->get('fromLocation'));
      $to_location = Utilities::clean_string($request->get('toLocation'));
      $from_loc_name = isset($location_names[$from_location]) ? $location_names[$from_location] : 'InvalidFromStore';
      $to_loc_name = isset($location_names[$to_location]) ? $location_names[$to_location] : 'InvalidToStore';
    } elseif(!is_null($request->get('fromLocation')) && !is_null($request->get('toLocation'))) {
      $from_location = Utilities::clean_string($request->get('fromLocation'));
      $to_location = Utilities::clean_string($request->get('toLocation'));
      if(!(in_array($from_location, $location_codes) && in_array($to_location, $location_codes))) {
        $this->flash->set_flash_message('Invalid From and To Store Names.',1);
        Utilities::redirect('/stock-transfer/choose-location');
      } else {
        $from_loc_name = isset($location_names[$from_location]) ? $location_names[$from_location] : 'InvalidFromStore';
        $to_loc_name = isset($location_names[$to_location]) ? $location_names[$to_location] : 'InvalidToStore';
      }
    } else {
      $message = 'Choose From and To Location for transfer.';
      $this->flash->set_flash_message($message, 1);
      Utilities::redirect('/stock-transfer/choose-location');
    }

    // build variables
    $controller_vars = array(
      'page_title' => 'Stock Transfer || '.$from_loc_name.' >>> '.$to_loc_name,
      'icon_name' => 'fa fa-database',
    );

    $template_vars = array(
      'qtys_a' => array(0=>'Choose')+$qtys_a,
      'errors' => $form_errors,
      'page_error' => $page_error,
      'page_success' => $page_success,
      'btn_label' => 'Transfer Stock',
      'form_data' => $form_data,
      'flash_obj' => $this->flash,
      'from_location' => $from_location,
      'to_location' => $to_location,
      'taxes' => $taxes,
      'taxcalc_opt_a' => array('e'=>'Exluding Item Rate', 'i' => 'Including Item Rate'),      
      'client_locations' => array(''=>'Choose') + $client_locations,
      'default_location' => isset($_SESSION['lc']) ? $_SESSION['lc'] : '',
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('stock-out-create', $template_vars), $controller_vars);
  }

  // Stock transfer view action.
  public function stockOutViewAction(Request $request) {
    $page_error = $page_success = $from_location = $to_location = '';
    $qtys_a = $form_data = $form_errors = $taxes = [];

    // validate transfer code.
    if(!is_null($request->get('transferCode')) && $request->get('transferCode') !== '') {
      $transfer_code = Utilities::clean_string($request->get('transferCode'));
      $transfer_status = Utilities::is_admin() ? 99 : 1;
      $transfer_details_reponse = $this->sto_model->get_stock_out_entry_details($transfer_code, 'from', $transfer_status);
      // dump($transfer_details_reponse);
      // exit;
      if($transfer_details_reponse['status'] === false) {
        $this->flash->set_flash_message('Invalid Transfer Code', 1);
        Utilities::redirect('/stock-transfer/register');
      } else {
        $form_data = $this->_map_form_data_with_api_response($transfer_details_reponse['stoDetails']);
      }
    }

    for($i=1;$i<=500;$i++) {
      $qtys_a[$i] = $i;
    }

    // ---------- get location codes from api -----------------------
    $client_locations = Utilities::get_client_locations(true, true, true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];
      $location_names[$location_key_a[0]] = $location_value;
    }

    // ---------- get tax percents from api ----------------------
    $taxes_a = $this->taxes_model->list_taxes();
    if($taxes_a['status'] && count($taxes_a['taxes'])>0 ) {
      $taxes_raw = $taxes_a['taxes'];
      foreach($taxes_a['taxes'] as $tax_details) {
        $taxes[$tax_details['taxCode']] = $tax_details['taxPercent'];
      }
    }    

    // build variables
    $controller_vars = array(
      'page_title' => "View Stock Transfer { Voucher No. $form_data[transferNo] }",
      'icon_name' => 'fa fa-database',
    );

    $template_vars = array(
      'qtys_a' => array(0=>'Choose')+$qtys_a,
      'errors' => $form_errors,
      'page_error' => $page_error,
      'page_success' => $page_success,
      'btn_label' => 'Transfer Stock',
      'form_data' => $form_data,
      'flash_obj' => $this->flash,
      'taxes' => $taxes,
      'taxcalc_opt_a' => array('e'=>'Exluding Item Rate', 'i' => 'Including Item Rate'),      
      'client_locations' => array(''=>'Choose') + $client_locations,
      'default_location' => isset($_SESSION['lc']) ? $_SESSION['lc'] : '',
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('stock-out-view', $template_vars), $controller_vars);
  }

  // Stock transfer validate action.
  public function stockOutValidateAction(Request $request) {
    $page_error = $page_success = $from_location = $to_location = '';
    $qtys_a = $form_data = $form_errors = $taxes = [];

    $transfer_code = Utilities::clean_string($request->get('transferCode'));
    $transfer_details_reponse = $this->sto_model->get_stock_out_entry_details($transfer_code, 'to');
    // dump($transfer_details_reponse);
    // exit;
    if($transfer_details_reponse['status'] === false) {
      $this->flash->set_flash_message('Invalid Transfer Code', 1);
      Utilities::redirect('/stock-transfer/register');
    }    

    // check whether the form is submmitted.
    if(count($request->request->all()) > 0) {

      // validate session exists or not.
      if(isset($_SESSION[$transfer_code]) && count($_SESSION[$transfer_code]) > 0) {
        $scanned_data = [];
        unset($_SESSION[$transfer_code]['total_scanned']);
        foreach($_SESSION[$transfer_code] as $transfer_key => $transfer_details) {
          $scanned_data['itemLotNos'][] = $transfer_key;
          $scanned_data['scannedQty'][] = $transfer_details['scanned'];
        }

        // dump($scanned_data);
        // exit;

        if( isset($scanned_data['total_scanned']) ) {
          unset($scanned_data['total_scanned']);
        }

      } else {
        $this->flash->set_flash_message('Unable to retrieve Scanned data.', 1);
        Utilities::redirect('/stock-transfer/register');
      }

      // dump($scanned_data, $_SESSION);
      // exit;

      // hit api and save the scanned data.
      $api_response = $this->sto_model->post_scanned_items_in_stock_transfer($scanned_data, $transfer_code);
      if($api_response['status']) {
        $this->flash->set_flash_message('<i class="fa fa-check" aria-hidden="true"></i> Stock transfer successfully completed.');
        Utilities::redirect('/stock-transfer/register');               
      } else {
        $this->flash->set_flash_message('<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> '.$api_response['apierror'], 1);
        Utilities::redirect('/stock-transfer/register');
      }

    // validate transfer code.
    } elseif(!is_null($request->get('transferCode')) && $request->get('transferCode') !== '') {
      $form_data = $this->_map_form_data_with_api_response($transfer_details_reponse['stoDetails']);
      if(isset($_SESSION[$transfer_code])) {
        unset($_SESSION[$transfer_code]);
      }
      $transfer_items = $transfer_details_reponse['stoDetails']['itemDetails'];
      if(count($transfer_items) > 0) {
        foreach($transfer_items as $key => $item_details) {
          $session_item_key = $item_details['itemCode'].'__'.$item_details['lotNo'];
          $transfer_qty = $item_details['itemQty'];
          $_SESSION[$transfer_code][$session_item_key]['actual']  = $transfer_qty;
          $_SESSION[$transfer_code][$session_item_key]['scanned'] = 0;
        }
        $_SESSION[$transfer_code]['total_scanned'] = 0;        
      } else {
        $this->flash->set_flash_message('<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Flushed Items or No items are available in this stock transfer.', 1);
        Utilities::redirect('/stock-transfer/register');
      }
    }

    for($i=1;$i<=500;$i++) {
      $qtys_a[$i] = $i;
    }

    // ---------- get location codes from api -----------------------
    $client_locations = Utilities::get_client_locations(true, true, true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];
      $location_names[$location_key_a[0]] = $location_value;
    }

    // ---------- get tax percents from api ----------------------
    $taxes_a = $this->taxes_model->list_taxes();
    if($taxes_a['status'] && count($taxes_a['taxes'])>0 ) {
      $taxes_raw = $taxes_a['taxes'];
      foreach($taxes_a['taxes'] as $tax_details) {
        $taxes[$tax_details['taxCode']] = $tax_details['taxPercent'];
      }
    }

    // dump($_SESSION);

    // build variables
    $controller_vars = array(
      'page_title' => 'Validate Stock Transfer / స్టాక్ బదిలీ సరిచూసుకొనే ప్రక్రియ',
      'icon_name' => 'fa fa-check',
    );

    $template_vars = array(
      'qtys_a' => array(0=>'Choose')+$qtys_a,
      'errors' => $form_errors,
      'page_error' => $page_error,
      'page_success' => $page_success,
      'btn_label' => 'Transfer Stock',
      'form_data' => $form_data,
      'flash_obj' => $this->flash,
      'taxes' => $taxes,
      'taxcalc_opt_a' => array('e'=>'Exluding Item Rate', 'i' => 'Including Item Rate'),      
      'client_locations' => array(''=>'Choose') + $client_locations,
      'default_location' => isset($_SESSION['lc']) ? $_SESSION['lc'] : '',
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,
      'transfer_code' => $transfer_code,
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('stock-out-validate', $template_vars), $controller_vars);    
  }

  // Delete stock transfer
  public function stockOutDeleteAction(Request $request) {
    $page_error = $page_success = $from_location = $to_location = '';
    $qtys_a = $form_data = $form_errors = $taxes = [];

    // validate transfer code.
    if(!is_null($request->get('transferCode')) && $request->get('transferCode') !== '') {
      $transfer_code = Utilities::clean_string($request->get('transferCode'));
      $transfer_status = Utilities::is_admin() ? 99 : 1;
      $transfer_details_reponse = $this->sto_model->get_stock_out_entry_details($transfer_code, 'from', $transfer_status);
      if($transfer_details_reponse['status'] === false) {
        $this->flash->set_flash_message('Invalid Transfer Code', 1);
        Utilities::redirect('/stock-transfer/register');
      } else {
        $voucher_no = $transfer_details_reponse['stoDetails']['transferNo'];
        $delete_response = $this->sto_model->delete_stock_transfer_voucher($transfer_code);
        if($delete_response['status'] && $delete_response['response'] === 'Success') {
          $message = 'Voucher No. ` '.$voucher_no.' ` deleted successfully.';
          $this->flash->set_flash_message($message);
        } else {
          $message = 'An error occurred while deleting Voucher No. ` '.$voucher_no.' `.';
          $this->flash->set_flash_message($message, 1);
        }
        Utilities::redirect('/stock-transfer/register');          
      }
    }
  }

  private function _map_form_data_with_api_response($form_data = []) {
    $cleaned_params = [];
    if(isset($form_data['itemDetails']) && $form_data['itemDetails'] > 0) {
      foreach($form_data['itemDetails'] as $key => $item_details) {
        $cleaned_params['itemName'][$key] = $item_details['itemName'];
        $cleaned_params['itemAvailQty'][$key] = '';
        $cleaned_params['itemSoldQty'][$key] = $item_details['itemQty'];
        $cleaned_params['itemRate'][$key] = $item_details['mrp'];
        $cleaned_params['itemTaxPercent'][$key] = $item_details['taxPercent'];
        $cleaned_params['lotNo'][$key] = $item_details['lotNo'];
        $cleaned_params['status'][$key] = $item_details['status'];
        $cleaned_params['scannedDate'][$key] = $item_details['scanDate'];
        $cleaned_params['cno'][$key] = $item_details['cno'];
        $cleaned_params['barcode'][$key] = $item_details['barcode'];
      }
      unset($form_data['itemDetails']);
      $form_data['itemDetails'] = $cleaned_params;
    } else {
      $form_data['itemDetails'] = [];      
    }
    
    return $form_data;
  }

  // List of stock out transactions.
  public function stockOutTransactionsList(Request $request) {
    $search_params = $transactions = $location_ids = [];
    $page_error = '';
    $total_pages = $total_records = $record_count = $page_no = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;

    # ---------- get from location codes from api -----------------------
    $from_locations = Utilities::get_client_locations(true, true, true);
    $default_location = isset($_SESSION['lc']) ? $_SESSION['lc'] : '';

    $to_locations = Utilities::get_client_locations(true, true, true);
    foreach($to_locations as $location_key => $location_value) {
        $location_key_a = explode('`', $location_key);
        $location_ids[$location_key_a[1]] = $location_value;
    }

    # parse request parameters.
    $from_date = $request->get('fromDate') !== null ? Utilities::clean_string($request->get('fromDate')):'01-'.date('m').'-'.date("Y");
    $to_date = $request->get('toDate') !== null ? Utilities::clean_string($request->get('toDate')):date("d-m-Y");
    $from_location_code = $request->get('fromLocationCode')!== null ? Utilities::clean_string($request->get('fromLocationCode')) : $default_location;
    $to_location_code = $request->get('toLocationCode') !== null ? Utilities::clean_string($request->get('toLocationCode')) : '';
    $page_no = $request->get('pageNo') !== null?Utilities::clean_string($request->get('pageNo')) : 1;
    $per_page = 100;

    $search_params = array(
      'fromDate' => $from_date,
      'toDate' => $to_date,
      'fromLocationCode' => $from_location_code,
      'toLocationCode' => $to_location_code,
      'pageNo' => $page_no,
      'perPage' => $per_page,
    );

    # hit API
    $api_response = $this->sto_model->get_stockout_transactions_list($search_params);
    if($api_response['status']===true) {
      if(count($api_response['data']['response']['entries'])>0) {
          $slno = Utilities::get_slno_start(count($api_response['data']['response']['entries']),$per_page,$page_no);
          $to_sl_no = $slno+$per_page;
          $slno++;
          if($page_no<=3) {
            $page_links_to_start = 1;
            $page_links_to_end = 10;
          } else {
            $page_links_to_start = $page_no-3;
            $page_links_to_end = $page_links_to_start+10;            
          }
          if($api_response['data']['response']['total_pages']<$page_links_to_end) {
            $page_links_to_end = $api_response['data']['response']['total_pages'];
          }
          if($api_response['data']['response']['this_page'] < $per_page) {
            $to_sl_no = ($slno+$api_response['data']['response']['this_page'])-1;
          }

          $transactions = $api_response['data']['response']['entries'];
          $total_pages = $api_response['data']['response']['total_pages'];
          $total_records = $api_response['data']['response']['total_records'];
          $record_count = $api_response['data']['response']['this_page'];
      } else {
        $page_error = $api_response['apierror'];
      }
    } else {
      $page_error = $api_response['apierror'];
    }

    # prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'transactions' => $transactions,
      'total_pages' => $total_pages ,
      'total_records' => $total_records,
      'record_count' => $record_count,
      'sl_no' => $slno,
      'to_sl_no' => $to_sl_no,
      'page_links_to_start' => $page_links_to_start,
      'page_links_to_end' => $page_links_to_end,
      'current_page' => $page_no,
      'search_params' => $search_params,
      'from_locations' => ['' => 'From Store Name'] + $from_locations,
      'to_locations' => ['' => 'To Store Name'] + $to_locations,
      'location_ids' => $location_ids,      
    );

    # build variables
    $controller_vars = array(
      'page_title' => 'Stock Transfer Register',
      'icon_name' => 'fa fa-database',
    );

    # render template
    $template = new Template($this->views_path);
    return array($template->render_view('stock-out-register', $template_vars), $controller_vars);    
  }

  private function _validate_form_data($form_data=[], $location_names = []) {
    // dump($form_data);
    // exit;

    $cleaned_params = $form_errors = [];
    $tot_billable_value = $tot_tax_value = $round_off = $net_pay = 0;

    $one_item_found = false;

    $item_details = $form_data['itemDetails'];
    $from_location = Utilities::clean_string($form_data['fromLocation']);
    $to_location = Utilities::clean_string($form_data['toLocation']);
    $transfer_date = Utilities::clean_string($form_data['transferDate']);
    $tax_calc_option = Utilities::clean_string($form_data['taxCalcOption']);    

    # validate from location code
    if(isset($location_names[$from_location])) {
      $cleaned_params['fromLocation'] = $from_location;
    } else {
      $form_errors['fromLocation'] = 'Invalid from location.'; 
    }

    # validate to location code
    if(isset($location_names[$to_location])) {
      $cleaned_params['toLocation'] = $to_location;
    } else {
      $form_errors['toLocation'] = 'Invalid to location.'; 
    }    

    # validate item details.
    for($item_key=0;$item_key<12;$item_key++) {
      if($item_details['itemName'][$item_key] !== '') {
        $one_item_found = true;

        $item_name = Utilities::clean_string($item_details['itemName'][$item_key]);
        $item_ava_qty = Utilities::clean_string($item_details['itemAvailQty'][$item_key]);
        $item_sold_qty = Utilities::clean_string($item_details['itemSoldQty'][$item_key]);
        $item_rate = Utilities::clean_string($item_details['itemRate'][$item_key]);
        $item_tax_percent = Utilities::clean_string($item_details['itemTaxPercent'][$item_key]);
        $lot_no = Utilities::clean_string($item_details['lotNo'][$item_key]);

        $item_total = round($item_sold_qty * $item_rate, 2);
        if($tax_calc_option === 'i') {
          $item_tax_amount = 0;
        } else {
          $item_tax_amount = round(($item_total * $item_tax_percent)/100, 2);
        }

        $tot_billable_value += $item_total;
        $tot_tax_value += $item_tax_amount;

        # validate item name.
        if($item_name === '') {
          $form_errors['itemDetails']['itemName'][$item_key] = 'Invalid item name.';
        } else {
          $cleaned_params['itemDetails']['itemName'][$item_key] = $item_name;
        }

        # validate item avaiable qty.
        if(!is_numeric($item_ava_qty) || $item_ava_qty<=0) {
          $form_errors['itemDetails']['itemAvailQty'][$item_key] = 'Invalid available qty.';
        } else {
          $cleaned_params['itemDetails']['itemAvailQty'][$item_key] = $item_ava_qty;
        }

        # validate sold qty.
        if(!is_numeric($item_sold_qty) || $item_sold_qty<=0) {
          $form_errors['itemDetails']['itemSoldQty'][$item_key] = 'Invalid tran. qty.';
        } else {
          $cleaned_params['itemDetails']['itemSoldQty'][$item_key] = $item_sold_qty;
        }

        # validate item rate.
        if(!is_numeric($item_rate) || $item_rate<=0) {
          $form_errors['itemDetails']['itemRate'][$item_key] = 'Invalid item rate.';
        } else {
          $cleaned_params['itemDetails']['itemRate'][$item_key] = $item_rate;
        }

        # validate item tax.
        if(!is_numeric($item_tax_percent) || $item_tax_percent<0) {
          $form_errors['itemDetails']['itemTaxPercent'][$item_key] = 'Invalid tax rate.';
        } else {
          $cleaned_params['itemDetails']['itemTaxPercent'][$item_key] = $item_tax_percent;
        }

        # validate lot no.
        if(ctype_alnum($lot_no)) {
          $cleaned_params['itemDetails']['lotNo'][$item_key] = $lot_no;
        } else {
          $form_errors['itemDetails']['lotNo'][$item_key] = 'Invalid Lot No.';  
        }        

        # validate if sold qty. is more than available qty.
        if($item_sold_qty>$item_ava_qty) {
          $form_errors['itemDetails']['itemSoldQty'][$item_key] = 'Invalid tran. qty.';
        }
      }
    }

    $net_pay = round($tot_billable_value + $tot_tax_value, 0);
    // dump('net pay is...'.$net_pay);

    # if no items are available through an error.
    if($one_item_found === false) {
      $form_errors['itemDetails']['itemName'][0] = 'Invalid item name.';
      $form_errors['itemDetails']['itemAvailQty'][0] = 'Invalid available qty.';
      $form_errors['itemDetails']['itemSoldQty'][0] = 'Invalid tran. qty.';
      $form_errors['itemDetails']['itemRate'][0] = 'Invalid item rate.';
      $form_errors['itemDetails']['itemTaxPercent'][0] = 'Invalid tax rate.';      
    }

    # add misc parameters.
    if(Utilities::is_valid_fin_date($transfer_date)) {
      $cleaned_params['transferDate'] = $transfer_date;
    } else {
      $form_errors['transferDate'] = 'Stock Transfer Date is out of Financial year dates.';
    }

    $cleaned_params['taxCalcOption'] = $tax_calc_option;

    // dump($form_errors);

    # return response.
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
