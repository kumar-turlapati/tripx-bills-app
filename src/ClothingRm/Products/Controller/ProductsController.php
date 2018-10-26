<?php 

namespace ClothingRm\Products\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;

use ClothingRm\Products\Model\Products;
use ClothingRm\Taxes\Model\Taxes;

class ProductsController {

  protected $views_path;

  public function __construct() {
    $this->views_path = __DIR__.'/../Views/';
    $this->taxes_model = new Taxes;
  }

  public function createProductService(Request $request) {
    $errors = $taxes = $taxes_a = [];
    $page_error = $page_success = $item_code = '';
    $upp_a = $categories_a = array(''=>'Choose');
    $update_flag=false;
    $submitted_data = $product_details = [];

    $category_name = 'Product / Service';
    $create_url = '/products/create';
    $update_url = '/products/update';
    $list_url = '/products/list';

    # ---------- get location codes from api -----------------------
    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }

    // initiate classes.
    $products_api_call = new Products;
    $flash = new Flash;

    for($i=1;$i<=1000;$i++) {
      $upp_a[$i] = $i;
    }

    $taxes_a = $this->taxes_model->list_taxes();
    if($taxes_a['status'] && count($taxes_a['taxes'])>0 ) {
      $taxes_raw = $taxes_a['taxes'];
      foreach($taxes_a['taxes'] as $tax_details) {
        $taxes[$tax_details['taxCode']] = $tax_details['taxPercent'];
      }
    }

    if($request->get('itemCode') && $request->get('itemCode')!='') {
      $item_code = Utilities::clean_string($request->get('itemCode'));
      $products_api_response = $products_api_call->get_product_details($item_code);
      if($products_api_response['status']) {
        $product_details = $products_api_response['productDetails'];
        $update_flag = true;
      } else {
        $flash->set_flash_message("Invalid product code.",1);
        Utilities::redirect($list_url);
      }
      $page_title = "Update $category_name".(isset($product_details['itemName'])?' - '.$product_details['itemName']:'');
      $btn_label = "Update $category_name";
    } else {
      $btn_label = "Create $category_name";
      $page_title = 'Create Product / Service';
    }

    $categories_a = $categories_a + $products_api_call->get_product_categories();

    if(count($request->request->all()) > 0) {
      $submitted_data = $request->request->all();
      if(count($product_details)>0) {
        $new_product = $products_api_call->updateProduct($request->request->all(), $item_code);             
      } else {
        $new_product = $products_api_call->createProduct($request->request->all());
      }
      $status = $new_product['status'];
      if($status === false) {
        if(isset($new_product['errors'])) {
          $errors     =   $new_product['errors'];
        } elseif(isset($new_product['apierror'])) {
          $page_error =   $new_product['apierror'];
        }
      } elseif($update_flag===false) {
        $page_success   = 'Product/Service information added successfully with code ['.$new_product['itemCode'].']';
        $flash->set_flash_message($page_success);
        Utilities::redirect($create_url);
      } else {
        $page_success   = 'Product/Service information updated successfully';
        $flash->set_flash_message($page_success);
        Utilities::redirect($update_url.'/'.$item_code);
      }
    } else {
      $submitted_data = $product_details;            
    }

    // prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'page_success' => $page_success,
      'status' =>  Constants::$RECORD_STATUS,
      'submitted_data' => $submitted_data,
      'errors' => $errors,
      'btn_label' => $btn_label,
      'item_code' => $item_code,
      'flash_obj' => $flash,
      'mfgs' => array(),
      'categories' => $categories_a,
      'comps' => array(),
      'upp_a' => $upp_a,
      'presc_options_a' => [0 => 'No', 1 => 'Yes'],
      'item_types_a' => ['p' => 'Product', 's' => 'Service'],
      'tax_rates_a' => ['' => 'Choose'] + $taxes,
      'client_locations' => array(''=>'Choose') + $client_locations,
      'default_location' => isset($_SESSION['lc']) ? $_SESSION['lc'] : '',
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,      
    );

    // build variables
    $controller_vars = array(
      'page_title' => $page_title,
      'icon_name' => 'fa fa-tasks',
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('products-create', $template_vars), $controller_vars);
  }

  public function listProductsOrServices(Request $request) {
    $categories = array(''=>'All Categories');

    $products_list = $search_params = $products = $location_ids = $location_codes = [];
    $total_pages = $total_records = $record_count = $page_no = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;
    $page_success = $page_error = '';
    $show_add_link = false;

    $default_location = isset($_SESSION['lc']) ? $_SESSION['lc'] : '';

    $flash = new Flash;        
    $product_api_call = new Products;
    $search_params = [];

    $page_no = !is_null($request->get('pageNo')) ? Utilities::clean_string($request->get('pageNo')) : 1;
    $per_page = !is_null($request->get('perPage')) ? Utilities::clean_string($request->get('perPage')) : 100;
    $ps_name = !is_null($request->get('psName')) ? Utilities::clean_string($request->get('psName')) : '';
    $category = !is_null($request->get('category')) ? Utilities::clean_string($request->get('category')) : '';
    $mfg = !is_null($request->get('mfg')) ? Utilities::clean_string($request->get('mfg')) : '';
    $location_code = $request->get('locationCode')!== null ? Utilities::clean_string($request->get('locationCode')) : $default_location;

    $search_params = [
      'psName' => $ps_name,
      'category' => $category,
      'mfg' => $mfg,
      'locationCode' => $location_code,
      'medname' => $ps_name,
    ];

    # ---------- get location codes from api -----------------
    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }

    $products_list = $product_api_call->get_products($page_no, $per_page, $search_params);
    $api_status = $products_list['status'];
    $client_id = Utilities::get_current_client_id();

    // check api status
    if($api_status) {
      // check whether we got products or not.
      if(count($products_list['products']) >0) {
        $categories = array('' => 'All Categories')+$product_api_call->get_product_categories();
        $slno = Utilities::get_slno_start(count($products_list['products']), $per_page, $page_no);
        $to_sl_no = $slno+$per_page;
        $slno++;
        if($page_no<=3) {
          $page_links_to_start = 1;
          $page_links_to_end = 10;
        } else {
          $page_links_to_start = $page_no-3;
          $page_links_to_end = $page_links_to_start+10;            
        }
        if($products_list['total_pages']<$page_links_to_end) {
          $page_links_to_end = $products_list['total_pages'];
        }
        if($products_list['record_count'] < $per_page) {
          $to_sl_no = ($slno+$products_list['record_count'])-1;
        }
        $products = $products_list['products'];
        $total_pages = $products_list['total_pages'];
        $total_records = $products_list['total_records'];
        $record_count = $products_list['record_count'];
      } else {
        $page_error = $products_list['apierror'];
      }
    } else {
      $page_error = $products_list['apierror'];
    }

    // build variables
    $controller_vars = array(
      'page_title' => 'Products (or) Services',
      'icon_name' => 'fa fa-tasks',
    );
    $template_vars = array(
      'products' => $products,
      'categories' => $categories,
      'total_pages' => $total_pages ,
      'total_records' => $total_records,
      'record_count' =>  $record_count,
      'sl_no' => $slno,
      'to_sl_no' => $to_sl_no,
      'page_links_to_start' => $page_links_to_start,
      'page_links_to_end' => $page_links_to_end,
      'current_page' => $page_no,
      'search_params' => $search_params,
      'page_error' => $page_error,
      'page_success' => $page_success,
      'flash_obj'  => $flash,
      'client_locations' => array(''=>'All Stores') + $client_locations,
      'default_location' => $default_location,
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,      
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('products-list', $template_vars), $controller_vars);        
  }
}