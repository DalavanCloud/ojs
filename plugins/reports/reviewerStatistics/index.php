<?php

/**
 * @defgroup plugins_reports_reviewerStatistics
 */

/**
 * @file plugins/reports/reviewerStatistics/index.php
 *
 * Copyright (c) 2013-2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_reports_reviewerStatistics
 * @brief Wrapper for reviewer statistics plugin.
 */

require_once('ReviewerStatisticsPlugin.inc.php');

return new ReviewerStatisticsPlugin();

?>
