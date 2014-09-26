<?php

/**
 * @file plugins/generic/sectionEditorOptions/SectionEditorOptionsPluginEditAssignment.inc.php
 *
 * Copyright (c) 2013-2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SectionEditorOptionsPluginEditAssignment
 * @ingroup plugins_generic_sectionEditorOptions
 * @see SectionEditorOptionsPluginEditAssignmentDAO
 *
 * @brief Implement rules to deny object information in some cases.
 *
 */

import('classes.submission.editAssignment.EditAssignment');

class SectionEditorOptionsPluginEditAssignment extends EditAssignment {

	/**
	 * Get full name of editor.
	 * @return string
	 */
	function getEditorFullName() {
		if ($this->_isBlocked()) {
			return '';
		} else {
			return parent::getEditorFullName();
		}
	}

	/**
	 * Get first name of editor.
	 * @return string
	 */
	function getEditorFirstName() {
		if ($this->_isBlocked()) {
			return '';
		} else {
			return parent::getEditorFirstName();
		}
	}

	/**
	 * Get last name of editor.
	 * @return string
	 */
	function getEditorLastName() {
		if ($this->_isBlocked()) {
			return $this->getData('sectionEditorOptionsPluginUniqueId');
		} else {
			return parent::getEditorLastName();
		}
	}

	/**
	 * Get initials of editor.
	 * @return string
	 */
	function getEditorInitials() {
		if ($this->_isBlocked()) {
			return '';
		} else {
			return parent::getEditorInitials();
		}
	}


	//
	// Private helper methods.
	//
	/**
	 * Define if some of this object information should be blocked.
	 * @return boolean
	 */
	function _isBlocked() {
		$debug = debug_backtrace();
		$callerFile = null;
		if (isset($debug[1]) && isset($debug[1]['file'])) {
			$callerFile = $debug[1]['file'];
		}

		$cacheFolder = CacheManager::getFileCachePath();

		if ($callerFile && strpos($callerFile, $cacheFolder) === 0) {
			// The call came from a template file in cache.
			// Let the plugin check if it's necessary to block.
			$plugin =& PluginRegistry::getPlugin('generic', 'sectioneditoroptionsplugin'); /* @var $plugin SectionEditorOptionsPlugin */
			return $plugin->isObjectInfoBlocked($this);
		} else {
			return false;
		}
	}
}

?>
