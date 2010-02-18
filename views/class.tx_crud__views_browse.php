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

require_once (t3lib_extMgm::extPath ( 'crud' ) . 'views/class.tx_crud__views_retrieve.php');
class tx_crud__views_browse extends tx_crud__views_retrieve {
	var $panelAction = 'BROWSE';

	// -------------------------------------------------------------------------------------
	// VIEW SETUP
	// -------------------------------------------------------------------------------------

	/**
	 * Setup of the View
	 *
 	 * @param	object	$controller	the controller reference
	 * @return	void
	 */
	function setup(&$controller) {
		parent::setup ( $controller );
		$typoscript = $this->controller->configurations->getArrayCopy ();
		$this->config = $typoscript ['view.'];
		$this->limit = $this->config ['limit'];
		$this->page = $this->config ['page'];
		$this->count = $this->config ['count'];
		$this->start = $this->config ['start'];
	}

	// -------------------------------------------------------------------------------------
	// BROWSE ELEMENTS HELPER
	// -------------------------------------------------------------------------------------

	/**
	 * print an forward link for the listing
	 *
 	 * @param	string	$label	the label for the link
 	 * @param 	boolean	$urlOnly	if set only the url will returned
 	 * @param   string	$wrap	the wrap for the elemtent
	 * @return	void
	 */
	function printAsForward($label = '%%%next%%%', $urlOnly = false, $outerWrap='', $innerWrap='', $style='', $ajax=true) {
		$config = $this->controller->configurations->getArrayCopy();
		$innerWrap = explode('|', $innerWrap);
		$outerWrap = explode('|', $outerWrap);
		$pars = $this->parameters;
		$anz = ceil( $this->config ['count'] / $this->config ['limit'] );
		$data = $pars;
		if ($ajax) {
			$onClick = $this->getAjaxOnClick();
		}
		$data['ajaxTarget'] = $this->getAjaxTarget ('printAsForward');
		if (is_array( $pars ['search'] )) {
			unset($pars['search']);
			$data ['track'] = 1;
		}
		$data ['page'] = $this->page + 1;
		if ($this->page + 1 < $anz) {
			if ($urlOnly) {
				echo $this->getUrl ( $data );
			} else {
				echo $outerWrap[0] . '<a ' . $onClick . ' ' . $style . ' href="' . $this->getUrl ( $data ) . '">' . $innerWrap[0] . $label . $innerWrap[1] . '</a>' . $outerWrap[1];
			}
		} else {
			return false;
		}
	}

	/**
	 * print an backward link for the listing
	 *
 	 * @param	string	$label	the label for the link
 	 * @param 	boolean	$urlOnly	if set only the url will returned
 	 * @param   string	$wrap	the wrap for the elemtent
	 * @return	void
	 */
	function printAsReverse($label = '%%%prev%%%', $urlOnly = false, $outerWrap='', $innerWrap='', $style='', $ajax=true) {
		$innerWrap = explode('|', $innerWrap);
		$outerWrap = explode('|', $outerWrap);
		$pars = $this->controller->parameters->getArrayCopy();
		$data = $pars;
		$data['page'] = $this->page - 1;
		if ($ajax) {
			$onClick = $this->getAjaxOnClick();
		}
		$data['ajaxTarget'] = $this->getAjaxTarget('printAsReverse');
		if (is_array( $pars ['search'] )) {
			unset($pars['search']);
			$data ['track'] = 1;
		}
		if ($data ['page'] >= 0) {
			if ($urlOnly) {
				echo $this->getUrl ( $data );
			} else {
				echo $outerWrap[0] . '<a ' . $onClick . ' ' . $style . ' href="' . $this->getUrl($data) . '">' . $innerWrap[0] . $label . $innerWrap[1] . '</a>' . $outerWrap[1];
			}
		}
	}

	/**
	 * print a link to next page for the listing
	 *
 	 * @param	string	$label	the label for the link
 	 * @param 	boolean	$urlOnly	if set only the url will returned
 	 * @param   string	$wrap	the wrap for the elemtent
	 * @return	void
	 */
	function printAsBegin($label = '&laquo;', $urlOnly = false, $outerWrap='', $innerWrap='', $style='', $ajax=true) {
		$pars=$this->controller->parameters->getArrayCopy();
		$innerWrap = explode('|', $innerWrap);
		$outerWrap = explode('|', $outerWrap);
		$data = $pars;
		if ($ajax) {
			$onClick = $this->getAjaxOnClick();
		}
		unset($data['page']);
		$data ['ajaxTarget'] = $this->getAjaxTarget('printAsBegin');
		if (is_array( $pars ['search'] )) {
			unset($pars['search']);
			$data ['track'] = 1;
		}
		if ($pars['page'] >= 1) {
			if ($urlOnly) {
				echo $this->getUrl ( $data );
			} else {
				echo $outerWrap[0] . '<a ' . $onClick . ' ' . $style . ' href="' . $this->getUrl($data ) . '">' . $innerWrap[0] . $label . $innerWrap[1] . '</a>' . $outerWrap[1];
			}
		}
	}

