<?php

/**
 * @file plugins/generic/sectionEditorOptions/SectionEditorOptionsPlugin.inc.php
 *
 * Copyright (c) 2013-2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SectionEditorOptionsPlugin
 * @ingroup plugins_generic_sectionEditorOptions
 *
 * @brief Add journal level options to customize the section editor role in the submission workflow process.
 */


import('lib.pkp.classes.plugins.GenericPlugin');

class SectionEditorOptionsPlugin extends GenericPlugin {

	/** @var $_blockedEmails Array Users that need will have their contact info removed */
	var $_blockedEmails = array();

	/**
	 * Information about operations that needs to be blocked,
	 * grouped by each plugin setting that triggers it.
	 * @var array
	 */
	var $_blockedOperations = array();

	/** @var $_blockedFiles array List of files to be blocked. */
	var $_blockedFiles = array();

	/** @var $_uniqueId string Flag to be used inside rendered templates. */
	var $_uniqueId;

	/** @var $_replacementMarkup string HTML markup to replace blocked information. */
	var $_replacementMarkup;

	/** @var $_formReplacementMarkup string HTML markup to replace blocked forms. */
	var $_formReplacementMarkup;

	/** @var $_requestedPage string The requested page */
	var $_requestedPage;

	/** @var $_requestedOp string The requested operation */
	var $_requestedOp;

	/** @var $_requestedArgs array The requested arguments */
	var $_requestedArgs;


	//
	// Implement methods from PKPPlugin.
	//
	/**
	 * @see LazyLoadPlugin::register()
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);

		if ($this->getEnabled() && $success) {
			$this->_uniqueId = uniqid();

			$request =& Application::getRequest();
			$journal =& $request->getContext();
			$journalId = $journal->getId();

			$router =& $request->getRouter(); /* @var $router PageRouter */
			if (!is_a($router, 'PageRouter')) return $success;
			$this->_requestedPage = $router->getRequestedPage($request);
			$this->_requestedOp = $router->getRequestedOp($request);
			$this->_requestedArgs = $router->getRequestedArgs($request);

			// Hooks used by all settings.
			if ($this->getSetting($journalId, 'denyEditorialDecision') ||
					$this->getSetting($journalId, 'denyContact') || $this->getSetting($journalId, 'denyReviewFilesAccess')) {
				// Block operations.
				HookRegistry::register('LoadHandler', array(&$this, 'callbackLoadHandler'));

				// Remove links from blocked operations.
				HookRegistry::register('TemplateManager::display', array(&$this, 'generalCallbackDisplay'));
			}

			// Implement deny editorial decision option, if enabled.
			if ($this->getSetting($journalId, 'denyEditorialDecision')) {
				// Define operations to be blocked.
				$this->_blockedOperations[] = array(
						'sectionEditor',
						'recordDecision',
						false,
						'sectionEditor/submissionReview.tpl',
						'plugins.generic.sectionEditorOptions.message.denyDecision',
						'submissionReview');
			}

			// Implement email contact denial option if enabled.
			if ($this->getSetting($journalId, 'denyContact')) {
				HookRegistry::register('TemplateManager::display', array(&$this, 'denyContactCallbackDisplay'));

				// Register our DAO's so we can inject objects that will
				// restrict access to contact information.
				$this->import('SectionEditorOptionsPluginEditAssignmentDAO');
				DAORegistry::registerDAO('EditAssignmentDAO', new SectionEditorOptionsPluginEditAssignmentDAO());
				$this->import('SectionEditorOptionsPluginAuthorDAO');
				DAORegistry::registerDAO('AuthorDAO', new SectionEditorOptionsPluginAuthorDAO());

				// Define operations to be blocked.
				$this->_blockedOperations[] = array('sectionEditor', 'emailEditorDecisionComment', false, 'sectionEditor/submissionReview.tpl');
				$this->_blockedOperations[] = array('sectionEditor', 'viewEditorDecisionComments', false, 'sectionEditor/submissionReview.tpl');
				$this->_blockedOperations[] = array('sectionEditor', 'notifyAuthorCopyedit', false, 'sectionEditor/submissionEditing.tpl');
				$this->_blockedOperations[] = array('sectionEditor', 'notifyAuthorProofreader', false, 'sectionEditor/submissionEditing.tpl');
				$this->_blockedOperations[] = array('sectionEditor', 'thankAuthorCopyedit', false, 'sectionEditor/submissionEditing.tpl');
			}

