<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2006 Frank Thelemann
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
 * @author Frank Thelemann <f.thelemann@yellowmed.com>
 * @package TYPO3
 * @subpackage tx_crud
 */


//tx_div::load('tx_lib_phpTemplateEngine');
class tx_crud__views_common extends tx_lib_phpTemplateEngine {

	private $form;
	public $viewAction;
	private $html;
	private $extTranslator;
	private $functions;

	/**
	 * Setup of the View and checking for Search Array in the User Session
	 *
	 * @return	void
	 */
	function __construct(&$controller) {
		$this->controller = $controller;
		$config = $this->controller->configurations->getArrayCopy();
		$pars = $this->controller->parameters->getArrayCopy();
		$view = $config['view.'];
		parent::__construct($controller);
		//t3lib_div::debug($config);
		$this->set("setup",$view['setup']);
		$this->set("data",$view['data']);
		$this->set("errors",$view['errors']);
		$this->set("mode",$view['mode']);
		$this->set("nameSpace",$view['nameSpace']);
		$this->set("action",$view['action']);
		$this->set("backValues",$view['backValues']);
		$this->set("keyOfPathToLanguageFile",$config['view.']['keyOfPathToLanguageFile']);
		$this->generateUrls();
		if (is_array($pars['search']) && !$pars['track']) {
			$hash=md5($_REQUEST['PHPSESSID']."-".$config['setup.']['marker']."=".$GLOBALS['TSFE']->id);
			$_SESSION[$this->getSessionHash()]['search']=$pars['search'];
		}
			
	}

	
	
	// -------------------------------------------------------------------------------------
	// URL Rendering
	// -------------------------------------------------------------------------------------

	/**
	 * Setup of all possible URL Parameters
	 *
	 * Generates an URL with all possible Pars with tx_lib_link
	 * and then this url will cached. so we need only one time the rendering and
	 * it make no performance leaks if you have a lot of links in your template
	 *
	 * @return	void
	 */
	private function generateUrls() {
		$get=$_GET;
		$config = $this->controller->configurations->getArrayCopy();
		unset($get['id']);
		unset($get['cHash']);
		unset($get[$this->getDesignator()]);
		unset($get['ajaxTarget']);
		unset($get['ajax']);
		$params['page']='PAGE';
		$params['limit']='LIMIT';
		$params['search']='SEARCH';
		$params['upper']='UPPER';
		$params['lower']='LOWER';
		$params['retrieve']='RETRIEVE';
		$params['action']='ACTION';
		$params['track']='TRACK';
		$hash=md5($config['setup.']['marker']);
		foreach($params as $key=>$val) $params_keys[strtoupper($val)]=strtoupper($val);
		if(stristr($_SERVER['REQUEST_URI'],"index.php")) $hash.="norealurl".$GLOBALS['TSFE']->config['config']['sys_language_uid'];
		else $hash.="withrealurl".$GLOBALS['TSFE']->config['config']['sys_language_uid'];
		$url=tx_crud__cache::get($hash);
		//echo $url;
		if(!$url) {
			require_once(PATH_site.'typo3/sysext/cms/tslib/class.tslib_content.php');
			$link = tx_div::makeInstance('tx_lib_link');
			$link->parameters($params);
			$link->designator($this->getDesignator());
			$link->destination($this->getPageId());
			$url = $link->makeURL();
			 tx_crud__cache::write($hash,$url);
		}
		$url_exploded=explode(".",$url);
		if(!is_array($cash))
		if($url_exploded[0]=="index") {
			$url_exploded=explode("&amp;",$url);
			$this->baseUrl=$url_exploded[0];
			unset($url_exploded[0]);
			foreach($url_exploded as $key=>$val) {
				$val=str_replace($this->getDesignator()."[","",$val);
				$val=str_replace("]","",$val);
				$val_exploded=explode("=",$val);
				if($params_keys[$val_exploded[1]])$this->urlExtParameters[$val_exploded[0]]=$val_exploded[0];
				else $this->urlBaseParameters[$val_exploded[0]]=$val_exploded[1];
			}
			if(is_array($get)) foreach($get as $key=>$val) if($key!=$this->getDesignator())$this->urlBaseParameters[$key]=$val;
			$this->urlLimiter="&";
			$this->realUrl=false;

		}
		else{
			unset($get['L']);
			unset($get['no_cache']);
			$this->urlLimiter="/";
			$this->realUrl=true;
			$url_exploded=explode("/",$url);
			$url_exploded=array_reverse($url_exploded);
			foreach($url_exploded as $key=>$val) {
				if(strlen($val)>=1) $url_ok[]=$val;
			}
			$url_exploded=$url_ok;
			if(stristr($url_exploded[0],"?")) {
				unset($url_exploded[0]);
				$new_url=$url_exploded;
				$url_exploded=array();
				foreach($new_url as $key=>$val)$url_exploded[]=$val;
			}
			$url_ok=$url_exploded;
			foreach($url_ok as $key=>$val) {
				if($useNext)$this->urlExtParameters[$last]=$val;
				if(!$useNext && !$params_keys[$val]) $base[$val]=$val;
				if(strlen($params_keys[$val])>=1) {
					$useNext=true;
					$last=strtolower($val);
				}
				else $useNext=false;
			}
			if(is_array($base)) {
				$base=array_reverse($base);
				$this->baseUrl=implode("/",$base);
			}
			if(is_array($get)) foreach($get as $key=>$val) if($key!=$this->getDesignator())$this->urlBaseParameters[$key]=$val;

		}
	}

