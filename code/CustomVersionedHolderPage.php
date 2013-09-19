<?php

class CustomVersionedHolderPage extends DataExtension {
	
	// Name of the GridField to extend
	protected $gridfieldName;
	
	public function __construct($gridfieldName) {
		$this->gridfieldName = $gridfieldName;
		parent::__construct();
	}

	public function updateCMSFields(\FieldList $fields) {
		parent::updateCMSFields($fields);
		
		// Get the GridField from the filed list if it is not read-only
		$gf = $fields->dataFieldByName($this->gridfieldName);
		if ($gf !== null && !($gf instanceof ReadonlyField)) {
			$df = $gf->getConfig()->getComponentByType('GridFieldDetailForm');
			$df->setItemRequestClass('CustomVersionedGridFieldDetailForm_ItemRequest');
		}
	}
}