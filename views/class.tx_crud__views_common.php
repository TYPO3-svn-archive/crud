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
		$this->start = microtime(true);
		$this->reset();
		$this->controller = $controller;

		$config = $this->controller->configurations->getArrayCopy();
		//echo $config['storage.']['nameSpace'];
		$this->configuration=$config;
		$pars = $this->controller->parameters->getArrayCopy();
		$this->parameters = $pars;
		$view = $config['view.'];
		//require_once(PATH_site.'typo3/sysext/cms/tslib/class.tslib_content.php');
		parent::__construct($controller);
		$this->set('setup', $view['setup']);
		$this->set('data', $view['data']);
		$this->set('errors', $view['errors']);
		$this->set('mode', $view['mode']);
		$this->set('nameSpace', $view['nameSpace']);
		$this->set('action', $view['action']);
		$this->set('backValues', $view['backValues']);
		$this->set('keyOfPathToLanguageFile', $config['view.']['keyOfPathToLanguageFile']);
		if (stristr($_SERVER['REQUEST_URI'],"index.php")) {
			$hash .= 'norealurl' . $GLOBALS['TSFE']->config['config']['sys_language_uid'];
		} else {
			$hash .= 'withrealurl' . $GLOBALS['TSFE']->config['config']['sys_language_uid'];
		}
		$hash = md5($config['setup.']['marker'] . $config['storage.']['action'] . '-VIEW' . $hash);
		$this->cached=tx_crud__cache::get($hash);
		$this->generateUrls();
		if (is_array($pars['search']) && !$pars['track']) {
 	    	$hash = md5($_REQUEST['PHPSESSID'] . '-' . $config['setup.']['marker'] . '=' . $GLOBALS['TSFE']->id);
 	        $_SESSION[$this->getSessionHash()]['search'] = $pars['search'];
	    }
		if ($config['view.']['replace.'] && isset($pars['retrieve'])) {
			$data = $view['data'][$pars['retrieve']];
			foreach ($config['view.']['replace.'] as $field=>$val) {
				$field = str_replace('.', '', $field);
				$val['value'] = $data[$field];
				$this->replace[$field] = $val;
			}
		}
	}

	/**
	 * reset of the view
	 *
	 * @return	void
	 */
	function reset() {
		//$config = $this->controller->configurations->getArrayCopy();
		unset($this->cached);
		unset($this->cache);
		unset($this->configuration);
		unset($this->form);
		unset($this->html);
		$this->set('setup', '');
		//echo $config['storage.']['nameSpace'];
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
	private function generateUrls($pid=false) {
		if (!$pid) {
			$pid = $GLOBALS['TSFE']->id;
		}
		$get = tx_crud__div::_GP($this->getDesignator());
		if (is_array($_POST)) {
			foreach ($_POST as $key=>$val) {
				if (isset($get[$key])) {
					unset($get[$key]);
				}
				if (is_array($val)) {
					foreach ($val as $key2=>$val2) {
						if (isset($get[$key2])) {
							unset($get[$key2]);
						}
					}
				}
			}
		}
		if (is_array($_POST[$this->getDesignator()])) {
			foreach ($_POST[$this->getDesignator()] as $key=>$val) {
				if (isset($pars[$key])) {
					unset($get[$key]);
				}
				if (is_array($val)) {
					foreach ($val as $key2=>$val2) {
						if (isset($get[$key2])) {
							unset($get[$key2]);
						}
					}
				}
			}
		}
		$config = $this->controller->configurations->getArrayCopy();
		unset($get['id']);
		unset($get['cHash']);
		unset($get[$this->getDesignator()]);
		unset($get['ajaxTarget']);
		unset($get['saveContainer']);
		unset($get['restoreContainer']);
		unset($get['ajax']);
		unset($get['aID']);
		unset($get['mID']);
		//$params=$config['view.']['params.'];
		$params['page'] = 'PAGE';
		$params['action'] = 'ACTION';
		$params['retrieve'] = 'RETRIEVE';
		$hash = md5($config['setup.']['marker']);
		//$url=$this->cached['realurl'][$pid];

		if (is_array($params)) {
			foreach ($params as $key=>$val) {
				$params_keys[strtoupper($val)] = strtoupper($val);
			}
		}
		if (!$url) {
			require_once(PATH_site . 'typo3/sysext/cms/tslib/class.tslib_content.php');
			$link = tx_div::makeInstance('tx_lib_link');
			$link->parameters($params);
			$link->designator($this->getDesignator());
			$link->destination($pid);
			$url = $link->makeURL();
			//$url=str_replace("//","/",$url);
			$this->cache['realurl'][$pid] = $url;
		}

		$url = str_replace("%5B","[", $url);
		$url = str_replace("%5D","]", $url);
	//	echo $url;
		$url_exploded = explode('.', $url);
		if (trim($url_exploded[0]) == 'index') { //|| count($url_exploded)<=1

			//echo "nix real";
			$url_exploded = explode('&amp;', $url);
			$this->baseUrl = $url_exploded[0];
			unset($url_exploded[0]);
			foreach ($url_exploded as $key=>$val) {
				$val = str_replace($this->getDesignator() . '[', '', $val);
				$val = str_replace(']', '', $val);
				$val_exploded = explode('=', $val);
				//$params_keys=$params;
				if ($params_keys[$val_exploded[1]]) {
					$this->urlExtParameters[$val_exploded[0]] = $val_exploded[0];
				//	echo "ok"l
				} else {
					//echo "key ".$val_exploded[0]." empty";
					$this->urlBaseParameters[$val_exploded[0]] = $val_exploded[1];
				}
			}
			$get = $_GET;
			unset($get[$this->getDesignator()]);
			unset($get['id']);
			unset($get['cHash']);
			unset($get['ajax']);
			if (is_array($get)) {
				foreach($get as $key=>$val) {
					if ($key != $this->getDesignator()) {
						//echo $key." not set";
						$this->urlBaseParameters[$key] = $val;
					}
				}
			}
			$this->urlLimiter = '&';
			$this->realUrl = false;
		} else {
			//echo "real";
			//echo $url;
			unset($get['L']);
			unset($get['no_cache']);
			$this->urlLimiter = '/';
			$this->realUrl = true;
			$url_exploded = explode('/', $url);
			$url_exploded = array_reverse($url_exploded);
			foreach ($url_exploded as $key=>$val) {
				if (strlen($val) >= 1) {
					$url_ok[] = $val;
				}
			}
			$url_exploded = $url_ok;
			if (stristr($url_exploded[0], '?')) {
				unset($url_exploded[0]);
				$new_url = $url_exploded;
				$url_exploded = array();
				foreach ($new_url as $key=>$val) {
					$url_exploded[] = $val;
				}
			}
			$url_ok = $url_exploded;
			if (is_array($url_ok)) {
				foreach($url_ok as $key=>$val) {
					if ($useNext) {
						$this->urlExtParameters[$last] = $val;
					}
					if (!$useNext && !$params_keys[$val]) {
						$base[$val] = $val;
					}
					if (strlen($params_keys[$val]) >= 1) {
						$useNext = true;
						$last = strtolower($val);
					} else {
						$useNext = false;
					}
				}
			}
			if (is_array($base)) {
				$base = array_reverse($base);
				$this->baseUrl = implode('/', $base);
			}
			//if(is_array($get)) foreach($get as $key=>$val) if($key!=$this->getDesignator())$this->urlBaseParameters[$key]=$val;

		}
		//echo $this->baseUrl;
	}

	function getAjaxOnClick($ajaxTarget=false, $aID=false, $restoreContainer=false, $saveContainer=false) {
		$config = $this->controller->configurations->getArrayCopy();
		if (!$ajaxTarget) {
			$ajaxTarget = $config['view.']['ajaxTargets.']['default'];
		}
		if (!$aID) {
			$aID = tx_crud__div::getActionID($config);
		}
		$js = 'onclick="return ajaxlink(this,\''.$ajaxTarget.'\',\''.$aID.'\'';
		if ($saveContainer) {
			$js .= ',true';
		} else {
			$js .= ',false';
		}
		if ($restoreContainer) {
			$js.=',true';
		} else {
			$js.=',false';
		}
		$js .= ');"';
	//	$js=' onclick="javascript:ajaxlink(\'bluib\')"';
		return $js;
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
	function getUrl($pars=false, $pid=false, $search=false, $force=false, $ajaxTarget=false, $aID=false) {
		$params = $this->controller->parameters->getArrayCopy();
		unset($pars['ajaxTarget']);
		unset($pars['aID']);
		unset($pars['L']);
		if (!$pid) {
			$pid = $GLOBALS['TSFE']->id;
		}
		if ($pid != $GLOBALS['TSFE']->id) {
			//$this->generateUrls($pid);
			$renderNew = true;
		}
		if (is_array($_POST)) {
			foreach($_POST as $key=>$val) {
				if (isset($pars[$key])) {
					if($key!="action")unset($pars[$key]);
				}
				if (is_array($val)) {
					foreach($val as $key2=>$val2) {
						if (isset($get[$key2])) {
							unset($pars[$key2]);
						}
					}
				}
			}
		}
		if(is_array($_POST[$this->getDesignator()])) {
			foreach($_POST[$this->getDesignator()] as $key=>$val) {
				if (isset($pars[$key])) {
					 if($key!="action") unset($pars[$key]);
				}
				if (is_array($val)) {
					foreach($val as $key2=>$val2) {
						if (isset($get[$key2])) {
							unset($pars[$key2]);
						}
					}
				}
			}
		}

		$searchPars = $pars['search'];;
		if (is_array($pars['search'])) {
			unset($pars['search']);
		} elseif (isset($pars['find']) && !is_array($pars['find'])) {
			$pars['find'] = urlencode($pars['find']);
		}
		$config = $this->controller->configurations->getArrayCopy();
		if (is_array($config['storage.']['defaultParams.'])) foreach($config['storage.']['defaultParams.'] as $k=>$v) {
			$k = str_replace('.', '', $k);
			if (is_array($vXXX)) {
				foreach($v as $k2=>$v2) {
					$k2 = str_replace('.', '', $k2);
					if (is_array($v2)) {
						foreach($v2 as $k3=>$v3) {
							$k3 = str_replace('.', '', $k3);
							if (!isset($pars[$k][$k2][$k3])) {
								$pars[$k][$k2][$k3] = $v3;
							}
						}
					} elseif (isset($pars[$k][$k2])) {
						$pars[$k][$k2] = $v2;
					}
				}
			} else {
				if ($pars[$k] == $v) {
					if (isset($pars[$k])) {
						unset($pars[$k]);
					}
				}
			}
		}
		require_once(PATH_site . 'typo3/sysext/cms/tslib/class.tslib_content.php');
		$link = tx_div::makeInstance('tx_lib_link');
		$link->parameters($pars);
		$link->designator($this->getDesignator());
		$link->destination($pid);
		$url = $link->makeURL();
		if (is_array($searchPars) && $search) {
			foreach($searchPars as $key=>$params) {
				if (is_array($params)) {
					foreach($params as $trenner=>$value) {
						$url_exploded = explode('?', $url);
						if (sizeof($url_exploded) > 1) {
							$split = '&';
						} else {
							$split = '?';
						}
						$val = '[' . $trenner . ']';
						$url .= $split . $this->getDefaultDesignator() . '[search][' . $key . '][' . $trenner . ']=' . urlencode($value);
						//else $url.=$split.$this->getDefaultDesignator()."[search][".$key."][".$trenner."]=".urlencode($value);
						$i++;
					}
				} else {
					$url_exploded = explode('?', $url);
					if (sizeof($url_exploded) > 1) {
						$split = '&';
					} else {
						$split = '?';
					}
					 $url .= $split . $this->getDefaultDesignator() . '[search][' . $key . ']=' . urlencode($params);
					//else $url.=$split.$this->getDefaultDesignator()."[search][".$key."]=".urlencode($params);
				}
				//$split="&";
			//	$i++;
			}
		}
		$pars = $this->controller->parameters->getArrayCopy();
		if (!$force && strlen($pars['find']) >= 1) {
			$url_exploded = explode('?', $url);
			if (sizeof($url_exploded) > 1) {
				$split = '&';
			} else {
				$split = '?';
			}
			if ($i > 0) {
				$url .= $split . $this->getDefaultDesignator() . '[find]=' . urlencode($pars['find']);
			} else {
				$url .= $split . $this->getDefaultDesignator() . '[find]=' . urlencode($pars['find']);
			}
		}
		$url = str_replace('&amp;', '&', $url );
		$url = str_replace('&', '&amp;', $url );
		$url_exploded = explode('?cHash', $url);
		if (!isset($url_exploded[1])) {
			$url_exploded = explode('&amp;cHash', $url);
		}
		return $url_exploded[0];
	}

	function getUrlXXX($pars=false, $pid=false, $search=false, $force=false, $ajaxTarget=false, $aID=false) {

		if (!$pid) {
			$pid = $GLOBALS['TSFE']->id;
		}
		if ($pid!=$GLOBALS['TSFE']->id) {
			$this->generateUrls($pid);
			$renderNew = true;
		}
		if (is_array($_POST)) {
			foreach($_POST as $key=>$val) {
				if (isset($pars[$key])) {
					unset($pars[$key]);
				}
				if (is_array($val)) {
					foreach ($val as $key2=>$val2) {
						if (isset($get[$key2])) {
							unset($pars[$key2]);
						}
					}
				}
			}
		}
		if (is_array($_POST[$this->getDesignator()])) {
			foreach ($_POST[$this->getDesignator()] as $key=>$val) {
				if (isset($pars[$key])) {
					 unset($pars[$key]);
				}
				if (is_array($val)) {
					foreach($val as $key2=>$val2) {
						if (isset($get[$key2])) {
							unset($pars[$key2]);
						}
					}
				}
			}
		}

		unset($this->urlBaseParameters['cHash']);
		unset($this->urlBaseParameters['saveContainer']);
		unset($this->urlBaseParameters['aID']);
		unset($this->urlBaseParameters['mID']);
		unset($this->urlBaseParameters['restoreContainer']);
		unset($this->urlBaseParameters['ajaxTarget']);
		unset($pars['ajaxTarget']);
		$searchPars = $pars['search'];
		if (is_array($pars['search'])) {
			unset($pars['search']);
		} elseif (isset($pars['find']) && !is_array($pars['find'])) {
			$pars['find'] = urlencode($pars['find']);
		}
		if ($ajaxTarget && isset($pars['ajaxTarget'])) {
			$this->urlBaseParameters['ajaxTarget'] = $pars['ajaxTarget'];
		}
		if ($pars['saveContainer']) {
			$this->urlBaseParameters['saveContainer'] = $pars['saveContainer'];
		}
		if ($pars['restoreContainer']) {
			$this->urlBaseParameters['restoreContainer'] = $pars['restoreContainer'];
		}
		$url = $this->baseUrl;
		if (strpos($url, '?') === false) {
			$i = 0;
		} else {
			$i = 1;
		}
		if ($this->realUrl) {
			$url .= '/';
		}
		if (is_array($pars)) {
			foreach($pars as $key=>$val){
				//echo $key;
				if (!isset($this->urlBaseParameters[$key])) {
					if ($this->realUrl) {
						if (!$force) {
							$url .= $this->urlExtParameters[$key] . '/' . $val . '/';
						}
						$i++;
					} else {
						$i++;
						//echo $key;
						if (is_array($val)) {
							foreach($val as $k=>$v) {
								if (is_array($v)) {
									foreach($v as $k2=>$v2) {
										if (!is_array($v2)) {
											$url .= '&' . $this->getDefaultDesignator() . '[' . $this->urlExtParameters[$key] . '][' . $k . '][' . $k2 . ']=' . urlencode($v2);
										}
									}
								} else {
									$url .= '&' . $this->getDefaultDesignator() . '[' . $this->urlExtParameters[$key] . '][' . $k . ']=' . urlencode($v);
								}
							}
						} elseif (isset($this->urlExtParameters[$key])) {
							$url .= '&' . $this->getDefaultDesignator() . '[' . $this->urlExtParameters[$key] . ']=' . urlencode($val);
						} else {
							$url .= '&' . $this->getDefaultDesignator() . '[' . $key . ']=' . urlencode($val);
							unset($pars[$key]);
						}
					}
				} elseif ($key == $this->getDesignator()) {
					echo $key . 'nor in<br />';
				}
			}
		}

		if (is_array($this->urlBaseParameters) && !$force) {
			foreach($this->urlBaseParameters as $key=>$val) {
				if ($this->realUrl) {
					if (is_array($val)) {
						foreach($val as $k=>$v) {
							if ($i == 0) {
								$url .= '?' . $key . '[' . $k . ']=' . urlencode($v);
								$i++;
							} else {
								$url .= '&' . $key . '[' . $k . ']=' . urlencode($v);
							}
						}
					} elseif ($i == 0) {
						$url .= '?' . $key . '=' . urlencode($val);
						$i++;
					} else {
						$url .= '&' . $key . '=' . urlencode($val);
						$i++;
					}
				} elseif (!$this->realUrl) {
					$i++;
					if (is_array($val)) foreach($val as $k=>$v) {
						if (!is_array($v)) {
							if ($i==0) {
								$url .= '&' . $key . '[' . $k . ']=' . urlencode($v);
							} elseif (is_string($v)) {
								$url .= '&' . $key . '[' . $k . ']=' . urlencode($v);
							} else {
								$url .= '&' . $key . '[' . $k . ']=' . $v;
							}
						}
						$i++;
					} else {
						$url .= '&' . $key . '=' . urlencode($val);
					}
				}
			}
		}

		//echo $split;
		if (is_array($searchPars) && $search) {
			foreach($searchPars as $key=>$params) {
				if (is_array($params)) {
					foreach($params as $trenner=>$value) {
						$url_exploded = explode('?', $url);
						if (sizeof($url_exploded) > 1) {
							$split = '&';
						} else {
							$split = '?';
						}
						$val = '[' . $trenner . ']';
						$url .= $split . $this->getDefaultDesignator() . '[search][' . $key . '][' . $trenner . ']=' . urlencode($value);
						//else $url.=$split.$this->getDefaultDesignator()."[search][".$key."][".$trenner."]=".urlencode($value);
						$i++;
					}
				} else {
					$url_exploded = explode('?', $url);
					if (sizeof($url_exploded) > 1) {
						$split = '&';
					} else {
						$split = '?';
					}
					 $url .= $split . $this->getDefaultDesignator() . '[search][' . $key . ']=' . urlencode($params);
					//else $url.=$split.$this->getDefaultDesignator()."[search][".$key."]=".urlencode($params);
				}
				//$split="&";
			//	$i++;
			}
		}
		$pars = $this->controller->parameters->getArrayCopy();
		if (!$force && strlen($pars['find']) >= 1) {
			$url_exploded = explode('?', $url);
			if (sizeof($url_exploded) > 1) {
				$split = '&';
			} else {
				$split = '?';
			}
			if ($i > 0) {
				$url .= $split . $this->getDefaultDesignator() . '[find]=' . urlencode($pars['find']);
			} else {
				$url .= $split . $this->getDefaultDesignator() . '[find]=' . urlencode($pars['find']);
			}
		}
		if ($aID) {
			if (strlen($aID) > 4) {
				if ($i == 0 && $this->realUrl) {
					$url .= '?aID=' . $aID;
				} else {
					$url .= '&aID=' . $aID;
				}
			} else {
				if ($i == 0 && $this->realUrl) {
					$url .= '?aID=' . tx_crud__div::getActionID($this->controller->configurations->getArrayCopy());
				} else {
					$url .= '&aID=' . tx_crud__div::getActionID($this->controller->configurations->getArrayCopy());
				}
			}
		}
		if ($ajaxTarget) {
			if ($i == 0 && $this->realUrl) {
				$url .= '?ajaxTarget=' . $ajaxTarget;
			} else {
				$url .= '&ajaxTarget=' . $ajaxTarget;
			}
		}
		$url = str_replace('&amp;', '&', $url );
		$url = str_replace('&', '&amp;', $url );
		//echo $url;
		if ($renderNew) {
			$this->generateUrls();
		}
		return $url;
	}


	/**
	 * Build an HTML-Link and return them
	 *
	 * @param	array		$_GET pars for your URL
	 * @param	string		Label for your Link
	 * @return	string		the complete Link
	 */
	public function getTag($label, $pars=false, $uid=false, $ajaxTarget=false, $saveContainer=false, $restoreContainer=false) {
		if (isset($ah)) {
			$class = 'class="' . $style . '" ';
		}
		$config = $this->controller->configurations->getArrayCopy();
		if (!$ajaxTaget) {
			$ajaxTarget=tx_crud__div::getAjaxTarget($config, 'default');
		}
		unset($pars['ajaxTarget']);
		unset($pars['aID']);
		unset($pars['saveContainer']);
		unset($pars['restoreContainer']);
		$link = '<a href="' . $this->getUrl($pars, $uid) . '" ' . $class . ' ' . $this->getAjaxOnClick(tx_crud__div::getAjaxTarget($config, 'getTag'), tx_crud__div::getActionID($config), $restoreContainer, $saveContainer) . '>' . $label . '</a>'; // TODO: title-tag?
		return $link;
	}

	// -------------------------------------------------------------------------------------
	// Translation handling
	// -------------------------------------------------------------------------------------

	/**
	 * Translate a string bye the locallang key.
	 * Optional with a Path to a locallang.xml, for example LLL:EXT:crud/locallang.xml
	 *
	 * @param	string		$key 	the key in the locallang.xml
	 * @param	string		$path	optional a path to a locallang.xml. if empty standard locallang will be used
	 * @param	boolean		$force	if set the string will return alos if not successfull translated
	 * @return	string
	 */
	function getLLfromKey($key, $path=false, $force=false) {
		$config = $this->controller->configurations->getArrayCopy();
		if (!$path) {
			$LLL = 'LLL:' . $config['view.']['keyOfPathToLanguageFile'] . ':' . $key;
		} else {
			$LLL = 'LLL:' . $path . ':' . $key;
		}
		$str = $this->getLL($LLL,$key);
		if (!$force && $str != $LLL) {
			return $str;
		} elseif ($force) {
			return $key;
		} else {
			return false;
		}
	}

	/**
	 * returns an action link based on the setup
	 *
	 * @param 	array	$setup	the setup
	 * @return  string	the action link form
	 */
	function printAsActionLink($label='do it', $action='update', $ajax=1, $pid=false, $aID=false) {
		$setup = $this->controller->configurations->getArrayCopy();
		if (!$aID) {
			$aID = tx_crud__div::getActionID($setup);
		}
		if (!$pid) {
			$pid = $GLOBALS['TSFE']->id;
		}
		$url = $this->getUrl(false, $pid, false, false);

		$form = '<form  action="' . $url . '" method="post"><div>';
		$image = 'typo3conf/ext/crud/resources/icons/' . $action . '.gif';
		if ($ajax) {
			$form .= '<input type="hidden" name="ajaxTarget" value="' . tx_crud__div::getAjaxTarget ($setup, 'printActionLink') . '" />' . "\n\t";
		}
		$form .= '<input type="hidden" name="' . $setup ['setup.'] ['extension'] . '[form]" value="' . $aID . '" />' . "\n\t";
		if ($ajax) {
			$form .= '<input type="hidden" name="aID" value="' .$aID . '" />' . "\n\t";
		}
		$form .= '<input type="hidden" name="' . $this->getDesignator() . '[icon]" value="1" />' . "\n\t";
		$form .= '<input type="hidden" name="' .$this->getDesignator() . '[process]" value="' . strtolower ( $action ) . '" />' . "\n\t";
		$form .= '<button type="submit" name="' .$this->getDesignator() . '[submit]"><span>' . $label . '</span></button>' . "\n\t</div></form>\n"; //TODO: Localization
		echo  $form;
	}



	/**
	 * Tranlate a string bye the complete locallang key with the path.
	 *
	 * @param	string		$localLangKey 	the complete key. Example LLL:EXT:crud/locallang.xml:key
	 * @param	boolean		$force	if set the string will return alos if not successfull translated
	 * @return	string
	 */
	function getLL($localLangKey,$force=false) {
		//echo $localLangKey;
		$string = @explode(':', $localLangKey);
		$config = $this->controller->configurations->getArrayCopy();
		$hash = md5($string[1] . $string[2]);
		if ($string[0] == 'LLL') {
			if (!is_array($this->LL[$hash])) {
				$localLang = tx_crud__cache::get($hash);
				$this->LL[$hash] = $localLang;
			} else {
				$localLang = $this->LL[$hash];
			}
			$LLL[] = $localLangKey;
			if (!is_array($localLang)) {
				//echo "schreibe ll cache";
				foreach ($LLL as $num=>$str) {
					$action = $this->get('action');
					$string = @explode(':', $str);
					$path = $string[1] . ':' . $string[2];
					if (!is_object($this->extTranslator)) {
						$this->extTranslator = tx_div::makeInstance('tx_lib_translator');
					} else {
						$this->extTranslator->LOCAL_LANG_loaded=false;
					}
					if ($string[0] == 'LLL' && !$LLKey) {
						$this->extTranslator->setPathToLanguageFile($path);
						if (!@file_exists($this->extTranslator->getPathToLanguageFile())) {
							$type = explode('.', $string[2]);
							if ($type[1] == 'php') {
								$path = str_replace('php', 'xml', $path);
							}
							$this->extTranslator->setPathToLanguageFile($path);
						}
						if (@file_exists($this->extTranslator->getPathToLanguageFile())) {
							$this->extTranslator->_loadLocalLang();
							if (is_array($this->extTranslator->LOCAL_LANG)) {
								tx_crud__cache::write($hash,$this->extTranslator->LOCAL_LANG);
								$localLang = $this->extTranslator->LOCAL_LANG;
								$this->LL[$hash] = $localLang;
							}
						}
					}
				}
			}
			//echo $GLOBALS['TSFE']->config['config']['language'];
			if (is_array($localLang)) {
				$language = trim($GLOBALS['TSFE']->config['config']['language']);
				//echo $string[3];
				if (strlen($localLang[$language][$string[3]]) >= 1) {
					$translated = $localLang[$language][$string[3]];
					//echo $string;
				} elseif ($localLang["default"][$string[3]]) {
					$translated = $localLang['default'][$string[3]];
				} else {
					if ($string[3] == 'pages.doktype') {
						$translated = 'Pagetype';
					} elseif ($string[3] == 'pages.title') {
						$translated = 'Title';
					}
				}
			}
		}
		if (!empty($translated)) {
			return $translated;
		} else {
			if($force) {
				return $localLangKey;
			} else {
				return false;
			}
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
	function makeFilePreview($url, $height=30, $width=30) {
		$fileExtension_exploded = explode('.', $url);
		$fileExtension = strtolower($fileExtension_exploded[count($fileExtension_exploded) - 1]);
		$images = array('jpg', 'jpeg', 'png', 'gif', 'bmp');
		if (in_array($fileExtension, $images)) {
			if ($this->cached['images'][$url]) {
				$img = '<img src="' . $this->cached['images'][$url] . '" alt="' . $this->cached['images'][$url] . '"/>';
				return $img;
			} else {
				if (file_exists($url)) {
					require_once(PATH_site . 'typo3/sysext/cms/tslib/class.tslib_content.php');
					require_once(PATH_site . 't3lib/class.t3lib_stdgraphic.php');
					require_once(PATH_site . 'typo3/sysext/cms/tslib/class.tslib_gifbuilder.php');
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
					$img_exploded = explode('src="', $img);
					$img_exploded = explode('"', $img_exploded[1]);
					$this->cache['images'][$url] = $img_exploded[0];
					return $img;
				}
			}
		} else {
			$url_exploded = explode('/', $url);
			$file = $url_exploded[count($url_exploded) - 1];
			$img = '<a href="' . $url . '">' . $file . '</a>';
			return $img;
		}
	}

	function printAsDamMedia($record) {
		if ($record['file_mime_type'] == 'image') {
			$image = $record['file_path'] . $record['file_name'];
			echo $this->printAsImage($record['file_name'], $record['height'], $record['width'], $record['title'], $record['file_path']);
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
	function printAsImage($item_key, $height=30, $width=30, $altText=false, $path=false, $urlOnly=false, $maxImages=100, $lightbox=false, $wrapAll='', $wrapImage='') {
		$setup = $this->controller->configurations->getArrayCopy();
		if (!$path) {
			$img = explode(',', $this->get($item_key));
		} else {
			$img[0] = $item_key;
		}
		$wrapImage = explode('|', $wrapImage);
		if (strlen($img[0]) > 1) {
			$i = 0;
			foreach ($img as $key=>$val) {
				if (!$path) {
					$url = $setup['view.']['setup'][$item_key]['config.']['uploadfolder'] . '/' . $val;
				} else {
					$url = $path . '/' . $val;
				}
			//	echo $url;
				
				if (($config['storage.']['action'] == 'retrieve' || $config['storage.']['action'] == 'browse') && $this->cached['images'][$url]) {
					$img = '<img src="' . $this->cached['images'][$url] . '" alt="' . $altText . '"/>';
					echo $img;
				} elseif (@file_get_contents($url) && $i < $maxImages) {
					$size = getimagesize($url);
					if ($size[1] > $height) {
						$size[1] = $height;
					}
					if ($size[0] > $width) {
						$size[0] = $width;
					}
					require_once(PATH_site . 'typo3/sysext/cms/tslib/class.tslib_content.php');
					require_once(PATH_site . 't3lib/class.t3lib_stdgraphic.php');
					require_once(PATH_site . 'typo3/sysext/cms/tslib/class.tslib_gifbuilder.php');
					$imageClassName = tx_div::makeInstanceClassName('tx_lib_image');
					//if(is_object($image))
					$image = new $imageClassName();
					if (strlen($altText) > 1) {
						$image->alt($altText);
					} else {
						$image->alt($val);
					}
					$image->maxWidth($size[0]);
					$image->maxHeight($size[1]);
					$image->path($url);

					if ($lightbox) {
						$images .= '<a href="' . $url . '" rel="lightbox[lb26]">' . $wrapImage[0] . $image->make() . $wrapImage[1] . '</a>'; //TODO: [lb26]
					} else {
						if (!$urlOnly) {
							$images .= $wrapImage[0] . $image->make() . $wrapImage[1];
						} else {
							$images .= $image->make();
						}
					}
					echo $images;
					$img_exploded = explode('src="', $images);
					$img_exploded = explode('"', $img_exploded[1]);
					if ($config['storage.']['action'] == 'retrieve' || $config['storage.']['action'] == 'browse') $this->cache['images'][$url] = $img_exploded[0];
					unset($image);
				} else {
					echo '%%%error_no-image%%%';
				}
				$i++;
				if ($urlOnly) {
					$img = explode('src="', $images);
					$img = explode('"', $img[1]);
					//echo $img[0];
					return null;
				}
			}
			$wrapAll = explode('|', $wrapAll);
			if ($images) {
				//echo $wrapAll[0] . $images . $wrapAll[1];
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
	    return md5($_REQUEST['PHPSESSID'] . '-' . $config['setup.']['marker'] . '-' . $GLOBALS['TSFE']->id);
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
			return $config['view.']['ajaxTargets.']['default'];
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
		$this->headerData[$root][$what] = $conf['resources.'][$root . '.'][$what];
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
		$this->footerData[$root][$what] = $conf['resources.'][$root . '.'][$what];
	}

	function setTitleTag($string) {
		$this->titleTag = $string;
	}

	/**
	 * prints the javascript for the tabs and check for an error in a tab
	 *
	 * @param	array	$entryList	the form setup
	 * @param 	string	$call	the jquery tab call. example: $('#crud-tabs-form > ul')
	 * @return	void
	 */
	function enableTabs($call, $entryList=false) {
		$tab = 1;
		if (is_array($entryList)) {
			foreach ( $entryList as $divider => $dividers ) {
				foreach ( $dividers as $section => $sections ) {
					foreach ( $sections as $key => $entry ) {
						if ($entry ['error']) {
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
					$tab ++;
				}
			}
		}

		if ($tabError) {
			$tab = $tabError;
		} else {
			$tab = 1;
		}
		$tab = $tab - 1;

		$js = '<script type="text/javascript">
				function enableTabs(){
					$("' . $call . ' > ul").addClass("ui-tabs-nav");
					$("' . $call . ' > ul > li:eq(' . $tab . ')").addClass("ui-tabs-selected");
					$("' . $call . ' > div").addClass("ui-tabs-panel");
					$("' . $call . ' > ul").tabs("' . $call . ' > div", {initialIndex: "' . $tab . '" });
					$("' . $call . ' > ul > li").click(function() {
						$("' . $call . ' > ul > li").each(function(){
							$(this).removeClass("ui-tabs-selected");
						});
						$(this).addClass("ui-tabs-selected");
					});
				}
				</script>';
		echo $js;
	}

	function getCalDate($tstamp,$allday=true) {//20080210T131020
		$time = date('Y', $tstamp) . date('m', $tstamp) . date('d', $tstamp);
		$time .= 'T' . date('h', $tstamp) . date('i', $tstamp) . '00Z';
		return $time;
	}

	function getCalText($text) {
		$text = str_replace("\n", "", $text);
		$text = str_replace("\r", "", $text);
		return strip_tags($text);
	}

	function getCropText($string, $maxChars, $sentenceBreakDiff = 20) {
		if (strlen($string) < $maxChars) {
			return $string;
		}
		$sentences = explode('. ', $string);
		$sentenceCharCount = 0;
		foreach ($sentences AS $key => $value) {
			$sentenceString .= $value . '. ';
			$sentenceCharCount += strlen($sentenceString);
			if ($sentenceCharCount > $maxChars) {
				break;
			} elseif ($sentenceCharCount > $maxChars - $sentenceBreakDiff) {
				return $sentenceString . '&hellip;';
			}
		}
		return substr($string, 0, strrpos(substr($string, 0, $maxChars), ' ')) . '&hellip;';
	}

	/**
	 * write view caches if exist
	 *
	 * @param	$string		$root 	wich section call,css or javascript
	 * @param	string		$what	what should be addes to html footer
	 * @return	void
	 */
	function destruct() {
		//header("Location: index.php?id=1");
		$config = $this->controller->configurations->getArrayCopy();
		if (stristr($_SERVER['REQUEST_URI'], 'index.php')) {
			$hash .= 'norealurl' . $GLOBALS['TSFE']->config['config']['sys_language_uid'];
		} else {
			$hash .= 'withrealurl' . $GLOBALS['TSFE']->config['config']['sys_language_uid'];
		}
		$hash = md5($config['setup.']['marker'] . $config['storage.']['action'] . '-VIEW' . $hash);
		if (is_array ( $this->cache ) && $config ['enable.'] ['caching'] == 1) {
			if (is_array ( $this->cached )) {
				foreach ( $this->cached as $key => $val ) {
					if (is_array ( $val )) {
						foreach($val as $k=>$v) {
							$this->cache[$key][$k] = $v;
						}
					}
				}
			}
			//echo "schreibe view cache";
			tx_crud__cache::write($hash, $this->cache );
		}
		$end = microtime(true);
		$time = $end - $this->start;
		if ($_REQUEST['showtime']) {
			echo 'view ' . $config['setup.']['marker'] . 'rendered in $time seconds' . "\n";
		}
	}
}
?>