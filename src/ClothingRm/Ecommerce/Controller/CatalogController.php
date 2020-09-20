<?php 

namespace ClothingRm\Ecommerce\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;
use Atawa\Config\Config;

use ClothingRm\Ecommerce\Model\Catalog;
use ClothingRm\Products\Model\Products;
use ClothingRm\Ecommerce\Model\Category;

class CatalogController {

  private $template, $flash, $catalog_model, $products_model;

  public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->flash = new Flash;
    $this->catalog_model = new Catalog;
    $this->products_model = new Products;
    $this->category_model = new Category;
  }  

  // create gallery
  public function createCatalog(Request $request) {
    $form_errors = $submitted_data = [];
    $client_locations = Utilities::get_client_locations();

    if( count($request->request->all())>0 ) {
      $submitted_data = $request->request->all();
      $form_validation = $this->_validate_form_data($submitted_data);
      if($form_validation['status']) {
        $cleaned_params = $form_validation['cleaned_params'];
        $result = $this->catalog_model->catalog_create($cleaned_params);
        if($result['status']) {
          $this->flash->set_flash_message('<i class="fa fa-check aria-hidden="true"></i>&nbsp;Catalog created successfully with code [ '.$result['catalogCode'].' ]');
          Utilities::redirect('/catalog/create');
        } else {
          $page_error = $result['apierror'];
          $this->flash->set_flash_message($page_error, 1);
        }
      } else {
        $form_errors = $form_validation['errors'];
      }
    }

    $cats_and_subcats = $this->get_categories();
    $categories = isset($cats_and_subcats['categories']) ? $cats_and_subcats['categories'] : [];
    $subcategories = isset($cats_and_subcats['subcategories']) ? $cats_and_subcats['subcategories'] : [];

    // theme variables
    $controller_vars = array(
      'page_title' => 'Create Catalog',
      'icon_name' => 'fa fa-briefcase',
    );

    // template variables
    $template_vars = array(
      'form_errors' => $form_errors,
      'form_data' => $submitted_data,
      'categories' => ["0" => 'Choose']+$categories,
      'subcategories' => ["0" => 'Choose']+$subcategories,
    );

    return array($this->template->render_view('catalog-create',$template_vars),$controller_vars);
  }

  // update gallery
  public function updateCatalog(Request $request) {
    $form_errors = $submitted_data = $existing_catalog_details = [];

    $catalog_code = !is_null($request->get('catalogCode')) ? Utilities::clean_string($request->get('catalogCode')) : '';
    if($catalog_code === '') {
      $this->flash->set_flash_message('Invalid Catalog');
      Utilities::redirect('/catalog/list');
    }

    $catalog_details_response = $this->catalog_model->get_catalog_details($catalog_code);
    if(is_array($catalog_details_response) && $catalog_details_response['status']) {
      $existing_catalog_details = $catalog_details_response['catalogDetails'];
    } else {
      $this->flash->set_flash_message($catalog_details_response['apierror'], 1);
      Utilities::redirect('/catalog/list');
    }

    if( count($request->request->all())>0 ) {
      $submitted_data = $request->request->all();
      $form_validation = $this->_validate_form_data($submitted_data);
      if($form_validation['status']) {
        $cleaned_params = $form_validation['cleaned_params'];
        $result = $this->catalog_model->catalog_update($cleaned_params, $catalog_code);
        if($result['status']) {
          $this->flash->set_flash_message('<i class="fa fa-check aria-hidden="true"></i>&nbsp;Catalog updated successfully.');
          Utilities::redirect('/catalog/list');
        } else {
          $this->flash->set_flash_message($result['apierror'], 1);
        }
      } else {
        $form_errors = $form_validation['errors'];
      }
    }

    $cats_and_subcats = $this->get_categories();
    $categories = isset($cats_and_subcats['categories']) ? $cats_and_subcats['categories'] : [];
    $subcategories = isset($cats_and_subcats['subcategories']) ? $cats_and_subcats['subcategories'] : [];

    // theme variables
    $controller_vars = array(
      'page_title' => 'Update Catalog',
      'icon_name' => 'fa fa-briefcase',
    );

    // template variables
    $template_vars = array(
      'form_errors' => $form_errors,
      'form_data' => $submitted_data,
      'existing_catalog_details' => $existing_catalog_details,
      'categories' => ["0" => 'Choose']+$categories,
      'subcategories' => ["0" => 'Choose']+$subcategories,
    );

    return array($this->template->render_view('catalog-update',$template_vars),$controller_vars);
  }

  // delete catalog
  public function deleteCatalog(Request $request) {
    $catalog_code = !is_null($request->get('catalogCode')) ? Utilities::clean_string($request->get('catalogCode')) : '';
    if($catalog_code === '') {
      $this->flash->set_flash_message('Invalid Catalog');
      Utilities::redirect('/catalog/list');
    }

    $catalog_details_response = $this->catalog_model->get_catalog_details($catalog_code);
    if(is_array($catalog_details_response) && $catalog_details_response['status']) {
      $existing_catalog_details = $catalog_details_response['catalogDetails'];
    } else {
      $this->flash->set_flash_message($catalog_details_response['apierror'], 1);
      Utilities::redirect('/catalog/list');
    }

    $result = $this->catalog_model->catalog_delete($catalog_code);
    if($result['status']) {
      $this->flash->set_flash_message('<i class="fa fa-times aria-hidden="true"></i>&nbsp;Catalog deleted successfully.');
      Utilities::redirect('/catalog/list');
    } else {
      $this->flash->set_flash_message($result['apierror'], 1);
    }
  }

  // catalogs list
  public function catalogsList(Request $request) {

    $catalogs_list = $catalogs = $search_params = [];

    $total_pages = $total_records = $record_count = $page_no = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;
    $page_success = $page_error = '';

    $page_no = !is_null($request->get('pageNo')) ? Utilities::clean_string($request->get('pageNo')) : 1;
    $per_page = !is_null($request->get('perPage')) ? Utilities::clean_string($request->get('perPage')) : 100;

    $search_params = [
      'pageNo' => $page_no,
      'perPage' => $per_page,
    ];

    $catalogs_list = $this->catalog_model->catalogs_list($search_params);
    $api_status = $catalogs_list['status'];
    // check api status
    if($api_status) {
      // check whether we got products or not.
      if(count($catalogs_list['catalogs']['catalogs']) >0) {
        $slno = Utilities::get_slno_start(count($catalogs_list['catalogs']['catalogs']), $per_page, $page_no);
        $to_sl_no = $slno+$per_page;
        $slno++;
        if($page_no<=3) {
          $page_links_to_start = 1;
          $page_links_to_end = 10;
        } else {
          $page_links_to_start = $page_no-3;
          $page_links_to_end = $page_links_to_start+10;            
        }
        if($catalogs_list['catalogs']['total_pages']<$page_links_to_end) {
          $page_links_to_end = $catalogs_list['catalogs']['total_pages'];
        }
        if($catalogs_list['catalogs']['total_records'] < $per_page) {
          $to_sl_no = ($slno+$catalogs_list['catalogs']['total_records'])-1;
        }
        $catalogs = $catalogs_list['catalogs']['catalogs'];
        $total_pages = $catalogs_list['catalogs']['total_pages'];
        $total_records = $catalogs_list['catalogs']['total_records'];
        $record_count = $catalogs_list['catalogs']['total_records'];
      } else {
        $page_error = $catalogs_list['apierror'];
      }
    } else {
      $page_error = $catalogs_list['apierror'];
    }

    // build variables
    $controller_vars = array(
      'page_title' => 'Catalogs',
      'icon_name' => 'fa fa-briefcase',
    );

    $template_vars = array(
      'catalogs' => $catalogs,
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
      'flash_obj'  => $this->flash,
    );

    // render template
    return array($this->template->render_view('catalogs-list',$template_vars),$controller_vars);;
  }

  public function catalogItems(Request $request) {

    // validate catalog code.
    $catalog_code = !is_null($request->get('catalogCode')) ? Utilities::clean_string($request->get('catalogCode')) : '';
    if($catalog_code === '') {
      $this->flash->set_flash_message('Invalid Catalog');
      Utilities::redirect('/catalog/list');
    }
    $catalog_details_response = $this->catalog_model->get_catalog_details($catalog_code);
    if(is_array($catalog_details_response) && $catalog_details_response['status']) {
      $existing_catalog_details = $catalog_details_response['catalogDetails'];
    } else {
      $this->flash->set_flash_message($catalog_details_response['apierror'], 1);
      Utilities::redirect('/catalog/list');
    }

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
    $list_type = !is_null($request->get('listType')) ? Utilities::clean_string($request->get('listType')) : 'all';

    $search_params = [
      'locationCode' => $location_code,
      'category' => $category,
      'mfg' => $mfg,
      'itemName' => $item_name,
      'pageNo' => $page_no,
      'perPage' => $per_page,
      'listType' => $list_type
    ];

    $categories_a = $categories_a + $this->products_model->get_product_categories($location_code);
    # end of initializing variables
    #-------------------------------------------------------------------------------

    // hit api.
    $products_list = $this->catalog_model->catalog_items($catalog_code, $search_params);
    // dump($products_list);
    // exit;

    $api_status = $products_list['status'];
    if($api_status) {
      // check whether we got products or not.
      if(count($products_list['response']['items']) >0) {
        $slno = Utilities::get_slno_start(count($products_list['response']['items']), $per_page, $page_no);
        $to_sl_no = $slno+$per_page;
        $slno++;
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
        if(count($products_list['response']['items']) < $per_page) {
          $to_sl_no = ($slno + count($products_list['response']['items']))-1;
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


    // build variables
    $controller_vars = array(
      'page_title' => 'Catalog Items',
      'icon_name' => 'fa fa-briefcase',
    );

    $template_vars = array(
      'form_errors' => $form_errors,
      'form_data' => $form_data,
      'api_error' => $api_error,
      'client_locations' => array(''=>'All Stores') + $client_locations,
      'default_location' => $location_code,
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,
      'categories' => ['' => 'All Categories'] + $categories_a,
      'list_types' => ['all' => 'All Items', 'added' => 'Assigned Items', 'notadded' => 'Unassigned Items'],
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
      'catalog_code' => $catalog_code,
    );

    // render template
    return array($this->template->render_view('catalog-items',$template_vars),$controller_vars);;
  }

  public function catalogView(Request $request) {

    // validate catalog code.
    $catalog_code = !is_null($request->get('catalogCode')) ? Utilities::clean_string($request->get('catalogCode')) : '';
    if($catalog_code === '') {
      $this->flash->set_flash_message('Invalid Catalog');
      Utilities::redirect('/catalog/list');
    }
    $catalog_details_response = $this->catalog_model->get_catalog_details($catalog_code);
    if(is_array($catalog_details_response) && $catalog_details_response['status']) {
      $existing_catalog_details = $catalog_details_response['catalogDetails'];
    } else {
      $this->flash->set_flash_message($catalog_details_response['apierror'], 1);
      Utilities::redirect('/catalog/list');
    }

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
    $location_code = !is_null($request->get('locationCode')) ? Utilities::clean_string($request->get('locationCode')) : '';

    $search_params = [
      'locationCode' => $location_code,
      'category' => $category,
      'mfg' => $mfg,
      'itemName' => $item_name,
      'pageNo' => $page_no,
      'perPage' => $per_page,
      'onlyCatalog' => 'yes',
    ];

    $categories_a = $categories_a + $this->products_model->get_product_categories($location_code);
    # end of initializing variables
    #-------------------------------------------------------------------------------

    // hit api.
    $products_list = $this->catalog_model->catalog_items($catalog_code, $search_params);
    $api_status = $products_list['status'];
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


    // build variables
    $controller_vars = array(
      'page_title' => 'View Catalog',
      'icon_name' => 'fa fa-briefcase',
    );

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
      'catalog_code' => $catalog_code,
    );

    // render template
    return array($this->template->render_view('catalog-view',$template_vars),$controller_vars);;
  }



  public function addItemToCatalog(Request $request) {
    if( count($request->request->all())>0 ) {
      $form_data = $request->request->all();
      $lc = isset($form_data['lc']) ? Utilities::clean_string($form_data['lc']) : false;
      $cc = isset($form_data['cc']) ? Utilities::clean_string($form_data['cc']) : false;
      $ic = isset($form_data['ic']) ? Utilities::clean_string($form_data['ic']) : false;

      if($lc === false || $cc === false || $ic === false) {
        header("Content-type: application/json");
        echo json_encode(['status' => 'failed', 'response' => 'Invalid input']);
        exit;
      }

      $item_response = $this->catalog_model->add_item_to_catalog($lc, $cc, $ic);
      if($item_response['status']) {
        $response = ['status' => 'success', 'itemCode' => $item_response['response']['catalogItemCode']];   
      } else {
        $response = ['status' => 'failed', 'reason' => $item_response['apierror']];   
      }
      header("Content-type: application/json");
      echo json_encode($response);
      exit;
    }

    header("Content-type: application/json");
    echo json_encode(['status' => 'failed', 'response' => 'Invalid method']);
    exit;    
  }

  public function removeItemFromCatalog(Request $request) {
    if( count($request->request->all())>0 ) {
      $form_data = $request->request->all();
      $ic = isset($form_data['ic']) ? Utilities::clean_string($form_data['ic']) : false;

      if($ic === false) {
        header("Content-type: application/json");
        echo json_encode(['status' => 'failed', 'response' => 'Invalid input']);
        exit;
      }

      $item_response = $this->catalog_model->remove_item_from_catalog($ic);
      if($item_response['status']) {
        $response = ['status' => 'success', 'op' => 'success'];   
      } else {
        $response = ['status' => 'failed', 'reason' => $item_response['apierror']];   
      }
      header("Content-type: application/json");
      echo json_encode($response);
      exit;
    }

    header("Content-type: application/json");
    echo json_encode(['status' => 'failed', 'response' => 'Invalid method']);
    exit;    
  }

  // validate form data
  private function _validate_form_data($form_data = []) {
    $cleaned_params = $form_errors = [];

    $catalog_name = Utilities::clean_string($form_data['catalogName']);
    $is_default = Utilities::clean_string($form_data['isDefault']);
    $catalog_status = Utilities::clean_string($form_data['status']);
    $catalog_desc_short = Utilities::clean_string($form_data['catalogDescShort']);
    $catalog_desc = Utilities::clean_string($form_data['catalogDesc']);
    $category_id = Utilities::clean_string($form_data['categoryID']);
    $subcategory_id = Utilities::clean_string($form_data['subCategoryID']);

    if($catalog_name !== '') {
      $cleaned_params['catalogName'] = $catalog_name;
    } else {
      $form_errors['catalogName'] = 'Invalid catalog name or catalog name should not be empty.';
    }

    $cleaned_params['catalogDesc'] = $catalog_desc;
    $cleaned_params['catalogDescShort'] = $catalog_desc_short;
    $cleaned_params['status'] = $catalog_status;
    $cleaned_params['isDefault'] = $is_default;
    $cleaned_params['categoryID'] = $category_id;
    $cleaned_params['subCategoryID'] = $subcategory_id;

    if(count($form_errors)>0) {
      return array(
        'status' => false,
        'errors' => $form_errors,
      );
    } else {
      return array(
        'status' => true,
        'cleaned_params' => $cleaned_params,
      );
    } 
  }

  // get categories from api.
  private function get_categories() {
    $categories = $subcategories = [];
    $categories_list = $this->category_model->categories_list(['returnAll' => true, 'activeOnly' => true]);
    $api_status = $categories_list['status'];
    // check api status
    if($api_status) {
      // $category_keys = array_column($categories_list['categories']['categories'], 'categoryID');
      // $category_values = array_column($categories_list['categories']['categories'], 'categoryName');
      // return array_combine($category_keys, $category_values);
      foreach($categories_list['categories']['categories'] as $category_details) {
        $parent_id = $category_details['parentID'];
        $category_id = $category_details['categoryID'];
        if($parent_id > 0) {
          $subcategories[$category_id] = $category_details['categoryName'];
        } else {
          $categories[$category_id] = $category_details['categoryName'];
        }
      }
      return ['categories' => $categories, 'subcategories' => $subcategories];
    } else {
      return [];
    }    
  }  

} // end of class.