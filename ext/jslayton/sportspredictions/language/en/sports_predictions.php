<?php
/** 
*
* sports_predictions [English]
*
* @package language
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/
                    
/**
* DO NOT CHANGE
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
    $lang = array();
}
                        
// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
                        
$lang = array_merge($lang, array(
	'ACP_SPORTS_PREDICTIONS'	=> 'Sports Predictions',
	'ACP_SP_OVERVIEW'			=> 'Overview',
	'ACP_SP_CONFIGURATION'		=> 'Configuration',
	'ACP_SP_LEAGUES'			=> 'Leagues',
	'ACP_SP_TEAMS'				=> 'Teams',
	'ACP_SP_GAMES'				=> 'Games',
	'ACP_SP_SCORES'				=> 'Scores',
	'ACP_SP_PREDICTIONS'		=> 'Predictions',
    'ACP_SP_EXPLAIN'    		=> 'Sports Predictions for phpBB3 is an online "pick \'em" game for your users to play.  Using the links in the menu on your left, you are able to add teams and games for your users to predict.  After the games are completed and the scores are entered, your users\' predictions will then be compared to the actual scores and a leaderboard will be generated based on the points that you have specified in the configuration.',
	
	// config
	'ACP_SP_GENERAL_CONFIG'						=> 'General Configuration',
	'ACP_SP_CONFIG_EXPLAIN'						=> 'This is a short description about the config overview',
	'ACP_LEAGUE_NAME'							=> 'League Name',
	'ACP_LEADERBOARD_LIMIT'						=> 'Leaderboard Limit',
	'ACP_LEADERBOARD_LIMIT_EXPLAIN'				=> 'This is how many entries will be shown per page on the front page leaderboard',
	'ACP_UPCOMING_GAMES_LIMIT'					=> 'Upcoming Games Limit',
	'ACP_UPCOMING_GAMES_LIMIT_EXPLAIN'			=> 'This is how many games will be shown on the main page in the right-hand column',
	'ACP_SP_SCORING_CONFIG'						=> 'Scoring Configuration',
	'ACP_EXACT_PREDICTION_POINTS'				=> 'Exact Prediction Points',
	'ACP_EXACT_PREDICTION_POINTS_EXPLAIN'		=> 'This is the number of points a user will receive for an exact prediction (correct winner and correct score)',
	'ACP_CORRECT_PREDICTION_POINTS'				=> 'Correct Prediction Points',
	'ACP_CORRECT_PREDICTION_POINTS_EXPLAIN'		=> 'This is the number of points a user will receive for a correct prediction (correct winner, incorrect score)',
	'ACP_INCORRECT_PREDICTION_POINTS'			=> 'Incorrect Prediction Points',
	'ACP_INCORRECT_PREDICTION_POINTS_EXPLAIN'	=> 'This is the number of points a user will have deducted for an incorrect prediction (incorrect winner, incorrect score)',
	'ACP_LEADERBOARD_LIMIT_GTZERO'				=> 'The leaderboard limit must be greater than zero',
	'ACP_UPCOMING_GAMES_LIMIT_GTZERO'			=> 'The upcoming games limit must be greater than zero',
	'ACP_LOGO_CONFIGURATION'					=> 'Logo Configuration',
	'ACP_CONFIG_UPDATED'						=> 'Configuration was updated successfully',
	'DEFAULT_LEAGUE'							=> 'Default League',
	'LOGO_PATH'									=> 'Logo Path',
	'LOGO_MAX_THUMBNAIL_WIDTH'					=> 'Max Thumbnail Width',
	'LOGO_MAX_THUMBNAIL_HEIGHT'					=> 'Max Thumbnail Height',
	'REBUILD_THUMBNAILS'						=> 'Rebuild Thumbnails',
	'REBUILD_THUMBNAILS_LINK'					=> 'Click here to rebuild thumbnails',
	'CONFIRM_REBUILD_THUMBNAILS'				=> 'Are you sure you want to rebuild the thumbnails associated with Sports Predictions?',
	'ACP_THUMBNAILS_REBUILT'					=> 'Thumbnails were successfully rebuilt',
	
	// leagues
	'ACP_SP_LEAGUES_EXPLAIN'	=> 'This is a short description about leagues',
	'ACP_ADD_NEW_LEAGUE'		=> 'Add New League',
	'ACP_SP_ADD_LEAGUE_EXPLAIN'	=> 'This is a short description about adding a new league',
	'ACP_LEAGUE_ADDED'			=> 'League was added successfully',
	'ACP_LEAGUE_UPDATED'		=> 'League was updated successfully',
	'ACP_LEAGUE_REMOVED'		=> 'League was removed successfully',
	'ACP_NO_LEAGUE'				=> 'No league was specified',
	'CONFIRM_DELETE_LEAGUE'		=> 'Are you sure you want to delete this league?',
	'LEAGUE_NAME'				=> 'League Name',
	'LEAGUE_LOGO'				=> 'League Logo',
	'LEAGUE_LOGO_THUMBNAIL'		=> 'League Logo Thumbnail',
	'SP_CURRENT_LOGO'			=> 'Current Logo',
	'SP_NEW_LOGO'				=> 'New Logo',
	'LEAGUE_LOGO_EXPLAIN'		=> 'This is where we explain the logo size requirements',
	'SP_WRONG_SIZE'				=> 'The logo you selected exceeds the width/height limits set in the configuration',
	'REMOVE_LOGO'				=> 'Remove Logo',
	'SCORING_STYLE'				=> 'Scoring Style',
	'SCORING_STYLE_EXPLAIN'		=> 'If set to "pick score", users will be required to pick the score of the game and the point difference average will be used.  If set to "pick winner", users will only pick the winner using a radio button.',
	'PICK_SCORE'				=> 'Pick Score',
	'PICK_WINNER'				=> 'Pick Winner',
	'POINTDIFF_AVERAGE'			=> 'Point Difference Average',
	'POINTDIFF_AVERAGE_EXPLAIN'	=> 'Only applicable if using scoring style "pick score".  If set to yes, the value displayed will be an average of the point differential per game predicted.  If set to no, the actual point differential total will be displayed.',
	'ACTIVE_LEAGUE'				=> 'Active League',
	'CURRENT_LEAGUE'			=> 'Current League',
	'CHANGE_LEAGUE'				=> 'Change League',
	
	// teams
	'ACP_SP_ADD_TEAMS'			=> 'Add Teams',
	'ACP_SP_TEAMS_EXPLAIN'		=> 'Add teams here by clicking the \'Add New Team\' button below.',
	'ACP_SP_ADD_TEAMS_EXPLAIN'	=> 'Please enter the team name below',
	'ACP_ADD_NEW_TEAM'			=> 'Add New Team',
	'ACP_ADD_MULTIPLE_TEAMS'	=> 'Add Multiple Teams',
	'ACP_ENTER_TEAM_NAME'		=> 'Please enter a team name',
	'ACP_TEAM_ADDED'			=> 'Team was added successfully',
	'ACP_TEAM_EDITED'			=> 'Team was edited successfully',
	'ACP_TEAM_REMOVED'			=> 'Team was removed successfully.<br />%d game(s) was deleted.<br />%d prediction(s) was deleted.',
	'ACP_NO_TEAM'				=> 'No team was specified',
	'CONFIRM_DELETE_TEAM'		=> 'Are you sure you want to delete this team?  This will also delete any games and/or predictions associated with this team.',
	'TEAM_NAME'					=> 'Team Name',
	'TEAM_LOGO_EXPLAIN'			=> 'This is where we explain the logo size requirements',
	'MULTIADD_TEAM_EXPLAIN'		=> 'Place each new team on a new line.',
	'WIN_LOSS_ABBR'				=> '(W-L)',
	'SP_SHOW_RESULTS'			=> 'Show Results',
	
	// games
	'ACP_SP_ADD_GAMES'			=> 'Add Games',
	'ACP_SP_GAMES_EXPLAIN'		=> 'Add games here by clicking the \'Add New Game\' button below.  The button will not be visible if you have not configured at least two teams.',
	'ACP_SP_ADD_GAMES_EXPLAIN'	=> 'Games added here will be added as if you are entering the game time for the timezone currently set in your profile.  If the current date/time listed above is not correct, please correct your timezone BEFORE entering games.',
	'ACP_NO_GAME_TIME'			=> 'Gametime was not set correctly',
	'ACP_GAME_ADDED'			=> 'Game was added successfully',
	'ACP_GAME_EDITED'			=> 'Game was edited successfully',
	'ACP_GAME_REMOVED'			=> 'Game was removed successfully.<br />%d prediction(s) were deleted.',
	'ACP_NO_GAME'				=> 'No game was specified',
	'ACP_ADD_NEW_GAME'			=> 'Add New Game',
	'ACP_ADD_MULTIPLE_GAMES'	=> 'Add Multiple Games',
	'ACP_SAME_TEAM'				=> 'The away team and home team cannot be the same team',
	'ACP_SP_GAMES_TZ_EXPLAIN'	=> 'All game times are shown in the timezone set in your preferences, which is currently: ',
	'ACP_NO_PENDING_GAMES'		=> 'There are no pending games',
	'CONFIRM_DELETE_GAME'		=> 'Are you sure you want to delete this game?  Any predictions associated with this game will also be deleted.',
	'GAME_DATE'					=> 'Game Date',
	'GAME_TIME'					=> 'Game Time',
	'AWAY_TEAM'					=> 'Away Team',
	'HOME_TEAM'					=> 'Home Team',
	'BONUS'						=> 'Bonus',
	'SP_VS'						=> 'vs',
	'SP_DASH'					=> '-',
	
	// scores
	'ACP_SP_UPCOMING_GAMES'			=> 'Upcoming Games',
	'ACP_SP_UPCOMING_GAMES_EXPLAIN'	=> 'This is a list of games which have not started yet.',
	'ACP_SP_PENDING_GAMES'			=> 'Pending Games',
	'ACP_SP_PENDING_GAMES_EXPLAIN'	=> 'This is a list of games for which the game time has passed and scores can be entered.',
	'ACP_SP_SCORED_GAMES'			=> 'Scored Games',
	'ACP_SP_SCORED_GAMES_EXPLAIN'	=> 'This is a list of games where the score has already been entered.',
	'ACP_SP_EDIT_SCORE'				=> 'Edit Score',
	'ACP_SP_EDIT_SCORE_EXPLAIN'		=> 'Please enter the corrected score below.',
	'ACP_ADD_SCORES'				=> 'Add Scores',
	'ACP_SCORES_UPDATED'			=> 'Scores were updated successfully',
	'ACP_SCORE_REMOVED'				=> 'Score was successfully removed',
	'ACP_MISMATCH_SCORE'			=> 'You must enter a value for both the away score and home score.<br />If you wish to clear the score completely from the game, please leave both values blank.',
	'CONFIRM_CLEAR_SCORE'			=> 'Are you sure you want to clear the score for this game?',
	'AWAY_SCORE'					=> 'Away Score',
	'HOME_SCORE'					=> 'Home Score',
	'ACP_SP_NO_PENDING_GAMES'		=> 'There are no pending games to be scored',
	'ACP_SP_NO_UPCOMING_GAMES'		=> 'There are no upcoming games',
	'ACP_SP_NO_SCORED_GAMES'		=> 'There are no games that have already been scored',
	
	// predictions
	'ACP_SP_SEARCH'						=> 'View Predictions',
	'ACP_SP_PREDICTION_SEARCH_EXPLAIN'	=> 'You can filter your results by user, by game, or both.  However, both boxes can not be empty.',
	'ACP_SEARCH_PREDICTIONS_BY_USER'	=> 'Choose a user to view all predictions submitted by that user',
	'ACP_SEARCH_PREDICTIONS_BY_GAME'	=> 'Choose a game to view all predictions made for that game',
	'ACP_SP_NON_PREDICTED_GAMES'		=> 'Non-Predicted Games',
	'ACP_SP_PREDICTED_GAMES'			=> 'Predicted Games',
	'ACP_SP_VIEW_PREDICTIONS_ERROR'		=> 'You must select either a user or a game to filter by',
	'ACP_PREDICTIONS_UPDATED'			=> 'The predictions have been updated',
	'AWAY_PREDICTION'					=> 'Away Prediction',
	'HOME_PREDICTION'					=> 'Home Prediction',
	'ACP_SP_FINAL_SCORE'				=> 'Final Score',
	'SP_YOUR_PREDICTIONS'				=> 'Your Predictions',
	'FOR'								=> 'for',
	
	// nav
	'SPORTS_PREDICTIONS_HOME'	=> 'Footy Tipping Home',
	'MAKE_PREDICTIONS'			=> 'Add Tips',
	'EDIT_PREDICTIONS'			=> 'Edit Tips',
	
	// main
	'SPORTS_PREDICTIONS'	=> 'Footy Tipping',
	'PREDICT'				=> 'Tip',
	'LEADERBOARD'			=> 'Leaderboard',
	'LEADERBOARD_HEADER'	=> 'Leaderboard for',
	'WINS'					=> 'Wins',
	'LOSSES'				=> 'Losses',
	'WIN_PERC'				=> 'Win %',
	'POINTS'				=> 'Points',
	'POINT_DIFFERENTIAL'	=> 'PointDiff',
	'UPCOMING_GAMES'		=> 'Upcoming Games',
	'LEADERBOARD_KEY'		=> 'Leaderboard Key',
	'CLICK_TO_PREDICT'		=> 'Click here to tip',
	'MAKE_PREDICTIONS'		=> 'Make Tips',
	'YOUR_PREDICTION'		=> 'Your Tip',
	'WIN_PERC_SIGN'			=> '%',
	'FULL_LEADERBOARD_LINK'	=> 'View Full Leaderboard',
	'GAME'					=> 'Game',
	'NO_UPCOMING_GAMES'		=> 'No upcoming games',
	
	// predict
	'NO_GAMES_TO_PREDICT'		=> 'There are no games to tip',
	'NO_PREDICTIONS_TO_EDIT'	=> 'There are no tips to edit',
	
	// messages
	'NO_LEADERBOARD_DATA'			=> 'There is no data to create a leaderboard',
	'PREDICTION_UPDATE_SUCCESS'		=> 'You have successfully made your tips',
	'RETURN_SP_INDEX'				=> '%sClick here to return to the Footy Tipping Homepage%s',
	'EXACT_PREDICTION_EXPLAIN'		=> '+%d point(s) for exact tip',
	'CORRECT_PREDICTION_EXPLAIN'	=> '+%d point(s) for correct tip',
	'INCORRECT_PREDICTION_EXPLAIN'	=> '-%d point(s) for incorrect tip',
	'EXACT_PREDICTION'				=> 'Exact',
	'CORRECT_PREDICTION'			=> 'Correct',
	'INCORRECT_PREDICTION'			=> 'Incorrect',
	'POINTDIFF_EXPLAIN'				=> 'Point Difference:<br />This is a total of the difference between your tips and the actual scores.',
	'SP_LOGIN_REQUIRED'				=> 'You must be logged in to play Footy Tipping',
));
