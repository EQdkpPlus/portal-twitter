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
 
class twittermodule extends gen_class {
	public static $shortcuts = array('puf'=>'urlfetcher');

	public $output_left = '';
	public $news		= array();
	private $module_id = 0;
	private $cachetime = 3600;
	private $maxitems = 5;
	private $hideUserReplys = true;
	private $hideRetweets = false;
	
	/**
	 * Constructor
	 *
	 * @return rss
	 */
	public function twittermodule($id){
		$this->module_id = $id;
		$this->checkURL_first = true;
		$this->twitter_screenname = $this->config->get('account', 'pmod_'.$this->module_id);
		$this->cachetime = ($this->config->get('cachetime', 'pmod_'.$this->module_id)) ? ($this->config->get('cachetime', 'pmod_'.$this->module_id)*3600) : 3600;
		$this->maxitems = ($this->config->get('maxitems', 'pmod_'.$this->module_id)) ? ($this->config->get('maxitems', 'pmod_'.$this->module_id)) : 5;
		$this->hideUserReplys = ($this->config->get('hideuserreplys', 'pmod_'.$this->module_id)) ? ($this->config->get('hideuserreplys', 'pmod_'.$this->module_id)) : true;
		$this->hideRetweets = ($this->config->get('hideretweets', 'pmod_'.$this->module_id)) ? ($this->config->get('hideretweets', 'pmod_'.$this->module_id)) : false;
		
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
		$rss_string = $this->pdc->get('portal.module.twitter.id'.$this->module_id,false,true);
		
		if (!$rss_string){
			$sql = "SELECT updated,rss FROM __module_twitter WHERE id=?";
			$result = $this->db->prepare($sql)->execute($this->module_id);
			if($row = $result->fetchAssoc()){
				$this->updated = $row['updated'];
				if( (time() - $this->updated) > $this->cachetime ){
					//normal update
					$this->tpl->add_js('$.get("'.$this->server_path.'portal/twitter/update.php'.$this->SID.'&mid='.$this->module_id.'");');
					
					$rss_string = $row['rss'];
				}elseif (isset($row['rss']) ){
					$rss_string = $row['rss'];
				}
			}else{ //nothing in DB
				if ($this->twitter_screenname != ""){
					$this->tpl->add_js('$.get("'.$this->server_path.'portal/twitter/update.php'.$this->SID.'&mid='.$this->module_id.'");');
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
			$this->pdc->del('portal.module.twitter.id'.$this->module_id);
			$sql = "DELETE FROM __module_twitter WHERE id=?";
			$this->db->prepare($sql)->execute($this->module_id);
			
			$this->db->prepare("INSERT INTO __module_twitter :p")->set(array(
					'updated'	=> time(),
					'rss'		=> $rss_string,
					'id'		=> $this->module_id,
			))->execute();

			$this->pdc->put('portal.module.twitter.id'.$this->module_id,$rss_string,$this->cachetime-5,false,true);
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
				if ($this->hideUserReplys && strlen($item['in_reply_to_user_id'])) continue;
				if ($this->hideRetweets && isset($item['retweeted_status'])) continue;
				
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
			$table = '<table class="table fullwidth colorswitch border-top">';
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
			
			.tw_actions {
				float: right;
				display: none;
			}
					
			.tw_action_trigger:hover .tw_actions {
				display: inline;
			}
					
			.tw_actions div {
				float: right;
				cursor: pointer;
				padding-left: 4px;
				font-size: 16px;
			}
			
			.tw_time{
				float: left;
			}
			
			'
			);
			

			for ($i=0; $i<$this->maxitems; $i++){
					if (!isset($news[$i])) continue;
					
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
					
					$bcout .='<tr><td class="tw_action_trigger">'.$this->twitterify($news[$i]['text'])."<br />
					<div>
						<div class=\"tw_time\">
							<span class=\"small\">".$this->nicetime($news[$i]['created_at'])."</span>
						</div>
						<div class=\"tw_actions\">
							
							<div class=\"tw_action_favorit\" onclick=\"window.open('https://twitter.com/intent/favorite?tweet_id=".$data['id_str']."', '', 'width=500,height=350,modal=yes,left=100,top=50,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); return false;\" title=\"".$this->user->lang('pm_twitter_favorit')."\"><i class=\"fa fa-star\"></i>
							</div>
							<div class=\"tw_action_retweet\" onclick=\"window.open('https://twitter.com/intent/retweet?tweet_id=".$data['id_str']."', '', 'width=500,height=350,modal=yes,left=100,top=50,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); return false;\" title=\"".$this->user->lang('pm_twitter_retweet')."\"><i class=\"fa fa-retweet\"></i>
							</div>
							<div class=\"tw_action_reply\" onclick=\"window.open('https://twitter.com/intent/tweet?in_reply_to=".$data['id_str']."', '', 'width=500,height=350,modal=yes,left=100,top=50,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); return false;\" title=\"".$this->user->lang('pm_twitter_answer')."\"><i class=\"fa fa-reply\"></i>
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