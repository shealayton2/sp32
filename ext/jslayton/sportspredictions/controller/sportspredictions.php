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
*/

/**
* @ignore
*/

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/sports_predictions');

include($phpbb_root_path . "includes/sportspredictions/class_sportspredictions.$phpEx");
$sportspredictions = new sportspredictions();

/*
if (!$user->data['is_registered'])
{
	login_box('', $user->lang['SP_LOGIN_REQUIRED']);
}
*/

//
// Set basic vars
//
$error				= array();
$action				= request_var('action', '');
$mode				= request_var('mode', 'index');
$u_action			= append_sid("{$phpbb_root_path}sportspredictions.$phpEx");
$s_hidden_fields 	= '';

if (isset($_POST['change_league']))
{
	$league_id = request_var('league_id', 0);
	if ($league_id)
	{
		$sportspredictions->set_league_id($league_id);
		$sportspredictions->change_user_default_league($league_id);
	}
}

switch ($mode)
{
	case 'index':
	
		$page_header	= $user->lang['SPORTS_PREDICTIONS'];
		$title			= $user->lang['SPORTS_PREDICTIONS'];
		$page_body		= 'sportspredictions/main.html';
		
		$sort = request_var('sort', '');
		$start = request_var('start', 0);
		
		if ($mode && $mode != 'index') {
			$params[] = "mode=$mode";
		}
		
		if ($sort) {
			$params[] = "sort=$sort";
		}

		$pagination_url = (sizeof($params) > 0) ? append_sid("{$phpbb_root_path}sportspredictions.$phpEx", implode('&amp;', $params)) : append_sid("{$phpbb_root_path}sportspredictions.$phpEx");
		
		$template->assign_vars(array(
			'PAGE_TITLE'					=> $title,
			'EXACT_PREDICTION_EXPLAIN'		=> sprintf($user->lang['EXACT_PREDICTION_EXPLAIN'], $sportspredictions->config['exact_prediction_points']),
			'CORRECT_PREDICTION_EXPLAIN'	=> sprintf($user->lang['CORRECT_PREDICTION_EXPLAIN'], $sportspredictions->config['correct_prediction_points']),
			'INCORRECT_PREDICTION_EXPLAIN'	=> sprintf($user->lang['INCORRECT_PREDICTION_EXPLAIN'], $sportspredictions->config['incorrect_prediction_points']),
			'DRAW_PREDICTION_EXPLAIN'		=> sprintf($user->lang['DRAW_PREDICTION_EXPLAIN'], $sportspredictions->config['draw_predictions_points']),
			'PERFECT_ROUND_BONUS_EXPLAIN'	=> sprintf($user->lang['PERFECT_ROUND_BONUS_EXPLAIN'], $sportspredictions->leagues_array[$sportspredictions->get_league_id()]['perfect_round_bonus_points']),
			'LEAGUE_NAME'					=> $sportspredictions->leagues_array[$sportspredictions->get_league_id()]['league_name'],
			'LOGO_CONTAINER_HEIGHT'			=> $sportspredictions->config['logo_max_thumbnail_height'] * 1.5,

			'S_REMINDER_STATUS'		=> $user->data['user_sp_reminder'],

			'U_FULL_LEADERBOARD'	=> $u_action . '?mode=full_leaderboard',
			'U_SORT_USERNAME'		=> $u_action . '?sort=username',
			'U_SORT_WINS'			=> $u_action . '?sort=wins',
			'U_SORT_LOSSES'			=> $u_action . '?sort=losses',
			'U_SORT_POINTDIFF'		=> $u_action . '?sort=pointdiff',
			'U_SORT_WINPERC'		=> $u_action . '?sort=winperc',
			'U_SORT_POINTS'			=> $u_action,
			'U_PREDICT' 			=> $u_action . '?mode=predict',
			'U_REMINDER_SUBSCRIBE'	=> $u_action . '?mode=reminder&amp;req=' . (($user->data['user_sp_reminder'] == 1) ? 'unsubscribe' : 'subscribe'))
		);

		$stats_array = $sportspredictions->build_leaderboard($sort, $sportspredictions->config['leaderboard_limit'], $start);

		foreach($stats_array AS $lb_data)
		{
			$template->assign_block_vars('leaderboard', array(
				'POSITION'		=> $lb_data['rank'],
				'USER_ID'		=> $lb_data['user_id'],
				'USERNAME'		=> $lb_data['username'],
				'STATS_LINK'	=> $u_action . '?mode=user_stats&amp;user_id=' . $lb_data['user_id'],
				'HIGHLIGHT'		=> (($lb_data['user_id'] == $user->data['user_id']) ? true : false),
				'WINS'			=> $lb_data['wins'],
				'LOSSES'		=> $lb_data['losses'],
				'POINTDIFF'		=> $lb_data['pointdiff'],
				'WINPERC'		=> $lb_data['winperc'],
				'POINTS'		=> $lb_data['points']
			));
		}
		
		if (sizeof($sportspredictions->stats_array) > $sportspredictions->config['leaderboard_limit']) {
			$template->assign_vars(array(
				'PAGINATION'        => generate_pagination($pagination_url, sizeof($sportspredictions->stats_array), $sportspredictions->config['leaderboard_limit'], $start),
				'PAGE_NUMBER'       => on_page(sizeof($sportspredictions->stats_array), $sportspredictions->config['leaderboard_limit'], $start)
			));
		}
		
		$user_predictions = $sportspredictions->get_user_predictions($user->data['user_id']);
		
		$upcoming_games = $sportspredictions->get_upcoming_games($sportspredictions->config['upcoming_games_limit']);
		foreach ((array) $upcoming_games AS $game_id => $game_data)
		{
			$away_prediction = (isset($user_predictions[$game_id])) ? $user_predictions[$game_id]['away_prediction'] : '';
			$home_prediction = (isset($user_predictions[$game_id])) ? $user_predictions[$game_id]['home_prediction'] : '';
			
			if ($away_prediction != '' && $home_prediction != '')
			{
				if ($away_prediction == $home_prediction)
				{
					$user_prediction = $user->lang['DRAW'];
				}
				else
				{
					$user_prediction = (($away_prediction > $home_prediction) ? $game_data['away_team'] : (($away_prediction < $home_prediction) ? $game_data['home_team'] : '')) . ' ' . $user->lang['WINS'];
				}
			}
			else
			{
				$user_prediction = '';
			}
			
			$template->assign_block_vars('upcoming_games', array(
				'GAME_ID'			=> $game_id,
				'GAME_TIME'			=> $user->format_date($game_data['game_time']),
				'AWAY_TEAM'			=> $game_data['away_team'],
				'HOME_TEAM'			=> $game_data['home_team'],
				'AWAY_LOGO_TN'		=> ((!empty($sportspredictions->teams_array['id_ref'][$game_data['away_id']]['team_logo_tn'])) ? $phpbb_root_path . $sportspredictions->config['logo_path'] . '/' . $sportspredictions->teams_array['id_ref'][$game_data['away_id']]['team_logo_tn'] : ''),
				'HOME_LOGO_TN'		=> ((!empty($sportspredictions->teams_array['id_ref'][$game_data['home_id']]['team_logo_tn'])) ? $phpbb_root_path . $sportspredictions->config['logo_path'] . '/' . $sportspredictions->teams_array['id_ref'][$game_data['home_id']]['team_logo_tn'] : ''),
				'AWAY_RECORD'		=> $sportspredictions->get_team_record($game_data['away_id']),
				'HOME_RECORD'		=> $sportspredictions->get_team_record($game_data['home_id']),
				'BONUS'				=> $game_data['bonus'],
				'AWAY_PREDICTION'	=> $away_prediction,
				'HOME_PREDICTION'	=> $home_prediction,
				'USER_PREDICTION'	=> $user_prediction
			));
		}

	break;
	
	case 'predict':
	case 'edit_predictions':
	
		if (!$user->data['is_registered'])
		{
			login_box('', $user->lang['SP_LOGIN_REQUIRED']);
		}
	
		$page_header	= $user->lang['SPORTS_PREDICTIONS'] . ' : ' . $user->lang['PREDICT'];
		$title			= $user->lang['SPORTS_PREDICTIONS'] . ' : ' . $user->lang['PREDICT'];
		$page_body		= 'sportspredictions/predict.html';
		add_form_key('predict');
		
		if ($action == 'save')
		{
			if ($sportspredictions->get_scoring_style() == 'score')
			{
				$away_prediction = request_var('away_prediction', array(''));
				$home_prediction = request_var('home_prediction', array(''));
			}
			else
			{
				$game_prediction = request_var('game_prediction', array(''));
				
				foreach($game_prediction AS $game_id => $_tmp_prediction)
				{
					switch($_tmp_prediction)
					{
						case 'away':
							$away_prediction[$game_id] = 1;
							$home_prediction[$game_id] = 0;
						break;
						case 'home':
							$home_prediction[$game_id] = 1;
							$away_prediction[$game_id] = 0;
						break;
						case 'draw':
							$home_prediction[$game_id] = 1;
							$away_prediction[$game_id] = 1;
						break;
					}
				}
			}
			
			if ($mode == 'edit_predictions')
			{
				$sportspredictions->add_predictions($user->data['user_id'], $away_prediction, $home_prediction, true);
			}
			else
			{
				$sportspredictions->add_predictions($user->data['user_id'], $away_prediction, $home_prediction);
			}
			
			meta_refresh(3, $u_action);
			$message = $user->lang['PREDICTION_UPDATE_SUCCESS'] . '<br /><br />' . sprintf($user->lang['RETURN_SP_INDEX'], '<a href="' . $u_action . '">', '</a>');
			trigger_error($message);
		}
		
		$s_hidden_fields .= '<input type="hidden" name="action" value="save" />';
		
		$template->assign_vars(array(
			'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
			'SCORING_STYLE'		=> $sportspredictions->get_scoring_style(),
			'PAGE_TITLE'		=> $title)
		);

		if ($mode == 'edit_predictions')
		{
			$upcoming_games = $sportspredictions->get_games_to_predict($user->data['user_id'], true);
		}
		else
		{
			$upcoming_games = $sportspredictions->get_games_to_predict($user->data['user_id']);
		}
		if (sizeof($upcoming_games) == 0)
		{
			meta_refresh(3, $u_action);
			$message = $user->lang['NO_GAMES_TO_PREDICT'] . '<br /><br />' . sprintf($user->lang['RETURN_SP_INDEX'], '<a href="' . $u_action . '">', '</a>');
			trigger_error($message);
		}
		foreach ($upcoming_games AS $game_id => $game_data)
		{
			if ($sportspredictions->get_scoring_style() == 'winner')
			{
				if ($mode == 'edit_predictions')
				{
					if ($game_data['away_prediction'] > $game_data['home_prediction'])
					{
						$default_prediction = 'away';
					}
					elseif ($game_data['home_prediction'] > $game_data['away_prediction'])
					{
						$default_prediction = 'home';
					}
					elseif ($game_data['home_prediction'] == $game_data['away_prediction'])
					{
						$default_prediction = 'draw';
					}
				}
				else
				{
					$default_prediction = '';
				}
			}
			else
			{
				$default_prediction = '';
			}

			$template->assign_block_vars('upcoming_games', array(
				'GAME_ID'				=> $game_id,
				'GAME_TIME'				=> $user->format_date($game_data['game_time']),
				'AWAY_TEAM'				=> $game_data['away_team'],
				'HOME_TEAM'				=> $game_data['home_team'],
				'BONUS'					=> (($game_data['bonus'] == 1) ? true : false),
				'AWAY_PREDICTION'		=> ($mode == 'edit_predictions') ? $game_data['away_prediction'] : '',
				'HOME_PREDICTION'		=> ($mode == 'edit_predictions') ? $game_data['home_prediction'] : '',
				'DEFAULT_PREDICTION'	=> $default_prediction
			));
		}

	break;
	
	case 'full_leaderboard':
		
		$page_header	= $user->lang['SPORTS_PREDICTIONS'];
		$title			= $user->lang['SPORTS_PREDICTIONS'];
		$page_body		= 'sportspredictions/full_leaderboard.html';
		
		$sort = request_var('sort', 'points');
		
		$template->assign_vars(array(
			'PAGE_TITLE'					=> $title,
			'EXACT_PREDICTION_EXPLAIN'		=> sprintf($user->lang['EXACT_PREDICTION_EXPLAIN'], $sportspredictions->config['exact_prediction_points']),
			'CORRECT_PREDICTION_EXPLAIN'	=> sprintf($user->lang['CORRECT_PREDICTION_EXPLAIN'], $sportspredictions->config['correct_prediction_points']),
			'INCORRECT_PREDICTION_EXPLAIN'	=> sprintf($user->lang['INCORRECT_PREDICTION_EXPLAIN'], $sportspredictions->config['incorrect_prediction_points']),
			'LEAGUE_NAME'					=> $sportspredictions->config['league_name'],

			'U_SORT_USERNAME'		=> $u_action . '?mode=' . $mode . '&sort=username',
			'U_SORT_WINS'			=> $u_action . '?mode=' . $mode . '&sort=wins',
			'U_SORT_LOSSES'			=> $u_action . '?mode=' . $mode . '&sort=losses',
			'U_SORT_POINTDIFF'		=> $u_action . '?mode=' . $mode . '&sort=pointdiff',
			'U_SORT_WINPERC'		=> $u_action . '?mode=' . $mode . '&sort=winperc',
			'U_SORT_POINTS'			=> $u_action . '?mode=' . $mode)
		);

		$stats_array = $sportspredictions->build_leaderboard($sort);
		foreach($stats_array AS $lb_data)
		{
			$template->assign_block_vars('leaderboard', array(
				'POSITION'		=> $lb_data['rank'],
				'USER_ID'		=> $lb_data['user_id'],
				'USERNAME'		=> $lb_data['username'],
				'STATS_LINK'	=> $u_action . '?mode=user_stats&amp;user_id=' . $lb_data['user_id'],
				'HIGHLIGHT'		=> (($lb_data['user_id'] == $user->data['user_id']) ? true : false),
				'WINS'			=> $lb_data['wins'],
				'LOSSES'		=> $lb_data['losses'],
				'POINTDIFF'		=> $lb_data['pointdiff'],
				'WINPERC'		=> $lb_data['winperc'],
				'POINTS'		=> $lb_data['points']
			));
		}
	
	break;
	
	case 'user_stats':
	
		$user_id = request_var('user_id', 0);
	
		$page_header	= $user->lang['SPORTS_PREDICTIONS'] . ' : ' . sprintf($user->lang['PREDICTION_STATS'], $sportspredictions->username_array[$user_id]);
		$title			= $user->lang['SPORTS_PREDICTIONS'] . ' : ' . sprintf($user->lang['PREDICTION_STATS'], $sportspredictions->username_array[$user_id]);
		$page_body		= 'sportspredictions/user_stats.html';
		
		$template->assign_vars(array(
			'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
			'PAGE_TITLE'		=> $title)
		);
		
		$sportspredictions->build_leaderboard('points');
		$user_stats = $sportspredictions->get_user_stats($user_id);
		
		//echo '<pre>';
		//print_r($user_stats);
		//echo '</pre>';
		
		foreach((array) $user_stats['incomplete_games'] AS $_game_id => $_game_data)
		{
			if ($sportspredictions->get_scoring_style() == 'winner')
			{
				if ($_game_data['away_prediction'] > $_game_data['home_prediction'])
				{
					$user_prediction = $_game_data['away_team'] . ' ' . $user->lang['WINS'];
				}
				elseif ($_game_data['home_prediction'] > $_game_data['away_predictions'])
				{
					$user_prediction = $_game_data['home_team'] . ' ' . $user->lang['WINS'];
				}
				elseif (!empty($_game_data['away_prediction']) && !empty($_game_data['home_prediction']) && $_game_data['away_prediction'] == $_game_data['home_prediction'])
				{
					$user_prediction = $user->lang['DRAW'];
				}
				else
				{
					$user_prediction = $user->lang['NOT_PREDICTED'];
				}
			}
			else
			{
				$default_prediction = '';
			}
		
			$template->assign_block_vars('incomplete_games', array(
				'GAME_TIME'			=> $user->format_date($_game_data['game_time']),
				'AWAY_TEAM'			=> $_game_data['away_team'],
				'HOME_TEAM'			=> $_game_data['home_team'],
				'USER_PREDICTION'	=> $user_prediction
			));
		}
		
		foreach($user_stats['completed_games'] AS $_game_id => $_game_data)
		{
			if ($sportspredictions->get_scoring_style() == 'winner')
			{
				if ($_game_data['away_prediction'] > $_game_data['home_prediction'])
				{
					$user_prediction = $_game_data['away_team'] . ' ' . $user->lang['WINS'];
				}
				elseif ($_game_data['home_prediction'] > $_game_data['away_predictions'])
				{
					$user_prediction = $_game_data['home_team'] . ' ' . $user->lang['WINS'];
				}
				elseif (!empty($_game_data['away_prediction']) && !empty($_game_data['home_prediction']) && $_game_data['away_prediction'] == $_game_data['home_prediction'])
				{
					$user_prediction = $user->lang['DRAW'];
				}
				else
				{
					$user_prediction = $user->lang['NOT_PREDICTED'];
				}
				
				if (isset($_game_data['away_score']) && isset($_game_data['home_score']))
				{
					if ($_game_data['away_prediction'] == $_game_data['home_prediction'] && $_game_data['away_score'] == $_game_data['home_score'])
					{
						// draw
						$result = 'Correct Draw Predition: +' . (($_game_data['bonus'] == 1) ? $sportspredictions->config['draw_prediction_points'] * 2 : $sportspredictions->config['draw_prediction_points']);
					}
					else if (($_game_data['away_prediction'] > $_game_data['home_prediction'] && $_game_data['away_score'] > $_game_data['home_score']) || ($_game_data['away_prediction'] < $_game_data['home_prediction'] && $_game_data['away_score'] < $_game_data['home_score']))
					{
						// correct
						$result = 'Correct Predition: +' . (($_game_data['bonus'] == 1) ? $sportspredictions->config['correct_prediction_points'] * 2 : $sportspredictions->config['correct_prediction_points']);
					}
					else
					{
						// incorrect
						$result = 'Incorrect Predition: -' . (($_game_data['bonus'] == 1) ? $sportspredictions->config['incorrect_prediction_points'] * 2 : $sportspredictions->config['incorrect_prediction_points']);
					}
				}
				else
				{
					$result = '';
				}
			}
			else
			{
				$default_prediction = '';
			}
			
			
		
			$template->assign_block_vars('complete_games', array(
				'GAME_TIME'			=> $user->format_date($_game_data['game_time']),
				'AWAY_TEAM'			=> $_game_data['away_team'],
				'HOME_TEAM'			=> $_game_data['home_team'],
				'AWAY_SCORE'		=> $_game_data['away_score'],
				'HOME_SCORE'		=> $_game_data['home_score'],
				'USER_PREDICTION'	=> $user_prediction,
				'RESULT'			=> $result
			));
		}
	
	break;
	
	case 'reminder':
	
		if (!$user->data['is_registered'])
		{
			login_box('', $user->lang['SP_LOGIN_REQUIRED']);
		}
	
		$req = request_var('req', 'subscribe');
		
		if ($req == 'subscribe')
		{
			$sportspredictions->reminder_subscribe($user->data['user_id']);
			
			$return_msg = $user->lang['SP_SUBSCRIBE_SUCCESS'];
		}
		else if ($req == 'unsubscribe')
		{
			$sportspredictions->reminder_subscribe($user->data['user_id'], true);
			
			$return_msg = $user->lang['SP_UNSUBSCRIBE_SUCCESS'];
		}
		
		meta_refresh(3, $u_action);
		$message = $return_msg . '<br /><br />' . sprintf($user->lang['RETURN_SP_INDEX'], '<a href="' . $u_action . '">', '</a>');
		trigger_error($message);
	
	break;
}

$template->assign_vars(array(
	'S_LEAGUE_OPTIONS'			=> $sportspredictions->build_league_options(),
	
	'U_SP_NAV_HOME'				=> $u_action,
	'U_SP_NAV_FULL_LEADERBOARD'	=> $u_action . '?mode=full_leaderboard',
	'U_SP_NAV_PREDICT'			=> $u_action . '?mode=predict',
	'U_SP_NAV_EDIT_PREDICTIONS'	=> $u_action . '?mode=edit_predictions')
);

// Output page
page_header($page_header);

$template->set_filenames(array(
	'body' => $page_body)
);

page_footer();

?>