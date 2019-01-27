<?php

namespace ClothingRm\Inventory\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class InventoryMrp
{
	private $api_caller;

	public function __construct() {
		$this->api_caller = new ApiCaller();
	}
}