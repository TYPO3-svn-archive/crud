<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009 Frank Thelemann
 *  Contact: f.thelemann@yellowmed.com
 *  All rights reserved
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 ***************************************************************/

/**
 * Depends on: lib/div
 *
 * typo3 hook for all cached page to parse all markers on the page and replace with the controller action contents
 * sets the typoscript configurations,params,models and views for the controller
 *
 * @author Frank Thelemann <f.thelemann@yellowmed.com>
 * @package TYPO3
 * @subpackage tx_crud
 */
final class tx_crud__parser{
	var $newContent;
	var $extras;

	// -------------------------------------------------------------------------------------
	// typo3 hook function for contentPostProc_output
	// -------------------------------------------------------------------------------------

	/**
	 * hook for replacing all markers on a page with controller content and class factory for models and views
	 *
	 * @param 	object	$params	the pObj
	 * @param 	string	$reference	the reference
	 * @return  string	the parsed html pages
	 */
	function contentPostProc_output(&$params, &$reference) {
		//if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip'))	        ob_start("ob_gzhandler");
	    ////else
	    //    ob_start();
		$start = microtime(true);
		//header('Content-Encoding: gzip');
		include_once(t3lib_extMgm::extPath('crud') . 'library/class.tx_crud__div.php');
		$hash = md5('crud-markers');
		
		if($_GET['return']>=1 OR $_POST['return']>=1) {
			tx_crud__redirect::prepare();
		}
		tx_crud__redirect::hasRedirect();

		$this->markers=tx_crud__cache::get($hash);
		//t3lib_div::debug($this->markers);
		if (isset($_REQUEST['ajax']) && isset($this->markers[$_REQUEST['aID']])) {
			$str = $this->markers[$_REQUEST['aID']];
			$this->parse($this->markers[$_REQUEST['aID']]);
			$content = $this->markerArray[$str];
			$this->parse($content);
			if (is_array($this->markerArray)) {
				foreach ($this->markerArray as $marker=>$html) {
					//$marker_exploded=explode("~",)
					$this->markers[tx_crud__div::getActionID('', $marker)] = $marker;
					$content = str_replace($marker, $html, $content);
				}
			}
			tx_crud__cache::write($hash, $this->markers);
			echo $content;
			$end = microtime(true);
			$time = $end - $start;
			if ($_REQUEST['showtime']) {
				echo "ajax request loaded in $time seconds\n";
			}
			die();
		} else {
			$this->parse($params['pObj']->content);
			$originalMarker = $this->markerArray;
			//t3lib_div::debug($orginalMarker);
			$this->markerArray = array();
			if (strlen($this->newContent) > 3) {
				//echo $this->newContent;
				$params['pObj']->content = $this->newContent;
				if (strlen($this->newContent) > 3) {
					//echo $this->actionID;
				}
				if (strlen($this->newContent) > 3) {
					$this->newContent = false;
					//echo 'second parse';
					$this->parse($params['pObj']->content);
					$params['pObj']->content = $this->newContent;
					
				}
			}
			//t3lib_div::Debug($this->headerdata);
			if (is_array($this->headerData)) {
				$params['pObj']->content = $this->makeHeader($this->headerData, $params['pObj']->content);
			}
			else {
				$params['pObj']->content = $this->makeHeader(array(),$params['pObj']->content);
			}
			if (is_array($this->footerData)) {
				$params['pObj']->content = $this->makeFooter($this->footerData,$params['pObj']->content);
			}
			//t3lib_div::Debug($this->markers);
			tx_crud__cache::write($hash, $this->markers);
			$end = microtime(true);
			$time = $end - $start;
			if ($_REQUEST['showtime']) {
				echo "normal parsed in $time seconds\n";
			}
		}
		//t3lib_div::debug($this->markers);

	}

