<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   PayToo Corp.
 * @package    GoPayToo (gopaytoo.com)
 * @copyright  Copyright (c) 2013 PayToo Corp.
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */



class Paytoo_GoPayToo_Block_Redirect extends Mage_Core_Block_Abstract
{
    protected function _toHtml()
    {
        $gopaytoo = Mage::getModel('gopaytoo/checkout');

        $form = new Varien_Data_Form();
        $form->setAction($gopaytoo->getUrl())
            ->setId('pay')
            ->setName('pay')
            ->setMethod('POST')
            ->setUseContainer(true);
        $gopaytoo->getFormFields();
        foreach ($gopaytoo->getFormFields() as $field=>$value) {
            if (is_array($value)) {
                foreach ($value as $field2=>$value2) {
                    if (is_array($value2)) {
                        foreach ($value2 as $field3=>$value3) {
                            $form->addField($field.'['.$field2.']'.'['.$field3.']', 'hidden', array('name'=>$field.'['.$field2.']'.'['.$field3.']', 'value'=>$value3, 'size'=>200));    
                        }
                    } else {
                        $form->addField($field.'['.$field2.']', 'hidden', array('name'=>$field.'['.$field2.']', 'value'=>$value2, 'size'=>200));
                    }
                }
            } else {
                $form->addField($field, 'hidden', array('name'=>$field, 'value'=>$value, 'size'=>200));
            }
        }

        $html = '<html><body>';
        $html.= $gopaytoo->getRedirectMessage();
        $html.= $form->toHtml();
        $html.= '<br>';
        $html.= '<script type="text/javascript">document.getElementById("pay").submit();</script>';
        $html.= '</body></html>';


        return $html;
    }
}

?>
