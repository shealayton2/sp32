<?php
/**
*
* @package Sports Predictions
* @version $Id$
* @copyright (c) 2011 www.shealayton.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Sports Predictions
* @package phpBB3
*/
class sportspredictions
{
	public $config				= array();
	public $leagues_array		= array();
	public $teams_array			= array();
	public $stats_array			= array();
	public $username_array		= array();
	public $prediction_array	= array();
	public $team_record_array	= array();
	
	private $league_id;
	private $in_admin	= false;
	
	function sportspredictions($include_mode = 'user')
	{
		global $db, $cache, $user;
		
		$this->in_admin = ($include_mode == 'admin') ? true : false;
		
		$this->load_config();
		$this->load_leagues_array();
		$this->load_teams_array();
		$this->load_username_array();
		$this->load_prediction_array();		

		if (!$this->in_admin)
		{
			if ($user->data['user_sp_default_league'] != 0)
			{
				if (isset($this->leagues_array[$user->data['user_sp_default_league']]) && $this->leagues_array[$user->data['user_sp_default_league']]['active'] == 1)
				{
					$this->league_id = $user->data['user_sp_default_league'];
				}
				else
				{
					$this->league_id = $this->config['default_league'];
					
					$data['user_sp_default_league'] = $this->config['default_league'];
					$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $data) . ' WHERE user_id = ' . $user->data['user_id'];
					$db->sql_query($sql);
				}
			}
			else
			{
				$this->league_id = $this->config['default_league'];

				$data['user_sp_default_league'] = $this->config['default_league'];
				$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $data) . ' WHERE user_id = ' . $user->data['user_id'];
				$db->sql_query($sql);
			}
		}
		else
		{
			$this->league_id = $this->config['default_league'];
		}
		
		$this->update_team_record();
	}
	
/**
* Fill $this->config with cache if possible, if not load from DB
*
*/
	function load_config()
	{
		global $db, $cache;
		
		$this->config = $cache->get('_sp_config');
		
		if (empty($this->config))
		{
			$sql = 'SELECT * FROM ' . SP_CONFIG_TABLE;
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$this->config[$row['config_name']] = $row['config_value'];
			}
			$db->sql_freeresult($result);
			
			if ($this->config['use_cache'] == 0)
			{
				$this->config['cache_ttl'] = 0;
			}
			
			if ($this->config['use_cache'])
			{
				$cache->put('_sp_config', $this->config);
			}
		}
		
		return;
	}
	
/**
* Fill $this->leagues_array with cache if possible, if not load from DB
*
*/	
	function load_leagues_array()
	{
		global $db;
	
		$sql = 'SELECT * FROM ' . SP_LEAGUE_TABLE;
		$result = $db->sql_query($sql, $this->config['cache_ttl']);
		while ($row = $db->sql_fetchrow($result))
		{
			$this->leagues_array[$row['league_id']] = array(
				'league_name'					=> $row['league_name'],
				'league_logo'					=> $row['league_logo'],
				'league_logo_tn'				=> $row['league_logo_tn'],
				'scoring_style'					=> $row['scoring_style'],
				'pointdiff_average'				=> $row['pointdiff_average'],
				'perfect_round_bonus'			=> $row['perfect_round_bonus'],
				'perfect_round_bonus_points'	=> $row['perfect_round_bonus_points'],
				'active'						=> $row['active']
			);
		}
		$db->sql_freeresult($result);
		
		return;
	}
	
/**
* Fill $this->teams_array with cache if possible, if not load from DB
*
*/
	function load_teams_array()
	{
		global $db;
		
		$sql = 'SELECT * FROM ' . SP_TEAM_TABLE . ' ORDER BY team_name';
		$result = $db->sql_query($sql, $this->config['cache_ttl']);
		while ($row = $db->sql_fetchrow($result))
		{
			$this->teams_array[$row['league_id']][$row['team_id']] = array(
				'team_name'		=> $row['team_name'],
				'team_logo'		=> $row['team_logo'],
				'team_logo_tn'	=> $row['team_logo_tn'],
				'show_results'	=> $row['show_results']
			);
			
			$this->teams_array['id_ref'][$row['team_id']] = array(
				'league_id'		=> $row['league_id'],
				'team_name'		=> $row['team_name'],
				'team_logo'		=> $row['team_logo'],
				'team_logo_tn'	=> $row['team_logo_tn'],
				'show_results'	=> $row['show_results']
			);
		}
		$db->sql_freeresult($result);
		asort($this->teams_array);
		
		return;
	}
	
/**
* Fill $this->username_array with cache if possible, if not load from DB
*
*/
	function load_username_array()
	{
		global $db;
		
		$sql_ary = array(
			'SELECT'	=> 'u.user_id, u.username',
			'FROM'		=> array(SP_PREDICTION_TABLE => 'p'),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(USERS_TABLE => 'u'),
					'ON'	=> 'p.user_id = u.user_id'
				)
			),
			'GROUP_BY'	=> 'p.user_id',
			'ORDER_BY'	=> 'u.username ASC'
		);
		$sql = $db->sql_build_query('SELECT', $sql_ary);
		$result = $db->sql_query($sql, $this->config['cache_ttl']);
		while ($row = $db->sql_fetchrow($result))
		{
			$this->username_array[$row['user_id']] = $row['username'];
		}
		$db->sql_freeresult($result);
		asort($this->username_array);
		
		return;
	}
	
/**
* Fill $this->prediction_array with cache if possible, if not load from DB
*
*/
	function load_prediction_array()
	{
		global $db;
		
		$sql = 'SELECT * FROM ' . SP_PREDICTION_TABLE;
		$result = $db->sql_query($sql, $this->config['cache_ttl']);
		while ($row = $db->sql_fetchrow($result))
		{
			$this->prediction_array['byuser'][$row['user_id']][$row['game_id']] = array(
				'away_prediction' => $row['away_prediction'],
				'home_prediction' => $row['home_prediction']
			);
			$this->prediction_array['bygame'][$row['game_id']][$row['user_id']] = array(
				'away_prediction' => $row['away_prediction'],
				'home_prediction' => $row['home_prediction']
			);
		}
		
		return;
	}
	
/**
* Fill $this->team_record_array with cache if possible, if not load from DB
*
*/
	function update_team_record()
	{
		global $db, $cache;
		
		$this->team_record_array = $cache->get('_sp_team_record');

		if (empty($this->team_record_array))
		{
			$update_data['wins'] = $update_data['losses'] = $update_data['ties'] = 0;
			$sql = 'UPDATE ' . SP_TEAM_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $update_data);
			$db->sql_query($sql);
			unset($update_data);
		
			$sql = 'SELECT * FROM ' . SP_GAME_TABLE . ' WHERE away_score IS NOT NULL AND home_score IS NOT NULL';
			$result = $db->sql_query($sql, $this->config['cache_ttl']);
			while ($row = $db->sql_fetchrow($result))
			{
				if ($row['away_score'] == $row['home_score'])
				{
					$this->team_record_array[$row['away_id']]['ties'] += 1;
					$this->team_record_array[$row['home_id']]['ties'] += 1;
				}
				elseif ($row['away_score'] > $row['home_score'])
				{
					$this->team_record_array[$row['away_id']]['wins'] += 1;
					$this->team_record_array[$row['home_id']]['losses'] += 1;
				}
				elseif ($row['away_score'] < $row['home_score'])
				{
					$this->team_record_array[$row['away_id']]['losses'] += 1;
					$this->team_record_array[$row['home_id']]['wins'] += 1;
				}
			}
			
			if (empty($this->team_record_array)) {
				return;
			}
			
			foreach ($this->team_record_array AS $team_id => $record_data)
			{
				$update_data['wins']	= (isset($record_data['wins'])) ? (int) $record_data['wins'] : 0;
				$update_data['losses']	= (isset($record_data['losses'])) ? (int) $record_data['losses'] : 0;
				$update_data['ties']	= (isset($record_data['ties'])) ? (int) $record_data['ties'] : 0;
				
				$sql = 'UPDATE ' . SP_TEAM_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $update_data) . ' WHERE team_id = ' . $team_id;
				$db->sql_query($sql);
				
				$this->team_record_array[$team_id]['wins']		= $update_data['wins'];
				$this->team_record_array[$team_id]['losses']	= $update_data['losses'];
				$this->team_record_array[$team_id]['ties']		= $update_data['ties'];
			}

			$cache->put('_sp_team_record', $this->team_record_array);
		}
		
		return;
	}
	