	/**
	 * Build a URL and return them
	 *
	 * Based on $this->urlExtParameters and $this->urlBaseParameters wich came from generateUrls()
	 * it returns a correct url with your Parameters.
	 *
	 * @param	array		$_GET pars for your URL
	 * @return	string		the complete URL
	 */
	function getUrl($pars=false) {
		unset($this->urlBaseParameters['cHash']);
		if(is_array($pars['search'])){
			unset($pars['search']);
		}
		if($pars['ajaxTarget']) {
			$this->urlBaseParameters['ajaxTarget']=$pars['ajaxTarget'];
			//unset($pars['ajaxTarget']);
		}
		$i=0;
		$url=$this->baseUrl;
		if($this->realUrl)$url.="/";
		if(is_array($pars)) foreach($pars as $key=>$val){
			if($this->urlExtParameters[$key]) {
				if($this->realUrl) {
					$url.=$this->urlExtParameters[$key]."/".$val."/";
				}
				else {
					$i++;
					$url.="&".$this->getDefaultDesignator()."[".$this->urlExtParameters[$key]."]=".$val;
				}
			}
		}
		if(is_array($this->urlBaseParameters)) foreach($this->urlBaseParameters as $key=>$val){
			if($this->realUrl) {
				if(is_array($val)) foreach($val as $k=>$v) {
					if($i==0){
						$url.="?".$key."[".$k."]=".$v;
						$i++;
					}
					else $url.="&".$key."[".$k."]=".$v;

				}

				elseif($i==0) {
					$url.="?".$key."=".$val;
					$i++;
				}
				else $url.="&".$key."=".$val;
			}
			elseif($i==0 && !$this->realUrl){
				if(is_array($val)) foreach($val as $k=>$v) {
					if($i==0) $url.="&".$key."[".$k."]=".$v;
					else $url.="&".$key."[".$k."]=".$v;
					$i++;

				}
				else $url.="&".$key."=".$val;
			}
			else {
				if(is_array($val)) foreach($val as $k=>$v) {
					$url.="&".$key."[".$k."]=".$v;
				}
				else $url.="&".$key."=".$val;
			}
			$i++;
		}
		return $url;
	}

	/**
	 * Builds an Link 
	 *
	 * @param	array		$_GET pars for your URL
	 * @param	strin		Label for your Link
	 * @return	string		the complete Link
	 */
	public function getTag($label,$pars=false) {
		$link = '<a href="'.$this->getUrl($pars).'">'.$label.'</a>'; // TODO: title-tag?
		return $link;
	}


	// -------------------------------------------------------------------------------------
	// CREATE/UPDATE  API
	// -------------------------------------------------------------------------------------

	
	function printAsFormHeader() {
		$setup = $this->controller->configurations->getArrayCopy();
		if (!is_object($this->form)) {
			$formEngineClassName = tx_div::makeInstanceClassName("tx_crud__formBase");
			$this->form = new $formEngineClassName($this->controller);
			$this->form->setup = $this->get("setup");
			$this->form->controller = $this->controller;
		}
		echo $this->form->begin($this->getDesignator(),array("name"=>$this->getDesignator()));
	}

	function printAsFormReset() {
		$code = '<input name="'.$this->getDesignator().'[cancel]" type="button" value="cancel" onclick="location.replace(\'\')" />'; //TODO: Localization
		$this->html['cancel'] = $code;
		echo $code;
	}