	/**
	 * get and cached the typoscript configuation for an action
	 *
	 * @return  array	typoscript
	 */
	function setup() {
		//echo "setup";
		unset($this->mode);
		$marker = $this->marker;
		$hash = md5("pluginSetup".$marker);
		$marker = explode("~",$marker);
		//$setup = explode(".",$marker[3]);
		if (count($marker)==2) {
				$setup = explode('.', $marker[1]);
		} else {
			$setup = explode('.', $marker[3]);
		}
		$hash = md5('pluginSetup-' . $this->marker.$GLOBALS['TSFE']->fe_user->user['usergroup']);
		//t3lib_div::debug($marker);
		$cached = tx_crud__cache::get($hash);
		//t3lib_div::Debug($cached);
		$this->pageConfig=$cached['config'];
		if(is_array($cached['typoscript'])) {
			$typoscript=$cached['typoscript'];
		}
		//t3lib_div::debug($marker);
		$cache['config']=$cached['config'];
		if (!is_array($typoscript)) {
			define(PATH_t3lib,"t3lib/");
			//echo "generiere typscript for ".$this->marker;
			require_once(PATH_t3lib . 'class.t3lib_page.php');
			require_once(PATH_t3lib . 'class.t3lib_tstemplate.php');
			require_once (PATH_t3lib . 'class.t3lib_tsparser_ext.php');
			$sysPageObj = tx_div::makeInstance('t3lib_pageSelect');
			$rootLine = $sysPageObj->getRootLine($GLOBALS['TSFE']->id);
			$TSObj = tx_div::makeInstance('t3lib_tsparser_ext');
			$TSObj->tt_track = 0;
			$TSObj->init();
			$TSObj->runThroughTemplates($rootLine);
			$TSObj->generateConfig();
			//t3lib_div::debug($TSObj->setup['plugin.']);die();
			//t3lib_div::debug($setup,"setup");
			//t3lib_div::debug($marker,"marker");
			if(strtolower($marker[0])=="plugin") {
				$ts=explode(".",$marker[1]);
				$cache['typoscript'] = $TSObj->setup[$ts[0]."."][$ts[1].'.'];
			}
			else $cache['typoscript'] = $TSObj->setup[$setup[0]."."][$setup[1].'.'];
			//t3lib_div::Debug($TSObj->setup[$setup[0]."."],"ts");
			//die();
			$cache['typoscript']['configurations.']['setup.']['baseURL']=$cache['config']['baseURL'];
			$cache['config'] = $TSObj->setup['config.'];
			tx_crud__cache::write($hash,$cache);

			$typoscript=$cache['typoscript'];
			//t3lib_div::debug($typoscript);
			$this->pageConfig=$cache['config'];
		}
		//t3lib_div::debug($cache['typoscript']);
		if(isset($_REQUEST['q'])) $setup = $typoscript['configurations.']["autocompleteAction."];
		else $setup = $typoscript['configurations.'][$marker[0]."Action."];
		//t3lib_div::debug($setup);
		//$pars=tx_crud__div::_GP($setup['setup.']['extension']);
		if(strtolower($marker[0])=="plugin") return $setup;
		//t3lib_div::debug($cache['typoscript'],"ts");
		//if(!is_array($setup['aa.'])) $this->newContent="CRUD Parser has no Typscript found for ".implode("~",$marker)."! Please create Typoscript: ". $marker[3];
		$pars=tx_crud__div::_GP($setup['setup.']['extension']);
		//t3lib_div::debug($pars,"vorher");
		if(strlen($pars["action"]) >= 3 && $pars['action'] != $marker[0]  && $setup['setup.']['freezeAction']!='1') {
			$marker[0] = $pars['action'];
			$setup = $typoscript['configurations.'][$marker[0]."Action."];
			//t3lib_div::debug($setup);
		}
		$this->actionID=$setup['storage.']['action'];
		$_checkNodeType = explode(".",$marker[2]);
		//t3lib_div::debug($setup);
		if ($_checkNodeType[0] == "PARS") {
			$marker[2] = $pars[strtolower($_checkNodeType[1])];
		}
		if (isset($pars['retrieve'])  && !isset($pars['action'])) {
			$marker[0] = "retrieve";
			$setup = $typoscript['configurations.'][$marker[0]."Action."];
			$setup['storage.']['nodes'] = $pars['retrieve'];
			//t3lib_div::debug($setup);
		}

		elseif(isset($marker[2])) $setup['storage.']['nodes']=$marker[2];
		elseif(isset($pars['retrieve'])) $setup['storage.']['nodes'] = $pars['retrieve'];
		if(strlen($setup['storage.']['nameSpace'])<=3) $setup['storage.']['nameSpace']= strtolower($marker[1]);
		if(strlen($marker[4])>1) $setup['storage.']['fields']=$marker[4];
		if (strlen($marker[5]) > 1) {
			$defaults = explode(",",$marker[5]);
			if (is_array($defaults)) {
				foreach ($defaults as $default) {
					$singleDefault_exploded = explode("=",$default);
					$setup['storage.']['defaultQuery.'][strtolower($marker[1])."."][$singleDefault_exploded[0]] = $singleDefault_exploded[1];
				}
			}
		}
		//t3lib_div::debug($setup);
		$setup_ok = $setup;
		if (is_array($setup['storage.']['virtual.'])) {
			foreach ($setup['storage.']['virtual.'] as $key=>$val) {
				if ($key != strtolower($setup['storage.']['nameSpace']) . ".") {
					unset($setup_ok['storage.']['virtual.'][$setup['storage.']['nameSpace']]);
				}
			}
		}
		if (is_array($setup['storage.']['modifications.'])) {
			foreach($setup['storage.']['modifications.'] as $key=>$val) {
				if ($key != strtolower($setup['storage.']['nameSpace']) . ".") {
					unset($setup_ok['storage.']['modifications.'][$setup['storage.']['nameSpace']]);
				}
			}
		}
		if(isset($_SERVER['HTTPS'])) $setup_ok['setup.']['baseURL']="https://".t3lib_div::getThisUrl();
		else $setup_ok['setup.']['baseURL']="http://".t3lib_div::getThisUrl();
		//t3lib_div::Debug($setup_ok);
	//	die();
		return $setup_ok;
	}

