<?php
 /*
 * Project:		EQdkp-Plus
 * License:		Creative Commons - Attribution-Noncommercial-Share Alike 3.0 Unported
 * Link:		http://creativecommons.org/licenses/by-nc-sa/3.0/
 * -----------------------------------------------------------------------
 * Began:		2008
 * Date:		$Date: 2012-12-14 15:34:31 +0100 (Fr, 14. Dez 2012) $
 * -----------------------------------------------------------------------
 * @author		$Author: godmod $
 * @copyright	2006-2011 EQdkp-Plus Developer Team
 * @link		http://eqdkp-plus.com
 * @package		eqdkp-plus
 * @version		$Rev: 12585 $
 * 
 * $Id: twitter_portal.class.php 12585 2012-12-14 14:34:31Z godmod $
 */

if ( !defined('EQDKP_INC') ){
	header('HTTP/1.0 404 Not Found');exit;
}

class twitter_portal extends portal_generic {

	protected static $path		= 'twitter';
	protected static $data		= array(
		'name'			=> 'Twitter-Reader',
		'version'		=> '0.1.0',
		'icon'			=> 'fa-twitter',
		'author'		=> 'GodMod',
		'contact'		=> EQDKP_PROJECT_URL,
		'description'	=> 'Shows a Module with Tweeds',
		'lang_prefix'	=> 'twitter_',
		'multiple'		=> true,
	);
	protected static $positions = array('left1', 'left2', 'right');
	
	protected static $apiLevel = 20;
	
	protected $settings	= array(
		'account'	=> array(
			'type'		=> 'text',
			'size'		=> '30',
		),
		'maxitems'	=> array(
			'type'		=> 'text',
			'size'		=> '3',
			'default'	=> 3,
		),
		'cachetime'	=> array(
			'type'		=>	'text',
			'size'		=>	'3',
		),
		'hideuserreplys' => array(
			'type'		=> 'radio',
			'default'	=> true,
		),
	);
	protected static $install	= array(
		'autoenable'		=> '0',
		'defaultposition'	=> 'left1',
		'defaultnumber'		=> '1',
	);
	protected static $tables	= array('module_twitter');
	protected static $sqls		= array(
		'CREATE TABLE IF NOT EXISTS __module_twitter (
		`id` int(11) NOT NULL DEFAULT \'0\',
		`updated` int(11) NOT NULL DEFAULT \'0\',
		`rss` text COLLATE utf8_bin NOT NULL,
		PRIMARY KEY (`id`)
		) DEFAULT CHARSET=utf8 COLLATE=utf8_bin',
	);

	public function output() {	
		include_once($this->root_path . 'portal/twitter/twittermodule.class.php');
		$rss_feeds = registry::register('twittermodule', array($this->id));
		$this->header = $this->user->lang('twitter');
		return $rss_feeds->output_left;
	}

	public static function reset() {
		register('pdc')->del('portal.module.twitter');
		register('db')->query("TRUNCATE __module_twitter;");
	}
}
?>