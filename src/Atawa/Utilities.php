<?php

namespace Atawa;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Atawa\Constants;
use Atawa\ApiCaller;
use Atawa\Config\Config;

use User\Model\User;

class Utilities
{

	public static function redirect($url, $type='external')
	{
    $response = new RedirectResponse($url);
    $response->send();
    exit;
	}

	public static function checkMandatoryParams($data_set=array(), $mand_params=array()) 
	{
		$diff_params = array_diff($mand_params, $data_set);
		if(count($diff_params) > 0) {
			return $diff_params;
		} else {
			return true;
		}
	}

	public static function validateDate($date = '') {
		if(! is_numeric(str_replace('-', '', $date)) ) {
			return false;
		} else {
      $date_a = explode('-', $date);
      if(checkdate($date_a[1],$date_a[0],$date_a[2])) {
        return true;
      } else {
			  return false;
      }
		}
	}

  public static function validateMonth($month = '') {
    if(!is_numeric($month) || $month<=0 || $month>12 ) {
      return false;
    } else {
      return true;
    }
  }

  public static function validateYear($year = '') {
    if(!is_numeric($year) || $year<=2015 ) {
      return false;
    } else {
      return true;
    }
  }   

	public static function getAuthToken($type='access') {
		if( isset($_COOKIE['__ata__']) ) {
			$base64_string = base64_decode($_COOKIE['__ata__']);
			$token = explode('#', $base64_string);
			if( is_array($token) && count($token)>0 ) {
				switch ($type) {
					case 'access':
						return $token[0];
						break;
					case 'refresh':
						return $token[1];
						break;				
					default:
						return false;
						break;
				}
			} else {
				Utilities::redirect('/login');				
			}
		} else {
			Utilities::redirect('/login');
		}
	}

	/**
	 * return starting index of slno
	 */
	public static function get_slno_start($record_count=0, $no_of_records=0, $page_no=1) {
	    
	    $total_records  =   $no_of_records*$page_no;
	    if($record_count==$no_of_records) {
	        $slno       =    $total_records-$no_of_records;
	    } else if($record_count < $no_of_records) {
	        $slno       =    $total_records-($no_of_records);
	    } else {
	        $slno       =    0;
	    }
	    
	    return $slno;
	}

  # removes tags, carriage returns and new lines from string.
  public static function clean_string($string = '') {
  	return trim(str_replace("\r\n",'',strip_tags($string)));
  }

  public static function get_current_client_id() {
    if(isset($_SESSION['ccode'])) {
      return $_SESSION['ccode'];
    } else {
      Utilities::redirect('/login');
    }
  }

  public static function get_logged_in_user_id() {
    if(isset($_SESSION['uid'])) {
      return $_SESSION['uid'];
    } else {
      Utilities::redirect('/login');
    }
  }

  public static function validateName($name='') {
  	if(!preg_match("/^[a-zA-Z ]*$/",$name)) {
  		return false;
  	} else {
  		return true;
  	}
  }

