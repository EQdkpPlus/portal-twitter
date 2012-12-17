<?php

if (!defined('EQDKP_INC')) {
	die('You cannot access this file directly.');
}

//Language: French	
//Created by EQdkp Plus Translation Tool on  2010-07-09 13:55
//File: module_twitter
//Source-Language: english

$alang = array( 
"twitter" => "Twitter",
"pm_twitter_account" => "Compte Twitter",
"pm_twitter_maxitems" => "Nombre max de tweet affich�s (vide = aucune limite)",
"pm_twitter_cachetime" => "Temps de cache en heure (d�faut 1heure)",
"pm_twitter_follow" => "Suivre %s tweets",
"pm_twitter_period" => array(
	"0" => "Seconde",
"1" => "minute",
"2" => "heure",
"3" => "jour",
"4" => "semaine",
"5" => "mois",
"6" => "ann�e",
"7" => "d�c�nie",
),
	"pm_twitter_periods" => array(
	"0" => "secondes",
"1" => "minutes",
"2" => "heures",
"3" => "jours",
"4" => "semaines",
"5" => "mois",
"6" => "ann�es",
"7" => "d�c�nies",
),
	"pm_twitter_tense" => array(
	"0" => "� partir de maintenant",
"1" => "auparavant",
),
	"pm_twitter_format" => "... %1$s %2$s",
 );
$plang = array_merge($plang, $alang);
?>