	/**
	 * search and replace content marker with the contoller action
	 *
	 * @param	$content	the content with the markers
	 * @return  void
	 */
	function parse($content) {
		unset($this->mode);
		$html = explode('{{{',$content);
		$replace = array();

		if(is_array($html))foreach($html as $key=>$val) {
			$needle = '}}}';
			$marker =  substr("$val",0,strpos($val,$needle)+strlen($needle));
			$marker = str_replace('}}}','',$marker);
			$test = explode("~",$marker);
			$str = '{{{' . $marker . '}}}';
			//echo $str;
			if (count($test) >= 2 ) $this->markers[tx_crud__div::getActionID("",$str)]=$str;
			if (count($test) >= 2 || strtolower($test[0])=="plugin"){
				$this->marker = $marker;
				$setup = $this->setup();
				$setup['setup.']['marker'] = $str;
				//t3lib_div::debug($setup);
				$mode = $this->setSubmit($setup);
				if ($test[0] == "create" || $test[0] == "update" || $test[0] == "delete") {
					$icon = true;
				}
				if(count($setup['storage.'])<=4 && strtolower($test[0])!="plugin") {
						$this->newContent="No Storage defined";
						$this->isError=true;
						echo str_replace($str,"<b>CRUD Typscript Error! No correct TS storage section defined in marker ".$str."</b>",$content);
						die();
				}
				if(count($setup['view.'])<=3) {
						$this->newContent="No View defined";
						$this->isError=true;
						echo str_replace($str,"<b>CRUD Typoscript Error! No correct TS view section defined in marker ".$str."</b>",$content);
						die();
				}
				if(count($setup['setup.'])<=3  && strtolower($test[0])!="plugin") {
						$this->newContent="No Storage defined";
						$this->isError=true;
						echo str_replace($str,"<b>CRUD Typoscript Error! No correct TS setup section defined in marker ".$str."</b>",$content);
						die();
				}
				if(count($setup['controller.'])<2  && strtolower($test[0])!="plugin") {
						$this->newContent="No Storage defined";
						$this->isError=true;
						echo str_replace($str,"<b>CRUD Typoscript Error! No correct TS controller section defined in marker ".$str."</b>",$content);
						die();
				}
				$pars=tx_crud__div::_GP($setup['setup.']['extension']);
				session_start();
				if($pars['track'] && strtoupper($pars['action'])!="RETRIEVE") {
	                $hash=md5($_REQUEST['PHPSESSID']."-".$setup['setup.']['marker']."-".$GLOBALS['TSFE']->id);
	            	$params=$_SESSION[$hash];
	                if(is_array($params)) foreach($params as $name=>$values) $pars[$name]=$values;
	            }
				if(strlen($pars['search'])>2 && !is_array($pars['search'])) {
					$search=explode(" (",$pars['search']);
					$pars['search']=urldecode($search[0]);
				}
				if ($mode == "ICON" && $icon  && $setup['icons.']['useParserIcons']=='1') { //TODO internal action icons parsen
					$replace[$str] = tx_crud__div::printActionLink($setup);;
					//t3lib_div::debug($setup,$marker);
				} elseif ($mode == "HIDE" && $setup['hideIfNotActive']!="1") {
					$replace[$str] = "";
				} else {
					if (is_array($setup)) {
						if (isset($setup['controller.']['className']) && !is_object($this->obj[$setup['controller.']['className']])) {
							require_once($setup['controller.']['classPath']);
							$this->obj[$setup['controller.']['className']]=new $setup['controller.']['className'];
						}
						if (isset($setup['storage.']['className']) && !is_object($this->obj[$setup['storage.']['className']])) {
							require_once($setup['storage.']['classPath']);
							$this->obj[$setup['storage.']['className']]= new  $setup['storage.']['className'];
						}
						if (isset($setup['view.']['className']) && !is_object($this->obj[$setup['view.']['className']])) {
							//echo $setup['view.']['classPath'];
							require_once($setup['view.']['classPath']);
							//t3lib_div::debug($setup['view.']);
							$this->obj[$setup['view.']['className']] = new $setup['view.']['className'];
						}
						if (isset($setup['controller.']['className'])) {
							//if(strtolower($test[0]) != "plugin")
							$this->obj[$setup['controller.']['className']]->configurations = new tx_lib_object($setup);
							//else $this->obj[$setup['controller.']['className']]->configurations = new tx_lib_object($setup['view.']);
						//	t3lib_div::debug($pars,'ss');
							$this->obj[$setup['controller.']['className']]->parameters = new tx_lib_object($pars);
							$this->obj[$setup['controller.']['className']]->defaultDesignator = strtolower($setup['setup.']['extension']);
							if(isset($_REQUEST['q'])) $pars['action']="autocomplete";
							if (strlen($pars["action"]) >= 3 && $pars['action']!=strtolower($marker[0]) && !isset($setup['setup.']['freezeAction'])) {
								$action = $pars['action'] . "Action";
							}
							else {
							$action = $test[0] . "Action";
							}
							$this->obj[$setup['controller.']['className']]->action = $action;
						}
						//t3lib_div::debug($_REQUEST);
						if(strtolower($test[0]) != "plugin" && !isset($_REQUEST['q']))
						{
							$this->obj [$setup ['storage.'] ['className']]->setup ( $this->obj [$setup ['controller.'] ['className']] );
							$this->obj [$setup ['view.'] ['className']]->setup ( $this->obj [$setup ['controller.'] ['className']] );
							$this->obj [$setup ['controller.'] ['className']]->model = $this->obj [$setup ['storage.'] ['className']];
							$this->obj [$setup ['controller.'] ['className']]->view = $this->obj [$setup ['view.'] ['className']];
							$replace [$str] = $this->obj [$setup ['controller.'] ['className']]->$action ();
							//die();
							if(!isset($_REQUEST['q'])) {
								$this->obj [$setup ['storage.'] ['className']]->destruct ( $this->obj [$setup ['controller.'] ['className']] );
								$this->obj [$setup ['view.'] ['className']]->destruct ( $this->obj [$setup ['controller.'] ['className']] );
								if (is_array ( $this->obj [$setup ['view.'] ['className']]->headerData )) {
									$headerData [] = $this->obj [$setup ['view.'] ['className']]->headerData;
								}
								if (is_array ( $this->obj [$setup ['view.'] ['className']]->footerData )) {
									$footerData [] = $this->obj [$setup ['view.'] ['className']]->footerData;
								}
								if (is_array ( $this->obj [$setup ['model.'] ['className']]->headerData )) {
									$headerData [] = $this->obj [$setup ['model.'] ['className']]->headerData;
								}
								if (is_array ( $this->obj [$setup ['model.'] ['className']]->footerData )) {
									$footerData [] = $this->obj [$setup ['mode;.'] ['className']]->footerData;
								}
								if (is_array ( $this->obj [$setup ['view.'] ['className']]->replace )) {
									$replaceData = $this->obj [$setup ['view.'] ['className']]->replace;
								}
								if ($marker == 'DELETE') {
									$extras [$marker] = $action [1];
								}
							}
							//t3lib_div::debug($headerData);
						}
						else {
							if(isset($_REQUEST['q'])) $action="autocomplete";

							if (isset($setup['controller.']['className']))  {
								if(isset($_REQUEST['q'])) {
									$action="autocompleteAction";
									$this->obj [$setup ['storage.'] ['className']]->setup ( $this->obj [$setup ['controller.'] ['className']] );
									$this->obj [$setup ['controller.'] ['className']]->model = $this->obj [$setup ['storage.'] ['className']];

								}
								else $action = strtolower($test[0]) . "Action";
								$replace [$str] = $this->obj [$setup ['controller.'] ['className']]->$action ();
							    //if (is_array ( $this->obj [$setup ['view.'] ['className']]->headerData )) {
								//	$headerData [] = $this->obj [$setup ['view.'] ['className']]->headerData;
								//}
							}
						}
					}
				}
			}
		}
		if (is_array($replace) && !isset($_REQUEST['ajax'])) {
			foreach($replace as $key=>$val) {
				$content = str_replace($key,$val,$content);
			}
		}
		//t3lib_div::debug($replaceData);
		if(is_array($replaceData) && !isset($_REQUEST['ajax'])) {
			foreach($replaceData as $field=>$val) {
				if(isset($val['splitter'])) {
					$split=explode("|",$val['splitter']);
					if($val['style']=="append") {
						$do=" - ".$val['value'].$split[1];
						//$do=strip_tags($do);
						$content=str_replace($split[1],$do,$content);
					}
					elseif($val['style']=="prepend") {
						$do=$split[0].$val['value']." - ";
						//$do=strip_tags($do);
						$content=str_replace($split[0],$do,$content);
					}
				}
				elseif(isset($val['tagvalueXX']))  {
					$what = explode(":",$val['tagvalue']);
					$tag = $what[0];
					//if($what[1]=='content') {
						$value = substr(strip_tags($val['value']),0,130)."...";
						$replace = $GLOBALS['TSFE']->page['description'];
						//t3lib_div::debug($GLOBALS['TSFE']->page,$value);
						$content=str_replace($replace,$value,$content);

					//}
				}
			}
		}
		$this->markerArray=$replace;
		if (is_array($headerData)) {
			foreach($headerData as $key=>$array) {
				foreach($array as $name=>$val) {
					foreach($val as $name2=>$val2)  $this->headerData[$name][$name2]=$val2;
				}
			}
		}
	
		if (is_array($footerData)) {
			foreach($footerData as $key=>$array) {
				foreach($array as $name=>$val) {
					foreach($val as $name2=>$val2)  $this->footerData[$name][$name2]=$val2;
				}
			}
		}
		if (is_array($replace) || is_array($headerData) || is_array($footerData)) {
			$this->newContent = $content;
		}
		//die();
	}

