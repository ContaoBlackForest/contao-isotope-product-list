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

namespace ContaoBlackForest\Isotope\ProductList\ContentElement;

use Contao\BackendTemplate;
use Contao\Database;
use Contao\Environment;
use Contao\Input;
use Contao\PageModel;
use Contao\System;
use Haste\Generator\RowClass;
use Haste\Http\Response\HtmlResponse;
use Isotope\ContentElement\ContentElement;
use Isotope\Model\Product;
use Isotope\RequestCache\Sort;

/**
 * This class is for output product list as content element.
 */
class ProductList extends ContentElement
{
    /**
     * The template.
     *
     * @var string
     */
    protected $strTemplate = 'ce_isotope_productlist';

    /**
     * Display a wildcard in the back end
     *
     * @return string
     */
    public function generate()
    {
        if ('BE' === TL_MODE) {
            $template = new BackendTemplate('be_wildcard');

            $template->wildcard = '### ISOTOPE PRODUCT LIST ###';

            $template->title = $this->headline;
            $template->id    = $this->id;
            $template->link  = $this->name;

            return $template->parse();
        }

        return parent::generate();
    }

    /**
     * Compile the content element.
     *
     * @return void
     */
    protected function compile()
    {
        $products = $this->findProducts();

        // No products found
        if (!is_array($products) || empty($products)) {

            $objPage = $GLOBALS['objPage'];
            // Do not index or cache the page
            $objPage->noSearch = 1;
            $objPage->cache    = 0;

            $this->Template->empty    = true;
            $this->Template->type     = 'empty';
            $this->Template->message  = $GLOBALS['TL_LANG']['MSC']['noProducts'];
            $this->Template->products = array();

            return;
        }

        $buffer = array();

        foreach ($products as $product) {
            $config = array(
                'module'      => $this,
                'template'    => ($this->iso_list_layout ?: $product->getRelated('type')->list_template),
                'gallery'     => ($this->iso_gallery ?: $product->getRelated('type')->list_gallery),
                'buttons'     => deserialize($this->iso_buttons, true),
                'useQuantity' => $this->iso_use_quantity,
                'jumpTo'      => $this->findJumpToPage($product),
            );

            if (Environment::get('isAjaxRequest') && Input::post('AJAX_MODULE') == $this->id
                && Input::post(
                    'AJAX_PRODUCT'
                ) === $product->getProductId()) {
                $response = new HtmlResponse($product->generate($config));
                $response->send();
            }

            $css = deserialize($product->cssID, true);

            $buffer[] = array(
                'cssID'   => ('' !== $css[0]) ? ' id="' . $css[0] . '"' : '',
                'class'   => trim('product ' . ($product->isNew() ? 'new ' : '') . $css[1]),
                'html'    => $product->generate($config),
                'product' => $product,
            );
        }

        // HOOK: to add any product field or attribute to mod_iso_productlist template
        if (isset($GLOBALS['ISO_HOOKS']['generateProductList'])
            && is_array(
                $GLOBALS['ISO_HOOKS']['generateProductList']
            )) {
            foreach ($GLOBALS['ISO_HOOKS']['generateProductList'] as $callback) {
                $importCallback = System::importStatic($callback[0]);
                $buffer         = $importCallback->{$callback[1]}($buffer, $products, $this->Template, $this);
            }
        }

        RowClass::withKey('class')
            ->addCount('product_')
            ->addEvenOdd('product_')
            ->addFirstLast('product_')
            ->addGridRows($this->iso_cols)
            ->addGridCols($this->iso_cols)
            ->applyTo($buffer);

        $this->Template->products = $buffer;
    }

    /**
     * Find all products for the list.
     *
     * @return  array
     */
    protected function findProducts()
    {
        $products = unserialize($this->iso_products);

        $options = array();

        $sorting = $this->getSorting();
        if (!empty($sorting)) {
            $options['sorting'] = $sorting;
        }
        if (empty($sorting)) {
            $options['order'] = 'FIELD(tl_iso_product.id, ' . implode(',', $products) . ')';
        }

        $products = Product::findAvailableByIds($products, $options);

        return (null === $products) ? array() : $products->getModels();
    }


    /**
     * Get sorting configuration.
     *
     * @return array
     */
    protected function getSorting()
    {
        $sorting = array();

        if ('' !== $this->iso_listingSortField) {
            $sorting[$this->iso_listingSortField] =
                ('DESC' === $this->iso_listingSortDirection ? Sort::descending() : Sort::ascending());
        }

        return $sorting;
    }

    /**
     * Find jumpTo page for current category scope
     *
     * @param   Product $product The product.
     *
     * @return  PageModel
     */
    protected function findJumpToPage($product)
    {
        global $objPage;
        global $objIsotopeListPage;

        $categories = $product->getCategories();

        // If our current category scope does not match with any product category, use the first product category in the current root page
        if (empty($categories)) {
            $categories = array_intersect(
                $product->getCategories(),
                Database::getInstance()->getChildRecords($objPage->rootId, $objPage->getTable())
            );
        }

        foreach ($categories as $category) {
            $pageModel = PageModel::findByPk($category);

            if ('index' === $pageModel->alias && count($categories) > 1) {
                continue;
            }

            return $pageModel;
        }

        return $objIsotopeListPage ?: $objPage;
    }
}
