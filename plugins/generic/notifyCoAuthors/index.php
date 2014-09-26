<?php

/**
 * @defgroup plugins_generic_notifyCoAuthors
 */

/**
 * @file plugins/generic/notifyCoAuthors/index.php
 *
 * Copyright (c) 2013-2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_generic_notifyCoAuthors
 * @brief Wrapper for notify co-authors plugin.
 *
 */
require_once('NotifyCoAuthorsPlugin.inc.php');

return new NotifyCoAuthorsPlugin();

?>
