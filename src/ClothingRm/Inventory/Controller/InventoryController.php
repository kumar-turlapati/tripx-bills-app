<?php 

namespace ClothingRm\Inventory\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;

use ClothingRm\Inventory\Model\Inventory;

class InventoryController
{
  protected $views_path;

  public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->inven_api = new Inventory;
    $this->flash = new Flash;
  }

  public function addStockAdjustment(Request $request) {

    $page_error = $page_success = '';
    $errors = $submitted_data = [];

    $api_response = $this->inven_api->get_inventory_adj_reasons();
    if($api_response['status']===true) {
      $adj_reasons = array(''=>'Choose') + $api_response['results'];
    } else {
      $adj_reasons = array(''=>'Choose');
    }

    # ---------- get location codes from api -----------------------
    $client_locations = Utilities::get_client_locations();    

    if(count($request->request->all()) > 0) {
      $params = $request->request->all();
      $api_response = $this->inven_api->add_stock_adjustment($params);
      $status = $api_response['status'];
      if($status === false) {
        if(isset($api_response['errors'])) {
          $errors = $api_response['errors'];
        } elseif(isset($api_response['apierror'])) {
          $page_error = $api_response['apierror'];
        }
        $submitted_data = $params;
      } else {
        $adj_code = $api_response['results']['adjCode'];
        $this->flash->set_flash_message('Adjustment entry added successfully with code [ '.$adj_code.' ]');
        Utilities::redirect('/inventory/stock-adjustment');
      }
    }

    // template variables.
    $template_vars = array(
      'adj_reasons' => $adj_reasons,
      'errors' => $errors,
      'page_error' => $page_error,
      'page_success' => $page_success,
      'submitted_data' => $submitted_data,
      'client_locations' => array(''=>'Choose') + $client_locations,
      'default_location' => isset($_SESSION['lc']) ? $_SESSION['lc'] : '',
    );

    // controller variables.
    $controller_vars = array(
      'page_title' => 'Inventory - Stock Adjustment',
      'icon_name' => 'fa fa-adjust',
    );         

    // render template
    return array($this->template->render_view('inventory-adj-add', $template_vars),$controller_vars);
  }

  public function deleteStockAdjustment(Request $request) {
    $adj_code = !is_null($request->get('adjCode')) ? Utilities::clean_string($request->get('adjCode')) : '';
    
    if($request->get('adjDateFrom') && $request->get('adjDateFrom') !== '') {
      $search_params['adjDateFrom'] = Utilities::clean_string($request->get('adjDateFrom'));
    } else {
      $search_params['adjDateFrom'] = '01-'.date('m').'-'.date("Y");;
    }
    if($request->get('adjDateTo') && $request->get('adjDateTo') !== '') {
      $search_params['adjDateTo'] = Utilities::clean_string($request->get('adjDateTo'));
    } else {
      $search_params['adjDateTo'] = date("d-m-Y");
    }
    if($request->get('locationCode') && $request->get('locationCode') !== '') {
      $search_params['locationCode'] = Utilities::clean_string($request->get('locationCode'));
    } else {
      $search_params['locationCode'] = isset($_SESSION['lc']) ? $_SESSION['lc'] : '';
    }

    $redirect_url = '/inventory/stock-adjustments-list?'.http_build_query($search_params);

    if(ctype_alnum($adj_code)) {
      $api_response = $this->inven_api->delete_stock_adjustment($adj_code);
      $status = $api_response['status'];
      if($status===false) {
        $this->flash->set_flash_message('No entry found or adjustment entry already deleted.', 1);
      } else {
        $this->flash->set_flash_message("Stock adjustment entry with adjustment code `$adj_code` deleted successfully");
      }
    } else {
      $this->flash->set_flash_message('Invalid adjustment entry code.', 1);
    }

    Utilities::redirect($redirect_url);    
  }

  public function getAllStockAdjustments(Request $request) {
    $items_list = $search_params = $items = [];

    $total_pages = $total_records = $record_count = $page_no = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;
    $page_success = $page_error = '';

    # ---------- get location codes from api -----------------------
    $client_locations = Utilities::get_client_locations();
    $default_location = isset($_SESSION['lc']) ? $_SESSION['lc'] : '';

    if($request->get('adjDateFrom') && $request->get('adjDateFrom') !== '') {
      $search_params['adjDateFrom'] = Utilities::clean_string($request->get('adjDateFrom'));
    } else {
      $search_params['adjDateFrom'] = '01-'.date('m').'-'.date("Y");
    }
    if($request->get('adjDateTo') && $request->get('adjDateTo') !== '') {
      $search_params['adjDateTo'] = Utilities::clean_string($request->get('adjDateTo'));
    } else {
      $search_params['adjDateTo'] = date("d-m-Y");
    }
    if($request->get('locationCode') && $request->get('locationCode') !== '') {
      $search_params['locationCode'] = Utilities::clean_string($request->get('locationCode'));
    } else {
      $search_params['locationCode'] = $default_location;
    }    
    if($request->get('pageNo') && is_numeric($request->get('pageNo'))) {
      $search_params['pageNo'] = Utilities::clean_string($request->get('pageNo'));
    } else {
      $search_params['pageNo'] = 1;
    }
    if($request->get('perPage') && is_numeric($request->get('perPage'))) {
      $search_params['perPage'] = Utilities::clean_string($request->get('perPage'));
    } else {
      $search_params['perPage'] = 100;
    }
 
    $per_page = $search_params['perPage'];
    $page_no = $search_params['pageNo'];

    $api_response = $this->inven_api->get_inventory_adj_reasons();
    if($api_response['status']===true) {
      $adj_reasons = array(''=>'Choose')+$api_response['results'];
    } else {
      $adj_reasons = array(''=>'Choose');
    }

    $items_list = $this->inven_api->get_inventory_adj_entries($search_params);
    $api_status = $items_list['status'];

    # check api status
    if($api_status) {
      # check whether we got products or not.
      if( count($items_list['results']['adjItems']) > 0) {
        $slno = Utilities::get_slno_start(count($items_list['results']['adjItems']),$per_page,$page_no);
        $to_sl_no = $slno+$per_page;
        $slno++;

        if($page_no<=3) {
          $page_links_to_start = 1;
          $page_links_to_end = 10;
        } else {
          $page_links_to_start = $page_no-3;
          $page_links_to_end = $page_links_to_start + 10;
        }

        if($items_list['results']['total_pages']<$page_links_to_end) {
          $page_links_to_end = $items_list['results']['total_pages'];
        }
        if($items_list['results']['total_records'] < $per_page) {
          $to_sl_no = ($slno+$items_list['results']['total_records'])-1;
        }

        $items = $items_list['results']['adjItems'];
        $total_pages = $items_list['results']['total_pages'];
        $total_records = $items_list['results']['total_records'];
        $record_count = $items_list['results']['this_page'];
      } else {
        $page_error = 'Unable to fetch data';
      }

    } else {
      $page_error = $items_list['apierror'];
    }        

    $template_vars = array(
      'page_error' => $page_error,
      'page_success' => $page_success,
      'items' => $items,
      'total_pages' => $total_pages ,
      'total_records' => $total_records,
      'record_count' =>  $record_count,
      'sl_no' => $slno,
      'to_sl_no' => $to_sl_no,
      'search_params' => $search_params,            
      'page_links_to_start' => $page_links_to_start,
      'page_links_to_end' => $page_links_to_end,
      'current_page' => $page_no,
      'adj_reasons' => $adj_reasons,
      'client_locations' => array(''=>'Choose') + $client_locations,
      'default_location' => $search_params['locationCode'],      
    );

    $controller_vars = array(
      'page_title' => 'Inventory - Stock Adjustment',
      'icon_name' => 'fa fa-adjust',
    );

    // render template
    return array($this->template->render_view('inventory-adj-list', $template_vars),$controller_vars);
  }

  public function availableQtyList(Request $request) {

      $items_list = $search_params = $items = [];
      $client_locations = $location_ids = [];

      $total_pages = $total_records = $record_count = $page_no = 0 ;
      $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;
      $page_success = $page_error = '';

      $inventory_api_call = new Inventory;

      # ---------- get location codes from api -----------------------
      $client_locations = Utilities::get_client_locations(true);
      foreach($client_locations as $location_key => $location_value) {
          $location_key_a = explode('`', $location_key);
          $location_ids[$location_key_a[1]] = $location_value;
      }

      if(count($request->request->all()) > 0) {
          $search_params = $request->request->all();
      } else {
          $search_params['medName'] = !is_null($request->get('medName')) ? Utilities::clean_string($request->get('medName')) : '';
          $search_params['locationCode'] = !is_null($request->get('locationCode')) ? Utilities::clean_string($request->get('locationCode')) : '';
          $search_params['pageNo'] = !is_null($request->get('pageNo')) ? Utilities::clean_string($request->get('pageNo')) : 1;
          $search_params['perPage'] = !is_null($request->get('perPage')) ? Utilities::clean_string($request->get('perPage')) : 100;
      }

      $items_list = $this->inven_api->get_available_qtys($search_params);

      // dump($items_list);
      // dump($search_params);

      $api_status = $items_list['status'];
      $per_page = isset($search_params['perPage']) ? $search_params['perPage'] : 100;
      $page_no = isset($search_params['pageNo']) ? $search_params['pageNo'] : 1;

      # check api status
      if($api_status) {

          # check whether we got products or not.
          if( count($items_list['items']) > 0) {
              $slno = Utilities::get_slno_start(count($items_list['items']),$per_page,$page_no);
              $to_sl_no = $slno+$per_page;
              $slno++;

              if($page_no<=3) {
                  $page_links_to_start = 1;
                  $page_links_to_end = 10;
              } else {
                  $page_links_to_start = $page_no-3;
                  $page_links_to_end = $page_links_to_start+10;            
              }

              if($items_list['total_pages']<$page_links_to_end) {
                  $page_links_to_end = $items_list['total_pages'];
              }

              if($items_list['record_count'] < $per_page) {
                  $to_sl_no = ($slno+$items_list['record_count'])-1;
              }

              $items = $items_list['items'];
              $total_pages = $items_list['total_pages'];
              $total_records = $items_list['total_records'];
              $record_count = $items_list['record_count'];
          } else {
              $page_error = 'Unable to fetch data';
          }

      } else {
          $page_error = $items_list['apierror'];
      }

       // prepare form variables.
      $template_vars = array(
          'page_error' => $page_error,
          'page_success' => $page_success,
          'items' => $items,
          'total_pages' => $total_pages ,
          'total_records' => $total_records,
          'record_count' =>  $record_count,
          'sl_no' => $slno,
          'to_sl_no' => $to_sl_no,
          'search_params' => $search_params,            
          'page_links_to_start' => $page_links_to_start,
          'page_links_to_end' => $page_links_to_end,
          'current_page' => $page_no,
          'client_locations' => ['' => 'All Stores'] + $client_locations,
          'location_ids' => $location_ids,
      );

      // build variables
      $controller_vars = array(
          'page_title' => 'Stock in Hand',
          'icon_name' => 'fa fa-database',
      );

      // render template
      return array($this->template->render_view('batch-qtys-list', $template_vars),$controller_vars);      
  }
