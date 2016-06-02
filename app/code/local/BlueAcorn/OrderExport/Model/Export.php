<?php
/**
 * Exports order into an xml file.
 *
 * @package     BlueAcorn\OrderExport
 * @version     1.0.0.2
 * @author      Blue Acorn, Inc. <code@blueacorn.com>
 * @copyright   Copyright © 2016 Blue Acorn, Inc.
 */
class BlueAcorn_OrderExport_Model_Export {
    public $formater = '';
    public $order = '';

    /**
     * Reads xml node from config
     * @param $item
     * @return Mage_Core_Model_Config_Element
     */
    public function collectSettings($item) {

        $config = Mage::getConfig()->getNode('blueacorn/export_template/'.$item);
        return $config;
    }

    /**
     * Outputs xml file into folder var/blueacorn/export
     *
     * @param $filename
     * @param $content
     * @throws Exception
     */
    public function exportToXML($filename, $content) {

        $io = new Varien_Io_File();
        
        $dir = Mage::getBaseDir('var') . DS . 'blueacorn' . DS . 'export' . DS . date('Y-m-d');
        $io->checkAndCreateFolder($dir);

        $xmlPath = $dir . DS . $filename ;
        $xmlData = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xmlData .= $content->asNiceXml($filename);
        
        $io->open(array('path' => $dir));
        $io->write($xmlPath, $xmlData);
    }

    /**
     * Lauches when order is being submitted into the system
     * @param $observer
     */
    public function observeOrderSave($observer) {

        $template = $this->collectSettings('order');
        $this->order = $observer->getOrder();
        $filename = $this->order->getIncrementId() .".xml";

        $data = array(
            'order' => $this->order,
            'shipping' => $this->order->getShippingAddress(),
            'billing' => $this->order->getBillingAddress()
        );

        $this->formater = new Varien_Filter_Template();

        $this->formater->setVariables($data);

        $this->constructXML($template);
        $this->exportToXML($filename, $template);
    }

    /**
     * Collects data into elements
     * @param $template
     */
    public function constructXML($template) {

        if(is_object($template)) {
            foreach (get_object_vars($template->children()) as $key => $value ) {

                if(is_object($value)) {

                    /**
                     * found attribute foreach, loop this part of xml until the loop ends
                     */
                    if ($value['foreach']) {

                        /**
                         * Corresponds to attribute value. Uses that information
                         * to know what is being looped
                         */
                        $parent = (string)$value['foreach'];

                        if($parent === 'items') {

                            $items = $this->order->getAllVisibleItems();
                            $default = $value;

                            foreach ($items as $itemKey => $item) {

                                $value = $default;
                                $value['id'] = $itemKey;
                                $itemNode = $template->addChild($key);
                                $itemNode ->addAttribute('id', $itemKey);

                                foreach (get_object_vars($value->children()) as $valueKey => $valueChild ) {
                                    $valueChild = str_replace( array('{{var ','}}') , '', $valueChild);

                                    if ($item->getData($valueChild)) {
                                        $itemNode->addChild($valueKey, $item->getData($valueChild));
                                    }
                                }
                            }
                            unset($template->item[0]);
                        }

                    } else {
                        $this->constructXML($value);
                    }
                }

                /** filter does the actual data mining. if result is string we can set it into our xml */
                $result = $this->formater->filter($value);
                if(is_string($result)) {
                    $template->$key = $result;
                }
            }
        } else {
            $this->formater->filter($template);
        }
    }
}