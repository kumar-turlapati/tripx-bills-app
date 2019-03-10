<?php 

namespace YearendJobs\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Atawa\Utilities;
use Atawa\Template;
use Atawa\Flash;

use YearendJobs\Model\YearendJobs;
use Settings\Model\Finy;

class YearendJobsController
{
	protected $views_path,$flash,$tax_model;

	public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->flash = new Flash;
    $this->yej_model = new YearendJobs;
    $this->finy_model = new Finy;
	}

  // post inventory
  public function postInventory(Request $request) {

    $finys = $form_data = $form_errors = [];
    $def_finy = '';

    $finy_response = $this->_get_available_fin_years();
    $from_finys = $to_finys = $finy_response['finys'];
    $def_finy_code = $finy_response['def_year'];
    $finy_dates = $finy_response['finy_dates'];

    if( count($request->request->all())>0 ) {
      $submitted_data = $request->request->all();
      $form_validation = $this->_validate_ye_post_data($submitted_data, $def_finy_code, $finy_dates);
      if($form_validation['status']) {
        $cleaned_params = $form_validation['cleaned_params'];
        $result = $this->yej_model->post_inventory($cleaned_params);
        if($result['status']) {
          $records_created = $result['recordsCreated'];
          $this->flash->set_flash_message('<i class="fa fa-check" aria-hidden="true"></i> You have successfully transferred closing balances of [ '.$records_created.' ] items');
        } else {
          $page_error = $result['apierror'];
          $this->flash->set_flash_message($page_error, 1);
        }
        Utilities::redirect('/year-end-jobs/post-inventory');
      } else {
        $form_errors = $form_validation['errors'];
      }
    }

    // prepare form variables.
    $template_vars = array(
      'from_finys' => ['' => 'Choose'] + $from_finys,
      'to_finys' => ['' => 'Choose'] + $to_finys,
      'def_finy_code' => $def_finy_code,
      'form_errors' => $form_errors,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Yearend Jobs - Post Inventory',
      'icon_name' => 'fa fa-database',
    );

    // render template
    return array($this->template->render_view('post-inventory',$template_vars),$controller_vars);
  }

  // post barcodes
  public function postBarcodes(Request $request) {

    $finys = $form_data = $form_errors = [];
    $def_finy = '';

    $finy_response = $this->_get_available_fin_years();
    $from_finys = $to_finys = $finy_response['finys'];
    $def_finy_code = $finy_response['def_year'];
    $finy_dates = $finy_response['finy_dates'];

    if( count($request->request->all())>0 ) {
      $submitted_data = $request->request->all();
      $form_validation = $this->_validate_ye_post_data($submitted_data, $def_finy_code, $finy_dates);
      if($form_validation['status']) {
        $cleaned_params = $form_validation['cleaned_params'];
        $result = $this->yej_model->post_barcodes($cleaned_params);
        if($result['status']) {
          $records_created = $result['recordsCreated'];
          $this->flash->set_flash_message('<i class="fa fa-check" aria-hidden="true"></i> You have successfully transferred [ '.$records_created.' ] Barcodes');
        } else {
          $page_error = $result['apierror'];
          $this->flash->set_flash_message($page_error, 1);
        }
        Utilities::redirect('/year-end-jobs/post-barcodes');
      } else {
        $form_errors = $form_validation['errors'];
      }
    }

    // prepare form variables.
    $template_vars = array(
      'from_finys' => ['' => 'Choose'] + $from_finys,
      'to_finys' => ['' => 'Choose'] + $to_finys,
      'def_finy_code' => $def_finy_code,
      'form_errors' => $form_errors,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Yearend Jobs - Post Barcodes',
      'icon_name' => 'fa fa-barcode',
    );

    // render template
    return array($this->template->render_view('post-barcodes',$template_vars),$controller_vars);
  }

  // post debtors
  public function postDebtors(Request $request) {

    $finys = $form_data = $form_errors = [];
    $def_finy = '';

    $finy_response = $this->_get_available_fin_years();
    $from_finys = $to_finys = $finy_response['finys'];
    $def_finy_code = $finy_response['def_year'];
    $finy_dates = $finy_response['finy_dates'];

    if( count($request->request->all())>0 ) {
      $submitted_data = $request->request->all();
      $form_validation = $this->_validate_ye_post_data($submitted_data, $def_finy_code, $finy_dates);
      if($form_validation['status']) {
        $cleaned_params = $form_validation['cleaned_params'];
        $result = $this->yej_model->post_debtors($cleaned_params);
        if($result['status']) {
          $records_created = $result['recordsCreated'];
          $this->flash->set_flash_message('<i class="fa fa-check" aria-hidden="true"></i> You have successfully transferred balances of [ '.$records_created.' ] Debtors');
        } else {
          $page_error = $result['apierror'];
          $this->flash->set_flash_message($page_error, 1);
        }
        Utilities::redirect('/year-end-jobs/post-debtors');
      } else {
        $form_errors = $form_validation['errors'];
      }
    }

    // prepare form variables.
    $template_vars = array(
      'from_finys' => ['' => 'Choose'] + $from_finys,
      'to_finys' => ['' => 'Choose'] + $to_finys,
      'def_finy_code' => $def_finy_code,
      'form_errors' => $form_errors,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Yearend Jobs - Post Debtors',
      'icon_name' => 'fa fa-plus',
    );

    // render template
    return array($this->template->render_view('post-debtors',$template_vars),$controller_vars);
  }

  // post creditors
  public function postCreditors(Request $request) {

    $finys = $form_data = $form_errors = [];
    $def_finy = '';

    $finy_response = $this->_get_available_fin_years();
    $from_finys = $to_finys = $finy_response['finys'];
    $def_finy_code = $finy_response['def_year'];
    $finy_dates = $finy_response['finy_dates'];

    if( count($request->request->all())>0 ) {
      $submitted_data = $request->request->all();
      $form_validation = $this->_validate_ye_post_data($submitted_data, $def_finy_code, $finy_dates);
      if($form_validation['status']) {
        $cleaned_params = $form_validation['cleaned_params'];
        $result = $this->yej_model->post_creditors($cleaned_params);
        if($result['status']) {
          $records_created = $result['recordsCreated'];
          $this->flash->set_flash_message('<i class="fa fa-check" aria-hidden="true"></i> You have successfully transferred balances of [ '.$records_created.' ] Creditors.');
        } else {
          $page_error = $result['apierror'];
          $this->flash->set_flash_message($page_error, 1);
        }
        Utilities::redirect('/year-end-jobs/post-creditors');
      } else {
        $form_errors = $form_validation['errors'];
      }
    }

    // prepare form variables.
    $template_vars = array(
      'from_finys' => ['' => 'Choose'] + $from_finys,
      'to_finys' => ['' => 'Choose'] + $to_finys,
      'def_finy_code' => $def_finy_code,
      'form_errors' => $form_errors,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Yearend Jobs - Post Creditors',
      'icon_name' => 'fa fa-minus',
    );

    // render template
    return array($this->template->render_view('post-creditors',$template_vars),$controller_vars);
  }

  // get available financial years.
  private function _get_available_fin_years() {
    $finy_response = $this->finy_model->get_finys();
    if($finy_response['status'] && count($finy_response['finys']) > 0) {
      $finy_codes = array_column($finy_response['finys'], 'finyCode');
      $finy_names = array_column($finy_response['finys'], 'finyName');
      foreach($finy_response['finys'] as $key => $finy_details) {
        $finy_dates[$finy_details['finyCode']] = [
          'startDate' => $finy_details['startDate'], 
          'endDate' => $finy_details['endDate'],
        ];
        if((int)$finy_details['isActive'] === 1) {
          $def_finy_code = $finy_details['finyCode'];
        }
        $finys[$finy_details['finyCode']] = $finy_details['finyName'];
      }
    } else {
      $this->flash->set_flash_message('No Financial years were defined for Yearend jobs.', 1);
      Utilities::redirect('/finy/list');
    }

    return ['finys' => $finys, 'def_year' => $def_finy_code, 'finy_dates' => $finy_dates];
  }

  // validate finy data.
  private function _validate_ye_post_data($submitted_data = [], $def_finy_code='', $finy_dates=[]) {
    $cleaned_params = $form_errors = [];
    $from_year_end_date = $to_year_start_date = '';

    $from_finy_code = Utilities::clean_string($submitted_data['fromFinyCode']);
    $to_finy_code = Utilities::clean_string($submitted_data['toFinyCode']);

    if($from_finy_code !== $def_finy_code) {
      $form_errors['fromFinyCode'] = 'Invalid Financial Year.';
    } else {
      $cleaned_params['fromFinyCode'] = $from_finy_code;
      $from_year_end_date = $finy_dates[$from_finy_code]['endDate'];
    }
    if($to_finy_code === '') {
      $form_errors['toFinyCode'] = 'Invalid Financial Year.';
    } else {
      $cleaned_params['toFinyCode'] = $to_finy_code;
      $to_year_start_date = $finy_dates[$to_finy_code]['startDate'];
    }

    if($from_year_end_date !== '' && $to_year_start_date !== '') {
      $from_year_end_ts = strtotime($from_year_end_date);
      $to_year_start_ts = strtotime($to_year_start_date);
      if($to_year_start_ts <= $from_year_end_ts) {
        $form_errors['toFinyCode'] = 'To Financial year must be greater than From financial year.';
      }
    }

    // dump($from_year_end_ts.'fromyear__'.$from_year_end_date, $to_year_start_ts.'toyear__'.$to_year_start_date);
    // dump($form_errors);
    // exit;

    if(count($form_errors)>0) {
      return ['status' => false, 'errors' => $form_errors];
    } else {
      return ['status' => true, 'cleaned_params' => $cleaned_params];
    }
  }
}