/**
* Get team record for display
*
* @param int $team_id
*
* @return string		Record in (W-L-T) format
*/
	function get_team_record($team_id)
	{
		if ($this->teams_array['id_ref'][$team_id]['show_results'] == 0)
		{
			return '';
		}
		else
		{
			if (isset($this->team_record_array[$team_id]))
			{
				return '(' . $this->team_record_array[$team_id]['wins'] . '-' . $this->team_record_array[$team_id]['losses'] . '' . (($this->team_record_array[$team_id]['ties'] != 0) ? '-' . $this->team_record_array[$team_id]['ties'] : '') . ')';
			}
			else
			{
				return '(0-0)';
			}
		}
	}
	
/**
* Get games whose gametime has not passed regardless of prediction status
*
* @param int $limit		Number of games to return
* @param int $offset	Where to start the array to be returned
*
* @return array			Game data
*/

	function get_upcoming_games($limit = 0, $offset = 0)
	{
		global $db;

		$sql = 'SELECT game_id, game_time, away_id, home_id, bonus FROM ' . SP_GAME_TABLE . ' WHERE league_id = ' . $this->league_id . ' AND game_time > ' . time() . ' ORDER BY game_time';
		$result = $db->sql_query_limit($sql, $limit, $offset);
		while ($row = $db->sql_fetchrow($result))
		{
			$return_ary[$row['game_id']]['game_time']	= $row['game_time'];
			$return_ary[$row['game_id']]['away_id']		= $row['away_id'];
			$return_ary[$row['game_id']]['home_id']		= $row['home_id'];
			$return_ary[$row['game_id']]['away_team']	= $this->teams_array[$this->league_id][$row['away_id']]['team_name'];
			$return_ary[$row['game_id']]['home_team']	= $this->teams_array[$this->league_id][$row['home_id']]['team_name'];
			$return_ary[$row['game_id']]['bonus']		= $row['bonus'];
		}
		$db->sql_freeresult($result);
		
		return $return_ary;
	}
	
/**
* Get games to predict for a particular user
*
* @param int 	$user_id		user_id of user to pull
* @param bool 	$predicted		if false, returns games that have not been predicted. if true, returns games that have been predicted but can be edited
*
* @return array					Game data
*/

	function get_games_to_predict($user_id, $predicted = false)
	{
		global $db;

		$games_predicted_ary = array_keys((array) $this->prediction_array['byuser'][$user_id]);

		$sql = 'SELECT game_id, game_time, away_id, home_id, bonus
				FROM ' . SP_GAME_TABLE . ' 
				WHERE league_id = ' . $this->league_id . ' AND game_time > ' . time() . ((sizeof($games_predicted_ary)) ? (($predicted == true) ? ' AND ' . $db->sql_in_set('game_id', $games_predicted_ary) : ' AND ' . $db->sql_in_set('game_id', $games_predicted_ary, true)) : '') . ' 
				ORDER BY game_time';
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$return_ary[$row['game_id']]['game_time']	= $row['game_time'];
			$return_ary[$row['game_id']]['away_id']		= $row['away_id'];
			$return_ary[$row['game_id']]['home_id']		= $row['home_id'];
			$return_ary[$row['game_id']]['bonus']		= $row['bonus'];
			$return_ary[$row['game_id']]['away_team']	= $this->teams_array[$this->league_id][$row['away_id']]['team_name'];
			$return_ary[$row['game_id']]['home_team']	= $this->teams_array[$this->league_id][$row['home_id']]['team_name'];
			if ($predicted == true)
			{
				$return_ary[$row['game_id']]['away_prediction']	= $this->prediction_array['byuser'][$user_id][$row['game_id']]['away_prediction'];
				$return_ary[$row['game_id']]['home_prediction']	= $this->prediction_array['byuser'][$user_id][$row['game_id']]['home_prediction'];
			}
		}
		$db->sql_freeresult($result);
		
		return $return_ary;
	}

/**
* Get predictions for a particular user
*
* @param int 	$user_id		user_id of user to pull
*
* @return array					array of users predictions
*/

	function get_user_predictions($user_id)
	{
		return $this->prediction_array['byuser'][$user_id];
	}

/**
* Get predictions for all users
*
* @return array		returns array( [user_id] => array( [game_id] => array( [away] => $away_prediction, [home] => $home_prediction) ) )
*/

	function get_predictions($by = 'user')
	{
		if ($by == 'game')
		{
			return $this->prediction_array['bygame'];
		}
		else
		{
			return $this->prediction_array['byuser'];
		}
	}

/**
* Add predictions to the DB for a particular user
*
* @param int 	$user_id				user_id of user to pull
* @param array 	$away_predictions		array of away predictions - array([game_id] => prediction)
* @param array 	$home_predictions		array of home predictions - array([game_id] => prediction)
* @param bool 	$update					false = adding predictions, true = updating predictions
*
* @return NULL
*/
	function add_predictions($user_id, $away_predictions, $home_predictions, $update = false)
	{
		global $db, $cache;
		
		if ($update)
		{
			$game_ary = $this->get_games_to_predict($user_id, true);
		}
		else
		{
			$game_ary = $this->get_games_to_predict($user_id);
		}
		
		$sql_multi_ary = array();
		foreach($game_ary AS $game_id => $game_info)
		{
			if (trim($away_predictions[$game_id]) != '' && trim($home_predictions[$game_id]) != '')
			{
				if ($update)
				{
					$data['away_prediction'] = (int) $away_predictions[$game_id];
					$data['home_prediction'] = (int) $home_predictions[$game_id];
				
					$sql = 'UPDATE ' . SP_PREDICTION_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $data) . ' WHERE user_id = ' . (int) $user_id . ' AND game_id = ' . (int) $game_id;
					$db->sql_query($sql);
				}
				else
				{
					$sql_multi_ary[] = array(
						'user_id'			=> $user_id,
						'game_id'			=> $game_id,
						'away_prediction'	=> (int) $away_predictions[$game_id],
						'home_prediction'	=> (int) $home_predictions[$game_id]
					);
				}
			}
			else if ($update && (trim($away_predictions[$game_id]) == '' && trim($home_predictions[$game_id]) == ''))
			{
				$sql = 'DELETE FROM ' . SP_PREDICTION_TABLE . ' WHERE user_id = ' . (int) $user_id . ' AND game_id = ' . (int) $game_id;
				$db->sql_query($sql);
			}
		}
		if (sizeof($sql_multi_ary))
		{
			$db->sql_multi_insert(SP_PREDICTION_TABLE, $sql_multi_ary);
		}
		
		$cache->destroy('sql', SP_PREDICTION_TABLE);
		$cache->destroy('_sp_stats_array_' . $this->league_id);
		
		return;
	}
	