	/**
	 * write header data like css or javscripts to the page header
	 *
	 * @param	array $headerData	the data for the header
	 * @param 	string	$content	the html page
	 * @return  string the htmlpage with the added header data
	 */
	function makeHeader($headerData,$content) {
		$headerData=$headerData;
		if (is_array($headerData)) {
			foreach ($headerData as $k=>$include) {
				if (is_array($include)) {
					$replace = TRUE;
					//t3lib_div::debug($include, $k);
					/*if($k == 'libraries') {
						$controllScript=fopen(md5($include).".txt",'r');//wohin?
						fseek($mainScript,0);
					}*/
					$lineCount=0;
					$writeScript=false;
					foreach ($include as $key=>$value) {
						if($k=='libraries') {
							/*$pathToScript=substr($value,strpos($value,'src="')+5, -11);
							$javaScript=file_get_contents($pathToScript);
							// datei noch nich da
							if(!is_file(md5($pathToScript.strlen($javascript)).'.js')) {
								$jsFile=fopen(md5($pathToScript.strlen($javascript)).".js",'w+');//wohin?
								fseek($jsFile,0);//safety
								$jsPack=t3lib_div::minifyJavaScript($javaScript, $error);
								if(strlen($jspack)!=fwrite($jsFile,$jspack))
									error;
								$line[$lineCount]=gets($controllScript);
								if($line[$lineCount]!='// '.$pathToScript.'('.strlen($javaScript).')') {
									$writeScript=true;
									//
									if(substr($line[$lineCount],$pathToScript)
								}
								else {

								}
							}
							else {
								$jsPack=file_get_contents(md5($pathToScript.strlen($javascript)).'.js');
							}


*/
						}
						//else
						$additionalHeader .= "" . $value . "\n\t";
					}
					//if($k == 'libraries') {
					//	$additionalHeader .= '<script type="text/javascript" src="'.md5($include).'.js"></script>';//wohin?
					//	fclose($mainScript);
					//}
				}
			}
		}
		if ($js) {
			$additionalHeader .= $js;
		}
		$replace=true;
		$redirect= explode("/",$_SERVER['REDIRECT_URL']);

		if(isset($_SERVER['HTTPS'])) $url="https://".t3lib_div::getThisUrl();
		else$url="http://".t3lib_div::getThisUrl();
		//$url_exploded=explode("/",$url);
		//t3lib_div::debug($url_exploded);
		//foreach($url_exploded as $val) if(strlen($val)>=1) $params1[$val]=$val;
		//foreach($redirect as $val) if(strlen($val)>=1) $params2[$val]=$val;
		//t3lib_div::debug($params1);
	//	t3lib_div::debug($params2);
		//if(is_array($params2)) foreach($params2 as $val) {
		//	if(strlen($val)>=1) {
		//		///if($next) $url.= $val."/";
		//		if(isset($params1[$val])) {
		//			$next=true;
		//		}
		//
		//	}
		//}
		$additionalHeader.='
<script type="text/javascript">
function getBaseurl(){
	return "'.$url.'";
}
</script>';
		if ($replace) {
			$content = str_replace('</head>',$additionalHeader."</head>",$content);
		}
		return $content;
	}