/*  
  public function searchItem(Request $request) {
      
      $search_params = $item_details = array();

      $inventory_api_call = new Inventory;
      $flash = new Flash;        

      if(count($request->request->all()) > 0) {
          $search_params = $request->request->all();
          $api_response = $inventory_api_call->get_inventory_item_details($search_params);
          if($api_response['status']) {
              $item_details = $api_response['item_details'];
          } else {
              $flash->set_flash_message('No details are available.');
              Utilities::redirect('/inventory/search-products');                
          }
      }

      // prepare form variables.
      $template_vars = array(
          'item_details' => $item_details,
          'search_params' => $search_params
      );

      // build variables
      $controller_vars = array(
          'page_title' => 'Inventory - Product Search',
          'icon_name' => 'fa fa-database',
      );

      // render template
      $template = new Template($this->views_path);
      return array($template->render_view('search-item',$template_vars),$controller_vars);
  }*/

/*  public function itemThresholdAdd(Request $request) {

      $page_error = $page_success = '';
      $errors = $submitted_data = array();

      $inven_api = new Inventory;
      $flash = new Flash;

      if(count($request->request->all())>0) {
          $params = $request->request->all();
          $submitted_data['itemName'] = Utilities::clean_string($params['itemName']);
          $submitted_data['thrQty'] =  Utilities::clean_string($params['thrQty']);
          $submitted_data['supplierName'] = Utilities::clean_string($params['supplierName']);

          # hit api
          $api_response = $inven_api->add_threshold_qty($submitted_data);
          $status = $api_response['status'];
          if($status === false) {
            if(isset($api_response['errors'])) {
              $errors = $api_response['errors'];
            } elseif(isset($api_response['apierror'])) {
              $page_error = $api_response['apierror'];
            }
          } else {
              $flash->set_flash_message('Threshold qty. added successfully with code [ '.$api_response['thrCode'].' ]');
              Utilities::redirect('/inventory/item-threshold-add');              
          }
      }

      # prepare template
      $template_vars = array(
        'page_error' => $page_error,
        'page_success' => $page_success,
        'submitted_data' => $submitted_data,
        'page_error' => $page_error,
        'page_success' => $page_success,
        'errors' => $errors,
      );

      $controller_vars = array(
        'page_title' => 'Inventory - Threshold',
        'icon_name' => 'fa fa-bullhorn',
      );

      # render template
      $template = new Template($this->views_path);
      return array($template->render_view('add-threshold-qty',$template_vars),$controller_vars);
  }

  public function itemThresholdUpdate(Request $request) {

      $page_error = $page_success = $thr_code = '';
      $errors = $submitted_data = array();

      $inven_api = new Inventory;
      $flash = new Flash;

      if(count($request->request->all())>0) {
          $params = $request->request->all();
          $submitted_data['itemName'] = Utilities::clean_string($params['itemName']);
          $submitted_data['thrQty'] =  Utilities::clean_string($params['thrQty']);
          $submitted_data['supplierName'] = Utilities::clean_string($params['supplierName']);

          # hit api
          $api_response = $inven_api->update_threshold_qty($submitted_data,$params['thrCode']);
          $status = $api_response['status'];
          if($status === false) {
            if(isset($api_response['errors'])) {
              $errors = $api_response['errors'];
            } elseif(isset($api_response['apierror'])) {
              $page_error = $api_response['apierror'];
            }
          } else {
              $flash->set_flash_message('Threshold quantity updated successfully for item [ '.$params['itemName'].' ]');
              Utilities::redirect('/inventory/item-threshold-list');              
          }
      } else {
          $thr_code = $request->get('thrCode');
          $thr_details = $inven_api->get_threshold_itemqty_details($thr_code);
          if($thr_details['status']===true && count($thr_details['thrDetails'])>0) {
              $submitted_data = $thr_details['thrDetails'];
              $thr_code = $submitted_data['thrCode'];
          }
      }

      # prepare template
      $template_vars = array(
        'page_error' => $page_error,
        'page_success' => $page_success,
        'submitted_data' => $submitted_data,
        'page_error' => $page_error,
        'page_success' => $page_success,
        'errors' => $errors,
        'thr_code' => $thr_code,
      );

      $controller_vars = array(
        'page_title' => 'Inventory - Threshold',
        'icon_name' => 'fa fa-bullhorn',
      );

      # render template
      $template = new Template($this->views_path);
      return array($template->render_view('update-threshold-qty',$template_vars),$controller_vars);
  }    

  public function itemThresholdList(Request $request) {

    $items = array();
    $total_pages = $total_records = $record_count = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;
    $page_error = $page_success = '';

    $inven_api = new Inventory;
    $flash = new Flash;

    if($request->get('pageNo') && is_numeric($request->get('pageNo'))) {
      $page_no = Utilities::clean_string($request->get('pageNo'));
    } else {
      $page_no = 1;
    }

    $per_page = 100;

    # hit api
    $api_response = $inven_api->list_threshold_qtys();
    if($api_response['status']) {
      $items = $api_response['results']['results'];
      $slno = Utilities::get_slno_start(count($items),$per_page,$page_no);
      $to_sl_no = $slno+$per_page;
      $slno++;

      if($page_no<=3) {
        $page_links_to_start = 1;
        $page_links_to_end = 10;
      } else {
        $page_links_to_start = $page_no-3;
        $page_links_to_end = $page_links_to_start+10;            
      }

      if($api_response['results']['total_pages']<$page_links_to_end) {
        $page_links_to_end = $api_response['results']['total_pages'];
      }

      if($api_response['results']['this_page'] < $per_page) {
        $to_sl_no = ($slno+$api_response['results']['this_page'])-1;
      }

      $total_pages = $api_response['results']['total_pages'];
      $total_records = $api_response['results']['total_records'];
      $record_count = $api_response['results']['this_page'];
    } else {
      $page_error = 'No threshold item qtys. are available';
    }      

    # prepare template
    $template_vars = array(
      'items' => $items,
      'total_pages' => $total_pages ,
      'total_records' => $total_records,
      'record_count' =>  $record_count,
      'sl_no' => $slno,
      'to_sl_no' => $to_sl_no,
      'page_links_to_start' => $page_links_to_start,
      'page_links_to_end' => $page_links_to_end,
      'current_page' => $page_no,
      'page_error' => $page_error,
      'page_success' => $page_success,
    );

    $controller_vars = array(
      'page_title' => 'Inventory - Threshold',
      'icon_name' => 'fa fa-angle-double-up',
    );

    # render template
    $template = new Template($this->views_path);
    return array($template->render_view('list-threshold-qty',$template_vars),$controller_vars);      
  }

  public function trackItem(Request $request) {
    $page_error = $page_success = $item_name = '';
    $errors = $submitted_data = $item_details = $total_trans = array();

    $inven_api = new Inventory;
    $flash = new Flash;

    if(count($request->request->all())>0) {
      $params = $request->request->all();
      $submitted_data['itemName'] = Utilities::clean_string($params['itemName']);

      # hit api
      $api_response = $inven_api->track_item($submitted_data);
      $status = $api_response['status'];

      // echo '<pre>';
      // print_r($api_response);
      // echo '</pre>';
      // exit;

      if($status === false) {
        if(isset($api_response['errors'])) {
          $errors = $api_response['errors'];
        } elseif(isset($api_response['apierror'])) {
          $page_error = $api_response['apierror'];
        }
      } else {
          $total_trans = $api_response['items'];
          $item_name = ' [ '.$submitted_data['itemName'].' ]';
      }
    }

    # prepare template
    $template_vars = array(
      'total_trans' => $total_trans,
      'submitted_data' => $submitted_data,
    );

    $controller_vars = array(
      'page_title' => 'Inventory - Item Track'.$item_name,
      'icon_name' => 'fa fa-angle-double-up',
    );

    # render template
    $template = new Template($this->views_path);
    return array($template->render_view('item-track',$template_vars),$controller_vars);      
  }*/
}