/**
* This is the main function that compares the predictions to the actual scores and builds the leaderboard
*
* @param string	$sort		field to sort by
* @param bool	$limit		how many results to return - if false, returns all results
* @param int	$start		offset to start
*
* @return array
*/
	
	function build_leaderboard($sort = 'points', $limit = false, $start = 0)
	{
		global $db, $cache;
		
		if ($this->config['use_cache'])
		{
			$this->stats_array[$this->league_id] = $cache->get('_sp_stats_array_' . $this->league_id);
			$this->stats_by_round[$this->league_id] = $cache->get('_sp_stats_by_round_' . $this->league_id);
		}
		
		if (empty($this->stats_array[$this->league_id]))
		{
			/*
			$sql_ary = array(
				'SELECT'	=> 'p.game_id, p.user_id, p.away_prediction, p.home_prediction',
				'FROM'		=> array(SP_PREDICTION_TABLE => 'p'),
				'LEFT_JOIN'	=> array(
					array(
						'FROM'	=> array(SP_GAME_TABLE => 'g'),
						'ON'	=> 'p.game_id = g.game_id'
					)
				),
				'WHERE'		=> 'g.league_id = ' . $this->league_id . ' AND g.away_score IS NOT NULL AND g.home_score IS NOT NULL',
				'ORDER_BY'	=> 'p.user_id, p.game_id'
			);
			$sql = $db->sql_build_query('SELECT', $sql_ary);
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$prediction_array[$row['user_id']][$row['game_id']] = array(
					'away_prediction'	=> $row['away_prediction'],
					'home_prediction'	=> $row['home_prediction']
				);
			}
			$db->sql_freeresult($result);
			*/
			
			$sql_ary = array(
				'SELECT'	=> 'p.game_id, p.user_id, p.away_prediction, p.home_prediction',
				'FROM'		=> array(SP_PREDICTION_TABLE => 'p'),
				'LEFT_JOIN'	=> array(
					array(
						'FROM'	=> array(SP_GAME_TABLE => 'g'),
						'ON'	=> 'p.game_id = g.game_id'
					)
				),
				'WHERE'		=> 'g.league_id = ' . $this->league_id . ' AND g.away_score IS NOT NULL AND g.home_score IS NOT NULL',
				'ORDER_BY'	=> 'p.game_id, p.user_id'
			);
			$sql = $db->sql_build_query('SELECT', $sql_ary);
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$prediction_array[$row['game_id']][$row['user_id']] = array(
					'away_prediction'	=> $row['away_prediction'],
					'home_prediction'	=> $row['home_prediction']
				);
			}
			$db->sql_freeresult($result);
			
			/*			
			$sql = 'SELECT game_id, away_id, home_id, away_score, home_score, bonus FROM ' . SP_GAME_TABLE . ' WHERE league_id = ' . $this->league_id . ' AND away_score IS NOT NULL AND home_score IS NOT NULL ORDER BY game_id';
			$result = $db->sql_query($sql);
			while ( $row = $db->sql_fetchrow($result) )
			{
				$game_list[] = $row['game_id'];

				$game_data[$row['game_id']]['away_id']		= $row['away_id'];
				$game_data[$row['game_id']]['home_id']		= $row['home_id'];
				$game_data[$row['game_id']]['away_score']	= $row['away_score'];
				$game_data[$row['game_id']]['home_score']	= $row['home_score'];
				$game_data[$row['game_id']]['bonus']		= $row['bonus'];
			}
			$db->sql_freeresult($result);
			*/
			
			$sql = 'SELECT game_id, round, away_id, home_id, away_score, home_score, bonus FROM ' . SP_GAME_TABLE . ' WHERE league_id = ' . $this->league_id . ' AND away_score IS NOT NULL AND home_score IS NOT NULL ORDER BY round, game_time';
			$result = $db->sql_query($sql);
			while ( $row = $db->sql_fetchrow($result) )
			{
				$game_list[] = $row['game_id'];

				$game_array[$row['round']][$row['game_id']]['away_id']		= $row['away_id'];
				$game_array[$row['round']][$row['game_id']]['home_id']		= $row['home_id'];
				$game_array[$row['round']][$row['game_id']]['away_score']	= $row['away_score'];
				$game_array[$row['round']][$row['game_id']]['home_score']	= $row['home_score'];
				$game_array[$row['round']][$row['game_id']]['bonus']		= $row['bonus'];
			}
			$db->sql_freeresult($result);
			
			$sql = 'SELECT round, COUNT(game_id) AS game_count FROM ' . SP_GAME_TABLE . ' WHERE league_id = ' . $this->league_id . ' GROUP BY round ORDER BY round';
			$result = $db->sql_query($sql);
			while ( $row = $db->sql_fetchrow($result) )
			{
				$games_per_round[$row['round']] = $row['game_count'];
			}
			
			if (empty($prediction_array) && empty($game_array))
			{
				//return array();
			}

			/*
			foreach($prediction_array AS $user_id => $_tmp_array)
			{
				foreach($_tmp_array AS $game_id => $prediction_data)
				{
					if (($prediction_data['away_prediction'] == $game_data[$game_id]['away_score']) && ($prediction_data['home_prediction'] == $game_data[$game_id]['home_score']))
					{
						// exact pick
						$_tmp_stats[$user_id]['wins']++;
						$_tmp_stats[$user_id]['points'] += $this->config['exact_prediction_points'];
						
						// bonus game?
						if ($game_data[$game_id]['bonus'] == 1)
						{
							$_tmp_stats[$user_id]['points'] += $this->config['exact_prediction_points'];
						}
					}
					elseif ((($prediction_data['away_prediction'] > $prediction_data['home_prediction']) && ($game_data[$game_id]['away_score'] > $game_data[$game_id]['home_score'])) || (($prediction_data['away_prediction'] < $prediction_data['home_prediction']) && ($game_data[$game_id]['away_score'] < $game_data[$game_id]['home_score'])))
					{
						// correct winner, wrong score
						$_tmp_stats[$user_id]['wins']++;
						$_tmp_stats[$user_id]['points'] += $this->config['correct_prediction_points'];
						$_tmp_stats[$user_id]['pointdiff'] += abs((intval($prediction_data['away_prediction']) + intval($prediction_data['home_prediction'])) - (intval($game_data[$game_id]['away_score']) + intval($game_data[$game_id]['home_score'])));
						
						// bonus game?
						if ($game_data[$game_id]['bonus'] == 1)
						{
							$_tmp_stats[$user_id]['points'] += $this->config['correct_prediction_points'];
						}
					}
					else
					{
						// wrong winner
						$_tmp_stats[$user_id]['losses']++;
						$_tmp_stats[$user_id]['points'] -= $this->config['incorrect_prediction_points'];
						$_tmp_stats[$user_id]['pointdiff'] += abs((intval($prediction_data['away_prediction']) + intval($prediction_data['home_prediction'])) - (intval($game_data[$game_id]['away_score']) + intval($game_data[$game_id]['home_score'])));
						
						// bonus game?
						if ($game_data[$game_id]['bonus'] == 1)
						{
							$_tmp_stats[$user_id]['points'] -= $this->config['incorrect_prediction_points'];
						}
					}
				}
				$user_id_ary[] = $user_id;
			}
			*/

			if (is_array($game_array)) {
				// start by looping through each round
				foreach ($game_array AS $_round => $_tmp_game_array)
				{
					// reset the _tmp_round_stats
					unset($_tmp_round_stats);
					
					// reset the game_count for this round
					$game_count = 0;
					
					// then loop through each game id
					foreach ($_tmp_game_array AS $_game_id => $_game_data)
					{
						$game_count++;
						
						// now loop through each prediction for this game
						foreach ($prediction_array[$_game_id] AS $_user_id => $_prediction_data)
						{
							if ($this->leagues_array[$this->league_id]['scoring_style'] == 'score')
							{
								if (($_prediction_data['away_prediction'] == $_game_data['away_score']) && ($_prediction_data['home_prediction'] == $_game_data['home_score']))
								{
									// exact pick
									$_tmp_stats[$_user_id]['wins']++;
									$_tmp_stats[$_user_id]['points'] += $this->config['exact_prediction_points'];
									
									$_tmp_round_stats[$_user_id]['wins']++;
									$_tmp_round_stats[$_user_id]['points'] += $this->config['exact_prediction_points'];
									
									// bonus game?
									if ($_game_data['bonus'] == 1)
									{
										$_tmp_stats[$_user_id]['points'] += $this->config['exact_prediction_points'];
										$_tmp_round_stats[$_user_id]['points'] += $this->config['exact_prediction_points'];
									}
								}
								elseif ((($_prediction_data['away_prediction'] > $_prediction_data['home_prediction']) && ($_game_data['away_score'] > $_game_data['home_score'])) || (($_prediction_data['away_prediction'] < $_prediction_data['home_prediction']) && ($_game_data['away_score'] < $_game_data['home_score'])))
								{
									// correct winner, wrong score
									$_tmp_stats[$_user_id]['wins']++;
									$_tmp_stats[$_user_id]['points'] += $this->config['correct_prediction_points'];
									$_tmp_stats[$_user_id]['pointdiff'] += abs((intval($_prediction_data['away_prediction']) + intval($_prediction_data['home_prediction'])) - (intval($_game_data['away_score']) + intval($_game_data['home_score'])));
									
									$_tmp_round_stats[$_user_id]['wins']++;
									$_tmp_round_stats[$_user_id]['points'] += $this->config['correct_prediction_points'];
									$_tmp_round_stats[$_user_id]['pointdiff'] += abs((intval($_prediction_data['away_prediction']) + intval($_prediction_data['home_prediction'])) - (intval($_game_data['away_score']) + intval($_game_data['home_score'])));
									
									// bonus game?
									if ($_game_data['bonus'] == 1)
									{
										$_tmp_stats[$_user_id]['points'] += $this->config['correct_prediction_points'];
										$_tmp_round_stats[$_user_id]['points'] += $this->config['correct_prediction_points'];
									}
								}
								else
								{
									// wrong winner
									$_tmp_stats[$_user_id]['losses']++;
									$_tmp_stats[$_user_id]['points'] -= $this->config['incorrect_prediction_points'];
									$_tmp_stats[$_user_id]['pointdiff'] += abs((intval($_prediction_data['away_prediction']) + intval($_prediction_data['home_prediction'])) - (intval($_game_data['away_score']) + intval($_game_data['home_score'])));
									
									$_tmp_round_stats[$_user_id]['losses']++;
									$_tmp_round_stats[$_user_id]['points'] -= $this->config['incorrect_prediction_points'];
									$_tmp_round_stats[$_user_id]['pointdiff'] += abs((intval($_prediction_data['away_prediction']) + intval($_prediction_data['home_prediction'])) - (intval($_game_data['away_score']) + intval($_game_data['home_score'])));
									
									// bonus game?
									if ($_game_data['bonus'] == 1)
									{
										$_tmp_stats[$_user_id]['points'] -= $this->config['incorrect_prediction_points'];
										$_tmp_round_stats[$_user_id]['points'] -= $this->config['incorrect_prediction_points'];
									}
								}
							}
							else
							{
								if ($_prediction_data['away_prediction'] == $_prediction_data['home_prediction'] && $_game_data['away_score'] == $_game_data['home_score'])
								{
									// draw
									$_tmp_stats[$_user_id]['wins']++;
									$_tmp_stats[$_user_id]['points'] += $this->config['draw_prediction_points'];

									$_tmp_round_stats[$_user_id]['wins']++;
									$_tmp_round_stats[$_user_id]['points'] += $this->config['draw_prediction_points'];
									
									if ($_game_data['bonus'] == 1)
									{
										$_tmp_stats[$_user_id]['points'] += $this->config['draw_prediction_points'];
										$_tmp_round_stats[$_user_id]['points'] += $this->config['draw_prediction_points'];
									}
								}
								elseif ((($_prediction_data['away_prediction'] > $_prediction_data['home_prediction']) && ($_game_data['away_score'] > $_game_data['home_score'])) || (($_prediction_data['away_prediction'] < $_prediction_data['home_prediction']) && ($_game_data['away_score'] < $_game_data['home_score'])))
								{
									// correct
									$_tmp_stats[$_user_id]['wins']++;
									$_tmp_stats[$_user_id]['points'] += $this->config['correct_prediction_points'];
									
									$_tmp_round_stats[$_user_id]['wins']++;
									$_tmp_round_stats[$_user_id]['points'] += $this->config['correct_prediction_points'];
									
									// bonus game?
									if ($_game_data['bonus'] == 1)
									{
										$_tmp_stats[$_user_id]['points'] += $this->config['correct_prediction_points'];
										$_tmp_round_stats[$_user_id]['points'] += $this->config['correct_prediction_points'];
									}
								}
								else
								{
									// wrong
									$_tmp_stats[$_user_id]['losses']++;
									$_tmp_stats[$_user_id]['points'] -= $this->config['incorrect_prediction_points'];
									
									$_tmp_round_stats[$_user_id]['losses']++;
									$_tmp_round_stats[$_user_id]['points'] -= $this->config['incorrect_prediction_points'];
									
									// bonus game?
									if ($_game_data['bonus'] == 1)
									{
										$_tmp_stats[$_user_id]['points'] -= $this->config['incorrect_prediction_points'];
										$_tmp_round_stats[$_user_id]['points'] -= $this->config['incorrect_prediction_points'];
									}
								}
							}
						}
					} // end game loop
					
					// round has ended
					if ($game_count == $games_per_round[$_round])
					{
						if ($this->leagues_array[$this->league_id]['perfect_round_bonus'])
						{
							foreach($_tmp_round_stats AS $_user_id => $_tmp)
							{
								if ($_tmp['wins'] > 0 && $_tmp['losses'] == 0) {
									$_tmp_stats[$_user_id]['points'] += $this->leagues_array[$this->league_id]['perfect_round_bonus_points'];
									$_tmp_round_stats[$_user_id]['points'] += $this->leagues_array[$this->league_id]['perfect_round_bonus_points'];
								}
							}
						}
					}
					
					$this->stats_by_round[$this->league_id]['individual'][$_round] = $_tmp_round_stats;
					$this->stats_by_round[$this->league_id]['cumulative'][$_round] = $_tmp_stats;
					
				} // end round loop
			}
			
			if ($this->leagues_array[$this->league_id]['pointdiff_average'] && $this->leagues_array[$this->league_id]['scoring_style'] == 'score')
			{
				foreach ($_tmp_stats AS $user_id => $_tmp)
				{
					$_tmp_stats[$user_id]['pointdiff'] = round(($_tmp['pointdiff'] / sizeof($prediction_array[$user_id])), 2);
				}
			}

			if (!$_tmp_stats)
			{
				$sql_ary = array(
					'SELECT'	=> 'p.user_id',
					'FROM'		=> array(SP_PREDICTION_TABLE => 'p'),
					'LEFT_JOIN'	=> array(
						array(
							'FROM'	=> array(SP_GAME_TABLE => 'g'),
							'ON'	=> 'p.game_id = g.game_id'
						)
					),
					'WHERE'		=> 'g.league_id = ' . $this->league_id,
					'ORDER_BY'	=> 'p.user_id'
				);
				$sql = $db->sql_build_query('SELECT_DISTINCT', $sql_ary);
				$result = $db->sql_query($sql);
				while ( $row = $db->sql_fetchrow($result) )
				{
					$_tmp_stats[$row['user_id']] = array('wins' => 0, 'losses' => 0, 'pointdiff' => 0, 'points' => 0);
				}
				
				$sort = 'username';
			}

			foreach($_tmp_stats AS $user_id => $stat_data) {
				$stats_array[] = array(
					'user_id'	=> $user_id,
					'username'	=> $this->username_array[$user_id],
					'wins'		=> ((isset($stat_data['wins'])) ? $stat_data['wins'] : 0),
					'losses'	=> ((isset($stat_data['losses'])) ? $stat_data['losses'] : 0),
					'pointdiff'	=> ((isset($stat_data['pointdiff'])) ? $stat_data['pointdiff'] : 0),
					'points'	=> ((isset($stat_data['points'])) ? $stat_data['points'] : 0),
					'winperc'	=> (($stat_data['wins'] + $stat_data['losses'] != 0) ? round(($stat_data['wins']/($stat_data['wins'] + $stat_data['losses'])) * 100, 1) : 0)
				);
			}
			
			$this->stats_array[$this->league_id] = $stats_array;
			
			if ($this->config['use_cache'])
			{
				$cache->put('_sp_stats_array_' . $this->league_id, $this->stats_array[$this->league_id], $this->config['cache_ttl']);
				$cache->put('_sp_stats_by_round_' . $this->league_id, $this->stats_by_round[$this->league_id], $this->config['cache_ttl']);
			}
		}
		
		unset($_user_id);
		foreach ( $this->stats_array[$this->league_id] as $key => $row )
		{
			$_user_id[$key]		= $row['user_id'];
			$_username[$key]	= strtolower($row['username']);
			$_wins[$key]		= $row['wins'];
			$_losses[$key]		= $row['losses'];
			$_winperc[$key]		= $row['winperc'];
			$_pointdiff[$key]	= $row['pointdiff'];
			$_points[$key]		= $row['points'];
		}

		array_multisort($_points, SORT_DESC, $_pointdiff, SORT_ASC, $this->stats_array[$this->league_id]);
		foreach($this->stats_array[$this->league_id] AS $rank => $data)
		{
			$this->stats_array[$this->league_id][$rank]['rank'] = $rank + 1;
		}
		
		switch ($sort)
		{
			case 'username':
				array_multisort($_username, SORT_ASC, $this->stats_array[$this->league_id]);
			break;
			
			case 'wins':
				array_multisort($_wins, SORT_DESC, $this->stats_array[$this->league_id]);
			break;
			
			case 'losses':
				array_multisort($_losses, SORT_DESC, $this->stats_array[$this->league_id]);
			break;
			
			case 'winperc':
				array_multisort($_winperc, SORT_DESC, $this->stats_array[$this->league_id]);
			break;
			
			case 'pointdiff':
				array_multisort($_pointdiff, SORT_ASC, $this->stats_array[$this->league_id]);
			break;

			default:
				array_multisort($_points, SORT_DESC, $_pointdiff, SORT_ASC, $this->stats_array[$this->league_id]);
			break;
		}
		
		if ($limit)
		{
			return array_slice($this->stats_array[$this->league_id], $start, $limit);
		}
		else
		{
			return $this->stats_array[$this->league_id];
		}
	}
	
	function get_user_stats($user_id)
	{
		global $db;
		
		if (empty($this->stats_array[$this->league_id]))
		{
			$this->build_leaderboard('points');
		}
		
		foreach($this->stats_array[$this->league_id] AS $key => $lb_data)
		{
			if ($lb_data['user_id'] == $user_id)
			{
				$leaderboard_data = $lb_data;
				break;
			}
		}

		$sql = 'SELECT * FROM ' . SP_GAME_TABLE . ' WHERE league_id = ' . $this->league_id . ' AND game_time < ' . time();
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$completed_games[$row['game_id']] = array(
				'game_time'			=> $row['game_time'],
				'away_id'			=> $row['away_id'],
				'home_id'			=> $row['home_id'],
				'away_team'			=> $this->teams_array[$this->league_id][$row['away_id']]['team_name'],
				'home_team'			=> $this->teams_array[$this->league_id][$row['home_id']]['team_name'],
				'away_score'		=> $row['away_score'],
				'home_score'		=> $row['home_score'],
				'away_prediction'	=> $this->prediction_array['byuser'][$user_id][$row['game_id']]['away_prediction'],
				'home_prediction'	=> $this->prediction_array['byuser'][$user_id][$row['game_id']]['home_prediction'],
				'bonus'				=> $row['bonus']
			);
		}
		$db->sql_freeresult();
		
		$sql = 'SELECT * FROM ' . SP_GAME_TABLE . ' WHERE league_id = ' . $this->league_id . ' AND game_time > ' . time();
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$incomplete_games[$row['game_id']] = array(
				'game_time'			=> $row['game_time'],
				'away_id'			=> $row['away_id'],
				'home_id'			=> $row['home_id'],
				'away_team'			=> $this->teams_array[$this->league_id][$row['away_id']]['team_name'],
				'home_team'			=> $this->teams_array[$this->league_id][$row['home_id']]['team_name'],
				'away_prediction'	=> $this->prediction_array['byuser'][$user_id][$row['game_id']]['away_prediction'],
				'home_prediction'	=> $this->prediction_array['byuser'][$user_id][$row['game_id']]['home_prediction'],
				'bonus'				=> $row['bonus']
			);
		}
		$db->sql_freeresult();
		
		$return_array = array(
			'leaderboard_data'	=> $leaderboard_data,
			'completed_games'	=> $completed_games,
			'incomplete_games'	=> $incomplete_games
		);
		
		return $return_array;
	}
	
