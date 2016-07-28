<?php
/*
* 2007-2014 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

class Googleanalytics extends Module
{
	/**
	* initiate Google Analytics module
	*/
	public function __construct()
	{
		$this->name = 'facebookpixel';
		$this->tab = 'analytics_stats';
		$this->version = '0.1 (alpha)';
		$this->author = 'Jufik';
		$this->need_instance = 1;
		$this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6');
		$this->bootstrap = true;

		parent::__construct();

		$this->displayName = $this->l('Facebook Pixel');
		$this->description = $this->l('This is the Facebook extension for Prestashop');

		$this->confirmUninstall = $this->l('Are you sure you want to uninstall Facebook Pixel?');
 		!Configuration::updateValue('facebookpixel', 'OK');
	}

	/**
	* install module *
	* @return bool
	*/
	public function install()
	{
		if (Shop::isFeatureActive())
		Shop::setContext(Shop::CONTEXT_ALL);

		// Checking relevant hooks are available
		if (!parent::install() ||
			!$this->registerHook('header') ||
			!Configuration::updateValue('facebook_pixel_active', false) ||
			!Configuration::updateValue('facebook_pixel_id', '')
		)
			
		return false;

		// Create relevant variable


	}

	/**
	* uninstall module
	* @return bool
	*/
	public function uninstall()
	{
		if (!parent::uninstall() ||
			!Configuration::deleteByName('facebook_pixel_active') ||
			!Configuration::deleteByName('facebook_pixel_id') ||
			!Configuration::deleteByName('facebookpixel')
		)
		return false;

		//drop transaction table

		return true;
	}

	/**
	* back office return configuration form
	* @return mixed
	*/
	public function displayForm()
	{
		// Get default Language
		$default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

		$helper = new HelperForm();

		// Module, t    oken and currentIndex
		$helper->module = $this;
		$helper->name_controller = $this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

		// Language
		$helper->default_form_language = $default_lang;
		$helper->allow_employee_form_lang = $default_lang;

		// Title and toolbar
		$helper->title = $this->displayName;
		$helper->show_toolbar = true;        // false -> remove toolbar
		$helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
		$helper->submit_action = 'submit'.$this->name;
		$helper->toolbar_btn = array(
			'save' =>
				array(
					'desc' => $this->l('Save'),
					'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
					'&token='.Tools::getAdminTokenLite('AdminModules'),
				),
			'back' => array(
				'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
				'desc' => $this->l('Back to list')
			)
		);

		// Init Fields form array
		$fields_form[0]['form'] = array(
			'legend' => array(
				'title' => $this->l('Facebook Pixel General Settings'),
			),
			'input' => array(
				//add enable&disable switch
				array(
					'type' => 'switch',
					'label' => $this->l('Enable Facebook Pixel Module'),
					'name' => 'facebook_pixel_active',
					'is_bool' => true,
					'required' => true,
					'values' => array(
						array(
						'id' => 'facebook_pixel_active_yes',
						'value' => 1,
						'label' => $this->l('Yes'),
						),
						array(
						'id' => 'facebook_pixel_active_no',
						'value' => 0,
						'label' => $this->l('No')
						)
					),
					'hint' => $this->l('Enable or disable Facebook Pixel')
				),
				//add google analytics
				array(
					'type' => 'text',
					'label' => $this->l('Web Tracking Id'),
					'name' => 'facebook_pixel_id',
					'size' => 20,
					'required' => true,
					'hint'=>'Get tracking Id from Facebook Pixel'
				),

			),
			'submit' => array(
				'title' => $this->l('Save'),
				'class' => 'btn btn-default pull-right'
			)
		);
		// Load current value
		$helper->fields_value['facebook_pixel_active'] = Configuration::get('facebook_pixel_active');
		$helper->fields_value['facebook_pixel_id'] = Configuration::get('facebook_pixel_id');

		return $helper->generateForm($fields_form);

	}

	/**
	* back office module configuration page content
	*/
	public function getContent()
	{
		$output = '';
		if (Tools::isSubmit('submit'.$this->name))
		{
			$error = false;
			$facebook_pixel_active = Tools::getValue('facebook_pixel_active');
			$facebook_pixel_id = Tools::getValue('facebook_pixel_id');

			if (!$error)
			{
				Configuration::updateValue('facebook_pixel_active', $facebook_pixel_active);
				Configuration::updateValue('facebook_pixel_id', $facebook_pixel_id);
				$output .= $this->displayConfirmation($this->l('Settings updated'));
			}
		}
		return $output.$this->displayForm();
	}

	/**
	* hook page header to add CSS and JS files
	*/
	public function hookDisplayHeader()
	{
		if (Configuration::get('facebook_pixel_active') != '' && Configuration::get('facebook_pixel_id') != '')
		{
			$this->context->smarty->assign(
				array(
					'facebook_pixel_id' => Configuration::get('facebook_pixel_id'),
				)
			);
			return $this->display(__FILE__, 'hookDisplayHeader.tpl');
		}

	}
}
