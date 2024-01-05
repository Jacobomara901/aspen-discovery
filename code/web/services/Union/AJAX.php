<?php

require_once ROOT_DIR . '/JSON_Action.php';

class Union_AJAX extends JSON_Action {

	/** @noinspection PhpUnused */
	function getCombinedResults() {
		$source = $_REQUEST['source'];
		$numberOfResults = $_REQUEST['numberOfResults'];
		$sectionId = $_REQUEST['id'];
		[
			$className,
			$id,
		] = explode(':', $sectionId);
		$sectionObject = null;
		if ($className == 'LibraryCombinedResultSection') {
			$sectionObject = new LibraryCombinedResultSection();
			$sectionObject->id = $id;
			$sectionObject->find(true);
		} elseif ($className == 'LocationCombinedResultSection') {
			$sectionObject = new LocationCombinedResultSection();
			$sectionObject->id = $id;
			$sectionObject->find(true);
		} else {
			return [
				'success' => false,
				'error' => 'Invalid section id passed in',
			];
		}
		$searchTerm = $_REQUEST['searchTerm'];
		$searchType = $_REQUEST['searchType'];
		$showCovers = $_REQUEST['showCovers'];
		$this->setShowCovers();

		$fullResultsLink = $sectionObject->getResultsLink($searchTerm, $searchType);
		if ($source == 'catalog') {
			$results = $this->getResultsFromSolrSearcher('GroupedWork', $searchTerm, $numberOfResults, $fullResultsLink);
		} elseif ($source == 'dpla') {
			$results = $this->getResultsFromDPLA($searchTerm, $numberOfResults, $fullResultsLink);
		} elseif ($source == 'ebsco_eds') {
			$results = $this->getResultsFromEDS($searchTerm, $numberOfResults, $fullResultsLink);
		} elseif ($source == 'summon') {
			$results = $this->getResultsFromSummon($searchTerm, $numberOfResults, $fullResultsLink);
		} elseif ($source == 'ebscohost') {
			$results = $this->getResultsFromEbscohost($searchTerm, $numberOfResults, $fullResultsLink);
		} elseif ($source == 'events') {
			$results = $this->getResultsFromSolrSearcher('Events', $searchTerm, $numberOfResults, $fullResultsLink);
		} elseif ($source == 'genealogy') {
			$results = $this->getResultsFromSolrSearcher('Genealogy', $searchTerm, $numberOfResults, $fullResultsLink);
		} elseif ($source == 'lists') {
			$results = $this->getResultsFromSolrSearcher('Lists', $searchTerm, $numberOfResults, $fullResultsLink);
		} elseif ($source == 'open_archives') {
			$results = $this->getResultsFromSolrSearcher('OpenArchives', $searchTerm, $numberOfResults, $fullResultsLink);
		} elseif ($source == 'innReach') {
			$results = $this->getResultsFromInnReach($searchType, $searchTerm, $numberOfResults, $fullResultsLink);
		} elseif ($source == 'websites') {
			$results = $this->getResultsFromSolrSearcher('Websites', $searchTerm, $numberOfResults, $fullResultsLink);
		} else {
			$results = "<div>" . translate([
					'text' => 'Showing %1% for %2%.',
					1 => $numberOfResults,
					2 => $source,
					'isPublicFacing' => true,
					'translateParameters' => true,
				]) . "</div>";
		}
		$results .= "<div><a href='" . $fullResultsLink . "' target='_blank'>" . translate([
				'text' => 'Full Results from %1%',
				1 => $sectionObject->displayName,
				'isPublicFacing' => true,
				'translateParameters' => true,
			]) . "</a></div>";

		return [
			'success' => true,
			'results' => $results,
		];
	}

