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
* @package acp
*/

namespace jslayton\sportspredictions\acp;

class main_module
{	
	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\config\db_text */
	protected $config_text;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/** @var string */
	public $u_action;
	
	public function main($id, $mode)
	{
		global $cache, $config, $db, $phpbb_log, $request, $template, $user, $phpbb_root_path, $phpEx, $phpbb_container;

		$this->cache = $cache;
		$this->config = $config;
		$this->config_text = $phpbb_container->get('config_text');
		$this->db = $db;
		$this->log = $phpbb_log;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $phpEx;
		
		@define('SP_GAME_TABLE', 'phpbb_sp_games');
		@define('SP_CONFIG_TABLE', 'phpbb_sp_config');
		@define('SP_TEAM_TABLE', 'phpbb_sp_teams');
		@define('SP_PREDICTION_TABLE', 'phpbb_sp_predictions');
		@define('SP_LEAGUE_TABLE', 'phpbb_sp_leagues');
		
		switch ($mode)
		{
			case 'configuration':
				$title = 'ACP_SP_CONFIGURATION';
				$this->page_title = $language->lang($title);
				$this->tpl_name = 'acp_sports_predictions_config';
				$this->configuration($id, $mode);
			break;
			
			case 'leagues':
				$title = 'ACP_SP_LEAGUES';
				$this->page_title = $language->lang($title);
				$this->tpl_name = 'acp_sports_predictions_leagues';
				$this->leagues($id, $mode);
			break;
			
			case 'teams':
				$title = 'ACP_SP_TEAMS';
				$this->page_title = $language->lang($title);
				$this->tpl_name = 'acp_sports_predictions_teams';
				$this->teams($id, $mode);
			break;
			
			case 'games':
				$title = 'ACP_SP_GAMES';
				$this->page_title = $language->lang($title);
				$this->tpl_name = 'acp_sports_predictions_games';
				$this->games($id, $mode);
			break;
			
			case 'scores':
				$title = 'ACP_SP_SCORES';
				$this->page_title = $language->lang($title);
				$this->tpl_name = 'acp_sports_predictions_scores';
				$this->scores($id, $mode);
			break;
			
			case 'predictions':
				$title = 'ACP_SP_PREDICTIONS';
				$this->page_title = $language->lang($title);
				$this->tpl_name = 'acp_sports_predictions_predictions';
				$this->predictions($id, $mode);
			break;
			
			case 'overview':
			default:
				$title = 'ACP_SPORTS_PREDICTIONS';
				$this->page_title = $language->lang($title);
				$this->tpl_name = 'acp_sports_predictions';
				$this->overview($id, $mode);
			break;
		}
	}
	
	function configuration($id, $mode)
	{
		trigger_error('Configuration Works');
	}
	
	function leagues($id, $mode)
	{
		trigger_error('Leagues Works');
	}
	
	function teams($id, $mode)
	{
		trigger_error('Teams Works');
	}
	
	function games($id, $mode)
	{
		trigger_error('Games Works');
	}
	
	function scores($id, $mode)
	{
		trigger_error('Scores Works');
	}
	
	function predictions($id, $mode)
	{
		trigger_error('Predictions Works');
	}

	function overview($id, $mode)
	{
		trigger_error('Overview Works');
	}
}

?>