<?php

/**
 * @file plugins/themes/scielo/ScieloThemePlugin.inc.php
 *
 * Copyright (c) 2013-2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ScieloThemePlugin
 * @ingroup plugins_themes_scielo
 *
 * @brief Scielo theme plugin
 */

import('classes.plugins.ThemePlugin');

class ScieloThemePlugin extends ThemePlugin {

	/**
	 * @see PKPPlugin::register($category, $path)
	 */
	function register($category, $path) {
		HookRegistry::register ('TemplateManager::display', array(&$this, 'callbackDisplay'));
		return parent::register($category, $path);
	}

	function callbackDisplay($hookName, $args) {
		$smarty = $args[0]; /* @var $smarty Smarty */
		$smarty->register_outputfilter(array(&$this, 'addPluginClassOutputFilter'));
	}

	/**
	 *
	 * @param unknown_type $output
	 * @param unknown_type $smarty
	 * @return mixed
	 */
	function addPluginClassOutputFilter($output, &$smarty) {
		$output = str_replace('<body id="pkp-common-openJournalSystems">', '<body id="pkp-common-openJournalSystems" class="scieloTheme">', $output);
		return $output;
	}

	/**
	 * @see ThemePlugin::getName()
	 */
	function getName() {
		return 'ScieloThemePlugin';
	}

	/**
	 * @see ThemePlugin::getDisplayName()
	 */
	function getDisplayName() {
		return 'Scielo Theme';
	}

	/**
	 * @see ThemePlugin::getDescription()
	 */
	function getDescription() {
		return 'Stylesheet with Scielo appearance.';
	}

	function getStylesheetFilename() {
		return 'scielo.css';
	}

	function getLocaleFilename($locale) {
		return null; // No locale data
	}
}

?>
