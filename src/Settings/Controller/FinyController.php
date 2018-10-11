<?php 

namespace Settings\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Atawa\Utilities;
use Atawa\Template;
use Atawa\Flash;
use Settings\Model\Finy;

class FinyController {
	
  protected $template, $flash;

  public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->flash = new Flash;
    $this->finy_model = new Finy;
  }

  // create finy
  public function createFinYear(Request $request) {

    $form_data = $form_errors = [];
    $status_a = ['Inactive', 'Active'];

    if( count($request->request->all())>0 ) {
      $form_data = $request->request->all();
      $validation = $this->_validate_fin_year_data($form_data);
      if($validation['status']) {
        $api_response = $this->finy_model->create_finy($validation['cleaned_params']);
        if($api_response['status']) {
          $message = 'Financial year successfully created with code ` '.$api_response['finyCode'].' `';
          $this->flash->set_flash_message($message);
          Utilities::redirect('/finy/create');
        } else {
          $api_error = $api_response['apierror'];
          $this->flash->set_flash_message($api_error, 1);
        }
      } else {
        $form_errors = $validation['form_errors'];
      }
    }

    // prepare form variables.
    $template_vars = array(
      'submitted_data' => $form_data,
      'errors' => $form_errors,
      'flash_obj' => $this->flash,
      'status_a' => $status_a,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Settings - Financial Year',
      'icon_name' => 'fa fa-cogs',      
    );

    return array($this->template->render_view('create-finyear', $template_vars), $controller_vars);
  }

  // update finy
  public function updateFinYear(Request $request) {

    $form_data = $form_errors = [];
    $status_a = ['Inactive', 'Active'];

    $finy_code = $request->get('finyCode');
    $finy_details = $this->finy_model->get_finy_details($finy_code);
    if($finy_details['status']===false) {
      $this->flash->set_flash_message('Invalid Financial year code', 1);
      Utilities::redirect('/finy/list');
    }

    $form_data = $finy_details['finyDetails'];
    if( count($request->request->all())>0 ) {
      $submitted_data = $request->request->all();
      $validation = $this->_validate_fin_year_data($submitted_data);
      if($validation['status']) {
        $api_response = $this->finy_model->update_finy($validation['cleaned_params'], $finy_code);
        if($api_response['status']) {
          $message = 'Financial year information updated successfully.';
          $this->flash->set_flash_message($message);
          Utilities::redirect('/finy/list');
        } else {
          $api_error = $api_response['apierror'];
          $this->flash->set_flash_message($api_error, 1);
        }
      } else {
        $form_errors = $validation['form_errors'];
      }
    }

    // prepare form variables.
    $template_vars = array(
      'submitted_data' => $form_data,
      'errors' => $form_errors,
      'flash_obj' => $this->flash,
      'status_a' => $status_a,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Settings - Financial Year',
      'icon_name' => 'fa fa-cogs',      
    );

    return array($this->template->render_view('update-finyear', $template_vars), $controller_vars);
  }  

  // list finys
  public function listFinYears(Request $request) {
    $api_response = $this->finy_model->get_finys();
    if($api_response['status']) {
      $finys = $api_response['finys'];
    } else {
      $finys = [];
    }

    // prepare form variables.
    $template_vars = array(
      'finys' => $finys,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Settings - Financial Years',
      'icon_name' => 'fa fa-cogs',
    );

    // render template
    return array($this->template->render_view('finys-list', $template_vars), $controller_vars);    
  }

  // set active financial year
  public function setActiveFinYear(Request $request) {

    $finys = $form_data = $form_errors = [];

    $finy_response = $this->finy_model->get_finys();
    if($finy_response['status'] && count($finy_response['finys']) > 0) {
      $finy_codes = array_column($finy_response['finys'], 'finyCode');
      $finy_names = array_column($finy_response['finys'], 'finyName');
      $finys = array_combine($finy_codes, $finy_names);
    } else {
      $this->flash->set_flash_message('No Financial years were defined for activation.');
      Utilities::redirect('/finy/list');
    }

    if( count($request->request->all())>0 ) {
      $submitted_data = $request->request->all();
      $submitted_finy_code = Utilities::clean_string($submitted_data['finyCode']);
      if($submitted_finy_code !== '' && in_array($submitted_finy_code, array_keys($finys))) {
        $fin_year_name = $finys[$submitted_finy_code];
        $api_response = $this->finy_model->set_active_fin_year(['finyCode' => $submitted_finy_code]);
        if($api_response['status']) {
          $message = 'Financial year `'.$fin_year_name.'` is Default and Active Financial year now.';
          $this->flash->set_flash_message($message);
          Utilities::redirect('/finy/list');
        } else {
          $api_error = $api_response['apierror'];
          $this->flash->set_flash_message($api_error, 1);
        }
      } else {
        $form_errors = array('finyCode' => 'Invalid Financial year.');
      }
    }    

    // prepare form variables.
    $template_vars = array(
      'submitted_data' => $form_data,
      'errors' => $form_errors,
      'flash_obj' => $this->flash,
      'finys' => ['' => 'Choose'] + $finys,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Settings - Financial Year',
      'icon_name' => 'fa fa-cogs',      
    );

    return array($this->template->render_view('set-active-finyear', $template_vars), $controller_vars);
  }

  // validate finy
  private function _validate_fin_year_data($form_data =[]) {
    $cleaned_params = $form_errors = [];
    $status_a = [0,1];

    $year_name = Utilities::clean_string($form_data['finyName']);
    $year_short_name = Utilities::clean_string($form_data['finyShortName']);
    $start_date = Utilities::clean_string($form_data['startDate']);
    $end_date = Utilities::clean_string($form_data['endDate']);
    $status = Utilities::clean_string($form_data['status']);

    if($year_name === '') {
      $form_errors['finyName'] = 'Year name is mandatory';
    } else {
      $cleaned_params['finyName'] = $year_name;
    }
    if($year_short_name === '') {
      $form_errors['finyShortName'] = 'Year short name is mandatory';
    } else {
      $cleaned_params['finyShortName'] = $year_short_name;
    }
    if($start_date === '' || Utilities::validate_date($start_date) === false) {
      $form_errors['startDate'] = 'Invalid start date';
    }
    if($end_date === '' || Utilities::validate_date($end_date) === false) {
      $form_errors['endDate'] = 'Invalid end date';
    }
    if(strtotime($end_date) < strtotime($start_date)) {
      $form_errors['endDate'] = 'End date must be greater than start date';
    } else {
      $cleaned_params['startDate'] = $start_date;
      $cleaned_params['endDate'] = $end_date;
    }
    if(in_array($status, $status_a)) {
      $cleaned_params['status'] = $status;
    } else {
      $form_errors['status'] = 'Invalid status';      
    }

    if(count($form_errors) > 0) {
      return [
        'status' => false,
        'form_errors' => $form_errors,
      ];
    } else {
      return [
        'status' => true,
        'cleaned_params' => $cleaned_params,
      ];      
    }
  }
}

/*
   1. Purchase Order        => POR =>  20000000
   2. Purchase Return       => PRE => 120000000
   3. GRN                   => GRN =>  40000000
   4. Sales Invoice         => SIN =>  10000000
   5. Sales Return          => SRN =>  30000000
   6. Debit Note            => DNO =>  90000000
       1) Auto
       2) Manual
   7. Credit Note           => CNO =>  80000000
       1) Auto
       2) Manual
   8. Stock Transfer        => STR => 130000000
   9. Payment               => PMT =>  50000000
  10. Receipt               => RCP =>  60000000
  11. Petty Cash            => PCA =>  70000000
  12. Stock Adjustment      => SAD => 140000000
*/