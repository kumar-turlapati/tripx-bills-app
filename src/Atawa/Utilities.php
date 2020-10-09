<?php

namespace Atawa;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Atawa\Constants;
use Atawa\ApiCaller;
use Atawa\Config\Config;

use User\Model\User;

class Utilities
{

  public static function enc_dec_string($action = 'encrypt', $string = '') {
    $token_config = Config::get_enc_dec_data();
    $key = hash('sha256', $token_config['secret_key']);
    // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
    $iv = substr(hash('sha256', $token_config['secret_iv']), 0, 16);
    if( $action === 'encrypt' ) {
      $output = openssl_encrypt($string, $token_config['encrypt_method'], $key, 0, $iv);
      $output = base64_encode($output);
    } elseif( $action === 'decrypt' ){
      $output = openssl_decrypt(base64_decode($string), $token_config['encrypt_method'], $key, 0, $iv);
    }
    return $output;
  }

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
  public static function clean_string($string = '', $breaks_needed=false) {
    if($breaks_needed) {
      return trim(strip_tags($string));
    } else {
    	return trim(str_replace("\r\n",'',strip_tags($string)));
    }
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
  	if( strlen(trim(str_replace("\r\n",'',$mobile_no))) !== 10 ) {
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

    $user_types_a = [3, 9, 10, 12];

    // check whether the device is allowed or not. Skip this for Administrator temporarily.
    if( in_array((int)$_SESSION['__utype'], $user_types_a) === false && $_SESSION['__just_logged_in'] === false) {
      if( !(isset($_SESSION['__bq_fp']) && isset($_SESSION['__allowed_devices']))) {
        Utilities::redirect('/login');
      } elseif($cookie_validation) {
        $this_device_id = $_SESSION['__bq_fp'];
        $allowed_devices = $_SESSION['__allowed_devices'];
        if(isset($_SESSION['uidn']) && is_numeric($_SESSION['uidn'])) {
          $cookie_name = 'qbdid'.$_SESSION['uidn'];
        } else {
          Utilities::redirect('/error');
        }        

        // before validating in devices array from server 
        // validate whether we have a cookie or not.
        if(isset($_COOKIE[$cookie_name]) && $_COOKIE[$cookie_name] !== '') {
          // set dec device id in this device id.
          $this_device_id = Utilities::enc_dec_string('decrypt', $_COOKIE[$cookie_name]);
          // var_dump($this_device_id);
          // exit;
        }

        if(!in_array($this_device_id, $allowed_devices)) {
          // unset cookie immediately
          @setcookie('__ata__','',time()-86400);

          // setcookie for Device.
          Utilities::set_device_cookie($this_device_id);

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
      $_SESSION['cname'] = $cookie_string_a[3];
      $_SESSION['ccode'] = $cookie_string_a[4];
      $_SESSION['uid'] = $cookie_string_a[5];
      $_SESSION['uname'] = $cookie_string_a[6];
      $_SESSION['utype'] = $cookie_string_a[7];
      $_SESSION['bc'] = $cookie_string_a[8];
      $_SESSION['lc'] = $cookie_string_a[9]; 
      $_SESSION['lname'] = $cookie_string_a[10];
      $_SESSION['editable_mrps'] = $cookie_string_a[11];
      $_SESSION['uidn'] = $cookie_string_a[12];
      $_SESSION['allow_man_discount'] = $cookie_string_a[13];
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
      10 => 'Marketing user',
      12 => 'Business head',
      13 => 'Dispatch clerk',
      14 => 'Sales Operator - Simple',
      15 => 'Floor incharge (Godown ops)',
      16 => 'Sales Operator - Multi Location/Store',
      127 => 'App user',
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
    if(is_array($path_a) && count($path_a) >= 3 && $path_a[1] !== 'print-grn' && $path_a[1] !== 'sales-invoice-b2b') {
      $path = '/'.$path_a[1].'/'.$path_a[2];
    } elseif(is_array($path_a)) {
      $path = '/'.$path_a[1];
    }
    // dump($path_a, $path);
    // exit;

    $allowed_pages = [

      // for Manager
      9 => [

        '/dashboard', '/error-404', '/logout', '/device/show-name', '/me', '/',

        '/async/day-sales', '/async/monthly-sales', '/async/itemsAc', '/async/brandAc', '/async/custAc',
        '/async/finyDefault', '/async/itd', '/async/sdiscount',

        '/sales/list', '/sales-return/view', '/sales-return/list', '/sales/view-invoice', 
        '/promo-offers/list', '/loyalty-members/list', '/inward-entry/list', '/inward-entry/view',
        '/grn/list', '/purchase-return/register', '/fin/payment-vouchers',
        '/fin/receipt-vouchers', '/fin/credit-notes', '/fin/petty-cash-book', '/stock-transfer/register',
        '/barcodes/list', '/sales-indents/list', '/inventory/available-qty', '/inventory/track-item',
        '/sales/search-bills', '/purchases/search-bills',  '/sales-indent/update-status',

        '/reports/stock-report', '/reports/opbal', '/reports/sales-register', '/reports/itemwise-sales-register', '/reports/sales-summary-by-month', 
        '/reports/day-sales', '/reports/sales-by-tax-rate', '/reports/po-register', '/reports/po-register-itemwise', '/reports/payables',
        '/reports/receivables', '/reports/item-master', '/reports/customer-master',

        '/reports/item-master-with-barcodes', '/reports/sales-billwise-itemwise', 
        '/reports/sales-billwise-itemwise-casewise', '/reports/sales-dispatch-register',

        '/reports/stock-transfer-register', '/reports/stock-adjustment-register',
        '/reports/po-return-register',

        '/report-options/indent-item-avail', '/report-options/indent-itemwise',
        '/report-options/indent-agentwise', '/report-options/indent-statewise', 
        '/report-options/print-indents-agentwise', '/report-options/indent-register',
        '/report-options/indent-dispatch-summary',

        '/indent-item-avail', '/indent-itemwise', '/indent-agentwise', '/indent-statewise',
        '/print-indents-agentwise', '/indent-register', '/indent-dispatch-summary',        

        '/finy/switch', '/discount-manager',

        '/indent-vs-sales', '/indent-vs-sales-by-item',
        '/print-indent', '/print-indent-wor',

        '/leads/list', '/lead/create', '/lead/update', '/lead/import', '/lead/remove',
        '/tasks/list', '/task/create', '/task/update', '/task/remove',
        '/appointments/list', '/appointment/create', '/appointment/update', '/appointment/remove',
      ],

      // for Business head
      12 => [

        '/dashboard', '/error-404', '/logout', '/device/show-name', '/me', '/',

        '/async/day-sales', '/async/monthly-sales', '/async/itemsAc', '/async/brandAc', '/async/custAc',
        '/async/finyDefault', '/async/itd', '/async/sdiscount',

        '/sales/list', '/sales-return/view', '/sales-return/list', '/sales/view-invoice', 
        '/promo-offers/list', '/loyalty-members/list', '/inward-entry/list', '/inward-entry/view',
        '/grn/list', '/purchase-return/register', '/fin/payment-vouchers',
        '/fin/receipt-vouchers', '/fin/credit-notes', '/fin/petty-cash-book', '/stock-transfer/register',
        '/barcodes/list', '/sales-indents/list', '/inventory/available-qty', '/inventory/track-item',
        '/sales/search-bills', '/purchases/search-bills',

        '/reports/stock-report', '/reports/opbal', '/reports/sales-register', '/reports/itemwise-sales-register', '/reports/sales-summary-by-month', 
        '/reports/day-sales', '/reports/sales-by-tax-rate', '/reports/po-register', '/reports/po-register-itemwise', '/reports/payables',
        '/reports/receivables', '/reports/item-master', '/reports/customer-master', 

        '/reports/inventory-profitability', '/reports/material-movement',

        '/reports/item-master-with-barcodes', '/reports/sales-billwise-itemwise', 
        '/reports/sales-billwise-itemwise-casewise',

        '/reports/stock-transfer-register', '/reports/stock-adjustment-register',
        '/reports/po-return-register',

        '/finy/switch', '/discount-manager',

        '/indent-vs-sales', '/indent-vs-sales-by-item',
      ],      

      // for Sales Operator
      5 => [

        '/dashboard', '/error-404', '/logout', '/device/show-name', '/me', '/',

        '/async/day-sales', '/async/itemsAc', '/async/brandAc', '/async/custAc', '/async/getAvailableQty', '/async/getItemDetailsByCode',
        '/async/finyDefault', '/async/getTrDetailsByCode', '/async/itd', '/async/getComboItemDetails',
        '/async/get-tax-percent', '/async/getBillNos',

        '/sales/entry', '/sales/entry-with-barcode', '/sales/list', '/sales/search-bills', '/sales/view-invoice',
        '/sales-entry/combos', '/sales/entry-with-indent', '/sales-indent/create',

        '/print-sales-bill-small', '/print-sales-bill', '/sales-invoice-b2b', '/sales/shipping-info',

        '/sales-return/entry', '/sales-return/view', '/sales-return/list', '/print-sales-return-bill',

        '/stock-audit/create', '/stock-audit/update', '/stock-audit/print', '/stock-audit/register',
        '/stock-audit/items', '/sales-indents/list', '/fin/sales2cb', '/fin/post-sales2cb',

        '/products/list', '/categories/list',

        '/mfgs/list',

        '/inventory/track-item', '/inventory/search-products', 

        '/customers/create', '/customers/update', '/customers/view', '/customers/list', 

        '/fin/cash-voucher', '/fin/cash-vouchers', '/fin/cash-book', '/fin/credit-note', '/fin/credit-notes',
        '/fin/receipt-vouchers',

        '/taxes/list', '/fin/receipt-voucher',

        '/promo-offers/list', 

        '/stock-transfer/out', '/stock-transfer/register', '/stock-transfer/choose-location',
        '/stock-transfer/validate',

        '/loyalty-member/add', '/loyalty-member/update', '/loyalty-members/list', '/loyalty-member/ledger', 

        '/barcodes/list', '/barcodes/print', '/barcode/opbal',

        '/reports/sales-register', '/reports/itemwise-sales-register', '/reports/day-sales', '/reports/sales-summary-by-month', 
        '/reports/sales-by-tax-rate', '/reports/sales-billwise-itemwise', '/reports/sales-upi-register', '/reports/receivables',
        '/reports/sales-billwise-itemwise-casewise',

        '/reports/stock-transfer-register', '/reports/stock-adjustment-register',

        '/finy/switch', '/discount-manager',

        '/indent-vs-sales', '/indent-vs-sales-by-item',

        '/tasks/list', '/task/create', '/task/update', '/task/remove',
      ],

      6  => [
      ],

      // for Purchase Operator
      7  => [

        '/dashboard', '/error-404', '/logout', '/device/show-name', '/me', '/',

        '/async/itemsAc', '/async/brandAc', '/async/custAc', '/async/get-supplier-details',
        '/async/finyDefault', '/async/itd', '/async/suppAc', '/async/getAvailableQty',

        '/products/list', '/categories/list', '/mfgs/list', '/suppliers/list',
        '/taxes/list', '/fin/supp-opbal/list', '/inward-entry', '/inward-entry/bulk-upload',
        '/stock-transfer/choose-location', '/stock-transfer/out', '/inward-entry/list', '/grn/list', '/stock-transfer/register',
        '/purchase-return/register', '/barcodes/list', '/purchases/search-bills', '/inventory/track-item',
        '/opbal/list', '/reports/stock-report', '/reports/opbal', '/reports/item-master',
        '/reports/po-register', '/reports/po-register-itemwise',

        '/products/update', '/products/create', '/category/create', '/category/update',
        '/mfg/create', '/mfg/update', '/suppliers/create', '/taxes/add', '/taxes/update',

        '/fin/supp-opbal', '/inventory/available-qty',

        '/barcode/generate', '/inward-entry/view', '/purchase-return/entry', '/fin/debit-note',
        '/fin/debit-notes',
        '/grn/view', '/print-grn', '/grn/create', 

        // '/opbal/add', '/opbal/update', '/barcodes/print', '/barcode/opbal', 
        '/barcodes/print', '/barcode/opbal', 

        '/reports/item-master-with-barcodes', '/reports/item-master',

        '/reports/stock-transfer-register', '/reports/stock-adjustment-register',
        '/reports/po-return-register',

        '/tasks/list', '/task/create', '/task/update', '/task/remove',

        '/finy/switch',
      ],

      // for Marketing user
      10 => [

        '/async/finyDefault', '/async/getItemDetailsByCode', '/async/itemsAc', '/async/brandAc', '/async/custAc',
        '/async/getItemBatchesByCode', 

        '/dashboard', '/error-404', '/logout', '/device/show-name', '/me', '/',
        
        '/finy/switch', '/customers/list', '/sales-indents/list', '/barcodes/list',
        '/sales-indent/create', '/sales-indent/create/mobile', '/fin/receipt-voucher/create',
        '/campaigns/list', '/sales-indent/update', '/finy/list',

        '/print-indent', '/print-indent-wor', 

        '/report-options/indent-item-avail', '/report-options/indent-itemwise',
        '/report-options/indent-agentwise', '/report-options/indent-statewise', 
        '/report-options/print-indents-agentwise', '/report-options/indent-register',
        '/report-options/indent-dispatch-summary',

        '/indent-item-avail', '/indent-itemwise', '/indent-agentwise', '/indent-statewise',
        '/print-indents-agentwise', '/indent-register', '/indent-dispatch-summary',

        '/indent-vs-sales', '/indent-vs-sales-by-item',

        '/leads/list', '/lead/create', '/lead/update', '/lead/import', '/lead/remove',
        '/tasks/list', '/task/create', '/task/update', '/task/remove',
        '/appointments/list', '/appointment/create', '/appointment/update', '/appointment/remove',
      ],

      13 => [
        '/dashboard', '/error-404', '/logout', '/device/show-name', '/me', '/',

        '/async/getTrDetailsByCode', '/async/finyDefault', '/async/getItemDetailsByCode',

        '/stock-transfer/validate', '/stock-transfer/register', '/stock-transfer/out',

        '/finy/switch', '/gate-pass/entry', '/get-invoice-no', '/sales/list', '/sales/view-invoice',

        '/gate-pass/register',
      ],

      15 => [
        '/dashboard', '/error-404', '/logout', '/device/show-name', '/me', '/',
        '/async/itemsAc', '/async/brandAc', '/async/custAc', '/async/updateRackNo',
        '/async/finyDefault',

        '/sales-indents/list', '/campaigns/list', '/inventory/available-qty', '/products/list',
        '/products/update',

        '/finy/switch',

        '/print-indent', '/print-indent-wor',
      ],

    ];

    // dump($path);
    // exit;

    // validate permission
    if(array_key_exists($role_id, $allowed_pages) && (int)$role_id !== 3) {
      $is_allowed = array_search($path, $allowed_pages[$role_id]);
      if($is_allowed === false ) {
        Utilities::redirect('/error-404?source='.$path);
      }
    }
    return true;
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
  public static function get_applicable_tax_percent($taxable_value=0, $item_qty=0, $hsn_sac_code='', $domain='cl') {
    $tax_percent = ['status'=>'fail', 'taxPercent' => 0];
    if(is_numeric($taxable_value) && is_numeric($item_qty)) {
        /*apply logic for clothing */
      if($domain === 'cl') {
        /* apply tax only for hsn code 61 and 62 */
        if(substr($hsn_sac_code, 0, 2) === '61' || substr($hsn_sac_code, 0, 2) === '62') {
          $per_item_tax_value = round( ($taxable_value/$item_qty), 2);
          if($per_item_tax_value > 0 && $per_item_tax_value <= 1000) {
            $tax_percent['taxPercent'] = 5;
            $tax_percent['status'] = 'success';
          } elseif($per_item_tax_value>1000) {
            $tax_percent['taxPercent'] = 12;
            $tax_percent['status'] = 'success';
          }
        }
      }
    }
    return $tax_percent;
  }

  # return client locations based on user type.
  public static function get_client_locations($with_ids=false, $return_all=false, $remove_inactive=false) {
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
          // remove inactive stores based on condition.
          if($remove_inactive) {
            if((int)$loc_details['status'] === 1) {
              $client_locations[$location_code] = $loc_details['locationName'];
            }
          } else {
            $client_locations[$location_code] = $loc_details['locationName'];
          }
        }
      }
      if( ($utype !== 3 && $utype !== 9 && $utype !== 7 && $utype !== 12 && $utype !== 16 && $utype !== 13) && !$return_all) {
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
    return ($Rupees ? 'Rupees '.trim($Rupees) . ' only.' : '');
  }

  // validate the device name from where the client is logged in.
  public static function check_device_name() {
    // don't perform any validation if user is not yet landed on dashboard.
    if(isset($_SESSION['__just_logged_in']) && $_SESSION['__just_logged_in']) {
      return;
    }

    // dump($_SESSION);
    // exit;

    if( !isset($_SESSION['utype']) || 
        !isset($_SESSION['__bq_fp']) || 
        !isset($_SESSION['__allowed_devices']) 
      ) {
      Utilities::redirect('/login');
    }

    $user_type = (int)$_SESSION['utype'];
    $allowed_devices = $_SESSION['__allowed_devices'];

    // dump('in check device name....', $_SESSION);
    if(isset($_SESSION['uidn']) && is_numeric($_SESSION['uidn'])) {
      $cookie_name = 'qbdid'.$_SESSION['uidn'];
    } else {
      Utilities::redirect('/error');
    }

    // dump($cookie_name);
    // exit;

    // before validating in devices array from server 
    // validate whether we have a cookie or not.
    if(isset($_COOKIE[$cookie_name]) && $_COOKIE[$cookie_name] !== '') {
      // set dec device id in this device id.
      $this_device_id = Utilities::enc_dec_string('decrypt', $_COOKIE[$cookie_name]);
    } else {
      $this_device_id = $_SESSION['__bq_fp'];
    }

    $user_types_a = [3, 9, 10, 12];

    // dump($this_device_id);
    // exit;
/*    switch ($user_type) {
      case 3:
        # code...
        break;
      default:
        if(in_array($this_device_id, $allowed_devices) === false) {
          // unset cookie immediately
          setcookie('__ata__','',time()-200400);

          // set device cookie.
          Utilities::set_device_cookie($this_device_id, $cookie_name);

          unset($_SESSION['ccode'], $_SESSION['uid'], $_SESSION['uname'], 
                $_SESSION['bc'], $_SESSION['lc'], 
                $_SESSION['lname'], $_SESSION['token_valid'], $_SESSION['cname']
              );
          Utilities::redirect('/error-device');         
        }
        break;
    } 3, 9, 10, 12*/

    // ignore device validation for below roles.
    if(in_array($user_type, $user_types_a) === false) {
      // if device id does not matched with allowed devices 
      // setup the new device cookie and redirect to error page.
      if(in_array($this_device_id, $allowed_devices) === false) {
        // unset cookie immediately
        setcookie('__ata__','',time()-200400);

        // set device cookie.
        Utilities::set_device_cookie($this_device_id, $cookie_name);

        unset($_SESSION['ccode'], $_SESSION['uid'], $_SESSION['uname'], 
              $_SESSION['bc'], $_SESSION['lc'], 
              $_SESSION['lname'], $_SESSION['token_valid'], $_SESSION['cname']
        );
        Utilities::redirect('/error-device');         
      }
    }

  }

 public static function get_business_user_types($bu_type=0, $return_all=true) {
    $sources = [
      90 => 'Wholesaler/Agent',
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
      'indent2' => 'Indent Sticker - By Item',
      'mrp' => 'MRP Sticker',
      'worate' => 'Sticker Without Rate',
      'sku-small' => 'Warehouse Sticker with Case/Container/Box No.',
      'wh-large' => 'Warehouse Sticker - Large',
    ];
  }

  public static function is_valid_fin_date($tran_date = '') {
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

  public static function get_real_user_ip() {
    if(!empty($_SERVER['HTTP_CLIENT_IP'])) {    //check ip from share internet
      $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {    //to check ip is pass from proxy
      $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
      $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
  }

  public static function is_mobile_device() {
    return preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$_SERVER['HTTP_USER_AGENT'])||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($_SERVER['HTTP_USER_AGENT'],0,4));
  }
  
  public static function get_number_of_days_in_month($month_name='', $year=0) {
    $months    =   array(
      1       =>   31,
      2       =>   28,
      3       =>   31,
      4       =>   30,
      5       =>   31,
      6       =>   30,
      7       =>   31,
      8       =>   31,
      9       =>   30,
      10      =>   31,
      11      =>   30,
      12      =>   31,
    );

    if($year>0 && Utilities::is_leap_year($year)) {
      $months[2] = 29;
    }
    return $months[(int)$month_name];
  }

  public static function is_leap_year($year) {
    return ((($year % 4) == 0) && ((($year % 100) != 0) || (($year %400) == 0)));
  }  

  public static function _set_fin_start_end_dates($response=[]) {
    if(isset($response['startDate']) && isset($response['endDate'])) {
      $_SESSION['finy_s_date'] = $response['startDate'];
      $_SESSION['finy_e_date'] = $response['endDate']; 
    } else {
      $_SESSION['finy_s_date'] = '1981-08-24';
      $_SESSION['finy_e_date'] = '1981-08-24';      
    }
  }

  public static function get_location_state_name($state_id = '') {
    $states_a = Constants::$LOCATION_STATES;
    if(isset($states_a[$state_id])) {
      return $states_a[$state_id];
    } else {
      return '';
    }
  }

  public static function is_mrp_editable() {
    return 
      (isset($_SESSION['editable_mrps']) && (int)$_SESSION['editable_mrps'] === 1) ||
      (isset($_SESSION['__utype']) && (int)$_SESSION['__utype'] === 3)
      ? true 
      : false;
  }

  public static function is_manual_discount_allowed() {
    return 
      (isset($_SESSION['allow_man_discount']) && (int)$_SESSION['allow_man_discount'] === 1) ||
      (isset($_SESSION['__utype']) && (int)$_SESSION['__utype'] === 3)
      ? true 
      : false;
  }  

  public static function set_device_cookie($device_id = '', $cookie_name='') {
    $enc_device_id = Utilities::enc_dec_string('encrypt', $device_id);
    if(!isset($_COOKIE[$cookie_name])) {
      @setcookie($cookie_name, $enc_device_id, time()+60*60*24*30);
    }
  }

  public static function get_country_name($country_id = 99) {
    return 'India';
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

  public static function get_logout_url() {
    $bc = Utilities::get_business_category();
    $environment = $_SERVER['appEnvironment'];
    return Config::get_logout_urls($bc, $environment);
  }  

}
