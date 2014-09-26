<?php

/**
 * @file plugins/generic/sectionEditorOptions/SectionEditorOptionsPluginAuthorDAO.inc.php
 *
 * Copyright (c) 2013-2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SectionEditorOptionsPluginAuthorDAO
 * @ingroup plugins_generic_sectionEditorOptions
 * @see AuthorDAO
 *
 * @brief Inject a custom Author object.
 */

import('classes.article.AuthorDAO');

class SectionEditorOptionsPluginAuthorDAO extends AuthorDAO {
	/**
	 * Return a custom Author object.
	 * @see AuthorDAO::newDataObject()
	 */
	function newDataObject() {
		import('plugins.generic.sectionEditorOptions.SectionEditorOptionsPluginAuthor');
		return new SectionEditorOptionsPluginAuthor();
	}
}

?>