	/**
	 * print a link to the end of the listing
	 *
 	 * @param	string	$label	the label for the link
 	 * @param 	boolean	$urlOnly	if set only the url will returned
	 * @param   string	$wrap	the wrap for the elemtent
	 * @return	void
	 */
	function printAsEnd($label = '&raquo;', $urlOnly = false, $outerWrap='', $innerWrap='', $style='', $ajax=true) {
		$innerWrap = explode('|', $innerWrap);
		$outerWrap = explode('|', $outerWrap);
		$pars = $this->controller->parameters->getArrayCopy ();
		$anz = ceil( $this->config ['count'] / $this->config ['limit'] );
		$data = $pars;
		$data['page'] = $anz - 1;
		if ($ajax) {
			$onClick = $this->getAjaxOnClick();
		}
		$data['ajaxTarget'] = $this->getAjaxTarget('printAsEnd');
		if (is_array( $pars ['search'] )) {
			unset($pars['search']);
			$data ['track'] = 1;
		}
		if ($this->page + 1 < $anz) {
			if ($urlOnly) {
				echo $this->getUrl ( $data );
			} else {
				echo $outerWrap[0] . '<a ' . $onClick . ' ' . $style . ' href="' . $this->getUrl($data) . '">' . $innerWrap[0] . $label . $innerWrap[1] . '</a>' . $outerWrap[1];
			}
		} else {
			return false;
		}
	}

	/**
	 * print a sorting link
	 *
 	 * @param	string	$what	wich db field to sort (the key of the setup)
 	 * @param 	string	$label	the label for the sorting link
 	 * @param 	boolean	$urlOnly	if set only the url will returned
	 * @return	void
	 */
	function printAsSorting($what, $label = '%%%sort%%%', $urlOnly=false, $ajax=true, $pars=false) {
		if (!$pars) {
			$pars = $this->controller->parameters->getArrayCopy();
		}
		$typoscript = $this->controller->configurations->getArrayCopy();
		$anz = ceil( $this->config ['count'] / $this->config ['limit'] );
		$data = $pars;
		unset($data['upper']);
		unset($data['lower']);
		unset($data['saveContainer']);
		if (is_array( $pars['search'] )) {
			unset($pars['search']);
			$data ['track'] = 1;
		}
		$data['page'] = $this->page;;
		if ($pars ['upper']) {
			unset( $data['upper'] );
			$data['lower'] = $what;
			if ($what == $pars['upper']) {
				$sorting = 'sort-up';
			} else {
				$sorting = 'sort';
			}
		} elseif ($pars['lower']) {
			unset ( $data['lower'] );
			$data['upper'] = $what;
			if ($what == $pars ['lower']) {
				$sorting = 'sort-down';
			} else {
				$sorting = 'sort';
			}
		} else {
			$data['lower'] = $what;
			$sorting = 'sort';
		}
		if (! $urlOnly) {
			if ($ajax) {
				$onClick = $this->getAjaxOnClick();
			}
			echo '<a class="' . $sorting . '" href="' . $this->getUrl($data) . '" ' . $onClick . '>' . $label . '</a>';
		} else {
			echo $this->getUrl ( $data );
		}
	}

	/**
	 * prints a page browser
	 *
 	 * @param	string	$pages	how much pages on the and right side should showed
  	 * @param 	string	$label	the label for the sorting link
 	 * @param 	string	$wrapCurrent	wrap for the actual page link
 	 * @param 	string	$wrapPageLinks	wrap for nonactive page links
	 * @return	void
	 */
	function printAsBrowse($pages='3|3', $label='%%%pages%%%', $wrapCurrent='<li class="current">|</li>', $wrapPageLinks='<li>|</li>', $innerWrap='', $outerWrap='', $ajax=true) {
		$pars = $this->controller->parameters->getArrayCopy();
		$pages = explode('|', $pages);
		$anz = ceil( $this->config ['count'] / $this->config ['limit'] );
		$data = $pars;
		$data ['ajaxTarget'] = $this->getAjaxTarget('printAsBrowse');
		if (is_array ( $pars ['search'] )) {
			unset($pars['search']);
			$data ['track'] = 1;
		}
		if ($pars ['page']) {
			$current = $pars ['page'] + 1;
		} else {
			$current = 1;
		}
		$wrapCurrent = explode('|', $wrapCurrent );
		$wrapPageLinks = explode('|', $wrapPageLinks );
		$innerWrap = explode('|', $innerWrap);
		$outerWrap = explode('|', $outerWrap);
		if ($ajax) {
			$onClick = $this->getAjaxOnClick();
		}
		$now = $wrapCurrent [0] . $current . $wrapCurrent [1];
		if (empty ( $pages [1] )) {
			$pages [1] = $pages [0];
		}
		if ($current < 1) {
			$current = 0;
		}
		for ($i = $current; $i < ($pages [1] + $current); $i ++) {
			$data['page'] = $i;
			if ($i < $anz) {
				//$forward .= $wrapPageLinks [0] . $this->getTag ( $data ["page"] + 1, $data ) . $wrapPageLinks [1];
				$forward .= $wrapPageLinks[0] . $outerWrap[0] . '<a ' . $onClick . ' ' . $style . ' href="' . $this->getUrl($data) . '">' . $innerWrap[0] . ($data['page'] + 1) . $innerWrap[1] . '</a>' . $outerWrap[1] . $wrapPageLinks [1];
			}
		}
		if ($current < $pages [0]) {
			$reverse = $current;
		} else {
			$reverse = $pages [0];
		}
		$revD = array ();
		for ($i = 1; $i < $reverse + 1; $i ++) {
			$back = $current - $i;
			$data['page'] = $back - 1;
			if ($back >= 1) {
				//$revD [] = $wrapPageLinks [0] . $this->getTag ( $back, $data ) . $wrapPageLinks [1];
				$revD [] = $wrapPageLinks[0] . $outerWrap[0] . '<a ' . $onClick . ' ' . $style . ' href="' . $this->getUrl($data) . '">' . $innerWrap[0] .  $back . $innerWrap[1] . '</a>' . $outerWrap[1] . $wrapPageLinks [1];
			}
		}
		$revD = array_reverse( $revD );
		$rev = implode( '', $revD );
		$rev = str_replace ( $this->getDesignator () . '%5BajaxTarget%5D', 'ajaxTarget', $rev );
		$forward = str_replace ( $this->getDesignator () . '%5BajaxTarget%5D', 'ajaxTarget', $forward );
		$rev = str_replace ( $this->getDesignator () . '[ajaxTarget]', 'ajaxTarget', $rev );
		$forward = str_replace ( $this->getDesignator () . '[ajaxTarget]', 'ajaxTarget', $forward );
		echo $label . $rev . $now . $forward;
	}

