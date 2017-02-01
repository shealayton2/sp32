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

if (!$user->data['is_registered'])
{
	login_box('', $user->lang['SP_LOGIN_REQUIRED']);
}

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
			'LEAGUE_NAME'					=> $sportspredictions->leagues_array[$sportspredictions->get_league_id()]['league_name'],
			'SCORING_STYLE'					=> $sportspredictions->get_scoring_style(),

			'U_FULL_LEADERBOARD'	=> $u_action . '?mode=full_leaderboard',
			'U_SORT_USERNAME'		=> $u_action . '?sort=username',
			'U_SORT_WINS'			=> $u_action . '?sort=wins',
			'U_SORT_LOSSES'			=> $u_action . '?sort=losses',
			'U_SORT_POINTDIFF'		=> $u_action . '?sort=pointdiff',
			'U_SORT_WINPERC'		=> $u_action . '?sort=winperc',
			'U_SORT_POINTS'			=> $u_action,
			'U_PREDICT' 			=> $u_action . '?mode=predict')
		);

		$stats_array = $sportspredictions->build_leaderboard($sort, $sportspredictions->config['leaderboard_limit'], $start);

		foreach($stats_array AS $lb_data)
		{
			$template->assign_block_vars('leaderboard', array(
				'POSITION'		=> $lb_data['rank'],
				'USER_ID'		=> $lb_data['user_id'],
				'USERNAME'		=> $lb_data['username'],
				'STATS_LINK'	=> $u_action . '?mode=user_stats&amp;user_id=' . $lb_data['user_id'],
				'STATS_LINK'	=> '#',
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
			
			$user_prediction = ($away_prediction > $home_prediction) ? $game_data['away_team'] : (($away_prediction < $home_prediction) ? $game_data['home_team'] : '');
			
			$template->assign_block_vars('upcoming_games', array(
				'GAME_ID'			=> $game_id,
				'GAME_TIME'			=> $user->format_date($game_data['game_time']),
				'AWAY_TEAM'			=> $game_data['away_team'],
				'HOME_TEAM'			=> $game_data['home_team'],
				'AWAY_PREDICTION'	=> $away_prediction,
				'HOME_PREDICTION'	=> $home_prediction,
				'USER_PREDICTION'	=> $user_prediction
			));
		}

	break;
	
	case 'predict':
	case 'edit_predictions':
	
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

				foreach ((array) $game_prediction AS $game_id => $prediction)
				{
					if ($prediction == 'away')
					{
						$away_prediction[$game_id] = '1';
						$home_prediction[$game_id] = '0';
					}
					elseif ($prediction == 'home')
					{
						$away_prediction[$game_id] = '0';
						$home_prediction[$game_id] = '1';
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
			
			'PAGE_TITLE'		=> $title,
			'SCORING_STYLE'		=> $sportspredictions->get_scoring_style()
		));

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
			$template->assign_block_vars('upcoming_games', array(
				'GAME_ID'			=> $game_id,
				'GAME_TIME'			=> $user->format_date($game_data['game_time']),
				'AWAY_TEAM'			=> $game_data['away_team'],
				'HOME_TEAM'			=> $game_data['home_team'],
				'BONUS'				=> (($game_data['bonus'] == 1) ? true : false),
				'AWAY_PREDICTION'	=> ($mode == 'edit_predictions') ? $game_data['away_prediction'] : '',
				'HOME_PREDICTION'	=> ($mode == 'edit_predictions') ? $game_data['home_prediction'] : '',
				'USER_PREDICTION'	=> ($mode == 'edit_predictions') ? (($game_data['away_prediction'] > $game_data['home_prediction']) ? 'away' : 'home') : '',
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
			'SCORING_STYLE'					=> $sportspredictions->get_scoring_style(),

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
				'STATS_LINK'	=> '#',
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
	
		$page_header	= $user->lang['SPORTS_PREDICTIONS'];
		$title			= $user->lang['SPORTS_PREDICTIONS'] . ' : ' . sprintf($user->lang['PREDICTION_STATS'], $username);
		$page_body		= 'sportspredictions/user_stats.html';
		
		$user_id = request_var('user_id', 0);
		
		$sportspredictions->build_leaderboard('points');
		$user_leaderboard_info = $sportspredictions->get_user_stats($user_id);
	
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