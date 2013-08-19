<?php
 /*
 * Project:		EQdkp-Plus
 * License:		Creative Commons - Attribution-Noncommercial-Share Alike 3.0 Unported
 * Link:		http://creativecommons.org/licenses/by-nc-sa/3.0/
 * -----------------------------------------------------------------------
 * Began:		2009
 * Date:		$Date: 2012-12-17 12:22:26 +0100 (Mo, 17. Dez 2012) $
 * -----------------------------------------------------------------------
 * @author		$Author: godmod $
 * @copyright	2006-2011 EQdkp-Plus Developer Team
 * @link		http://eqdkp-plus.com
 * @package		eqdkp-plus
 * @version		$Rev: 12601 $
 * 
 * $Id: twittermodule.class.php 12601 2012-12-17 11:22:26Z godmod $
 */
 
class twittermodule extends gen_class {
	public static $shortcuts = array('core', 'user', 'tpl', 'pdc', 'db', 'config', 'puf'=>'urlfetcher');

	public $output_left = '';
	public $news		= array();

	/**
	 * Constructor
	 *
	 * @return rss
	 */
	public function twittermodule(){
		$this->checkURL_first = true;
		$this->twitter_screenname = $this->config->get('pm_twitter_account');
		$this->parseJSON($this->GetRSS($this->twitter_screenname));

		if (is_array($this->news) && count($this->news) > 0){
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
	public function GetRSS($url){
		$rss_string = null;
		$cachetime = ($this->config->get('pm_twitter_cachetime')) ? ($this->config->get('pm_twitter_cachetime')*3600) : 3600;

		$rss_string = $this->pdc->get('portal.module.twitter',false,true);
		
		if (!$rss_string){
			$sql = "SELECT updated,rss FROM __module_twitter";
			$result = $this->db->query($sql);
			if($row = $this->db->fetch_record($result)){
				$this->updated = $row['updated'];
				if( (time() - $this->updated) > $cachetime ){
					//normal update
					$this->tpl->add_js('$.get("'.$this->server_path.'portal/twitter/update.php");');
					
					$rss_string = $row['rss'];
				}elseif (isset($row['rss']) ){
					$rss_string = $row['rss'];
				}
			}else{ //nothing in DB
				if ($this->config->get('pm_twitter_account') != ""){
					$this->tpl->add_js('$.get("'.$this->server_path.'portal/twitter/update.php'.$this->SID.'");');
				}
				return false;
			}
		}
		return $this->decodeRSS($rss_string) ;
	}

	/**
	 * saveRSS
	 * Save the given RSS String into the Database
	 *
	 * @param String $rss
	 */
	 
	public function decodeRSS($rss){
		$rss_string = unserialize($rss);
		return $rss_string;
	}

	public function updateRSS(){
		$cachetime = ($this->config->get('pm_twitter_cachetime')) ? ($this->config->get('pm_twitter_cachetime')*3600) : 3600;
		
		include_once($this->root_path.'libraries/twitter/codebird.class.php');
		Codebird::setConsumerKey(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET); // static, see 'Using multiple Codebird instances'

		$cb = Codebird::getInstance();
		$cb->setReturnFormat(CODEBIRD_RETURNFORMAT_ARRAY);
		$cb->setToken(TWITTER_OAUTH_TOKEN, TWITTER_OAUTH_SECRET);
		$params = array(
			'screen_name' => $this->twitter_screenname,
		);
		$objJSON = $cb->statuses_userTimeline($params);
		
		$rss_string = serialize($objJSON);

		if (strlen($rss_string)>1){
			$this->pdc->del('portal.module.twitter');
			$sql = "TRUNCATE TABLE __module_twitter ";
			$this->db->query($sql);

			$sql = "INSERT INTO __module_twitter SET ".
				"  updated='".$this->db->escape(time())."'".
				",  rss='".$this->db->escape($rss_string)."'";

			$this->db->query($sql);
			$this->pdc->put('portal.module.twitter',$rss_string,$cachetime-5,false,true);
		}
	}

	/**
	 * parseXML
	 * parse the XML Data into an Array
	 *
	 * @param RSS-XML $rss
	 */
	public function parseJSON($json){
		$i = 0;
		if (is_array($json)){
			foreach ($json as $item){
				$this->news[$i]['text'] 		=  $item['text'];
				$this->news[$i]['created_at']	=  $item['created_at'];
				$this->news[$i]['data']			=  $item;
				$i++;
			}
		}
	} # end function

	/**
	 * createTPLvar
	 *
	 * @param Array $news
	 * @return NewstickerArray
	 */
	public function createTPLvar($news){
		if (is_array($news)){
			$maxitems = ($this->config->get('pm_twitter_maxitems') == "") ? count($news) : $this->config->get('pm_twitter_maxitems');
			$table = '<table width="100%" cellspacing="0" cellpadding="2" class="colorswitch border-top">';
			$bcout = "";
			
			$this->tpl->add_css(
			'
			.tw_header{
				padding: 2px;
			}
			.tw_logo img{
				height: 32px;
				width: 32px;
				border-radius: 4px;
			}
			.tw_logo{
				float: left;
			}
			.tw_names{
				float: left;
				margin-left: 3px;
			}
			.tw_name{
				font-weight: bold;
			}
			
			.tw_follow{
				float: right;
				margin-top:6px;
			}
			
			.tw_action_reply {
				background-image: url('.$this->server_path.'portal/twitter/images/everything-spritev2.png);
				background-position: 0px 0px;
				width: 16px;
				height: 16px;
				cursor: pointer;
				float: right;
				margin-left: 5px;
			}
			.tw_action_reply:hover {
				background-position: -16px 0px;
			}
			
			.tw_action_retweet {
				background-image: url('.$this->server_path.'portal/twitter/images/everything-spritev2.png);
				background-position: -80px 0px;
				width: 16px;
				height: 16px;
				cursor: pointer;
				float: right;
				margin-left: 5px;
			}
			.tw_action_retweet:hover {
				background-position: -96px 0px;
			}
			
			.tw_action_favorit {
				background-image: url('.$this->server_path.'portal/twitter/images/everything-spritev2.png);
				background-position: -32px 0px;
				width: 16px;
				height: 16px;
				cursor: pointer;
				float: right;
				margin-left: 5px;
			}
			.tw_action_favorit:hover {
				background-position: -48px 0px;
			}
			
			.tw_actions {
				float: right;
			}
			
			.tw_time{
				float: left;
			}
			
			'
			);
			

			for ($i=0; $i<$maxitems; $i++){
					$data = $news[$i]['data'];	
					
					$author ='
					<div class="tw_header">
						<div class="tw_logo">
							<a href="https://twitter.com/'.sanitize($data['user']['screen_name']).'" target="_blank">
							<img src="'.sanitize($data['user']['profile_image_url_https']).'" alt="'.sanitize($data['user']['screen_name']).'" />
							</a>
						</div>
						<div class="tw_names">
							<div class="tw_name">
							<a href="https://twitter.com/'.sanitize($data['user']['screen_name']).'" target="_blank">
							'.sanitize($data['user']['name']).'
							</a>
							</div>
							<div class="tw_screenname">
							<a href="https://twitter.com/'.sanitize($data['user']['screen_name']).'" target="_blank">
							@'.sanitize($data['user']['screen_name']).'
							</a>
							</div>
						</div>
						<div class="tw_follow">
							    <a href="https://twitter.com/'.sanitize($data['user']['screen_name']).'" class="twitter-follow-button" data-show-count="false" data-show-screen-name="false" data-lang="'.$this->user->lang('XML_LANG').'">Follow @twitterapi</a>

    <script type="text/javascript">!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
						</div>
						<div class="clear"></div>
					</div>
					';
					
					$bcout .='<tr><td>'.$this->twitterify($news[$i]['text'])."<br />
					<div>
						<div class=\"tw_time\">
							<span class=\"small\">".$this->nicetime($news[$i]['created_at'])."</span>
						</div>
						<div class=\"tw_actions\">
							
							<div class=\"tw_action_favorit\" onclick=\"window.open('https://twitter.com/intent/favorite?tweet_id=".$data['id_str']."', '', 'width=500,height=350,modal=yes,left=100,top=50,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); return false;\" title=\"".$this->user->lang('pm_twitter_favorit')."\">&nbsp;
							</div>
							<div class=\"tw_action_retweet\" onclick=\"window.open('https://twitter.com/intent/retweet?tweet_id=".$data['id_str']."', '', 'width=500,height=350,modal=yes,left=100,top=50,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); return false;\" title=\"".$this->user->lang('pm_twitter_retweet')."\">&nbsp;
							</div>
							<div class=\"tw_action_reply\" onclick=\"window.open('https://twitter.com/intent/tweet?in_reply_to=".$data['id_str']."', '', 'width=500,height=350,modal=yes,left=100,top=50,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); return false;\" title=\"".$this->user->lang('pm_twitter_answer')."\">&nbsp;
							</div>
						</div>
						<div class=\"clear\"></div>
					</div>
						</td></tr>";
			}
			$bcout = $author.$table.$bcout;
			
			$bcout .= '</table>';
			$this->output_left = $bcout;
		}
		return $bcout;
	} # end function

	public function twitterify($ret) {
		$ret = preg_replace("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t< ]*)#u", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $ret);
		$ret = preg_replace("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r< ]*)#u", "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>", $ret);
		$ret = preg_replace("/@(\w+)/u", "<a href=\"https://www.twitter.com/\\1\" target=\"_blank\">@\\1</a>", $ret);
		$ret = preg_replace("/#(\w+)/u", "<a href=\"https://twitter.com/search?q=%23\\1&amp;src=hash\" target=\"_blank\">#\\1</a>", $ret);
		return $ret;
	}

	public function nicetime($date){
		if(empty($date)) {
			return "No date provided";
		}

		$lengths	= array("60","60","24","7","4.35","12","10");
		$now		= time();
		$unix_date	= strtotime($date);

		// check validity of date
		if(empty($unix_date)) {   
			return "Bad date";
		}

		// is it future date or past date
		if($now > $unix_date) {   
			$difference		= $now - $unix_date;
			$tense			= $this->user->lang(array('pm_twitter_tense', 1));
		}else{
			$difference		= $unix_date - $now;
			$tense			= $this->user->lang(array('pm_twitter_tense', 0));
		}

		for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
			$difference /= $lengths[$j];
		}

		$difference = round($difference);
		if($difference != 1) {
			$period	= $this->user->lang(array('pm_twitter_periods', $j));
		}else{
			$period	= $this->user->lang(array('pm_twitter_period', $j));
		}

		return sprintf($this->user->lang('pm_twitter_format'), "$difference $period", $tense);
	}
}// end of class
?>