/**
* Get imagetypes supported
*
* @return array		array containing imagetypes supported, possibles are GIF, JPG, PNG, & WBMP
*/
	
	function getsupportedimagetypes()
	{
		$supported_types = array();
		$possibles = array(
			IMG_GIF=>'GIF',
			IMG_JPG=>'JPG',
			IMG_PNG=>'PNG',
			IMG_WBMP=>'WBMP'
		);

		foreach ($possibles as $iImageTypeBits => $sImageTypeString)
		{
			if (imagetypes() & $iImageTypeBits)
			{
				$supported_types[$sImageTypeString] = true;
			}
			else
			{
				$supported_types[$sImageTypeString] = false;
			}
		}

		return $supported_types;
	}
	
/**
* Handles uploading logo to the server and naming it properly
*
* @param string	$form_name		name of input field to upload
* @param array 	$prefix			prefix for the file, should be L_$id for league and T_$id for team
*
* @return array					array( [logo] => 'path_to_logo' , [logo_thumb] => 'path_to_logo_thumbnail' )
*/
	
	function upload_logo($form_name, $prefix)
	{
		global $phpbb_root_path, $phpEx;
		
		include_once($phpbb_root_path . 'includes/functions_upload.' . $phpEx);
		$upload = new fileupload('SP_', array('jpg', 'jpeg', 'gif', 'png'), false, 0, 0, $this->config['logo_max_width'], $this->config['logo_max_height']);
		if ($upload->is_valid($form_name))
		{
			$file = $upload->form_upload($form_name);
			$file->clean_filename('real', $prefix);
			$file->move_file($this->config['logo_path'], true);
			@chmod($phpbb_root_path . $this->config['logo_path'] . '/' . $file->realname, 0644);
			if (sizeof($file->error))
			{
				$file->remove();
				var_dump($file->error);
				exit;
				trigger_error(implode('<br />', $file->error), E_USER_WARNING);
			}
			else
			{
				$return_ary['logo'] = $file->realname;

				$thumbnail = $this->create_thumbnail($file->realname);
				if ($thumbnail)
				{
					$return_ary['logo_thumb'] = $thumbnail;
				}
				else
				{
					$return_ary['logo_thumb'] = $file->realname;
				}
				
				return $return_ary;
			}
		}
		else
		{
			return false;
		}
	}
	