	function printAsFormCancel() {
		$form = "\n\t" . '<form name=crud-cancel" method="post" action=""><div>';
		$form .= "\n\t" . '<input type="hidden" name="ajaxTarget" value="' . $this->getAjaxTarget("printAsFormCancel") . '" />';
		$conf = $this->controller->configurations->getArrayCopy();
		$form .= "\n\t" . '<input type="hidden" name="' . $this->getDesignator() . '[form]" value="' . $this->getActionID($storage['nameSpace'],$storage['nodes'],$storage['fields']) . '" />';
		$form .= "\n\t" . '<input type="submit" name="' . $this->getDesignator() . '[cancel]" value="Cancel" />
		</div></form>'; //TODO: Localization
		echo $form;
	}

	function printBackLink($label="Back",$ajaxTarget=1,$pid=false) {
		$pars = $this->controller->parameters->getArrayCopy();
		$data=$pars;
		unset($data['action']);
		unset($data['retrieve']);
		if($pars['track']>=1)$data['track']=1;
		if ($this->page >= 1) {
			$data['page'] = $this->page;
		}
		$data['ajaxTarget'] = $this->getAjaxTarget("printbackLink");
		$out = '<form action="'.$this->getUrl($data).'" method="post"><div>
				<input type="submit" value="'.$label.'" />';
		$out .= '<input type="hidden" name="ajaxTarget" value="'.$this->getAjaxTarget("printBackLink").'" />';
		$out .= "</div></form>";
		echo $out;
	}

	function printAsFormSubmit() {
		$image ='typo3conf/ext/crud/resources/icons/preview.gif';
		$form = '<input type="hidden" name="ajaxTarget" value="'.$this->getAjaxTarget("printAsFormSubmit").'" />';
		$conf = $this->controller->configurations->getArrayCopy();
		$tinymce = $conf['view.']['tinymce.'];
		$datepicker = $conf['view.']['datepicker.'];
		$storage = $conf['storage.'];
		$form .= '<input type="hidden" name="' . $this->getDesignator() . '[form]" value="' . $this->getActionID($storage['nameSpace'],$storage['nodes'],$storage['fields']) . '" />';
		$form .= '<input type="hidden" name="' . $this->getDesignator() . '[process]" value="preview" />';
		//TODO: Localization
		$form .= '<input type="submit" name="' . $this->getDesignator() . '[submit]" value="Send" alt="Submit" />';
		$conf = $this->get("setup");
		if ($tinymce['enable'] == 1 && is_array($conf)) {
			foreach ($conf as $key=>$val) {
				if ($val['element'] == "rteRow") {
					$rte['default.'][$key] = $tinymce['default.'];
				} elseif ($val['element'] == "textareaRow" || $val['element'] == "textarea") {
					$rte['noRTE'][$key] = $key;
				}
			}
		}
		if (is_array($rte['default.'])) {
			//$GLOBALS['TSFE']->additionalHeaderData[] = '<script language="javascript" type="text/javascript" src="typo3conf/ext/crud/resources/tiny_mce/tiny_mce.js"></script>';
//			$this->headerData['libraries']['tinymce'] = '<script language="javascript" type="text/javascript" src="typo3conf/ext/crud/resources/tiny_mce/tiny_mce.js"></script>';
			//debug($tinymce);
			unset($tinymce['enable']);
			$tiny = '<script>function enableTinyMCE(){ ';
//			$tiny = '<script>';
			foreach ($tinymce as $key=>$al) {
				unset($tinymce[$key]['cols']);
				unset($tinymce[$key]['rows']);
				unset($tinymce[$key]['fields']);
				$tiny .='tinyMCE.init({';
				if(is_array($tinymce[$key])) {
					foreach($tinymce[$key] as $key2=>$val2) {
						$tiny .= '' . $key2 . ' : "' . $val2 . '",';
					}
				}
				$key = str_replace(".","",$key);
				$tiny .= 'editor_selector : "tinymce_' . $key . '"});';
			}
			$tiny .= "} \nenableTinyMCE();</script>";
//			$tiny .= '</script>';
			$GLOBALS['TSFE']->additionalFooterData[] = $tiny;
			//$this->footerData['call']['tiny'] = $tiny;
		}
		echo $form . $tiny;
	}
	
	function printAsFormFooter() {
		$code = '</form>';
		echo $code;
	}
	
	function addPlainEntry($entry,$content) {
		$this->html[$entry['divider']][$entry['section']][$entry['key']]['html'] = $content;
	}

	function getByType($item_key,$item_value) {
		$typoscript=$this->controller->configurations->getArrayCopy();
		$setup=$typoscript['view.']['setup'][$item_key];
	
			$values=explode(",",$item_value);
			foreach($values as $key=>$value) {
				if(strlen($this->getLL($value))>=1) $item_values[]=$this->getLL($value);
			}
		
		if(is_array($item_values)) $item_value=implode(",",$item_values);
		return $item_value;
	}
	
