<?php

/**
 * MageMind
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/OSL-3.0
 *
 * @package     src_MMind_ProductMeta
 * @copyright   Copyright (c) 2015 MageMind (http://www.magemind.com)
 * @license     http://opensource.org/licenses/OSL-3.0
 */

namespace MMind\ProductMeta;

class ProductMeta
{
    /**
     * Magento Root Folder
     * @var string
     */
    protected $_magento_root;
    protected $_pageSize = 100;

    /**
     * Constructor
     *
     * @param string    $_magento_root
     * @param int       $_store_id
     */
    public function __construct($_magento_root, $_store_id)
    {
        $this->_magento_root = $_magento_root;
        require_once($this->_magento_root.'/app/Mage.php');
        \Mage::app()->setCurrentStore($_store_id);
    }

    /**
     * Set Collection Page Size
     *
     * @param int $_pageSize
     */
    public function setPageSize($_pageSize)
    {
        $this->_pageSize = $_pageSize;
    }

    /**
     * Get Collection Page Size
     *
     * @return int
     */
    public function getPageSize()
    {
        return $this->_pageSize;
    }

    /**
     * Copy Meta Informations of Products
     */
    public function run()
    {
        $collection = \Mage::getModel('catalog/product')->getCollection();
        $collection->addAttributeToSelect("name")
            ->addAttributeToSelect("description")
            ->addAttributeToSelect("short_description")
            ->addAttributeToSelect("meta_title")
            ->addAttributeToSelect("meta_description")
            ->addAttributeToSelect("meta_keyword")
            ->addAttributeToFilter('meta_description', array('null' => true))
            ->addAttributeToFilter('meta_keyword', array('null' => true))
            ->addAttributeToFilter('meta_title', array('null' => true))
            ->setPageSize($this->getPageSize());

        foreach ($collection as $product) {
            if ($product->getData('meta_title') == null) {
                $product->setData('meta_title', $product->getData('name'));
            }
            if ($product->getData('meta_description') == null) {
                $product->setData('meta_description', $product->getData('short_description'));
            }
            if ($product->getData('meta_keyword') == null) {
                $keywords = explode(" ", $product->getData("name"));
                $keywords = implode(",", $keywords);
                $product->setData('meta_keyword', $keywords);
            }
            if ($product->hasDataChanges()) {
                try {
                    $product->save();
                } catch (Exception $ex) {
                    $this->log($ex->getMessage());
                }
                $this->log("SAVE: ".$product->getSku());
            }
        }
    }

    /**
     * Clean Meta Informations of Products
     */
    public function clean()
    {
        $collection = \Mage::getModel('catalog/product')->getCollection();
        $_maxprod = $collection->getSize();

        $this->log("***** TOT PRODUCTS: ".$_maxprod);

        // Current Page Collection Increment
        $increment = 0;
        for ($_count = 0; $_count <= $_maxprod; $_count += $this->getPageSize()) {

            $this->log("***** COUNTER: ".$_count);
            $collection = \Mage::getModel('catalog/product')->getCollection();
            $collection->addAttributeToSelect("name")
                ->addAttributeToSelect("description")
                ->addAttributeToSelect("short_description")
                ->addAttributeToSelect("meta_title")
                ->addAttributeToSelect("meta_description")
                ->addAttributeToSelect("meta_keyword")
                ->addAttributeToFilter('meta_description', array('notnull' => true))
                ->setPageSize($this->getPageSize())
                ->setCurPage($increment);

            // Creazione del file CSV
            /** @var Mage_Catalog_Model_Product $product */
            foreach ($collection as $product) {
                $product->setData('meta_title', null);
                $product->setData('meta_description', null);
                $product->setData('meta_keyword', null);
                if ($product->hasDataChanges()) {
                    try {
                        $product->save();
                    } catch (Exception $ex) {
                        $this->log($ex->getMessage());
                    }
                    $this->log("CLEAN: ".$product->getSku());
                }
            }
            // Increase Current Page Collection
            $increment++;
        }
    }

    /**
     * Log file
     * (only output)
     *
     * @param string $_log
     */
    protected function log($_log)
    {
        echo $_log."\n";
    }

    /**
     * Usage Help
     *
     * @return string
     */
    public function help()
    {
        return <<<USAGE
USAGE:
run         copy meta informations for products (no overwrite)
clean       clean meta informations for products

USAGE;
    }
}