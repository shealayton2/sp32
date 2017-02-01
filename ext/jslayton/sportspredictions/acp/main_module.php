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
	public $u_action;
	public $tpl_name;
	public $page_title;
	//public $sportspredictions;
	//public $logo_path;
	
	public function main($id, $mode)
	{
		//global $db, $user, $auth, $template;
        //global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;
		global $cache, $phpbb_container, $request, $user;
		
		$user->add_lang_ext('jslayton\sportspredictions', 'sports_predictions');
		
		//include($phpbb_root_path . "includes/sportspredictions/class_sportspredictions.$phpEx");
		//$sportspredictions = new sportspredictions('admin');
		
		// Get an instance of the admin controller
		$sportspredictions = $phpbb_container->get('jslayton.sportspredictions');

		$this->logo_path = $phpbb_root_path . $sportspredictions->config['logo_path'];
		
		// Set up general vars
		$action	= $request->variable('action', '');
		
		$form_key = 'acp_sp_add_teams';
		add_form_key($form_key);
		
		switch ($mode)
		{
			case 'configuration':
				$title = 'ACP_SPORTS_PREDICTIONS_CONFIGURATION';
				$this->page_title = $user->lang[$title];
				$this->tpl_name = 'acp_sports_predictions_config';
				$this->configuration($id, $mode);
			break;
			
			case 'leagues':
				$title = 'ACP_SPORTS_PREDICTIONS_LEAGUES';
				$this->page_title = $user->lang[$title];
				$this->tpl_name = 'acp_sports_predictions_leagues';
				$this->leagues($id, $mode);
			break;
			
			case 'teams':
				$title = 'ACP_SPORTS_PREDICTIONS_TEAMS';
				$this->page_title = $user->lang[$title];
				$this->tpl_name = 'acp_sports_predictions_teams';
				$this->teams($id, $mode);
			break;
			
			case 'games':
				$title = 'ACP_SPORTS_PREDICTIONS_GAMES';
				$this->page_title = $user->lang[$title];
				$this->tpl_name = 'acp_sports_predictions_games';
				$this->games($id, $mode);
			break;
			
			case 'scores':
				$title = 'ACP_SPORTS_PREDICTIONS_SCORES';
				$this->page_title = $user->lang[$title];
				$this->tpl_name = 'acp_sports_predictions_scores';
				$this->scores($id, $mode);
			break;
			
			case 'predictions':
				$title = 'ACP_SPORTS_PREDICTIONS_PREDICTIONS';
				$this->page_title = $user->lang[$title];
				$this->tpl_name = 'acp_sports_predictions_predictions';
				$this->predictions($id, $mode);
			break;
			
			case 'overview':
			default:
				$title = 'ACP_SPORTS_PREDICTIONS';
				$this->page_title = $user->lang[$title];
				$this->tpl_name = 'acp_sports_predictions';
				$this->overview($id, $mode);
			break;
		}
	}
	
	function overview($id, $mode)
	{
		global $phpbb_root_path, $language, $template, $request, $config, $sportspredictions, $user;
		
		$sort		= $request->variable('sort', '');
		$start		= $request->variable('start', 0);
		$league_id	= $request->variable('league_id', $sportspredictions->config['default_league']);

		$template->assign_vars(array(
			'LEAGUE_NAME'				=> $sportspredictions->leagues_array[$league_id]['league_name'],
			
			'U_PENDING_SCORES_ACTION'	=> (str_replace('mode=overview', 'mode=scores', $this->u_action) . '&amp;action=add'),
			'U_SORT_USERNAME'			=> $this->u_action . '&amp;sort=username',
			'U_SORT_WINS'				=> $this->u_action . '&amp;sort=wins',
			'U_SORT_LOSSES'				=> $this->u_action . '&amp;sort=losses',
			'U_SORT_POINTDIFF'			=> $this->u_action . '&amp;sort=pointdiff',
			'U_SORT_WINPERC'			=> $this->u_action . '&amp;sort=winperc',
			'U_SORT_POINTS'				=> $this->u_action,
			'U_PREDICT' 				=> $this->u_action . '&amp;mode=predict')
		);

		$sportspredictions->set_league_id($league_id);
		$stats_array = $sportspredictions->build_leaderboard($sort, $sportspredictions->config['leaderboard_limit'], $start);

		foreach($stats_array AS $lb_data)
		{
			$template->assign_block_vars('leaderboard', array(
				'POSITION'	=> $lb_data['rank'],
				'USER_ID'	=> $lb_data['user_id'],
				'USERNAME'	=> $lb_data['username'],
				'HIGHLIGHT'	=> (($lb_data['user_id'] == $user->data['user_id']) ? true : false),
				'WINS'		=> $lb_data['wins'],
				'LOSSES'	=> $lb_data['losses'],
				'POINTDIFF'	=> $lb_data['pointdiff'],
				'WINPERC'	=> $lb_data['winperc'],
				'POINTS'	=> $lb_data['points']
			));
		}
		
		/*
		if (sizeof($sportspredictions->stats_array[$league_id]) > $sportspredictions->config['leaderboard_limit']) {
			$template->assign_vars(array(
				'PAGINATION'	=> generate_pagination($this->u_action . "&amp;sort=$sort", sizeof($sportspredictions->stats_array[$league_id]), $sportspredictions->config['leaderboard_limit'], $start),
				'S_ON_PAGE'		=> on_page(sizeof($sportspredictions->stats_array[$league_id]), $sportspredictions->config['leaderboard_limit'], $start)
			));
		}
		*/
		
		$pagination = $phpbb_container->get('pagination');
		$pagination->generate_template_pagination($this->u_action . "&amp;sort=$sort", 'paginated', 'page', sizeof($sportspredictions->stats_array[$league_id]), $sportspredictions->config['leaderboard_limit'], $start);
		$template->assign_vars(array(
			'S_ON_PAGE' => $pagination->on_page(sizeof($sportspredictions->stats_array[$league_id]), $sportspredictions->config['leaderboard_limit'], $start),
		));
		
		$s_league_options = $sportspredictions->build_league_options($league_id);
		$s_hidden_fields .= '<input type="hidden" name="league_id" value="' . $league_id . '" />';
		
		$template->assign_vars(array(
			'S_SHOW_LEAGUE_BOX'	=> ((sizeof($sportspredictions->leagues_array) >= 2) ? true : false),
			'S_LEAGUE_OPTIONS'	=> $s_league_options,
			'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
			
			'U_ACTION'		=> $this->u_action)
		);

		$sql = 'SELECT * FROM ' . SP_GAME_TABLE . ' WHERE game_time < ' . time() . ' AND away_score IS NULL AND home_score IS NULL ORDER BY game_time ASC';
		$result = $db->sql_query($sql);
		
		while ($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars('pending_games', array(
				'GAMETIME'		=> $user->format_date($row['game_time']),
				'AWAY_TEAM'		=> $sportspredictions->teams_array[$row['league_id']][$row['away_id']]['team_name'],
				'HOME_TEAM'		=> $sportspredictions->teams_array[$row['league_id']][$row['home_id']]['team_name'])
			);
		}
		$db->sql_freeresult($result);
	}
	
	function configuration($id, $mode)
	{
		global $phpbb_root_path, $language, $template, $request, $config, $sportspredictions, $user;
		
		// Set some vars
		$action	= $request->variable('action', '');
		$action = (isset($_POST['add'])) ? 'add' : ((isset($_POST['save'])) ? 'save' : $action);
		
		$s_hidden_fields = '';
		
		$form_name = 'acp_sp_config';
		add_form_key($form_name);
		
		switch ($action)
		{
			case 'rebuild_thumbs':
			
				if (confirm_box(true))
				{
					foreach ((array) $sportspredictions->leagues_array AS $league_id => $league_info)
					{
						if ($league_info['league_logo'] != '')
						{
							if ($league_info['league_logo_tn'] != '')
							{
								if (file_exists($phpbb_root_path . $sportspredictions->config['logo_path'] . '/' . $league_info['league_logo_tn']))
								{
									@unlink($phpbb_root_path . $sportspredictions->config['logo_path'] . '/' . $league_info['league_logo_tn']);
								}
							}
								
							$thumbnail = $sportspredictions->create_thumbnail($phpbb_root_path . $sportspredictions->config['logo_path'] . '/' . $league_info['league_logo']);

							if ($thumbnail)
							{
								$sql_ary['league_logo_tn'] = $thumbnail;
							}
							else
							{
								$sql_ary['league_logo_tn'] = NULL;
							}

							$sql = 'UPDATE ' . SP_LEAGUE_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . ' WHERE league_id = ' . $league_id;
							$db->sql_query($sql);
							unset($sql_ary);
						}
					}
					$cache->destroy('sql', SP_LEAGUE_TABLE);
					
					foreach ((array) $sportspredictions->teams_array['id_ref'] AS $team_id => $team_info)
					{
						if ($team_info['team_logo'] != '')
						{
							if ($team_info['team_logo_tn'] != '')
							{
								if (file_exists($phpbb_root_path . $sportspredictions->config['logo_path'] . '/' . $team_info['team_logo_tn']))
								{
									@unlink($phpbb_root_path . $sportspredictions->config['logo_path'] . '/' . $team_info['team_logo_tn']);
								}
							}
								
							$thumbnail = $sportspredictions->create_thumbnail($phpbb_root_path . $sportspredictions->config['logo_path'] . '/' . $team_info['team_logo']);

							if ($thumbnail)
							{
								$sql_ary['team_logo_tn'] = $thumbnail;
							}
							else
							{
								$sql_ary['team_logo_tn'] = NULL;
							}

							$sql = 'UPDATE ' . SP_TEAM_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . ' WHERE team_id = ' . $team_id;
							$db->sql_query($sql);
							unset($sql_ary);
						}
					}
					$cache->destroy('sql', SP_TEAM_TABLE);
					
					trigger_error($user->lang['ACP_THUMBNAILS_REBUILT'] . adm_back_link($this->u_action));
				}
				else
				{
					confirm_box(false, $user->lang['CONFIRM_REBUILD_THUMBNAILS'], build_hidden_fields(array(
						'i'			=> $id,
						'mode'		=> $mode,
						'action'	=> 'rebuild_thumbs',
					)));
				}
				
			break;
			
			case 'save':
			
				if (!check_form_key($form_name))
				{
					trigger_error($user->lang['FORM_INVALID']. adm_back_link($this->u_action), E_USER_WARNING);
				}
				
				$config_ary['default_league']				= $request->variable('default_league', 0);
				$config_ary['leaderboard_limit']			= $request->variable('leaderboard_limit', 0);
				$config_ary['upcoming_games_limit']			= $request->variable('upcoming_games_limit', 0);
				$config_ary['exact_prediction_points']		= $request->variable('exact_prediction_points', 0);
				$config_ary['correct_prediction_points']	= $request->variable('correct_prediction_points', 0);
				$config_ary['incorrect_prediction_points']	= $request->variable('incorrect_prediction_points', 0);
				$config_ary['logo_path']					= $request->variable('logo_path', '', true);
				$config_ary['logo_max_thumbnail_width']		= $request->variable('logo_max_thumbnail_width', 0);
				$config_ary['logo_max_thumbnail_height']	= $request->variable('logo_max_thumbnail_height', 0);
				
				if ($config_ary['default_league'] == 0)
				{
					trigger_error($user->lang['ACP_DEFAULT_LEAGUE_ERROR'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				
				if ($config_ary['leaderboard_limit'] == 0)
				{
					trigger_error($user->lang['ACP_LEADERBOARD_LIMIT_GTZERO'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				
				if ($config_ary['upcoming_games_limit'] == 0)
				{
					trigger_error($user->lang['ACP_UPCOMING_GAMES_LIMIT_GTZERO'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				
				// format logo_path
				$_logo_path = $config_ary['logo_path'];
				if (substr($_logo_path, -1, 1) == '/' || substr($_logo_path, -1, 1) == '\\')
				{
					$_logo_path = substr($_logo_path, 0, -1);
				}
				$_logo_path = str_replace(array('../', '..\\', './', '.\\'), '', $_logo_path);
				if ($_logo_path && ($_logo_path[0] == '/' || $_logo_path[0] == "\\"))
				{
					$_logo_path = '';
				}
				$config_ary['logo_path'] = trim($_logo_path);
				
				foreach($config_ary AS $config_name => $config_value)
				{
					$sql = "UPDATE phpbb_sp_config SET config_value = '" . $db->sql_escape($config_value) . "'  WHERE config_name = '$config_name'";
					$db->sql_query($sql);
				}
				$cache->destroy('_sp_config');
				trigger_error($user->lang['ACP_CONFIG_UPDATED'] . adm_back_link($this->u_action));
				
			break;
		}
		
		$s_league_options = $sportspredictions->build_league_options($sportspredictions->config['default_league']);
		
		$template->assign_vars(array(
			'S_LEAGUE_OPTIONS'				=> $s_league_options,

			'LEADERBOARD_LIMIT'				=> $sportspredictions->config['leaderboard_limit'],
			'UPCOMING_GAMES_LIMIT'			=> $sportspredictions->config['upcoming_games_limit'],
			'EXACT_PREDICTION_POINTS'		=> $sportspredictions->config['exact_prediction_points'],
			'CORRECT_PREDICTION_POINTS'		=> $sportspredictions->config['correct_prediction_points'],
			'INCORRECT_PREDICTION_POINTS'	=> $sportspredictions->config['incorrect_prediction_points'],
			'LOGO_PATH'						=> $sportspredictions->config['logo_path'],
			'LOGO_MAX_THUMBNAIL_WIDTH'		=> $sportspredictions->config['logo_max_thumbnail_width'],
			'LOGO_MAX_THUMBNAIL_HEIGHT'		=> $sportspredictions->config['logo_max_thumbnail_height'],
			
			'U_REBUILD_THUMBNAILS'	=> $this->u_action . '&amp;action=rebuild_thumbs',
			'U_ACTION'				=> $this->u_action)
		);
		$db->sql_freeresult($result);
	}
	
	function leagues($id, $mode)
	{
		global $phpbb_root_path, $language, $template, $request, $config, $sportspredictions, $user;
		
		// Set some vars
		$action	= $request->variable('action', '');
		$action = (isset($_POST['add'])) ? 'add' : ((isset($_POST['save'])) ? 'save' : $action);
		
		$s_hidden_fields = '';
		
		$form_name = 'acp_sp_leagues';
		add_form_key($form_name);
		
		switch ($action)
		{
			case 'edit':
			
				$league_id = $request->variable('league_id', 0);
				
				if (!$league_id)
				{
					trigger_error($user->lang['ACP_NO_LEAGUE'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				
				$s_hidden_fields .= '<input type="hidden" name="league_id" value="' . $league_id . '" />';
				
			case 'add':
				
				$template->assign_vars(array(
					'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
					'S_EDIT_LEAGUE'		=> true,
					
					'LEAGUE_NAME'		=> (isset($league_id)) ? $sportspredictions->leagues_array[$league_id]['league_name'] : '',
					'LEAGUE_LOGO'		=> ((isset($league_id)) ? ((!empty($sportspredictions->leagues_array[$league_id]['league_logo'])) ? $this->logo_path . '/' . $sportspredictions->leagues_array[$league_id]['league_logo'] : '') : ''),
					'SCORING_STYLE'		=> (isset($league_id)) ? $sportspredictions->leagues_array[$league_id]['scoring_style'] : '',
					'POINTDIFF_AVERAGE'	=> (isset($league_id)) ? $sportspredictions->leagues_array[$league_id]['pointdiff_average'] : '',
					'ACTIVE'			=> (isset($league_id)) ? $sportspredictions->leagues_array[$league_id]['active'] : '',
					
					'U_BACK'			=> $this->u_action,
					'U_ACTION'			=> $this->u_action)
				);
				
				return;
				
			break;
				
			case 'save':
			
				if (!check_form_key($form_name))
				{
					trigger_error($user->lang['FORM_INVALID']. adm_back_link($this->u_action), E_USER_WARNING);
				}
				
				$league_id			= $request->variable('league_id', 0);
				$league_name		= utf8_normalize_nfc($request->variable('league_name', '', true));
				$remove_logo		= $request->variable('remove_logo', false);
				$scoring_style		= $request->variable('scoring_style', 'score');
				$pointdiff_average	= $request->variable('pointdiff_average', 1);
				$active				= $request->variable('active', 1);
				
				if ($league_name === '')
				{
					trigger_error($user->lang['ACP_ENTER_LEAGUE_NAME'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				
				$sql_ary['league_name']			= $league_name;
				$sql_ary['scoring_style']		= $scoring_style;
				$sql_ary['pointdiff_average']	= $pointdiff_average;
				$sql_ary['active']				= $active;
				
				if ($league_id)
				{
					if ($remove_logo)
					{
						$sportspredictions->remove_logo('league', $league_id);
						
						$sql_ary['league_logo']		= NULL;
						$sql_ary['league_logo_tn']	= NULL;
					}
					elseif ($_FILES['league_logo']['name'] != '')
					{
						$logo = $sportspredictions->upload_logo('league_logo', 'L_' . $league_id . '_');
						if ($logo)
						{
							$sql_ary['league_logo'] = $logo['logo'];
							if ($logo['logo_thumb'])
							{
								$sql_ary['league_logo_tn'] = $logo['logo_thumb'];
							}
							else
							{
								$sql_ary['league_logo_tn'] = NULL;
							}
							
							$sportspredictions->remove_logo('league', $league_id);
						}
					}
					
					$sql = 'UPDATE ' . SP_LEAGUE_TABLE . '
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE league_id = ' . $league_id;
					$db->sql_query($sql);
					
					if ($league_id == $sportspredictions->config['default_league'] && $active == 0)
					{
						unset($sql_ary);
						$sql = 'SELECT league_id FROM ' . SP_LEAGUE_TABLE . ' WHERE active = 1 ORDER BY league_id DESC LIMIT 1';
						$result = $db->sql_query($sql);
						$sql_ary['config_value'] = (int) $db->sql_fetchfield('league_id');
						$db->sql_freeresult($result);

						$sql = 'UPDATE ' . SP_CONFIG_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . " WHERE config_name = 'default_league'";
						$db->sql_query($sql);
					}
					
					$lang = 'ACP_LEAGUE_UPDATED';
				}
				else
				{
					$sql = 'INSERT INTO ' . SP_LEAGUE_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
					$db->sql_query($sql);
					$league_id = $db->sql_nextid();

					if ($_FILES['league_logo']['name'] != '')
					{
						$logo = $sportspredictions->upload_logo('league_logo', 'L_' . $league_id . '_');
						if ($logo)
						{
							$sql_ary = array();
							$sql_ary['league_logo'] = $logo['logo'];
							if ($logo['logo_thumb'])
							{
								$sql_ary['league_logo_tn'] = $logo['logo_thumb'];
							}
							else
							{
								$sql_ary['league_logo_tn'] = NULL;
							}
							
							$sportspredictions->remove_logo('league', $league_id);
						}
					}
					
					$sql = 'UPDATE ' . SP_LEAGUE_TABLE . '
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE league_id = ' . $league_id;
					$db->sql_query($sql);
					$lang = 'ACP_LEAGUE_ADDED';
				}
				$cache->destroy('sql', SP_LEAGUE_TABLE);
				$cache->destroy('_sp_stats_array_' . $league_id);
				trigger_error($user->lang[$lang] . adm_back_link($this->u_action));
				
			break;
			
			case 'delete':
			
				$league_id = $request->variable('league_id', 0);
				
				if (!$league_id)
				{
					trigger_error($user->lang['ACP_NO_LEAGUE'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				
				if (confirm_box(true))
				{
					$sql = 'DELETE FROM ' . SP_LEAGUE_TABLE . " WHERE league_id = $league_id";
					$db->sql_query($sql);
					
					$sportspredictions->remove_logo('league', $league_id);

					$cache->destroy('sql', SP_LEAGUE_TABLE);
					$cache->destroy('_sp_stats_array_' . $league_id);

					trigger_error($user->lang['ACP_LEAGUE_REMOVED'] . adm_back_link($this->u_action));
				}
				else
				{
					confirm_box(false, $user->lang['CONFIRM_DELETE_LEAGUE'], build_hidden_fields(array(
						'i'			=> $id,
						'mode'		=> $mode,
						'league_id'	=> $league_id,
						'action'	=> 'delete',
					)));
				}
			
			break;
		}
		
		$template->assign_vars(array(
			'U_ACTION'		=> $this->u_action)
		);

		foreach ($sportspredictions->leagues_array AS $league_id => $league_data)
		{
			$template->assign_block_vars('leagues', array(
				'LEAGUE_ID'				=> $league_id,
				'LEAGUE_NAME'			=> $league_data['league_name'],
				'LEAGUE_LOGO_THUMBNAIL'	=> ((!empty($league_data['league_logo_tn'])) ? $this->logo_path . '/' . $league_data['league_logo_tn'] : ''),
				
				'U_EDIT'				=> $this->u_action . '&amp;action=edit&amp;league_id=' . $league_id,
				'U_DELETE'				=> $this->u_action . '&amp;action=delete&amp;league_id=' . $league_id)
			);
		}
		
		return;
	}
	
	function teams($id, $mode)
	{
		global $phpbb_root_path, $language, $template, $request, $config, $sportspredictions, $user;
		
		// Set some vars
		$action	= $request->variable('action', '');
		$action = (isset($_POST['add'])) ? 'add' : ((isset($_POST['multiadd'])) ? 'multiadd' : ((isset($_POST['save'])) ? 'save' : ((isset($_POST['multisave'])) ? 'multisave' : $action)));
		
		$s_hidden_fields = '';
		
		$form_name = 'acp_sp_teams';
		add_form_key($form_name);
		
		switch ($action)
		{
			case 'edit':
			
				$team_id	= $request->variable('team_id', 0);

				if (!$team_id)
				{
					trigger_error($user->lang['ACP_NO_TEAM'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$s_hidden_fields .= '<input type="hidden" name="team_id" value="' . $team_id . '" />';
				
			case 'add':
			
				$league_id	= $request->variable('league_id', 0);
				
				if (!$league_id)
				{
					trigger_error($user->lang['ACP_NO_LEAGUE'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				
				$s_league_options = $sportspredictions->build_league_options($league_id);
				
				$template->assign_vars(array(
					'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
					'S_LEAGUE_OPTIONS'	=> $s_league_options,
					'S_EDIT_TEAM'		=> true,
					
					'TEAM_NAME'			=> (isset($team_id)) ? $sportspredictions->teams_array[$league_id][$team_id]['team_name'] : '',
					'TEAM_LOGO'			=> ((isset($team_id)) ? ((!empty($sportspredictions->teams_array[$league_id][$team_id]['team_logo'])) ? $this->logo_path . '/' . $sportspredictions->teams_array[$league_id][$team_id]['team_logo'] : '') : ''),
					'SHOW_RESULTS'		=> (isset($team_id)) ? $sportspredictions->teams_array[$league_id][$team_id]['show_results'] : '',
					
					'U_BACK'			=> $this->u_action,
					'U_ACTION'			=> $this->u_action)
				);
				
				return;
				
			break;
			
			case 'multiadd':
			
				$league_id	= $request->variable('league_id', 0);
				
				if (!$league_id)
				{
					trigger_error($user->lang['ACP_NO_LEAGUE'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				
				$s_league_options = $sportspredictions->build_league_options($league_id);
				
				$template->assign_vars(array(
					'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
					'S_LEAGUE_OPTIONS'	=> $s_league_options,
					'S_MULTIADD_TEAM'	=> true,
					
					'U_BACK'			=> $this->u_action,
					'U_ACTION'			=> $this->u_action)
				);
			
			break;
				
			case 'save':
			
				if (!check_form_key($form_name))
				{
					trigger_error($user->lang['FORM_INVALID']. adm_back_link($this->u_action), E_USER_WARNING);
				}
				
				$league_id		= $request->variable('league_id', 0);
				$team_id		= $request->variable('team_id', 0);
				$team_name		= utf8_normalize_nfc($request->variable('team_name', '', true));
				$remove_logo 	= $request->variable('remove_logo', false);
				$show_results	= $request->variable('show_results', 1);
				
				if (!$league_id)
				{
					trigger_error($user->lang['ACP_NO_LEAGUE'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				
				if ($team_name === '')
				{
					trigger_error($user->lang['ACP_ENTER_TEAM_NAME'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				
				$sql_ary['league_id'] 		= $league_id;
				$sql_ary['team_name'] 		= $team_name;
				$sql_ary['show_results']	= $show_results;
				
				if ($team_id)
				{
					if ($remove_logo)
					{
						$sportspredictions->remove_logo('team', $team_id);
						
						$sql_ary['team_logo']		= NULL;
						$sql_ary['team_logo_tn']	= NULL;
					}
					elseif ($_FILES['team_logo']['name'] != '')
					{
						$logo = $sportspredictions->upload_logo('team_logo', 'T_' . $team_id . '_');
						if ($logo)
						{
							$sql_ary['team_logo'] = $logo['logo'];
							if ($logo['logo_thumb'])
							{
								$sql_ary['team_logo_tn'] = $logo['logo_thumb'];
							}
							else
							{
								$sql_ary['team_logo_tn'] = NULL;
							}
							
							$sportspredictions->remove_logo('team', $team_id);
						}
					}
					
					$sql = 'UPDATE ' . SP_TEAM_TABLE . '
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE team_id = ' . $team_id;
					$db->sql_query($sql);
					$lang = 'ACP_TEAM_EDITED';
				}
				else
				{					
					$sql = 'INSERT INTO ' . SP_TEAM_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
					$db->sql_query($sql);
					$team_id = $db->sql_nextid();
					
					if ($_FILES['team_logo']['name'] != '')
					{
						$logo = $sportspredictions->upload_logo('team_logo', 'T_' . $team_id . '_');
						if ($logo)
						{
							$sql_ary = array();
							$sql_ary['team_logo'] = $logo['logo'];
							if ($logo['logo_thumb'])
							{
								$sql_ary['team_logo_tn'] = $logo['logo_thumb'];
							}
							else
							{
								$sql_ary['team_logo_tn'] = NULL;
							}
							
							$sportspredictions->remove_logo('team', $team_id);
						}
					}
					
					$sql = 'UPDATE ' . SP_TEAM_TABLE . '
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE league_id = ' . $league_id . ' AND team_id = ' . $team_id;
					$db->sql_query($sql);
					$lang = 'ACP_TEAM_ADDED';
				}
				$cache->destroy('sql', SP_TEAM_TABLE);
				trigger_error($user->lang[$lang] . adm_back_link($this->u_action . '&amp;league_id=' . $league_id));
				
			break;
			
			case 'multisave':
			
				if (!check_form_key($form_name))
				{
					trigger_error($user->lang['FORM_INVALID']. adm_back_link($this->u_action), E_USER_WARNING);
				}
				
				$league_id	= $request->variable('league_id', 0);
				$team_names	= $request->variable('team_names', '');
				
				if (!$league_id)
				{
					trigger_error($user->lang['ACP_NO_LEAGUE'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$team_names_ary = explode("\n", $team_names);
				
				foreach ($team_names_ary AS $team_name)
				{
					$team_name = trim($team_name);
					
					if (!empty($team_name))
					{
						$sql_multi_ary[] = array(
							'league_id'	=> $league_id,
							'team_name'	=> $team_name
						);
					}
				}

				if (sizeof($sql_multi_ary))
				{
					$db->sql_multi_insert(SP_TEAM_TABLE, $sql_multi_ary);
				}

				$cache->destroy('sql', SP_TEAM_TABLE);
				trigger_error($user->lang['ACP_TEAM_ADDED'] . adm_back_link($this->u_action . '&amp;league_id=' . $league_id));
			
			break;
			
			case 'delete':
			
				$league_id	= $request->variable('league_id', 0);
				$team_id	= $request->variable('team_id', 0);
				
				if (!$league_id)
				{
					trigger_error($user->lang['ACP_NO_LEAGUE'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				
				if (!$team_id)
				{
					trigger_error($user->lang['ACP_NO_TEAM'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				
				if (confirm_box(true))
				{
					$db->sql_transaction('begin');
					
					$sql = 'DELETE FROM ' . SP_TEAM_TABLE . " WHERE league_id = $league_id AND team_id = $team_id";
					$db->sql_query($sql);
					
					$sportspredictions->remove_logo('team', $team_id);
					
					$_tables_to_uncache[] = SP_TEAM_TABLE;
					
					$sql = 'SELECT game_id FROM ' . SP_GAME_TABLE . " WHERE league_id = $league_id AND (away_id = $team_id OR home_id = $team_id)";
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						$games_to_delete[] = $row['game_id'];
					}
					$db->sql_freeresult($result);

					if (sizeof($games_to_delete))
					{
						$sql = 'DELETE FROM ' . SP_PREDICTION_TABLE . ' WHERE ' . $db->sql_in_set('game_id', $games_to_delete);
						$db->sql_query($sql);
						$predictions_deleted = $db->sql_affectedrows();
						
						if (sizeof($predictions_deleted))
						{
							$_tables_to_uncache[] = SP_PREDICTION_TABLE;
						}
						
						$sql = 'DELETE FROM ' . SP_GAME_TABLE . " WHERE league_id = $league_id AND (away_id = $team_id OR home_id = $team_id)";
						$db->sql_query($sql);
						$games_deleted = $db->sql_affectedrows();
						
						if (sizeof($games_deleted))
						{
							$_tables_to_uncache[] = SP_GAME_TABLE;
						}
					}
					else
					{
						$predictions_deleted = $games_deleted = 0;
					}
					
					$db->sql_transaction('commit');

					$cache->destroy('sql', $_tables_to_uncache);
					if (in_array(SP_PREDICTION_TABLE, $_tables_to_uncache))
					{
						$cache->destroy('_sp_stats_array_' . $league_id);
					}
					trigger_error(sprintf($user->lang['ACP_TEAM_REMOVED'], $games_deleted, $predictions_deleted) . adm_back_link($this->u_action . '&amp;league_id=' . $league_id));
				}
				else
				{
					confirm_box(false, $user->lang['CONFIRM_DELETE_TEAM'], build_hidden_fields(array(
						'i'			=> $id,
						'mode'		=> $mode,
						'league_id'	=> $league_id,
						'team_id'	=> $team_id,
						'action'	=> 'delete',
					)));
				}
			
			break;
		}
		
		$league_id	= $request->variable('league_id', $sportspredictions->config['default_league']);
		
		$s_league_options = $sportspredictions->build_league_options($league_id);
		$s_hidden_fields .= '<input type="hidden" name="league_id" value="' . $league_id . '" />';
		
		$template->assign_vars(array(
			'S_LEAGUE_OPTIONS'	=> $s_league_options,
			'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
			'S_SHOW_LEAGUE_BOX'	=> ((sizeof($sportspredictions->leagues_array) >= 2) ? true : false),
			
			'LEAGUE_NAME'		=> $sportspredictions->leagues_array[$league_id]['league_name'],
			'TEAM_COUNT'		=> $team_count,
			'USER_TIMEZONE'		=> $user->lang['tz_zones'][rtrim(trim($user->data['user_timezone'], '0'), '.')],
			
			'U_ACTION'			=> $this->u_action)
		);
			
		foreach ((array) $sportspredictions->teams_array[$league_id] AS $team_id => $team_data)
		{
			$template->assign_block_vars('teams', array(
				'TEAM_NAME'				=> $team_data['team_name'],
				'TEAM_LOGO_THUMBNAIL'	=> ((!empty($team_data['team_logo_tn'])) ? $this->logo_path . '/' . $team_data['team_logo_tn'] : ''),
				'TEAM_RECORD'			=> $sportspredictions->get_team_record($team_id),
				
				'U_EDIT'				=> $this->u_action . '&amp;action=edit&amp;league_id=' . $league_id . '&amp;team_id=' . $team_id,
				'U_DELETE'				=> $this->u_action . '&amp;action=delete&amp;league_id=' . $league_id . '&amp;team_id=' . $team_id)
			);
		}
		
		return;
	}
	
	function games($id, $mode)
	{
		global $phpbb_root_path, $language, $template, $request, $config, $sportspredictions, $user;
		
		// Set some vars
		$action	= $request->variable('action', '');
		$action	= (isset($_POST['add'])) ? 'add' : ((isset($_POST['multiadd'])) ? 'multiadd' : ((isset($_POST['save'])) ? 'save' : ((isset($_POST['multisave'])) ? 'multisave' : ((isset($_POST['change_league']) ? 'change_league' : $action)))));
		
		$s_hidden_fields = '';
		
		$form_name = 'acp_sp_games';
		add_form_key($form_name);
		
		switch ($action)
		{
			case 'edit':
			
				$game_id = $request->variable('game_id', 0);

				if (!$game_id)
				{
					trigger_error($user->lang['ACP_NO_GAME'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				
				$sql = 'SELECT * 
						FROM ' . SP_GAME_TABLE . "
						WHERE game_id = $game_id";
				$result = $db->sql_query($sql);
				$game_info = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$s_hidden_fields .= '<input type="hidden" name="game_id" value="' . $game_id . '" />';
				
			case 'add':

				$league_id = $request->variable('league_id', 0);

				if (!$league_id)
				{
					trigger_error($user->lang['ACP_NO_LEAGUE'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				
				$s_hidden_fields .= '<input type="hidden" name="league_id" value="' . $league_id . '" />';

				if (isset($game_info))
				{
					$gametime_options = $sportspredictions->build_gametime_options($game_info['game_time']);
				}
				else
				{
					$gametime_options = $sportspredictions->build_gametime_options(time());
				}

				if (isset($game_info))
				{
					$s_away_team_options = $sportspredictions->build_team_options($league_id, $game_info['away_id']);
					$s_home_team_options = $sportspredictions->build_team_options($league_id, $game_info['home_id']);
				}
				else
				{
					$s_away_team_options = $sportspredictions->build_team_options($league_id);
					$s_home_team_options = $sportspredictions->build_team_options($league_id);
				}

				$template->assign_vars(array(
					'USER_TIMEZONE'				=> $user->lang['tz_zones'][rtrim(trim($user->data['user_timezone'], '0'), '.')],
					'DISPLAY_TIME'				=> sprintf($user->lang['CURRENT_TIME'], $user->format_date(time(), $format, false)),
					'BONUS'						=> ((isset($game_info['bonus'])) ? $game_info['bonus'] : 0),
					
					'U_BACK'					=> $this->u_action,
					'U_ACTION'					=> $this->u_action,
					
					'S_EDIT_GAME'				=> true,
					'S_GAMETIME_MONTH_OPTIONS'	=> $gametime_options['month'],
					'S_GAMETIME_DAY_OPTIONS'	=> $gametime_options['day'],
					'S_GAMETIME_YEAR_OPTIONS'	=> $gametime_options['year'],
					'S_GAMETIME_HOUR_OPTIONS'	=> $gametime_options['hour'],
					'S_GAMETIME_MINUTE_OPTIONS'	=> $gametime_options['minute'],
					'S_GAMETIME_AMPM_OPTIONS'	=> $gametime_options['ampm'],
					'S_AWAY_TEAM_OPTIONS'		=> $s_away_team_options,
					'S_HOME_TEAM_OPTIONS'		=> $s_home_team_options,
					'S_HIDDEN_FIELDS'			=> $s_hidden_fields)
				);
				
				return;
				
			break;
			
			case 'multiadd':
			
				$league_id	= $request->variable('league_id', 0);
				
				if (!$league_id)
				{
					trigger_error($user->lang['ACP_NO_LEAGUE'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				
				$s_league_options = $sportspredictions->build_league_options($league_id);
				
				for($i = 0; $i < 5; $i++)
				{
					$template->assign_block_vars('multiadd', array());
				}

				$gametime_options 		= $sportspredictions->build_gametime_options(time());
				$s_away_team_options	= $sportspredictions->build_team_options($league_id);
				$s_home_team_options	= $sportspredictions->build_team_options($league_id);
				
				$template->assign_vars(array(
					'USER_TIMEZONE'				=> $user->lang['tz_zones'][rtrim(trim($user->data['user_timezone'], '0'), '.')],
					'DISPLAY_TIME'				=> sprintf($user->lang['CURRENT_TIME'], $user->format_date(time(), $format, false)),
					
					'S_HIDDEN_FIELDS'			=> $s_hidden_fields,
					'S_LEAGUE_OPTIONS'			=> $s_league_options,
					'S_EDIT_GAME'				=> true,
					'S_MULTIADD'				=> true,
					'S_GAMETIME_MONTH_OPTIONS'	=> $gametime_options['month'],
					'S_GAMETIME_DAY_OPTIONS'	=> $gametime_options['day'],
					'S_GAMETIME_YEAR_OPTIONS'	=> $gametime_options['year'],
					'S_GAMETIME_HOUR_OPTIONS'	=> $gametime_options['hour'],
					'S_GAMETIME_MINUTE_OPTIONS'	=> $gametime_options['minute'],
					'S_GAMETIME_AMPM_OPTIONS'	=> $gametime_options['ampm'],
					'S_AWAY_TEAM_OPTIONS'		=> $s_away_team_options,
					'S_HOME_TEAM_OPTIONS'		=> $s_home_team_options,
					
					'U_BACK'			=> $this->u_action,
					'U_ACTION'			=> $this->u_action)
				);
			
			break;
				
			case 'save':
				
				if (!check_form_key($form_name))
				{
					trigger_error($user->lang['FORM_INVALID']. adm_back_link($this->u_action), E_USER_WARNING);
				}
				
				$league_id			= $request->variable('league_id', 0);
				$game_id			= $request->variable('game_id', 0);
				$gametime_month		= $request->variable('gametime_month', 0);
				$gametime_day		= $request->variable('gametime_day', 0);
				$gametime_year		= $request->variable('gametime_year', 0);
				$gametime_hour		= $request->variable('gametime_hour', 0);
				$gametime_minute	= $request->variable('gametime_minute', 99);
				$gametime_ampm		= $request->variable('gametime_ampm', '', true);
				$away_id			= $request->variable('away_id', 0);
				$home_id			= $request->variable('home_id', 0);
				$bonus				= (isset($_POST['bonus'])) ? 1 : 0;
				
				if (!$league_id)
				{
					trigger_error($user->lang['ACP_NO_LEAGUE'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				
				if ($gametime_month == 0 || $gametime_day == 0 || $gametime_year == 0 || $gametime_hour == 0 || $gametime_minute == 99 || $gametime_ampm == '')
				{
					trigger_error($user->lang['ACP_NO_GAME_TIME'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				
				if ($away_id == 0 || $home_id == 0)
				{
					trigger_error($user->lang['ACP_NO_TEAM'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				
				if ($away_id == $home_id)
				{
					trigger_error($user->lang['ACP_SAME_TEAM'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				
				// convert hour to 24 hr
				$gametime_hour = ($gametime_ampm == 'pm') ? (($gametime_hour != 12) ? $gametime_hour + 12 : $gametime_hour) : (($gametime_hour == 12) ? 0 : $gametime_hour);
				
				// convert time to gmt timestamp
				$gametime = gmmktime($gametime_hour, $gametime_minute, 0, $gametime_month, $gametime_day, $gametime_year, $user->data['user_dst']) - ((float) $user->data['user_timezone'] * 3600);
				
				$sql_ary = array(
					'league_id'	=> $league_id,
					'game_time'	=> $gametime,
					'away_id'	=> $away_id,
					'home_id'	=> $home_id,
					'bonus'		=> $bonus
				);
				
				if ($game_id)
				{
					$sql = 'UPDATE ' . SP_GAME_TABLE . '
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
							WHERE game_id = $game_id";
					$db->sql_query($sql);
					$lang = 'ACP_GAME_EDITED';
				}
				else
				{
					$sql = 'INSERT INTO ' . SP_GAME_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
					$db->sql_query($sql);
					$lang = 'ACP_GAME_ADDED';
				}
				
				$cache->destroy('sql', SP_GAME_TABLE);
				$cache->destroy('_sp_stats_array_' . $sportspredictions->get_league_id());
				trigger_error($user->lang[$lang] . adm_back_link($this->u_action . '&amp;league_id=' . $league_id));
				
			break;
			
			case 'multisave':
			
				if (!check_form_key($form_name))
				{
					trigger_error($user->lang['FORM_INVALID']. adm_back_link($this->u_action), E_USER_WARNING);
				}
				
				$league_id			= $request->variable('league_id', 0);
				$game_id			= $request->variable('game_id', array(0));
				$gametime_month		= $request->variable('gametime_month', array(0));
				$gametime_day		= $request->variable('gametime_day', array(0));
				$gametime_year		= $request->variable('gametime_year', array(0));
				$gametime_hour		= $request->variable('gametime_hour', array(0));
				$gametime_minute	= $request->variable('gametime_minute', array(99));
				$gametime_ampm		= $request->variable('gametime_ampm', array(''), true);
				$away_id			= $request->variable('away_id', array(0));
				$home_id			= $request->variable('home_id', array(0));
				
				if (!$league_id)
				{
					trigger_error($user->lang['ACP_NO_LEAGUE'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				
				for ($i = 0; $i < sizeof($gametime_month); $i++)
				{
					if (($gametime_month[$i] != 0 && $gametime_day[$i] != 0 && $gametime_year[$i] != 0 && $gametime_hour[$i] != 0 && $gametime_minute[$i] != 99 && $gametime_ampm[$i] != '') && ($away_id[$i] != 0 && $home_id[$i] != 0))
					{
						$gametime_hour[$i] = ($gametime_ampm[$i] == 'pm') ? (($gametime_hour[$i] != 12) ? $gametime_hour[$i] + 12 : $gametime_hour[$i]) : (($gametime_hour[$i] == 12) ? 0 : $gametime_hour[$i]);
						
						$sql_multi_ary[] = array(
							'league_id'	=> $league_id,
							'game_time'	=> gmmktime($gametime_hour[$i], $gametime_minute[$i], 0, $gametime_month[$i], $gametime_day[$i], $gametime_year[$i], $user->data['user_dst']) - ((float) $user->data['user_timezone'] * 3600),
							'away_id'	=> $away_id[$i],
							'home_id'	=> $home_id[$i],
							'bonus'		=> ((isset($_POST['bonus'][$i])) ? 1 : 0)
						);
					}
				}
				
				if (sizeof($sql_multi_ary))
				{
					$db->sql_multi_insert(SP_GAME_TABLE, $sql_multi_ary);
				}

				$cache->destroy('sql', SP_GAME_TABLE);
				$cache->destroy('_sp_stats_array_' . $league_id);
				trigger_error($user->lang['ACP_GAME_ADDED'] . adm_back_link($this->u_action));
			
			break;
			
			case 'delete':
			
				$league_id	= $request->variable('league_id', 0);
				$game_id	= $request->variable('game_id', 0);
				
				if (!$league_id)
				{
					trigger_error($user->lang['ACP_NO_LEAGUE'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				
				if (!$game_id)
				{
					trigger_error($user->lang['ACP_NO_GAME'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				
				if (confirm_box(true))
				{
					$sql = 'DELETE FROM ' . SP_GAME_TABLE . " WHERE league_id = $league_id AND game_id = $game_id";
					$db->sql_query($sql);
					$_tables_to_uncache[] = SP_GAME_TABLE;
					
					$sql = 'DELETE FROM ' . SP_PREDICTION_TABLE . " WHERE game_id = $game_id";
					$db->sql_query($sql);
					$predictions_deleted = $db->sql_affectedrows();
					
					if (sizeof($predictions_deleted))
					{
						$_tables_to_uncache[] = SP_PREDICTION_TABLE;
					}

					$cache->destroy('sql', $_tables_to_uncache);
					$cache->destroy('_sp_stats_array_' . $league_id);
					trigger_error(sprintf($user->lang['ACP_GAME_REMOVED'], $predictions_deleted) . adm_back_link($this->u_action . '&amp;league_id=' . $league_id));
				}
				else
				{
					confirm_box(false, $user->lang['CONFIRM_DELETE_GAME'], build_hidden_fields(array(
						'i'			=> $id,
						'mode'		=> $mode,
						'league_id'	=> $league_id,
						'game_id'	=> $game_id,
						'action'	=> 'delete',
					)));
				}

			break;
		}

		$league_id	= $request->variable('league_id', $sportspredictions->config['default_league']);
		
		$s_league_options = $sportspredictions->build_league_options($league_id);
		$s_hidden_fields .= '<input type="hidden" name="league_id" value="' . $league_id . '" />';

		$team_count = sizeof($sportspredictions->teams_array[$league_id]);
		
		$template->assign_vars(array(
			'S_LEAGUE_OPTIONS'	=> $s_league_options,
			'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
			'S_SHOW_LEAGUE_BOX'	=> ((sizeof($sportspredictions->leagues_array) >= 2) ? true : false),
			
			'TEAM_COUNT'		=> $team_count,
			'USER_TIMEZONE'		=> $user->lang['tz_zones'][rtrim(trim($user->data['user_timezone'], '0'), '.')],
			
			'U_ACTION'			=> $this->u_action)
		);

		$sql = 'SELECT * FROM ' . SP_GAME_TABLE . ' WHERE league_id = ' . $league_id . ' ORDER BY game_time DESC';
		$result = $db->sql_query($sql);		
		while ($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars('games', array(
				'GAMETIME'		=> $user->format_date($row['game_time']),
				'AWAY_TEAM'		=> $sportspredictions->teams_array['id_ref'][$row['away_id']]['team_name'],
				'HOME_TEAM'		=> $sportspredictions->teams_array['id_ref'][$row['home_id']]['team_name'],
				'BONUS'			=> (($row['bonus'] == 1) ? $user->lang['YES'] : $user->lang['NO']),
				
				'U_EDIT'		=> $this->u_action . '&amp;action=edit&amp;league_id=' . $league_id . '&amp;game_id=' . $row['game_id'],
				'U_DELETE'		=> $this->u_action . '&amp;action=delete&amp;league_id=' . $league_id . '&amp;game_id=' . $row['game_id'])
			);
		}
		$db->sql_freeresult($result);
	}
	
	function scores($id, $mode)
	{
		global $phpbb_root_path, $language, $template, $request, $config, $sportspredictions, $user;
		
		// Set some vars
		$action	= $request->variable('action', '');
		$action = (isset($_POST['add'])) ? 'add' : ((isset($_POST['save'])) ? 'save' : $action);
		
		$s_hidden_fields = '';
		
		$form_name = 'acp_sp_scores';
		add_form_key($form_name);
		
		switch ($action)
		{
			case 'edit':

				$league_id	= $request->variable('league_id', 0);
				$game_id	= $request->variable('game_id', 0);
								
				if (!$league_id)
				{
					trigger_error($user->lang['ACP_NO_LEAGUE'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				
				if (!$game_id)
				{
					trigger_error($user->lang['ACP_NO_GAME'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$sql = 'SELECT * FROM ' . SP_GAME_TABLE . " WHERE league_id = $league_id AND game_id = $game_id";
				$result = $db->sql_query($sql);
				$game_info = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);
			
				$s_hidden_fields .= '<input type="hidden" name="from" value="edit" />';
				$s_hidden_fields .= '<input type="hidden" name="league_id" value="' . $game_info['league_id'] . '" />';
				$s_hidden_fields .= '<input type="hidden" name="game_id" value="' . $game_info['game_id'] . '" />';
				
				$template->assign_vars(array(
					'S_EDIT_SCORE'		=> true,
					'S_HIDDEN_FIELDS'	=> $s_hidden_fields,

					'GAMETIME'			=> $user->format_date($game_info['game_time']),
					'AWAY_TEAM'			=> $sportspredictions->teams_array[$league_id][$game_info['away_id']]['team_name'],
					'HOME_TEAM'			=> $sportspredictions->teams_array[$league_id][$game_info['home_id']]['team_name'],
					'AWAY_SCORE'		=> $game_info['away_score'],
					'HOME_SCORE'		=> $game_info['home_score'],
					
					'U_BACK'			=> $this->u_action,
					'U_ACTION'			=> $this->u_action)
				);
				
				return;
			
			break;
			
			case 'add':
			
				$league_id	= $request->variable('league_id', 0);
				
				if (!$league_id)
				{
					trigger_error($user->lang['ACP_NO_LEAGUE'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
			
				$s_hidden_fields .= '<input type="hidden" name="from" value="add" />';
			
				$template->assign_vars(array(
					'S_ADD_SCORES'		=> true,
					'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
					'U_BACK'			=> $this->u_action,
					'U_ACTION'			=> $this->u_action)
				);
			
				$now = time();
				$sql = 'SELECT COUNT(game_id) AS game_count 
						FROM ' . SP_GAME_TABLE . "
						WHERE league_id = $league_id AND game_time < $now AND away_score IS NULL AND home_score IS NULL";
				$result = $db->sql_query($sql);
				$numrows = (int) $db->sql_fetchfield('game_count');
				if ($numrows == 0)
				{
					trigger_error($user->lang['ACP_NO_PENDING_GAMES']. adm_back_link($this->u_action . '&amp;league_id=' . $league_id), E_USER_WARNING);
				}
				$db->sql_freeresult($result);

				$sql = 'SELECT * FROM ' . SP_GAME_TABLE . " WHERE game_time < $now AND away_score IS NULL AND home_score IS NULL";
				$result = $db->sql_query($sql);
				
				while ($row = $db->sql_fetchrow($result))
				{
					$template->assign_block_vars('pending_games', array(
						'GAME_ID'		=> $row['game_id'],
						'GAMETIME'		=> $user->format_date($row['game_time']),
						'AWAY_TEAM'		=> $sportspredictions->teams_array[$league_id][$row['away_id']]['team_name'],
						'HOME_TEAM'		=> $sportspredictions->teams_array[$league_id][$row['home_id']]['team_name'])
					);
				}
				$db->sql_freeresult($result);
				
				return;
			
			break;
			
			case 'save':
			
				if (!check_form_key($form_name))
				{
					trigger_error($user->lang['FORM_INVALID']. adm_back_link($this->u_action), E_USER_WARNING);
				}
				
				$league_id	= $request->variable('league_id', $sportspredictions->config['default_league']);
				
				$from = $request->variable('from', '');
				
				if ($from == '')
				{
					trigger_error($user->lang['FORM_INVALID']. adm_back_link($this->u_action . '&amp;league_id=' . $league_id), E_USER_WARNING);
				}

				switch ($from)
				{
					case 'add':
					
						$away_score = $request->variable('away_score', array(''));
						$home_score = $request->variable('home_score', array(''));
				
						$now = time();
						$sql = 'SELECT * FROM ' . SP_GAME_TABLE . " WHERE game_time < $now AND away_score IS NULL AND home_score IS NULL";
						$result = $db->sql_query($sql);
						
						while ($row = $db->sql_fetchrow($result))
						{
							if ($away_score[$row['game_id']] != '' && $home_score[$row['game_id']] != '')
							{
								$sql_ary = array(
									'away_score' => (int) $away_score[$row['game_id']],
									'home_score' => (int) $home_score[$row['game_id']]
								);
								
								$sql = 'UPDATE ' . SP_GAME_TABLE . ' 
										SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
										WHERE game_id = ' . $row['game_id'];
								$db->sql_query($sql);
							}
						}
						$db->sql_freeresult($result);
						
					break;
					
					case 'edit':
					
						$game_id = $request->variable('game_id', 0);
						$away_score = $request->variable('away_score', '');
						$home_score = $request->variable('home_score', '');
						
						if (!$game_id)
						{
							trigger_error($user->lang['ACP_NO_GAME'] . adm_back_link($this->u_action . '&amp;league_id=' . $league_id), E_USER_WARNING);
						}
						
						if (($away_score == '' && $home_score != '') || ($away_score != '' && $home_score == ''))
						{
							trigger_error($user->lang['ACP_MISMATCH_SCORE'] . adm_back_link($this->u_action . '&amp;league_id=' . $league_id), E_USER_WARNING);
						}
						
						if ($away_score == '' && $home_score == '')
						{
							confirm_box(false, $user->lang['CONFIRM_CLEAR_SCORE'], build_hidden_fields(array(
								'i'				=> $id,
								'mode'			=> $mode,
								'game_id'		=> $game_id,
								'action'		=> 'delete',
							)));
						}
						
						$sql_ary = array(
							'away_score' => (int) $away_score,
							'home_score' => (int) $home_score
						);
						
						$sql = 'UPDATE ' . SP_GAME_TABLE . '
								SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
								WHERE game_id = $game_id";
						$db->sql_query($sql);
						
					break;
				}
				
				$cache->destroy('sql', SP_GAME_TABLE);
				$cache->destroy('_sp_stats_array_' . $league_id);
				$cache->destroy('_sp_team_record');
				trigger_error($user->lang['ACP_SCORES_UPDATED'] . adm_back_link($this->u_action . '&amp;league_id=' . $league_id));
			
			break;
			
			case 'delete':
			
				$game_id = $request->variable('game_id', 0);
				
				if (!$game_id)
				{
					trigger_error($user->lang['ACP_NO_GAME'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				
				if (confirm_box(true))
				{
					$sql_ary = array(
						'away_score' => NULL,
						'home_score' => NULL
					);
					
					$sql = 'UPDATE ' . SP_GAME_TABLE . '
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
							WHERE game_id = $game_id";
					$db->sql_query($sql);

					$cache->destroy('sql', SP_GAME_TABLE);
					$cache->destroy('_sp_stats_array_' . $league_id);
					$cache->destroy('_sp_team_record');
					trigger_error($user->lang['ACP_SCORE_REMOVED'] . adm_back_link($this->u_action));
				}
				else
				{
					confirm_box(false, $user->lang['CONFIRM_CLEAR_SCORE'], build_hidden_fields(array(
						'i'			=> $id,
						'mode'		=> $mode,
						'game_id'	=> $game_id,
						'action'	=> 'delete',
					)));
				}
			
			break;
		}
		
		$league_id	= $request->variable('league_id', $sportspredictions->config['default_league']);

		$s_league_options = $sportspredictions->build_league_options($league_id);
		$s_hidden_fields .= '<input type="hidden" name="league_id" value="' . $league_id . '" />';
		
		$template->assign_vars(array(
			'S_SHOW_LEAGUE_BOX'	=> ((sizeof($sportspredictions->leagues_array) >= 2) ? true : false),
			'S_LEAGUE_OPTIONS'	=> $s_league_options,
			'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
			
			'U_ACTION'		=> $this->u_action)
		);
		
		$now = time();
		$sql = 'SELECT * FROM ' . SP_GAME_TABLE . " WHERE league_id = $league_id AND game_time < $now AND away_score IS NULL AND home_score IS NULL";
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars('pending_games', array(
				'GAMETIME'		=> $user->format_date($row['game_time']),
				'AWAY_TEAM'		=> $sportspredictions->teams_array[$league_id][$row['away_id']]['team_name'],
				'HOME_TEAM'		=> $sportspredictions->teams_array[$league_id][$row['home_id']]['team_name'])
			);
		}
		$db->sql_freeresult($result);

		$sql = 'SELECT * FROM ' . SP_GAME_TABLE . " WHERE league_id = $league_id AND game_time > $now";
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars('upcoming_games', array(
				'GAMETIME'		=> $user->format_date($row['game_time']),
				'AWAY_TEAM'		=> $sportspredictions->teams_array[$league_id][$row['away_id']]['team_name'],
				'HOME_TEAM'		=> $sportspredictions->teams_array[$league_id][$row['home_id']]['team_name'])
			);
		}
		$db->sql_freeresult($result);

		$sql = 'SELECT * FROM ' . SP_GAME_TABLE . " WHERE league_id = $league_id AND game_time < $now AND away_score IS NOT NULL AND home_score IS NOT NULL";
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars('scored_games', array(
				'GAMETIME'		=> $user->format_date($row['game_time']),
				'AWAY_TEAM'		=> $sportspredictions->teams_array[$league_id][$row['away_id']]['team_name'],
				'HOME_TEAM'		=> $sportspredictions->teams_array[$league_id][$row['home_id']]['team_name'],
				'AWAY_SCORE'	=> $row['away_score'],
				'HOME_SCORE'	=> $row['home_score'],
				'U_EDIT'		=> $this->u_action . '&amp;action=edit&amp;league_id=' . $league_id . '&amp;game_id=' . $row['game_id'],
				'U_DELETE'		=> $this->u_action . '&amp;action=delete&amp;league_id=' . $league_id . '&amp;game_id=' . $row['game_id'])
			);
		}
		$db->sql_freeresult($result);
	}
	
	function predictions($id, $mode)
	{
		global $phpbb_root_path, $language, $template, $request, $config, $sportspredictions, $user;
		
		// Set some vars
		$action	= $request->variable('action', '');
		$action = (isset($_POST['search'])) ? 'search' : ((isset($_POST['save'])) ? 'save' : $action);
		
		$s_hidden_fields = '';
		
		$form_name = 'acp_sp_teams';
		add_form_key($form_name);
		
		switch ($action)
		{
			case 'search':
			
				$league_id	= $request->variable('league_id', 0);
				$user_id	= $request->variable('user_id', 0);
				$game_id	= $request->variable('game_id', 0);
				
				if (!$league_id || (!$user_id && !$game_id))
				{
					trigger_error($user->lang['ACP_SP_VIEW_PREDICTIONS_ERROR'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				
				$s_hidden_fields .= '<input type="hidden" name="league_id" value="' . $league_id . '" />';
				
				if ($user_id)
				{
					// get games not predicted
					$sql_ary = array(
						'SELECT'	=> 'g.*, p.user_id, p.away_prediction, p.home_prediction',
						'FROM'		=> array(SP_GAME_TABLE => 'g'),
						'LEFT_JOIN'	=> array(
							array(
								'FROM'	=> array(SP_PREDICTION_TABLE => 'p'),
								'ON'	=> 'g.game_id = p.game_id AND p.user_id = ' . $user_id,
							),
							array(
								'FROM'	=> array(SP_LEAGUE_TABLE => 'l'),
								'ON'	=> 'g.league_id = l.league_id'
							)
						),
						'WHERE'		=> 'l.league_id = ' . $league_id . ' AND p.away_prediction IS NULL AND p.home_prediction IS NULL',
						'ORDER_BY'	=> 'g.game_time DESC'
					);

					if ($game_id)
					{
						$sql_ary['WHERE'] .= " AND g.game_id = $game_id";
					}

					$sql = $db->sql_build_query('SELECT', $sql_ary);
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						$template->assign_block_vars('empty_predictions', array(
							'GAME_ID'			=> $row['game_id'],
							'USER_ID'			=> $user_id,
							'GAMETIME'			=> $user->format_date($row['game_time']),
							'AWAY_TEAM'			=> $sportspredictions->teams_array[$league_id][$row['away_id']]['team_name'],
							'HOME_TEAM'			=> $sportspredictions->teams_array[$league_id][$row['home_id']]['team_name'],
							'AWAY_SCORE'		=> $row['away_score'],
							'HOME_SCORE'		=> $row['home_score'],
							'AWAY_PREDICTION'	=> $row['away_prediction'],
							'HOME_PREDICTION'	=> $row['home_prediction'])
						);
						
						$game_id_ary[] = $row['game_id'];
					}
					unset($_sql_where_ary);
					
					$s_hidden_fields .= '<input type="hidden" name="user_id" value="' . $user_id . '" />';
				}

				// get games predicted
				$sql_ary = array(
					'SELECT'	=> 'g.*, p.user_id, p.away_prediction, p.home_prediction',
					'FROM'		=> array(SP_GAME_TABLE => 'g'),
					'LEFT_JOIN'	=> array(
						array(
							'FROM'	=> array(SP_PREDICTION_TABLE => 'p'),
							'ON'	=> 'g.game_id = p.game_id',
						),
						array(
							'FROM'	=> array(SP_LEAGUE_TABLE => 'l'),
							'ON'	=> 'g.league_id = l.league_id'
						)
					),
					'ORDER_BY'	=> 'g.game_time DESC'
				);
				
				$_sql_where_ary[] = "l.league_id = $league_id";
				
				if ($user_id)
				{
					$_sql_where_ary[] = "p.user_id = $user_id";
				}
				
				if ($game_id)
				{
					$_sql_where_ary[] = "g.game_id = $game_id";
					$s_hidden_fields .= '<input type="hidden" name="game_id" value="' . $game_id . '" />';
				}
				
				$sql_ary['WHERE'] = implode(' AND ', $_sql_where_ary);	
				$sql = $db->sql_build_query('SELECT', $sql_ary);
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$template->assign_block_vars('full_predictions', array(
						'GAME_ID'				=> $row['game_id'],
						'USER_ID'				=> $row['user_id'],
						'USERNAME'				=> $sportspredictions->username_array[$row['user_id']],
						'GAMETIME'				=> $user->format_date($row['game_time']),
						'AWAY_TEAM'				=> $sportspredictions->teams_array[$league_id][$row['away_id']]['team_name'],
						'HOME_TEAM'				=> $sportspredictions->teams_array[$league_id][$row['home_id']]['team_name'],
						'AWAY_SCORE'			=> $row['away_score'],
						'HOME_SCORE'			=> $row['home_score'],
						'AWAY_PREDICTION'		=> $row['away_prediction'],
						'HOME_PREDICTION'		=> $row['home_prediction'])
					);
					
					$game_id_ary[] = $row['game_id'];
				}
				
				$s_hidden_fields .= '<input type="hidden" name="game_ids" value="' . implode(',', array_unique($game_id_ary)) . '" />';

				$template->assign_vars(array(
					'S_VIEW_PREDICTIONS_SUBMIT'	=> true,
					'S_BY_USERID'				=> (($user_id) ? true : false),
					'S_HIDDEN_FIELDS'			=> $s_hidden_fields,
					
					'SP_USERNAME'				=> (($user_id) ? $sportspredictions->username_array[$user_id] : ''),
					
					'U_ACTION'					=> $this->u_action)
				);
				
				return;
				
			break;
			
			case 'save':
			
				$league_id		= $request->variable('league_id', 0);
				$return_user_id = $request->variable('user_id', 0);
				$return_game_id = $request->variable('game_id', 0);
				$prediction_ary = (isset($_POST['prediction_ary'])) ? $_POST['prediction_ary'] : false;

				if (!$prediction_ary)
				{
					trigger_error($user->lang['ACP_SP_VIEW_PREDICTIONS_ERROR'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				
				$db_predictions = $sportspredictions->get_predictions();

				$sql_multi_ary = array();
				foreach($prediction_ary AS $game_id => $user_info)
				{
					foreach ($user_info AS $user_id => $prediction_data)
					{						
						if ($prediction_data['away'] != '' && $prediction_data['home'] != '')
						{
							if (isset($db_predictions[$user_id][$game_id]))
							{
								if (($prediction_data['away'] != $db_predictions[$user_id][$game_id]['away']) || ($prediction_data['home'] != $db_predictions[$user_id][$game_id]['home']))
								{
									$data['away_prediction'] = (int) $prediction_data['away'];
									$data['home_prediction'] = (int) $prediction_data['home'];
									
									$sql = 'UPDATE ' . SP_PREDICTION_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $data) . ' WHERE game_id = ' . (int) $game_id . ' AND user_id = ' . (int) $user_id;
									$db->sql_query($sql);
								}
							}
							else
							{
								$sql_multi_ary[] = array(
									'game_id'			=> (int) $game_id,
									'user_id'			=> (int) $user_id,
									'away_prediction'	=> (int) $prediction_data['away'],
									'home_prediction'	=> (int) $prediction_data['home']
								);
							}
						}
						elseif ($prediction_data['away'] == '' && $prediction_data['home'] == '')
						{
							if (isset($db_predictions[$user_id][$game_id]))
							{
								$sql = 'DELETE FROM ' . SP_PREDICTION_TABLE . ' WHERE game_id = ' . (int) $game_id . ' AND user_id = ' . (int) $user_id;
								$db->sql_query($sql);
							}
						}
					}
				}
				
				if (sizeof($sql_multi_ary))
				{
					$db->sql_multi_insert(SP_PREDICTION_TABLE, $sql_multi_ary);
				}
				
				$params[] = 'action=search';
				$params[] = 'league_id=' . $league_id;
				if ($return_user_id)
				{
					$params[] = 'user_id=' . $user_id;
				}
				if ($return_game_id)
				{
					$params[] = 'game_id=' . $game_id;
				}
				
				$cache->destroy('sql', SP_PREDICTION_TABLE);
				$cache->destroy('_sp_stats_array_' . $league_id);
				trigger_error($user->lang['ACP_PREDICTIONS_UPDATED'] . adm_back_link($this->u_action . '&amp;' . implode('&amp;', $params)));
			
			break;
		}
		
		$league_id	= $request->variable('league_id', $sportspredictions->config['default_league']);
		
		$s_hidden_fields .= '<input type="hidden" name="league_id" value="' . $league_id . '" />';

		$s_league_options = $sportspredictions->build_league_options($league_id);

		$s_user_options = '<option value="0">--</option>';
		foreach($sportspredictions->username_array AS $user_id => $username)
		{
			$s_user_options .= '<option value="' . $user_id . '">' . $username . '</option>';
		}
		$db->sql_freeresult($result);
		
		$s_game_options = '<option value="0">--</option>';
		$sql = 'SELECT game_id FROM ' . SP_PREDICTION_TABLE . ' GROUP BY game_id';
		$sql_ary = array(
			'SELECT'	=> 'g.*',
			'FROM'		=> array(SP_PREDICTION_TABLE => 'p'),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(SP_GAME_TABLE => 'g'),
					'ON'	=> 'p.game_id = g.game_id'
				),
				array(
					'FROM'	=> array(SP_LEAGUE_TABLE => 'l'),
					'ON'	=> 'g.league_id = l.league_id'
				)
			),
			'WHERE'		=> 'l.league_id = ' . $league_id,
			'GROUP_BY'	=> 'p.game_id',
			'ORDER_BY'	=> 'g.game_time DESC'
		);
		$sql = $db->sql_build_query('SELECT', $sql_ary);
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$s_game_options .= '<option value="' . $row['game_id'] . '">' . $sportspredictions->teams_array[$league_id][$row['away_id']]['team_name'] . ' @ ' . $sportspredictions->teams_array[$league_id][$row['home_id']]['team_name'] . ' (' . $user->format_date($row['game_time']) . ')</option>';
		}
		$db->sql_freeresult($result);

		$template->assign_vars(array(
			'S_SHOW_LEAGUE_BOX'	=> ((sizeof($sportspredictions->leagues_array) >= 2) ? true : false),
			'S_LEAGUE_OPTIONS'	=> $s_league_options,
			'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
			'S_USER_OPTIONS'	=> $s_user_options,
			'S_GAME_OPTIONS'	=> $s_game_options,
			
			'U_ACTION'			=> $this->u_action)
		);
		
		return;
	}
}

?>