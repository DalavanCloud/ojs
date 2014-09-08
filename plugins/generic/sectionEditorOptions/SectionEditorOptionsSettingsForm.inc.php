<?php

/**
 * @file plugins/generic/sectionEditorOptions/SectionEditorOptionsSettingsForm.inc.php
 *
 * Copyright (c) 2013-2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SectionEditorOptionsSettingsForm
 * @ingroup plugins_generic_sectionEditorOptions
 *
 * @brief Form for journal managers to modify section editor options plugin settings.
 */

import('lib.pkp.classes.form.Form');

class SectionEditorOptionsSettingsForm extends Form {

	/** @var $journalId int */
	var $_journalId;

	/** @var $plugin object */
	var $_plugin;

	/**
	 * Constructor
	 * @param $plugin object
	 * @param $journalId int
	 */
	function SectionEditorOptionsSettingsForm(&$plugin, $journalId) {
		$this->_journalId = $journalId;
		$this->_plugin =& $plugin;

		parent::Form($plugin->getTemplatePath() . 'sectionEditorOptionsSettingsForm.tpl');
		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Initialize form data.
	 */
	function initData() {
		$plugin =& $this->_plugin;

		$this->setData('denyEditorialDecision', $plugin->getSetting($this->_journalId, 'denyEditorialDecision'));
		$this->setData('denyContact', $plugin->getSetting($this->_journalId, 'denyContact'));
		$this->setData('denyReviewFilesAccess', $plugin->getSetting($this->_journalId, 'denyReviewFilesAccess'));
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('denyEditorialDecision','denyContact', 'denyReviewFilesAccess'));
	}

	/**
	 * @see Form::fetch()
	 */
	function display($request) {
		$templateMgr =& TemplateManager::getManager($request);
		$templateMgr->assign('pluginName', $this->_plugin->getName());
		parent::display();
	}

	/**
	 * Save settings.
	 */
	function execute() {
		$plugin =& $this->_plugin;

		$plugin->updateSetting($this->_journalId, 'denyEditorialDecision', (int)$this->getData('denyEditorialDecision'));
		$plugin->updateSetting($this->_journalId, 'denyContact', (int)$this->getData('denyContact'));
		$plugin->updateSetting($this->_journalId, 'denyReviewFilesAccess', (int)$this->getData('denyReviewFilesAccess'));
	}
}

?>
