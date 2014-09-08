<?php

/**
 * @file plugins/generic/sectionEditorOptions/SectionEditorOptionsPluginAuthor.inc.php
 *
 * Copyright (c) 2013-2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SectionEditorOptionsPluginAuthor
 * @ingroup plugins_generic_sectionEditorOptions
 * @see SectionEditorOptionsPluginAuthorDAO
 *
 * @brief Implement rules to deny object information in some cases.
 *
 */

import('classes.article.Author');

class SectionEditorOptionsPluginAuthor extends Author {

	/**
	 * Get full name of editor.
	 * @return string
	 */
	function getFullName() {
		if ($this->_isBlocked()) {
			return '';
		} else {
			return parent::getFullName();
		}
	}

	/**
	 * Get first name of editor.
	 * @return string
	 */
	function getFirstName() {
		if ($this->_isBlocked()) {
			return '';
		} else {
			return parent::getFirstName();
		}
	}

	/**
	 * Get last name of editor.
	 * @return string
	 */
	function getLastName() {
		if ($this->_isBlocked()) {
			return $this->getData('sectionEditorOptionsPluginUniqueId');
		} else {
			return parent::getLastName();
		}
	}

	/**
	 * Get initials of editor.
	 * @return string
	 */
	function getInitials() {
		if ($this->_isBlocked()) {
			return '';
		} else {
			return parent::getInitials();
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
		if (isset($debug[2]) && isset($debug[2]['file'])) {
			$callerFile = $debug[2]['file'];
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
