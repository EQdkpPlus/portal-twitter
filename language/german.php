<?php
/*
 * Project:     EQdkp-Plus
 * License:     Creative Commons - Attribution-Noncommercial-Share Alike 3.0 Unported
 * Link:		http://creativecommons.org/licenses/by-nc-sa/3.0/
 * -----------------------------------------------------------------------
 * Began:       2008
 * Date:        $Date: 2009-03-16 12:02:35 +0100 (Mo, 16 Mrz 2009) $
 * -----------------------------------------------------------------------
 * @author      $Author: osr-corgan $
 * @copyright   2006-2008 Corgan - Stefan Knaak | Wallenium & the EQdkp-Plus Developer Team
 * @link        http://eqdkp-plus.com
 * @package     eqdkp-plus
 * @version     $Rev: 4235 $
 * 
 * $Id: german.php 4235 2009-03-16 11:02:35Z osr-corgan $
 */

if ( !defined('EQDKP_INC') ){
    header('HTTP/1.0 404 Not Found');exit;
}

$plang = array_merge($plang, array(
	'twitter'                 => 'Twitter',
	'pm_twitter_account'				=> 'Twitter-Account',
	'pm_twitter_maxitems'			=> 'Maximal angezeigte Tweeds (leer = unbegerenzt)',
	'pm_twitter_cachetime'			=> 'Stunden zum Cachen der Daten (in Stunden; Standard: 1 Std.)',
	'pm_twitter_follow'			=> 'Folge %s auf twitter',
	'pm_twitter_period'			=> array("Sekunde", "Minute", "Stunde", "Tag", "Woche", "Monat", "Jahr", "Jahrzehnt"),
	'pm_twitter_periods'		=> array("Sekunden", "Minuten", "Stunden", "Tagen", "Wochen", "Monaten", "Jahren", "Jahrzehnten"),
	'pm_twitter_tense'		=> array("von jetzt an", "vor"),
	'pm_twitter_format'		=>  '... %2$s %1$s',

));
?>
