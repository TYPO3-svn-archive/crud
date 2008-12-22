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
 * @subpackage tx_cruds
 */
//tx_div::load('tx_crud_views_retrieve');
require_once(t3lib_extMgm::extPath('crud') . 'views/class.tx_crud__views_retrieve.php');
class tx_crud__views_browse extends tx_crud__views_retrieve {
	var $urlData;
	var $config;
	var $panelAction = "BROWSE";

	function __construct(&$controller) {
		parent::__construct($controller);
		$typoscript=$this->controller->configurations->getArrayCopy();
		$this->config=$typoscript['view.'];
		$this->limit=$this->config['limit'];
		$this->page=$this->config['page'];
		$this->count=$this->config['count'];
		$this->start=$this->config['start'];
		//$this->controller->parameters=new tx_lib_object(t3lib_div::_GP($this->controller->getDesignator()));
	}

	function printAsForward($label="%%%next%%%",$urlOnly=false) {
		$pars = $this->controller->parameters->getArrayCopy();
		$anz = ceil($this->config['count'] / $this->config['limit']);
		$data = $pars;
		$data['ajaxTarget'] = $this->getAjaxTarget("printAsForward");
		if(is_array($pars['search'])) $data['track']=1;
		$data["page"] = $this->page + 1;
		if ($this->page + 1 < $anz){
			if($urlOnly) echo $this->getUrl($data);
			else echo $this->getTag($label,$data);
		}
		else return false;
	}
	
	function printAsReverse($label="%%%prev%%%",$urlOnly=false) {
		$pars = $this->controller->parameters->getArrayCopy();
		$data = $pars;
		$data["page"] = $this->page - 1;
		$data['ajaxTarget'] = $this->getAjaxTarget("printAsReverse");
		if(is_array($pars['search'])) $data['track']=1;
		if ($data['page'] >= 0) {
			if($urlOnly)echo $this->getUrl($data);
			else echo $this->getTag($label,$data);
		}
	}

	function printAsBegin($label="&laquo;",$urlOnly=false) {
		$data = $pars;
		$data["page"] = 0;
		$data['ajaxTarget'] = $this->getAjaxTarget("printAsBegin");
		if(is_array($pars['search'])) $data['track']=1;
		if ($data['page'] > 0) {
			if($urlOnly)echo $this->getUrl($data);
			else echo $this->getTag($label,$data);
		}
		else return false;
	}

	function printAsEnd($label="&raquo;",$urlOnly=false) {
		$pars = $this->controller->parameters->getArrayCopy();
		$anz = ceil($this->config['count'] / $this->config['limit']);
		$data = $pars;
		$data["page"] = $anz - 1;
		$data['ajaxTarget'] = $this->getAjaxTarget("printAsEnd");
		if(is_array($pars['search'])) $data['track']=1;
		if ($this->page + 1 < $anz) {
			if($urlOnly) echo $this->getUrl($data);
			else echo $this->getTag($label,$data);
		}
		else return false;
	}

	function printAsBrowse($pages="3|3",$label="%%%pages%%% ") {
		$pars = $this->controller->parameters->getArrayCopy();
		$pages = explode("|",$pages);
		$anz = ceil($this->config['count'] / $this->config['limit']);
		$data = $pars;
		$data['ajaxTarget'] = $this->getAjaxTarget("printAsBrowse");
		if (is_array($pars['search'])) {
			$data['track'] = 1;
		}
		if ($pars['page']) {
			$current = $pars['page'] + 1;
		}
		//if (strlen($data['search']) < 1) {
		//	unset($data['search']);
		//} else {
		//	$current = 1;
		//}
		$now = '<strong>' . $current . '</strong>';
		if (empty($pages[1])) {
			$pages[1] = $pages[0];
		}
		if($current<1) $current=0;
		for ($i = $current; $i < ($pages[1] + $current); $i++) {
			$data["page"] = $i;
			if ($i < $anz) {
				$forward .= $this->getTag($data["page"]+1,$data);
			}
		}
		if ($current < $pages[0]) {
			$reverse = $current;
		} else {
			$reverse = $pages[0];
		}
		$revD = array();
		for ($i = 1; $i < $reverse + 1; $i++) {
			$back = $current - $i;
			$data["page"] = $back - 1;
			if ($back >= 1) {
				$revD[] = $this->getTag($back,$data);
			}
		}
		//t3lib_div::debug($data);
		$revD = array_reverse($revD);
		$rev = implode("",$revD);
		$rev = str_replace($this->getDesignator()."%5BajaxTarget%5D","ajaxTarget",$rev);
		$forward = str_replace($this->getDesignator()."%5BajaxTarget%5D","ajaxTarget",$forward);
		$rev = str_replace($this->getDesignator()."[ajaxTarget]","ajaxTarget",$rev);
		$forward = str_replace($this->getDesignator()."[ajaxTarget]","ajaxTarget",$forward);
		echo $label . $rev . $now . $forward;
	}

