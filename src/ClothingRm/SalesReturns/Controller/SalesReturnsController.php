<?php 

namespace ClothingRm\SalesReturns\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;

use ClothingRm\SalesReturns\Model\SalesReturns;
use ClothingRm\Sales\Model\Sales;

class SalesReturnsController {
  protected $views_path;
  
  public function __construct() {
    $this->views_path = __DIR__.'/../Views/';
  }
  
  // sales return entry
  public function salesReturnEntryAction(Request $request) {

    $errors = $sale_details = $sale_item_details = [];
    $submitted_data = $return_item_details = [];
    $return_items_a = $return_details = [];

    $page_error = $page_success = '';
    $path_info = $request->getpathInfo();
    $qtys_a = array(0=>'Choose');    

    # check request source.
    $update_flag = strpos($path_info, 'sales-return/update');

    # initialize models.
    $sales_returns = new SalesReturns;
    $sales = new Sales;
    $flash = new Flash;

    // get sales details from salesCode
    if( !is_null($request->get('salesCode')) && $request->get('salesCode') !=='' ) {
      $sales_code = Utilities::clean_string($request->get('salesCode'));
      $sales_response = $sales->get_sales_details($sales_code);
      if($sales_response['status']) {
        $sale_details = $sales_response['saleDetails'];
        // dump($sale_details);
        // exit;
        $sale_date = date("Y-m-d", strtotime($sale_details['invoiceDate']));
        $date_diff = time()-strtotime($sale_date);
        $total_days = floor($date_diff / (60 * 60 * 24));

        # for business owner we void this rule.
        if( $total_days>30 && isset($_SESSION['utype']) && $_SESSION['utype']>3 ) {
          $flash->set_flash_message('Returns are not accepted after 30 days from sale date.',1);
          Utilities::redirect('/sales/list');
        }

        if(count($sale_details['itemDetails'])>0) {
          $sale_item_details = $sale_details['itemDetails'];
          unset($sale_details['itemDetails']);
          if(isset($sale_details['returnDetails'])) {
            $return_item_details = $sale_details['returnDetails'];
            unset($sale_details['returnDetails']);
            foreach($return_item_details as $ritem_details) {
              $return_key = $ritem_details['itemName'].'__'.$ritem_details['lotNo'];
              $tot_return_qtys[$return_key] = $ritem_details['totReturnQty'];
            }
            // $return_item_keys = array_column($return_item_details, 'itemCode');
            // $return_item_qtys = array_column($return_item_details, 'totReturnQty');
            // $return_item_lots = array_column($return_item_details, 'lotNo');
            // $tot_return_qtys = array_combine($return_item_keys, $return_item_qtys);
          } else {
            $tot_return_qtys = [];
          }
        } else {
          $flash->set_flash_message('No items found to return.',1);
          Utilities::redirect('/sales-return/list');                    
        }
      } else {
        $flash->set_flash_message('Invalid sales code for return transaction',1);
        Utilities::redirect('/sales/list');
      }
    } else {
      $flash->set_flash_message('Sales code not found',1);
      Utilities::redirect('/sales/list');
    }

    # check to see return code is valid or not.
    // if($update_flag) {
    //   if($request->get('salesReturnCode') && $request->get('salesReturnCode')!=='') {
    //     $return_code = $request->get('salesReturnCode');
    //     $return_details = $sales_returns->get_sales_return_details($return_code);
    //     if($return_details['status']===true) {
    //       $return_items_qtys = array_column($return_details['returnDetails']['itemDetails'],'itemQty');
    //       $return_item_names = array_column($return_details['returnDetails']['itemDetails'],'itemName');
    //       $return_items_a = array_combine($return_item_names, $return_items_qtys);
    //     } else {
    //       $flash->set_flash_message('Invalid Sales Return Code.',1);
    //       Utilities::redirect('/sales-return/entry/'.$sales_code);                     
    //     }
    //   } else {
    //     $flash->set_flash_message('Invalid Sales Return Code.',1);
    //     Utilities::redirect('/sales-return/entry/'.$sales_code);                
    //   }
    //   $page_title = 'Update Sales Return - Bill No - '.$sale_details['billNo'];
    //   $btn_label = 'Update';
    // } else {
      $page_title = 'Sales Return';
      $btn_label = 'Save';
    // }

    if(count($request->request->all()) > 0) {
      $submitted_data = $request->request->all();
      if($update_flag) {
        $sales_ret_response = $sales_returns->updateSalesReturn($submitted_data,$return_code,$sale_item_details);
      } else {
        $sales_ret_response = $sales_returns->createSalesReturn($submitted_data,$sales_code,$sale_item_details);
      }

      $status = $sales_ret_response['status'];
      if($status === false) {
        if(isset($sales_ret_response['errors'])) {
          $errors     =   $sales_ret_response['errors'];
        } elseif(isset($sales_ret_response['apierror'])) {
          $page_error =   $sales_ret_response['apierror'];
        }
      } elseif($update_flag) {
        $flash->set_flash_message('Sales Return Transaction updated successfully');
        Utilities::redirect('/sales-return/update/'.$sales_code.'/'.$return_code);
      } else {
        $success = 'Sales Return transaction saved successfully with MRN No. <b>`'.$sales_ret_response['mrnNo'].'`</b>. Credit note raised with serial number `'.$sales_ret_response['cnNo'].'`';
        $flash->set_flash_message($success);
        Utilities::redirect('/sales-return/list');                
      }
    } elseif(count($return_details)>0) {
      $submitted_data = $return_details['returnDetails'];
    }

    // build variables
    $controller_vars = array(
      'page_title' => $page_title,
      'icon_name' => 'fa fa-repeat',
    );

    $ages_a[0] = 'Choose';
    for($i=0;$i<500;$i++) {
      $qtys_a[$i] = $i;
    }

    $qtys_a[0] = 'Choose';

    // prepare form variables.
    $template_vars = array(
      'sale_details' => $sale_details,
      'sale_item_details' => $sale_item_details,
      'tot_return_qtys' => $tot_return_qtys,
      'return_items' => $return_items_a,
      'status' => Constants::$RECORD_STATUS,
      'payment_methods' => Constants::$PAYMENT_METHODS_RC,
      'ages' => $ages_a,
      'credit_days_a' => array(0=>'Choose'),
      'qtys_a' => $qtys_a,
      'yes_no_options' => array(''=>'Choose', 1=>'Yes', 0=>'No'),
      'errors' => $errors,
      'page_error' => $page_error,
      'page_success' => $page_success,
      'btn_label' => $btn_label,
      'submitted_data' => $submitted_data,
      'flash_obj' => $flash,
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('sales-return-entry', $template_vars), $controller_vars);
  }
  
