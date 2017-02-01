<?php

/**
 * This file is part of the Sports Prediction package.
 *
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the license.txt file.
 */

namespace jslayton\sportspredictions\migrations\v10x;

use phpbb\db\migration\migration;

/**
 * @package jslayton\sportspredictions\migrations\v10x
 */
class v_1_0_0_initial_schema extends migration
{
	/**
	 * {@inheritdoc}
	 */
	public function effectively_installed()
	{
		return isset($this->config['sportspredictions_mod_version']) && version_compare($this->config['sportspredictions_mod_version'], '1.0.0', '>=');
	}

	/**
	 * {@inheritdoc}
	 */
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\dev');
	}
	
	public function update_schema()
	{
		return array(
			'add_columns' => array(
				$this->table_prefix . 'users' => array(
					'user_sp_default_league' => array('UINT', 0),
			),
			
			'add_tables' => array(
				$this->table_prefix . 'sp_config' => array(
					'COLUMNS' => array(
						'config_name'	=> array('VCHAR:255', ''),
						'config_value'	=> array('VCHAR:255', ''),
					),					
				),
				$this->table_prefix . 'sp_leagues' => array(
					'COLUMNS' => array(
						'league_id'			=> array('UINT', NULL, 'auto_increment'),
						'league_name'		=> array('VCHAR:255', ''),
						'league_logo'		=> array('VCHAR:255', ''),
						'league_logo_tn'	=> array('VCHAR:255', ''),
						'scoring_style'		=> array('VCHAR:6', ''),
						'pointdiff_average'	=> array('VCHAR:255', ''),
						'perfect_round_bonus'			=> array('UINT', 0),
						'perfect_round_bonus_points'	=> array('UINT', 0),
						'active'			=> array('UINT', 1),
					),
					'PRIMARY_KEY' => 'league_id',
				),
				$this->table_prefix . 'sp_teams' => array(
					'COLUMNS' => array(
						'team_id'		=> array('UINT', NULL, 'auto_increment'),
						'league_id'		=> array('UINT', NULL),
						'team_name'		=> array('VCHAR:255', ''),
						'team_logo'		=> array('VCHAR:255', ''),
						'team_logo_tn'	=> array('VCHAR:255', ''),
						'wins'			=> array('UINT', 0),
						'losses'		=> array('UINT', 0),
						'ties'			=> array('UINT', 0),
						'show_results'	=> array('UINT', 0),
					),
					'PRIMARY_KEY' => 'team_id',
				),
				$this->table_prefix . 'sp_games' => array(
					'COLUMNS' => array(
						'game_id'	=> array('UINT', NULL, 'auto_increment'),
						'league_id'	=> array('UINT', NULL),
						'round'		=> array('UINT', NULL),
						'game_time'	=> array('UINT', NULL),
						'away_id'	=> array('UINT', NULL),
						'home_id'	=> array('UINT', NULL),
						'away_score'	=> array('UINT', NULL),
						'home_score'	=> array('UINT', NULL),
						'bonus'		=> array('UINT', NULL),
					),
					'PRIMARY_KEY' => 'game_id',
				),
				$this->table_prefix . 'sp_predictions' => array(
					'COLUMNS' => array(
						'user_id'			=> array('UINT', NULL),
						'game_id'			=> array('UINT', NULL),
						'away_prediction'	=> array('UINT', NULL),
						'home_prediction'	=> array('UINT', NULL),
					),
				),
			),
		);
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function update_data()
	{
		return array(
			array('config.add', array('sportspredictions_mod_version', '1.0.0')),
		);
	}
}
