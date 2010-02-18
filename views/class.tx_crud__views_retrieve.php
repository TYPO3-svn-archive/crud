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

require_once(t3lib_extMgm::extPath('crud') . 'views/class.tx_crud__views_common.php');
class tx_crud__views_retrieve extends tx_crud__views_common {
	var $panelAction = "RETRIEVE";

	function printValueByType($item_value) {
		return $this->getLL($item_value);
	}

/**
	 * prints a page browser
	 *
 	 * @param	integer	$uid	the uid of the record to show
  	 * @param 	string	$label	the label for the sorting link
 	 * @param 	boolean	$urlOnly	if set only the url will returned
 	 * @param 	string	$action	optional a special action
	 * @return	void
	 */
	function printAsSingleLink($uid, $label='%%%show%%%', $urlOnly=false, $action='retrieve', $pid=false, $ajax=true, $saveContainer=true) {
		$pars = $this->controller->parameters->getArrayCopy ();
		$pars ['retrieve'] = $uid;
		//s$pars ['action'] = $action;
		unset($pars['action']);
		if ($action != 'retrieve') {
			$pars['action']=$action;
		}
		if (!$pid) {
			$pid=$GLOBALS['TSFE']->id;
		}
		$config = $this->controller->configurations->getArrayCopy();
		$data = $pars;
		if (is_array($data ['search'] )) {
			unset ( $data ['search'] );
			$data ['track'] = 1;
		}
		if ($this->page >= 1) {
			$data ['page'] = $this->page;
		}
		if ($urlOnly) {
			return $this->getUrl($data, $pid);
		}

		if ($ajax) {
			$onClick = $this->getAjaxOnClick(tx_crud__div::getAjaxTarget($config, 'printAsSingleLink'), tx_crud__div::getActionID($config), false, $saveContainer);
		}
		$link = '<a href="' . $this->getUrl($data, $pid) . '" ' . $onClick . '>' . $label . '</a>';
		echo $link;
	}

	function printAsBackLink($label='%%%back%%%', $pid=false, $pars=false, $ajax=true, $saveContainer=true, $restoreContainer=true) {
		if (!$pars) {
			$pars = $this->controller->parameters->getArrayCopy();
		}
		unset($pars['retrieve']);
		unset($pars['action']);
		$data = $pars;
		if (!$pid) {
			$pid = $GLOBALS['TSFE']->id;
		}
		$config = $this->controller->configurations->getArrayCopy();
		if (is_array($pars['search'])) {
			$data['track'] = 1;
			unset($data['search']);
		}
		if ($this->page >= 1) {
			$data['page'] = $this->page;
		}
		if ($ajax) { $onClick = $this->getAjaxOnClick(tx_crud__div::getAjaxTarget($config, 'printAsSingleLink'), tx_crud__div::getActionID($config), $restoreContainer, false);
		}
		//$data['action']="list";
		$url = '<a class="backlink" ' . $onClick . ' href="' . $this->getUrl($data, $pid, 1) . '">' . $label . '</a>';
		echo $url;
	}

