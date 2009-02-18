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


class tx_crud__views_common extends tx_lib_phpTemplateEngine {

	private $form;
	public $viewAction;
	private $html;
	private $extTranslator;
	private $functions;

	// -------------------------------------------------------------------------------------
	// VIEW SETUP
	// -------------------------------------------------------------------------------------
	
	/**
	 * Setup of the View and checking for  a existing search in the User Session 
	 * 
 	 * @param object $controller name to declaree
	 * @return	void
	 */
	public function setup(&$controller) {
		$this->reset();
		$this->controller = $controller;
		$config = $this->controller->configurations->getArrayCopy();
		$this->configuration=$config;
		$pars = $this->controller->parameters->getArrayCopy();
		$this->parameters=$pars;
		$view = $config['view.'];
		parent::__construct($controller);
		$this->set("setup",$view['setup']);
		$this->set("data",$view['data']);
		$this->set("errors",$view['errors']);
		$this->set("mode",$view['mode']);
		$this->set("nameSpace",$view['nameSpace']);
		$this->set("action",$view['action']);
		$this->set("backValues",$view['backValues']);
		$this->set("keyOfPathToLanguageFile",$config['view.']['keyOfPathToLanguageFile']);
		if(stristr($_SERVER['REQUEST_URI'],"index.php")) $hash.="norealurl".$GLOBALS['TSFE']->config['config']['sys_language_uid'];
		else $hash.="withrealurl".$GLOBALS['TSFE']->config['config']['sys_language_uid'];
		$hash = md5($config['setup.']['marker']."-VIEW".$hash);
		$this->cached=tx_crud__cache::get($hash);
		$this->generateUrls();
		if (is_array($pars['search']) && !$pars['track']) {
 	    	$hash=md5($_REQUEST['PHPSESSID']."-".$config['setup.']['marker']."=".$GLOBALS['TSFE']->id);
 	        $_SESSION[$this->getSessionHash()]['search']=$pars['search'];
	    }
			
	}

