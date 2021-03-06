<?php
namespace Nimut\Hellurl\ViewHelpers;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2017 Nicole Cordes (typo3@cordes.co)
 *  (c) 2005-2010 Dmitry Dulepov (dmitry@typo3.org)
 *  (c) 2004-2005 Kasper Skaarhoj (kasperYYYY@typo3.com)
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * This class is a page browser for the HellUrl backend module.
 */
class PageBrowserViewHelper
{
    const PAGES_BEFORE = 1;
    const PAGES_BEFORE_END = 1;
    const PAGES_AFTER = 1;
    const PAGES_AFTER_START = 1;
    const RESULTS_PER_PAGE_DEFAULT = 20;

    protected $currentPage;

    protected $totalPages;

    protected $baseURL;

    protected $resultsPerPage;

    /**
     * Constructor
     */
    public function __construct()
    {
        $urlParameters = $_GET;
        \TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($urlParameters, $_POST);
        $this->currentPage = max(1, intval($urlParameters['page']));
        unset($urlParameters['page']);
        unset($urlParameters['cmd']);
        $this->baseURL = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_REQUEST_SCRIPT') .
            '?' . \TYPO3\CMS\Core\Utility\GeneralUtility::implodeArrayForUrl('', $urlParameters);
        $this->resultsPerPage = self::RESULTS_PER_PAGE_DEFAULT;
    }

    public function getPageBrowser($totalResults, $resultsPerPage = 0)
    {
        if ($resultsPerPage) {
            $this->resultsPerPage = $resultsPerPage;
        }
        $this->calcTotalPages($totalResults);

        $markup = '';

        if ($this->totalPages > 1) {
            $markup = $this->generatePageBrowser();
            $markup = '<table class="pagebrowser"><tr>' . $markup . '</tr></table>';
        }

        return $markup;
    }

    public static function getInlineStyles()
    {
        return '
			TABLE.pagebrowser {
				margin-left: auto;
			}
			TABLE.pagebrowser TR TD {
				padding: 2px 4px;
			}
			TABLE.pagebrowser TR TD.page {
				border: 1px solid #595d66;
			}
		';
    }

    protected function generatePageBrowser()
    {
        $markup = '';
        for ($page = 1; $page <= min($this->totalPages, $this->currentPage, self::PAGES_AFTER_START + 1); $page++) {
            $markup .= $this->createCell($page);
        }

        if ($page < $this->currentPage - self::PAGES_BEFORE) {
            $markup .= $this->createEllipses();
            $page = $this->currentPage - self::PAGES_BEFORE;
        }

        for (; $page <= min($this->totalPages, $this->currentPage + self::PAGES_AFTER); $page++) {
            $markup .= $this->createCell($page);
        }

        if ($page < $this->totalPages - self::PAGES_BEFORE_END) {
            $markup .= $this->createEllipses();
            $page = $this->totalPages - self::PAGES_BEFORE_END;
        }

        for (; $page <= $this->totalPages; $page++) {
            $markup .= $this->createCell($page);
        }

        return $markup;
    }

    protected function createCell($pageNumber)
    {
        $extraClass = '';
        if ($pageNumber != $this->currentPage) {
            $link = array(
                '<a href="' . $this->baseURL . '&amp;page=' . $pageNumber . '">',
                '</a>',
            );
        } else {
            $link = array('', '');
            $extraClass = ' bgColor-20';
        }

        return '<td class="page' . $extraClass . '">' . $link[0] . $pageNumber . $link[1] . '</td>';
    }

    protected function createEllipses()
    {
        return '<td>...</td>';
    }

    protected function calcTotalPages($totalResults)
    {
        $this->totalPages = intval($totalResults / $this->resultsPerPage) +
            (($totalResults % $this->resultsPerPage) != 0 ? 1 : 0);
    }
}