	function renderPreview($data=false) {
		$typoscript = $this->controller->configurations->getArrayCopy();
		$params = $this->controller->parameters->getArrayCopy();
		$setup = $typoscript['view.']['setup'];
		if (!$data) {
			$data = $typoscript['view.']['data'];
		}
		if (is_array($setup) && is_array($data)) {
			foreach ($data as $uid=>$entry) {
				foreach ($entry as $key=>$value) {
					if ($setup[$key]['config.']['type'] != "check" && strlen(trim($value)) >= 1 && is_array($setup[$key]['options.'])) {
						$value_exploded = explode(',', $value);
						$preview = false;
						foreach ($value_exploded as $v) {
							//echo $setup[$key]['options.'][$v];
							if (strlen(trim($setup[$key]['options.'][$v])) >= 1) {
								$v_exploded = explode('LLL', $setup[$key]['options.'][$v]);
								if (strlen($v_exploded[1]) >= 1) {
									$preview[] = $setup[$key]['options.'][$v];
								} else {
									if (strlen($setup[$key]['options.'][$v]) >= 1) {
										$preview[] = $setup[$key]['options.'][$v];
									} else {
										$preview[] = $v;
									}
								}
							}
						}
						if (is_array($preview)) {
							$data[$uid][$key] = implode(',', $preview);
						}
					} elseif ($setup[$key]['config.']['internal_type'] == 'file' && strlen($value) > 4) {
						$preview = false;
						$value_exploded = explode(',', $value);
						foreach ($value_exploded as $file) {
							//if(!isset($params['history']))
							$preview[] = $file;
							//$preview[]=$this->makeFilePreview($setup[$key]['config.']['uploadfolder']."/".$file);
						}
						if (is_array($preview)) {
							$data[$uid][$key] = implode(',', $preview);
						}
					} elseif ($setup[$key]['config.']['type'] == 'check' && !is_array($setup[$key]['options.'])) {
						if ($data[$uid][$key] == '1') {
							$data[$uid][$key] = '%%%yes%%%';
						} else {
							$data[$uid][$key] = '%%%no%%%';
						}
					} elseif ($setup[$key]['config.']['type'] == 'check') {
						$dataArray = array();
						$entry = array();
						$db = $data[$uid][$key];
						$y = 1;
						for ($i = 1; $i <= count($setup[$key]['options.']); $i++) {
							$dataArray[$y] = $y;
							$y = $y * 2;
						}
						$dataArray = array_reverse($dataArray);
						$alle = 0;
						foreach ($dataArray as $key2=>$val) {
							$alle += $val;
						}
						if ($alle == $db) {
							foreach ($dataArray as $key2=>$val) {
								$entry[] = $key2; //all ok
							}
						} else {
							$next = true;
							$begin = true;
							$sum = $db;
							$counter = count($dataArray) - 1;
							$size = count($dataArray);
							foreach ($dataArray as $key2=>$val) {
								if ($begin) {
									$try = $db;
								} else {
									if ($next) {
										$try = $db;
									} else {
										$try = $zsum;
									}
								}
								$zsum = $try-$val;
								if ($zsum >= 0) {
									$values[$counter] = $val;//wert ok!
									$next = false;
								} else {
									$next = true;
									$values[$counter] = false;
								}
								$begin = false;
								$counter--;
							}
						}

						if (is_array($values)) {
							$values = array_reverse($values);
							foreach ($values as $key2=>$val) {
								if ($val) {
									$entry[] = $key2;
								}
							}
						}

						$ll = array();
						if (is_array($entry)) {
							foreach ($entry as $key3=>$val3) {
								$ll[] = $this->getLL($setup[$key]['options.'][$val3], 1);
							}
							if (is_array($ll)) {
								$data[$uid][$key] = implode(',', $ll);
							}
						}
					} elseif (is_array($setup[$key]['config.']['wizards']['link']) && !isset($params['history']) && strlen($data[$uid][$key]) >= 3) {
							$data[$uid][$key] = str_replace('http://', '', $data[$uid][$key]);
							//$link="http://".$data[$uid][$key];
							$data[$uid][$key] = '<a href="http://' . $data[$uid][$key] . '">' . $data[$uid][$key] . '</a>'; // FIXME: target ist nicht unbedingt valide
					}
				}
			}
		}
		//t3lib_div::Debug($data);
	//	echo $this->panelAction;
		if ($this->panelAction == 'RETRIEVE') {
			return $data[$uid];
		} else {
			return $data;
		}
	}

	function printAsOptionLinks($item_key, $wrap = '', $urlOnly = false){ //($uid, $label = "%%%show%%%", $urlOnly=false, $action = "retrieve", $pid=false, $ajax=true, $saveContainer=true
		$wrap = explode('|', $wrap);
		$options = $this->get($item_key);
		$config = $this->controller->configurations->getArrayCopy();
		$options = explode(',', $config['view.']['data'][$config['storage.']['nodes']][$item_key]);
		foreach ($options as $key=>$option) {
			$option_exploded = explode('__', $option);
			if (count($option_exploded) > 1) {
				if ($config['storage.']['nameSpace'] == $option_exploded[0]) {
					if (!$urlOnly) {
						echo $wrap[0];
						$this->printAsSingleLink($option_exploded[1], $config['view.']['setup'][$item_key]['options.'][$option], 0, 'retrieve', $config['setup.']['singlePid'], 0);
						echo $wrap[1];
					} else {
						$urls[$option]['url'] = $this->printAsSingleLink($option_exploded[1], $config['view.']['setup'][$item_key]['options.'][$option], 1, 'retrieve', $config['setup.']['singlePid'], 0);
						$urls[$option]['label'] = $config['view.']['setup'][$item_key]['options.'][$option];
					}
				} elseif ($option_exploded[0] == 'pages') {
					if (!$urlOnly) { // TODO: RealURL konform machen
						echo $wrap[0] . '<a href="index.php?id=' . $option_exploded[1] . '" />' . $config['view.']['setup'][$item_key]['options.'][$option] . '</a>' . $wrap[1];
					} else {
						$urls[$option]['url'] = $this->printAsSingleLink($option_exploded[1], $config['view.']['setup'][$item_key]['options.'][$option], 1);
						$urls[$option]['label'] = $config['view.']['setup'][$item_key]['options.'][$option];
					}
				}
			}
		}
		if ($urlOnly) {
			return $urls;
		}
	}
}
?>