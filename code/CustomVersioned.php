<?php

class CustomVersioned extends DataExtension {

	public function updateSummaryFields(&$fields) {
		$fields['Published'] = 'Published';
		$fields['Modified'] = 'Modified';		
	}
	
	public function updateFieldLabels(&$labels) {
		$labels['Published'] = _t('CustomVersioned.PUBLISHED', 'Published');
    $labels['Modified'] = _t('CustomVersioned.MODIFIED', 'Modified');
	}

	/**
	 * Remove the Version field, which is used only internally
	 * @param \FieldList $fields
	 */
	public function updateCMSFields(\FieldList $fields) {
		parent::updateCMSFields($fields);
		$fields->removeByName('Version');
	}
		
	/**
	 * Extract the date of publication
	 * @return String
	 */
	public function Published() {

		$retVal = null;
		if ($this->owner->isPublished()) {

			$lastPub = $this->owner->Versions('WasPublished=1', 'Version DESC', 1);
			$pub = $lastPub->pop();
			$pubEditedTime = new DateTime($pub->LastEdited);
			$retVal = $pubEditedTime->format('Y-m-d H:i:s');
		}
		return $retVal;
	}

	/**
	 * Extract the time of last modification
	 * @return String
	 */
	public function Modified() {

		$retVal = null;
		if ($this->owner->stagesDiffer('Stage', 'Live')) {

			$thisEditedTime = new DateTime($this->owner->LastEdited);
			$retVal = $thisEditedTime->format('Y-m-d H:i:s');
		}
		return $retVal;
	}

	/**
	 * Check permission to publish
	 * 
	 * @param Member $member
	 * @return boolean True if the current user can publish the DataObject
	 */
	public function canPublish($member = null) {

		$className = get_class($this->owner);
		if (Permission::check("PUBLISH_$className", 'any', $member))
			return true;
		else
			return false;
	}

	/**
	 * Check permission to remove from Live
	 * 
	 * @param Member $member
	 * @return boolean True if the current user can publish the DataObject
	 */
	public function canDeleteFromLive($member = null) {

		return $this->canPublish($member);
	}

	/**
	 * Check if the DataObject is new (it has yet to be written to the database)
	 *
	 * @return boolean
	 */
	function isNew() {
		if (empty($this->owner->ID))
			return true;

		if (is_numeric($this->owner->ID))
			return false;

		return stripos($this->owner->ID, 'new') === 0;
	}

	/**
	 * Check if the DataObject is published
	 *
	 * @return boolean True if the DataObject is published
	 */
	function isPublished() {

		if ($this->owner->isNew())
			return false;

		$className = get_class($this->owner);
		return (DB::query("SELECT \"ID\" FROM \"" . $className . "_Live\" WHERE \"ID\" = {$this->owner->ID}")->value()) ? true : false;
	}
	
	/**
	 * Elimino il DO anche dalla tabella Live
	 */
	public function onBeforeDelete() {
		
		$className = get_class($this->owner);
		$id = $this->owner->ID;
		
		DB::query("DELETE FROM {$className}_Live WHERE ID=$id");	
		
		parent::onBeforeDelete();
	}
	
}

