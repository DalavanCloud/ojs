<?php

import('classes.plugins.ReportPlugin');

class ReviewerStatisticsPlugin extends ReportPlugin {

	/**
	 * @see LazyLoadPlugin::register()
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		$this->addLocaleData();

		return $success;
	}

	/**
	 * @see LazyLoadPlugin::getName()
	 */
	function getName() {
		return 'reviewerStatisticsPlugin';
	}

	/**
	 * @see PKPPlugin::getDisplayName()
	 */
	function getDisplayName() {
		return Locale::translate('plugins.generic.reviewerstatistics.name');
	}

	/**
	 * @see PKPPlugin::getDescription()
	 */
	function getDescription() {
		return Locale::translate('plugins.generic.reviewerstatistics.description');
	}

	/**
	 * @see ReportPlugin::display()
	 */
	function display(&$args) {
		$templateManager = &TemplateManager::getManager();
		$statisticsYear = Request::getUserVar('statisticsYear');
		$journal = Request::getJournal();
		$journalId=$journal->getJournalId();

		if (empty($statisticsYear)) $statisticsYear = date('Y');

		if ($statisticsYear != date('Y')) {
			$mes='12';
		} else {
			$mes=date('m');
		}

		$nextYear=0;
		$prevYear=0;

		$templateManager->assign('statisticsYear', $statisticsYear);

        if ($statisticsYear=='ALL') {
			$fromDate = mktime(0, 0, 1, 1, 1, '2006');
			$toDate = mktime(23, 59, 59, 12, 31, date('Y'));
			$nextYear=date('Y');
			$prevYear=$nextYear-1;
		} else {
			$fromDate = mktime(0, 0, 1, 1, 1, $statisticsYear);
			$toDate = mktime(23, 59, 59, 12, 31, $statisticsYear);
			$nextYear=$statisticsYear+1;
			$prevYear=$statisticsYear-1;
		}

		$reviewerStatistics = $this->_getReviewersStatistics($journalId, $fromDate, $toDate);

		$templateManager->assign('nextYear', $nextYear);
		$templateManager->assign('prevYear', $prevYear);
		$templateManager->assign('reviewerStatistics', $reviewerStatistics);
		$templateManager->display($this->getTemplatePath() . 'statisticsReviewers.tpl');

		return false;
	}


	//
	// Private helper methods.
	//
	/**
	 * Get reviewer stats from database.
	 * @param $journalId int
	 * @param $dateStart int
	 * @param $dateEnd int
	 * @return array
	 */
	function _getReviewersStatistics($journalId, $dateStart = null, $dateEnd = null) {
		// Get any DAO instance.
		$dao = DAORegistry::getDAO('UserDAO'); /* @var $dao DAO */

		$result =& $dao->retrieve(
			' SELECT u.user_id, u.email, a.article_id, u.username, u.first_name,u.last_name, u.middle_name, a.journal_id,ra.recommendation ,ra.declined, ra.cancelled, ra.date_assigned, ra.date_confirmed, ra.date_completed '.
			' FROM review_assignments AS ra '.
			' LEFT JOIN articles  AS a ON ra.submission_id = a.article_id '.
			' LEFT JOIN users  AS u ON u.user_id = ra.reviewer_id '.
			' WHERE a.journal_id='.$journalId.
			' AND NOT ra.cancelled = 1 '.
			' AND date_assigned >= '.$dao->datetimeToDB($dateStart).
			' AND date_assigned <= '.$dao->datetimeToDB($dateEnd).
			' ORDER BY u.username, a.article_id, ra.date_assigned'
		);

		$reviewers = array();

		$returner = array(
			'username' => '',
			'email' => '',
			'name' => '',
			'invited' => 0,
			'accepted' => 0,
			'completed' => 0,
			'declined' => 0
		);

		$reviewerId = 0;

		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			if ($row['user_id'] != $reviewerId) {
				if ($reviewerId != 0 ) {
					$reviewers[$reviewerId] = $returner;
				}
				$reviewerId = $row['user_id'];
				$returner['username']= $row['username'];
				$returner['email']= $row['email'];
				$returner['name']=$row['first_name']." ".$row['middle_name']." ".$row['last_name'];
				$returner['invited']=0;
				$returner['accepted']=0;
				$returner['completed']=0;
				$returner['declined']=0;
			}

			$returner['invited'] = $returner['invited'] + 1;
			if ($row['date_confirmed'] > 0 && $row['declined'] != 1) { $returner['accepted']= $returner['accepted'] + 1;  }
			if ($row['date_completed'] > 0) { $returner['completed']= $returner['completed'] + 1;  }
			if ($row['declined'] == 1) { $returner['declined']=$returner['declined'] + 1;  }
			$result->moveNext();
		}

		$reviewers[$row['user_id']] = $returner;

		$result->Close();
		unset($result);

		return $reviewers;
	}
}

?>
