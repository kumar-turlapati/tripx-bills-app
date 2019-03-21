<?php 

namespace StockAdjReasons\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Atawa\Utilities;
use Atawa\Template;
use Atawa\Flash;

use StockAdjReasons\Model\StockAdjReasons;

class StockAdjReasonsController
{
	protected $views_path,$flash,$tax_model;

	public function __construct() {
		$this->views_path = __DIR__.'/../Views/';
    $this->flash = new Flash();
    $this->adj_reason_model = new StockAdjReasons;
	}

  public function addAdjReason(Request $request) {

    $submitted_data = $form_errors = [];
    $status_options = array(-1=>'Select', 1 => 'Active', 0 => 'Inactive');

    if( count($request->request->all())>0 ) {
      $submitted_data = $request->request->all();
      $form_validation = $this->_validate_form_data($submitted_data);
      if($form_validation['status']) {
        $cleaned_params = $form_validation['cleaned_params'];
        $result = $this->adj_reason_model->add_adj_reason($cleaned_params);
        if($result['status']) {
          $this->flash->set_flash_message('<i class="fa fa-check" aria-hidden="true"></i> Inventory adjustment reason added successfully with code `'.$result['adjReasonCode'].'`');
        } else {
          $page_error = $result['apierror'];
          $this->flash->set_flash_message($page_error, 1);
        }
        Utilities::redirect('/stock-adj-reason/add');
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
      'page_title' => 'Inventory Adjustment Reasons - Add',
      'icon_name' => 'fa fa-adjust',
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('add-adj-reason',$template_vars),$controller_vars);
  }

  public function updateAdjReason(Request $request) {

    $submitted_data = $form_errors = [];
    $status_options = array(-1 => 'Select', 1 => 'Active', 0 => 'Inactive');

    if( is_null($request->get('reasonCode')) ) {
      $this->set_flash_message('Invalid Code', 1);
      Utilities::redirect('/stock-adj-reasons/list');
    } else {
      $reason_code = Utilities::clean_string($request->get('reasonCode'));
      $reason_details_response = $this->adj_reason_model->get_adj_reason_details($reason_code);
      if($reason_details_response['status'] === false) {
        $this->set_flash_message('Invalid Code or Code does not exists.', 1);
        Utilities::redirect('/stock-adj-reasons/list');
      } else {
        $submitted_data = $reason_details_response['reason_details'];
        $reason_code = $submitted_data['adjReasonCode'];
      }
    }

    if( count($request->request->all())>0 ) {
      $submitted_data = $request->request->all();
      $form_validation = $this->_validate_form_data($submitted_data);
      if($form_validation['status']) {
        $cleaned_params = $form_validation['cleaned_params'];
        $result = $this->adj_reason_model->update_adj_reason($cleaned_params, $reason_code);
        if($result['status']) {
          $this->flash->set_flash_message('<i class="fa fa-check" aria-hidden="true"></i> Adjustment reason updated successfully');
        } else {
          $page_error = $result['apierror'];
          $this->flash->set_flash_message($page_error, 1);
        }
        Utilities::redirect('/stock-adj-reasons/list');
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
      'page_title' => 'Inventory Adjustment Reasons - Update',
      'icon_name' => 'fa fa-adjust',
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('update-adj-reason',$template_vars),$controller_vars);
  }

  public function listAdjReasonCodes(Request $request) {

    $adj_reasons = []; $page_error = '';

    $api_response = $this->adj_reason_model->list_adj_reasons();
    // dump($api_response);
    // exit;
    if($api_response['status']) {
      $adj_reasons = $api_response['response'];
    } else {
      $page_error = $api_response['apierror'];
    }

    // prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'adj_reasons' => $adj_reasons,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Inventory Adjustment Reasons - List',
      'icon_name' => 'fa fa-adjustment',
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('adj-reasons-list',$template_vars),$controller_vars);
  }
 
  private function _validate_form_data($form_data=array()) {
    $cleaned_params = $errors = [];

    $reason_desc = Utilities::clean_string($form_data['adjReasonName']);
    $status = Utilities::clean_string($form_data['status']);

    if($reason_desc !== '') {
      $cleaned_params['adjReasonName'] = $reason_desc;
    } else {
      $errors['hsnSacCodeDescShort'] = 'Invalid adjustment reason.';
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