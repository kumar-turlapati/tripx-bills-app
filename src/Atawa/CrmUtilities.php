<?php

namespace Atawa;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Atawa\Constants;
use Atawa\ApiCaller;
use Atawa\Config\Config;

class CrmUtilities
{

  public static function render_dropdown($dd_name='', $dd_options=[], $dd_default_value='') {
    $output = '<select '.'id='.$dd_name.' name='.$dd_name.' class="form-control">';
    foreach($dd_options as $dd_key => $dd_value) {
      if($dd_key == $dd_default_value) {
        $output .= '<option value="'.$dd_key.'" selected>'.$dd_value.'</option>';
      } else {
        $output .= '<option value='.$dd_key.'>'.$dd_value.'</option>';
      }
    }
    $output .= '</select>';
    return $output;
  }  

  # return lead sources or lead source name
  public static function get_lead_source($source_id=0, $return_all=true) {
    $sources = [
      10 => 'Advertisement', 11 => 'Cold Call', 12 => 'Employee Referral', 
      13 => 'External Referral', 14 => 'Online Store', 15 => 'Partner', 
      16 => 'Public Relations', 17 => 'Sales Email Alias', 18 => 'Seminar Partner', 
      19 => 'Internal Seminar', 20 => 'Trade Show', 21 => 'Web Download', 
      22 => 'Web Research', 23 => 'Chat',
    ];
    asort($sources);

    if($return_all) {
      return $sources;
    } elseif(isset($sources[$source_id])) {
      return $sources[$source_id];
    }
    return false;    
  }

  # get lead statuses or lead status
  public static function get_lead_status($status_id=0, $return_all=true) {
    $sources = [
      90 => 'Attempted to contact', 91 => 'Contact in future', 92 => 'Contacted', 
      93 => 'Junk lead', 94 => 'Lost lead', 95 => 'Not contacted', 
      96 => 'Prequalified'
    ];
    asort($sources);

    if($return_all) {
      return $sources;
    } elseif(isset($sources[$status_id])) {
      return $sources[$status_id];
    }
    return false;
  }

  # titles
  public static function get_titles($status_id=0, $return_all=true) {
    $sources = [
      'Mr.' => 'Mr.', 'Ms.' => 'Ms.', 'M/s' => 'M/s', 
    ];
    asort($sources);

    if($return_all) {
      return $sources;
    } elseif(isset($sources[$status_id])) {
      return $sources[$status_id];
    }
    return false;
  }

  # get lead rating
  public static function get_lead_rating($rating_id=0, $return_all=true) {
    $sources = [
      70 => 'Acquired', 71 => 'Active', 72 => 'Market Failed', 
      73 => 'Project Canceled', 74 => 'Shut Down'
    ];
    asort($sources);

    if($return_all) {
      return $sources;
    } elseif(isset($sources[$rating_id])) {
      return $sources[$rating_id];
    }
    
    return false;    
  }

  # get crm industries
  public static function get_crm_industries($industry_id=0, $return_all=true) {
    $sources = [
      20 => 'Agriculture & Allied Industries', 
      21 => 'Automobiles',
      22 => 'Auto Components', 
      23 => 'Aviation',
      24 => 'Banking', 
      26 => 'Cement',
      27 => 'Consumer Durables', 
      29 => 'Education & Training', 
      30 => 'Engineering & Capital Goods', 
      31 => 'Financial Services', 
      32 => 'Gems & Jewellery',
      33 => 'Healthcare', 
      34 => 'Infrastructure', 
      35 => 'Insurance', 
      36 => 'IT & ITES',
      37 => 'Manufacturing', 
      38 => 'Media & Entertainment', 
      39 => 'Oil & Gas', 
      40 => 'Pharmaceuticals',
      41 => 'Ports', 
      42 => 'Real Estate', 
      43 => 'Retail', 
      44 => 'Science & Technology',
      45 => 'Services', 
      46 => 'Steel', 
      47 => 'Telecommunications', 
      48 => 'Textiles', 
      49 => 'Tourism & Hospitality',
      50 => 'Apparel', 
      51 => 'Cotton', 
      52 => 'Electronic & Computer Software',
      55 => 'Leather', 
      56 => 'Pharmaceutical', 
      57 => 'Plastics', 
      58 => 'Sports', 
      59 => 'Tobacco', 
      60 => 'Ecommerce',
      61 => 'FMCG',
    ];
    asort($sources);

    if($return_all) {
      return $sources;
    } elseif(isset($sources[$industry_id])) {
      return $sources[$industry_id];
    }
    
    return false;
  }