/**
* Creates thumbnail
*
* @param string	$source		path to image to create thumbnail from
*
* @return array					Game data
*/
	
	function create_thumbnail($source)
	{
		global $phpbb_root_path;
		
		$logo_path = $phpbb_root_path . $this->config['logo_path'] . '/';
		
		if (!file_exists($logo_path . $source))
		{
			return false;
		}
		
		$image_info = getimagesize($logo_path . $source);
		
		if (!$image_info)
		{
			return false;
		}
		
		list($src_width, $src_height, $src_type) = $image_info;
		
		if (empty($src_width) || empty($src_height))
		{
			return false;
		}
		
		if ($src_width > $this->config['logo_max_thumbnail_width'] || $src_height > $this->config['logo_max_thumbnail_height'])
		{
			if (@extension_loaded('gd'))
			{								
				$ratio = (int) $src_width / (int) $src_height;
				if ($ratio < 1)
				{
					$dest_width = $this->config['logo_max_thumbnail_height'] * $ratio;
					$dest_height = $this->config['logo_max_thumbnail_height'];
				}
				else
				{
					$dest_width = $this->config['logo_max_thumbnail_width'];
					$dest_height = $this->config['logo_max_thumbnail_width'] / $ratio;
				}
				
				$_filename_ary = explode('.', $source);
				$_filename = $_filename_ary[0];
				$_ext = $_filename_ary[1];
				$thumbnail_filename = $_filename . '_thumb.' . $_ext;
				
				$img_types_supported = $this->getsupportedimagetypes();
				switch($src_type)
				{
					case IMAGETYPE_JPEG:
						if ($img_types_supported['JPG'])
						{
							$_thumbnail_src = imagecreatefromjpeg($logo_path . $source);
						}
					break;
					
					case IMAGETYPE_GIF:
						if ($img_types_supported['GIF'])
						{
							$_thumbnail_src = imagecreatefromgif($logo_path . $source);
						}
					break;
					
					case IMAGETYPE_PNG:
						if ($img_types_supported['PNG'])
						{
							$_thumbnail_src = imagecreatefrompng($logo_path . $source);
						}
					break;
				}
				
				if ($_thumbnail_src)
				{
					$_thumbnail = imagecreatetruecolor($dest_width, $dest_height);
					@imagealphablending($_thumbnail, false);
					@imagesavealpha($_thumbnail, true);
					imagecopyresampled($_thumbnail, $_thumbnail_src, 0, 0, 0, 0, $dest_width, $dest_height, $src_width, $src_height);
									
					switch ($src_type)
					{
						case IMAGETYPE_JPEG:
							imagejpeg($_thumbnail, $logo_path . $thumbnail_filename, 90);
						break;

						case IMAGETYPE_GIF:
							imagegif($_thumbnail, $logo_path . $thumbnail_filename);
						break;

						case IMAGETYPE_PNG:
							imagepng($_thumbnail, $logo_path . $thumbnail_filename);
						break;
					}
					imagedestroy($_thumbnail);
									
					@chmod($logo_path . $thumbnail_filename, 0644);
					
					return $thumbnail_filename;
				}
			}
		}
		else
		{
			return $source;
		}
		
		return false;
	}
	
