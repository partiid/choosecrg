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

<div id="modal-center" class="uk-flex-top" uk-modal>
    <div class="uk-modal-dialog uk-modal-body uk-margin-auto-vertical uk-text-center">

        <button class="uk-modal-close-default" type="button" uk-close></button>
        <h2>
        Dziękujemy za rejestrację! Przebiegła ona pomyślnie. <br/> Oczekuj na email potwierdzający akceptacje przez administratora. 

        </h2>
        <p>Z reguły odpowiadamy na zgłoszenia w ciągu 24h</p>
        

    </div>
</div>
{literal}
<script>
UIkit.modal(document.getElementById('modal-center')).show();
</script>
{/literal}/**
