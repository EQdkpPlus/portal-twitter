<?php
/*	Project:	EQdkp-Plus
 *	Package:	Twitter Portal Module
 *	Link:		http://eqdkp-plus.eu
 *
 *	Copyright (C) 2006-2015 EQdkp-Plus Developer Team
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU Affero General Public License as published
 *	by the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU Affero General Public License for more details.
 *
 *	You should have received a copy of the GNU Affero General Public License
 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if ( !defined('EQDKP_INC') ){
	header('HTTP/1.0 404 Not Found');exit;
}

class twitter_portal extends portal_generic {

	protected static $path		= 'twitter';
	protected static $data		= array(
		'name'			=> 'Twitter-Reader',
		'version'		=> '0.2.7',
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
		'hideretweets' => array(
			'type'		=> 'radio',
			'default'	=> 0,
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
		`rss` mediumtext COLLATE utf8_bin NOT NULL,
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
