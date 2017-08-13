<?php

/**
 * This file is part of contao-isotope-product-list.
 *
 * (c) 2015-2017 ContaoBlackForest
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package    contao-isotope-product-list
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2015-2017 ContaoBlackForest.
 * @license    https://github.com/ContaoBlackForest/contao-isotope-product-list/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoBlackForest\Isotope\ProductList\SelectRi;


use Hofff\Contao\Selectri\Model\Flat\SQLListData;
use Hofff\Contao\Selectri\Util\SearchUtil;

/**
 * This class if for list the isotope products.
 */
class IsotopeProductListData extends SQLListData
{
    /**
     * {@inheritDoc}
     */
    protected function buildSearchExpr($keywordCnt, &$columnCnt)
    {
        $columns   = $this->cfg->getSearchColumns();
        $keyColumn = $this->cfg->getKeyColumn();
        in_array($keyColumn, $columns) || $columns[] = $keyColumn;

        $condition = array();
        foreach ($columns as $column) {
            if ('id' === $column) {
                continue;
            }

            $condition[] = $column . ' LIKE CONCAT(\'%\', CONCAT(?, \'%\'))';
        }
        $condition = implode(' OR ', $condition);

        $columnCnt = count($columns);
        return '(' . implode(') AND (', array_fill(0, $keywordCnt, $condition)) . ')';
    }

    /**
     * {@inheritDoc}
     */
    public function search($search, $limit, $offset = 0)
    {
        $keywords = SearchUtil::parseKeywords($search);
        if (!$keywords) {
            return new \EmptyIterator;
        }

        // Improved search alias.
        $aliasKeyword = implode('-', $keywords);
        if ($search === $aliasKeyword) {
            $keywords = [$aliasKeyword];
        }

        $columnCount = null;

        $query = sprintf(
            $this->buildNodeQuery(),
            $this->buildSearchExpr(count($keywords), $columnCount)
        );

        $parameters = array();
        foreach ($keywords as $keyword) {
            $parameters[] = array_fill(0, $columnCount, $keyword);
        }
        $parameters = call_user_func_array('array_merge', $parameters);

        $primaryKeys = $this->db->prepare($query)->limit($limit, $offset)->execute($parameters)->fetchEach('_key');

        return $this->getNodes($primaryKeys);
    }
}
