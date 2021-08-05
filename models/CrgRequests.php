<?php
/**
* 2007-2021 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    Marek Łysiak
*  @copyright 2021 Marek Łysiak
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class CrgRequests extends ObjectModel
{
    public $id_request;
    public $id_customer;
    public $id_group;
    public $approved;
    public $group_name;
    public $customer_name;


    public static $definition = [
        'table' => 'crgRequests',
        'primary' => 'id_request',
        'fields' => array(
            'id_request' => ['type' => self::TYPE_INT, 'validate' => 'isAnything', 'required' => true],
            'id_customer' => ['type' => self::TYPE_INT, 'validate' => 'isAnything', 'required' => true],
            'approved' => ['type' => self::TYPE_INT, 'validate' => 'isAnything', 'required'=>true],
            'id_group' => ['type' => self::TYPE_INT, 'validate' => 'isAnything', 'required' => true],
            'group_name' => ['type' => self::TYPE_STRING, 'validate' => 'isAnything', 'required' => true],
            'customer_name' => ['type' => self::TYPE_STRING, 'validate' => 'isAnything', 'required' => true],
            'customer_siret' => ['type' => self::TYPE_STRING, 'validate' => 'isAnything', 'required' => true]
        ),
    ];
}