	/**
	 * write footer data like css or javscripts to the page footer
	 *
	 * @param	array $headerData	the data for the header
	 * @param 	string	$content	the html page
	 * @return  string the htmlpage with the added footer data
	 */
	function makeFooter($headerData,$content) {
		$headerData=$headerData;
		if (is_array($headerData)) {
			foreach ($headerData as $k=>$include) {
				if (is_array($include)) {
					$replace = TRUE;
					foreach ($include as $key=>$value) {
						$additionalHeader .= "" . $value . "\n\t";
					}
				}
			}
		}
		if ($js) {
			$additionalHeader .= $js;
		}
		if ($replace) {
			$content = str_replace('</body>',$additionalHeader."</body>",$content);
		}
		return $content;
	}

	/**
	 * set submit state
	 *
	 * @param	array $setup	the configuration setup
	 * @return  string the mode
	 */
	function setSubmit($setup) {
		//t3lib_div::Debug($setup);
		$pars=tx_crud__div::_GP($setup['setup.']['extension']);
		if ($pars["form"] == tx_crud__div::getActionID($setup)) {
			if ($pars["process"] == "preview") {
				$this->submit = true;
				$this->mode = "PROCESS";
			} elseif ($pars["process"] == "create") {
				$this->submit = false;
				$this->mode = "EDIT";
			} elseif ($pars["process"] == "update") {
				$this->submit = true;
				$this->mode = "EDIT";
			} elseif ($pars["process"]=="delete") {
				$this->submit = true;
				$this->mode = "PROCESS";
			} elseif ($pars["process"]=="cancel") {
				$this->submit = false;
				$this->mode = "ICON";
			} else {
				$this->submit = false;
				$this->mode = "ICON";
			}
		} else {
			if ($pars["form"] && !$pars["cancel"]) {
				$this->submit = false;
				$this->mode = "HIDE";
			} else {
				$this->submit = false;
				if($setup['storage.']['action']=="create" || $setup['storage.']['action']=="update" ) $this->mode = "ICON";
			}
		}
		//if($this->mode!="ICON") $this->mode="";
		return $this->mode;
	}

