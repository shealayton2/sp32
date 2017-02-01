<?php
// ext/jslayton/sportspredictions/event/main_listener.php

/**
 *
 * @package NV Newspage Extension
 * @copyright (c) 2013 nickvergessen
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

/**
 * @ignore
 */

namespace jslayton\sportspredictions\event;

/**
 * Event listener
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class main_listener implements EventSubscriberInterface
{
	/**
		* Instead of using "global $user;" in the function, we use dependencies again.
	*/
	public function __construct(\phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\user $user)
	{
		$this->helper = $helper;
		$this->template = $template;
		$this->user = $user;
	}
   
	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'    => 'load_language_on_setup',
			'core.page_header'   => 'add_page_header_link',
		);
	}
	
	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'jslayton/sportspredictions',
			'lang_set' => 'sports_predictions',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	public function add_page_header_link($event)
	{
		// I use a second language file here, so I only load the strings global which are required globally.
		// This includes the name of the link, aswell as the ACP module names.
		$this->user->add_lang_ext('jslayton/sportspredictions', 'sports_predictions');

		$this->template->assign_vars(array(
			'U_SPORTS_PREDICTIONS'    => $this->helper->route('jslayton_sportspredictions_base_controller'),
		));
	}
}
