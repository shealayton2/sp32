<?php
/** 
*
* @package acp
* @version $Id: acp_sports_predictions.php 2011-10-18 jslayton $ 
* @copyright (c) 2011 jslayton 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/
/**
* @ignore
*/

/**
* @package module_install
*/

namespace jslayton\sportspredictions\acp;

class main_info
{
	function module()
	{
		global $user;

		$user->add_lang('mods/sports_predictions');

		return array(
			'filename'	=> '\jslayton\sportspredictions\acp\main_module',
			'title'		=> 'ACP_SPORTS_PREDICTIONS',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'overview'			=> array('title' => 'ACP_SPORTS_PREDICTIONS', 'auth' => 'acl_a_sportspredictions', 'cat' => array('ACP_DOT_MODS')),
				'configuration'		=> array('title' => 'ACP_SPORTS_PREDICTIONS_CONFIGURATION', 'auth' => 'acl_a_sportspredictions', 'cat' => array('ACP_SPORTS_PREDICTIONS')),
				'leagues'			=> array('title' => 'ACP_SPORTS_PREDICTIONS_LEAGUES', 'auth' => 'acl_a_sportspredictions', 'cat' => array('ACP_SPORTS_PREDICTIONS')),
				'teams'				=> array('title' => 'ACP_SPORTS_PREDICTIONS_TEAMS', 'auth' => 'acl_a_sportspredictions', 'cat' => array('ACP_SPORTS_PREDICTIONS')),
				'games'				=> array('title' => 'ACP_SPORTS_PREDICTIONS_GAMES', 'auth' => 'acl_a_sportspredictions', 'cat' => array('ACP_SPORTS_PREDICTIONS')),
				'scores'			=> array('title' => 'ACP_SPORTS_PREDICTIONS_SCORES', 'auth' => 'acl_a_sportspredictions', 'cat' => array('ACP_SPORTS_PREDICTIONS')),
				'predictions'		=> array('title' => 'ACP_SPORTS_PREDICTIONS_PREDICTIONS', 'auth' => 'acl_a_sportspredictions', 'cat' => array('ACP_SPORTS_PREDICTIONS')),
			),
		);
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}


?>