	function printAsSorting($what,$label="%%%sort%%%",$urlOnly=false) { 
		$pars = $this->controller->parameters->getArrayCopy();
		$anz = ceil($this->config['count'] / $this->config['limit']);
		$data = $pars;
		unset($data['upper']);
		unset($data['lower']);
		unset($data['track']);
		if (strlen($data['search']) < 1) unset($data['search']);
		$data["page"] = $this->page;
		$data['ajaxTarget'] = $this->getAjaxTarget("printAsSorting");
		if ($pars['upper']) {
			unset($data['upper']);
			$data["lower"] = $what;
			if($what==$pars['upper'])$image='<img src="typo3conf/ext/crud/resources/icons/sort_asc.gif" alt="%%%sort%%% '.$what.'"/>';
			else $image='<img src="typo3conf/ext/crud/resources/icons/sort.gif" alt="%%%sort%%% '.$what.'"/>';
		} elseif ($pars['lower']) {
			unset($data['lower']);
			$data["upper"] = $what;
			if($what==$pars['lower'])$image='<img src="typo3conf/ext/crud/resources/icons/sort_desc.gif" alt="%%%sort%%% '.$what.'"/>';
			else $image='<img src="typo3conf/ext/crud/resources/icons/sort.gif" alt="%%%sort%%% '.$what.'"/>';
		} else {
			$data["lower"] = $what;
			$image='<img src="typo3conf/ext/crud/resources/icons/sort.gif" alt="%%%sort%%% '.$what.'"/>';
		}
		if(!$urlOnly) echo $this->getTag($image.$label,$data);
		else echo $this->getUrl($data);

	}

	function printAsNoSearch() {
		$pars = $this->controller->parameters->getArrayCopy();
		if ($pars['search'] && !is_array($pars['search'])) {
			$data = $this->urlData;
			unset($data['track']);
			unset($data['search']);
			$out = $this->getUrl($data);
			echo '<form method="post" action="' . $out . '"><div>
				<input type="hidden" name="ajaxTarget" value="' . $this->getAjaxTarget("printAsNoSearch") . '" />
				<input type="hidden" name="' . $this->getDesignator() . '[search] id="' . $this->getDesignator() . '-search-input" value="" />
				<input type="submit" value="X" />
			</div></form>'; // TODO: value="X" Localization
		}
	}

	function printAsSingleLink($uid,$label="%%%show%%%",$urlOnly=false,$action="retrieve") {
		$pars = $this->controller->parameters->getArrayCopy();
		$pars['retrieve'] = $uid;
		$pars['action'] = $action;
		$data = $pars;
		$data["ajaxTarget"]=$this->getAjaxTarget("printAsSingleLink");
		if (is_array($data['search'])) {
			unset($data['search']);
			$data['track'] = 1;
		}
		if ($this->page >= 1) {
			$data['page'] = $this->page;
		}
		if($urlOnly) return $this->getUrl($data);
		else echo $this->getTag($label,$data);
	}

	function printAsSearch() {
		$pars = $this->controller->parameters->getArrayCopy();
		$data = $pars;
		unset($data['track']);
		unset($data['page']);;
		if (is_array($pars['search'])) {
			$pars['search'] = "";
		}
		$out = '<form method="post" action="' . $this->getUrl($data) . '"><div>
			<input type="hidden" name="ajaxTarget" value="' . $this->getAjaxTarget("printAsSearch") . '" />
			<input type="text" name="' . $this->getDesignator() . '[search]" value="' . $pars['search'] . '" />
			<input type="submit" value="Search" />'; //TODO: Localization
		
		$out .= $hidden . '</div></form>';
		echo $out;
	}

