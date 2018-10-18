<?php 

namespace ClothingRm\Categories\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;

use ClothingRm\Categories\Model\Categories;

class CategoriesController
{
	protected $template, $categories_model, $flash;

	public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');    
    $this->categories_model = new Categories;
    $this->flash = new Flash;
	}

  public function createCategory(Request $request) {

    $submitted_data = $form_errors = array();
    $status_options = array(''=>'Select','1'=>'Active','0'=>'Inactive');

    # ---------- get location codes from api -----------------
    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }    

    if( count($request->request->all())>0 ) {
      $submitted_data = $request->request->all();
      $form_validation = $this->_validate_form_data($submitted_data);
      if($form_validation['status']===true) {
        $cleaned_params = $form_validation['cleaned_params'];
        $result = $this->categories_model->create_product_category($cleaned_params);
        if($result['status']===true) {
          $category_code = $result['categoryCode'];
          $this->flash->set_flash_message('Category create successfully with code `'.$category_code.'`');
        } else {
          $page_error = $result['apierror'];
          $this->flash->set_flash_message($page_error, 1);
        }
        Utilities::redirect('/category/create');
      } else {
        $form_errors = $form_validation['errors'];
      }
    }

    # prepare form variables.
    $template_vars = array(
      'status_options' => $status_options,
      'submitted_data' => $submitted_data,
      'form_errors' => $form_errors,
      'flash_obj' => $this->flash,
      'client_locations' => array(''=>'Choose') + $client_locations,
      'default_location' => isset($_SESSION['lc']) ? $_SESSION['lc'] : '',
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,
    );

    # build variables
    $controller_vars = array(
      'page_title' => 'Product Categories',
      'icon_name' => 'fa fa-list',
    );

    # render template
    return array($this->template->render_view('add-category',$template_vars),$controller_vars);
  }

  public function updateCategory(Request $request) {
    $submitted_data = $form_errors = array();
    $status_options = array(''=>'Select','1'=>'Active','0'=>'Inactive');

    # ---------- get location codes from api -----------------
    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }    

    $category_code = $request->get('categoryCode');
    $category_details = $this->categories_model->get_category_details($category_code);
    if($category_details === false) {
      $this->flash->set_flash_message('Invalid category code', 1);
      Utilities::redirect('/categories/list');
    } else {
      $submitted_data = $category_details;
    }

    if( count($request->request->all())>0 ) {
      $submitted_data = $request->request->all();
      $form_validation = $this->_validate_form_data($submitted_data);
      if($form_validation['status']) {
        $cleaned_params = $form_validation['cleaned_params'];
        $result = $this->categories_model->update_product_category($cleaned_params,$category_code);
        if($result['status']) {
          $this->flash->set_flash_message('Category updated successfully');
        } else {
          $page_error = $result['apierror'];
          $this->flash->set_flash_message($page_error, 1);
        }
        Utilities::redirect('/categories/list');
      } else {
        $form_errors = $form_validation['errors'];
      }
    }

    # prepare form variables.
    $template_vars = array(
      'status_options' => $status_options,
      'submitted_data' => $submitted_data,
      'form_errors' => $form_errors,
      'flash_obj' => $this->flash,
      'client_locations' => array(''=>'Choose') + $client_locations,
      'default_location' => isset($_SESSION['lc']) ? $_SESSION['lc'] : '',
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,      
    );

    # build variables
    $controller_vars = array(
      'page_title' => 'Categories',
      'icon_name' => 'fa fa-list',
    );

    # render template
    return array($this->template->render_view('update-category',$template_vars),$controller_vars);
  }

  public function listCategories(Request $request) {
    $products_list = $search_params = $categories = array();
    $total_pages = $total_records = $record_count = $page_no = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;
    $page_success = $page_error = '';

    if( !is_null($request->get('pageNo'))) {
      $page_no = $request->get('pageNo');
    } else {
      $page_no = 1;
    }

    if(!is_null($request->get('perPage'))) {
      $per_page = $request->get('perPage');
    } else {
      $per_page = 100;
    }

    if(count($request->request->all()) > 0) {
      $search_params = $request->request->all();
    } elseif( !is_null($request->get('catname'))) {
      $search_params['catname'] = $request->get('catname');
    } else {
      $search_params = [];
    }

    $categories_list = $this->categories_model->get_categories($page_no, $per_page, $search_params);
    $api_status = $categories_list['status'];

    # check api status
    if($api_status) {
      # check whether we got products or not.
      if(count($categories_list['categories']) >0) {
        $categories = $categories_list['categories'];
      } else {
        $page_error = $categories_list['apierror'];
      }
    } else {
      $page_error = $categories_list['apierror'];
    }

    # build variables
    $controller_vars = array(
      'page_title' => 'Product Categories',
      'icon_name' => 'fa fa-list',
    );
    $template_vars = array(
      'categories' => $categories,
      'sl_no' => 1,
      'search_params' => $search_params,
      'page_error' => $page_error,
      'page_success' => $page_success,
      'flash_obj' => $this->flash,
    );

    return array($this->template->render_view('categories-list', $template_vars), $controller_vars);        
  }

  /***************************** Private functions should go here ***************************/
  private function _validate_form_data($form_data=[]) {
    $cleaned_params = $errors = [];

    if(isset($form_data['categoryName']) && $form_data['categoryName'] !== '') {
      $category_name = Utilities::clean_string($form_data['categoryName']);
      if(!preg_match('/^[a-zA-Z0-9 .%\-]+$/i', $category_name)) {
        $errors['categoryName'] = 'Category name should contain only alphabets, digits, period, dash and percentage symbol.';
      } else {
        $cleaned_params['categoryName'] = $category_name;
      }
    } else {
      $errors['categoryName'] = 'Category name is required.';
    }

    if( is_numeric($form_data['status']) && ((int)$form_data['status']===0 || (int)$form_data['status']===1) ) {
      $cleaned_params['status'] = Utilities::clean_string($form_data['status']);
    } else {
      $errors['status'] = 'Invalid status.';
    }

    $cleaned_params['locationCode'] = Utilities::clean_string($form_data['locationCode']);

    if(count($errors)>0) {
      return array(
        'status' => false,
        'errors' => $errors,
      );
    } else {
      return array(
        'status' => true,
        'cleaned_params' => $cleaned_params,
      );
    }
  }
}