/**
* Remove logo from the filesystem
*
* @param string	$category	'league' or 'team'
* @param int 	$id			league_id or team_id
* @param bool 	$update_db	currently not used
*
* @return NULL
*/
	
	function remove_logo($category, $id, $update_db = false)
	{
		global $db, $phpbb_root_path, $phpEx;
		
		switch ($category)
		{
			case 'league':

				if (isset($this->leagues_array[$id]))
				{
					if (!empty($this->leagues_array[$id]['league_logo']))
					{
						if (file_exists($phpbb_root_path . $this->config['logo_path'] . '/' . $this->leagues_array[$id]['league_logo']))
						{
							@unlink($phpbb_root_path . $this->config['logo_path'] . '/' . $this->leagues_array[$id]['league_logo']);
						}
					}
						
					if (!empty($this->leagues_array[$id]['league_logo_tn']))
					{
						if (file_exists($phpbb_root_path . $this->config['logo_path'] . '/' . $this->leagues_array[$id]['league_logo_tn']))
						{
							@unlink($phpbb_root_path . $this->config['logo_path'] . '/' . $this->leagues_array[$id]['league_logo_tn']);
						}
					}
					
					if ($update_db)
					{
						$sql_ary = array(
							'league_logo'		=> NULL,
							'league_logo_tn'	=> NULL
						);
						
						$sql = 'UPDATE ' . SP_LEAGUE_TABLE . ' ' . $db->sql_build_array('UPDATE', $sql_ary) . ' WHERE league_id = $id';
						$db->sql_query($sql);
					}
				}
				
			break;
			
			case 'team':

				if (isset($this->teams_array['id_ref'][$id]))
				{
					if (!empty($this->teams_array['id_ref'][$id]['team_logo']))
					{
						if (file_exists($phpbb_root_path . $this->config['logo_path'] . '/' . $this->teams_array['id_ref'][$id]['team_logo']))
						{
							@unlink($phpbb_root_path . $this->config['logo_path'] . '/' . $this->teams_array['id_ref'][$id]['team_logo']);
						}
					}
						
					if (!empty($this->teams_array['id_ref'][$id]['team_logo_tn']))
					{
						if (file_exists($phpbb_root_path . $this->config['logo_path'] . '/' . $this->teams_array['id_ref'][$id]['team_logo_tn']))
						{
							@unlink($phpbb_root_path . $this->config['logo_path'] . '/' . $this->teams_array['id_ref'][$id]['team_logo_tn']);
						}
					}
					
					if ($update_db)
					{
						$sql_ary = array(
							'team_logo'		=> NULL,
							'team_logo_tn'	=> NULL
						);
						
						$sql = 'UPDATE ' . SP_TEAM_TABLE . ' ' . $db->sql_build_array('UPDATE', $sql_ary) . ' WHERE team_id = $id';
						$db->sql_query($sql);
					}
				}
				
			break;
		}
	}
	
	function regenerate_thumbnails()
	{
		global $db, $cache;
	
		foreach ($this->leagues_array AS $league_id => $league_data)
		{
			//remove existing thumbnail
			if (file_exists($phpbb_root_path . $this->config['logo_path'] . '/' . $league_data['league_logo_tn']))
			{
				@unlink($phpbb_root_path . $this->config['logo_path'] . '/' . $league_data['league_logo_tn']);
			}
			
			$thumbnail = $this->create_thumbnail($league_data['league_logo']);
			
			$sql = 'UPDATE ' . SP_LEAGUE_TABLE . " SET league_logo_tn = '$thumbnail' WHERE league_id = $league_id";
			$db->sql_query($sql);
		}
		
		foreach ($this->teams_array['id_ref'] AS $team_id => $team_data)
		{
			//remove existing thumbnail
			if (file_exists($phpbb_root_path . $this->config['logo_path'] . '/' . $team_data['team_logo_tn']))
			{
				@unlink($phpbb_root_path . $this->config['logo_path'] . '/' . $team_data['team_logo_tn']);
			}

			$thumbnail = $this->create_thumbnail($team_data['team_logo']);
			
			$sql = 'UPDATE ' . SP_TEAM_TABLE . " SET team_logo_tn = '$thumbnail' WHERE team_id = $team_id";
			$db->sql_query($sql);
		}
		
		$cache->destroy('sql', array(SP_LEAGUE_TABLE, SP_TEAM_TABLE));
		
		return;
	}
	
	function set_league_id($league_id)
	{
		if ($this->leagues_array[$league_id]['active'] == 1 || $this->in_admin)
		{
			$this->league_id = $league_id;
		}
	}
	
	function get_league_id()
	{
		return $this->league_id;
	}
	
	function get_scoring_style($league_id = false)
	{
		if (!$league_id)
		{
			return $this->leagues_array[$this->league_id]['scoring_style'];
		}
		else
		{
			return $this->leagues_array[$league_id]['scoring_style'];
		}
	}
	
	function change_user_default_league($league_id)
	{
		global $db, $user;
		
		if ($this->leagues_array[$league_id]['active'] == 1)
		{
			$sql_ary['user_sp_default_league'] = $league_id;
			$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . ' WHERE user_id = ' . $user->data['user_id'];
			$db->sql_query($sql);
		
			return true;
		}
		else
		{
			return false;
		}
	}
	
	function build_gametime_options($default_time)
	{
		global $user;
		
		$month_opts = $day_opts = $year_opts = $hours_opts = $minute_opts = $ampm_opts = '';
		
		$timezone_offset = 3600 * (float) $user->data['user_timezone'];
		$default_time_ary = array(
			'mon'		=> gmdate('n', ($default_time + $timezone_offset)),
			'mday'		=> gmdate('j', ($default_time + $timezone_offset)),
			'year'		=> gmdate('Y', ($default_time + $timezone_offset)),
			'hours'		=> gmdate('G', ($default_time + $timezone_offset)),
			'minutes'	=> gmdate('i', ($default_time + $timezone_offset))
		);

		$month_opts = '<option value="0">--</option>';
		for ($i = 1; $i < 13; $i++)
		{
			$selected = ($i == $default_time_ary['mon']) ? ' selected="selected"' : '';
			$month_opts .= "<option value=\"$i\"$selected>$i</option>";
		}

		$day_opts = '<option value="0">--</option>';
		for ($i = 1; $i < 32; $i++)
		{
			$selected = ($i == $default_time_ary['mday']) ? ' selected="selected"' : '';
			$day_opts .= "<option value=\"$i\"$selected>$i</option>";
		}

		$year_opts = '<option value="0">--</option>';
		for ($i = $default_time_ary['year'] - 10; $i <= ($default_time_ary['year'] + 2); $i++)
		{
			$selected = ($i == $default_time_ary['year']) ? ' selected="selected"' : '';
			$year_opts .= "<option value=\"$i\"$selected>$i</option>";
		}

		$hour_opts = '<option value="0">--</option>';
		for ($i = 1; $i < 13; $i++)
		{
			$selected_hour = ($default_time_ary['hours'] == 0) ? 12 : (($default_time_ary['hours'] > 12) ? $default_time_ary['hours'] - 12 : $default_time_ary['hours']);
			$selected = ($i == $selected_hour) ? ' selected="selected"' : '';
			$hour_opts .= "<option value=\"$i\"$selected>$i</option>";
		}

		$minute_opts = '<option value="99">--</option>';
		for ($i = 0; $i < 60; $i++)
		{
			$selected = ($i == $default_time_ary['minutes']) ? ' selected="selected"' : '';
			$minute_opts .= "<option value=\"$i\"$selected>".str_pad((string) $i, 2, "0", STR_PAD_LEFT)."</option>";
		}
		
		$ampm_opts = ($default_time_ary['hours'] > 12) ? '<option value="0">--</option><option value="am">AM</option><option value="pm" selected="selected">PM</option>' : '<option value="0">--</option><option value="am" selected="selected">AM</option><option value="pm">PM</option>';
		
		return array(
			'month'		=> $month_opts,
			'day'		=> $day_opts,
			'year'		=> $year_opts,
			'hour'		=> $hour_opts,
			'minute'	=> $minute_opts,
			'ampm'		=> $ampm_opts
		);
	}
	
	function build_league_options($selected = false)
	{
		if ($selected == false)
		{
			$selected = $this->league_id;
		}
		
		$return_var = '';
		foreach($this->leagues_array AS $league_id => $league_data)
		{
			if ($league_data['active'] == 1 || $this->in_admin)
			{
				$return_var .= '<option value="' . $league_id . '"' . (($selected == $league_id) ? ' selected="selected"' : '') . '>' . $league_data['league_name'] . '</option>';
			}
		}
		
		return $return_var;
	}
	
	function build_team_options($league_id, $default_team = false)
	{
		$team_options = '<option value="0">--</option>';
		foreach ($this->teams_array[$league_id] AS $team_id => $team_data)
		{
			if ($default_team)
			{
				$selected = ($team_id == $default_team) ? ' selected="selected"' : '';
			}
			$team_options .= '<option value="' . $team_id . '"' . $selected . '>' . $team_data['team_name'] . '</option>';
		}
		
		return $team_options;
	}
	
	function build_round_options($league_id, $default_round = false)
	{
		global $db;
		
		$sql = 'SELECT MAX(round) AS max_round FROM ' . SP_GAME_TABLE . ' WHERE league_id = ' . $league_id;
		$result = $db->sql_query($sql);
		$max_round = (int) $db->sql_fetchfield('max_round');
		$db->sql_freeresult($result);

		for($i = 1; $i <= ($max_round + 1); $i++)
		{
			if ($default_round)
			{
				$selected = ($i == $default_round) ? ' selected="selected"' : '';
			}
			else
			{
				$selected = ($i == $max_round) ? ' selected="selected"' : '';
			}
			
			$round_options .= '<option value="' . $i . '"' . $selected . '>' . $i . '</option>';
		}
		
		return $round_options;
	}
	
	function reminder_subscribe($user_id, $unsubscribe = false)
	{
		global $db;
		
		$sql = 'UPDATE ' . USERS_TABLE . ' SET user_sp_reminder = ' . (($unsubscribe) ? 0 : 1) . ' WHERE user_id = ' . $user_id;
		$db->sql_query($sql);
	}
	
	function send_reminders()
	{
		global $db, $user, $config;
		
		$sql = 'SELECT user_id FROM ' . USERS_TABLE . ' WHERE user_sp_reminder = 1 AND user_sp_last_reminder < ' . (time() - 86400);
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			unset($link_array);
			
			$games_predicted_ary = array_keys((array) $this->prediction_array['byuser'][$row['user_id']]);

			$sql = 'SELECT game_id, game_time, away_id, home_id 
					FROM ' . SP_GAME_TABLE . ' 
					WHERE game_time > ' . time() . ' AND game_time < ' . (time() + 259200) . 
					((sizeof($games_predicted_ary)) ? ' AND ' . $db->sql_in_set('game_id', $games_predicted_ary, true) : '') . ' 
					ORDER BY game_time';
			$result2 = $db->sql_query($sql);
			while ($row2 = $db->sql_fetchrow($result2))
			{
				$link_array[] = $user->format_date($row2['game_time']) . ' -- ' . $this->teams_array['id_ref'][$row2['home_id']]['team_name'] . ' vs ' . $this->teams_array['id_ref'][$row2['away_id']]['team_name'];
			}
			$db->sql_freeresult($result);
			
			if (!empty($link_array))
			{
				$links = implode("\n", $link_array);
				
				// note that multibyte support is enabled here 
				$subject = 'Footy Tipping Reminder';
				$text    = sprintf("These games will be played soon and you have not predicted them yet.  Please click on the 'Footy Tipping' link above to predict these games.\n\n%s", $links);

				// variables to hold the parameters for submit_pm
				$poll = $uid = $bitfield = $options = ''; 
				generate_text_for_storage($subject, $uid, $bitfield, $options, false, false, false);
				generate_text_for_storage($text, $uid, $bitfield, $options, true, true, true);

				$data = array( 
					'address_list'      => array ('u' => array($row['user_id'] => 'to')),
					'from_user_id'      => 2,
					'from_username'     => 'Beaussie',
					'icon_id'           => 0,
					'from_user_ip'      => $user->data['user_ip'],
					 
					'enable_bbcode'     => true,
					'enable_smilies'    => true,
					'enable_urls'       => true,
					'enable_sig'        => true,

					'message'           => $text,
					'bbcode_bitfield'   => $bitfield,
					'bbcode_uid'        => $uid,
				);

				submit_pm('post', $subject, $data, false);
				
				$sql3 = 'UPDATE ' . USERS_TABLE . ' SET user_sp_last_reminder = ' . time() . ' WHERE user_id = ' . $row['user_id'];
				$db->sql_query($sql3);
			}
		}
		
		set_config('sportspredictions_last_gc', time(), true);
	}
	
	function get_incomplete_rounds($league_id)
	{
		global $db;
		
		$sql = 'SELECT round FROM ' . SP_GAME_TABLE . ' WHERE league_id = ' . $league_id . ' AND away_score IS NULL AND home_score IS NULL GROUP BY round';
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$return_ary[] = $row['round'];
		}
		
		return $return_ary;
	}
	
	function send_round_results($league_id, $round)
	{
		global $db, $cache, $phpbb_root_path, $phpEx;

		$sql_ary = array(
			'SELECT'	=> 'u.user_id, u.username, u.user_email',
			'FROM'		=> array(SP_GAME_TABLE => 'g'),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(SP_PREDICTION_TABLE => 'p'),
					'ON'	=> 'g.game_id = p.game_id'
				),
				array(
					'FROM'	=> array(USERS_TABLE => 'u'),
					'ON'	=> 'p.user_id = u.user_id'
				)
			),
			'WHERE'		=> 'g.league_id = ' . $this->league_id,
			'ORDER_BY'	=> 'u.user_id'
		);
		$sql = $db->sql_build_query('SELECT_DISTINCT', $sql_ary);
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$users_ary[$row['user_id']] = array(
				'username'		=> $row['username'],
				'user_email'	=> $row['user_email']
			);
		}
		
		$cache->destroy('sql', SP_GAME_TABLE);
		$cache->destroy('_sp_stats_array_' . $league_id);
		$this->build_leaderboard();
		$weekly_leaderboard = $this->stats_by_round[$league_id]['individual'][$round];
		$leaderboard_to_date = $this->stats_by_round[$league_id]['cumulative'][$round];
		
		foreach($weekly_leaderboard AS $user_id => $stat_data) {
			$stats_array[] = array(
				'user_id'	=> $user_id,
				'username'	=> $this->username_array[$user_id],
				'wins'		=> ((isset($stat_data['wins'])) ? $stat_data['wins'] : 0),
				'losses'	=> ((isset($stat_data['losses'])) ? $stat_data['losses'] : 0),
				'pointdiff'	=> ((isset($stat_data['pointdiff'])) ? $stat_data['pointdiff'] : 0),
				'points'	=> ((isset($stat_data['points'])) ? $stat_data['points'] : 0),
				'winperc'	=> (($stat_data['wins'] + $stat_data['losses'] != 0) ? round(($stat_data['wins']/($stat_data['wins'] + $stat_data['losses'])) * 100, 1) : 0)
			);
		}
		
		foreach ( $stats_array as $key => $row )
		{
			$_user_id[$key]		= $row['user_id'];
			$_username[$key]	= strtolower($row['username']);
			$_wins[$key]		= $row['wins'];
			$_losses[$key]		= $row['losses'];
			$_winperc[$key]		= $row['winperc'];
			$_pointdiff[$key]	= $row['pointdiff'];
			$_points[$key]		= $row['points'];
		}

		array_multisort($_points, SORT_DESC, $_pointdiff, SORT_ASC, $stats_array);
		foreach($stats_array AS $rank => $data)
		{
			$stats_array[$rank]['rank'] = $rank + 1;
		}
		$stats_array = array_slice($stats_array, 0, 10);
		
		$round_results_html = '<table width="100%">';
		$round_results_html .= '<tr>';
		$round_results_html .= '<th style="background-color: #333333; color: #ffffff;">Rank</th>';
		$round_results_html .= '<th style="background-color: #333333; color: #ffffff;">Username</th>';
		$round_results_html .= '<th style="background-color: #333333; color: #ffffff;">Wins</th>';
		$round_results_html .= '<th style="background-color: #333333; color: #ffffff;">Losses</th>';
		$round_results_html .= '<th style="background-color: #333333; color: #ffffff;">Win Perc</th>';
		$round_results_html .= '<th style="background-color: #333333; color: #ffffff;">Points</th>';
		$round_results_html .= '</tr>';
		
		$i=0;
		foreach($stats_array AS $_data)
		{
			$round_results_html .= '<tr style="background-color: ' . (($i % 2) ? '#cfcfcf;' : '#fcfcfc') . '">';
			$round_results_html .= '<td>' . $_data['rank'] . '</td>';
			$round_results_html .= '<td>' . $_data['username'] . '</td>';
			$round_results_html .= '<td>' . $_data['wins'] . '</td>';
			$round_results_html .= '<td>' . $_data['losses'] . '</td>';
			$round_results_html .= '<td>' . $_data['winperc'] . '</td>';
			$round_results_html .= '<td>' . $_data['points'] . '</td>';
			$round_results_html .= '</tr>';
			$i++;
		}
		$round_results_html .= '</table>';
		
		unset($stats_array);
		foreach($leaderboard_to_date AS $user_id => $stat_data) {
			$stats_array[] = array(
				'user_id'	=> $user_id,
				'username'	=> $this->username_array[$user_id],
				'wins'		=> ((isset($stat_data['wins'])) ? $stat_data['wins'] : 0),
				'losses'	=> ((isset($stat_data['losses'])) ? $stat_data['losses'] : 0),
				'pointdiff'	=> ((isset($stat_data['pointdiff'])) ? $stat_data['pointdiff'] : 0),
				'points'	=> ((isset($stat_data['points'])) ? $stat_data['points'] : 0),
				'winperc'	=> (($stat_data['wins'] + $stat_data['losses'] != 0) ? round(($stat_data['wins']/($stat_data['wins'] + $stat_data['losses'])) * 100, 1) : 0)
			);
		}
		
		foreach ( $stats_array as $key => $row )
		{
			$_user_id[$key]		= $row['user_id'];
			$_username[$key]	= strtolower($row['username']);
			$_wins[$key]		= $row['wins'];
			$_losses[$key]		= $row['losses'];
			$_winperc[$key]		= $row['winperc'];
			$_pointdiff[$key]	= $row['pointdiff'];
			$_points[$key]		= $row['points'];
		}

		array_multisort($_points, SORT_DESC, $_pointdiff, SORT_ASC, $stats_array);
		foreach($stats_array AS $rank => $data)
		{
			$stats_array[$rank]['rank'] = $rank + 1;
		}
		$stats_array = array_slice($stats_array, 0, 10);
		
		$lb_to_date_html = '<table width="100%">';
		$lb_to_date_html .= '<tr>';
		$lb_to_date_html .= '<th style="background-color: #333333; color: #ffffff;">Rank</th>';
		$lb_to_date_html .= '<th style="background-color: #333333; color: #ffffff;">Username</th>';
		$lb_to_date_html .= '<th style="background-color: #333333; color: #ffffff;">Wins</th>';
		$lb_to_date_html .= '<th style="background-color: #333333; color: #ffffff;">Losses</th>';
		$lb_to_date_html .= '<th style="background-color: #333333; color: #ffffff;">Win Perc</th>';
		$lb_to_date_html .= '<th style="background-color: #333333; color: #ffffff;">Points</th>';
		$lb_to_date_html .= '</tr>';
		
		$i=0;
		foreach($stats_array AS $_data)
		{
			$lb_to_date_html .= '<tr style="background-color: ' . (($i % 2) ? '#cfcfcf;' : '#fcfcfc') . '">';
			$lb_to_date_html .= '<td>' . $_data['rank'] . '</td>';
			$lb_to_date_html .= '<td>' . $_data['username'] . '</td>';
			$lb_to_date_html .= '<td>' . $_data['wins'] . '</td>';
			$lb_to_date_html .= '<td>' . $_data['losses'] . '</td>';
			$lb_to_date_html .= '<td>' . $_data['winperc'] . '</td>';
			$lb_to_date_html .= '<td>' . $_data['points'] . '</td>';
			$lb_to_date_html .= '</tr>';
			$i++;
		}
		$lb_to_date_html .= '</table>';
		
		include_once($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);
		$messenger = new messenger(false);
		$messenger->template('sp_round_summary', 'en');
		$messenger->headers('Content-Type: text/html');
		foreach ($users_ary AS $_uid => $user_data)
		{
			$messenger->bcc($user_data['user_email'], $user_data['username']);
		}
		$messenger->assign_vars(array(
			'ROUND_RESULTS'				=> $round_results_html,
			'LEADERBOARD_TO_DATE'		=> $lb_to_date_html,
			'U_SPORTS_PREDICTION_LINK'	=> generate_board_url() . "/sportspredictions.$phpEx")
		);
		$messenger->send();
	}
}

?>