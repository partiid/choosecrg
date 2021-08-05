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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2021 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}
use PrestaShop\PrestaShop\Core\MailTemplate\Layout\Layout;
use PrestaShop\PrestaShop\Core\MailTemplate\ThemeCatalogInterface;
use PrestaShop\PrestaShop\Core\MailTemplate\ThemeCollectionInterface;
use PrestaShop\PrestaShop\Core\MailTemplate\ThemeInterface;

include(dirname(__FILE__).'/sql/tools.php');

class Choosecrg extends Module
{
    

    protected $config_form = false;

    private $config_fields = array(
        'CRG_CHOOSE_ALLOWED_GROUPS',
        'CRG_NOTIFICATIONS_EMAIL'
     ); 
   
    

    public function __construct()
    {
        $this->name = 'choosecrg';
        $this->tab = 'others';
        $this->version = '1.0.0';
        $this->author = 'Marek Łysiak';
        $this->need_instance = 1;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('CRG - Customer Registration Group');
        $this->description = $this->l('Allows customers to choose a group on sign up.
         Approve customer requests from back office to assign them to their desired group.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module? 
        All the customer requests will be removed and all customers assigned to groups will stay in their groups.');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
         

         foreach($this->config_fields as $default) {
             Configuration::set($default, ' '); 
         }; 

        

        include(dirname(__FILE__).'/sql/install.php');

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('displayHome') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayCustomerAccountForm') &&
            $this->registerHook('AdditionalCustomerFormFields') &&
            $this->registerHook('validateCustomerFormFields') &&
            $this->registerHook('actionCustomerAccountAdd') &&
            $this->registerHook('actionCustomerAccountUpdate') &&
            $this->registerHook(ThemeCatalogInterface::LIST_MAIL_THEMES_HOOK) && 
            $this->installTab();
    }
    public function installTab()
    {

        $tab_id= Tab::getIdFromClassName('Choosecrg'); 

        if($tab_id == false){
            $tab = new Tab(); 
            $tab->module = $this->name; 
            $tab->class_name = 'AdminCrg'; 
            $tab->name[$this->context->language->id] = $this->l('Customer registrations approval');
            $tab->parent_class_name = 'AdminParentCustomer';            
            $tab->id_parent = (int) Tab::getIdFromClassName('AdminParentCustomer'); 
    
            $tab->add(); 
            return $tab->save();
        }
         

    }
    public function uninstallTab()
    {
        $tabId = (int) Tab::getIdFromClassName('Choosecrg');
        if (!$tabId) {
            return true;
        }

        $tab = new Tab($tabId);

        return $tab->delete();
    }
   
    public function uninstall()
    {
        foreach($this->config_fields as $default){
            Configuration::deleteByName($default); 
        }

        include(dirname(__FILE__).'/sql/uninstall.php');

        return parent::uninstall();
    }


    public function hookActionListMailThemes(array $hookParams)
    {
        if (!isset($hookParams['mailThemes'])) {
            return;
        }

        /** @var ThemeCollectionInterface $themes */
        $themes = $hookParams['mailThemes'];

        /** @var ThemeInterface $theme */
        foreach ($themes as $theme) {
            if (!in_array($theme->getName(), ['classic', 'modern'])) {
                continue;
            }

            // Add a layout to each theme (don't forget to specify the module name)
            $theme->getLayouts()->add(new Layout(
                'choosecrg',
                _PS_MODULE_DIR_. 'choosecrg/mails/layouts/custom_' . $theme->getName() . '_layout.html.twig',
                '',
                $this->name
            ));


        }
    }
    
    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitChoosecrgModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitChoosecrgModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        
        $customer_groups = getCustomerGroups(); 

        $groups = array_map(function($object) {return ['id' => $object->id_group, 'name' => $object->name, 'val' => $object->id_group] ;}, $customer_groups); //change to array of arrays
        
        // $groups_checkboxes[] = array(
        //     'type' => 'checkbox',
        //     'required' => true,
        //     'multiple' => true,
        //     'desc' => $this->l('Jakie grupy klientów klienci mogą wybrać?'), 
        //     'values' => 
        //     array (
        //         'query' => $groups,
        //         'id' => 'id',
        //         'name' => 'name', 
        //         'val' => 'val'
                
        //     ),
        //     'name' => 'CRG_CHOOSE_ALLOWED_GROUPS',
        //     'id' => 'id'
            
        // );
       
       
      

        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'checkbox',
                        'required' => true,
                        'multiple' => true,
                        'desc' => $this->l('Which customer groups may customers choose on registration?'), 
                        'label' => $this->l('Customer groups'),
                        'values' => 
                        array (
                            'query' => $groups,
                            'id' => 'id',
                            'name' => 'name', 
                            'val' => 'val'
                            
                        ),
                        'name' => 'CRG_CHOOSE_ALLOWED_GROUPS',
                        'id' => 'id'
                    ), 
                 array (
                    'type' => 'text',
                    'label' => $this->l('Email Address'),
                    'desc' => 'Email to receive notifications about new request',
                    'name' => 'CRG_NOTIFICATIONS_EMAIL',
                    'size' => 20
                )
             ),
                 
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        $values = array(); 

        $customer_groups = getCustomerGroups(); 
        $groups = array_map(function($object) {return ['id' => $object->id_group, 'name' => $object->name, 'val' => $object->id_group] ;}, $customer_groups); //change to array of arrays
        
        $allowed_groups = explode (',', Configuration::get('CRG_CHOOSE_ALLOWED_GROUPS')); 
        
        //handling checkboxes values
        for ($i=0; $i < count($groups); $i++) { 
           foreach($allowed_groups as $group){
               $values['CRG_CHOOSE_ALLOWED_GROUPS_'.$group] = true; 
           }
        };

        foreach($this->config_fields as $default){
            array_push($values, $values[$default] = Configuration::get($default)); 
        }
        

        
         return $values; 
         /*array (
             'CRG_CHOOSE_ALLOWED_GROUPS' => Configuration::get('CRG_CHOOSE_ALLOWED_GROUPS'),
             'CRG_NOTIFICATIONS_EMAIL' => Configuration::get('CRG_NOTIFICATIONS_EMAIL'),
         ); */ 

         

    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {

        $customer_groups = getCustomerGroups(); 
        
        //$this->getConfigFormValues();
        
        //handling checkboxes
        $groups = array_map(function($object) {return ['id' => $object->id_group, 'name' => $object->name, 'val' => $object->id_group] ;}, $customer_groups); //change to array of arrays

        $cgr_cag = getCheckboxValue('CRG_CHOOSE_ALLOWED_GROUPS', count($groups)); 

        //todo - sort out defaults handling
        $notification_email = Tools::getValue('CRG_NOTIFICATIONS_EMAIL'); 


        Configuration::updateValue($cgr_cag->setting_name, join(",", $cgr_cag->setting_value));
        Configuration::updateValue('CRG_NOTIFICATIONS_EMAIL', $notification_email); 
       
        

        

        // update earlier settings
          /*foreach (array_keys($form_values) as $key) {
              if($key != 'CRG_CHOOSE_ALLOWED_GROUPS'){
                Configuration::updateValue($key, Tools::getValue($key));
              }

            } */ 
        //  }
    }

    public function sendMail()
    {
        Mail::Send(
            (int)(Configuration::get('PS_LANG_DEFAULT')), // defaut language id
            'newCustomerRequest', // email template file to be use
            'New customer is awaiting approval', // email subject
            array(
                '{email}' => Configuration::get('PS_SHOP_EMAIL'), // sender email address
                '{message}' => 'New customer has signed up. His request is awaiting your approval' // email content
            ),
            Configuration::get('CRG_NOTIFICATIONS_EMAIL'), // receiver email address
            NULL, //receiver name
            NULL, //from email address
            NULL,  //from name
            NULL, //file attachment
            NULL, //mode smtp
            _PS_MODULE_DIR_ . 'choosecrg/mails' //custom template path
        );
    }
   

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJqueryUI('ui.dialog'); 
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
      
        
    }

   

    public function hookDisplayCustomerAccountForm()
    {
        
        //return $this->display(__FILE__, 'customerForm.tpl');
        
    
    }

    //function to add custom field in customer form
    public function hookAdditionalCustomerFormFields($params)
    {

    

       //getting the allowed groups from the config to display on front 
    $selected_groups = Configuration::get('CRG_CHOOSE_ALLOWED_GROUPS'); //selected group from the backoffice by admin to allow customers choose from

    $customer_groups = getCustomerGroups($selected_groups); //get customer groups from database 

    $groups = array_map(function($object) {return [$object->id_group => $object->name] ;}, $customer_groups); 
   
    $merged = call_user_func_array("array_merge", $groups); //merge array 
     
    $to_reduce = array_replace($merged, $groups); 
    
     $reduced_result = array_reduce($to_reduce, function($id, $name) {
        return $id + $name;
    }, []);
    
    
    
    //create the extra field 
       $extra_fields = array(); 
       $extra_fields['group'] = (new FormField)
       ->setName('group')
       ->setAvailableValues($reduced_result)
       ->setType('radio-buttons')
       ->setLabel($this->l('Typ konta'))
       ->setRequired(true); 

    
       return $extra_fields;
    }
    //validating the new field
    public function hookValidateCustomerFormFields($params)
    {
       
     
    }

    //called after account creation - insert data into requests table
    public function hookActionCustomerAccountAdd($params)
    {
        $db = Db::getInstance(); 


       $customer_id = $params['newCustomer']->id; 
       $customer_name = $params['newCustomer']->company; 
       $customer_siret = $params['newCustomer']->siret; 
       

       $group = Tools::getValue('group', ''); 

        //get customergroup name
        $group_name  = $db->executeS('SELECT name FROM '. _DB_PREFIX_ . 'group_lang WHERE id_group= ' . $group.';'); 
        

        //deactive customer on registration and redirect to homepage to set the cookie and display notification
         deactivateCustomer($params);

         //logout customer
         $params['newCustomer']->logout(); 
         
       
        $this->context->cookie->__set('displaySignUpNotification', '1'); 

        $this->sendMail(); 


       return (bool) $db->insert('crgRequests', [
           'id_customer' => $customer_id,
           'id_group' => $group,
           'approved' => 0,
           'group_name' => $group_name[0]['name'],
           'customer_name' => $customer_name,
           'customer_siret' => $customer_siret
       ]
           ); 

        
    }
    //handle action when customer is trying to change the account type 
    public function hookActionCustomerAccountUpdate($params)
    {
        $this->context->cookie->__set('displayErrorNotification', '1'); 
        
    }


    //used to display modal notifications after redirect to home and 
    public function hookDisplayHome()
    {
        //if cookie after registration isset
        if($this->context->cookie->__isset('displaySignUpNotification')){
            $this->context->cookie->__unset('displaySignUpNotification');
            return $this->display(__FILE__, 'signUpNotification.tpl');
        }
        if($this->context->cookie->__isset('displayErrorNotification')){
            $this->context->cookie->__unset('accountUpdateNotification');
            return $this->display(__FILE__, 'accountUpdateNotification.tpl'); 
        }
        
        


    }
   

    
     
}
