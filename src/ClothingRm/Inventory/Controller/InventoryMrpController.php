<?php 

namespace ClothingRm\Inventory\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;

use ClothingRm\Inventory\Model\Inventory;
use ClothingRm\Inventory\Model\InventoryMrp;
use ClothingRm\Products\Model\Products;

class InventoryMrpController {

  protected $views_path;

  public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->inven_api = new Inventory;
    $this->inven_mrp_api = new InventoryMrp;
    $this->product_api_call = new Products;    
    $this->flash = new Flash;
  }

  public function changeMrpAction(Request $request = null) {
    // pagination variables.
    $total_pages = $total_records = $record_count = $page_no = 0;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;
    $page_success = $page_error = '';
    
    $client_locations = $location_ids = $location_codes = $items_a = [];
    $default_location = isset($_SESSION['lc']) ? $_SESSION['lc'] : '';    

    // get location codes from api
    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }

    if(is_null($request->get('locationCode'))) {
      $search_params['locationCode'] = $default_location;
    } else {
      $search_params['locationCode'] = Utilities::clean_string($request->get('locationCode'));
    }    
    if(is_null($request->get('pageNo'))) {
      $search_params['pageNo'] = $page_no = 1;
    } else {
      $search_params['pageNo'] = $page_no = (int)$request->get('pageNo');
    }
    if(is_null($request->get('perPage'))) {
      $search_params['perPage'] = $per_page = 100;
    } else {
      $search_params['perPage'] = $per_page = (int)$request->get('perPage');
    }
    if(is_null($request->get('category'))) {
      $search_params['category'] = '';
    } else {
      $search_params['category'] = Utilities::clean_string($request->get('category'));
    }
    if(is_null($request->get('brandName'))) {
      $search_params['brandName'] = '';
    } else {
      $search_params['brandName'] = Utilities::clean_string($request->get('brandName'));
    }
    if(is_null($request->get('psName'))) {
      $search_params['psName'] = '';
    } else {
      $search_params['psName'] = Utilities::clean_string($request->get('psName'));
    }

    $search_params['onlyPOItems'] = 1;

    // dump($search_params);
    // exit;

    // get categories
    $categories = array('' => 'All Categories')+$this->product_api_call->get_product_categories($search_params['locationCode']);    

    // hit API.
    $items_api_call = $this->inven_api->get_available_qtys($search_params);
    $api_status = $items_api_call['status'];

    // dump($items_api_call);
    // exit;

    // check api status
    if($api_status) {
      if(count($items_api_call['items'])>0) {
        $slno = Utilities::get_slno_start(count($items_api_call['items']), $per_page, $page_no);
        $to_sl_no = $slno + $per_page;
        $slno++;
        if($page_no<=3) {
          $page_links_to_start = 1;
          $page_links_to_end = 10;
        } else {
          $page_links_to_start = $page_no-3;
          $page_links_to_end = $page_links_to_start+10;
        }
        if($items_api_call['total_pages']<$page_links_to_end) {
          $page_links_to_end = $items_api_call['total_pages'];
        }
        if($items_api_call['total_records'] < $per_page) {
          $to_sl_no = ($slno + $items_api_call['total_records'])-1;
        }

        $items_a = $items_api_call['items'];
        $total_pages = $items_api_call['total_pages'];
        $total_records = $items_api_call['total_records'];
        $record_count = $items_api_call['total_records'];
      } else {
        $page_error = $items_api_call['apierror'];
      }
    } else {
      $this->flash->set_flash_message($items_api_call['apierror'], 1);
    }    

    // prepare form variables.
    $template_vars = array(
      'items' => $items_a,
      'total_pages' => $total_pages,
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
      'location_codes' => $location_codes,
      'flash_obj' => $this->flash,
      'categories' => $categories,
      'default_location' => $default_location,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Update MRP in Purchase Orders',
      'icon_name' => 'fa fa-tasks',
    );

    // render template
    return array($this->template->render_view('change-mrp', $template_vars),$controller_vars);
  }

  public function mrpRegisterAction(Request $request = null) {
    // pagination variables.
    $total_pages = $total_records = $record_count = $page_no = 0;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;
    $page_success = $page_error = '';
    
    $client_locations = $location_ids = $location_codes = $items_a = [];
    $default_location = isset($_SESSION['lc']) ? $_SESSION['lc'] : '';    

    // get location codes from api
    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }

    if(is_null($request->get('locationCode'))) {
      $search_params['locationCode'] = $default_location;
    } else {
      $search_params['locationCode'] = Utilities::clean_string($request->get('locationCode'));
    }    
    if(is_null($request->get('pageNo'))) {
      $search_params['pageNo'] = $page_no = 1;
    } else {
      $search_params['pageNo'] = $page_no = (int)$request->get('pageNo');
    }
    if(is_null($request->get('perPage'))) {
      $search_params['perPage'] = $per_page = 100;
    } else {
      $search_params['perPage'] = $per_page = (int)$request->get('perPage');
    }
    if(is_null($request->get('category'))) {
      $search_params['category'] = '';
    } else {
      $search_params['category'] = Utilities::clean_string($request->get('category'));
    }
    if(is_null($request->get('brandName'))) {
      $search_params['brandName'] = '';
    } else {
      $search_params['brandName'] = Utilities::clean_string($request->get('brandName'));
    }
    if(is_null($request->get('psName'))) {
      $search_params['psName'] = '';
    } else {
      $search_params['psName'] = Utilities::clean_string($request->get('psName'));
    }

    // get categories
    $categories = array('' => 'All Categories')+$this->product_api_call->get_product_categories($search_params['locationCode']);    

    // hit API.
    $items_api_call = $this->inven_api->changed_mrp_register($search_params);
    $api_status = $items_api_call['status'];

    // dump($items_api_call);
    // exit;

    // check api status
    if($api_status) {
      if(count($items_api_call['items'])>0) {
        $slno = Utilities::get_slno_start(count($items_api_call['items']), $per_page, $page_no);
        $to_sl_no = $slno + $per_page;
        $slno++;
        if($page_no<=3) {
          $page_links_to_start = 1;
          $page_links_to_end = 10;
        } else {
          $page_links_to_start = $page_no-3;
          $page_links_to_end = $page_links_to_start+10;
        }
        if($items_api_call['total_pages']<$page_links_to_end) {
          $page_links_to_end = $items_api_call['total_pages'];
        }
        if($items_api_call['total_records'] < $per_page) {
          $to_sl_no = ($slno + $items_api_call['total_records'])-1;
        }

        $items_a = $items_api_call['items'];
        $total_pages = $items_api_call['total_pages'];
        $total_records = $items_api_call['total_records'];
        $record_count = $items_api_call['total_records'];
      } else {
        $page_error = $items_api_call['apierror'];
      }
    } else {
      $this->flash->set_flash_message($items_api_call['apierror'], 1);
    }    

    // prepare form variables.
    $template_vars = array(
      'items' => $items_a,
      'total_pages' => $total_pages,
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
      'location_codes' => $location_codes,
      'flash_obj' => $this->flash,
      'categories' => $categories,
      'default_location' => $default_location,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Changed MRP Register',
      'icon_name' => 'fa fa-edit',
    );

    // render template
    return array($this->template->render_view('changed-mrp-register', $template_vars),$controller_vars);
  }

}