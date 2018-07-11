<?php 

namespace Settings\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Atawa\Utilities;
use Atawa\Template;
use Atawa\Flash;

class SettingsController
{
	protected $views_path,$flash;

	public function __construct() 
 	{
		$this->views_path = __DIR__.'/../Views/';
    $this->flash = new Flash();
	}

  public function changeFinYear(Request $request) {

    if( count($request->request->all())>0 ) {
      $sel_finyear = Utilities::clean_string($request->get('defYear'));
      if(isset($_SESSION['finY'])) {
        unset($_SESSION['finY']);
      }
      $_SESSION['finY'] = $sel_finyear;
      $message = 'Financial year successfully changed for this Session. All your requests will be effected with this setting.';
      $this->flash->set_flash_message($message);
      Utilities::redirect('/settings/fin-year');
    }

    // prepare form variables.
    $template_vars = array(
      'sel_fin_year' => Utilities::get_default_financial_year(),
      'fin_years' => array(''=>'Choose')+Utilities::get_financial_years_list(),
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Settings',
      'icon_name' => 'fa fa-cogs',      
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('change-finyear',$template_vars),$controller_vars);
  }

}