  // sales return view
  public function salesReturnViewAction(Request $request) {
    $errors = $sale_details = $sale_item_details = array();
    $submitted_data = $return_item_details = array();
    $return_items_a = $return_details = array();

    $page_error = $page_success = '';
    $path_info = $request->getpathInfo();
    $qtys_a = array(0=>'Choose');

    # check request source.
    $update_flag = strpos($path_info, 'sales-return/update');

    # initialize model.
    $sales_returns = new SalesReturns;
    $sales = new Sales;
    $flash = new Flash;

      # get sales details from salesCode
      if($request->get('salesCode') && $request->get('salesCode')!=='') {
          $sales_code = Utilities::clean_string($request->get('salesCode'));
          $sales_response = $sales->get_sales_details($sales_code);
          if($sales_response['status']===true) {
              $sale_details = $sales_response['saleDetails'];
              if(count($sale_details['itemDetails'])>0) {
                  $sale_item_details = $sale_details['itemDetails'];
                  unset($sale_details['itemDetails']);
              } else {
                  $flash->set_flash_message('No items found to return.',1);
                  Utilities::redirect('/sales-return/list');
              }
          } else {
              $flash->set_flash_message('Invalid sales code',1);
              Utilities::redirect('/sales-return/list');
          }
      } else {
          $flash->set_flash_message('Sales code is mandatory',1);
          Utilities::redirect('/sales-return/list');            
      }

      # check to see return code is valid or not.
      if($request->get('salesReturnCode') && $request->get('salesReturnCode')!=='') {
          $return_code = $request->get('salesReturnCode');
          $return_details = $sales_returns->get_sales_return_details($return_code);
          if($return_details['status']) {
              $return_items_qtys = array_column($return_details['returnDetails']['itemDetails'],'itemQty');
              $return_item_names = array_column($return_details['returnDetails']['itemDetails'],'itemName');
              $return_items_a = array_combine($return_item_names, $return_items_qtys);
          } else {
              $flash->set_flash_message('Invalid Sales Return Code.',1);
              Utilities::redirect('/sales-return/entry/'.$sales_code);                     
          }
      } else {
          $flash->set_flash_message('Invalid Sales Return Code.',1);
          Utilities::redirect('/sales-return/entry/'.$sales_code);                
      }

      $page_title = 'View Sales Return - Bill No - '.$sale_details['billNo'];
      $submitted_data = $return_details['returnDetails'];

      // build variables
      $controller_vars = array(
        'page_title' => $page_title,
        'icon_name' => 'fa fa-repeat',
      );

      $ages_a[0] = 'Choose';
      for($i=1;$i<=150;$i++) {
        $ages_a[$i] = $i;
      }
      for($i=1;$i<=365;$i++) {
        $credit_days_a[$i] = $i;
      }
      for($i=0;$i<500;$i++) {
        $qtys_a[$i] = $i;
      }

      // prepare form variables.
      $template_vars = array(
        'sale_details' => $sale_details,
        'sale_item_details' => $sale_item_details,
        'return_items' => $return_items_a,
        'status' => Constants::$RECORD_STATUS,
        'payment_methods' => Constants::$PAYMENT_METHODS_RC,
        'ages' => $ages_a,
        'credit_days_a' => array(0=>'Choose') +$credit_days_a,
        'qtys_a' => $qtys_a,
        'yes_no_options' => array(''=>'Choose', 1=>'Yes', 0=>'No'),
        'errors' => $errors,
        'page_error' => $page_error,
        'page_success' => $page_success,
        'submitted_data' => $submitted_data,
        'flash_obj' => $flash,
      );

      // render template
      $template = new Template($this->views_path);
      return array($template->render_view('sales-return-view', $template_vars), $controller_vars);
  }

