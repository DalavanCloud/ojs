<?php

/**
 * @file plugins/generic/reviewAskColors/ReviewAskColorsPlugin.inc.php
 *
 * Copyright (c) 2013-2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewAskColorsPlugin
 * @ingroup plugins_generic_reviewAskColors
 *
 * @brief Style the peer review ask column, inside in review submission lists,
 * representing 4 states: finished reviews, in process and no delay, in process and delayed
 * and declined or cancelled.
 */


import('lib.pkp.classes.plugins.GenericPlugin');

class ReviewAskColorsPlugin extends GenericPlugin {


	//
	// Implement methods from PKPPlugin.
	//
	/**
	 * @see LazyLoadPlugin::register()
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);

		if ($this->getEnabled() && $success) {
			HookRegistry::register('TemplateManager::display', array(&$this, 'callbackDisplay'));
		}

		return $success;
	}

	/**
	 * @see PKPPlugin::getDisplayName()
	 */
	function getDisplayName() {
		return __('plugins.generic.reviewAskColors.displayName');
	}

	/**
	 * @see PKPPlugin::getDescription()
	 */
	function getDescription() {
		return __('plugins.generic.reviewAskColors.description');
	}

	/**
	 * @see Plugin::getContextSpecificPluginSettingsFile()
	 */
	function getContextSpecificPluginSettingsFile() {
		return $this->getPluginPath() . '/settings.xml';
	}

	/**
	 * @see PKPPlugin::manage()
	 */
	function manage($verb, $args, &$message, &$messageParams) {
		$returner = parent::manage($verb, $args, $message, $messageParams);
		switch($verb) {
			case 'enable':
			case 'disable':
				// Flush the cache so the plugin will have a chance to alter
				// the compiled template file.
				$request =& Application::getRequest();
				$templateMgr =& TemplateManager::getManager($request);
				$templateMgr->clear_compiled_tpl();
				break;
		}

		return $returner;
	}

	/**
	 * Template manager display hook to register smarty filters
	 * and add the review ask column styles.
	 * @param $hookName string
	 * @param $args array
	 * @return boolean
	 */
	function callbackDisplay($hookName, $args) {
		$smarty =& $args[0]; /* @var $smarty Smarty */
		$templateFile = $args[1];

		if ($templateFile === 'editor/submissions.tpl') {
			$smarty->register_prefilter(array(&$this, 'addStateStylesPreFilter'));

			// Add the plugin stylesheet.
			$baseImportPath = Request::getBaseUrl() . DIRECTORY_SEPARATOR . $this->getPluginPath() . DIRECTORY_SEPARATOR;
			$smarty->addStyleSheet($baseImportPath . 'css/reviewAskColorsPlugin.css');
		}

		return false;
	}

	/**
	 * Change the template output before it's compiled,
	 * adding the code necessary to present the review states.
	 * @param $output string
	 * @param $smarty Smarty
	 * @return string
	 */
	function addStateStylesPreFilter($output, &$smarty) {
		if (strpos($output, ' * templates/editor/submissionsInReview.tpl') !== false) {

			// Remove the check to avoid cancelled or declined, now we want
			// to show those assignments.
			$checkCancelledOrDeclinedString = '{if not $assignment->getCancelled() and not $assignment->getDeclined()}';
			$output = str_replace($checkCancelledOrDeclinedString, '{if true}', $output);

			// Replace the review ask cell markup, adding a hook so we can
			// add the review assignment state style.
			$reviewAskCellString = 'style="padding: 0 4px 0 0; font-size: 1.0em">{if $assignment->getDateNotified()}{$assignment->getDateNotified()|date_format:$dateFormatTrunc}{else}&mdash;{/if}</td>';
			$output = str_replace($reviewAskCellString, '{call_hook name="ReviewAskColorsPlugin::reviewAskCell"} ' . $reviewAskCellString, $output);
			HookRegistry::register('ReviewAskColorsPlugin::reviewAskCell', array(&$this, 'reviewAskCellCallback'));

			$dueString = '{if $assignment->getDateCompleted() || !$assignment->getDateConfirmed()}&mdash;{else}{$assignment->getWeeksDue()|default:"&mdash;"}{/if}';
			$output = str_replace($dueString, $checkCancelledOrDeclinedString . $dueString . '{/if}', $output);
		}

		return $output;
	}

	/**
	 * Add the css style that matches the review assignment state.
	 * @param $hookName string
	 * @param $args array
	 * @return boolean
	 */
	function reviewAskCellCallback($hookName, $args) {
		$smarty =& $args[1];
		$output =& $args[2];

		$reviewAssignment =& $smarty->get_template_vars('assignment'); /* @var $reviewAssignment ReviewAssignment */
		if (!$reviewAssignment) return false;

		$class = null;

		if ($reviewAssignment->getCancelled() || $reviewAssignment->getDeclined()) {
			$class = 'review_ask_colors_plugin_cancelledOrFinished';
		} else if ($reviewAssignment->getDateCompleted() && !$class) {
			$class = 'review_ask_colors_plugin_completed';
		} else if (strtotime($reviewAssignment->getDateDue()) < time() && !$class) {
			$class = 'review_ask_colors_plugin_delayed';
		} else {
			$class = 'review_ask_colors_plugin_noDelay';
		}

		$output = 'class="' . $class . '"';

		return false;
	}
}

?>