	function printAsLimit($steps="10",$max="50",$wrap="") {
		$pars = $this->controller->parameters->getArrayCopy();
		$data = $pars;
		unset($data['page']);
		if (strlen($data['search']) < 1) {
			unset($data['search']);
		}
		$data['ajaxTarget'] = $this->getAjaxTarget("printAsLimit");
		$anz = ceil($this->config['count'] / $this->config['limit']);
		$wrap = explode("|",$wrap);
		unset($data['limit']);
		if ($data['page'] > $anz) {
			$data['page'] = $anz - 1;
		}
		if (is_array($data)) {
			foreach ($data as $key=>$val) {
				$hidden .= '<input type="hidden" name="' . $this->getDesignator() . '[' . $key . ']" value="' . $val . '" />';
			}
		}
		$form = '<form id="' . $this->getDesignator() . '-limit" method="post" action="' . $this->getUrl($data) . '"><div>
			<input type="hidden" name="ajaxTarget" value="' . $this->getAjaxTarget("printAsLimit") . '" />
			<select name="' . $this->getDesignator() . '[limit]" onchange="ajax4onClick(this)">';
		$step = $steps;
		for ($i = 0; $i <= $this->count + 1; $i++) {
			if ($step <= $this->count && $step <= $max) {
				if ($step == $pars['limit']) {
					$selected = ' selected="selected"';
				} else {
					$selected = "";
				}
			//$form .= '<option value="' . $step . '" ' . $selected . ' onclick="this.form.submit();">' . $step . '</option>';
			$form .= '<option value="' . $step . '" ' . $selected . '>' . $step . '</option>' . "\n\t";

			}
			$step = $step + $steps;

		}
		$form .= '</select>' . "\n";
		$form .= $hidden . '</div>';
		if (is_array($pars['search'])) {
			$form.='<input type="hidden" name="'.$this->getDesignator().'[track]" value="1" />';
		}
		$form.='</form>';
		$form = str_replace("%5B","[",$form);
		$form = str_replace("%5D","]",$form);
		$out = $form;
		if ($this->count > $steps) {
			echo $wrap[0] . $out . $wrap[1];
		}
	}

	function printFilter($wrap="No Sorting by |") { //TODO: Localization
		$pars = $this->controller->parameters->getArrayCopy();
		$wrap = explode("|",$wrap);
		if ($pars['upper'] || $pars['lower']) {
			$data = $pars;
			$data["ajaxTarget"] = $this->getAjaxTarget("printFilter");
			if (isset($pars['upper'])) {
				$what = $pars['upper'];
			} else {
				$what = $this->parameters['lower'];
			}
			$label = $wrap[0] . $this->getLL($this->form[$what]['label']) . $wrap[1];
			if ($pars['upper']) {
				$data['upper'] = "";
			}
			if ($pars['lower']) {
				$data['lower'] = "";
			}
			$out .= $this->getUrl($data);
			$out = str_replace($this->getDesignator()."%5BajaxTarget%5D","ajaxTarget",$out);
			echo $out;
		}
	}


	function printAsArray($data,$fields,$elementWrap=false,$allWrap=false) {
		if ($elementWrap) {
			$elementWrap = explode("|",$elementWrap);
		}
		if ($allWrap) {
			$allWrap = explode("|",$allWrap);
		}
		$middle = false;
		if (is_array($data)) {
			foreach ($data as $uid=>$value) {
				$data = false;
				foreach ($fields as $field=>$fieldWrap) {
					if ($value[$field]) {
						$wrap = explode("|",$fieldWrap);
						$data .= $wrap[0] . $value[$field] . $wrap[1];
					}
				}
				if ($data) {
					$middle .= $elementWrap[0] . $data . $elementWrap[1];
				}
			}
		}
		if ($middle) {
			$out = $allWrap[0] . $middle . $allWrap[1];
		}
		if ($out) {
			echo $out;
		}
	}

	function printAsCommalist($data,$wrapAll,$wrapElement) {
		if (!empty($data)) {
			$data = explode(",",$data);
		}
		if (is_array($data)) {
			$wrapAll = explode("|",$wrapAll);
			$wrapElement = explode("|",$wrapElement);
			$out = $wrapAll[0];
			foreach ($data as $value) {
				$out .= $wrapElement[0] . $value . $wrapElement[1];
			}
			$out .= $wrapAll[1];
			echo $out;
		}
	}

	function getSearchUrl($pid,$params,$pars=false,$return=false) {
		$params = $this->controller->parameters->getArrayCopy();
		$params['ajaxTarget'] = $this->getAjaxTarget("getSearchUrl");
		
		$config = $this->controller->configurations->getArrayCopy();
		
		
		$url = $this->getUrl($params);
		$url = str_replace("%5B","[",$url);
		$url = str_replace("%5D","]",$url);
		$url = str_replace($this->getDesignator() . "%5BajaxTarget%5D","ajaxTarget",$url);
		if ($return) {
			return $url;
		} else {
			echo $url;
		}
	}

