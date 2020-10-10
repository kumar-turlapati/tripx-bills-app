<?php 

namespace User\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Atawa\Utilities;
use Atawa\Template;

class DashBoardController
{
	protected $views_path;

	public function __construct() {
		$this->views_path = __DIR__.'/../Views/';
	}

  public function indexAction(Request $request) {

    $sel_location_code = $request->get('lc') !== null && $request->get('lc') !== '' ? Utilities::clean_string($request->get('lc')) : '';

    // ---------- get location codes from api -----------------------
    $client_locations = Utilities::get_client_locations(false, false, true);
    // dump($client_locations);
    // exit;

    // prepare form variables.
    $template_vars = array(
      'cal_months' => Utilities::get_calender_months(),
      'cal_years' => Utilities::get_calender_years(1),
      'cur_month' => date('m'),
      'cur_year' => date('Y'),
      'mon_year_string' => date('F, Y'),
      'today' => date("dS F, Y"),
      'client_locations' => ["" => 'All Locations / Stores'] + $client_locations,
      'sel_location_code' => $sel_location_code,
      'sel_location_name' => $sel_location_code !== '' ? $client_locations[$sel_location_code] : '',
    );

    // make session variable to false.
    $_SESSION['__just_logged_in'] = false;

    // build variables
    $controller_vars = array(
      // 'show_page_name' => false,
      'page_title' => 'Dashboard',
      'icon_name' => 'fa fa-tachometer',      
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('dash-board',$template_vars),$controller_vars);
  }

  public function errorAction() {
		return '<h1 style="color:red;font-weight:bold;">Action Denied...Please contact Administrator.</h1>';  	
  }

  public function errorActionDevice() {
    $uname = isset($_SESSION['uname']) ? $_SESSION['uname'] : '';
    $controller_vars = array(
      'page_title' => 'Page not found',
      'disable_layout' => true,
    );

    $template_vars = array(
      'uname' => $uname,
    );

    $template = new Template($this->views_path);
    return array($template->render_view('error-device',$template_vars),$controller_vars);    
  }

  public function forceLogoutAction() {
    $controller_vars = array(
      'page_title' => 'Auto Logout',
      'disable_layout' => true,
    );

    $template_vars = array(
    );

    $template = new Template($this->views_path);
    return array($template->render_view('force-logout', $template_vars), $controller_vars);    
  }

  /** 404 error template **/
  public function errorActionNotFound() {

    $controller_vars = array(
      'page_title' => 'Page not found',
      'disable_layout' => true,
    );

    $template = new Template($this->views_path);
    return array($template->render_view('error-404',[]),$controller_vars);
  }  
}