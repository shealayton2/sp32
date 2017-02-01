<?php
// ext/jslayton/sportspredictions/controller/main.php

/**
 *
 * @package Sports Predictions Extension
 * @copyright (c) 2017 jslayton
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace jslayton\sportspredictions\controller;

class main
{
	/** @var \phpbb\config\config */
	protected $config;
	
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;
	
	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \jslayton\sportspredictions\sportspredictions */
	protected $sportspredictions;

	/**
	* Constructor
	* NOTE: The parameters of this method must match in order and type with
	* the dependencies defined in the services.yml file for this service.
	*
	* @param \phpbb\config            $config    Config object
	* @param \phpbb\template          $template  Template object
	* @param \phpbb\user              $user      User object
	* @param \phpbb\controller\helper $helper    Controller helper object
	* @param string                   $root_path phpBB root path
	* @param string                   $php_ext   phpEx
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\template\template $template, \phpbb\user $user, \phpbb\controller\helper $helper, \phpbb\request\request $request, \jslayton\sportspredictions\sportspredictions $sportspredictions, $root_path, $php_ext)
	{
		$this->config = $config;
		$this->db = $db;
		$this->template = $template;
		$this->user = $user;
		$this->helper = $helper;
		$this->request = $request;
		$this->sportspredictions = $sportspredictions;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
		
		$template->assign_vars(array(
			'S_LEAGUE_OPTIONS'			=> $this->sportspredictions->build_league_options(),
			
			'U_SP_NAV_HOME'				=> $this->helper->route('sportspredictions_base_controller'),
			'U_SP_NAV_FULL_LEADERBOARD'	=> $this->helper->route('sportspredictions_full_leaderboard'),
			'U_SP_NAV_PREDICT'			=> $this->helper->route('sportspredictions_predict'),
			'U_SP_NAV_EDIT_PREDICTIONS'	=> $this->helper->route('sportspredictions_edit_predictions'))
		);
	}

	/**
	* Base controller to be accessed with the URL /newspage/{page}
	* (where {page} is the placeholder for a value)
	*
	* @param int    $page    Page number taken from the URL
	* @return Symfony\Component\HttpFoundation\Response A Symfony Response object
	*/
	public function index()
	{
		/*
		* Do some magic here,
		* load your data and send it to the template.
		*/
		
		$this->user->add_lang_ext('jslayton/sportspredictions', 'sports_predictions');
		
		$page_header	= $this->user->lang('SPORTS_PREDICTIONS');
		$title			= $this->user->lang('SPORTS_PREDICTIONS');
		
		$sort = $this->request->variable('sort', '');
		$start = $this->request->variable('start', 1);
		
		
		//if ($mode && $mode != 'index') {
			//$params[] = "mode=$mode";
		//}
		
		//if ($sort) {
			//$params[] = "sort=$sort";
		//}

		//$pagination_url = (sizeof($params) > 0) ? append_sid("{$phpbb_root_path}sportspredictions.$phpEx", implode('&amp;', $params)) : append_sid("{$phpbb_root_path}sportspredictions.$phpEx");
		
		if ($this->user->data['user_sp_reminder'] == 1) {
			$u_reminder_subscribe = $this->helper->route('sportspredictions_reminder', array('req' => 'unsubscribe'));
		} else {
			$u_reminder_subscribe = $this->helper->route('sportspredictions_reminder', array('req' => 'subscribe'));
		}
		
		$this->template->assign_vars(array(
			'PAGE_TITLE'					=> $title,
			'EXACT_PREDICTION_EXPLAIN'		=> sprintf($this->user->lang('EXACT_PREDICTION_EXPLAIN'), $this->sportspredictions->config['exact_prediction_points']),
			'CORRECT_PREDICTION_EXPLAIN'	=> sprintf($this->user->lang('CORRECT_PREDICTION_EXPLAIN'), $this->sportspredictions->config['correct_prediction_points']),
			'INCORRECT_PREDICTION_EXPLAIN'	=> sprintf($this->user->lang('INCORRECT_PREDICTION_EXPLAIN'), $this->sportspredictions->config['incorrect_prediction_points']),
			'DRAW_PREDICTION_EXPLAIN'		=> sprintf($user->lang['DRAW_PREDICTION_EXPLAIN'], $sportspredictions->config['draw_predictions_points']),
			'PERFECT_ROUND_BONUS_EXPLAIN'	=> sprintf($user->lang['PERFECT_ROUND_BONUS_EXPLAIN'], $sportspredictions->leagues_array[$sportspredictions->get_league_id()]['perfect_round_bonus_points']),
			'LEAGUE_NAME'					=> $this->sportspredictions->leagues_array[$this->sportspredictions->get_league_id()]['league_name'],
			'LOGO_CONTAINER_HEIGHT'			=> $sportspredictions->config['logo_max_thumbnail_height'] * 1.5,
			
			'S_REMINDER_STATUS'		=> $this->user->data['user_sp_reminder'],

			'U_FULL_LEADERBOARD'	=> $this->helper->route('sportspredictions_full_leaderboard'),
			'U_SORT_USERNAME'		=> $this->helper->route('sportspredictions_index_sort_controller', array('sort' => 'username')),
			'U_SORT_WINS'			=> $this->helper->route('sportspredictions_index_sort_controller', array('sort' => 'wins')),
			'U_SORT_LOSSES'			=> $this->helper->route('sportspredictions_index_sort_controller', array('sort' => 'losses')),
			'U_SORT_POINTDIFF'		=> $this->helper->route('sportspredictions_index_sort_controller', array('sort' => 'pointdiff')),
			'U_SORT_WINPERC'		=> $this->helper->route('sportspredictions_index_sort_controller', array('sort' => 'winperc')),
			'U_SORT_POINTS'			=> $this->helper->route('sportspredictions_base_controller'),
			'U_PREDICT' 			=> $this->helper->route('sportspredictions_predict'),
			'U_REMINDER_SUBSCRIBE'	=> $u_reminder_subscribe)
		);

		$stats_array = $this->sportspredictions->build_leaderboard($sort, $this->sportspredictions->config['leaderboard_limit'], $start);

		foreach($stats_array AS $lb_data)
		{
			$this->template->assign_block_vars('leaderboard', array(
				'POSITION'		=> $lb_data['rank'],
				'USER_ID'		=> $lb_data['user_id'],
				'USERNAME'		=> $lb_data['username'],
				'STATS_LINK'	=> $this->helper->route('sportspredictions_user_stats', array('user_id' => $lb_data['user_id'])),
				'HIGHLIGHT'		=> (($lb_data['user_id'] == $this->user->data['user_id']) ? true : false),
				'WINS'			=> $lb_data['wins'],
				'LOSSES'		=> $lb_data['losses'],
				'POINTDIFF'		=> $lb_data['pointdiff'],
				'WINPERC'		=> $lb_data['winperc'],
				'POINTS'		=> $lb_data['points']
			));
		}
		
		if (sizeof($this->sportspredictions->stats_array) > $this->sportspredictions->config['leaderboard_limit']) {
			if($sort != '') {
				$pagination = $phpbb_container->get('pagination');
				$pagination->generate_template_pagination(append_sid("{$this->root_path}app.$phpEx", 'controller=sportspredictions&sort=' . $sort), 'pagination', 'page', sizeof($this->sportspredictions->stats_array), $this->sportspredictions->config['leaderboard_limit'], $start);
				
				$this->template->assign_vars(array(
					'PAGE_NUMBER'	=> $pagination->on_page(sizeof($this->sportspredictions->stats_array), $this->sportspredictions->config['leaderboard_limit'], $start),
				));
			} else {
				$pagination = $phpbb_container->get('pagination');
				$pagination->generate_template_pagination(
					array(
						'routes' => array(
							'sportspredictions_base_controller',
							'sportspredictions_index_page_controller',
						),
						'params' => array(),
					), 'pagination', 'page', sizeof($this->sportspredictions->stats_array), $this->sportspredictions->config['leaderboard_limit'], $start);
				
				$this->template->assign_vars(array(
					'PAGE_NUMBER'	=> $pagination->on_page(sizeof($this->sportspredictions->stats_array), $this->sportspredictions->config['leaderboard_limit'], $start),
				));
			}
		}
		
		$user_predictions = $this->sportspredictions->get_user_predictions($this->user->data['user_id']);
		
		$upcoming_games = $this->sportspredictions->get_upcoming_games($this->sportspredictions->config['upcoming_games_limit']);
		foreach ((array) $upcoming_games AS $game_id => $game_data)
		{
			$away_prediction = (isset($user_predictions[$game_id])) ? $user_predictions[$game_id]['away_prediction'] : '';
			$home_prediction = (isset($user_predictions[$game_id])) ? $user_predictions[$game_id]['home_prediction'] : '';
			
			if ($away_prediction != '' && $home_prediction != '')
			{
				if ($away_prediction == $home_prediction)
				{
					$user_prediction = $this->user->lang['DRAW'];
				}
				else
				{
					$user_prediction = (($away_prediction > $home_prediction) ? $game_data['away_team'] : (($away_prediction < $home_prediction) ? $game_data['home_team'] : '')) . ' ' . $this->user->lang['WINS'];
				}
			}
			else
			{
				$user_prediction = '';
			}
			
			$this->template->assign_block_vars('upcoming_games', array(
				'GAME_ID'			=> $game_id,
				'GAME_TIME'			=> $user->format_date($game_data['game_time']),
				'AWAY_TEAM'			=> $game_data['away_team'],
				'HOME_TEAM'			=> $game_data['home_team'],
				'AWAY_LOGO_TN'		=> ((!empty($this->sportspredictions->teams_array['id_ref'][$game_data['away_id']]['team_logo_tn'])) ? $phpbb_root_path . $this->sportspredictions->config['logo_path'] . '/' . $this->sportspredictions->teams_array['id_ref'][$game_data['away_id']]['team_logo_tn'] : ''),
				'HOME_LOGO_TN'		=> ((!empty($this->sportspredictions->teams_array['id_ref'][$game_data['home_id']]['team_logo_tn'])) ? $phpbb_root_path . $this->sportspredictions->config['logo_path'] . '/' . $this->sportspredictions->teams_array['id_ref'][$game_data['home_id']]['team_logo_tn'] : ''),
				'AWAY_RECORD'		=> $this->sportspredictions->get_team_record($game_data['away_id']),
				'HOME_RECORD'		=> $this->sportspredictions->get_team_record($game_data['home_id']),
				'BONUS'				=> $game_data['bonus'],
				'AWAY_PREDICTION'	=> $away_prediction,
				'HOME_PREDICTION'	=> $home_prediction,
				'USER_PREDICTION'	=> $user_prediction
			));
		}

		/*
		* The render method takes up to three other arguments
		* @param    string        Name of the template file to display
		*                        Template files are searched for two places:
		*                        - phpBB/styles/<style_name>/template/
		*                        - phpBB/ext/<all_active_extensions>/styles/<style_name>/template/
		* @param    string        Page title
		* @param    int            Status code of the page (200 - OK [ default ], 403 - Unauthorised, 404 - Page not found, etc.)
		*/
		return $this->helper->render('main.html');
	}
	
	public function predict( $mode2="predict" )
	{
		/*
		* Do some magic here,
		* load your data and send it to the template.
		*/
		
		$this->user->add_lang_ext('jslayton/sportspredictions', 'sports_predictions');
		
		$page_header	= $this->user->lang['SPORTS_PREDICTIONS'] . ' : ' . $this->user->lang['PREDICT'];
		$title			= $this->user->lang['SPORTS_PREDICTIONS'] . ' : ' . $this->user->lang['PREDICT'];
		//$page_body		= 'sportspredictions/predict.html';
		add_form_key('predict');
		
		if ($action == 'save')
		{
			if ($this->sportspredictions->get_scoring_style() == 'score')
			{
				$away_prediction = $this->request->variable('away_prediction', array(''));
				$home_prediction = $this->request->variable('home_prediction', array(''));
			}
			else
			{
				$game_prediction = $this->request->variable('game_prediction', array(''));

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
				$this->sportspredictions->add_predictions($this->user->data['user_id'], $away_prediction, $home_prediction, true);
			}
			else
			{
				$this->sportspredictions->add_predictions($this->user->data['user_id'], $away_prediction, $home_prediction);
			}

			meta_refresh(3, $u_action);
			$message = $this->user->lang['PREDICTION_UPDATE_SUCCESS'] . '<br /><br />' . sprintf($this->user->lang['RETURN_SP_INDEX'], '<a href="' . $this->helper->route('sportspredictions_base_controller') . '">', '</a>');
			trigger_error($message);
		}
		
		$s_hidden_fields .= '<input type="hidden" name="action" value="save" />';
		
		$template->assign_vars(array(
			'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
			
			'PAGE_TITLE'		=> $title,
			'SCORING_STYLE'		=> $this->sportspredictions->get_scoring_style()
		));

		if ($mode == 'edit_predictions')
		{
			$upcoming_games = $this->sportspredictions->get_games_to_predict($this->user->data['user_id'], true);
		}
		else
		{
			$upcoming_games = $this->sportspredictions->get_games_to_predict($this->user->data['user_id']);
		}
		if (sizeof($upcoming_games) == 0)
		{
			meta_refresh(3, $u_action);
			$message = $this->user->lang['NO_GAMES_TO_PREDICT'] . '<br /><br />' . sprintf($this->user->lang['RETURN_SP_INDEX'], '<a href="' . $this->helper->route('sportspredictions_base_controller') . '">', '</a>');
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
			
			$this->template->assign_block_vars('upcoming_games', array(
				'GAME_ID'				=> $game_id,
				'GAME_TIME'				=> $this->user->format_date($game_data['game_time']),
				'AWAY_TEAM'				=> $game_data['away_team'],
				'HOME_TEAM'				=> $game_data['home_team'],
				'BONUS'					=> (($game_data['bonus'] == 1) ? true : false),
				'AWAY_PREDICTION'		=> ($mode == 'edit_predictions') ? $game_data['away_prediction'] : '',
				'HOME_PREDICTION'		=> ($mode == 'edit_predictions') ? $game_data['home_prediction'] : '',
				'DEFAULT_PREDICTION'	=> $default_prediction
			));
		}

		/*
		* The render method takes up to three other arguments
		* @param    string        Name of the template file to display
		*                        Template files are searched for two places:
		*                        - phpBB/styles/<style_name>/template/
		*                        - phpBB/ext/<all_active_extensions>/styles/<style_name>/template/
		* @param    string        Page title
		* @param    int            Status code of the page (200 - OK [ default ], 403 - Unauthorised, 404 - Page not found, etc.)
		*/
		return $this->helper->render('predict.html', $title);
	}
	
	public function full_leaderboard()
	{
		/*
		* Do some magic here,
		* load your data and send it to the template.
		*/
		
		$this->user->add_lang_ext('jslayton/sportspredictions', 'sports_predictions');
		
		$page_header	= $this->user->lang('SPORTS_PREDICTIONS');
		$title			= $this->user->lang('SPORTS_PREDICTIONS');
		
		$sort = $this->request->variable('sort', 'points');
		
		$this->template->assign_vars(array(
			'PAGE_TITLE'					=> $title,
			'EXACT_PREDICTION_EXPLAIN'		=> sprintf($this->user->lang('EXACT_PREDICTION_EXPLAIN'), $this->sportspredictions->config['exact_prediction_points']),
			'CORRECT_PREDICTION_EXPLAIN'	=> sprintf($this->user->lang('CORRECT_PREDICTION_EXPLAIN'), $this->sportspredictions->config['correct_prediction_points']),
			'INCORRECT_PREDICTION_EXPLAIN'	=> sprintf($this->user->lang('INCORRECT_PREDICTION_EXPLAIN'), $this->sportspredictions->config['incorrect_prediction_points']),
			'LEAGUE_NAME'					=> $this->sportspredictions->leagues_array[$this->sportspredictions->get_league_id()]['league_name'],

			'U_FULL_LEADERBOARD'	=> $this->helper->route('sportspredictions_full_leaderboard'),
			'U_SORT_USERNAME'		=> $this->helper->route('sportspredictions_index_sort_controller', array('sort' => 'username')),
			'U_SORT_WINS'			=> $this->helper->route('sportspredictions_index_sort_controller', array('sort' => 'wins')),
			'U_SORT_LOSSES'			=> $this->helper->route('sportspredictions_index_sort_controller', array('sort' => 'losses')),
			'U_SORT_POINTDIFF'		=> $this->helper->route('sportspredictions_index_sort_controller', array('sort' => 'pointdiff')),
			'U_SORT_WINPERC'		=> $this->helper->route('sportspredictions_index_sort_controller', array('sort' => 'winperc')),
			'U_SORT_POINTS'			=> $this->helper->route('sportspredictions_base_controller'),
			'U_PREDICT' 			=> $this->helper->route('sportspredictions_predict'))
		);
		
		$stats_array = $this->sportspredictions->build_leaderboard($sort);

		foreach($stats_array AS $lb_data)
		{
			$this->template->assign_block_vars('leaderboard', array(
				'POSITION'		=> $lb_data['rank'],
				'USER_ID'		=> $lb_data['user_id'],
				'USERNAME'		=> $lb_data['username'],
				'STATS_LINK'	=> $this->helper->route('sportspredictions_user_stats', array('user_id' => $lb_data['user_id'])),
				'HIGHLIGHT'		=> (($lb_data['user_id'] == $this->user->data['user_id']) ? true : false),
				'WINS'			=> $lb_data['wins'],
				'LOSSES'		=> $lb_data['losses'],
				'POINTDIFF'		=> $lb_data['pointdiff'],
				'WINPERC'		=> $lb_data['winperc'],
				'POINTS'		=> $lb_data['points']
			));
		}

		/*
		* The render method takes up to three other arguments
		* @param    string        Name of the template file to display
		*                        Template files are searched for two places:
		*                        - phpBB/styles/<style_name>/template/
		*                        - phpBB/ext/<all_active_extensions>/styles/<style_name>/template/
		* @param    string        Page title
		* @param    int            Status code of the page (200 - OK [ default ], 403 - Unauthorised, 404 - Page not found, etc.)
		*/
		return $this->helper->render('full_leaderboard.html', $title);
	}
	
	public function user_stats()
	{
		/*
		* Do some magic here,
		* load your data and send it to the template.
		*/
		
		$user_id = $this->request->variable('user_id', 0);
	
		$page_header	= $this->user->lang['SPORTS_PREDICTIONS'] . ' : ' . sprintf($this->user->lang['PREDICTION_STATS'], $this->sportspredictions->username_array[$user_id]);
		$title			= $this->user->lang['SPORTS_PREDICTIONS'] . ' : ' . sprintf($this->user->lang['PREDICTION_STATS'], $this->sportspredictions->username_array[$user_id]);

		
		$this->sportspredictions->build_leaderboard('points');
		$user_stats = $this->sportspredictions->get_user_stats($user_id);
		
		//echo '<pre>';
		//print_r($user_stats);
		//echo '</pre>';
		
		foreach((array) $user_stats['incomplete_games'] AS $_game_id => $_game_data)
		{
			if ($this->sportspredictions->get_scoring_style() == 'winner')
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
		
			$this->template->assign_block_vars('incomplete_games', array(
				'GAME_TIME'			=> $this->user->format_date($_game_data['game_time']),
				'AWAY_TEAM'			=> $_game_data['away_team'],
				'HOME_TEAM'			=> $_game_data['home_team'],
				'USER_PREDICTION'	=> $user_prediction
			));
		}
		
		foreach($user_stats['completed_games'] AS $_game_id => $_game_data)
		{
			if ($this->sportspredictions->get_scoring_style() == 'winner')
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
						$result = 'Correct Draw Predition: +' . (($_game_data['bonus'] == 1) ? $this->sportspredictions->config['draw_prediction_points'] * 2 : $this->sportspredictions->config['draw_prediction_points']);
					}
					else if (($_game_data['away_prediction'] > $_game_data['home_prediction'] && $_game_data['away_score'] > $_game_data['home_score']) || ($_game_data['away_prediction'] < $_game_data['home_prediction'] && $_game_data['away_score'] < $_game_data['home_score']))
					{
						// correct
						$result = 'Correct Predition: +' . (($_game_data['bonus'] == 1) ? $this->sportspredictions->config['correct_prediction_points'] * 2 : $this->sportspredictions->config['correct_prediction_points']);
					}
					else
					{
						// incorrect
						$result = 'Incorrect Predition: -' . (($_game_data['bonus'] == 1) ? $this->sportspredictions->config['incorrect_prediction_points'] * 2 : $this->sportspredictions->config['incorrect_prediction_points']);
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
			
			
		
			$this->template->assign_block_vars('complete_games', array(
				'GAME_TIME'			=> $this->user->format_date($_game_data['game_time']),
				'AWAY_TEAM'			=> $_game_data['away_team'],
				'HOME_TEAM'			=> $_game_data['home_team'],
				'AWAY_SCORE'		=> $_game_data['away_score'],
				'HOME_SCORE'		=> $_game_data['home_score'],
				'USER_PREDICTION'	=> $user_prediction,
				'RESULT'			=> $result
			));
		}

		/*
		* The render method takes up to three other arguments
		* @param    string        Name of the template file to display
		*                        Template files are searched for two places:
		*                        - phpBB/styles/<style_name>/template/
		*                        - phpBB/ext/<all_active_extensions>/styles/<style_name>/template/
		* @param    string        Page title
		* @param    int            Status code of the page (200 - OK [ default ], 403 - Unauthorised, 404 - Page not found, etc.)
		*/
		return $this->helper->render('user_stats.html');
	}
	
	public function reminder()
	{
		/*
		* Do some magic here,
		* load your data and send it to the template.
		*/
		
		$req = $this->request->variable('req', 'subscribe');
		
		if ($req == 'subscribe')
		{
			$this->sportspredictions->reminder_subscribe($user->data['user_id']);
			
			$return_msg = $this->user->lang['SP_SUBSCRIBE_SUCCESS'];
		}
		else if ($req == 'unsubscribe')
		{
			$this->sportspredictions->reminder_subscribe($user->data['user_id'], true);
			
			$return_msg = $this->user->lang['SP_UNSUBSCRIBE_SUCCESS'];
		}
		
		meta_refresh(3, $u_action);
		$message = $return_msg . '<br /><br />' . sprintf($this->user->lang['RETURN_SP_INDEX'], '<a href="' . $this->helper->route('sportspredictions_base_controller') . '">', '</a>');
		trigger_error($message);
		
		/*
		* The render method takes up to three other arguments
		* @param    string        Name of the template file to display
		*                        Template files are searched for two places:
		*                        - phpBB/styles/<style_name>/template/
		*                        - phpBB/ext/<all_active_extensions>/styles/<style_name>/template/
		* @param    string        Page title
		* @param    int            Status code of the page (200 - OK [ default ], 403 - Unauthorised, 404 - Page not found, etc.)
		*/
		//return $this->helper->render('user_stats.html');
	}
}
