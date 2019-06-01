<?php 

namespace ClothingRm\DiscountManager\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;

use ClothingRm\Products\Model\Products;
use ClothingRm\DiscountManager\Model\DiscountManager;

class DiscountManagerController
{
  private $template;
  private $products_model, $sc_model;

  public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->flash = new Flash;
    $this->products_model = new Products;
    $this->dm_model = new DiscountManager;
  }  

  // discount manager
  public function discountManager(Request $request) {
    #-------------------------------------------------------------------------------
    # Initialize variables

    $total_pages = $total_records = $record_count = $page_no = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;

    $form_errors = $form_data = $products = [];
    $location_ids = $location_codes = $categories_a = $search_params = [];
    $api_error = '';

    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }

    // process get parameters
    $page_no = !is_null($request->get('pageNo')) ? Utilities::clean_string($request->get('pageNo')) : 1;
    $per_page = !is_null($request->get('perPage')) ? Utilities::clean_string($request->get('perPage')) : 100;
    $item_name = !is_null($request->get('itemName')) ? Utilities::clean_string($request->get('itemName')) : '';
    $category = !is_null($request->get('category')) ? Utilities::clean_string($request->get('category')) : '';
    $mfg = !is_null($request->get('mfg')) ? Utilities::clean_string($request->get('mfg')) : '';
    $location_code = !is_null($request->get('locationCode')) ? Utilities::clean_string($request->get('locationCode')) : $_SESSION['lc'];

    $search_params = [
      'locationCode' => $location_code,
      'category' => $category,
      'mfg' => $mfg,
      'itemName' => $item_name,
      'pageNo' => $page_no,
      'perPage' => $per_page,
    ];

    $categories_a = $categories_a + $this->products_model->get_product_categories($location_code);
    # end of initializing variables
    #-------------------------------------------------------------------------------

    // hit api.
    $products_list = $this->dm_model->discount_manager($search_params);
    $api_status = $products_list['status'];
    // dump($products_list['response']);
    if($api_status) {
      // check whether we got products or not.
      if(count($products_list['response']['items']) >0) {
        $slno = Utilities::get_slno_start(count($products_list['response']['items']), $per_page, $page_no);
        $to_sl_no = $slno+$per_page;
        $slno++;
        // dump('sl no....'.$slno);
        if($page_no<=3) {
          $page_links_to_start = 1;
          $page_links_to_end = 10;
        } else {
          $page_links_to_start = $page_no-3;
          $page_links_to_end = $page_links_to_start+10;            
        }
        if($products_list['response']['total_pages']<$page_links_to_end) {
          $page_links_to_end = $products_list['response']['total_pages'];
        }
        if($products_list['response']['total_records'] < $per_page) {
          $to_sl_no = ($slno+$products_list['response']['total_records'])-1;
        }
        $products = $products_list['response']['items'];
        $total_pages = $products_list['response']['total_pages'];
        $total_records = $products_list['response']['total_records'];
        $record_count = $products_list['response']['total_records'];
      } else {
        $page_error = $products_list['apierror'];
      }
    } else {
      $page_error = $products_list['apierror'];
    }    

    // theme variables.
    $controller_vars = array(
      'page_title' => 'Discount Manager',
      'icon_name' => 'fa fa-hand-peace-o',
    );

    // template variables
    $template_vars = array(
      'form_errors' => $form_errors,
      'form_data' => $form_data,
      'api_error' => $api_error,
      'client_locations' => array(''=>'All Stores') + $client_locations,
      'default_location' => $location_code,
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,
      'categories' => ['' => 'All Categories'] + $categories_a,
      'products' => $products,
      'total_pages' => $total_pages ,
      'total_records' => $total_records,
      'record_count' =>  $record_count,
      'sl_no' => $slno,
      'to_sl_no' => $to_sl_no,
      'page_links_to_start' => $page_links_to_start,
      'page_links_to_end' => $page_links_to_end,
      'current_page' => $page_no,
      'search_params' => $search_params,
    );

    return array($this->template->render_view('discount-manager',$template_vars),$controller_vars);
  }

  // delete discount entry
  public function deleteDiscountEntry(Request $request) {
    $item_name = !is_null($request->get('in')) ? Utilities::clean_string($request->get('in')) : '';
    $location_code = !is_null($request->get('locationCode')) ? Utilities::clean_string($request->get('locationCode')) : '';
    $lot_no = !is_null($request->get('lotNo')) ? Utilities::clean_string($request->get('lotNo')) : '';
    $params = [];
    if($item_name !== '' && $location_code !== '' && $lot_no !== '') {
      $params['locationCode'] = $location_code;
      $params['itemName'] = $item_name;
      $params['lotNo'] = $lot_no;
      $api_response = $this->dm_model->delete_discount_entry($params);
      if($api_response['status']) {
        $this->flash->set_flash_message('<i class="fa fa-check" aria-hidden="true"></i> Discount entry deleted successfully.');
      } else {
        $page_error = $api_response['apierror'];
        $this->flash->set_flash_message($page_error, 1);
      }
    } else {
      $this->flash->set_flash_message('Invalid input', 1);
    }
    Utilities::redirect('/discount-manager');
  }
} // end of class.