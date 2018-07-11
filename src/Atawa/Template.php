<?php

namespace Atawa;

use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\TemplateNameParser;
use Symfony\Component\Templating\Loader\FilesystemLoader;

class Template
{
	protected $loader, $templating;

	function __construct($view_path='') 
	{
		$this->loader = new FilesystemLoader($view_path."%name%");
		$this->templating = new PhpEngine(new TemplateNameParser(), $this->loader);
	}

	function render_view($template_name='', $params=array())
	{
		return $this->templating->render($template_name.'.tpl.php', $params);
	}
}