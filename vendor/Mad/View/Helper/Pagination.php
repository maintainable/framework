<?php
/**
 * Provides methods for converting a numbers into formatted strings.
 * Methods are provided for phone numbers, currency, percentage,
 * precision, positional notation, and file size.
 *
 * @category   Mad
 * @package    Mad_View
 * @subpackage Helper
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * Provides methods for converting a numbers into formatted strings.
 * Methods are provided for phone numbers, currency, percentage,
 * precision, positional notation, and file size.
 *
 * @category   Mad
 * @package    Mad_View
 * @subpackage Helper
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_View_Helper_Pagination extends Mad_View_Helper_Base
{
    /**
     * Renders Digg-style pagination. (We know you wanna!)
     * Returns '1' if there is only one page in total (can't paginate that).
     */
    public function willPaginate($entries, $options=array())
    {
        $totalPages = $entries->pageCount;
        if ($totalPages < 1)  { return; }
        if ($totalPages == 1) { return 1; }

        $page = $entries->currentPage;

        // get options
        $valids = array('href', 'class' => 'pagination', 
                        'prevLabel'   => "&larr; Prev", 
                        'nextLabel'   => 'Next &rarr;', 
                        'innerWindow' => 2, 
                        'outerWindow' => 1);
        $options = Mad_Support_Base::assertValidKeys($options, $valids);
        $href        = $options['href'];        unset($options['href']);
        $innerWindow = $options['innerWindow']; unset($options['innerWindow']);
        $outerWindow = $options['outerWindow']; unset($options['outerWindow']);
        $prevLabel   = $options['prevLabel'];   unset($options['prevLabel']);
        $nextLabel   = $options['nextLabel'];   unset($options['nextLabel']);

        $min = $page - $innerWindow;
        $max = $page + $innerWindow;

        $current   = range($min, $max);
        $beginning = range(1, 1 + $outerWindow);
        $tail      = range($totalPages - $outerWindow, $totalPages);
        $visible   = array_merge($current, $beginning, $tail);

        $links = array();
        foreach (range(1, $totalPages) as $n) {
            if (in_array($n, $visible)) {
                $links[] = $this->_linkOrSpan($n, $n == $page, 'current', $n, $href);
            } elseif ($n == $beginning[sizeof($beginning)-1] + 1 || $n == $tail[0] - 1) {
                $links[] = '... ';          
            }
        }

        if (($prev = $page - 1) > 0) {
            $prevLink = $this->_linkOrSpan($prev, $prev == 0, 'disabled', $prevLabel, $href);
            array_unshift($links, $prevLink);
        }
        if (($succ = $page + 1) <= $totalPages) {
            $nextLink = $this->_linkOrSpan($succ, $succ > $totalPages, 'disabled', $nextLabel, $href);
            array_push($links, $nextLink);
        }
        return $this->contentTag('div', join('', $links), $options);
    }

    /**
     * @param   integer $page
     * @param   boolean $conditionForSpan
     * @param   string  $spanClass
     * @param   string  $text
     * @param   string  $href
     */
    protected function _linkOrSpan($page, $conditionForSpan, $spanClass, $text, $href)
    {
        if ($conditionForSpan) {
            return $this->contentTag('span', $text, array('class' => $spanClass)).' ';
        } else {
            // set order_by param, default to 'created_at'
            list($baseUrl, $params) = $this->_parseUrl($href);
            if ($page == '1' && isset($params['page'])) { 
                unset($params['page']); 
            } else { 
                $params['page'] = $page; 
            }
            $paramStr = $this->_buildParamString($params);
            return "<a href=\"/".$baseUrl.$paramStr."\">$text</a> ";
        }
    }

    // annoyingly parsing urls until we have routes
    protected function _parseUrl($url)
    {
        if (strstr($url, '?')) {
            list($baseUrl, $qs) = explode('?', $url);
            $paramPairs = explode('&', isset($qs) ? $qs : '');
        } else {
            $baseUrl = $url;
            $paramPairs = array();
        }
        // split up all vals
        $params = array();
        foreach ($paramPairs as $paramPair) {
            list($key, $value) = explode('=', $paramPair);
            $params[$key] = $value;
        }
        return array($baseUrl, $params);
    }

    // rejoin params to string for url
    protected function _buildParamString($params)
    {
        $values = array();
        foreach ($params as $key => $value) { 
            $values[] = "$key=$value"; 
        }
        return !empty($values) ? '?'.join('&', $values) : '';
    }
}