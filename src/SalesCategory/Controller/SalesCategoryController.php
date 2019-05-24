<?php 

namespace SalesCategory\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Atawa\Utilities;
use Atawa\Template;
use Atawa\Flash;

use SalesCategory\Model\SalesCategory;

class SalesCategoryController
{
	protected $views_path,$flash,$tax_model;

	public function __construct() {
		$this->views_path = __DIR__.'/../Views/';
    $this->flash = new Flash();
    $this->sc_model = new SalesCategory;
	}

  public function addSalesCategory(Request $request) {

    $submitted_data = $form_errors = [];
    $status_options = array(-1=>'Select', 1 => 'Active', 0 => 'Inactive');

    if( count($request->request->all())>0 ) {
      $submitted_data = $request->request->all();
      $form_validation = $this->_validate_form_data($submitted_data);
      if($form_validation['status']) {
        $cleaned_params = $form_validation['cleaned_params'];
        $result = $this->sc_model->add_sales_category($cleaned_params);
        if($result['status']) {
          $this->flash->set_flash_message('<i class="fa fa-check" aria-hidden="true"></i> Sales category added successfully with code `'.$result['salesCategoryCode'].'`');
        } else {
          $page_error = $result['apierror'];
          $this->flash->set_flash_message($page_error, 1);
        }
        Utilities::redirect('/sales-category/add');
      } else {
        $form_errors = $form_validation['errors'];
      }
    }

    // prepare form variables.
    $template_vars = array(
      'status_options' => $status_options,
      'submitted_data' => $submitted_data,
      'form_errors' => $form_errors,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Sales Category - Add',
      'icon_name' => 'fa fa-inr',
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('add-sales-category',$template_vars),$controller_vars);
  }

  public function updateSalesCategory(Request $request) {

    $submitted_data = $form_errors = [];
    $status_options = array(-1 => 'Select', 1 => 'Active', 0 => 'Inactive');

    if( is_null($request->get('categoryCode')) ) {
      $this->set_flash_message('Invalid Code', 1);
      Utilities::redirect('/stock-adj-reasons/list');
    } else {
      $category_code = Utilities::clean_string($request->get('categoryCode'));
      $category_details_response = $this->sc_model->get_sales_category_details($category_code);
      if($category_details_response['status'] === false) {
        $this->set_flash_message('Invalid Code or Code does not exists.', 1);
        Utilities::redirect('/sales-category/list');
      } else {
        $submitted_data = $category_details_response['category_details'];
        $category_code = $submitted_data['salesCategoryCode'];
      }
    }

    if( count($request->request->all())>0 ) {
      $submitted_data = $request->request->all();
      $form_validation = $this->_validate_form_data($submitted_data);
      if($form_validation['status']) {
        $cleaned_params = $form_validation['cleaned_params'];
        $result = $this->sc_model->update_sales_category($cleaned_params, $category_code);
        if($result['status']) {
          $this->flash->set_flash_message('<i class="fa fa-check" aria-hidden="true"></i> Sales category updated successfully');
        } else {
          $page_error = $result['apierror'];
          $this->flash->set_flash_message($page_error, 1);
        }
        Utilities::redirect('/sales-category/list');
      } else {
        $form_errors = $form_validation['errors'];
      }
    }

    // prepare form variables.
    $template_vars = array(
      'status_options' => $status_options,
      'submitted_data' => $submitted_data,
      'form_errors' => $form_errors,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Sales Categories - Update',
      'icon_name' => 'fa fa-inr',
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('update-sales-category',$template_vars),$controller_vars);
  }

  public function listSalesCategories(Request $request) {

    $sales_categories = []; 
    $page_error = '';

    $api_response = $this->sc_model->list_sales_categories();
    // dump($api_response);
    // exit;
    if($api_response['status']) {
      $sales_categories = $api_response['response'];
    } else {
      $page_error = $api_response['apierror'];
    }

    // prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'sales_categories' => $sales_categories,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Sales Categories - List',
      'icon_name' => 'fa fa-inr',
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('sales-category-list',$template_vars),$controller_vars);
  }
 
  private function _validate_form_data($form_data=array()) {
    $cleaned_params = $errors = [];

    $category_name = Utilities::clean_string($form_data['categoryName']);
    $status = Utilities::clean_string($form_data['status']);

    if($reason_desc !== '') {
      $cleaned_params['categoryName'] = $category_name;
    } else {
      $errors['categoryName'] = 'Invalid category Name.';
    }
    if((int)$form_data['status'] === 0 || (int)$form_data['status']===1) {
      $cleaned_params['status'] = $form_data['status'];
    } else {
      $errors['status'] = 'Status is required.';
    }

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