	function getExitLink($label) {
		$pars = $this->controller->parameters->getArrayCopy();
		$link = tx_div::makeInstance('tx_lib_link');
		$link->label($label, 1);
		$link->designator($this->getDesignator());
		if (!$pid) {
			$link->destination($GLOBALS['TSFE']->id);
		} else {
			$link->destination($pid);
		}
		$out = '<a href="'.$link->makeUrl().'">'.$label.'</a>';
		return  $out;
	}
	
	// -------------------------------------------------------------------------------------
	// SETUP API
	// -------------------------------------------------------------------------------------

	function renderSetup($entryList) {
		$setup = $this->controller->configurations->getArrayCopy();
		$pars=$this->controller->configurations->getArrayCopy();
		$hash=md5($setup['setup.']['marker']."-FORM");
		
		//if(!$pars['submit']) $this->html=tx_crud__cache::get($hash);
		//t3lib_div::debug($this->html);
		if (!is_array($this->html)) {
			foreach ($entryList as $key=>$entry) {
				$this->renderEntry($entry);
				
			}
			
		}
		if(!$pars['submit'])tx_crud__cache::write($hash,$this->html);
		return $this->html;
	}

	function loadHeaderData($root,$what) {
		$conf = $this->controller->configurations->getArrayCopy();
		$this->headerData[$root][$what] = $conf['resources.'][$root.'.'][$what];
	}

	function loadFooterData($root,$what) {
		$conf = $this->controller->configurations->getArrayCopy();
		$this->footerData[$root][$what] = $conf['resources.'][$root.'.'][$what];
	}

