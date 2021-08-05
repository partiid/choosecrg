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

require_once(_PS_MODULE_DIR_.'choosecrg/models/CrgRequests.php'); 

class AdminCrgController extends ModuleAdminController
{
    public function __construct()
    {
          parent::__construct(); 
           
           $this->bootstrap = true;
           $this->table = 'crgRequests';
           $this->identifier = 'id_request'; 

           Shop::addTableAssociation($this->table, array('type' => 'shop'));

           $this->context = Context::getContext();
           $this->_defaultOrderBy = 'a.id_request'; 
           $this->_defaultOrderWay = 'ASC'; 
           
           

           $this->allow_export = true;


       
    

    }
        
        public function initContent()
        {
            parent::initContent(); 

            // $content = $this->context->smarty->fetch(_PS_MODULE_DIR_. 'choosecrg/views/templates/admin/crgController.tpl'); 
            // $this->context->smarty->assign(array(
            //     'content' => $this->content . $content,
            // )); 
            
            
        }
        public function renderList()
        {
            
           $this->addRowAction('edit');
           

           $this->bulk_actions = array (
               
            ); 
            $this->fields_list = array (
                'id_request' => array (
                    'title' => $this->l('ID'),
 
                    'width' => 50, 
                    'lang' => false,
                    
                ), 
                'customer_name' => array (
                    'title' => $this->l('Customer Company'), 
                    'width' => 180,
                    
                ),
                'customer_siret' => array(
                    'title' => $this->l('Customer VAT ID'),
                    'width' => 50
                ),
                'group_name' => array (
                    'title' => $this->l('Desired group'),
                    'width' => 90,
                    
                ),
                'approved' => array (
                    'title' => $this->l('Request approved?'),
                    'width' => 50,
                    'type' => 'text',
                    'active' => 'status',
                    
                   
                ),

                
                );
                
            $lists = parent::renderList();
            parent::initToolbar(); 
            return $lists;
        }

        //render config form for each record
        public function renderForm()
        {
            $id_request = Tools::getValue('id_request');
            
           $this->fields_form = array(
                'legend' => array (
                    'title' => $this->l('Edit'), 
                    'icon' => 'icon-list-ul'
                ), 
                'input' => array (
                    $this->renderSwitchOption('approved',
                    'Approve request', 'Should a client be assigned to desired group?'
                    ),
                    'customer_id' => array (
                        'type' => 'hidden',
                        'name' => 'customer_id',
                        
                    ),
                    'customer_group_id' => array(
                        'type' => 'hidden', 
                        'name' => 'customer_group_id'
                    ),
                    'customer_email' => array(
                        'type' => 'hidden', 
                        'name' => 'customer_email'
                    )
                ),
                'submit' => array (
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right', 
                    'name' => 'approveCrgRequest'
                ),

                
            );
        //get neccesary data from db to the form 

         $this->fields_value = array (
             'customer_id' => $this->getCustomerId($id_request),
             'customer_group_id' => $this->getCustomerGroupId($id_request),
             'customer_email' => $this->getCustomerEmail($this->getCustomerId($id_request)), 

         ); 
         
            $forms = parent::renderForm(); 
            return $forms;
        }

        

        //handle setting form for record
        public function postProcess()
        {
            //approve request
           if($this->approveRequest() == true){
               $this->activateCustomer(); 

               Mail::Send(
                (int)(Configuration::get('PS_LANG_DEFAULT')), // defaut language id
                'requestApproved', // email template file to be use
                'Twoja prośba o rejestrację została zaakceptowana.' ,// email subject
                array(
                    '{email}' => Configuration::get('PS_SHOP_EMAIL'), // sender email address
                    '{message}' => '' // email content
                ),
                Tools::getValue('customer_email'), // receiver email address
                NULL, //receiver name
                NULL, //from email address
                NULL,  //from name
                NULL, //file attachment
                NULL, //mode smtp
                _PS_MODULE_DIR_ . 'choosecrg/mails' //custom template path
            );
        


           } else if($this->approveRequest() == false){
            $this->deactivateCustomer();
           }

        }

        //render switches
    
        private function renderSwitchOption(string $name, string $label, string $desc)
        {
            return [
                'type' => 'switch',
                'label' => $this->l($label),
                'desc' => $desc,
                'name' => $name,
                'values' => [
                    [
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => 'Enabled'
                    ],
                    [
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => 'Disabled'
                    ]
                ]
            ];
        } 
        //get data from db 
        public function getCustomerId($request_id)
        {
            $db = Db::getInstance();
            $q = 'SELECT id_customer FROM '. _DB_PREFIX_ . 'crgRequests WHERE id_request = ' . $request_id  ; 
            return $db->executeS($q)[0]['id_customer'];
            
        }
        public function getCustomerGroupId($request_id)
        {
            $db = Db::getInstance();
            $q = 'SELECT id_group FROM '. _DB_PREFIX_ . 'crgRequests WHERE id_request = ' . $request_id  ; 
            return $db->executeS($q)[0]['id_group'];
        }

        public function getCustomerEmail($customer_id){
            
            $db = Db::getInstance(); 

            $q = 'SELECT email FROM ' ._DB_PREFIX_ .'customer WHERE id_customer= ' . $customer_id; 
            print_r($db->executeS($q));
            return $db->executeS($q)[0]['email'];
        }
      
        
        private function activateCustomer()
        {
            $db = Db::getInstance();
            
            $id_customer = Tools::getValue('customer_id'); 
            $customer_group_id = Tools::getValue('customer_group_id'); 

            $db->update('customer', array(
                'id_default_group' => $customer_group_id, 
                'active' => 1,
                
            ), 'id_customer = '.$id_customer); 

            $db->update('customer_group', array (
                'id_group' => $customer_group_id,
                
            ), 'id_customer = '. $id_customer); 
        }
        //TODO - ADD DEFAULT CUSTOMER GROUP
        private function deactivateCustomer()
        {
            $db = Db::getInstance();
            
            if(Tools::isSubmit('approveCrgRequest') == 1){
                $id_customer = Tools::getValue('customer_id'); 
                
    
                $db->update('customer', array( 
                    'active' => 0,
                    'id_default_group' => Configuration::get('PS_CUSTOMER_GROUP')
                    
                ), 'id_customer = '.$id_customer); 
    
                $db->update('customer_group', array (
                    'id_group' => Configuration::get('PS_CUSTOMER_GROUP'),
                    
                ), 'id_customer='.$id_customer); 
            }
          
        }
        private function approveRequest()
        {
            $db = Db::getInstance(); 

           
            //approve request by switching
            if(Tools::isSubmit('approveCrgRequest')){
                $approval_state = Tools::getValue('approved'); 

                $db->update('crgRequests', array(
                    'approved' => $approval_state
                    ), 'id_customer ='. Tools::getValue('customer_id'),
                ); 

                if($approval_state == 1){
                    return true;
                } else {
                    return false; 
                }
            } 
        }
       
}
?>