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

\Contao\System::loadLanguageFile('tl_module');

/**
 * Add palettes
 */
$GLOBALS['TL_DCA']['tl_content']['palettes']['isotope_productList'] =
    '{type_legend},type,headline;{include_legend},iso_products;{config_legend},iso_listingSortField,iso_listingSortDirection,iso_cols,iso_use_quantity,iso_buttons,iso_addProductJumpTo;{template_legend},customTpl,iso_gallery,iso_list_layout;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';

/**
 * Add fields
 */
$GLOBALS['TL_DCA']['tl_content']['fields'] = array_merge(
    $GLOBALS['TL_DCA']['tl_content']['fields'],
    array(
        'iso_products'             => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['iso_products'],
            'exclude'   => true,
            'inputType' => 'selectri',
            'eval'      => array(
                'min'         => 1,
                'max'         => 9999,
                'searchLimit' => 9999,
                'tl_class'    => 'clr',
                'class'       => 'checkbox',
                'data'        => 'ContaoBlackForest\Isotope\ProductList\SelectRiFactory\IsotopeProductFactory',
            ),
            'sql'       => "blob NULL"
        ),
        'iso_gallery'              => array
        (
            'label'      => &$GLOBALS['TL_LANG']['tl_module']['iso_gallery'],
            'exclude'    => true,
            'inputType'  => 'select',
            'foreignKey' => \Isotope\Model\Gallery::getTable() . '.name',
            'eval'       => array('includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'),
            'sql'        => "int(10) unsigned NOT NULL default '0'",
        ),
        'iso_list_layout'          => array
        (
            'label'            => &$GLOBALS['TL_LANG']['tl_module']['iso_list_layout'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => function () {
                return \Isotope\Backend::getTemplates('iso_list_');
            },
            'eval'             => array('includeBlankOption' => true, 'tl_class' => 'w50', 'chosen' => true),
            'sql'              => "varchar(64) NOT NULL default ''",
        ),
        'iso_use_quantity'         => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_use_quantity'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array('tl_class' => 'w50'),
            'sql'       => "char(1) NOT NULL default ''",
        ),
        'iso_cols'                 => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_cols'],
            'exclude'   => true,
            'default'   => 1,
            'inputType' => 'text',
            'eval'      => array('maxlength' => 1, 'rgxp' => 'digit', 'tl_class' => 'w50'),
            'sql'       => "int(1) unsigned NOT NULL default '1'",
        ),
        'iso_buttons'              => array
        (
            'label'            => &$GLOBALS['TL_LANG']['tl_module']['iso_buttons'],
            'exclude'          => true,
            'inputType'        => 'checkboxWizard',
            'default'          => array('add_to_cart'),
            'options_callback' => array('Isotope\Backend\Module\Callback', 'getButtons'),
            'eval'             => array('multiple' => true, 'tl_class' => 'clr'),
            'sql'              => 'blob NULL',
        ),
        'iso_listingSortField'     => array
        (
            'label'            => &$GLOBALS['TL_LANG']['tl_module']['iso_listingSortField'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => array('Isotope\Backend\Module\Callback', 'getSortingFields'),
            'eval'             => array('includeBlankOption' => true, 'tl_class' => 'clr w50'),
            'sql'              => "varchar(255) NOT NULL default ''",
            'save_callback'    => array
            (
                array('Isotope\Backend', 'truncateProductCache'),
            ),
        ),
        'iso_listingSortDirection' => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_listingSortDirection'],
            'exclude'   => true,
            'default'   => 'DESC',
            'inputType' => 'select',
            'options'   => array('DESC', 'ASC'),
            'reference' => &$GLOBALS['TL_LANG']['tl_module']['sortingDirection'],
            'eval'      => array('tl_class' => 'w50'),
            'sql'       => "varchar(8) NOT NULL default ''",
        ),
        'iso_cart_jumpTo'          => array
        (
            'label'       => &$GLOBALS['TL_LANG']['tl_module']['iso_cart_jumpTo'],
            'exclude'     => true,
            'inputType'   => 'pageTree',
            'foreignKey'  => 'tl_page.title',
            'eval'        => array('fieldType' => 'radio', 'tl_class' => 'clr'),
            'explanation' => 'jumpTo',
            'sql'         => "int(10) unsigned NOT NULL default '0'",
            'relation'    => array('type' => 'hasOne', 'load' => 'lazy'),
        ),
        'iso_addProductJumpTo'     => array
        (
            'label'       => &$GLOBALS['TL_LANG']['tl_module']['iso_addProductJumpTo'],
            'exclude'     => true,
            'inputType'   => 'pageTree',
            'foreignKey'  => 'tl_page.title',
            'eval'        => array('fieldType' => 'radio', 'tl_class' => 'clr'),
            'explanation' => 'jumpTo',
            'sql'         => "int(10) unsigned NOT NULL default '0'",
            'relation'    => array('type' => 'hasOne', 'load' => 'lazy'),
        )
    )
);