  # get employee range
  public function get_employee_range($emp_range_id=0, $return_all=true) {
    $sources = [
      101 => '0 to 10',
      102 => '10 to 50',
      103 => '50 to 100',
      104 => 'above 100',
    ];

    if($return_all) {
      return $sources;
    } elseif(isset($sources[$emp_range_id])) {
      return $sources[$emp_range_id];
    }
    
    return false;
  }
  
  public static function format_api_error_messages($error_string='') {
    $api_errors = [];
    $errors_a = explode('|',explode('#', $error_string)[1]);
    foreach($errors_a as $key => $error_details) {
      $field_details = explode('=', $error_details);
      if(is_array($field_details) && count($field_details) > 0) {
        $api_errors[$field_details[0]] = $field_details[1];
      }
    }
    return $api_errors;
  }

  public static function get_lead_matching_attributes($field_code='', $return_all=true) {
    $sources =  [
      'businessName',
      'email',
      'phone',
      'mobile'
    ];
    if($return_all) {
      return $sources;
    } elseif(isset($sources[$field_code])) {
      return $sources[$$field_code];
    } else {
      return '';
    }
  }

  public static function get_custom_field_data_types($field_code='', $return_all=true) {
    $sources = [
      '01' => 'Number',
      '02' => 'Auto Number',
      '03' => 'Text',
      '04' => 'Date',
      '05' => 'Dropdown',
      '06' => 'Dropdown - Multiple',
      '07' => 'URL',
      '08' => 'Text',
      '09' => 'Email',
    ];
    asort($sources);

    if($return_all) {
      return $sources;
    } elseif(isset($sources[$field_code])) {
      return $sources[$field_code];
    }
    
    return false;
  }

  // get task types
  public function get_task_types($task_type_id=0, $return_all=true) {
    $sources = [
      600 => 'Follow up',
      601 => 'Call reminder',
      602 => 'Sales actitivity',
      603 => 'Marketing activity',
      604 => 'Lead sourcing',
      604 => 'Facebook activity',
      604 => 'Email campaign',
      604 => 'Tele calling',
      604 => 'Cold calling',
    ];
    if($return_all) {
      return $sources;
    } elseif(isset($sources[$task_type_id])) {
      return $sources[$task_type_id];
    }
    return false;
  }

  // get appointment types
  public function get_appointment_types($appointment_type_id=0, $return_all=true) {
    $sources = [
      1200 => 'Virtual',
      1201 => 'Physical',
    ];
    if($return_all) {
      return $sources;
    } elseif(isset($sources[$appointment_type_id])) {
      return $sources[$appointment_type_id];
    }
    return false;
  }

  // get appointment status
  public function get_appointment_status($appointment_status_id=0, $return_all=true) {
    $sources = [
      1300 => 'To Do',
      1301 => 'In Progress',
      1302 => 'Cancelled',
      1303 => 'Completed',
    ];
    if($return_all) {
      return $sources;
    } elseif(isset($sources[$appointment_status_id])) {
      return $sources[$appointment_status_id];
    }
    return false;
  }

  // get appointment response
  public function get_appointment_response($appointment_response_id=0, $return_all=true) {
    $sources = [
      704 => 'Tobe added',
      700 => 'Interested',
      701 => 'Not interested',
      702 => 'Unable to reach',
      703 => 'No Response',
    ];
    if($return_all) {
      return $sources;
    } elseif(isset($sources[$appointment_response_id])) {
      return $sources[$appointment_response_id];
    }
    return false;
  }  

  // get task types
  public function get_task_response($task_response_id=0, $return_all=true) {
    $sources = [
      704 => 'Tobe added',
      700 => 'Interested',
      701 => 'Not interested',
      702 => 'Unable to reach',
      703 => 'No Response',
    ];
    if($return_all) {
      return $sources;
    } elseif(isset($sources[$task_response_id])) {
      return $sources[$task_response_id];
    }
    return false;
  }

  // get task types
  public function get_appointment_purpose($appt_purpose_id=0, $return_all=true) {
    $sources = [
      1100 => 'Marketing activity',
      1101 => 'Sales activity',
      1102 => 'Payment followup',
    ];
    if($return_all) {
      return $sources;
    } elseif(isset($sources[$appt_purpose_id])) {
      return $sources[$appt_purpose_id];
    }
    return false;
  }  

  // get task status
  public function get_task_status($task_status_id=0, $return_all=true) {
    $sources = [
      800 => 'To Do',
      801 => 'In Progress',
      802 => 'Completed',
    ];
    if($return_all) {
      return $sources;
    } elseif(isset($sources[$task_status_id])) {
      return $sources[$task_status_id];
    }
    return false;
  }
}