			// Implement review files access option if enabled.
			if ($this->getSetting($journalId, 'denyReviewFilesAccess')) {
				if (Validation::isSectionEditor()) {

					// Add blocked operation to upload review file.
					$this->_blockedOperations[] = array('sectionEditor', 'uploadReviewVersion', false, 'sectionEditor/submissionReview.tpl', 'plugins.generic.sectionEditorOptions.message.denyDecision', 'submissionReview');
				}
			}
		}

		return $success;
	}

	/**
	 * @see PKPPlugin::getDisplayName()
	 */
	function getDisplayName() {
		return __('plugins.generic.sectionEditorOptions.displayName');
	}

	/**
	 * @see PKPPlugin::getDescription()
	 */
	function getDescription() {
		return __('plugins.generic.sectionEditorOptions.description');
	}

	/**
	 * @see Plugin::getContextSpecificPluginSettingsFile()
	 */
	function getContextSpecificPluginSettingsFile() {
		return $this->getPluginPath() . '/settings.xml';
	}

	/**
	 * @see PKPPlugin::getTemplatePath()
	 */
	function getTemplatePath() {
		return parent::getTemplatePath() . 'templates/';
	}

	/**
	 * @see PKPPlugin::manage()
	 */
	function manage($verb, $args, &$message, &$messageParams) {
		$returner = parent::manage($verb, $args, $message, $messageParams);
		if (!$returner) return false;

		switch($verb) {
			case 'settings':
				$templateMgr =& TemplateManager::getManager();
				$templateMgr->register_function('plugin_url', array(&$this, 'smartyPluginUrl'));
				$settingsForm = $this->_getSettingsForm();
				$settingsForm->initData();
				$settingsForm->display();
				break;
			case 'save':
				$settingsForm = $this->_getSettingsForm();
				$settingsForm->readInputData();
				if ($settingsForm->validate()) {
					$settingsForm->execute();
					$message = NOTIFICATION_TYPE_SUCCESS;
					$messageParams = array('contents' => __('plugins.generic.sectionEditorOptions.settings.saved'));
					return false;
				} else {
					$settingsForm->display();
				}
				break;
			default:
				return $returner;
		}
		return true;
	}


	//
	// Implement hooks
	//
	/**
	 * Load handler hook to block listed operations.
	 * @param $hookName string
	 * @param $args array
	 * @return boolean
	 * @see PKPPageRouter::route() for the hook call.
	 */
	function callbackLoadHandler($hookName, $args) {
		$requestedPage = $args[0];
		$requestedOp = $args[1];

		import('classes.notification.NotificationManager');
		$notificationManager = new NotificationManager();
		$request =& Application::getRequest();
		$user = $request->getUser();

		$context = $request->getContext();
		$contextId = $context->getId();

		foreach($this->_blockedOperations as $operationInfo) {
			$page = $operationInfo[0];
			$operation = $operationInfo[1];
			if ($page == $requestedPage && $operation == $requestedOp) {
				// Check path.
				$path = null;
				if (isset($operationInfo[2]) && $operationInfo[2]) {
					$requestedArgs = $this->_requestedArgs;

					if (!call_user_func_array($operationInfo[2], array($requestedArgs))) {
						// The operation path is not the same compared to the
						// requested one, allow the operation.
						return false;
					}
				}

				// Need to block.
				$articleId = $request->getUserVar('articleId');
				if (isset($operationInfo[4])) {
					// Let users know what happened.
					$notificationLocaleKey = $operationInfo[4];
					$notificationManager->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_ERROR,
							array('contents' => __($notificationLocaleKey)));
				}

				if (isset($operationInfo[5]) && $operationInfo[5]) {
					// Redirect.
					$redirectOp = $operationInfo[5];
					$request->redirect(null, $page, $redirectOp, $articleId);
				} else {
					// Stop operation execution here.
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Template manager display hook that perform general actions
	 * related to all three plugin settings.
	 * @param $hookName string
	 * @param $args array
	 */
	function generalCallbackDisplay($hookName, $args) {
		$smarty =& $args[0]; /* @var $smarty Smarty */
		$templateFile = $args[1];

		// Add the plugin stylesheet.
		$baseImportPath = Request::getBaseUrl() . DIRECTORY_SEPARATOR . $this->getPluginPath() . DIRECTORY_SEPARATOR;
		$smarty->addStyleSheet($baseImportPath . 'css/sectionEditorOptionsPlugin.css');

		// Render the replacement markup.
		$smarty->assign('warningText', __('plugins.generic.sectionEditorOptions.message.blockedInfo'));
		$this->_replacementMarkup = $smarty->fetch($this->getTemplatePath() . DIRECTORY_SEPARATOR . 'blockedInfoReplacement.tpl');

		// Render the form replacement markup.
		$smarty->assign('warningText', __('plugins.generic.sectionEditorOptions.message.formBlockedInfo'));
		$this->_formReplacementMarkup = $smarty->fetch($this->getTemplatePath() . DIRECTORY_SEPARATOR . 'blockedInfoReplacement.tpl');

		// Register filter to remove links that points to blocked operations from markup.
		$templateFiles = array();
		foreach ($this->_blockedOperations as $operationInfo) {
			if (!isset($operationInfo[3])) continue;
			$templateFiles[] = $operationInfo[3];
		}

		if (in_array($templateFile, $templateFiles)) {
			$this->_currentTemplateFile = $templateFile;
			$smarty->register_outputfilter(array(&$this, 'removeLinksOutputFilter'));
		}

		return false;
	}

	/**
	 * Template manager display hook that decides to register or not
	 * smarty output filter to implement deny contact setting.
	 * @param $hookName string
	 * @param $args array
	 * @return boolean
	 * @see TemplateManager::display()
	 */
	function denyContactCallbackDisplay($hookName, $args) {
		$smarty =& $args[0];

		// Register filter to remove blocked emails.
		$smarty->register_outputfilter(array(&$this, 'removeEmailsOutputFilter'));

		// Register filter to replace the unique id by the blocked replacement markup.
		$smarty->register_outputfilter(array(&$this, 'addReplacementMarkup'));

		return false;
	}

	/**
	 * Replace uniqueId flags by the replacement markup.
	 * @param $output string
	 * @param $smarty Smarty
	 * @return string
	 */
	function addReplacementMarkup($output, &$smarty) {
		$output = str_replace($this->_uniqueId, $this->_replacementMarkup, $output);
		return $output;
	}

	/**
	 * Remove links from output markup.
	 * @param $output string
	 * @param $smarty Smarty
	 * @return string
	 */
	function removeLinksOutputFilter($output, &$smarty) {
		foreach ($this->_blockedOperations as $operationInfo) {
			$page = $operationInfo[0];
			$op = $operationInfo[1];
			$templateFile = null;

			// Let the check callback run, if any.
			if (isset($operationInfo[2]) && $operationInfo[2]) {
				if (!call_user_func_array($operationInfo[2], array($this->_requestedArgs))) {
					continue;
				}
			}

			if (isset($operationInfo[3])) {
				$templateFile = $operationInfo[3];
			}
			if ($templateFile == $this->_currentTemplateFile) {
				$output = $this->_removeLinkFromHtml($page, $op, array(), $output);
			}
		}

		return $output;
	}

	/**
	 * Remove download file links from output markup.
	 * @param $output string
	 * @param $smarty Smarty
	 * @return string
	 */
	function removeFileLinksOutputFilter($output, &$smarty) {
		foreach ($this->_blockedFiles as $file) { /* @var $file ArticleFile */
			$args = array($file->getArticleId(), $file->getFileId(), $file->getRevision());
			$output = $this->_removeLinkFromHtml('sectionEditor', 'downloadFile', $args, $output);
		}

		return $output;
	}

	/**
	 * Output filter to remove blocked emails info from rendered markup.
	 * @param $output string
	 * @param $smarty Smarty
	 */
	function removeEmailsOutputFilter($output, &$smarty) {
		if (!empty($this->_blockedEmails)) {
			import('lib.pkp.classes.mail.Mail');
			$replacement = $this->_replacementMarkup;

			foreach($this->_blockedEmails as $userEmail) {
				// Remove email link and user full names. We search
				// for the email address because it is the unique.
				$encondedMail =  urlencode($userEmail); // Encoded because it's inside a link.

				$output = preg_replace('/.*' . $encondedMail . '.*/', $replacement, $output);

				// Remove email not encoded.
				$output = str_replace($userEmail, '', $output);
			}
		}

		return $output;
	}

	/**
	 * Decide if we should block information from users or not.
	 * @param $object DataObject
	 * @return boolean
	 */
	function isObjectInfoBlocked(&$object) {
		$request =& Application::getRequest();
		$journal =& $request->getContext();
		$journalId = $journal->getId();
		$userDao =& DAORegistry::getDAO('UserDAO'); /* @var $userDao UserDAO */

		// Get user that's requesting the page.
		$user =& $request->getUser();

		// Get the user that was requested from database.
		$requestedUser = null;
		$editAssignment = null;
		if (is_a($object, 'Author') || is_a($object, 'User')) {
			$requestedUser =& $object; /* @var $requestedUser User */
		} else if (is_a($object, 'EditAssignment')) {
			$editAssignment =& $object; /* @var $editAssignment EditAssignment */
			$requestedUser =& $userDao->getUser($editAssignment->getEditorId());
		}

		if (!$requestedUser) return false;

		// Test if current user is a section editor.
		$sections = array();
		if (Validation::isSectionEditor($journalId) && is_a($requestedUser, 'Author')) {
			// Get all section editor sections.
			$sectionDao =& DAORegistry::getDAO('SectionDAO'); /* @var $sectionDao SectionDAO */
			$journalSections = $sectionDao->getEditorSections($journalId);
			if (!isset($journalSections[$user->getId()])) {
				// No section, no need to block.
				return false;
			}

			$sections = $journalSections[$user->getId()];
			$sectionIds = array();

			foreach ($sections as $section) {
				$sectionIds[] = $section->getId();
			}

			$submissionId = $requestedUser->getSubmissionId();
			$submissionDao =& DAORegistry::getDAO('AuthorSubmissionDAO'); /* @var $submissionDao AuthorSubmissionDAO */
			$submission =& $submissionDao->getAuthorSubmission($submissionId);

			if (!in_array($submission->getSectionId(), $sectionIds)) {
				// Don't need to block.
				return false;
			}
		} else {
			// Users with any other roles should see all.
			$roleDao =& DAORegistry::getDAO('RoleDAO'); /* @var $roleDao RoleDAO */
			$roles = $roleDao->getRolesByUserId($user->getId(), $journalId);
			$acceptedRoles = array(ROLE_ID_AUTHOR, ROLE_ID_READER, ROLE_ID_REVIEWER, ROLE_ID_SECTION_EDITOR);
			foreach ($roles as $role) { /* @var $role Role */
				if (!in_array($role->getRoleId(), $acceptedRoles)) {
					return false;
				}
			}

			// We are only interested in specific author pages.
			$page = $this->_requestedPage;
			$op = $this->_requestedOp;
			$args = $this->_requestedArgs;

			$wantedOperations = array('submission', 'submissionReview', 'submissionEditing');
			if ($page == 'author' && in_array($op, $wantedOperations)) {
				// Requested submission is assigned to the section editor?
				$submissionId = current($args);
				$sectionEditorSubmissionDao =& DAORegistry::getDAO('SectionEditorSubmissionDAO'); /* @var $sectionEditorSubmissionDao SectionEditorSubmissionDAO */
				$submission = $sectionEditorSubmissionDao->getSectionEditorSubmission($submissionId);
				$sectionId = $submission->getSectionId();

				$sectionEditorsDao =& DAORegistry::getDAO('SectionEditorsDAO'); /* @var $sectionEditorsDao SectionEditorsDAO */
				if (!$sectionEditorsDao->editorExists($journalId, $sectionId, $requestedUser->getId())) {
					return false;
				}
			} else {
				return false;
			}
		}

		// All requirements checked, need to block information.
		$this->_blockedEmails[] = $requestedUser->getEmail();
		$object->setData('sectionEditorOptionsPluginUniqueId', $this->_uniqueId);
		return true;
	}


	//
	// Implement template methods from GenericPlugin.
	//
	/**
	 * @see GenericPlugin::getManagementVerbs()
	 */
	function getManagementVerbs() {
		$verbs = parent::getManagementVerbs();
		if ($this->getEnabled()) {
			$verbs[] = array('settings', __('manager.plugins.settings'));
		}
		return $verbs;
	}

	/**
	 * Check the passed arguments to see if they match
	 * a review file that's associated with an article
	 * that the current user is section editor.
	 * @param $args array
	 * @return boolean
	 */
	function checkFilePath($args) {
		$submissionId = null;
		if (isset($args[0])) {
			$submissionId = $args[0];
		}

		$fileId = null;
		if (isset($args[1])) {
			$fileId = $args[1];
		}

		$revision = null;
		if (isset($args[2])) {
			$revision = $args[2];
		}

		// Check the file, must be in review stage.
		$articleFileDao =& DAORegistry::getDAO('ArticleFileDAO'); /* @var $articleFileDao ArticleFileDAO */
		$submissionFile =& $articleFileDao->getArticleFile($fileId, $revision, $submissionId);
		if (!$submissionFile || $submissionFile->getFileSize() !== ARTICLE_FILE_REVIEW) {
			return false;
		}

		$request =& Application::getRequest();
		$user =& $request->getUser();

		$sectionEditorSubmissionDao =& DAORegistry::getDAO('SectionEditorSubmissionDAO'); /* @var $sectionEditorSubmissionDao SectionEditorSubmissionDAO */
		$submission =& $sectionEditorSubmissionDao->getSectionEditorSubmission($submissionId); /* @var $submission SectionEditorSubmission */
		$sectionId = $submission->getSectionId();
		$journalId = $submission->getJournalId();

		$sectionEditorDao =& DAORegistry::getDAO('SectionEditorsDAO');
		$sectionEditors = $sectionEditorDao->getEditorsBySectionId($journalId, $sectionId);
		foreach ($sectionEditors as $userInfo) {
			$sectionEditor = $userInfo['user'];
			if ($user->getId() == $sectionEditor->getId()) {
				// The user is a section editor of the submission section.
				return true;
			}
		}

		return false;
	}

	/**
	 * Identify the upload file action that section editor
	 * can perform with the operation checkEditorReview.
	 * @param $args array
	 * @return boolean
	 */
	function checkEditorReview($args) {
		$request =& Application::getRequest();
		if ($request->getUserVar('submit')) {
			return true;
		}

		return false;
	}

	//
	// Private helper methods.
	//
	/**
	 * Instantiate and return the plugin's setting form.
	 * @return SectionEditorOptionsSettingsForm
	 */
	function _getSettingsForm() {
		$journal =& Request::getJournal();
		$this->import('SectionEditorOptionsSettingsForm');
		return new SectionEditorOptionsSettingsForm($this, $journal->getId());
	}

	/**
	 * Remove a link based on the passed page and operation
	 * from the passed html markup.
	 * @param $page string
	 * @param $operation string
	 * @param $path array (optional)
	 * @param $html string
	 * @return string
	 */
	function _removeLinkFromHtml($page, $operation, $path = array(), $html) {
		$request = Application::getRequest(); /* @var $request Request */
		$url = preg_quote($request->url(null, $page, $operation, $path), '/');
		$matches = array();
		$result = preg_match('/.*' . $url . '.*/', $html, $matches);

		if ($result == 1) {
			$test = true;
		}

		// Check if the url is inside a link or a form.
		if ($result == 1 && strpos($matches[0], '<form') !== false ) {
			// Form. Delete the whole form markup.
			$html = preg_replace("/\<form.*" . $url . "[\s\S]*?\<\/form\>/", $this->_formReplacementMarkup, $html);
		} else {
			// Link.
			$html = preg_replace('/.*' . $url . '.*/', $this->_replacementMarkup, $html);
		}

		return $html;
	}
}

?>