	/**
	 * prints a selectbox for choosing  the record per page
	 *
 	 * @param	integer	$steps	the steps for the select
  	 * @param 	integer	$max	how much records maximal per page
 	 * @param 	string	$wrap	wrap for the select
	 * @return	void
	 */
	function printAsLimit($steps='10', $max='50', $wrap='') {
		$pars = $this->controller->parameters->getArrayCopy ();
		$config = $this->controller->configurations->getArrayCopy();
		$data = $pars;
		unset ( $data ['page'] );
		if (is_array( $pars ['search'] )) {
			unset($pars['search']);
			$data ['track'] = 1;
		}
		$data['ajaxTarget'] = $this->getAjaxTarget('printAsLimit');
		$anz = ceil( $this->config ['count'] / $this->config ['limit'] );
		$wrap = explode( '|', $wrap );
		unset ( $data ['limit'] );
		if ($data ['page'] > $anz) {
			$data ['page'] = $anz - 1;
		}
		if (is_array ( $data )) {
			foreach ( $data as $key => $val ) {
				$hidden .= "\n\t" . '<input type="hidden" name="' . $this->getDesignator () . '[' . $key . ']" value="' . $val . '" />';
			}
		}
		$form = '<form class="' . $this->getDesignator () . '-limit yform" method="post" action="' . $this->getUrl ( $data ) . '"><div class="type-select">
			<input type="hidden" name="ajaxTarget" value="' . $this->getAjaxTarget('printAsLimit') . '" />
			<input type="hidden" name="aID" value="' . tx_crud__div::getActionID($config)  . '" />
			<select class="type-select" name="' . $this->getDesignator() . '[limit]" onchange="ajax4onClick(this)">'; // TODO: JS-Aufruf per Domscripting
		$step = $steps;
		if (! isset ( $pars ['limit'] )) {
			$pars['limit'] = $config['view.']['limit'];
		}
		for ($i = 0; $i <= $this->count + 1; $i ++) {
			if ($step <= $this->count && $step <= $max) {
				if ($step == $pars ['limit']) {
					$selected = ' selected="selected"';
				} else {
					$selected = "";
				}
				$form .= '<option value="' . $step . '" ' . $selected . '>' . $step . '</option>' . "\n\t";
			}
			$step = $step + $steps;
		}
		$form .= '</select>' . "\n";
		$form .= $hidden . '</div>';
		if (is_array ( $pars ['search'] )) {
			$form .= '<input type="hidden" name="' . $this->getDesignator () . '[track]" value="1" />';
		}
		$form .= '</form>';
		$form = str_replace( '%5B', '[', $form );
		$form = str_replace( '%5D', ']', $form );
		$out = $form;
		if ($config['view.']['count'] > ($steps * 2)) {
			echo $wrap[0] . $out . $wrap[1];
		}
	}

	// -------------------------------------------------------------------------------------
	// BROWSE CONTENT HELPER
	// -------------------------------------------------------------------------------------


