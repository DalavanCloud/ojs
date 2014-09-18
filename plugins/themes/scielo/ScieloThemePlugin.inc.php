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

	/**
	 * Template manager display hook callback to register smarty filters.
	 * @param $hookName string
	 * @param $args array
	 * @return boolean
	 */
	function callbackDisplay($hookName, $args) {
		$smarty = $args[0]; /* @var $smarty Smarty */
		$smarty->register_outputfilter(array(&$this, 'addPluginClassOutputFilter'));
		$smarty->register_prefilter(array(&$this, 'addArticleTitleInWorkflowPagesHeader'));

		return false;
	}

	/**
	 * Output filter to add the plugin css class into the body html element.
	 * @param $output string
	 * @param $smarty Smarty
	 * @return string
	 */
	function addPluginClassOutputFilter($output, &$smarty) {
		$output = str_replace('<body id="pkp-common-openJournalSystems">', '<body id="pkp-common-openJournalSystems" class="scieloTheme">', $output);
		return $output;
	}

	/**
	 * Smarty prefilter to modify the template markup before it's rendered,
	 * adding the article title in workflow pages h2 element.
	 * @param $output string
	 * @param $smarty Smarty
	 * @return string
	 */
	function addArticleTitleInWorkflowPagesHeader($output, &$smarty) {
                $templatesToChange = array(
			'templates/sectionEditor/submission.tpl' => 'submission.page.summary',
			'templates/sectionEditor/submissionReview.tpl' => 'submission.page.review',
			'templates/sectionEditor/submissionEditing.tpl' => 'submission.page.editing',
			'templates/sectionEditor/submissionHistory.tpl' => 'submission.page.history',
			'templates/sectionEditor/submissionCitations.tpl' => 'submission.page.citations'
		);

		foreach ($templatesToChange as $templateFile => $localeKey) {
			if (strpos($output, ' * ' . $templateFile) !== false) {
				$markupToBeReplaced = '{translate|assign:"pageTitleTranslated" key="' . $localeKey . '" id=$submission->getId()}';
				$replaceMarkup = $markupToBeReplaced . '{assign var="pageTitleTranslated" value=$pageTitleTranslated|concat:" - ":$submission->getLocalizedTitle()|strip_unsafe_html}'; 
				$output = str_replace($markupToBeReplaced, $replaceMarkup, $output);
			}
		}

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
