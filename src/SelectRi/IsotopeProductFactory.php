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

use Hofff\Contao\Selectri\Exception\SelectriException;
use Hofff\Contao\Selectri\Model\Flat\SQLListDataConfig;
use Hofff\Contao\Selectri\Model\Flat\SQLListDataFactory;
use Hofff\Contao\Selectri\Util\LabelFormatter;
use Hofff\Contao\Selectri\Widget;

/**
 * This class is the factory for selectri widget.
 */
class IsotopeProductFactory extends SQLListDataFactory
{
    /**
     * Create the data for selectri widget.
     *
     * @param Widget|null $widget The widget.
     *
     * @return \Hofff\Contao\Selectri\Model\Data|\Hofff\Contao\Selectri\Model\Flat\SQLListData
     *
     * @throws SelectriException
     */
    public function createData(Widget $widget = null)
    {
        if (!$widget) {
            throw new SelectriException('Selectri widget is required to create a SQLAdjacencyTreeData');
        }

        $widget->setDisableBrowsing(true);

        $config = clone $this->getConfig();
        $this->prepareConfig($config);

        return new IsotopeProductListData($widget, $this->getDatabase(), $config);
    }

    /**
     * Prepare the configuration for the selectri widget.
     *
     * @param SQLListDataConfig $config The configuration.
     *
     * @return void
     */
    protected function prepareConfig(SQLListDataConfig $config)
    {
        parent::prepareConfig($config);

        $config->setTable('tl_iso_product');

        $labelFormatter = new LabelFormatter();
        $labelFormatter->setFormat('%s (ID %s) (SKU %s)');
        $labelFormatter->setFields(['name', 'id', 'sku']);
        $config->setLabelCallback([$labelFormatter, 'format']);

        $config->setSearchColumns(['name', 'sku', 'alias']);

        parent::prepareConfig($config);
    }
}
