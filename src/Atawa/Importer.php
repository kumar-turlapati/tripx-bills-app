<?php
 
namespace Atawa;

require __DIR__.'/../../libraries/spreadsheet-reader-master/php-excel-reader/excel_reader2.php';
require __DIR__.'/../../libraries/spreadsheet-reader-master/SpreadsheetReader.php';

final class Importer {

	protected $file_path;

	# constructor method
	public function __construct($file_path='') {
		$this->file_path = $file_path;
	}

	# import data using spread sheet.
	public function _import_data() {
		$objects = $objects_final = [];
		$header_index = 0;
		try {
			$spread_sheet = new \SpreadsheetReader($this->file_path);
			foreach($spread_sheet as $key => $row) {
				if($row) {
					$objects[] = $row;
				}
			}
			if( isset($objects[0]) && count($objects[0]) > 0) {
				# loop through objects and make the array as associative.
				foreach($objects as $key => $object_details) {
					foreach($object_details as $object_key => $object_value) {
						$objects_final[$key][$objects[0][$object_key]] = Utilities::clean_string($object_value);
					}
				}
				array_shift($objects_final);
			}
		} catch(Exception $e) {
			return false;
		}
		return $objects_final;
	}
}