<?php 

namespace Leads\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Template;
use Atawa\Flash;
use Atawa\Importer;
use Atawa\CrmUtilities;
use User\Model\User;

use Leads\Model\Lead;

class LeadsController
{

	public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->lead_model = new Lead;
    $this->flash = new Flash;
    $this->user_model = new User;
	}

	# lead create action
	public function leadCreateAction(Request $request) {

		$page_error = $page_success = '';
    $lead_source_id = $lead_status_id = $lead_rating_id = '';
    $industry_id = $lead_emprange_id = '';
    $lead_code = '';

    $form_data = $form_errors = [];
    $users = $users_a = [];

    # form submit
    if(count($request->request->all()) > 0) {
      $form_data = $request->request->all();
      # validate form data
      $form_validation = $this->_validate_form_data($form_data);
      if($form_validation['status'] === false) {
        $form_errors = $form_validation['errors'];
        $message = 'You have errors in the Form. Please fix them before you hit Save.';
        $this->flash->set_flash_message($message, 1);
      } else {
        # hit api and get the status.
        $api_action = $this->lead_model->createLead($form_data);
        if($api_action['status']) {
          $lead_code = $api_action['leadCode'];
          $message = '<i class="fa fa-check-circle-o" aria-hidden="true"></i>&nbsp;Lead created successfully with code `'.$lead_code.'`';
          $this->flash->set_flash_message($message);
          Utilities::redirect('/lead/create');
        } else {
          $form_errors = Utilities::format_api_error_messages($api_action['apierror']);
          $message = 'You have errors in the Form. Please fix them before you hit Save.';
          $this->flash->set_flash_message($message, 1);
        }
      }
    }

    // get users from api
    $result = $this->user_model->get_users();
    if($result['status']) {
      $users_a = $result['users'];
      foreach($users_a as $user_details) {
        $users[$user_details['uuid']] = $user_details['userName'];
      }
    }    

    # prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'page_success' => $page_success,
      'lead_sources_a' => array(''=>'Choose') + CrmUtilities::get_lead_source(),
      'lead_status_a' => array(''=>'Choose') + CrmUtilities::get_lead_status(),
      'lead_ratings_a' => array(''=>'Choose') + CrmUtilities::get_lead_rating(),
      'industries_a' => array(''=>'Choose') + CrmUtilities::get_crm_industries(),
      'emp_ranges_a' => array(''=>'Choose') + CrmUtilities::get_employee_range(),
      'titles_a' => array(''=>'Choose') + CrmUtilities::get_titles(),
      'email_optout_a' => array(''=>'Choose') + array(0=>'No', 1=>'Yes'),
      'lead_source_id' => $lead_source_id,
      'lead_status_id' => $lead_status_id,
      'lead_rating_id' => $lead_rating_id,
      'lead_emprange_id' => $lead_emprange_id,
      'industry_id' => $industry_id,
      'form_errors' => $form_errors,
      'form_data' => $form_data,
      'users' => array(''=>'Choose') + $users,
      'flash' => $this->flash,
    );

    # build variables
    $controller_vars = array(
      'page_title' => 'CRM - Create Lead',
      'icon_name' => 'fa fa-users',
    );

    return array($this->template->render_view('lead-create', $template_vars),$controller_vars);		
	}

	# lead update action
	public function leadUpdateAction(Request $request) {
    $page_error = $page_success = '';
    $lead_source_id = $lead_status_id = $lead_rating_id = '';
    $industry_id = $lead_emprange_id = '';
    $lead_code = '';

    $form_data = $form_errors = [];
    $users = $users_a = [];

    # form submit
    if(count($request->request->all()) > 0) {
      $form_data = $request->request->all();
      # validate form data
      $lead_code = $request->get('leadCode');
      $form_validation = $this->_validate_form_data($form_data, $lead_code);
      if($form_validation['status'] === false) {
        $form_errors = $form_validation['errors'];
        $message = 'You have errors in the Form. Please fix them before you hit Save.';
        $this->flash->set_flash_message($message, 1);
      } else {
        # hit api and get the status.
        $api_action = $this->lead_model->updateLead($form_data, $lead_code);
        if($api_action['status']) {
          $message = '<i class="fa fa-check-circle-o" aria-hidden="true"></i>&nbsp;Lead updated successfully with code [`'.$lead_code.'`]';
          $this->flash->set_flash_message($message);
          Utilities::redirect('/lead/update/'.$lead_code);
        } else {
          dump($api_action['apierror']);
          $form_errors = Utilities::format_api_error_messages($api_action['apierror']);
          $message = 'You have errors in the Form. Please fix them before you hit Save.';
          $this->flash->set_flash_message($message, 1);
        }
      }
    # get lead details
    } elseif( !is_null($request->get('leadCode')) ) {
      $lead_code = Utilities::clean_string($request->get('leadCode'));
      $lead_details_response = $this->lead_model->leadDetails($lead_code);
      if($lead_details_response === false) {
        $this->flash->set_flash_message('Invalid lead object', 1);
      } else {
        $form_data = $lead_details_response['leadDetails'];
      }
    } else {
      $this->flash->set_flash_message('Invalid lead object (or) lead does not exists',1);         
      Utilities::redirect('/leads/list');
    }

    // # lead owner details.
    // $lead_owner_a = array(
    //   $_SESSION['uid'] => $_SESSION['uname'],
    // );

    // get users from api
    $result = $this->user_model->get_users();
    if($result['status']) {
      $users_a = $result['users'];
      $user_ids = array_column($users_a, 'uid');
      $user_uuids = array_column($users_a, 'uuid');
      $user_ids_uuids = array_combine($user_ids, $user_uuids);
      foreach($users_a as $user_details) {
        $users[$user_details['uuid']] = $user_details['userName'];
      }
      if(isset($user_ids_uuids[$form_data['leadOwnerId']])) {
        $form_data['leadOwnerId'] = $user_ids_uuids[$form_data['leadOwnerId']];
      } else {
        $form_data['leadOwnerId'] = '';
      }
    }

    # prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'page_success' => $page_success,
      'lead_sources_a' => array(''=>'Choose') + CrmUtilities::get_lead_source(),
      'lead_status_a' => array(''=>'Choose') + CrmUtilities::get_lead_status(),
      'lead_ratings_a' => array(''=>'Choose') + CrmUtilities::get_lead_rating(),
      'industries_a' => array(''=>'Choose') + CrmUtilities::get_crm_industries(),
      'emp_ranges_a' =>  array(''=>'Choose') + CrmUtilities::get_employee_range(),
      'titles_a' => array(''=>'Choose') + CrmUtilities::get_titles(),
      'email_optout_a' => array(''=>'Choose') + array(0=>'No', 1=>'Yes'),
      'lead_source_id' => $lead_source_id,
      'lead_status_id' => $lead_status_id,
      'lead_rating_id' => $lead_rating_id,
      'lead_emprange_id' => $lead_emprange_id,
      'industry_id' => $industry_id,
      'users' => array(''=>'Choose') + $users,
      'form_errors' => $form_errors,
      'form_data' => $form_data,
      'flash' => $this->flash,
    );

    # build variables
    $controller_vars = array(
      'page_title' => 'CRM - Update Lead',
      'icon_name' => 'fa fa-users',
    );

    return array($this->template->render_view('lead-update', $template_vars),$controller_vars);
	}

	# lead remove action
	public function leadRemoveAction(Request $request) {
    if( !is_null($request->get('leadCode')) ) {
      $page_no = $request->get('pageNo') !== null ? Utilities::clean_string($request->get('pageNo')) : 1;      
      $lead_code = Utilities::clean_string($request->get('leadCode'));
      $lead_details_response = $this->lead_model->leadDetails($lead_code);
      if($lead_details_response === false) {
        $this->flash->set_flash_message('Invalid lead object', 1);
        Utilities::redirect('/leads/list');
      } else {
        $lead_api_response = $this->lead_model->deleteLead($lead_code);
        if($lead_api_response['status']) {
          $this->flash->set_flash_message('<i class="fa fa-times" aria-hidden="true"></i>&nbsp;Lead removed successfully.');
        } else {
          $this->flash->set_flash_message('<i class="fa fa-times" aria-hidden="true"></i>&nbsp;An error occurred while removing this lead.', 1);          
        }
        Utilities::redirect('/leads/list/'.$page_no);
      }
    } else {
      Utilities::redirect('/leads/list');
    }
	}

	# lead list action
	public function leadListAction(Request $request) {

    $leads = $search_params = $lead_status_a = $lead_ratings_a = [];
    $lead_sources_a = [];

    $total_pages = $total_records = $record_count = $page_no = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;

    $page_error = $page_success = '';

    # check page no and per page variables.
    $page_no = $request->get('pageNo')!== null ? Utilities::clean_string($request->get('pageNo')):1;
    $per_page = $request->get('perPage')!== null ? Utilities::clean_string($request->get('perPage')):100;
    $lead_status_id = $request->get('leadStatusId')!== null ? Utilities::clean_string($request->get('leadStatusId')) : '';

    # hit api and get the status.
    $api_action = $this->lead_model->getAllLeads($page_no, $per_page, $lead_status_id);
    $api_status = $api_action['status'];

    # check api status
    if($api_status) {
      # check whether we got leads or not.
      if(count($api_action['leadsObject']['leads']) >0) {

        $leads = $api_action['leadsObject']['leads'];
        $slno = Utilities::get_slno_start(count($api_action['leadsObject']['leads']),$per_page,$page_no);
        $to_sl_no = $slno+$per_page;

        $slno++;

        if($page_no <= 3) {
          $page_links_to_start = 1;
          $page_links_to_end = 10;
        } else {
          $page_links_to_start = $page_no-3;
          $page_links_to_end = $page_links_to_start+10;
        }
        if($api_action['leadsObject']['total_pages']<$page_links_to_end) {
          $page_links_to_end = $api_action['leadsObject']['total_pages'];
        }
        if($api_action['leadsObject']['this_page'] < $per_page) {
          $to_sl_no = ($slno+$api_action['leadsObject']['this_page'])-1;
        }
        $leads = $api_action['leadsObject']['leads'];
        $total_pages = $api_action['leadsObject']['total_pages'];
        $total_records = $api_action['leadsObject']['total_records'];
        $record_count = $api_action['leadsObject']['total_records'];
      } else {
        $page_error = $api_action['apierror'];
      }
    } else {
      $page_error = $api_action['apierror'];
    }    

    # prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'page_success' => $page_success,
      'leads' => $leads,
      'total_pages' => $total_pages,
      'total_records' => $total_records,
      'record_count' => $record_count,
      'sl_no' => $slno,
      'to_sl_no' => $to_sl_no,
      'page_links_to_start' => $page_links_to_start,
      'page_links_to_end' => $page_links_to_end,
      'current_page' => $page_no,
      'search_params' => $search_params,
      'lead_status_a' => array(''=>'Lead Status') + CrmUtilities::get_lead_status(),
      'lead_sources_a' => CrmUtilities::get_lead_source(),
      'lead_ratings_a' => CrmUtilities::get_lead_rating(),
      'lead_industries_a' => CrmUtilities::get_crm_industries(),
      'lead_status_id' => $lead_status_id,
    );

    # build variables
    $controller_vars = array(
      'page_title' => 'CRM - Leads',
      'icon_name' => 'fa fa-users',
    );

    return array($this->template->render_view('lead-list', $template_vars),$controller_vars);
	}

  # lead details
  public function leadDetailsAction(Request $request) {
  }

  # import leads through xls, ods, xlsx
  public function importLeadsAction(Request $request) {

    # variable assignments.
    $unique_leads = $import_data = $form_errors = [];
    $users = $users_a = [];

    $op_a = ['append' => 'Append to existing data', 'remove' => 'Remove existing data and append'];
    $remove_duplicates_a = [0 => 'No', 1 => 'Yes'];
    $allowed_extensions = ['xlsx'];
    $redirect_url = '/lead/import';
    $matching_attribute = $remove_duplicates = $op = -1;

    # form submit
    if(count($request->request->all()) > 0) {
      $form_data = $request->request->all();
      $validate_data = $this->_validate_import_form_data($form_data);
      # if form is not valid don't process.
      if($validate_data['status'] === false) {
        $matching_attribute = $form_data['matchingAttribute'];
        $remove_duplicates = $form_data['removeDuplicates'];
        $form_errors = $validate_data['errors'];
      } else {
        # check uploaded file information
        $file_details = $_FILES['fileName'];
        $file_name = $file_details['name'];
        $extension = pathinfo($file_name, PATHINFO_EXTENSION);
        $lead_date = Utilities::clean_string($form_data['leadDate']);
        $lead_onwer_id = Utilities::clean_string($form_data['leadOwnerId']);

        // check if we have valid file extension
        if(!in_array($extension, $allowed_extensions)) {
          $this->flash->set_flash_message('Invalid file uploaded. Only .xlsx file formats are allowed',1);
          Utilities::redirect($redirect_url);
        }

        // upload file to server
        $file_upload_path = __DIR__.'/../../../bulkuploads';
        $storage = new \Upload\Storage\FileSystem($file_upload_path);
        $file = new \Upload\File('fileName', $storage);

        $uploaded_file_name = $file->getNameWithExtension();
        $uploaded_file_ext = $file->getExtension();
        if(!in_array($uploaded_file_ext, $allowed_extensions)) {
          $this->flash->set_flash_message('Invalid file extension',1);
          Utilities::redirect($redirect_url);        
        }

        // upload file.
        $new_filename = 'objectUpload_'.time();
        $file->setName($new_filename);
        try {
          $file->upload();
        } catch (\Exception $e) {
          $this->flash->set_flash_message('Unknown error. Unable to upload your file.',1);
          Utilities::redirect($redirect_url);        
        }

        // get file path from uploaded operation.
        $file_path = $file_upload_path.'/'.$new_filename.'.'.$uploaded_file_ext;

        // initiate importer
        $importer = new Importer($file_path, 'lead');
        $imported_leads = $importer->_import_data();

        $remove_duplicates = (bool)$form_data['removeDuplicates'];
        $matching_attribute = CrmUtilities::get_lead_matching_attributes($form_data['matchingAttribute'], false);

        // validate imported leads.
        $validation_response = $this->_validate_imported_leads($imported_leads, $matching_attribute, $remove_duplicates, $lead_onwer_id, $lead_date);
        // dump($validation_response);        
        // exit;
        if($validation_response === false) {
          $this->flash->set_flash_message('Could not upload. You have duplicate data in the uploaded file for matching column.', 1);
          Utilities::redirect($redirect_url);
        } else {
          $unique_leads = $validation_response;
          $import_data['objects'] = $unique_leads;
          $import_data['op'] = $form_data['op'];
        }

        // dump($import_data);
        // exit;

        // upload leads information.
        $insert_response = $this->lead_model->bulkLeadsUpload($import_data);
        if($insert_response['status']) {
          $message = '<i class="fa fa-check-circle-o" aria-hidden="true"></i>&nbsp;Successfully imported '.$insert_response['objectsInserted'].' record(s).';
          $this->flash->set_flash_message($message);
          Utilities::redirect('/leads/list');      
        } else {
          $message = 'An error occurred while importing records.';
          $this->flash->set_flash_message($message,1);
          Utilities::redirect($redirect_url);
        }
      }
    }

    // get users from api
    $result = $this->user_model->get_users();
    if($result['status']) {
      $users_a = $result['users'];
      foreach($users_a as $user_details) {
        $users[$user_details['uuid']] = $user_details['userName'];
      }
    }

    # prepare form variables.
    $template_vars = array(
      'matching_attribs_a' => array('-1' => 'Choose') + CrmUtilities::get_lead_matching_attributes(),
      'remove_duplicates_a' => array('-1' => 'Choose') + $remove_duplicates_a,
      'op_a' => array('-1' => 'Choose') + $op_a,
      'matching_attribute' => $matching_attribute,
      'remove_duplicates' => $remove_duplicates,
      'op' => $op,
      'flash' => $this->flash,
      'form_errors' => $form_errors,
      'users' => array(''=>'Choose') + $users,
    );

    # build variables
    $controller_vars = array(
      'page_title' => 'CRM - Import Leads',
      'icon_name' => 'fa fa-users',
    );

    return array($this->template->render_view('lead-import', $template_vars), $controller_vars);
  }

  # validate form data
  private function _validate_form_data($form_data=[]) {
    $errors = [];
    // $first_name = Utilities::clean_string($form_data['firstName']);
    // $last_name = Utilities::clean_string($form_data['lastName']);
    $business_name = Utilities::clean_string($form_data['businessName']);

    if($business_name === '') {
      $errors['businessName'] = 'Invalid business name.';
      // if($first_name === '') {
      //   $errors['firstName'] = 'Invalid first name.';
      // }
      // if($last_name === '') {
      //   $errors['lastName'] = 'Invalid last name.';
      // }
    }

    if(count($errors) > 0) {
      return [
        'status' => false,
        'errors' => $errors,
      ];
    } else {
      return [
        'status' => true,
      ];  
    }
  }

  # validating imported leads.
  private function _validate_imported_leads($imported_leads=[], $matching_attribute='mobile', $remove_duplicates = false,  $lead_onwer_id='', $lead_date='') {
    $unique_leads = $duplicate_leads = [];
    foreach($imported_leads as $key => $imported_lead_details) {
      if($matching_attribute !== '') {
        if(count($unique_leads)>0) {
          if(array_search($imported_lead_details[$matching_attribute], array_column($unique_leads, $matching_attribute)) === false) {
            $unique_leads[$key] = $imported_lead_details;
          } else {
            $duplicate_leads[$key] = $imported_lead_details;
          }
        } else {
          $unique_leads[$key] = $imported_lead_details;
        }
      } else {
        $unique_leads[$key] = $imported_lead_details;
      }

      $unique_leads[$key]['leadOwnerId'] = $lead_onwer_id;
      $unique_leads[$key]['leadDate'] = $lead_date;
    }

    if($remove_duplicates === false && count($duplicate_leads)>0) {
      return false;
    }

    return $unique_leads;
  }

  # validate import form data
  private function _validate_import_form_data($form_data = '') {
    $form_errors = [];
    $op_a = ['append' => 'Append to existing data', 'remove' => 'Remove existing data and append'];
    $remove_duplicates_a = [0 => 'No', 1 => 'Yes'];
    $matching_attributes = CrmUtilities::get_lead_matching_attributes();

    $op = Utilities::clean_string($form_data['op']);
    $remove_duplicates = Utilities::clean_string($form_data['removeDuplicates']);
    $matching_attribute = Utilities::clean_string($form_data['matchingAttribute']);

    // check uploaded file information
    $file_details = $_FILES['fileName'];
    $file_name = $file_details['name'];
    if(trim($file_name) === '') {
      $form_errors['fileName'] = 'Please upload a file.';
    }
    if( $op < 0 || !in_array($op, array_keys($op_a)) ) {
      $form_errors['op'] = 'Please choose an option';
    }
    if( $remove_duplicates < 0 || !in_array($remove_duplicates, array_keys($remove_duplicates_a)) ) {
      $form_errors['removeDuplicates'] = 'Please choose an option';
    }
    if( (int)$remove_duplicates === 1 && ($matching_attribute < 0 || !in_array($matching_attribute, array_keys($matching_attributes))) ) {
      $form_errors['matchingAttribute'] = 'Please choose an option';
    }

    if(count($form_errors)>0) {
      return array(
        'status' => false,
        'errors' => $form_errors,
      );
    } else {
      return array(
        'status' => true,
      );
    }
  }

}