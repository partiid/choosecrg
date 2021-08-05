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

function getCustomerGroups($choosen = null)
{
    if (!$choosen) {
        $sql = 'SELECT DISTINCT 
    `'._DB_PREFIX_.'group`.`id_group`, `group_name`.`name` FROM `'._DB_PREFIX_.'group` 
    INNER JOIN `'._DB_PREFIX_.'group_lang` as group_name on 
    `group_name`.`id_group` = `'._DB_PREFIX_.'group`.`id_group`';
    } else {
        $sql = 'SELECT DISTINCT 
        `'._DB_PREFIX_.'group`.`id_group`, `group_name`.`name` FROM `'._DB_PREFIX_.'group` 
        INNER JOIN `'._DB_PREFIX_.'group_lang` as group_name on
        `group_name`.`id_group` = `'._DB_PREFIX_.'group`.`id_group` WHERE `group_name`.`id_group` IN('. $choosen.')' ;
    }


    $db = Db::getInstance();

    $db_result = $db->executeS($sql);

    $array = array();

    foreach ($db_result as $group) {
        array_push($array, (object)['id_group' => $group['id_group'], 'name' =>  $group['name']]);
    }

    return $array;
}

function getCheckboxValue($name, $count)
{
    $arr = array();
    for ($i=0; $i <= $count; $i++) {
        if (Tools::getValue($name.'_'.$i) != '' || Tools::getValue($name.'_'.$i) != 0) {
            array_push($arr, Tools::getValue($name.'_'.$i));
        }
    };
    return (object)['setting_name' => $name, 'setting_value' => $arr];
}

//function to deactivate user in order to approve it in controller
function deactivateCustomer($params)
{
     $db = Db::getInstance();
     $customer_id = $params['newCustomer']->id;

     return (bool) $db->update('customer', array(
        'active' => 0
     ), 'id_customer = ' . $customer_id);
}
