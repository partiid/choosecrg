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

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'choosecrg` (
    `id_choosecrg` int(11) NOT NULL AUTO_INCREMENT,
    PRIMARY KEY  (`id_choosecrg`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

//table with customer requests after registration

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'crgRequests` (
    `id_request` int(11) NOT NULL AUTO_INCREMENT,
    `id_customer` int(11) NOT NULL, 
    `customer_name` varchar(128) NOT NULL,
    `customer_siret` varchar(32) NOT NULL,
    `approved` int(11) NOT NULL,
    `id_group` int(11) NOT NULL ,
    `group_name` varchar(64) NOT NULL,
    PRIMARY KEY (`id_request`)) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

    
foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
