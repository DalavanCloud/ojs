<?php

/**
 * @file plugins/generic/notifyCoAuthors/NotifyCoAuthorsPlugin.inc.php
 *
 * Copyright (c) 2013-2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NotifyCoAuthorsPlugin
 * @ingroup plugins_generic_notifyCoAuthors
 *
 * @brief Add journal level options to customize the section editor role in the submission workflow process.
 */

import('lib.pkp.classes.plugins.GenericPlugin');

define('NOTIFY_CO_AUTHORS_PLUGIN_DEFAULT_MAIL_TEMPLATE', 'SUBMISSION_ACK_COAUTHORS');

class NotifyCoAuthorsPlugin extends GenericPlugin {


	//
	// Implement methods from PKPPlugin.
	//
	/**
	 * @see LazyLoadPlugin::register()
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);

		if ($this->getEnabled() && $success) {
			HookRegistry::register('Author::SubmitHandler::saveSubmit', array(&$this, 'callbackSaveSubmit'));
		}

		return $success;
	}

	/**
	 * @see PKPPlugin::getDisplayName()
	 */
	function getDisplayName() {
		return __('plugins.generic.notifyCoAuthors.displayName');
	}

	/**
	 * @see PKPPlugin::getDescription()
	 */
	function getDescription() {
		return __('plugins.generic.notifyCoAuthors.description');
	}

	/**
	 * @see Plugin::getContextSpecificPluginSettingsFile()
	 */
	function getContextSpecificPluginSettingsFile() {
		return $this->getPluginPath() . '/settings.xml';
	}

	/**
	 * Save submit submission form hook to send
	 * a different email to co-authors.
	 * @param $hookName string
	 * @param $args array
	 */
	function callbackSaveSubmit($hookName, $args) {
		$step = $args[0];

		if ($step !== 5) return false;

		$article =& $args[1]; /* @var $article Article */

		$request =& Application::getRequest();
		$journal =& $request->getJournal();
		$journalId = $journal->getId();
		$user = $request->getUser();

		// Check the custom mail template.
		$mailTemplateDao =& DAORegistry::getDAO('EmailTemplateDAO'); /* @var $mailTemplateDao EmailTemplateDAO */
		$mailTemplate =& $mailTemplateDao->getEmailTemplate(NOTIFY_CO_AUTHORS_PLUGIN_DEFAULT_MAIL_TEMPLATE, AppLocale::getLocale(), $journalId);
		if (!$mailTemplate) return false;

		// Send email to coauthors.
		$authors = $article->getAuthors();
		foreach($authors as $author) {
			// Skip the current user, submit form execute operation
			// already sent email to him.
			if ($author->getId() == $user->getId()) continue;

			import('classes.mail.MailTemplate');
			$mail = new MailTemplate(NOTIFY_CO_AUTHORS_PLUGIN_DEFAULT_MAIL_TEMPLATE);
			$mail->setFrom($journal->getSetting('contactEmail'), $journal->getSetting('contactName'));
			$mail->addRecipient($author->getEmail(), $author->getFullName());
			$mail->assignParams(array(
				'authorName' => $author->getFullName(),
				'authorUsername' => $author->getUsername(),
				'editorialContactSignature' => $journal->getSetting('contactName') . "\n" . $journal->getLocalizedTitle(),
				'articleTitle' => $article->getLocalizedTitle(),
				'articleAbstract' => $article->getLocalizedAbstract(),
			));

			$mail->send($request);
		}

		return false;
	}


	//
	// Private helper methods.
	//
	/**
	 * Instantiate and return the plugin's setting form.
	 * @return NotifyCoAuthorsSettingsForm
	 */
	function _getSettingsForm() {
		$journal =& Request::getJournal();
		$this->import('NotifyCoAuthorsSettingsForm');
		return new NotifyCoAuthorsSettingsForm($this, $journal->getId());
	}
}

?>