	/**
	 * @param string $searcherType
	 * @param string $searchTerm
	 * @param int $numberOfResults
	 * @param $fullResultsLink
	 * @return string
	 */
	private function getResultsFromSolrSearcher($searcherType, $searchTerm, $numberOfResults, $fullResultsLink) {
		global $interface;
		$interface->assign('viewingCombinedResults', true);
		/** @var SearchObject_SolrSearcher $searchObject */
		$searchObject = SearchObjectFactory::initSearchObject($searcherType);
		//$searchObject->init('local', $searchTerm);
		$searchObject->setLimit($numberOfResults);
		$searchObject->setSearchTerms([
			'index' => $searchObject->getDefaultIndex(),
			'lookfor' => $searchTerm,
		]);
		$searchObject->processSearch(true, false);
		$summary = $searchObject->getResultSummary();
		$records = $searchObject->getCombinedResultsHTML();
		if ($summary['resultTotal'] == 0) {
			$results = '<div class="clearfix"></div><div>No results match your search.</div>';
		} else {
			$formattedNumResults = number_format($summary['resultTotal']);
			$results = "<a href='{$fullResultsLink}' class='btn btn-default combined-results-button'>See all {$formattedNumResults} results <i class='fas fa-chevron-right fa-lg'></i></a><div class='clearfix'></div>";

			$interface->assign('recordSet', $records);
			$interface->assign('showExploreMoreBar', false);
			$results .= $interface->fetch('Search/list-list.tpl');
		}
		return $results;
	}

	private function getResultsFromEbscohost($searchTerm, $numberOfResults, $fullResultsLink) {
		global $interface;
		$interface->assign('viewingCombinedResults', true);
		if ($searchTerm == '') {
			$results = '<div class="clearfix"></div><div>Enter search terms to see results.</div>';
		} else {
			/** @var SearchObject_EbscohostSearcher $ebscohostSearcher */
			$ebscohostSearcher = SearchObjectFactory::initSearchObject("Ebscohost");
			$ebscohostSearcher->init();
			$ebscohostSearcher->setSearchTerms([
				'index' => $ebscohostSearcher->getDefaultIndex(),
				'lookfor' => $searchTerm,
			]);
			$searchSettings = $ebscohostSearcher->getSearchSettings();
			if ($searchSettings != null) {
				foreach ($searchSettings->getDatabases() as $database) {
					if ($database->allowSearching && $database->showInCombinedResults) {
						$ebscohostSearcher->addFilter('db:' . $database->shortName);
					}
				}
			}
			$ebscohostSearcher->processSearch(true, false);
			$summary = $ebscohostSearcher->getResultSummary();
			$records = $ebscohostSearcher->getCombinedResultHTML();
			if ($summary['resultTotal'] == 0) {
				$results = '<div class="clearfix"></div><div>No results match your search.</div>';
			} else {
				$formattedNumResults = number_format($summary['resultTotal']);
				$results = "<a href='{$fullResultsLink}' class='btn btn-default combined-results-button'>See all {$formattedNumResults} results <i class='fas fa-chevron-right fa-lg'></i></a><div class='clearfix'></div>";

				$records = array_slice($records, 0, $numberOfResults);
				global $interface;
				$interface->assign('recordSet', $records);
				$interface->assign('showExploreMoreBar', false);
				$results .= $interface->fetch('Search/list-list.tpl');
			}
		}

		return $results;
	}

	/**
	 * @param string $searchTerm
	 * @param int $numberOfResults
	 * @param string $fullResultsLink
	 * @return string
	 */
	private function getResultsFromEDS($searchTerm, $numberOfResults, $fullResultsLink) {
		global $interface;
		$interface->assign('viewingCombinedResults', true);
		if ($searchTerm == '') {
			$results = '<div class="clearfix"></div><div>Enter search terms to see results.</div>';
		} else {
			/** @var SearchObject_EbscoEdsSearcher $edsSearcher */
			$edsSearcher = SearchObjectFactory::initSearchObject("EbscoEds");
			$edsSearcher->init();
			$edsSearcher->setSearchTerms([
				'index' => $edsSearcher->getDefaultIndex(),
				'lookfor' => $searchTerm,
			]);
			$edsSearcher->processSearch(true, false);
			$summary = $edsSearcher->getResultSummary();
			$records = $edsSearcher->getCombinedResultHTML();
			if ($summary['resultTotal'] == 0) {
				$results = '<div class="clearfix"></div><div>No results match your search.</div>';
			} else {
				$formattedNumResults = number_format($summary['resultTotal']);
				$results = "<a href='{$fullResultsLink}' class='btn btn-default combined-results-button'>See all {$formattedNumResults} results <i class='fas fa-chevron-right fa-lg'></i></a><div class='clearfix'></div>";

				$records = array_slice($records, 0, $numberOfResults);
				global $interface;
				$interface->assign('recordSet', $records);
				$interface->assign('showExploreMoreBar', false);
				$results .= $interface->fetch('Search/list-list.tpl');
			}
		}

		return $results;
	}

