<?php 

namespace User\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Flash;
use Atawa\Template;

use User\Model\Login;

class LoginController
{
	protected $views_path;

	public function __construct() {
		$this->views_path = __DIR__.'/../Views/';
    $this->template = new Template($this->views_path);
    $this->login_model = new Login;
	}

  public function indexAction(Request $request) {

    $error = $site_key = '';
    $login = new Login;

    if( isset($_SESSION['token_valid']) && $_SESSION['token_valid'] === true ) {
      $this->_redirect_to_dashboard();
    }

    $environment = Utilities::get_host_environment_key();
    $site_key = Utilities::get_captcha_keys($environment,'public');      

    if( count($request->request->all()) > 0) {
      $user_id = Utilities::clean_string($request->request->get('userid'));
      $password = Utilities::clean_string($request->request->get('pass'));
      $g_captcha_response = Utilities::clean_string($request->request->get('g-recaptcha-response'));
      if($g_captcha_response !== '') {
        $site_private_key = Utilities::get_captcha_keys($environment,'private');
        $gcaptcha_response = $login->validateGoogleCaptcha($site_private_key,$g_captcha_response);
        if($gcaptcha_response === false) {
          $error = 'Invalid captcha challenge. Try again.';
        } elseif($user_id !== '' && $password !== '' && $login->validateUser($user_id, $password)) {
          $this->_redirect_to_dashboard();
        } else {
          $error = 'Invalid Userid (or) Password';
        }
      } else {
        $error = 'Invalid captcha challenge. Try again.';
      }
    }

    return array('',array('site_key'=>$site_key,'error'=>$error));
  }

  public function logoutAction(Request $request) {
    $expires_in = time()-200400;
    $flash = new Flash;
    if(setcookie('__ata__','',$expires_in)) {
      unset($_SESSION);
      $_SESSION['__logged_out'] = true;

      // hit api and log out user completely.
      $api_response = $this->login_model->logout();      

      Utilities::redirect('/login');
    } else {
      $flash->set_flash_message('Unable to Logout. Please contact administrator',1);
      Utilities::redirect('/dashboard');        
    }
  }

  /* forgot password */
  public function forgotPasswordAction(Request $request) {

    # initialize variables.
    $form_data = array();

    # handle form submit.
    if(count($request->request->all())>0) { # form submitted 
      $form_data = $request->request->all();
    }

    # controller and template variables.
    $controller_vars = array(
      'page_title' => 'Forgot Password',
      'disable_sidebar' => true,
      'disable_footer' => true,
    );

    $template_vars = array(
    );

    # render template
    return array($this->template->render_view('forgot-password',$template_vars), $controller_vars);   
  }

  public function sendOTPAction(Request $request) {

    $response = array(
      'status' => false,
      'error' => 'Invalid operation.',
    );

    # check whether the form is submitted.
    if(count($request->request->all())>0) {
      $form_data = $request->request->all();
      $email_id = $form_data['emailID'];
      $is_email_valid = Utilities::validateEmail($email_id);

      # check for malformed email before hitting api
      if($is_email_valid === false) {
        $response = array(
          'status' => false,
          'error' => 'Invalid email id.',
        );
      }

      # hit api
      $api_response = $this->login_model->sendOTP($email_id);
      if($api_response['status']==='success') {
        $response['status'] = true;
        $response['message'] = 'OTP sent to mobile no. '.$api_response['response']['response']['mobileNo'];
        unset($response['error']);
      } else {
        $response['status'] = false;
        $response['message'] = $api_response['errortext'];
      }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
  } 

  public function resetPasswordAction(Request $request) {

    # check whether the form is submitted.
    if(count($request->request->all())>0) {
      $form_data = $request->request->all();
      $email_id = $form_data['emailID'];
      $otp = $form_data['pass'];
      $new_password = $form_data['password'];

      $is_email_valid = Utilities::validateEmail($email_id);

      # check for malformed email before hitting api
      if($is_email_valid === false) {
        return array(
          'status' => false,
          'error' => 'Invalid email id.',
        );
      }

      # check we have proper OTP.
      if(!is_numeric($otp)) {
        return array(
          'status' => false,
          'error' => 'Invalid OTP.',
        );        
      }

      # check we have proper password.
      if(strlen($new_password)<6) {
        return array(
          'status' => false,
          'error' => 'Invalid password.',
        );
      }

      # hit api
      $api_response = $this->login_model->resetPassword($email_id, $otp, $new_password);
      if($api_response['status']==='failed') {
        $response['status'] = false;
        $response['error'] = $api_response['reason'];
      } else {
        $response['status'] = true;
      }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
  }

  # redirect the user to dashboard based on category.
  private function _redirect_to_dashboard() {
    $bc = isset($_SESSION['bc'])&&$_SESSION['bc']>0?$_SESSION['bc']:0;
    if((int)$bc === 3) {
      Utilities::redirect('/crm-dashboard');
    } else {
      Utilities::redirect('/dashboard');
    }    
  }

  public function idMapper(Request $request) {
    if(count($request->request->all())>0) {
      $form_data = $request->request->all();
      $fingerprint = Utilities::clean_string($form_data['__bq_pub']);
      if($fingerprint !== '') {
        $_SESSION['__bq_fp'] = $fingerprint;
      }
    }
    echo 'QwikBills.V.1.0 - OK...';
    exit;
  }

}