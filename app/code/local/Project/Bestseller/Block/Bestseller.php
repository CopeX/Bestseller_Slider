<?php

/**
 * Created by PhpStorm.
 * User: rhutterer
 * Date: 17.11.14
 * Time: 13:05
 */
class Project_Bestseller_Block_Bestseller extends Mage_Catalog_Block_Product_Abstract
{
    public function __construct()
    {
        $this->setHeader(Mage::getStoreConfig("bestseller/general/heading"));
        $this->setImageHeight((int)Mage::getStoreConfig("bestseller/general/thumbnail_height"));
        $this->setImageWidth((int)Mage::getStoreConfig("bestseller/general/thumbnail_width"));
        $this->setTimePeriod((int)Mage::getStoreConfig("bestseller/general/time_period"));
        $this->setAddToCart((bool)Mage::getStoreConfig('bestseller/general/add_to_cart'));
        $this->setWishlist((bool)Mage::getStoreConfigFlag("bestseller/general/active"));
        $this->setAddToCompare((bool)Mage::getStoreConfig("bestseller/general/add_to_compare"));
        $this->setShowPrice((bool)Mage::getStoreConfig('bestseller/general/products_price'));

        $this->setData('template', 'copex/bestseller/bestseller.phtml');
        parent::_construct();
    }

    public function isEnabled()
    {
        return (bool)Mage::getStoreConfig('bestseller/general/enabled');
    }

    function getBestsellerProducts()
    {
        $storeId = (int)Mage::app()->getStore()->getId();
        // Date
        $date = new Zend_Date();
        $toDate = $date->setDay(1)->getDate()->get('Y-MM-dd');
        $fromDate = $date->subYear(1)->getDate()->get('Y-MM-dd');
        $collection = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->addStoreFilter()
            ->addPriceData()
            ->addTaxPercents()
            ->addUrlRewrite()
            ->setPage(1, (int)Mage::getStoreConfig('bestseller/general/number_of_items'));
        $collection->getSelect()
            ->joinLeft(
                array('aggregation' => $collection->getResource()->getTable('sales/bestsellers_aggregated_yearly')),
                "e.entity_id = aggregation.product_id AND aggregation.store_id={$storeId} AND aggregation.period BETWEEN '{$fromDate}' AND '{$toDate}'",
                array('SUM(aggregation.qty_ordered) AS sold_quantity')
            )
            ->group('e.entity_id')
            ->order(array('sold_quantity DESC', 'e.created_at'));

        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);

        Mage::log($collection->getSize());

        return $collection->load();


    }

    public function getConfig($name, $param = null)
    {
        /* @var $helper Mage_Core_Helper_Data */
        $helper = Mage::helper('core');

        switch ($name) {
            case 'enableCoundown':
                return (bool)$this->getData('countdown');
                break;
            case 'countdown':
                return $helper->jsonEncode(array(
                    'enable' => (bool)$this->getData('countdown'),
                    'yearText' => Mage::helper('widgetproducts')->__('years'),
                    'monthText' => Mage::helper('widgetproducts')->__('months'),
                    'weekText' => Mage::helper('widgetproducts')->__('weeks'),
                    'dayText' => Mage::helper('widgetproducts')->__('days'),
                    'hourText' => Mage::helper('widgetproducts')->__('hours'),
                    'minText' => Mage::helper('widgetproducts')->__('mins'),
                    'secText' => Mage::helper('widgetproducts')->__('secs'),
                    'yearSingularText' => Mage::helper('widgetproducts')->__('year'),
                    'monthSingularText' => Mage::helper('widgetproducts')->__('month'),
                    'weekSingularText' => Mage::helper('widgetproducts')->__('week'),
                    'daySingularText' => Mage::helper('widgetproducts')->__('day'),
                    'hourSingularText' => Mage::helper('widgetproducts')->__('hour'),
                    'minSingularText' => Mage::helper('widgetproducts')->__('min'),
                    'secSingularText' => Mage::helper('widgetproducts')->__('sec')
                ));
                break;
            case 'carousel':
                return $helper->jsonEncode(array(
                    'enable' => (bool)$this->getData('scroll'),
                    'pagination' => (bool)$this->getData('paging'),
                    'autoPlay' => is_numeric($this->getData('autoplay')) ? (int)$this->getData('autoplay') : false,
                    'items' => is_numeric($this->getData('column')) ? (int)$this->getData('column') : 4,
                    'singleItem' => $this->getData('column') == 1,
                    'lazyLoad' => true,
                    'lazyEffect' => false,
                    'addClassActive' => true,
                    'navigation' => (bool)$this->getData('navigation'),
                    'navigationText' => array($this->getData('navigation_prev'), $this->getData('navigation_next'))
                ));
                break;
            case 'widget_title':
                return $this->escapeHtml($this->getData('widget_title'));
                break;
            case 'id':
                return $helper->uniqHash(is_string($param) ? $param : 'block-');
                break;
            case 'row':
                return is_numeric($this->getData('row')) ? (int)$this->getData('row') : 1;
                break;
            case 'column':
                return is_numeric($this->getData('column')) ? (int)$this->getData('column') : 4;
                break;
            case 'limit':
                return is_numeric($this->getData('limit')) ? (int)$this->getData('limit') : 1;
                break;
            case 'classes':
                return $this->getData('classes') . ($this->getData('animate') ? ' ' . $this->getData('animation') : '');
                break;
            default:
                return $this->getData($name);
        }
    }

}