	function enableTabs($entryList,$call) {
		$tab = 1;
		foreach ($entryList as $divider=>$dividers) {
			foreach ($dividers as $section=>$sections) {
				foreach ($sections as $key=>$entry) {
					if ($entry['error'])  {
						$tabError = $tab;
						break;
					}
				}
				if ($tabError) {
					break;
				}
			}
			if ($tabError) {
				break;
			} else {
				$tab++;
			}
		}

		if ($tabError) {
			$tab = $tabError;
		} else {
			$tab = 1;
		}
		$js = "<script>
				function enableTabs(){\n\t";
		$js .= $call . '.tabs(' . $tab . ');' . "\n";
		$js .= '};' . "\n" . '</script>';
		echo $js;
	}

	function renderEntry($entry) {
		$start = microtime(true);
		$setup = $this->controller->configurations->getArrayCopy();
		if (!is_object($this->form)) {
			$formEngineClassName = tx_div::makeInstanceClassName("tx_crud__formBase");
			$this->form = new $formEngineClassName($this->controller);
			$this->form->setup = $this->get("setup");
			$this->form->controller = $this->controller;
		}
		$entry['label'] = $this->getLL($entry['label'],$entry['key']);
		if (is_array($entry['options.'])) {
			foreach ($entry['options.'] as $key=>$val) {
				$entry['options.'][$key] = $this->getLL($val,$key);
			}
		}
		if (is_array($entry['attributes.']['options.'])) {
			foreach ($entry['attributes.']['options.'] as $key=>$val) {
				$entry['attributes.']['options.'][$key] = $this->getLL($val,$key,1);
			}
		}
		if (!$entry['divider'] || $entry['divider'] == "General") {
			$entry['divider'] = '%%%' . strtolower($this->viewAction) . '%%%' . " " . $this->getLL($setup['view.']['title']);
		}

		$label = $entry['label'];

		$eval_exploded = explode(",",$entry['config.']['eval']);
		foreach ($eval_exploded as $key=>$val) {
			$eval[$val] = $val;
		}

		if ($entry['error']) {
			$entry['error'] = $this->getFormError($entry['error'],$entry['key']);
		}
		$this->html[$entry['divider']][$entry['section']][$entry['key']] = $entry;
		if ($eval['captcha']) {
			$this->html[$entry['divider']][$entry['section']][$entry['key']]['html'] = $this->form->captchaRow($entry['key'],$label,$entry['attributes.']);
		} elseif ($entry['element'] == "inputRow") {
			$this->html[$entry['divider']][$entry['section']][$entry['key']]['html'] = $this->form->inputRow($entry['key'],$label,$entry['attributes.']);
		} elseif ($entry['element'] == "dateTimeRow") {
			$this->html[$entry['divider']][$entry['section']][$entry['key']]['html'] = $this->form->dateTimeRow($entry['key'],$label,$entry['attributes.']);
		} elseif ($entry['element'] == "passwordRow") {
			$this->html[$entry['divider']][$entry['section']][$entry['key']]['html'] = $this->form->passwordRow($entry['key'],$label,$entry['attributes.']);
		} elseif ($entry['element'] == "multicheckbox") {
			$this->html[$entry['divider']][$entry['section']][$entry['key']]['html'] = $this->form->multicheckbox($entry['key'],$label,$entry['attributes.']);
		} elseif ($entry['element'] == "rteRow") {
			$this->html[$entry['divider']][$entry['section']][$entry['key']]['html'] = $this->form->rteRow($entry['key'],$label,$entry['attributes.']);
		} elseif ($entry['element'] == "radio") {
			$this->html[$entry['divider']][$entry['section']][$entry['key']]['html'] = $this->form->radio($entry['key'],$label,$entry['attributes.']);
		} elseif ($entry['element'] == "checkboxRow") {
			$this->html[$entry['divider']][$entry['section']][$entry['key']]['html'] = $this->form->checkboxRow($entry['key'],$label,$entry['attributes.']);
		} elseif ($entry['element'] == "selectRow") {
			$this->html[$entry['divider']][$entry['section']][$entry['key']]['html'] = $this->form->selectRow($entry['key'],$label,$entry['attributes.'],$entry['options.']);
		} elseif ($entry['element'] == "multiselectRow") {
			$this->html[$entry['divider']][$entry['section']][$entry['key']]['html'] = $this->form->multiselectRow($entry['key'],$label,$entry['attributes.'],$entry['options.']);
		} elseif ($entry['element'] == "textareaRow") {
			$this->html[$entry['divider']][$entry['section']][$entry['key']]['html'] = $this->form->textareaRow($entry['key'],$label,$entry['attributes.']);
		} elseif ($entry['element'] == "fileRow") {
			$this->html[$entry['divider']][$entry['section']][$entry['key']]['html'] = $this->form->fileRow($entry['key'],$label,$entry['attributes.']);
		} elseif ($entry['element'] == "noFileRow") {
			$this->html[$entry['divider']][$entry['section']][$entry['key']]['html'] = $this->form->noFileRow($entry['key'],$label,$entry['attributes.']);
		} elseif ($entry['element'] == "multiFileRow") {
			$this->html[$entry['divider']][$entry['section']][$entry['key']]['html'] = $this->form->multiFileRow($entry['key'],$label,$entry['attributes.']);
		} else {
			return FALSE;
		}
		if ($entry['key'] != "captcha" && $entry['element']!="multiFileRow") {
			$html_exploded = explode("<dd>",$this->html[$entry['divider']][$entry['section']][$entry['key']]['html']);
			$html = str_replace("</dd>","",$html_exploded[1]);
			$this->html[$entry['divider']][$entry['section']][$entry['key']]['html'] = str_replace("\n","",$html);
		}
		$stop = microtime(true);
	}

	// -------------------------------------------------------------------------------------
	// Translate handling
	// -------------------------------------------------------------------------------------

	function getError($localLangKey,$key=false) {
		$string = @explode(':',$localLangKey);
		$config = $this->controller->configurations->getArrayCopy();
		$path = $string[1] . ":" . $string[2];
		if($path!="EXT:crud/locallang.xml")$pathFallback = "EXT:crud/locallang.xml";
		$action = $this->controller->action;
		$table = $config['storage.']['nameSpace'];
		if ($string[0] == "LLL") {
			$what[0] = $table . "." . $action . "." . $key . "." . $string[3];
			$what[1] = $table . "." . $key . "." . $string[3];
			$what[2] = $key . "." . $string[3];
			$what[3] = $string[3];
			foreach($what as $key=>$str) {
				$LLL="LLL:".$path.":".$str;
				if($translated=$this->getLL($LLL)) break;
			}
			if(!$translated  && $pathFallback) {
				$translated=$this->getLL("LLL:".$pathFallback.":".$what[1]);
			}
		}
		if($translated) return $translated;
		else return false;
	}

	function getFormError($str,$key) {
		$config = $this->controller->configurations->getArrayCopy();
		$LLL = "LLL:" . $config['view.']['keyOfPathToLanguageFile'] . ":" . $str;
		$str = $this->getError($LLL,$key);
		return $str;
	}
	
	function getLLfromKey($key,$path=false,$force=false) {
		$config=$this->controller->configurations->getArrayCopy();
		if(!$path)$LLL="LLL:".$config['view.']['keyOfPathToLanguageFile'].":".$key;
		else $LLL="LLL:".$path.":".$key;
		$str=$this->getLL($LLL,$key);
		if(!$force && $str!=$LLL)return $str;
		elseif($force) return $key;
		else return false;
	}

	function getLL($localLangKey,$force=false) {
		$string = @explode(':',$localLangKey);
		$config = $this->controller->configurations->getArrayCopy();
		$hash=md5($string[1].$string[2]);
		if(!is_array($this->LL[$hash])) {
			$localLang=tx_crud__cache::get($hash);
			$this->LL[$hash]=$localLang;
		}
		else $localLang=$this->LL[$hash];
		$LLL[] = $localLangKey;
		if (!is_array($localLang)) {
			foreach($LLL as $num=>$str) {
				$action = $this->get("action");
				$string = @explode(':',$str);
				$path = $string[1] . ':' . $string[2];
				if (!is_object($this->extTranslator)) {
					$this->extTranslator = tx_div::makeInstance('tx_lib_translator');
				} else {
					$this->extTranslator->LOCAL_LANG_loaded=false;
				}
				if ($string[0] == "LLL" && !$LLKey) {
					$this->extTranslator->setPathToLanguageFile($path);
					if (!file_exists($this->extTranslator->getPathToLanguageFile())) {
						$type = explode(".",$string[2]);
						if ($type[1] == "php") {
							$path = str_replace("php","xml",$path);
						}
						$this->extTranslator->setPathToLanguageFile($path);
					}
					if (file_exists($this->extTranslator->getPathToLanguageFile())) {
						$this->extTranslator-> _loadLocalLang();
						if(is_array($this->extTranslator->LOCAL_LANG)) {
							tx_crud__cache::write($hash,$this->extTranslator->LOCAL_LANG);
							$localLang=$this->extTranslator->LOCAL_LANG;
							$this->LL[$hash]=$localLang;
						}
					}
				}
			}
		}
		if(is_array($localLang)) {
			$language=$GLOBALS['TSFE']->config['config']['language'];
			if ($localLang[$language][$string[3]]) {
				$translated =  $localLang[$language][$string[3]];
			}
			elseif ($localLang["default"][$string[3]]) {
				$translated =  $localLang['default'][$string[3]];
			}
			else {
				if ($string[3] == "pages.doktype") {
					$translated = "Pagetype";
				} elseif($string[3] == "pages.title") {
					$translated =  "Title";
				}
			}
		}
		if (!empty($translated)) {
			return $translated;
		} else {
			if($force) return $localLangKey;
			else return false;
		}
	}
	
	function getEvalConfig($str,$key=false) {
		$setup = $this->controller->configurations->getArrayCopy();
		$pars = $this->controller->parameters->getArrayCopy();
		if ($setup['view.']['setup'][$key]['config.']["internal_type"] == "file") {
			if (is_array($_FILES[$this->getDesignator()]['name'][$key])) {
				foreach($_FILES[$this->getDesignator()]['name'][$key] as $uid=>$file) {
					if (!$setup['view.']['setup'][$key][$uid] && strlen($file) >= 2) {
						$files[] = $file;
					}
				}
			}

			if(strlen($files) >= 1) {
				$files = implode(", ",$files);
			}
			$setup[$key]['config.']['filename'] = $files;
		}
		$ts = $this->controller->configurations->getArrayCopy();
		$split = $ts["view."]["datepicker."]['default.']["Date."]['splitter'];
		if (strlen($ts["view."]["datepicker."]['default.']["Date."]['format']) > 2) {
			$format=explode($split,$ts["view."]["datepicker."]['default.']["Date."]['format']);
		}
		$eval = explode(",",$setup[$key]['config.']['eval']);
		foreach ($eval as $k) {
			if (!empty($k)) {
				$evalTCA[$k] = $k;
			}
		}
		if (is_array($setup[$key]['config.'])) {
			foreach ($setup[$key]['config.'] as $name=>$val) {
				if (is_array($val) && $name == "range") {
					foreach ($val as $name2=>$val2) {
						$marker = "###" . strtoupper($name2) . "###";
						$str_old = $str;
						if ($name2 == "upper" || $name2 == "lower" && is_numeric($val2)) {
							if ($evalTCA['datetime'] || $evalTCA['date']) {
								$value = date("d.m.Y",$val2); // TODO: Datumsformat anpassen?
							} else {
								$value = $val2;
							}
						}
						$str = str_replace($marker,$value,$str_old);
					}
				} else {
					$marker = "###" . strtoupper($name) . "###";
					$str_old = $str;
					if ($name == "max_size") {
						$val = $val/100;
					}
					$str = str_replace($marker,$val,$str_old);
				}
			}
		}
		return $str;
	}
	
	function checkForLLL() {
		$config = $this->controller->configurations->getArrayCopy();
		$config_new = $config;
		foreach ($config['view.']['setup'] as $key=>$val) {
			$config_new['view.']['setup'][$key]['preview'] = $this->getLL($val['preview']);
			$config_new['view.']['setup'][$key]['value'] = $this->getLL($val['value']);
		}
		$this->controller->configurations = new tx_lib_object($config_new);
		$this->set("setup",$config_new['view.']['setup']);
	}

	// -------------------------------------------------------------------------------------
	// HELPER API
	// -------------------------------------------------------------------------------------
	
	function printAsImageXXXX($item_key,$height=60,$width=60,$maxImages=100,$lightbox=1,$wrapAll="",$wrapImage="") {
		$setup=$this->setup;
		//debug($setup);
		$img = $item_key;
		if (strlen($img) > 5) {
			$img = explode(",",$img);
		}
		$wrapImage = explode("|",$wrapImage);
		if (is_array($img)) {
			$i = 0;
			foreach ($img as $key=>$val) {
				$url = "uploads/".$val;
				//debug($_SERVER['DOCUMENT_ROOT']."/dev/".$url);
				if (file_exists($url) && $i < $maxImages) {
					//$setup=$this->get("setup");
					$size = getimagesize($url);
					if ($size[1] > $height) {
						$size[1] = $height;
					}
					if ($size[0] > $width) {
						$size[0] = $width;
					}
					$imageClassName = tx_div::makeInstanceClassName('tx_lib_image');
					$image = new $imageClassName();
					$image->alt($setup[$key]['label']);//TODO img label
					$image->maxWidth($size[0]);
					$image->maxHeight($size[1]);
					$image->path($url);
					if ($lightbox) {
						//$link = '<a href="'.$url.'" rel="lightbox[lb26]">';
						$images .= '<a href="' . $url . '" rel="lightbox[lb26]">' . $wrapImage[0] . $image->make() . $wrapImage[1] . '</a>'; //FIXME: das [lb26] ist eigtl. eine dynamische sache: # des Bildes auf der Seite
					} else {
						$images .= $wrapImage[0] . $image->make() . $wrapImage[1];
					}
					unset($image);
				}
				$i++;
			}
			$wrapAll = explode("|",$wrapAll);
			if ($images) {
				echo $wrapAll[0] . $images . $wrapAll[1];
			}
		}
	}
	
	function printAsImage($item_key,$height=30,$width=30,$maxImages=100,$lightbox=1,$wrapAll="",$wrapImage="") {
		$setup = $this->setup;
		$pars = $this->controller->parameters->getArrayCopy();
		$img = $item_key;
		$wrapImage = explode("|",$wrapImage);
		t3lib_div::debug($img);
		if (is_array($img)) {
			$i = 0;
			foreach ($img as $key=>$val) {
				$url = $val;
				if (file_exists($url) && $i < $maxImages) {
					$size = getimagesize($url);
					if ($size[1] > $height) {
						$size[1] = $height;
					}
					if ($size[0] > $width) {
						$size[0] = $width;
					}
					require_once(PATH_site.'t3lib/class.t3lib_stdgraphic.php');
					require_once(PATH_site.'typo3/sysext/cms/tslib/class.tslib_gifbuilder.php');
					$imageClassName = tx_div::makeInstanceClassName('tx_lib_image');
					$image = new $imageClassName();
					$image->alt($setup[$key]['label']);//TODO img label
					$image->maxWidth($size[0]);
					$image->maxHeight($size[1]);
					$image->path($url);
					if ($lightbox) {
						$images .= '<a href="' . $url . '" rel="lightbox[lb26]">' . $wrapImage[0] . $image->make() . $wrapImage[1] . '</a>'; //TODO: [lb26]
					} else {
						$images .= $wrapImage[0] . $image->make() . $wrapImage[1];
					}
					unset($image); 
				} else {
					echo "%%%error_no-image%%%";
				}
				$i++;
			}
			$wrapAll = explode("|",$wrapAll);
			if ($images) {
				return $wrapAll[0] . $images . $wrapAll[1];
			}
		}
	}
	
	
	function getSessionHash() {
        $config = $this->controller->configurations->getArrayCopy();
	    return md5($_REQUEST['PHPSESSID']."-".$config['setup.']['marker']."-".$GLOBALS['TSFE']->id);
	}

	//////////////////////////////////////////////////////////////////////////////
	//////////////////////////////INTERNAL////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////

	private function getBackValues() {;
	if (is_array($GET))  {
		if (isset($GET['id'])) {
			unset($GET['id']);
		}
		unset($GET[$this->getDesignator()]);
		if (is_array($GET)) {
			foreach ($GET as $key=>$val) {
				if (!is_array($val)) {
					$this->backValues['_GET'][$key] = $val;
				} else {
					foreach ($val as $k=>$v) $this->backValues['_GET'][$key][$k] = $v;
				}
			}
		}
	};
	unset($POST[$this->getDesignator()]);
	if (is_array($POST))  {
		foreach ($POST as $key=>$val) {
			$this->backValues['_POST'][$key] = $val;
		}
	}
	return $this->backValues;
	}



	function getList() {
		//$this->parameters=t3lib_div::_GP("crud");
		if ($this->controller->parameters->get('limit')) {
			$this->pageBrowser = $this->controller->parameters->get('limit');
		}
		if ($this->controller->parameters->get('lower')) {
			$data['lower'] = $this->controller->parameters->get('lower');
		}
		if ($this->controller->parameters->get('limit')) {
			$data['limit'] = $this->controller->parameters->get('limit');
		}
		if ($this->controller->parameters->get('search')) {
			$data['search'] = $this->controller->parameters->get('search');
		}
		if ($this->controller->parameters->get('page')) {
			$data['page'] = $this->controller->parameters->get('page');
		} else {
			$data["page"] = "0";
		}
		$this->urlData = $data;
		$table = strtolower($this->controller->configurations->get("panelTable"));
		$action = strtolower($this->controller->configurations->get("panelAction"));
		$array = $this->data[$table][$action];
		return new tx_lib_object($array);
	}



	function getAjaxTarget($function) {
		$config = $this->controller->configurations->getArrayCopy();
		if ($config['view.']['ajaxTargets.'][$function]) {
			return $config['view.']['ajaxTargets.'][$function];
		} else {
			return $config['view.']['ajaxTargets.']["default"];
		}
	}

	
	function loadLibrary($nameOfLibrary) {

	}

	function getNextSection($item_key,$actSection,$setup) {
		foreach ($setup as $key=>$val) {
			if ($val['section'] == $actSection) {
				$counter[] = $actSection;
			}
		}
		$i = 0;
		$size = count($counter);
		foreach ($setup as $key=>$val) {
			if ($val['section'] == $actSection) {
				$i++;
			}
			if ($i == $size) {
				return $key;
				break;
			}
		}
	}

	function getStaticValue($uid,$table="static_countries",$field="cn_short") {
		$where = "uid=" . $uid;
		$query = $GLOBALS['TYPO3_DB']->exec_SELECTquery($what,$table,$where);
		$result = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($query);
		return $result[$field];
	}


	function getData($type="preview") {
		if (is_array($this->get("setup"))) {
			foreach($this->get("data") as $key=>$val) {
				if (strlen($val[$type]) >= 1) {
					$checkLL = explode(":",$val[$type]);
					if ($checkLL[0] == "LLL") {
						$val[$type] = $this->getLL($val[$type]);
					}
					$data[$key] = $val[$type];
				}
			}
		}
		if(is_array($data)) return $data;
	}

	function getActionID($nameSpace,$node,$fields) {
		$config = $this->controller->configurations->getArrayCopy();
		return md5($config['setup.']['secret'] . $nameSpace . $node . $fields);
	}

	function getCreator(){
		$config = $this->controller->configurations->getArrayCopy();
		if (!$config['enable.']['logging']) {
			return false;
		}
		if (isset($config['view.']['logs']['create'])) {
			return $config['view.']['logs']['create']['user'];
		} else {
			return false;
		}
	}

	function getCreationDate(){
		$config = $this->controller->configurations->getArrayCopy();
		if (!$config['enable.']['logging']) {
			return false;
		}
		if (isset($config['view.']['logs']['create'])) {
			return date($this->printKeyFromLL("dateFormat"), $config['view.']['logs']['create']['tstamp']);
		} else {
			return false;
		}
	}

	function getVisitorCount(){
		$config = $this->controller->configurations->getArrayCopy();
		if (!$config['enable.']['logging']) {
			return false;
		}
		if (isset($config['view.']['logs']['retrieve'])) {
			return $config['view.']['logs']['retrieve']['count'];
		} else {
			return false;
		}
	}

	function getLastVisitor(){
		$config = $this->controller->configurations->getArrayCopy();
		if (!$config['enable.']['logging']) {
			return false;
		}
		if (isset($config['view.']['logs']['retrieve'][0])) {
			return $config['view.']['logs']['retrieve'][0]['user'];
		} else {
			return false;
		}
	}

	function getLastVisitDate(){
		$config = $this->controller->configurations->getArrayCopy();
		if (!$config['enable.']['logging']) {
			return false;
		}
		if (isset($config['view.']['logs']['retrieve'][0])) {
			return date("d.m.Y \u\m H:i",$config['view.']['logs']['retrieve'][0]['tstamp']);
		} else {
			return false;
		}
	}
}
?>