	function setSubmitXX($setup) {

		if ($this->controller->parameters->get ( "form" ) == tx_crud__div::getActionID ( $setup )) {

			if ($this->controller->parameters->get ( "process" ) == "preview") {
				$this->submit = true;
				$this->mode = "PROCESS";
			} elseif ($this->controller->parameters->get ( "process" ) == "create") {
				$this->submit = false;
				$this->mode = "EDIT";
			} elseif ($this->controller->parameters->get ( "process" ) == "update") {
				if ($this->controller->parameters->get ( "icon" ) == "1") {
					$this->submit = false;
					$this->mode = "EDIT";
				} else {
					$this->submit = true;
					$this->mode = "EDIT";
				}
			} elseif ($this->controller->parameters->get ( "process" ) == "delete") {
				$this->submit = true;
				$this->mode = "PROCESS";
			} elseif ($this->controller->parameters->get ( "process" ) == "cancel") {
				$this->submit = false;
				$this->mode = "ICON";
			} else {
				$this->submit = false;
				$this->mode = "ICON";
			}
		} else {

			if ($this->controller->parameters->get ( "form" ) && ! $this->controller->parameters->get ( "cancel" )) {
				$this->submit = false;
				$this->mode = "HIDE";
			} else {
				$this->submit = false;
				$this->mode = "ICON";
			}
		}

		return $this->mode;;
		//echo "mode set crud submit:".$this->mode;
	}
}

?>