  // sales return list
  public function salesReturnListAction(Request $request) {

    $search_params = $sales_returns_a = [];

    $total_pages = $total_records = $record_count = $page_no = 0;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;
    $page_success = $page_error = '';

    $page_no = 1; 
    $per_page = 100;

    // check for filter variables.
    if(count($request->request->all()) > 0) {
      $search_params = $request->request->all();
    } else {
      $search_params['pageNo'] = $page_no;
      $search_params['perPage'] = $per_page;
      $search_params['fromDate'] = date("01-m-Y");
      $search_params['toDate'] = date("d-m-Y");
      $search_params['locationCode'] = $_SESSION['lc'];
    }

    # ---------- get location codes from api -----------------------
    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
    }

    if((int)$_SESSION['utype'] === 3 || (int)$_SESSION['utype'] === 9) {
      $client_locations = [''=>'All Stores'] + $client_locations;
    }

    // initiate Model.
    $sales_return_model = new SalesReturns;

    // hit API.
    $sales_return_api_call = $sales_return_model->get_all_sales_returns($search_params);
    $api_status = $sales_return_api_call['status'];

    // dump($sales_return_api_call);
    // exit;

    // check api status
    if($api_status) {
      // check whether we got products or not.
      if(count($sales_return_api_call['sales_returns'])>0) {
        $sales_returns_a = $sales_return_api_call['sales_returns'];
        $slno = Utilities::get_slno_start(count($sales_return_api_call['sales_returns']), $per_page, $page_no);
        $to_sl_no = $slno+$per_page;
        $slno++;
        if($page_no<=3) {
          $page_links_to_start = 1;
          $page_links_to_end = 10;
        } else {
          $page_links_to_start = $page_no-3;
          $page_links_to_end = $page_links_to_start+10;            
        }
        if($sales_return_api_call['total_pages']<$page_links_to_end) {
          $page_links_to_end = $sales_return_api_call['total_pages'];
        }
        if($sales_return_api_call['record_count'] < $per_page) {
          $to_sl_no = ($slno+$sales_return_api_call['record_count'])-1;
        }
        $sales_a = $sales_return_api_call['sales_returns'];
        $total_pages = $sales_return_api_call['total_pages'];
        $total_records = $sales_return_api_call['total_records'];
        $record_count = $sales_return_api_call['record_count'];
      } else {
        $page_error = $sales_return_api_call['apierror'];
      }
    } else {
      $page_error = $sales_return_api_call['apierror'];
    }

    // dump($sales_returns_a);
    // exit;

     // prepare form variables.
    $template_vars = array(
      'sales_returns' => $sales_returns_a,       
      'page_error' => $page_error,
      'page_success' => $page_success,
      'total_pages' => $total_pages ,
      'total_records' => $total_records,
      'record_count' =>  $record_count,
      'sl_no' => $slno,
      'to_sl_no' => $to_sl_no,
      'search_params' => $search_params,            
      'page_links_to_start' => $page_links_to_start,
      'page_links_to_end' => $page_links_to_end,
      'current_page' => $page_no,
      'client_locations' => $client_locations,
      'location_ids' => $location_ids,      
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Sales Return Register',
      'icon_name' => 'fa fa-repeat',
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('sales-return-register', $template_vars), $controller_vars);
  }
}