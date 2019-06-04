<?php

require_once ROOT_DIR . '/Action.php';
require_once ROOT_DIR . '/services/MyResearch/lib/Search.php';

require_once ROOT_DIR . '/sys/Pager.php';

class Archive_Results extends Action {
	function launch()
	{
		global $interface;
		global $configArray;
		global $timer;
		global $aspenUsage;
		$aspenUsage->islandoraSearches++;

		// Include Search Engine Class
		require_once ROOT_DIR . '/sys/SolrConnector/Solr.php';
		$timer->logTime('Include search engine');

		// Initialise from the current search globals
		/** @var SearchObject_IslandoraSearcher $searchObject */
		$searchObject = SearchObjectFactory::initSearchObject('Islandora');
		$searchObject->init();
		$searchObject->setPrimarySearch(true);
		$searchObject->addHiddenFilter('!RELS_EXT_isViewableByRole_literal_ms', "administrator");
		$searchObject->addHiddenFilter('!mods_extension_marmotLocal_pikaOptions_showInSearchResults_ms', "no");

		// Build RSS Feed for Results (if requested)
		if ($searchObject->getView() == 'rss') {
			// Throw the XML to screen
			echo $searchObject->buildRSS();
			// And we're done
			exit();
		}else if ($searchObject->getView() == 'excel'){
			// Throw the Excel spreadsheet to screen for download
			echo $searchObject->buildExcel();
			// And we're done
			exit();
		}
		$displayMode = $searchObject->getView();
		if ($displayMode == 'covers') {
			$searchObject->setLimit(24); // a set of 24 covers looks better in display
		}

		// Set Interface Variables
		//   Those we can construct BEFORE the search is executed
		$interface->assign('sortList',   $searchObject->getSortList());
		$interface->assign('rssLink',    $searchObject->getRSSUrl());
		$interface->assign('excelLink',  $searchObject->getExcelUrl());

		// Hide Covers when the user has set that setting on the Search Results Page
		$this->setShowCovers();

		$timer->logTime('Setup Search');

		// Process Search
		$result = $searchObject->processSearch(true, true);
		if ($result instanceof AspenError) {
			AspenError::raiseError($result->getMessage());
		}
		$timer->logTime('Process Search');

		// Some more variables
		//   Those we can construct AFTER the search is executed, but we need
		//   no matter whether there were any results
		$interface->assign('qtime',               round($searchObject->getQuerySpeed(), 2));
		$interface->assign('lookfor',             $searchObject->displayQuery());
		$interface->assign('searchType',          $searchObject->getSearchType());
		// Will assign null for an advanced search
		$interface->assign('searchIndex',         $searchObject->getSearchIndex());

        //Always get spelling suggestions to account for cases where something is misspelled, but still gets results
        $spellingSuggestions = $searchObject->getSpellingSuggestions();
        $interface->assign('spellingSuggestions', $spellingSuggestions['suggestions']);

		// We'll need recommendations no matter how many results we found:
		$interface->assign('topRecommendations',
		$searchObject->getRecommendationsTemplates('top'));
		$interface->assign('sideRecommendations',
		$searchObject->getRecommendationsTemplates('side'));

		// 'Finish' the search... complete timers and log search history.
		$searchObject->close();
		$interface->assign('time', round($searchObject->getTotalSpeed(), 2));
		$interface->assign('savedSearch', $searchObject->isSavedSearch());
		$interface->assign('searchId',    $searchObject->getSearchId());
		$currentPage = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
		$interface->assign('page', $currentPage);

		if ($searchObject->getResultTotal() < 1) {
			// No record found
			$interface->assign('subpage', 'Archive/list-none.tpl');
			$interface->setTemplate('list.tpl');
			$interface->assign('recordCount', 0);

			// Was the empty result set due to an error?
			$error = $searchObject->getIndexError();
			if ($error !== false) {
				// If it's a parse error or the user specified an invalid field, we
				// should display an appropriate message:
				if (is_array($error)){
					$errorMessage = reset($error);
				}else{
					$errorMessage = $error;
				}
				if (stristr($errorMessage, 'org.apache.lucene.queryParser.ParseException') ||
				preg_match('/^undefined field/', $errorMessage)) {
					$interface->assign('parseError', true);

					// Unexpected error -- let's treat this as a fatal condition.
				} else {
					AspenError::raiseError(new AspenError('Unable to process query<br />' .
                        'Solr Returned: ' . $errorMessage));
				}
			}

			$timer->logTime('no hits processing');

		} else {
			$timer->logTime('save search');

			// Assign interface variables
			$summary = $searchObject->getResultSummary();
			$interface->assign('recordCount', $summary['resultTotal']);
			$interface->assign('recordStart', $summary['startRecord']);
			$interface->assign('recordEnd',   $summary['endRecord']);

			$facetSet = $searchObject->getFacetList();
			$interface->assign('facetSet',       $facetSet);

			// Big one - our results
			$recordSet = $searchObject->getResultRecordHTML();
			$interface->assign('recordSet', $recordSet);
			$timer->logTime('load result records');

			// Setup Display
			if ($displayMode == 'covers') {
				$displayTemplate = 'Archive/covers-list.tpl'; // structure for bookcover tiles
			}else{
				$displayTemplate = 'Archive/list-list.tpl'; // structure for regular results
				$displayMode = 'list'; // In case the view is not explicitly set, do so now for display & clients-side functions
				// Process Paging
				$link = $searchObject->renderLinkPageTemplate();
				$options = array('totalItems' => $summary['resultTotal'],
						'fileName'   => $link,
						'perPage'    => $summary['perPage']);
				$pager = new Pager($options);
				$interface->assign('pageLinks', $pager->getLinks());
			}

			$timer->logTime('finish hits processing');
			$interface->assign('subpage', $displayTemplate);
		}

		$interface->assign('displayMode', $displayMode); // For user toggle switches

		// Save the ID of this search to the session so we can return to it easily:
		$_SESSION['lastSearchId'] = $searchObject->getSearchId();

		// Save the URL of this search to the session so we can return to it easily:
		$_SESSION['lastSearchURL'] = $searchObject->renderSearchUrl();

		// clear Exhibit Navigation
		if (!empty($_SESSION['ExhibitContext'])) {
			unset($_SESSION['ExhibitContext'], $_SESSION['placePid'], $_SESSION['placeLabel']);
		}

		//Setup explore more
		$showExploreMoreBar = true;
		if (isset($_REQUEST['page']) && $_REQUEST['page'] > 1){
			$showExploreMoreBar = false;
		}
		$exploreMore = new ExploreMore();
		$exploreMoreSearchTerm = $exploreMore->getExploreMoreQuery();
		$interface->assign('exploreMoreSection', 'archive');
		$interface->assign('showExploreMoreBar', $showExploreMoreBar);
		$interface->assign('exploreMoreSearchTerm', $exploreMoreSearchTerm);

		// Done, display the page
		$interface->assign('sectionLabel', 'Local Digital Archive Results');
		$this->display($searchObject->getResultTotal() ? 'list.tpl' : 'list-none.tpl','Archive Search Results', 'Search/results-sidebar.tpl');
	} // End launch()
}