	function getFilterSelect($field,$label,$pid,$class="",$id="") {
		$setup = $this->controller->configurations->getArrayCopy();
		$host = 'http://' . $_SERVER['HTTP_HOST'] . str_replace("index.php","",$_SERVER['SCRIPT_NAME']);
		$path = str_replace("/index.php","",$_SERVER['SCRIPT_NAME']);
		$data = $this->get("existingValues");
		$pars = $this->controller->parameters->getArrayCopy();
		unset($pars['page']);
		$tca = $data['setup'][$field];
		$url = $this->getSearchUrl($pid,$pars,array(),1);
		//debug($url,"url");
		$select = '<form action="' . $url . '" name="selector" method="post">';
		if (is_array($data[$field]) && count($data[$field]) > 1) {
			//$select.='<select  onChange="javascript:document.selector.submit();" name="partner[country]">';
			$select = '<select onchange="top.location=options[selectedIndex].value" name="partner[country]">';
			if($pars[$field]) {
				$params = $pars;
				unset($params[$field]);
				//$first['option']=$this->getSearchUrl($pid,$params,array(),1);
				$first['value'] = "Filter leeren"; //TODO: Localization
			} else {
				//$first['option']=$this->getSearchUrl($pid,array(),array(),1);
				$first['value'] = $label;
			}
			$select .= '<option value="'.$first['option'].'">'.$first['value'].'</option>';
			foreach($data[$field] as $uid=>$value) {
				if ($tca['config.']['type'] == "input") {
					$target = $this->getSearchUrl($pid,$pars,array($field=>$value['title']),1);
					if ($pars[$field] == $value['title']) {
						$selected = " selected";
					} else {
						$selected = "";
					}
				} elseif ($tca['config.']['type'] == "select") {
					$target = $this->getSearchUrl($pid,$pars,array($field=>$uid),1);
					if ($pars[$field] == $uid) {
						$selected=" selected";
					} else {
						$selected = "";
					}
				}
				$select .= '<option value="'.$host.$target.'" '.$selected.'>'.$value['title']." (".$value['count'].")".'</option>' . "\n\t";
				//$select.='<option value="'.$uid.'">'.$value['title']." (".$value['count'].")".'</option>';
			}
			$select .= '</select>' . "\n" . '</form>';
			return $select;
		}
	}

	function getFilterUnselect($field,$label,$pid,$class="",$id="") {
		$setup = $this->controller->configurations->getArrayCopy();
		$host = 'http://' . $_SERVER['HTTP_HOST'] . str_replace("index.php","",$_SERVER['SCRIPT_NAME']);
		$path = str_replace("/index.php","",$_SERVER['SCRIPT_NAME']);
		$pars = $this->controller->parameters->getArrayCopy();
		$data = $this->get("existingValues");
		$tca = $data['setup'][$field];
		//debug($data);
		if ($pars[$field]) {
			//debug($data);
			if ($tca['config.']['type']=="select") {
				$value = $tca['options.'][$pars[$field]];
				unset($pars[$field]);
				$url = $this->getSearchUrl($pid,$pars,array(),1);
				if (count($data) >= 1) {
					foreach($data[$field] as $key=>$val) {
						if ($val['title'] == $value) {
							$count = $val['count'];
							break;
						}
					}
				}
			}
			$out = $label . $value . " (" . $count . ")" . '<a href="' . $url . '"><img src="typo3conf/ext/partner__listing/resources/images/list_remove_btn.gif" border="0" alt="" /></a>';
			echo $out;
		} else {
			$value = $pars[$field];
			unset($pars[$field]);
			$url = $this->getSearchUrl($pid,$pars,array(),1);
			$typoscript = $this->controller->configurations->getArrayCopy();
			$count = $typoscript[$this->controller->action."."]["view."]["count"];
			$out = $label . $value . " (" . $count . ")" . '<a href="' . $url . '"><img src="typo3conf/ext/partner__listing/resources/images/list_remove_btn.gif" border="0" alt="" /></a>';
			echo $out;
		}
	}


	function existSelect($fields) {
		$fields = explode(",",$fields);
		$size = count($fields);
		$pars = $this->controller->parameters->getArrayCopy();
		$parsCounter = 0;
		if (is_array($fields)) {
			foreach ($fields as $field) {
				if (!$this->getFilterSelect($field,$field,1)) {
					$size--;
				}
				if ($pars[$field]) {
					$parsCounter++;
				}
			}
		}
		if ($parsCounter < $size) {
			return true;
		}
	}
}

?>