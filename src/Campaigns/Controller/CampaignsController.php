<?php 

namespace Campaigns\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Atawa\Utilities;
use Atawa\Template;
use Atawa\Flash;

use Campaigns\Model\Campaigns;

class CampaignsController
{
	protected $views_path,$flash,$camp_model;

	public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->flash = new Flash;
    $this->camp_model = new Campaigns;
	}

  // add a campaign
  public function addCampaign(Request $request) {
    $submitted_data = $form_errors = array();
    $yes_no_options = array( '-1'=>'Select','1'=>'Active','0'=>'Inactive');

    if( count($request->request->all())>0 ) {
      $submitted_data = $request->request->all();
      $form_validation = $this->_validate_form_data($submitted_data);
      if($form_validation['status']) {
        $cleaned_params = $form_validation['cleaned_params'];
        $result = $this->camp_model->create_campaign($cleaned_params);
        if($result['status']) {
          $this->flash->set_flash_message('Campaign created successfully with Campaign Code `'.$result['campaignCode'].'`');
        } else {
          $page_error = $result['apierror'];
          $this->flash->set_flash_message($page_error, 1);
        }
        Utilities::redirect('/campaigns/create');
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
      'page_title' => 'Campaigns',
      'icon_name' => 'fa fa-magic',
    );

    // render template
    return array($this->template->render_view('add-campaign',$template_vars),$controller_vars);
  }

  // update a campaign
  public function updateCampaign(Request $request) {
    $submitted_data = $errors = [];
    $yes_no_options = array( '-1'=>'Select', '1'=>'Active', '0'=>'Inactive');    
    $campaign_code = Utilities::clean_string($request->get('campaignCode'));
    if( count($request->request->all())>0 ) {
      $submitted_data = $request->request->all();
      $form_validation = $this->_validate_form_data($submitted_data);
      if($form_validation['status']) {
        $cleaned_params = $form_validation['cleaned_params'];
        $result = $this->camp_model->update_campaign($cleaned_params,$campaign_code);
        if($result['status']) {
          $this->flash->set_flash_message('Campaign updated successfully');
        } else {
          $page_error = $result['apierror'];
          $this->flash->set_flash_message($page_error, 1);
        }
        Utilities::redirect('/campaigns/list');
      } else {
        $form_errors = $form_validation['errors'];
      }
    } elseif(!is_null($request->get('campaignCode'))) {
      $api_response = $this->camp_model->get_campaign_details($campaign_code);
      if($api_response['status'] === false) {
        $this->flash->set_flash_message('Invalid campaign code', 1);
        Utilities::redirect('/campaigns/list');
      }
      $submitted_data = $api_response['campaign_details'];
    } else {
      $this->flash->set_flash_message('Invalid campaign code', 1);      
      Utilities::redirect('/campaigns/list');
    }

    // prepare form variables.
    $template_vars = array(
      'yes_no_options' => $yes_no_options,
      'submitted_data' => $submitted_data,
      'campaignCode' => $campaign_code,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Campaigns',
      'icon_name' => 'fa fa-magic',
    );

    // render template
    return array($this->template->render_view('update-campaign', $template_vars),$controller_vars);
  }

  // list campaigns
  public function listCampaigns(Request $request) {

    $api_response = $this->camp_model->list_campaigns();
    if($api_response['status']) {
      $campaigns = $api_response['campaigns']['campaigns'];
    } else {
      $campaigns = [];
    }

    // prepare form variables.
    $template_vars = array(
      'campaigns' => $campaigns,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Campaigns',
      'icon_name' => 'fa fa-magic',
    );

    // render template
    return array($this->template->render_view('list-campaigns',$template_vars),$controller_vars);
  }

  // validate form data
  private function _validate_form_data($form_data=[]) {
    $cleaned_params = $errors = [];
    
    $start_date = Utilities::clean_string($form_data['startDate']);
    $end_date = Utilities::clean_string($form_data['endDate']);
    $status = (int)Utilities::clean_string($form_data['status']);
    $camp_desc = Utilities::clean_string($form_data['campaignDesc']);

    if(isset($form_data['campaignName']) && $form_data['campaignName'] !== '') {
      $campaign_name = Utilities::clean_string($form_data['campaignName']);
      if(!preg_match('/^[a-zA-Z0-9 .\-]+$/i', $campaign_name)) {
        $errors['campaignName'] = 'Campaign name should contain only alphabets, digits, period and dash symbol.';
      } else {
        $cleaned_params['campName'] = $campaign_name;
      }
    } else {
      $errors['campaignName'] = 'Campaign name is required.';
    }

    if(strtotime($end_date) < strtotime($start_date)) {
      $errors['endDate'] = 'End date must be greater than or equal to Start date.';
    } else {
      $cleaned_params['startDate'] = $start_date;
      $cleaned_params['endDate'] = $end_date;
    }

    if($status === 0 || $status === 1) {
      $cleaned_params['status'] = $status;
    } else {
      $errors['status'] = 'Invalid status';
    }

    $cleaned_params['campDesc'] = $camp_desc;

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