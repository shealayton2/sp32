<?php
/** 
*
* @package acp
* @version $Id: acp_sports_predictions.php 2011-10-18 jslayton $ 
* @copyright (c) 2011 jslayton 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

namespace jslayton\sportspredictions\acp;

class main_info
{
	public function module()
	{
		return array(
			'filename'	=> '\jslayton\sportspredictions\acp\main_module',
			'title'		=> 'ACP_SPORTS_PREDICTIONS',
			'modes'		=> array(
				'overview' => array(
					'title' => 'ACP_SPORTS_PREDICTIONS',
					'auth' => 'ext_jslayton/sportspredictions',
					'cat' => array('ACP_SPORTS_PREDICTIONS')
				),
				'configuration' => array(
					'title' => 'ACP_SP_CONFIGURATION', 
					'auth' => 'ext_jslayton/sportspredictions',
					'cat' => array('ACP_SPORTS_PREDICTIONS')
				),
				'leagues' => array(
					'title' => 'ACP_SP_LEAGUES', 
					'auth' => 'ext_jslayton/sportspredictions',
					'cat' => array('ACP_SPORTS_PREDICTIONS')
				),
				'teams' => array(
					'title' => 'ACP_SP_TEAMS', 
					'auth' => 'ext_jslayton/sportspredictions',
					'cat' => array('ACP_SPORTS_PREDICTIONS')
				),
				'games' => array(
					'title' => 'ACP_SP_GAMES',
					'auth' => 'ext_jslayton/sportspredictions',
					'cat' => array('ACP_SPORTS_PREDICTIONS')
				),
				'scores' => array(
					'title' => 'ACP_SP_SCORES', 
					'auth' => 'ext_jslayton/sportspredictions',
					'cat' => array('ACP_SPORTS_PREDICTIONS')
				),
				'predictions' => array(
					'title' => 'ACP_SP_PREDICTIONS', 
					'auth' => 'ext_jslayton/sportspredictions',
					'cat' => array('ACP_SPORTS_PREDICTIONS')
				),
			),
		);
	}
}

?>