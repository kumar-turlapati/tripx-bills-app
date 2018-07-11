<?php 

namespace ClothingRm\Taxes\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Atawa\Utilities;
use Atawa\Template;
use Atawa\Flash;

use ClothingRm\Taxes\Model\Taxes;

class TaxesController
{
	protected $views_path,$flash,$tax_model;

	public function __construct() {
		$this->views_path = __DIR__.'/../Views/';
    $this->flash = new Flash();
    $this->tax_model = new Taxes();
	}

  public function addTax(Request $request) {

    $submitted_data = $form_errors = array();
    $yes_no_options = array(''=>'Select','0'=>'No','1'=>'Yes');

    if( count($request->request->all())>0 ) {
      $submitted_data = $request->request->all();
      $form_validation = $this->_validate_form_data($submitted_data);
      if($form_validation['status']===true) {
        $cleaned_params = $form_validation['cleaned_params'];
        $result = $this->tax_model->add_tax($cleaned_params);
        if($result['status']===true) {
          $this->flash->set_flash_message('Tax rate added successfully');
        } else {
          $page_error = $result['apierror'];
          $this->flash->set_flash_message($page_error, 1);
        }
        Utilities::redirect('/taxes/add');
      } else {
        $form_errors = $form_validation['errors'];
      }
    }

    // prepare form variables.
    $template_vars = array(
      'yes_no_options' => $yes_no_options,
      'submitted_data' => $submitted_data,
      'form_errors' => $form_errors,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Taxes',
      'icon_name' => 'fa fa-scissors',
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('add-tax',$template_vars),$controller_vars);
  }

  public function updateTax(Request $request) {

    $submitted_data = $errors = array();
    $tax_code = $request->get('taxCode');
    $tax_details = $this->tax_model->get_tax_details($tax_code);
    if($tax_details['status']===false) {
      $this->flash->set_flash_message('Invalid tax code', 1);
      Utilities::redirect('/taxes/list');
    }

    $submitted_data = $tax_details['tax_details'];

    if( count($request->request->all())>0 ) {
      $submitted_data = $request->request->all();
      $form_validation = $this->_validate_form_data($submitted_data);
      if($form_validation['status']===true) {
        $cleaned_params = $form_validation['cleaned_params'];
        $result = $this->tax_model->update_tax($cleaned_params,$tax_code);
        if($result['status']===true) {
          $this->flash->set_flash_message('Tax rate updated successfully');
        } else {
          $page_error = $result['apierror'];
          $this->flash->set_flash_message($page_error, 1);
        }
        Utilities::redirect('/taxes/list');
      } else {
        $form_errors = $form_validation['errors'];
      }
    }

    // prepare form variables.
    $template_vars = array(
      'yes_no_options' => array(''=>'Select','0'=>'No','1'=>'Yes'),
      'submitted_data' => $submitted_data,
      'taxCode' => $tax_code,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Taxes',
      'icon_name' => 'fa fa-scissors',
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('update-tax',$template_vars),$controller_vars);
  }

  public function listTaxes(Request $request) {

    $taxes = $this->tax_model->list_taxes();

    // prepare form variables.
    $template_vars = array(
      'taxes' => $taxes['taxes'],
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Taxes',
      'icon_name' => 'fa fa-scissors',
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('taxes-list',$template_vars),$controller_vars);
  }

  /***************************** Private functions should go here ***************************/
  private function _validate_form_data($form_data=array()) {
    $cleaned_params = $errors = array();

    if(isset($form_data['taxName']) && $form_data['taxName'] !== '') {
      $tax_name = Utilities::clean_string($form_data['taxName']);
      if(!preg_match('/^[a-zA-Z0-9 .%\-]+$/i', $tax_name)) {
        $errors['taxName'] = 'Tax name should contain only alphabets, digits, period, dash and percentage symbol.';
      } else {
        $cleaned_params['taxName'] = $tax_name;
      }
    } else {
      $errors['taxName'] = 'Tax name is required.';
    }

    if( is_numeric($form_data['taxPercent']) && $form_data['taxPercent']>=0) {
      $cleaned_params['taxPercent'] = Utilities::clean_string($form_data['taxPercent']);
    } else {
      $errors['taxPercent'] = 'Invalid tax percentage.';
    }

    $cleaned_params['isCompound'] = 0;

    /*
    if( isset($form_data['isCompound']) && ((int)$form_data['isCompound']===0 || (int)$form_data['isCompound']===1) ) {
      $cleaned_params['isCompound'] = Utilities::clean_string($form_data['isCompound']);
    } else {
      $errors['isCompound'] = 'Invalid choice.';
    }*/

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