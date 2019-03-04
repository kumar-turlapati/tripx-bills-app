<?php 

namespace HsnSacCodes\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Atawa\Utilities;
use Atawa\Template;
use Atawa\Flash;

use HsnSacCodes\Model\HsnSacCodes;

class HsnSacCodesController
{
	protected $views_path,$flash,$tax_model;

	public function __construct() {
		$this->views_path = __DIR__.'/../Views/';
    $this->flash = new Flash();
    $this->hsnsac_model = new HsnSacCodes;
	}

  public function addHsnSacCode(Request $request) {

    $submitted_data = $form_errors = [];
    $status_options = array(-1=>'Select', 1 => 'Active', 0 => 'Inactive');

    if( count($request->request->all())>0 ) {
      $submitted_data = $request->request->all();
      $form_validation = $this->_validate_form_data($submitted_data);
      if($form_validation['status']) {
        $cleaned_params = $form_validation['cleaned_params'];
        $result = $this->hsnsac_model->add_hsnsac_code($cleaned_params);
        if($result['status']) {
          $this->flash->set_flash_message('<i class="fa fa-check" aria-hidden="true"></i> HSN/SAC code added successfully with code `'.$result['hsnSacUniqueCode'].'`');
        } else {
          $page_error = $result['apierror'];
          $this->flash->set_flash_message($page_error, 1);
        }
        Utilities::redirect('/hsnsac/add');
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
      'page_title' => 'HSN / SAC Codes',
      'icon_name' => 'fa fa-arrows-h',
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('add-hsn-sac-code',$template_vars),$controller_vars);
  }

  public function updateHsnSacCode(Request $request) {

    $submitted_data = $form_errors = [];
    $status_options = array(-1 => 'Select', 1 => 'Active', 0 => 'Inactive');

    if( is_null($request->get('hsnSacUniqueCode')) ) {
      $this->set_flash_message('Invalid Code', 1);
      Utilities::redirect('/hsnsac/list');
    } else {
      $hsnsac_unique_code = Utilities::clean_string($request->get('hsnSacUniqueCode'));
      $hsnsac_details_response = $this->hsnsac_model->get_hsnsac_details($hsnsac_unique_code);
      if($hsnsac_details_response['status'] === false) {
        $this->set_flash_message('Invalid Code or Code does not exists.', 1);
        Utilities::redirect('/hsnsac/list');
      } else {
        $submitted_data = $hsnsac_details_response['hsnsac_details'];
        $hsnsac_unique_code = $submitted_data['hsnSacUniqueCode'];
      }
    }

    if( count($request->request->all())>0 ) {
      $submitted_data = $request->request->all();
      $form_validation = $this->_validate_form_data($submitted_data);
      if($form_validation['status']) {
        $cleaned_params = $form_validation['cleaned_params'];
        $result = $this->hsnsac_model->update_hsnsac_code($cleaned_params, $hsnsac_unique_code);
        if($result['status']) {
          $this->flash->set_flash_message('<i class="fa fa-check" aria-hidden="true"></i> HSN/SAC code updated successfully');
        } else {
          $page_error = $result['apierror'];
          $this->flash->set_flash_message($page_error, 1);
        }
        Utilities::redirect('/hsnsac/list');
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
      'page_title' => 'HSN / SAC Codes Update',
      'icon_name' => 'fa fa-arrows-h',
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('update-hsn-sac-code',$template_vars),$controller_vars);
  }

  public function listHsnSacCodes(Request $request) {

    $devices_a = $search_params = $users_a = $users = [];
    $hsn_sac_codes = [];
    $page_error = '';
    
    $total_pages = $total_records = $record_count = $page_no = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;

    // parse request parameters.
    $hsn_sac_code = $request->get('hsnSacCode') !== null ? Utilities::clean_string($request->get('hsnSacCode')) : '';
    $hsn_sac_desc = $request->get('hsnSacCodeDesc') !== null ? Utilities::clean_string($request->get('hsnSacCodeDesc')) : '';
    $page_no = $request->get('pageNo') !== null ? Utilities::clean_string($request->get('pageNo')) : 1;
    $per_page = 100;

    $search_params = array(
      'pageNo' => $page_no,
      'perPage' => $per_page,
      'hsnSacCode' => $hsn_sac_code,
      'hsnSacCodeDesc' => $hsn_sac_desc,
    );

    // dump($search_params);
    // exit;

    $api_response = $this->hsnsac_model->list_hsnsac_codes($search_params);
    // dump($api_response);
    // exit;
    if($api_response['status']) {
      if(count($api_response['response']['hsnsaccodes'])>0) {
          $slno = Utilities::get_slno_start(count($api_response['response']['hsnsaccodes']),$per_page,$page_no);
          $to_sl_no = $slno+$per_page;
          $slno++;
          if($page_no <= 3) {
            $page_links_to_start = 1;
            $page_links_to_end = 10;
          } else {
            $page_links_to_start = $page_no-3;
            $page_links_to_end = $page_links_to_start+10;            
          }
          if($api_response['response']['total_pages']<$page_links_to_end) {
            $page_links_to_end = $api_response['response']['total_pages'];
          }
          if($api_response['response']['this_page'] < $per_page) {
            $to_sl_no = ($slno+$api_response['response']['this_page'])-1;
          }
          $hsn_sac_codes = $api_response['response']['hsnsaccodes'];
          $total_pages = $api_response['response']['total_pages'];
          $total_records = $api_response['response']['total_records'];
          $record_count = $api_response['response']['this_page'];
      } else {
        $page_error = $api_response['apierror'];
      }
    } else {
      $page_error = $api_response['apierror'];
    }

    // prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'codes' => $hsn_sac_codes,
      'total_pages' => $total_pages ,
      'total_records' => $total_records,
      'record_count' => $record_count,
      'sl_no' => $slno,
      'to_sl_no' => $to_sl_no,
      'page_links_to_start' => $page_links_to_start,
      'page_links_to_end' => $page_links_to_end,
      'current_page' => $page_no,
      'search_params' => $search_params,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'HSN / SAC Codes - List',
      'icon_name' => 'fa fa-arrows-h',
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('hsn-sac-codes-list',$template_vars),$controller_vars);
  }
 
  private function _validate_form_data($form_data=array()) {
    $cleaned_params = $errors = [];

    $hsnsac_code_desc = Utilities::clean_string($form_data['hsnSacCodeDesc']);
    $hsnsac_code_desc_short = Utilities::clean_string($form_data['hsnSacCodeDescShort']);
    $hsnsac_code = Utilities::clean_string($form_data['hsnSacCode']);
    $status = Utilities::clean_string($form_data['status']);

    if($hsnsac_code_desc !== '') {
      $cleaned_params['hsnSacCodeDescShort'] = $hsnsac_code_desc_short;
    } else {
      $errors['hsnSacCodeDescShort'] = 'HSN/SAC short description is required.';
    }

    if($hsnsac_code_desc !== '') {
      $cleaned_params['hsnSacCodeDesc'] = $hsnsac_code_desc;
    } else {
      $errors['hsnSacCodeDesc'] = 'HSN/SAC description is required.';
    }    

    if(ctype_digit(str_replace([' '], [''], $hsnsac_code))) {
      $cleaned_params['hsnSacCode'] = $hsnsac_code;
    } else {
      $errors['hsnSacCode'] = 'HSN/SAC code is required.';
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