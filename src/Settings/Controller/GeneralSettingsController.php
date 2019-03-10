<?php 

namespace Settings\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Atawa\Utilities;
use Atawa\Template;
use Atawa\Flash;
use Atawa\ApiCaller;

use Settings\Model\GenSettings;
use User\Model\User;

class GeneralSettingsController {
	
  protected $template, $flash;

  public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->flash = new Flash;
    $this->gens_model = new GenSettings;
    $this->api_caller = new ApiCaller;
    $this->user_model = new User;
  }

  // maintenance mode
  public function maintenanceMode(Request $request) {

    $form_data = $form_errors = [];
    $status_a = [99=>'Choose', 1=>'On', 0=>'Off'];

    // get client details.
    $client_details = $this->user_model->get_client_details();
    if($client_details['status'] === false) {
      $this->flash->set_flash_message('Invalid Account Information.');
    } else {
      $current_status = $client_details['clientDetails']['inMaintainence'];
    }

    if( count($request->request->all())>0 ) {
      $form_data = $request->request->all();
      $status = (int)Utilities::clean_string($form_data['status']);
      if( ($status===1 || $status === 0) && $status !== $current_status) {
        $api_params = ['status' => $status];
        $api_response = $this->gens_model->maintenance_mode($api_params);
        if($api_response['status']) {
          if($status === 1) {
            $message = '<i class="fa fa-info-circle" aria-hidden="true"></i> The App is now under maintenance mode. Only admin users are permitted for accessing the app.';
          } else {
            $message = '<i class="fa fa-check-circle-o" aria-hidden="true"></i> The App is now accessible for all users.';
          }
          $this->flash->set_flash_message($message);
          Utilities::redirect('/maintenance-mode');
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
      'current_status' => $current_status,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'General Settings - Maintenance Mode',
      'icon_name' => 'fa fa-power-off',      
    );

    return array($this->template->render_view('maintenance-mode', $template_vars), $controller_vars);
  }

}