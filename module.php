<?php
/*************************************************\
*             RSS-Feeds 4 EQdkp plus              *
* ----------------------------------------------- *
* Project Start: 05/2009                          *
* Author: GodMod                                  *
* Copyright: GodMod 				              *
* Link: http://eqdkp-plus.com/forum               *
* Version: 0.0.1a                                 *
* ----------------------------------------------- *
* License: Creative Commons - Attribution-Noncommercial-Share Alike 3.0 Unported
* License-Link: http://creativecommons.org/licenses/by-nc-sa/3.0/
\*************************************************/


if ( !defined('EQDKP_INC') ){
  header('HTTP/1.0 404 Not Found');exit;
}

$portal_module['twitter'] = array(
			'name'           => 'Twitter-Reader',
			'path'           => 'twitter',
			'version'        => '0.0.1',
			'author'         => 'GodMod',
			'contact'        => 'http://www.eqdkp-plus.com',
			'description'    => 'Shows a Module with Tweeds',
			'positions'      => array('left1', 'left2', 'right'),
      'signedin'       => '0',
      'install'        => array(
			      'autoenable'        => '0',
			      'defaultposition'   => 'left1',
			       'defaultnumber'     => '1',
						'customsql'			=> array(
											'CREATE TABLE IF NOT EXISTS __module_twitter (
  											`id` int(11) NOT NULL AUTO_INCREMENT,
  											`updated` int(11) NOT NULL DEFAULT \'0\',
  											`rss` text NOT NULL,
  											PRIMARY KEY (`id`))',
			                      		),
			                         ),
    );

// Settings
$portal_settings['twitter'] = array(
		
		'pm_twitter_account'	=> array(
		'name'  			=>	'pm_twitter_account',
		'language'			=>	'pm_twitter_account',
		'property'			=>	'text',
		'size'	   			=>	'30',
	),
		'pm_rssfeeds_maxitems'	=> array(
		'name'  			=>	'pm_twitter_maxitems',
		'language'			=>	'pm_twitter_maxitems',
		'property'			=>	'text',
		'size'	   			=>	'3',
	),
		
		'pm_twitter_cachetime'	=> array(
		'name'  			=>	'pm_twitter_cachetime',
		'language'			=>	'pm_twitter_cachetime',
		'property'			=>	'text',
		'size'	   			=>	'3',
	),
	
);


include_once($eqdkp_root_path . 'portal/twitter/twittermodule.class.php');

if(!function_exists(twitter_module))
{
  function twitter_module()
  {
  	global $eqdkp, $plang, $pcache, $pm, $conf_plus;
    
	
	$rss_feeds = new twittermodule();
	$output = $rss_feeds->output_left;
	return $output;
  }
}



?>