	function printAsArray($data, $fields, $elementWrap = false, $allWrap = false) {
		if ($elementWrap) {
			$elementWrap = explode( '|', $elementWrap );
		}
		if ($allWrap) {
			$allWrap = explode( '|', $allWrap );
		}
		$middle = false;
		if (is_array ( $data )) {
			foreach ( $data as $uid => $value ) {
				$data = false;
				foreach ( $fields as $field => $fieldWrap ) {
					if ($value [$field]) {
						$wrap = explode( '|', $fieldWrap );
						$data .= $wrap[0] . $value [$field] . $wrap[1];
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

	function printAsCommalist($data, $wrapAll, $wrapElement) {
		if (! empty ( $data )) {
			$data = explode( ',', $data );
		}
		if (is_array ( $data )) {
			$wrapAll = explode ( '|', $wrapAll );
			$wrapElement = explode ( '|', $wrapElement );
			$out = $wrapAll[0];
			foreach ( $data as $value ) {
				$out .= $wrapElement[0] . $value . $wrapElement[1];
			}
			$out .= $wrapAll[1];
			echo $out;
		}
	}


	// -------------------------------------------------------------------------------------
	// BROWSE SEARCH HELPER
	// -------------------------------------------------------------------------------------

	/**
	 * prints a searchbox
	 *
 	 * @param	string	$fields	the db fieldnames
	 * @return	boolean
	 */
	function printAsSearch($label = '%%%search%%%', $wrap='', $class='autocomplete', $ajax=true, $pid=false, $id=false, $pars=false, $noSearch='%%%clearSearch%%%') {
		if (!$pid) {
			$pid=$GLOBALS['TSFE']->id;
		}
		if (!$pars) {
			$pars = $this->controller->parameters->getArrayCopy();
		}
		$config = $this->controller->configurations->getArrayCopy();
		$data = $pars;
		unset ( $data['track'] );
		unset ( $data['page'] );
		if (is_array ( $pars['search'] )) {
			$pars ['search'] = '';
		}
		$out = '';
		if ($class == 'autocomplete') {
			if (isset($config['view.']['autocomplete.']['minChars'])) {
				$ac_minChars = $config['view.']['autocomplete.']['minChars'];
			} else {
				$ac_minChars = 4;
			}
			if (isset($config['view.']['autocomplete.']['maxResultsPerCat'])) {
				$ac_maxResultsPerCat = $config['view.']['autocomplete.']['maxResultsPerCat'];
			} else {
				$ac_maxResultsPerCat = 3;
			}
			if (isset($config['view.']['autocomplete.']['showHitCount'])) {
				$ac_showHitCount = $config['view.']['autocomplete.']['showHitCount'];
			} else {
				$ac_showHitCount = true;
			}
			$out .= '<script type="text/javascript">ac_minChars=' . $ac_minChars . '; ac_maxResultsPerCat = ' . $ac_maxResultsPerCat . '; ac_showHitCount = ' . $ac_showHitCount . ';</script>';
		}
		$wrap = explode( '|', $wrap );
		if (isset($class)) {
			$class = ' class="' . $class . '" ';
		}
		if (strlen($id) >=3) {
			$id = ' id="' . $id . '" ';
		}

		$out .= $wrap[0] . '<form method="post" action="' . $this->getUrl(array(),$pid) . '" class="yform printAsSearch"><div class="type-text">';
			if ($ajax) {
				$out .= '<input type="hidden" name="ajaxTarget" value="' . $this->getAjaxTarget('printAsSearch') . '" />';
			}
			$out .= '<input type="hidden" name="aID" value="' . tx_crud__div::getActionID($config) . '" />';
			$out .= '<input ' . $class . $id . ' size="30" type="text" name="' . $this->getDesignator () . '[find]" value="' . $pars ['find'] . '" />';
			$out .= '<button type="submit"><span>' . $label . '</span></button>';
			if (!empty($noSearch)) {
				$out .= $this->printAsNoSearch($noSearch, $ajax, true);
			}
		$out .= $hidden . '</div></form>' . $wrap[1];
		echo $out;
	}

	function printAsNoSearch($label='%%%clearSearch%%%', $ajax=false, $return=false) {
		$pars = $this->controller->parameters->getArrayCopy();
		$config = $this->controller->configurations->getArrayCopy();
		if (!empty($pars['find']) && !is_array($pars['search'])) {
 			$data=$pars;
			unset ( $data ['track'] );
			unset ( $data ['find'] );
			unset ( $data ['search'] );
			$out = $this->getUrl( $data,$GLOBALS['TSFE']->id,0,1);
			if ($ajax) {
				$js = $this->getAjaxTarget('printAsNoSearch') ;
			}
			if (!$return) {
				echo '<a ' . $js . ' href="' . $out . '" >' . $label . '</a>';
			} else {
				return '<a ' . $js . ' href="' . $out . '" >' . $label . '</a>';
			}
		}
	}

	function getSearchUrlXXX($pid, $params, $pars = false, $return = false) {
		$params = $this->controller->parameters->getArrayCopy ();
		$params['ajaxTarget'] = $this->getAjaxTarget('getSearchUrl');
		$config = $this->controller->configurations->getArrayCopy ();
		$url = $this->getUrl ( $params );
		$url = str_replace( '%5B', '[', $url );
		$url = str_replace( '%5D', ']', $url );
		$url = str_replace( $this->getDesignator () . '%5BajaxTarget%5D', 'ajaxTarget', $url );
		if ($return) {
			return $url;
		} else {
			echo $url;
		}
	}

	// -------------------------------------------------------------------------------------
	// BROWSE FILTER HELPER
	// -------------------------------------------------------------------------------------

/**
	 * returns a link to filter the listing with a value
	 *
 	 * @param	string	$value	the value to filter
  	 * @param 	array	$item	the setup of the value
	 * @return	void
	 */
	function getSortingLink($value, $item,$ajax=true) {
		$pars = $this->controller->parameters->getArrayCopy ();
		unset($pars['track']);
		//echo $value;
		$value_exploded = explode( ',', $value );
		$item_key = $item ['key'];
		if ($item['config.']['type'] == 'check' && is_array ( $item ['options.'] )) {
			foreach ( $item['options.'] as $key => $val ) {
				$checkbox [$val] = $key;
			}
			$y = 1;
			for ($i = 1; $i <= count ( $item['config.']['items'] ); $i++) {
				$o[$y] = $y;
				$y = $y * 2;
			}
			foreach ( $o as $val ) {
				$options[] = $val;
			}
			foreach ( $value_exploded as $check ) {
				$checkLabels[] = $this->getLL( $item['options.'][$checkbox[$check]], 1 );
				$checkValue += ($options[$checkbox[$check]]);
				$data [] = $this->getLL( $item['options.'][$checkbox[$check]], 1 );
			}
			if (isset($pars['page'])) {
				unset($pars['page']);
			}
			unset($pars['search'][$item['key']]);
			$url = $this->getUrl( $pars );// . '&' . $this->getDesignator () . '[search][' . $item ['key'] . '][is]=' . $checkValue;


		} elseif (strlen ( $value ) >= 1 || $value===0)
			foreach ( $value_exploded as $val ) {
			//	unset($pars['search'][$item['key']]);
				///$url = $this->getUrl ( $pars ,$GLOBALS['TSFE']->id,1);
				$renderPars = $pars;
				unset($renderPars['search']);
				if (is_array ( $item ['options.'] ) && in_array ( $val, $item ['options.'] )) {
					//echo "ok".$value;
					$keys = array_keys ( $item ['options.'], $val );
					if (is_array ( $pars ['search'] [$item_key] ) && strlen ( $pars ['search'] [$item_key] ['is'] ) >= 1)
						$pars_exploded = explode ( ",", $pars ['search'] [$item_key] ['is'] );
					elseif (is_array ( $pars ['search'] [$item_key] ) && strlen ( $pars ['search'] [$item_key] ['mm'] ) >= 1)
						$pars_exploded = explode ( ",", $pars ['search'] [$item_key] ['mm'] );
					if (is_array ( $pars ['search'] [$item_key] ) and (strlen ( $pars ['search'] [$item_key] ['is'] ) >= 1 or strlen ( $pars ['search'] [$item_key] ['mm'] ) >= 1) and is_array ( $pars_exploded ) and in_array ( $keys [0], $pars_exploded )) {
						$data [] = $this->getLL ( $val, 1 );
					} else {
						$value = $this->getLL ( $val, 1 );
						unset ( $pars ['track'] );
						unset ( $pars ['page'] );
						if (isset ( $item ['config.'] ['MM'] )) {
							$renderPars['search'][$item['key']]['mm'] = $keys[0];
						} else {
							$renderPars['search'][$item['key']]['is'] = $keys[0];
						}
						if (isset ( $pars ['search'] [$item_key] ['is'] ) && $pars ['search'] [$item_key] ['is'] != $keys [0]) {
							$renderPars['search'][$item['key']]['is'] .= ',' . $pars ['search'] [$item_key] ['is'];
						}
						if (isset ( $pars ['search'] [$item_key] ['mm'] ) && $pars ['search'] [$item_key] ['mm'] != $keys [0]) {
							$renderPars['search'][$item['key']]['mm'] .= ',' . $pars ['search'] [$item_key] ['mm'];
						}
						$renderPars = $pars;
						if (is_array ( $orgPars['search'][$item_key] )) {
							unset( $orgPars['search'][$item_key] );
						}
						if (is_array ( $orgPars['search'] )) {
							foreach ( $orgPars['search'] as $name => $val ) {
								if (isset ( $val['mm'] )) {
									$renderPars['search'][$name]['mm'] = $val['mm'];
								} elseif (isset ( $val['is'] )) {
									$renderPars['search'][$name]['is'] = $val['is'];
								}
							}
						}
						if (isset($renderPars['page'])) {
							unset($renderPars['page']);
						}
						$url = $this->getUrl($renderPars, $GLOBALS['TSFE']->id, 1);
						$url = str_replace('&amp;', '&', $url);
						$url = str_replace('&', '&amp;', $url);
						if ($ajax) {
							$onClick = $this->getAjaxOnClick();
						}
						$data[] = '<a ' . $onClick . ' href="' . $url . '">' . $value . '</a>';
					}
				} else {
					$renderPars['search'][$item['key']]['is'] = $value;
					$url = $this->getUrl($renderPars, $GLOBALS['TSFE']->id, 1);
					$url = str_replace('&amp;', '&', $url);
					$url = str_replace('&', '&amp;', $url);
					if ($ajax) {
						$onClick=$this->getAjaxOnClick();
					}
					$data [] = '<a '.$onClick.' href="' . $url . '">' . $value . '</a>';
				}
			}
		if (is_array($data)) {
			return implode ( ', ', $data );
		}
	}




	/**
	 * returns an array with all active filter
	 *
	 * @return	array	all active filters
	 */
	function getActiveFilters() {
		$pars = $this->controller->parameters->getArrayCopy ();
		//$pars ['ajaxTarget'] = $this->getAjaxTarget ( "getActiveFilters" );
		unset ( $pars ['track'] );
		unset ( $pars ['page'] );
		$setup = $this->get('setup');
		if (is_array ( $pars['search'] )) {
			foreach ( $pars['search'] as $item_key => $value ) {
				$parsCopy = $pars;
				unset($parsCopy['search'][$item_key] );
				$v = '';
				$v_array = array();
				if (isset ( $value['is'])) {
					$value_exploded = explode(',', $value['is']);
				} elseif (isset ( $value['mm'])) {
					$value_exploded = explode(',', $value['mm']);
				} elseif (isset ( $value['min'])) {
					$value_exploded = explode(',', $value['min']);
				} elseif (isset ( $value['max'])) {
					$value_exploded = explode(',', $value['max']);
				} else {
					$value_exploded = explode(',', $value);
				}
				foreach ($value_exploded as $key=>$val) {
					if (isset ( $setup[$item_key]['options.'][$val] )) {
						$active[$item_key][$val] = $this->getLL( $setup [$item_key] ['options.'] [$val], 1 );
					} elseif (strlen($value) >= 1 && !is_array($value)) {
						$active[$item_key][$value] = $value;
					}
				}
			}
		}
		//elseif(strlen($pars['search'])>=3) $active["Suche"][][]
		if (is_array ( $active )) {
			foreach ( $active as $key => $data ) {
				foreach ( $data as $value => $label ) {
					$pars = $this->controller->parameters->getArrayCopy ();
					unset($pars['page']);
					$orgData = $data;
					unset($orgData[$value]);
					$extras = '';
					if (is_array($orgData)) {
						foreach ($orgData as $v2=>$label2) {
							$extras[] = $v2;
						}
					}
					unset( $pars['track'] );
					unset( $pars['search'][$key] );
					if (is_array($extras)) {
						if (isset($setup[$key]['config.']['MM'])) {
							$pars['search'][$key]['mm'] = implode(',', $extras);
						} else {
							$pars['search'][$key]['is'] = implode(',', $extras);
						}
					}
					$pars ['ajaxTarget'] = $this->getAjaxTarget('getActiveFilters');
					$url = $this->getUrl ( $pars, $GLOBALS['TSFE']->id, 1);
					$return[$key][$value][$url] = $label;
				}
			}
			if ($return) {
				return $return;
			}
		} else {
			return false;
		}
	}

	/**
	 * print a filter select with values set by the typoscript getExistValues
	 *
 	 * @param	string	$field	the db fieldname
  	 * @param 	string	$label	the label for the select
  	 * @param 	integer	$pid	the target page id for the select
	 * @return	string	the filter select from
	 */
	function printAsFilterList($table, $field, $label, $pid, $start=0, $limit=20, $wrapAll='<ul>|</ul>', $wrapElement='<li>|</li>', $returnOnly=false) {
		$setup = $this->controller->configurations->getArrayCopy ();
		$data = $setup['view.']['existingValues'][$table];
		$pars = $this->controller->parameters->getArrayCopy ();
		unset ( $pars['page'] );
		unset ( $pars['track'] );
		$tca = $setup['view.']['setup']['setup'][$field];
		$pars['ajaxTarget'] = $this->getAjaxTarget('printAsFilterList');
		if (is_array($pars['search']) && isset($pars['search'][$field])) {
			unset($pars['search'][$field]);
		}
		$wrapElement = explode('|', $wrapElement);
		$wrapAll = explode('|', $wrapAll);
		if (!$pid) {
			$pid = $GLOBALS['TSFE']->id;
		}
		$url = $this->getUrl($pars, $pid, 1);
		//echo $url;

		if (is_array ( $data [$field] ) && count($data[$field]) >= 1) {
			$select = $wrapAll[0];
			//echo $split;
			foreach ( array_slice($data[$field], $start, $limit, true) as $string => $count ) {
				$url_exploded = explode('?', $url);
				if (sizeof($url_exploded) > 1) {
					$split = '&';
				} else {
					$split = '?';
				}
				$select .= $wrapElement[0];
				if (is_array($setup['view.']['setup'][$field]['options.']) && strlen($setup['view.']['setup'][$field]['options.'][$string]) <= 1) {
					$select .= '<a href="' . $url . $split . $this->getDesignator() . '[search][' . $field . ']=' . urlencode($string). '" ' . $selected . '>nicht angegeben (' . $count . ')' . '</a>' . "\n\t";
				} elseif ($setup['view.']['setup'][$field]['config.']['MM']) {
					$select .= '<a href="' .  $url . $split . $this->getDesignator() . '[search][' . $field . '][mm]=' . urlencode($string) . '">' . $setup['view.']['setup'][$field]['options.'][$string] . ' (' . $count . ')' . '</a>' . "\n\t";
				} elseif (is_array($setup['view.']['setup'][$field]['options.'])) {
					$select .= '<a href="' .  $url . $split . $this->getDesignator() . '[search][' . $field . '][is]=' . urlencode($string) . '">' . $this->getLL($setup['view.']['setup'][$field]['options.'][$string],1) . ' (' . $count . ')' . '</a>' . "\n\t";
				} else {
					$select .= '<a href="' .  $url . $split . $this->getDesignator() . '[search][' . $field . ']=' . urlencode($string) . '">' . $string . ' (' . $count . ')' . '</a>' . "\n\t";
				}
				$select .= $wrapElement[1];
				$split = '&';
			}
			$select = str_replace('&amp;', '&', $select);
			$select = str_replace('&', '&amp;', $select);
			echo $select . $wrapAll[1];
		}
	}

	/**
	 * print a filter select with values set by the typoscript getExistValues
	 *
 	 * @param	string	$field	the db fieldname
  	 * @param 	string	$label	the label for the select
  	 * @param 	integer	$pid	the target page id for the select
	 * @return	string	the filter select from
	 */
	function printAsFilterSelect($table, $field, $label, $pid, $start=0, $limit=20) {
		$setup = $this->controller->configurations->getArrayCopy ();
		$data = $setup['view.']['existingValues'][$table];
		$pars = $this->controller->parameters->getArrayCopy ();
		unset ( $pars ['page'] );
		unset ( $pars ['track'] );
		$tca = $setup['view.']['setup'] ['setup'] [$field];
		$pars['ajaxTarget'] = $this->getAjaxTarget('printAsFilterSelect');
		if (!$pid) {
			$pid = $GLOBALS['TSFE']->id;
		}
		$url = $setup['setup.']['baseURL'].$this->getUrl ($pars,$pid,1);
		$select = '<form action="" method="post" class="yform yformfull"><div class="type-select">';
		if (is_array ( $data [$field] )) {
			$select .= '<select onchange="top.location=options[selectedIndex].value" name="select">';
			if ($pars[$field]) {
				$params = $pars;
				unset ( $params[$field] );
				$first ['value'] = 'Filter leeren';
			} else {
				$first ['value'] = $label;
			}
			$select .= '<option value="' . $first ['option'] . '">' . $first ['value'] . '</option>';
			$i = 0;
			foreach ( array_slice($data[$field],$start,$limit,true)  as $string => $count ) {
				$url_exploded = explode('?', $url);
				if (sizeof($url_exploded)>1) {
					$split = '&';
				} else {
					$split = '?';
				}
				$i++;
				if (is_array($setup['view.']['setup'][$field]['options.']) && strlen($setup['view.']['setup'][$field]['options.'][$string])<=1) {
					$select .= '<option value="' .  $url . $split . $this->getDesignator() . '[search][' . $field . '][is]=' . urlencode($string) . '" ' . $selected . '>nicht angegeben (' . $count . ')' . '</option>' . "\n\t";
				} elseif ($setup['view.']['setup'][$field]['config.']['MM']) {
					$select .= '<option value="' .  $url . $split . $this->getDesignator() . '[search][' . $field . '][mm]=' . urlencode($string) . '" ' . $selected . '>' . $setup['view.']['setup'][$field]['options.'][$string] . ' (' . $count . ')' . '</option>' . "\n\t";
				} elseif (is_array($setup['view.']['setup'][$field]['options.'])) {
					$select .= '<option value="' .  $url . $split . $this->getDesignator() . '[search][' . $field . '][is]=' . urlencode($string) . '" ' . $selected . '>' . $this->getLL($setup['view.']['setup'][$field]['options.'][$string], 1) . ' (' . $count . ')' . '</option>' . "\n\t";
				} else {
					$select .= '<option value="' .  $url . $split . $this->getDesignator() . '[search][' . $field . ']=' . urlencode($string) . '" ' . $selected . '>' . $string . " (" . $count . ")" . '</option>' . "\n\t";
				}
			}
		}
		$select = str_replace ('&amp;', '&', $select );
		$select = str_replace ('&', '&amp;', $select );
		$select .= '</select>' . "\n" . '</div></form>' . "\n";
		if ($i >= 1) {
			echo $select;
		}
	}

	function getTimeLine($table, $field, $start=false, $max=false, $first='Y', $second='n') {

		if (!$max) {
			$max = (time() * 2);
		}
		if (!$start) {
			$start = (time() / 2);
		}
		$config = $this->controller->configurations->getArrayCopy();
		if (!is_array($config['view.']['existingValues'][$table][$field])) {
			return false;
		}

		foreach ($config['view.']['existingValues'][$table][$field] as $tstamp=>$count ) {
			if ($tstamp >= $start &&  $tstamp <= $max) {
				$year = date($first,$tstamp);
				$month = date($second,$tstamp);
				$dates[$year][$month] += $count;
			}
		}
		asort($dates);
		foreach($dates AS $key => $value) {
			ksort($value);
			$dates[$key] = array_reverse($value, true);
		}
		$dates_ok = array_reverse($dates, true);
		return $dates_ok;
	}

	/**
	 * print a filter select with values set by the typoscript getExistValues
	 *
 	 * @param	string	$field	the db fieldname
  	 * @param 	string	$label	the label for the select
  	 * @param 	integer	$pid	the target page id for the select
	 * @return	string	the filter select from
	 */
	function getFilterSelect($table, $field, $label, $pid) {
		$setup = $this->controller->configurations->getArrayCopy ();
		$host = 'http://' . $_SERVER ['HTTP_HOST'] . str_replace ( 'index.php', '', $_SERVER ['SCRIPT_NAME'] );
		$path = str_replace ( '/index.php', '', $_SERVER ['SCRIPT_NAME'] );
		$data = $setup['view.']["existingValues"][$table];
		$pars = $this->controller->parameters->getArrayCopy ();
		unset ( $pars ['page'] );
		$tca = $data ['setup'] [$field];
		$url = $this->getUrl ( $pars, $pid, 1, 1 );
		$select = '<form action="' . $url . '" name="selector" method="post" class="yform">' . "\n\t";
		if (is_array ( $data [$field] ) && count ( $data [$field] ) > 1) {
			$select = '<select onchange="top.location=options[selectedIndex].value" name="partner[country]">';
			if ($pars [$field]) {
				$params = $pars;
				unset ( $params [$field] );
				$first ['value'] = 'Filter leeren'; //TODO: Localization
			} else {
				$first ['value'] = $label;
			}
			$select .= "\n\t\t" . '<option value="' . $first ['option'] . '">' . $first ['value'] . '</option>';
			foreach ( $data[$field]  as $string => $count ) {
				if ($tca ['config.']['type'] == 'input') {
					//$target = $this->getSearchUrl ( $pid);
					if ($pars [$field] == $string) {
						$selected = ' selected';
					} else {
						$selected = '';
					}
				} elseif ($tca ['config.']['type'] == 'select') {
					//$target = $this->getSearchUrl ( );
					if ($pars [$field] == $key) {
						$selected = ' selected';
					} else {
						$selected = '';
					}
				}
				if (($setup['view.']['setup'][$field]['options.'])) {
					$select .= '<option value="' .  $this->baseUrl . '&amp;' . $this->getDesignator() . '[search][' . $field . '][mm]=' . urlencode($string) . '" ' . $selected . '>' . $setup['view.']['setup'][$field]['options.'][$string] . ' (' . $count . ')' . '</option>' . "\n\t";
				}
				//else $select .= '<option value="' .  $this->baseUrl. "&".$this->getDesignator()."[search][".$field."]=".urlencode($string). '" ' . $selected . '>' . $string . " (" . $count . ")" . '</option>' . "\n\t";
			}
			$select .= '</select>' . "\n" . '</form>';
			return $select;
		}
	}

	/**
	 * prints a list for deselecting a filter set by the typoscript getExistValues
	 *
 	 * @param	string	$field	the db fieldname
  	 * @param 	string	$label	the label for the select
  	 * @param 	integer	$pid	the target page id for the select
	 * @return	string	the filter deselect html
	 */
	function getFilterUnselect($field, $label, $pid) {
		$setup = $this->controller->configurations->getArrayCopy ();
		$host = 'http://' . $_SERVER ['HTTP_HOST'] . str_replace ( 'index.php', '', $_SERVER ['SCRIPT_NAME'] );
		$path = str_replace ( '/index.php', '', $_SERVER ['SCRIPT_NAME'] );
		$pars = $this->controller->parameters->getArrayCopy ();
		$data = $setup['view.']['existingValues'];
		$tca = $data ['setup'] [$field];
		if ($pars [$field]) {
			if ($tca ['config.'] ['type'] == 'select') {
				$value = $tca ['options.'] [$pars [$field]];
				unset ( $pars [$field] );
				$url = $this->getSearchUrl ( $pid, $pars, array (), 1 );
				if (count ( $data ) >= 1) {
					foreach ( $data [$field] as $key => $val ) {
						if ($val ['title'] == $value) {
							$count = $val ['count'];
							break;
						}
					}
				}
			}
			//$out = $label . $value . " (" . $count . ")" . '<a href="' . $url . '"><img src="typo3conf/ext/partner__listing/resources/images/list_remove_btn.gif" border="0" alt="" /></a>';
			echo $out;
		} else {
			$value = $pars [$field];
			unset ( $pars [$field] );
			$url = $this->getSearchUrl ( $pid, $pars, array (), 1 );
			$typoscript = $this->controller->configurations->getArrayCopy ();
			$count = $typoscript['view.']['existingValues'][$field]['count'];
			//$out = $label . $value . " (" . $count . ")" . '<a href="' . $url . '"><img src="typo3conf/ext/partner__listing/resources/images/list_remove_btn.gif" border="0" alt="" /></a>';
			echo $out;
		}
	}

	/**
	 * checks if an existingValues filter is set
	 *
 	 * @param	string	$fields	the db fieldnames
	 * @return	boolean
	 */
	function existSelect($fields) {
		$fields = explode ( ',', $fields );
		$size = count ( $fields );
		$pars = $this->controller->parameters->getArrayCopy ();
		$parsCounter = 0;
		if (is_array ( $fields )) {
			foreach ( $fields as $field ) {
				if (! $this->getFilterSelect ( $field, $field, 1 )) {
					$size --;
				}
				if ($pars [$field]) {
					$parsCounter ++;
				}
			}
		}
		if ($parsCounter < $size) {
			return true;
		}
	}
}
?>