	/**
	 * reset of the view
	 * 
	 * @return	void
	 */
	function reset() {
		unset($this->cached);
		unset($this->cache);
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
		unset($get['saveContainer']);
		unset($get['restoreContainer']);
		unset($get['ajax']);
		$params['page']='PAGE';
		$params['limit']='LIMIT';
		$params['search']='SEARCH';
		$params['upper']='UPPER';
		$params['showhistory']='SHOWHISTORY';
		$params['compareWith']='COMPAREWITH';
		$params['history']='HISTORY';
		$params['lower']='LOWER';
		$params['retrieve']='RETRIEVE';
		$params['action']='ACTION';
		$params['track']='TRACK';
		$hash=md5($config['setup.']['marker']);
		foreach($params as $key=>$val) $params_keys[strtoupper($val)]=strtoupper($val);
		if(!$url) {
			require_once(PATH_site.'typo3/sysext/cms/tslib/class.tslib_content.php');
			$link = tx_div::makeInstance('tx_lib_link');
			$link->parameters($params);
			$link->designator($this->getDesignator());
			$link->destination($this->getPageId());
			$url = $link->makeURL();
		}
		$url_exploded=explode(".",$url);
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
	 * @param	array		pars for your URL
	 * @return	string		the complete URL
	 */
	function getUrl($pars=false) {
		unset($this->urlBaseParameters['cHash']);
		unset($this->urlBaseParameters['saveContainer']);
		unset($this->urlBaseParameters['aID']);
		unset($this->urlBaseParameters['restoreContainer']);
		unset($this->urlBaseParameters['ajaxTarget']);
		if(is_array($pars['search'])){
			unset($pars['search']);
		}
		elseif(isset($pars['search']) && !is_array($pars['search'])) {
			$pars['search']=urlencode($pars['search']);
		}
		if($pars['ajaxTarget']) {
			$this->urlBaseParameters['ajaxTarget']=$pars['ajaxTarget'];
		}
		if($pars['saveContainer']) {
			$this->urlBaseParameters['saveContainer']=$pars['saveContainer'];
		}
		if($pars['restoreContainer']) {
			$this->urlBaseParameters['restoreContainer']=$pars['restoreContainer'];
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
		$url.="&aID=".tx_crud__div::getActionID($this->controller->configurations->getArrayCopy());
		return $url;
	}

	/**
	 * Build an HTML-Link and return them
	 *
	 * @param	array		$_GET pars for your URL
	 * @param	string		Label for your Link
	 * @return	string		the complete Link
	 */
	public function getTag($label,$pars=false) {
		$link = '<a href="'.$this->getUrl($pars).'">'.$label.'</a>'; // TODO: title-tag?
		return $link;
	}
	
	// -------------------------------------------------------------------------------------
	// Translating handling
	// -------------------------------------------------------------------------------------
	
	/**
	 * Tranlate a string bye the locallang key.
	 * Optional with a Path to a locallang.xml, for example LLL:EXT:crud/locallang.xml
	 *
	 * @param	string		$key 	the key in the locallang.xml
	 * @param	string		$path	optional a path to a locallang.xml. if empty standard locallang will be used
	 * @param	boolean		$force	if set the string will return alos if not successfull translated
	 * @return	string
	 */
	function getLLfromKey($key,$path=false,$force=false) {
		$config=$this->controller->configurations->getArrayCopy();
		if(!$path)$LLL="LLL:".$config['view.']['keyOfPathToLanguageFile'].":".$key;
		else $LLL="LLL:".$path.":".$key;
		$str=$this->getLL($LLL,$key);
		if(!$force && $str!=$LLL)return $str;
		elseif($force) return $key;
		else return false;
	}

	/**
	 * Tranlate a string bye the complete locallang key with the path.
	 *
	 * @param	string		$localLangKey 	the complete key. Example LLL:EXT:crud/locallang.xml:key
	 * @param	boolean		$force	if set the string will return alos if not successfull translated
	 * @return	string
	 */
	function getLL($localLangKey,$force=false) {
		$string = @explode(':',$localLangKey);
		$config = $this->controller->configurations->getArrayCopy();
		$hash=md5($string[1].$string[2]);
		if($string[0]=="LLL") {
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
							$this->extTranslator->_loadLocalLang();
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
				$language=trim($GLOBALS['TSFE']->config['config']['language']);
				if (strlen($localLang[$language][$string[3]])>=5) {
					$translated =  $localLang["default"][$string[3]];
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
		}
		if (!empty($translated)) {
			return $translated;
		} else {
			if($force) return $localLangKey;
			else return false;
		}
	}
	

	// -------------------------------------------------------------------------------------
	// HELPER API
	// -------------------------------------------------------------------------------------
	
	/**
	 * render an file preview if the url is an path to an image.
	 * 
	 * Otherwise the filextension will be checked and if an icon for the fileextenions exist it will returned
	 *
	 * @param	string		$url	the path to the file
	 * @param	integer		$height		the height of the image 
	 * @param	integer		$width		the hwidth of the image 
	 * @return	string
	 */
	function makeFilePreview($url,$height=30,$width=30) {
		$fileExtension_exploded = explode(".",$url);
		$fileExtension = strtolower($fileExtension_exploded[count($fileExtension_exploded)-1]);
		$images = array("jpg","jpeg","png","gif","bmp");
		if (in_array($fileExtension,$images)) {
			if($this->cached['images'][$url]) {
				$img = '<img src="'.$this->cached['images'][$url].'" alt="'.$this->cached['images'][$url].'"/>';
				return $img;
			}
			else {
				if (file_exists($url)) {
					require_once(PATH_site.'typo3/sysext/cms/tslib/class.tslib_content.php');
					require_once(PATH_site.'t3lib/class.t3lib_stdgraphic.php');
					require_once(PATH_site.'typo3/sysext/cms/tslib/class.tslib_gifbuilder.php');
					$size = getimagesize($url);
					if ($size[1] > $height) {
						$size[1] = $height;
					}
					if ($size[0] > $width) {
						$size[0] = $width;
					}
					$imageClassName = tx_div::makeInstanceClassName('tx_lib_image');
					$image = new $imageClassName();
					$image->maxWidth($size[0]);
					$image->maxHeight($size[1]);
					$image->path($url);
					$img = $image->make();
					$img_exploded=explode('src="',$img);
					$img_exploded=explode('"',$img_exploded[1]);
					$this->cache['images'][$url]=$img_exploded[0];
					return $img;
				}
			}
		}
		else {
			$url_exploded = explode("/",$url);
			$file=$url_exploded[count($url_exploded)-1];
			$img = '<a href="'.$url.'">'.$file.'</a>';
			return $img;
		}
	}
	
	/**
	 * renders images from a setup automatic
	 *
	 * @param	string		$item_key	the key in the setup
	 * @param	integer		$height		the height of the image 
	 * @param	integer		$width		the hwidth of the image 
	 * @param	integer		$maxImages	how much images should rendered maximal
	 * @param	boolean		$lightbox	should a lightbox be active
	 * @param	string		$wrapAll	wrap some html about all images
	 * @param	string		$wrapImage	wrap some html about every single image
	 * @return	string
	 */
	function printAsImage($item_key,$height=30,$width=30,$maxImages=100,$lightbox=1,$wrapAll="",$wrapImage="") {
		$setup = $this->setup;
		$pars = $this->controller->parameters->getArrayCopy();
		$img = $item_key;
		$wrapImage = explode("|",$wrapImage);
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
					require_once(PATH_site.'typo3/sysext/cms/tslib/class.tslib_content.php');
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
	
		
	/**
	 * return the hash for the fe user session basesd on the PHPSESSID
	 *
	 * @return	string	the generated hash
	 */
	function getSessionHash() {
        $config = $this->controller->configurations->getArrayCopy();
	    return md5($_REQUEST['PHPSESSID']."-".$config['setup.']['marker']."-".$GLOBALS['TSFE']->id);
	}
	
	/**
	 * return the ajaxTraget defined by typoscript every function
	 *
	 * @param 	string	$function	the name of the function for the ajaxTarget. if not set the default will be returned
	 * @return	string	the ajaxTarget for the function
	 */
	function getAjaxTarget($function) {
		$config = $this->controller->configurations->getArrayCopy();
		if (isset($config['view.']['ajaxTargets.'][trim($function)])) {
			return $config['view.']['ajaxTargets.'][$function];
		} else {
			return $config['view.']['ajaxTargets.']["default"];
		}
	}

	/**
	 * return the next jquery tabsection
	 *
	 * @param 	string	the name of the function, if not set the default will be returned
	 * @return	string	the generated hash
	 */
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

	/**
	 * Adds a css or js libary defined bye typoscript to the page Header
	 *
	 * @param	$string		$root 	wich section call,css or javascript
	 * @param	string		$what	what should be addes to html header
	 * @return	void
	 */
	function loadHeaderData($root,$what) {
		$conf = $this->controller->configurations->getArrayCopy();
		$this->headerData[$root][$what] = $conf['resources.'][$root.'.'][$what];
	}

	/**
	 * Adds a css or js libary defined bye typoscript to the page footer
	 *
	 * @param	$string		$root 	wich section call,css or javascript
	 * @param	string		$what	what should be addes to html footer
	 * @return	void
	 */
	function loadFooterData($root,$what) {
		$conf = $this->controller->configurations->getArrayCopy();
		$this->footerData[$root][$what] = $conf['resources.'][$root.'.'][$what];
	}

	/**
	 * write view caches if exist 
	 *
	 * @param	$string		$root 	wich section call,css or javascript
	 * @param	string		$what	what should be addes to html footer
	 * @return	void
	 */
	function destruct() {
		$config = $this->controller->configurations->getArrayCopy();
		if(stristr($_SERVER['REQUEST_URI'],"index.php")) $hash.="norealurl".$GLOBALS['TSFE']->config['config']['sys_language_uid'];
		else $hash.="withrealurl".$GLOBALS['TSFE']->config['config']['sys_language_uid'];
		$hash = md5($config['setup.']['marker']."-VIEW".$hash);
		if(is_array($this->cache)) {
			tx_crud__cache::write($hash,$this->cache);
		}
	}
	
}
?>