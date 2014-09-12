<?php

/**
 * @file plugins/generic/sectionEditorOptions/SectionEditorOptionsPluginEditAssignmentDAO.inc.php
 *
 * Copyright (c) 2013-2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SectionEditorOptionsPluginEditAssignmentDAO
 * @ingroup plugins_generic_sectionEditorOptions
 * @see EditAssignmentDAO
 *
 * @brief Inject a custom EditAssigment object.
 */

import('classes.submission.editAssignment.EditAssignmentDAO');

class SectionEditorOptionsPluginEditAssignmentDAO extends EditAssignmentDAO {
	/**
	 * Return a custom EditAssignment object.
	 * @see EditAssignmentDAO::newDataObject()
	 */
	function newDataObject() {
		import('plugins.generic.sectionEditorOptions.SectionEditorOptionsPluginEditAssignment');
		return new SectionEditorOptionsPluginEditAssignment();
	}
}

?>
