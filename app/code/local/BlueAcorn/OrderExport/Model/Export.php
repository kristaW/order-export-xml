<?php
/**
 * Short description for file
 *
 * Long description for file (if any)...
 *
 * @package     BlueAcorn\OrderExport
 * @version     $Id:$
 * @author      Blue Acorn, Inc. <code@blueacorn.com>
 * @copyright   Copyright © 2016 Blue Acorn, Inc.
 */
class BlueAcorn_OrderExport_Model_Export {
    
    public function collectSettings($item) {

        $config = Mage::getConfig()->getNode('blueacorn/export/'.$item);

        return $config;
    }

    /**
     * @todo: add header to file and form the file to be valid xml
     */
    public function exportToXML($filename, $order) {

        $io = new Varien_Io_File();
        
        $dir = Mage::getBaseDir('var') . DS . 'blueacorn' . DS . 'export' . DS . date('Y-m-d');
        $io->checkAndCreateFolder($dir);

        $xmlPath = $dir . DS . $filename ;

        $xmlData = $order->asNiceXml($filename);
        
        $io->open(array('path' => $dir));

        $io->write($xmlPath, $xmlData);

    }

    public function constructXML($config, $order) {

        foreach (get_object_vars($config) as $key => $value) {

            if(strpos($value, '.') !== false) {
                $a = explode(".", $value);
                $m = $order;
                $c = 'order';
                for($i = 0; $i < $a; $i++) {

                    if(is_string($m)) {
//                        $config[$key] = $m;
                        $config->setNode($key, $m);

                        break;
                    }
                    if($m === null || $m === '') {
                        $config->setNode($key, $m);

                        break;
                    }

                    $c .= '->'.$a[$i];

                    switch ($a[$i]) {
                        case "billing":
                            $m = $m->getBillingAddress();
                            break;
                        case "shipping":
                            $m = $m->getShippingAddress();
                            break;
                        case "items":
                            $m = $m->getItemsCollection()->getItems();
                            foreach (get_object_vars($config) as $me => $item) {
                                $config->setNode($me, str_replace('items.', '', $item));
                            }

                            for ($j = 0; $j < count($m); $j++) {

                                $this->constructXML($config, $m[$j]);
                            }
                            break;
                        /**
                         * @todo: needs proper error handling (typos)
                         */
                        default:
                            $m = $m->{$a[$i]};
                            break;
                    }
                }
            } elseif ($order[$value]) {
                $config->setNode($key, $order[$value]);
            } elseif (is_object($value)) {
                $this->constructXML($value, $order);
            } else {
                if(is_object($value)) {
                    var_dump("hello I'm object");
                } elseif (is_array($value)) {
                    var_dump("hello I'm array");
                } else {
                    var_dump("hello I'm either string");
                    var_dump("or I'm unknown at the moment");
                    break;
                }
            }
        }
    }

    public function observeOrderSave($observer) {

        $config = $this->collectSettings('order');
        $order = $observer->getOrder();
        $filename = $order->getIncrementId() .".xml";

        $this->constructXML($config, $order);
        $this->exportToXML($filename, $config);
    }
}