  public static function validateEmail($email='') {
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return false;
    } else {
    	return true;
    }
  }

  public static function validateUrl($url='') {
		if (!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$url)) {
      return false;
    } else {
    	return true;
    }
  }   

  public static function validateMobileNo($mobile_no='') {
  	if( strlen(trim(str_replace("\r\n",'',$mobile_no)))<10 ) {
  		return false;
  	} elseif(!is_numeric($mobile_no)) {
  		return false;
  	}
  	return true;
  }

  # set flash message to be used on other page.
  public static function set_flash_message($message = '', $error=0) {
      if(isset($_SESSION['__FLASH'])) {
        unset($_SESSION['__FLASH']);
      }
      $_SESSION['__FLASH']['message'] = $message;
      $_SESSION['__FLASH']['error']   = $error;
  }

  /**
   * get flash message to be used on other page.
   *
   * @param str $message
   */
  public static function get_flash_message() {
      if(isset($_SESSION['__FLASH'])) {
          $message = $_SESSION['__FLASH']['message'];
          $status  = $_SESSION['__FLASH']['error'];
          unset($_SESSION['__FLASH']);
          return array('message'=>$message, 'error'=>$status);
      } else {
          return '';
      }
  }

  /**
   * print flash message to be used on other page.
   *
   * @param str $message
   */
  public static function print_flash_message($return=true) {

      $flash                  =   Utilities::get_flash_message();
      if(is_array($flash) && count($flash)>0) {
        $flash_message_error  =   $flash['error'];
        $flash_message        =   $flash['message'];
      } else {
        $flash_message        =   '';
      }

      if($flash_message != '' && $flash_message_error) {
        $message =  "<div class='alert alert-danger' role='alert'>
        							<strong>$flash_message</strong>
                    </div>";
      } elseif($flash_message != '') {
        $message =  "<div class='alert alert-success' role='alert'>
        								<strong>$flash_message</strong>
                     </div>";
      } else {
      	$message = '';
      }

      if($return) {
        return $message;
      } else {
        echo $message;
      }
  }

  public static function get_calender_months($index='') {
    $months   =   array(
      1      =>   '1 (January)',
      2      =>   '2 (February)',
      3      =>   '3 (March)',
      4      =>   '4 (April)',
      5      =>   '5 (May)',
      6      =>   '6 (June)',
      7      =>   '7 (July)',
      8      =>   '8 (August)',
      9      =>   '9 (September)',
      10     =>   '10 (October)',
      11     =>   '11 (November)',
      12     =>   '12 (December)',
    );
    if($index != '') {
      return $months[$index];
    } else {
      return $months;
    }
  }

  public static function get_calender_month_names($index='') {
    $months   =   array(
      1      =>   'January',
      2      =>   'February',
      3      =>   'March',
      4      =>   'April',
      5      =>   'May',
      6      =>   'June',
      7      =>   'July',
      8      =>   'August',
      9      =>   'September',
      10     =>   'October',
      11     =>   'November',
      12     =>   'December',
    );
    if($index != '') {
      return $months[$index];
    } else {
      return $months;
    }
  }

  public static function get_calender_month_names_short($index='') {
    $months   =   array(
      1      =>   'Jan',
      2      =>   'Feb',
      3      =>   'Mar',
      4      =>   'April',
      5      =>   'May',
      6      =>   'June',
      7      =>   'July',
      8      =>   'August',
      9      =>   'Sept.',
      10     =>   'Oct',
      11     =>   'Nov',
      12     =>   'Dec',
    );
    if($index != '') {
      return $months[$index];
    } else {
      return $months;
    }
  }  

  public static function get_calender_years($tot_years=4) {
    $current_year = date("Y");
    $years = array();
    for($i=$current_year-1;$i<=$current_year+$tot_years;$i++) {
      $years[$i] = $i;
    }
    return $years;
  }

  public static function print_json_response($response=array(),$encode=true) {
    header('Content-Type: application/json');
    if($encode) {
      echo json_encode($response);
    } else {
      echo $response;
    }
    exit();
  }

  public static function get_api_environment() {
    $business_category = Utilities::get_business_category();
    $environment = $_SERVER['apiEnvironment'];
    $api_urls = Config::get_api_urls();
    return $api_urls[$business_category][$environment];
  }

  public static function get_host_environment_key($environment='') {
    if(isset($_SERVER['appEnvironment']) && $_SERVER['appEnvironment'] !== '') {
      return $_SERVER['appEnvironment'];
    } else {
      return 'local';
    }
  }  

  public static function get_client_details() {
    $client_code = Utilities::get_current_client_id();
    // call api.
    $api_caller = new ApiCaller();
    $response = $api_caller->sendRequest('get','clients/details/'.$client_code);
    $status = $response['status'];
    if($status === 'success') {
      return $response['response']['clientDetails'];
    } elseif($status === 'failed') {
      return false;
    }
  }

  public static function check_access_token() {
    $cookie_validation = true; 
    $cookie_string_a = false; 
    $current_time = time();

    // validate last user logged in time.
    Utilities::check_user_inactivity();      

    // check cookie exists.
    if(!isset($_COOKIE['__ata__']) || $_COOKIE['__ata__']=='') {
      $cookie_validation = false;
    } else {
      # check cookie is properly formatted and valid.
      $cookie_string_a = explode("##",base64_decode(strip_tags($_COOKIE['__ata__'])));
      if(!is_array($cookie_string_a) || count($cookie_string_a)<4) {
        $cookie_validation = false;
      } else {
        $_SESSION['__utype'] = $cookie_string_a[7];
      }
      # check if expiry time sets.
      if(is_numeric($cookie_string_a[2])) {
        $expiry_time = $cookie_string_a[2];
        if($expiry_time<time()) {
          $cookie_validation = false;
        }
      } else {
        $cookie_validation = false;
      }
    }

    // dump($_SESSION);
    // exit;

    // check whether the device is allowed or not. Skip this for Administrator temporarily.
    if( (int)$_SESSION['__utype'] !== 3 && $_SESSION['__just_logged_in'] === false) {
      if( !(isset($_SESSION['__bq_fp']) && isset($_SESSION['__allowed_devices']))) {
        Utilities::redirect('/login');
      } elseif($cookie_validation) {
        $this_device_id = $_SESSION['__bq_fp'];
        $allowed_devices = $_SESSION['__allowed_devices'];
        if(!in_array($this_device_id, $allowed_devices)) {
          #unset cookie immediately
          setcookie('__ata__','',time()-86400);
          unset($_SESSION['ccode'], $_SESSION['uid'], $_SESSION['uname'], 
                $_SESSION['utype'], $_SESSION['bc'], $_SESSION['lc'], 
                $_SESSION['lname'], $_SESSION['token_valid'], $_SESSION['cname']
              );
          Utilities::redirect('/error-device');
        }
      } else {
        session_destroy();
        Utilities::redirect('/login');
      }
    }

    // redirect user to login if anything went wrong.
    if($cookie_validation) {
      if(!isset($_SESSION['uid'])) {
        $_SESSION['cname'] = $cookie_string_a[3];
        $_SESSION['ccode'] = $cookie_string_a[4];
        $_SESSION['uid'] = $cookie_string_a[5];
        $_SESSION['uname'] = $cookie_string_a[6];
        $_SESSION['utype'] = $cookie_string_a[7];
        $_SESSION['bc'] = $cookie_string_a[8];
        $_SESSION['lc'] = $cookie_string_a[9]; 
        $_SESSION['lname'] = $cookie_string_a[10];
      }
      $_SESSION['token_valid'] = true;
      $_SESSION['last_access_time'] = $current_time;
      return true;
    } else {
      unset($_SESSION['token_valid']);
      unset($_SESSION['cname']);
      Utilities::redirect('/login');
    }
  }

  public static function get_fin_payment_methods() {
    return array(
      'c' => 'Cash',
      'b' => 'Bank',
      // 'p' => 'PDC (Bank)',
    );
  }

  public static function process_key_value_pairs($list=array(),$index_key='',$value_key='') {
    $ary = array();
    foreach($list as $list_details) {
      $ary[$list_details[$index_key]] = $list_details[$value_key];
    }
    return $ary;
  }

  public static function get_user_types($user_type='') {
    $user_types = array(
      3 => 'Administrator',
      9 => 'Manager',
      5 => 'Sales operator',
      6 => 'Stores operator',
      7 => 'Purchase operator',
      // 4 => 'Sales executive',
      10 => 'Marketing user',
    );
    if(is_numeric($user_type) && isset($user_types[$user_type])) {
      return $user_types[$user_type];
    } elseif($user_type==='') {
      return $user_types;
    } else {
      return 'Unknown';
    }
  }

  public static function get_user_status($status='') {
    $status_a = array(
      1 => 'Active',
      2 => 'Blocked',
      0 => 'Inactive',
    );
    if(is_numeric($status) && isset($status_a[$status])) {
      return $status_a[$status];
    } elseif($status==='') {
      return $status_a;
    } else {
      return 0;
    }
  }

  public static function get_captcha_keys($host='',$needle='') {
    $captcha_keys = Config::get_captcha_keys();
    if(isset($captcha_keys[$host])) {
      return $needle === 'public' ? $captcha_keys[$host][0] : $captcha_keys[$host][1];
    } else {
      return false;
    }
  }

  public static function acls($role_id='', $path='') {
    $path_a = explode('/', $path);
    if(is_array($path_a) && count($path_a)>3) {
      $path = '/'.$path_a[1].'/'.$path_a[2];
    }
    $denied_permissions = [
      3 => [
      ],
      4 => [
        '/categories/list', '/suppliers/remove', '/opbal/list', '/opbal/add',
        '/opbal/update', '/inventory/stock-adjustment', '/inventory/stock-adjustments-list',
        '/inventory/trash-expired-items', '/fin/supp-opbal', '/fin/supp-opbal',
        '/fin/supp-opbal', '/fin/bank', '/fin/bank', '/fin/bank',
        '/users/list', '/users/update', '/users/create', '/admin-options/enter-bill-no',
        '/admin-options/edit-business-info', '/admin-options/edit-sales-bill', 
        '/admin-options/edit-po', '/admin-options/update-batch-qtys', '/admin-options/delete-sale-bill',
        '/taxes/add', '/taxes/update', '/taxes/list', '/sales-summary-by-month', '/stock-report',
        '/stock-report-new', '/adj-entries',
        '/adj-entries', '/io-analysis', '/inventory-profitability', '/mom-comparison',
        '/admin-options/edit-business-info',
      ],
      5 => [
        '/categories/list', '/suppliers/remove', '/opbal/list', '/opbal/add',
        '/opbal/update', '/inventory/stock-adjustment', '/inventory/stock-adjustments-list',
        '/inventory/trash-expired-items', '/fin/supp-opbal', '/fin/supp-opbal',
        '/fin/supp-opbal', '/fin/bank', '/fin/bank', '/fin/bank',
        '/users/list', '/users/update', '/users/create', '/admin-options/enter-bill-no',
        '/admin-options/edit-business-info', '/admin-options/edit-sales-bill', 
        '/admin-options/edit-po', '/admin-options/update-batch-qtys', '/admin-options/delete-sale-bill',
        '/taxes/add', '/taxes/update', '/taxes/list', '/sales-summary-by-month', '/stock-report',
        '/stock-report-new', '/adj-entries',
        '/adj-entries', '/io-analysis', '/inventory-profitability', '/mom-comparison',
        '/admin-options/edit-business-info',        
      ],
      6 => [
      ],
      7 => [
      ],
    ];

    # validate permission
    if(array_key_exists($role_id, $denied_permissions)) {
      $is_denied = array_search($path, $denied_permissions[$role_id]);
      if($is_denied!==false) {
        Utilities::redirect('/error-404');
      } else {
        return true;
      }
    }

    return false;
  }

  public static function show_batchno_expiry() {
    if(isset($_SESSION['bc']) && (int)$_SESSION['bc']===1 ) {
      return true;
    } else {
      return false;
    }
  }

  public static function get_business_category() {
    if(isset($_SESSION['bc']) && (int)$_SESSION['bc']>0 ) {
      return $_SESSION['bc'];
    } else {
      return 0;
    }
  }

  public static function validate_date($date='') {
    $date_ts = strtotime($date);
    $date_m = date("n", $date_ts);
    $date_d = date("j", $date_ts);
    $date_y = date("Y", $date_ts);
    return checkdate($date_m, $date_d, $date_y);
  }

  /**
   * generates 10 characters unique string.
  **/
  public static function generate_unique_string($length) {
      $token = "";
      $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
      $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
      $codeAlphabet.= "0123456789";
      $max = strlen($codeAlphabet) - 1;
      for ($i=0; $i < $length; $i++) {
          $token .= $codeAlphabet[Utilities::crypto_rand_secure(0, $max)];
      }
      return $token;
  }    

  public static function crypto_rand_secure($min, $max) {
      $range = $max - $min;
      if ($range < 1) return $min; // not so random...
      $log = ceil(log($range, 2));
      $bytes = (int) ($log / 8) + 1; // length in bytes
      $bits = (int) $log + 1; // length in bits
      $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
      do {
              $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
              $rnd = $rnd & $filter; // discard irrelevant bits
      } while ($rnd >= $range);
      return $min + $rnd;
  }

  public static function validate_state_code($state_code='') {
    $states_a = array_keys(Constants::$LOCATION_STATES);
    if(in_array($state_code, $states_a)) {
      return true;
    } else {
      return false;
    }
  }

  public static function validate_gst_no($gst_no='') {
    if(strlen($gst_no) !== 15) {
      return false;
    }
    $state_code = substr($gst_no, 0, 2);
    $pan_no = substr($gst_no, 2, 10);
    $entity_no = substr($gst_no, 10, 1);
    if(!Utilities::validate_state_code($state_code) || !ctype_alnum($pan_no) || !is_numeric($entity_no)) {
      return false;
    }
    return true;
  }

  public static function tax_table() {
    $tax_table = [
      ['from' => 0, 'to' => 1000, 'tax' => 5],
      ['from' => 1001, 'to' => 1000000, 'tax' => 12],      
    ];
    return $tax_table;
  }

  # get tax percent based on the amount.
  public static function get_applicable_tax_percent($taxable_value=0, $item_qty=0) {
    $tax_percent = ['status'=>'fail', 'taxPercent' => 0];
    if(is_numeric($taxable_value) && is_numeric($item_qty)) {
      $per_item_tax_value = round( ($taxable_value/$item_qty), 2);
      if($per_item_tax_value > 0 && $per_item_tax_value <= 1000) {
        $tax_percent['taxPercent'] = 5;
        $tax_percent['status'] = 'success';
      } elseif($per_item_tax_value>1000) {
        $tax_percent['taxPercent'] = 12;
        $tax_percent['status'] = 'success';
      }
    }
    return $tax_percent;
  }

  # return client locations based on user type.
  public static function get_client_locations($with_ids=false, $return_all=false) {
    $client_locations = [];
    $utype = (int)$_SESSION['utype'];
    $user_model = new User;

    if(isset($_SESSION['utype']) && $_SESSION['lc'] && $_SESSION['lname']) {
      $client_locations_resp = $user_model->get_client_locations($with_ids);
      if($client_locations_resp['status']) {
        foreach($client_locations_resp['clientLocations'] as $loc_details) {
          if($with_ids) {
            $location_code = $loc_details['locationCode'].'`'.$loc_details['locationID'];
          } else {
            $location_code = $loc_details['locationCode'];
          }
          $client_locations[$location_code] = $loc_details['locationName'];
        }
      }        
      if( ($utype !== 3 && $utype !== 9 && $utype !== 7) && !$return_all) {
        $client_locations = array_intersect($client_locations, [$_SESSION['lc'] => $_SESSION['lname']]);
      }
    }

    return $client_locations;
  }

  public static function get_credit_note_types() {
    $utype = (int)$_SESSION['utype'];
    if($utype !== 3 && $utype !== 9 && $utype !== 7) {
      return ['lo' => 'Loyalty Programme'];
    } else {
      return ['lo' => 'Loyalty Programme', 'ge' => 'General'];
    }
  }

  public static function get_indian_currency($number=0) {
    $decimal = round($number - ($no = floor($number)), 2) * 100;
    $hundred = null;
    $digits_length = strlen($no);
    $i = 0;
    $str = [];
    $words = [0 => '', 1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five', 6 => 'six',
      7 => 'seven', 8 => 'eight', 9 => 'nine', 10 => 'ten', 11 => 'eleven', 12 => 'twelve',
      13 => 'thirteen', 14 => 'fourteen', 15 => 'fifteen', 16 => 'sixteen', 17 => 'seventeen', 18 => 'eighteen',
      19 => 'nineteen', 20 => 'twenty', 30 => 'thirty', 40 => 'forty', 50 => 'fifty', 60 => 'sixty',
      70 => 'seventy', 80 => 'eighty', 90 => 'ninety'];
    $digits = array('', 'hundred','thousand','lakh', 'crore');
    while( $i < $digits_length ) {
      $divider = ($i == 2) ? 10 : 100;
      $number = floor($no % $divider);
      $no = floor($no / $divider);
      $i += $divider == 10 ? 1 : 2;
      if ($number) {
        $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
        $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
        $str [] = ($number < 21) ? $words[$number].' '. $digits[$counter]. $plural.' '.$hundred:$words[floor($number / 10) * 10].' '.$words[$number % 10]. ' '.$digits[$counter].$plural.' '.$hundred;
      } else {
        $str[] = null;
      }
    }

    $Rupees = implode('', array_reverse($str));
    $paise = ($decimal) ? "." . ($words[$decimal / 10] . " " . $words[$decimal % 10]) . ' Paise' : '';
    return ($Rupees ? 'Rupees '.$Rupees . ' only.' : '');
  }

  // validate the device name from where the client is logged in.
  public static function check_device_name() {
    // don't perform any validation if user is not yet landed on dashboard.
    if(isset($_SESSION['__just_logged_in']) && $_SESSION['__just_logged_in']) {
      return;
    }

    if( (!isset($_SESSION['utype']) && !is_numeric($_SESSION['utype'])) ||
         !isset($_SESSION['__bq_fp']) || !isset($_SESSION['__allowed_devices'])
      ) {
      Utilities::redirect('/login');
    }

    $user_type = $_SESSION['utype'];
    $this_device_id = $_SESSION['__bq_fp'];
    $allowed_devices = $_SESSION['__allowed_devices'];
    switch ($user_type) {
      case 3:
        # code...
        break;
      default:
        if(!in_array($this_device_id, $allowed_devices)) {
          #unset cookie immediately
          setcookie('__ata__','',time()-200400);
          unset($_SESSION['ccode'], $_SESSION['uid'], $_SESSION['uname'], 
                $_SESSION['bc'], $_SESSION['lc'], 
                $_SESSION['lname'], $_SESSION['token_valid'], $_SESSION['cname']
              );
          Utilities::redirect('/error-device');         
        }
        break;
    }
  }

 public static function get_business_user_types($bu_type=0, $return_all=true) {
    $sources = [
      90 => 'Wholesaler',
      91 => 'Marketing Executive',
      92 => 'Sales Executive - ShowRoom',
    ];
    if($return_all) {
      return $sources;
    } elseif(array_key_exists($bu_type, $sources) && (int)$bu_type>0) {
      return $sources[$bu_type];
    }
    return false;
  }

  public static function is_session_started() {
    if (php_sapi_name()!=='cli') {
      if ( version_compare(phpversion(), '5.4.0', '>=') ) {
        return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
      } else {
        return session_id() === '' ? FALSE : TRUE;
      }
    }
    return FALSE;
  }

  public static function download_as_CSV_attachment($file_name='', $headings=[], $records=[]) {
    $csv_keys = array_keys($records[0]);
    $d_file_name = $file_name.'_'.date("d-m-Y H:ia").'.csv';

    // output headers so that the file is downloaded rather than displayed
    header('Content-type: text/csv');
    header('Content-Disposition: attachment; filename="'.$d_file_name.'"');

    // do not cache the file
    header('Pragma: no-cache');
    header('Expires: 0');
     
    // create a file pointer connected to the output stream
    $file = fopen('php://output', 'w');

    // print headings
    if(count($headings)>0) {
      foreach($headings as $heading) {
        fputcsv($file, $heading);        
      }
    }

    // send the column headers
    fputcsv($file, $csv_keys);
    foreach($records as $csv_row) {
      fputcsv($file, $csv_row);
    }
    exit;
  }

  public static function get_barcode_sticker_print_formats() {
    return [
      'indent' => 'Indent Sticker',
      'mrp' => 'MRP Sticker',
      'worate' => 'Sticker Without Rate',
    ];
  }

  public static function is_valid_fin_date($tran_date='') {
    if( isset($_SESSION['finy_s_date']) && isset($_SESSION['finy_e_date']) ) {
      $start_date_ts = strtotime($_SESSION['finy_s_date']);
      $end_date_ts = strtotime($_SESSION['finy_e_date']);
      $tran_date_ts = strtotime($tran_date);
      if($tran_date_ts >= $start_date_ts && $tran_date_ts <= $end_date_ts) {
        return true;
      } else {
        return false;
      }
    } else {
      return false;
    }
  }

  public static function get_org_summary() {
    $api_caller = new ApiCaller();
    $response = $api_caller->sendRequest('get', 'org-summary', []);
    if($response['status'] === 'success') {
      return $response['response'];
    } elseif($response['status'] === 'failed') {
      return false;
    }
  }

  public static function check_user_inactivity($async = false) {
    $current_time = time();
    if(isset($_SESSION['last_access_time']) && $_SESSION['last_access_time'] > 0) {
      $inactive_period = Constants::$GET_SESSION_INACTIVE_PERIOD;
      $session_life = $current_time - $_SESSION['last_access_time'];
      if($session_life >= $inactive_period) {
        // logout from api.
        $login_model = new \User\Model\Login;
        $api_response = $login_model->logout();
        session_destroy();
        if($async === false) {
          Utilities::redirect('/force-logout');
        } else {
          return 'expired';
        }
      } else {
        return md5(time().'QwikBills.V.1.0');
      }
    }
  }

  public static function is_admin() {
    if(isset($_SESSION['utype']) && (int)$_SESSION['utype'] === 3) {
      return true;
    }
    return false;
  }
}