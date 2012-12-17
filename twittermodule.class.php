<?php 
 /*
 * Project:     EQdkp-Plus
 * License:     Creative Commons - Attribution-Noncommercial-Share Alike 3.0 Unported
 * Link:		http://creativecommons.org/licenses/by-nc-sa/3.0/
 * -----------------------------------------------------------------------
 * Began:       24.12.2007
 * Date:        Date: 2009-05-30
 * -----------------------------------------------------------------------
 * @author      Author: osr-corgan
 * @modified    Author: godmodn 
 * @copyright   2006-2008 Corgan - Stefan Knaak | Wallenium & the EQdkp-Plus Developer Team
 * @link        http://eqdkp-plus.com
 * @package     eqdkp-plus
 */
 
class twittermodule
{


	/**
	 * Constructor
	 *
	 * @return rss
	 */
	function twittermodule()
	{
		global $eqdkp,$user, $conf_plus;
		
		$this->checkURL_first = true;
		$this->rssurl = "http://twitter.com/statuses/user_timeline/".$conf_plus['pm_twitter_account'].".xml";
		$this->parseXML($this->GetRSS($this->rssurl));

		if ($this->news)
		{
			$this->createTPLvar($this->news);
		}

	}

	/**
	 * GetRSS get the RSS Feed from an given URL
	 * Check if an refresh is needed
	 *
	 * @param String $url must be an valid RSS Feed
	 * @return XMLString
	 */
	
	function GetRSS($url)
	{
		global $db, $urlreader, $pdc, $conf_plus ;
		$rss_string = null;
		$cachetime = ($conf_plus['pm_twitter_cachetime']) ? ($conf_plus['pm_twitter_cachetime']*3600) : 3600;

		$rss_string = $pdc->get('portal.modul.rss-feed',false,true);
		
		if (!$rss_string) 
		{		
			$sql = "SELECT updated,rss FROM __module_twitter";
			$result = $db->query($sql);
			if($row = $db->fetch_record($result))
	    	{
	      		$this->updated = $row['updated'];
				
	      		if( (time() - $this->updated) > $cachetime )
				{
					$urlreader->checkURL_first = $this->checkURL_first;
					$rss_string = $urlreader->GetURL($url) ;
					$this->saveRSS($rss_string);	
				}elseif (isset($row['rss']) )
				{					
					$rss_string = @base64_decode($row['rss']) ;
					$rss_string = @gzuncompress($rss_string) ;
					$rss_string = @unserialize($rss_string);						
					$rss_string = stripslashes($rss_string);
				}
			}else 
			{
					$urlreader->checkURL_first = $this->checkURL_first;
					$rss_string = $urlreader->GetURL($url) ;
					$this->saveRSS($rss_string);				
			}
			$pdc->put('portal.modul.rss-feed',$rss_string,$this->cachetime-5,false,true);
		}
		return $rss_string ;
	}


	/**
	 * saveRSS
	 * Save the given RSS String into the Database
	 *
	 * @param String $rss
	 */
	 
	 function decodeRSS($rss){
		
				$rss_string = @base64_decode($rss);
				$rss_string = @gzuncompress($rss_string) ;
				$rss_string = @unserialize($rss_string);						
				$rss_string = stripslashes($rss_string);
				return $rss_string;
		
		}
	 
	 
	function saveRSS($rss)
	{
		global $db, $eqdkp;

		if (strlen($rss)>1)
		{
			$sql = "TRUNCATE TABLE __module_twitter ";
			$db->query($sql);

			$rss = addslashes($rss);
			$rss = @base64_encode(gzcompress(serialize($rss))) ;
			
			$sql = "INSERT INTO __module_twitter SET ".
				"  updated='".time()."'".
				",  rss='".$rss."'";

			$db->query($sql);
		}
	}

	/**
	 * parseXML
	 * parse the XML Data into an Array
	 *
	 * @param RSS-XML $rss
	 */
	function parseXML($rss)
	{
		global $eqdkp,$conf_plus, $eqdkp_root_path;

		include_once($eqdkp_root_path.'pluskernel/include/parser/xml_php5.php'); // Load for php5

		$parser = new XMLParser($rss, $id);
		if ($parser)
		{
			$parser->Parse();

			$this->news = array();

			if (is_array($parser->document->status))
			{
				foreach ($parser->document->status as $key => $value)
				{
					$this->news[$key]['text'] 			= utf8_decode($value->text[0]->tagData);
					$this->news[$key]['created_at'] 			=  $value->created_at[0]->tagData;

					$i++;

				}
			}

		}
	} # end function

	/**
	 * createTPLvar
	 *
	 * @param Array $news
	 * @return NewstickerArray
	 */
	function createTPLvar($news)
	{
		global $tpl, $eqdkp,$eqdkp_root_path,$user,$conf_plus,$user, $html,$jqueryp, $plang;

		if (is_array($news))
		{	
		
			$maxitems = ($conf_plus['pm_twitter_maxitems'] == "") ? count($news) : $conf_plus['pm_twitter_maxitems'];

			$bcout = '<table width=100% cellspacing="2" cellpadding="1">';

			for ($i=0; $i<$maxitems; $i++)
			{	
				 	$bcout .="<tr><td class=".$eqdkp->switch_row_class().">".$this->twitterify($news[$i]['text'])."<br>".$this->nicetime($news[$i]['created_at'])."</td></tr>";
						
			}
			$bcout .= '<tr><td class='.$eqdkp->switch_row_class().' align="center"><a target="_blank" href="http://twitter.com/'.$conf_plus['pm_twitter_account'].'"><img src="'.$eqdkp_root_path.'images/Twitter-icon-16.png">'.sprintf($plang['pm_twitter_follow'], $conf_plus['pm_twitter_account']).'</a></td></tr>';
      $bcout .= '</table>';


			$this->output_left = $bcout;


		}

		return $bcout;

	} # end function


function twitterify($ret) {
  $ret = preg_replace("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t< ]*)#", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $ret);
  $ret = preg_replace("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r< ]*)#", "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>", $ret);
  $ret = preg_replace("/@(\w+)/", "<a href=\"http://www.twitter.com/\\1\" target=\"_blank\">@\\1</a>", $ret);
  $ret = preg_replace("/#(\w+)/", "<a href=\"http://search.twitter.com/search?q=%23\\1\" target=\"_blank\">#\\1</a>", $ret);
return $ret;
}

function nicetime($date)
{
    global $plang;
		
		if(empty($date)) {
        return "No date provided";
    }
   

    $lengths         = array("60","60","24","7","4.35","12","10");
   
    $now             = time();
    $unix_date         = strtotime($date);
   
       // check validity of date
    if(empty($unix_date)) {   
        return "Bad date";
    }

    // is it future date or past date
    if($now > $unix_date) {   
        $difference     = $now - $unix_date;
        $tense         = $plang['pm_twitter_tense'][1];
       
    } else {
        $difference     = $unix_date - $now;
        $tense         = $plang['pm_twitter_tense'][0];
    }
   
    for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
        $difference /= $lengths[$j];
    }
   
    $difference = round($difference);
   
    if($difference != 1) {
        $period = $plang['pm_twitter_periods'][$j];
    } else {
			$period = $plang['pm_twitter_period'][$j];
		}

    return sprintf($plang['pm_twitter_format'], "$difference $period", $tense);
}


}// end of class

?>