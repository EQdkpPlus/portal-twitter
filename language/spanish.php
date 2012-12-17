<?php

if (!defined('EQDKP_INC')) {
	die('You cannot access this file directly.');
}

//Language: Spanish	
//Created by EQdkp Plus Translation Tool on  2010-07-09 15:10
//File: module_twitter
//Source-Language: english

$alang = array( 
"twitter" => "Twitter",
"pm_twitter_account" => "Cuenta de Twitter",
"pm_twitter_maxitems" => "Número máximo de Tweets mostrados ( vacio = sin límite )",
"pm_twitter_cachetime" => "Tiempo de caché en horas (por defecto: 1 hora)",
"pm_twitter_follow" => "seguir %s en twitter",
"pm_twitter_period" => array(
	"0" => "segundo",
"1" => "minuto",
"2" => "hora",
"3" => "día",
"4" => "semana",
"5" => "mes",
"6" => "año",
"7" => "década",
),
	"pm_twitter_periods" => array(
	"0" => "segundos",
"1" => "minutos",
"2" => "horas",
"3" => "días",
"4" => "semanas",
"5" => "meses",
"6" => "años",
"7" => "décadas",
),
	"pm_twitter_tense" => array(
	"0" => "desde ahora",
"1" => "hace",
),
	"pm_twitter_format" => "... %1$s %2$s",
 );
$plang = array_merge($plang, $alang);
?>