	/**
	 * @param $searchTerm
	 * @param $numberOfResults
	 * @param $fullResultsLink
	 * @return string
	 */
	private function getResultsFromDPLA($searchTerm, $numberOfResults, $fullResultsLink) {
		global $interface;
		$interface->assign('viewingCombinedResults', true);
		require_once ROOT_DIR . '/sys/SearchObject/DPLA.php';
		$dpla = new DPLA();
		$dplaResults = $dpla->getDPLAResults($searchTerm, $numberOfResults);
		if (!isset($dplaResults['resultTotal']) || ($dplaResults['resultTotal'] == 0)) {
			$results = '<div class="clearfix"></div><div>No results match your search.</div>';
		} else {
			$formattedNumResults = number_format($dplaResults['resultTotal']);
			$results = "<a href='{$fullResultsLink}' class='btn btn-default combined-results-button' target='_blank'>See all {$formattedNumResults} results <i class='fas fa-chevron-right fa-lg'></i></a><div class='clearfix'></div>";
			$results .= $dpla->formatCombinedResults($dplaResults['records'], false);
		}

		return $results;
	}

	/**
	 * @param $searchTerm
	 * @param $numberOfResults
	 * @param $fullResultsLink
	 * @return string
	 */
	private function getResultsFromSummon($searchTerm, $numberOfResults, $fullResultsLink) {
		global $interface;
		$interface->assign('viewingCombinedResults', true);
		require_once ROOT_DIR . '/sys/SearchObject/SummonSearcher.php';
		$summon= new SummonSearcher();
		$summonResults = $summon->getSummonResults($searchTerm, $numberOfResults);
		if (!isset($summonResults['resultTotal']) || ($summonResults['resultTotal'] == 0)) {
			$results = '<div class="clearfix"></div><div>No results match your search.</div>';
		} else {
			$formattedNumResults = number_format($summonResults['resultTotal']);
			$results = "<a href='{$fullResultsLink}' class='btn btn-default combined-results-button' target='_blank'>See all {$formattedNumResults} results <i class='fas fa-chevron-right fa-lg'></i></a><div class='clearfix'></div>";
			$results .= $summon->formatCombinedResults($summonResults['records'], false);
		}

		return $results;
	}

	/**
	 * @param $searchType
	 * @param $searchTerm
	 * @param $numberOfResults
	 * @param $fullResultsLink
	 * @return string
	 */
	private function getResultsFromInnReach($searchType, $searchTerm, $numberOfResults, $fullResultsLink) {
		global $interface;
		$interface->assign('viewingCombinedResults', true);
		require_once ROOT_DIR . '/sys/InterLibraryLoan/InnReach.php';
		if ($searchTerm == '') {
			$results = '<div class="clearfix"></div><div>Enter search terms to see results.</div>';
		} else {
			$innReach = new InnReach();
			$searchTerms = [
				[
					'index' => $searchType,
					'lookfor' => $searchTerm,
				],
			];
			$innReachResults = $innReach->getTopSearchResults($searchTerms, $numberOfResults);
			global $interface;
			if ($innReachResults['resultTotal'] == 0) {
				$results = '<div class="clearfix"></div><div>No results match your search.</div>';
			} else {
				$formattedNumResults = number_format($innReachResults['resultTotal']);
				$results = "<a href='{$fullResultsLink}' class='btn btn-default combined-results-button' target='_blank'>See all {$formattedNumResults} results <i class='fas fa-chevron-right fa-lg'></i></a><div class='clearfix'></div>";
				$interface->assign('innReachResults', $innReachResults['records']);
				$results .= $interface->fetch('Union/innReach.tpl');
			}
		}
		return $results;
	}
}
