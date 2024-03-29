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

require_once (t3lib_extMgm::extPath ( 'crud' ) . 'library/class.tx_crud__formBase.php');
require_once (t3lib_extMgm::extPath ( 'crud' ) . 'views/class.tx_crud__views_common.php');
class tx_crud__views_create extends tx_crud__views_common {

	var $viewAction = 'CREATE';

	// -------------------------------------------------------------------------------------
	// FORM HELPER
	// -------------------------------------------------------------------------------------

	/**
	 * Prints the form start tag and makes an instance of tx_crud__formBase
	 *
	 * @return	string	form start tag
	 */
	function printAsFormHeader($url=false, $class=false) {
		$setup = $this->controller->configurations->getArrayCopy ();
		//if (! is_object ( $this->form )) {
			$formEngineClassName = tx_div::makeInstanceClassName ('tx_crud__formBase');
			$this->form = new $formEngineClassName ( $this->controller );
			$this->form->setup = $this->get ('setup');
			$this->form->controller = $this->controller;
		//}
		//
		if ($class) {
			$style = ' class="' . $class . '"';
		}
		echo "\n" . '<form ' . $style . ' method="post" action="' . $this->getUrl(array()) . '" enctype="multipart/form-data">' . "\n\t";
		//echo $this->form->begin($this->getDesignator(), array('name' => $this->getDesignator()), $url, $class);
	}

	/**
	 * Prints an link to exit the form
	 *
	 * @param	$label	the label
	 * @return	string	link to cancel the form action
	 */
	function printAsFormCancel($label='Cancel', $ajax=true, $pid=false) {
		if (!$pid) {
			$pid = $GLOBALS['TSFE']->id;
		}
		$pars = $this->controller->parameters->getArrayCopy ();
		$config = $this->controller->configurations->getArrayCopy ();
		$data = $pars;
		unset ( $data ['action'] );
		unset ( $data ['retrieve'] );
		if ($pars['track'] >= 1) {
			$data['track'] = 1;
		}
		if ($this->page >= 1) {
			$data['page'] = $this->page;
		}
		if ($ajax) {
			$onClick = $this->getAjaxOnClick(tx_crud__div::getAjaxTarget($config, 'printAsFormCancel'), tx_crud__div::getActionID($config, $mID), false, false);
		}
		$out = '<a href="' . $this->getUrl($data, $pid) . '" ' . $onClick . '>' . $label . '</a>';
		echo $out;
	}

	/**
	 * Prints the submit button and checks for an enabled RTE and includes tinyMCE
	 *
	 * @param	$label	the label
	 * @return	string	submit button for the form
	 */
	function printAsFormSubmit($label='%%%submit%%%', $wrap='', $type='submit', $ajax=true) {
		$image = 'typo3conf/ext/crud/resources/icons/preview.gif';
		$wrap = explode('|', $wrap);
		//if($ajax) $form .= '<input type="hidden" name="ajax" value="1" />';
		if($ajax) {
			$form .= '<input type="hidden" name="ajaxTarget" value="' . $this->getAjaxTarget('printAsFormSubmit') . '" />';
		}
		if($ajax) {
			$form .= '<input type="hidden" name="aID" value="' . tx_crud__div::getActionID ($this->controller->configurations->getArrayCopy()) . '" />';
		}
		$conf = $this->controller->configurations->getArrayCopy ();
		$tinymce = $conf ['view.'] ['tinymce.'];
		$storage = $conf ['storage.'];
		$form .= '<input type="hidden" name="' . $this->getDesignator() . '[form]" value="' . tx_crud__div::getActionID ( $conf ) . '" />';
		$form .= '<input type="hidden" name="' . $this->getDesignator() . '[process]" value="preview" />';
		$form .= '<input type="hidden" name="' . $this->getDesignator() . '[action]" value="' . str_replace('Action', '', $this->controller->action) . '" />';
		if ($this->viewAction == 'UPDATE') {
			$form .= '<input type="hidden" name="' . $this->getDesignator() . '[retrieve]" value="' . $conf['storage.']['nodes'] . '" />';
		}
		if ($type == 'button') {
			$form .= '<button type="submit" name="' . $this->getDesignator() . '[submit]">' . $wrap[0] . $label . $wrap[1] . '</button>';
		} else {
			$form .= '<input type="submit" name="' . $this->getDesignator() . '[submit]" value="' . $label . '" alt="' . $label . '" />';
		}
		$conf = $this->get('setup');
		if ($tinymce ['enable'] == 1 && is_array ( $conf )) {
			foreach ( $conf as $key => $val ) {
				if ($val ['element'] == 'rteRow') {
					$rte ['default.'][$key] = $tinymce ['default.'];
				} elseif ($val ['element'] == 'textareaRow' || $val ['element'] == 'textarea') {
					$rte ['noRTE'][$key] = $key;
				}
			}
		}
		if (is_array ( $rte ['default.'] )) {
			//$this->headerData ['libraries'] ['tinymce'] = '<script language="javascript" type="text/javascript" src="typo3conf/ext/crud/resources/tiny_mce/tiny_mce.js"></script>';
			unset ( $tinymce ['enable'] );
			$tiny = '<script type="text/javascript">
			function enableTinyMCE(){ tinyMCEpresent = true;';
			foreach ( $tinymce as $key => $al ) {
				unset ( $tinymce[$key]['cols'] );
				unset ( $tinymce[$key]['rows'] );
				unset ( $tinymce[$key]['fields'] );
				$tiny .= 'tinyMCE.init({';
				if (is_array ( $tinymce [$key] )) {
					foreach ( $tinymce [$key] as $key2 => $val2 ) {
						$tiny .= '' . $key2 . ' : "' . $val2 . '",';
					}
				}
				$key = str_replace ( '.', '', $key );
				$tiny .= 'editor_selector : "tinymce_' . $key . '"});';
			}
			$tiny .= "} \nenableTinyMCE();</script>";
			//$GLOBALS ['TSFE']->additionalFooterData [] = $tiny;
		}
		echo $form . $tiny;
	}

	/**
	 * Prints the form close tag
	 *
	 * @return	string	link to cancel the form action
	 */
	function printAsFormFooter() {
		$code = '</form>';
		echo $code;
	}

	/**
	 * Prints an link to exit the form
	 *
	 * @param	$label	the label
	 * @return	string	link to cancel the form action
	 */
	function printAsExitLink($label='back') {
		$pars = $this->controller->parameters->getArrayCopy ();
	//	$pars ['ajaxTarget'] = $this->getAjaxTarget ( "getExitLink" );
		$out = '<a href="' . $this->getUrl ( $pars ) . '">' . $label . '</a>';
		return $out;
	}

	/**
	 * Prints an array as checkboxes
	 *
	 * @return	string	checkboxes
	 */
	function printAsCheckbox($array, $name, $start = 0, $stop = -1, $wrap = '|') {
		$wrapExpl = explode('|', $wrap);
		if ($stop == -1)
			$stop = count($array);
		for ($i = $start; $i < $stop; $i++)
			$result .= $wrapExpl[0] . '<input type="checkbox" name="' . $this->getDesignator() . '['.$name.']" value="' . $i . '" />' . $array[$i] . $wrapExpl[1];
		return $result;
	}

	// -------------------------------------------------------------------------------------
	// SETUP HELPER
	// -------------------------------------------------------------------------------------

	/**
	 * render the setup from the model to forms
	 *
	 * @param 	array	$entryList	the setup for the form
	 * @return	array	the rendered setup
	 */
	function renderSetup($entryList) {
		$setup = $this->controller->configurations->getArrayCopy ();
		unset($this->html);
		$pars = $this->controller->configurations->getArrayCopy ();
		if (! is_array ( $this->html )) {
			foreach ( $entryList as $key => $entry ) {
				$this->renderEntry ( $entry );
			}
		}
		return $this->html;
	}

	/**
	 * render a single entry from the setup to a form element
	 *
	 * @param 	array	$entry	a single form element setup to render
	 * @return	void
	 */
	function renderEntry($entry) {
		$start = microtime ( true );
		$setup = $this->controller->configurations->getArrayCopy ();
		if (! is_object ( $this->form )) {
			$formEngineClassName = tx_div::makeInstanceClassName('tx_crud__formBase');
			$this->form = new $formEngineClassName ( $this->controller );
			$this->form->setup = $this->get ('setup');
			$this->form->controller = $this->controller;
		}
		$entry ['label'] = $this->getLL ( $entry ['label'], $entry ['key'] );
		if (is_array ( $entry ['options.'] )) {
			foreach ( $entry ['options.'] as $key => $val ) {
				$entry ['options.'] [$key] = $this->getLL ( $val, $key );
			}
		}
		if (is_array ( $entry ['attributes.'] ['options.'] )) {
			foreach ( $entry ['attributes.'] ['options.'] as $key => $val ) {
				$entry ['attributes.'] ['options.'] [$key] = $this->getLL ( $val, $key, 1 );
			}
		}
		if (! $entry ['divider'] || $entry ['divider'] == 'General') {
			$entry ['divider'] = '%%%' . strtolower ( $this->viewAction ) . '%%%' . " " . $this->getLL ( $setup ['view.'] ['title'] );
		}

		$label = $entry ['label'];
		$eval_exploded = explode ( ',', $entry['config.']['eval']);
		foreach ( $eval_exploded as $key => $val ) {
			$eval [$val] = $val;
		}
		$this->html [$entry ['divider']][$entry ['section']][$entry['key']] = $entry;

		if ($eval ['captcha']) {
			$this->html [$entry ['divider']] [$entry ['section']] [$entry ['key']] ['html'] = $this->form->captchaRow ( $entry ['key'], $label, $entry ['attributes.'] );
		} elseif ($entry ['element'] == 'inputRow') {
			$this->html [$entry ['divider']] [$entry ['section']] [$entry ['key']] ['html'] = $this->form->inputRow ( $entry ['key'], $label, $entry ['attributes.'] );
		} elseif ($entry ['element'] == 'dateTimeRow') {
			$this->html [$entry ['divider']] [$entry ['section']] [$entry ['key']] ['html'] = $this->form->dateTimeRow ( $entry ['key'], $label, $entry ['attributes.'] );

		} elseif ($entry ['element'] == 'dateRow') {
			$this->html [$entry ['divider']] [$entry ['section']] [$entry ['key']] ['html'] = $this->form->dateRow ( $entry ['key'], $label, $entry ['attributes.'] );

		} elseif ($entry ['element'] == 'passwordRow') {
			$this->html [$entry ['divider']] [$entry ['section']] [$entry ['key']] ['html'] = $this->form->passwordRow ( $entry ['key'], $label, $entry ['attributes.'] );
		} elseif ($entry ['element'] == 'multicheckbox') {
			$this->html [$entry ['divider']] [$entry ['section']] [$entry ['key']] ['html'] = $this->form->multicheckbox ( $entry ['key'], $label, $entry ['attributes.'] );
		} elseif ($entry ['element'] == 'rteRow') {
			$this->html [$entry ['divider']] [$entry ['section']] [$entry ['key']] ['html'] = $this->form->rteRow ( $entry ['key'], $label, $entry ['attributes.'] );
		} elseif ($entry ['element'] == 'radio') {
			$this->html [$entry ['divider']] [$entry ['section']] [$entry ['key']] ['html'] = $this->form->radio ( $entry ['key'], $label, $entry ['attributes.'] );
		} elseif ($entry ['element'] == 'checkboxRow') {
			$this->html [$entry ['divider']] [$entry ['section']] [$entry ['key']] ['html'] = $this->form->checkboxRow ( $entry ['key'], $label, $entry ['attributes.'] );
		}
		elseif ($entry ['element'] == 'selectRow') {
			if($entry['config.']['foreign_table'] == 'tx_categories') {
				$this->html [$entry ['divider']] [$entry ['section']] [$entry ['key']] ['html'] = $this->form->categoryRow ( $entry ['key'], $label, $entry ['attributes.'], $entry ['options.'] );
			}
			else $this->html [$entry ['divider']] [$entry ['section']] [$entry ['key']] ['html'] = $this->form->selectRow ( $entry ['key'], $label, $entry ['attributes.'], $entry ['options.'] );
			//$this->html [$entry ['divider']] [$entry ['section']] [$entry ['key']] ['html'] = $this->form->selectRow ( $entry ['key'], $label, $entry ['attributes.'], $entry ['options.'] );
		}
		elseif ($entry ['element'] == 'multiselectRow') {
			if ($entry['config.']['foreign_table'] == 'tx_categories') {
				$this->html [$entry ['divider']] [$entry ['section']] [$entry ['key']] ['html'] = $this->form->categoryRow ( $entry ['key'], $label, $entry ['attributes.'], $entry ['options.'] );
			}
			else $this->html [$entry ['divider']] [$entry ['section']] [$entry ['key']] ['html'] = $this->form->multiselectRow ( $entry ['key'], $label, $entry ['attributes.'], $entry ['options.'] );
		} elseif ($entry ['element'] == 'textareaRow') {
			$this->html [$entry ['divider']] [$entry ['section']] [$entry ['key']] ['html'] = $this->form->textareaRow ( $entry ['key'], $label, $entry ['attributes.'] );
		} elseif ($entry ['element'] == 'fileRow') {
			$this->html [$entry ['divider']] [$entry ['section']] [$entry ['key']] ['html'] = $this->form->fileRow ( $entry ['key'], $label, $entry ['attributes.'] );
		} elseif ($entry ['element'] == 'noFileRow') {
			$this->html [$entry ['divider']] [$entry ['section']] [$entry ['key']] ['html'] = $this->form->noFileRow ( $entry ['key'], $label, $entry ['attributes.'] );
		} elseif ($entry ['element'] == 'multiFileRow') {
			$this->html [$entry ['divider']] [$entry ['section']] [$entry ['key']] ['html'] = $this->form->multiFileRow ( $entry ['key'], $label, $entry ['attributes.'] );
		} else {
			return FALSE;
		}
		if ($entry ['key'] != 'captcha' && $entry['element'] != 'multiFileRow') {
			$html_exploded = explode ( '<dd>', $this->html [$entry ['divider']] [$entry ['section']] [$entry ['key']] ['html'] );
			$html = str_replace ( '</dd>', '', $html_exploded[1] );
			$this->html [$entry ['divider']] [$entry ['section']] [$entry ['key']] ['html'] = str_replace ( "\n", "", $html );
		}
	}

	/**
	 * adds a manual entry to to form wich is not defined in the setup
	 *
	 * @param 	array	$entry	an complete setup for element
	 * @param	string	$content	the html of the form elemnt
	 * @return	void
	 */
	function addPlainEntry($entry, $content) {
		$this->html [$entry ['divider']] [$entry ['section']] [$entry ['key']] ['html'] = $content;
	}

	// -------------------------------------------------------------------------------------
	// ERROR HELPER
	// -------------------------------------------------------------------------------------


	/**
	 * translate and search an error for a form element.
	 * if not the complete locallangkey isset, it fallback to the crud locallocal.xml
	 *
	 * @param 	string	$localLangKey	the error of the locallang or a complete locallang path with the errro(LLL:EXT"crud/locallangxml:someError)
	 * @param 	string	$key	optional the key of the formelement to get a a special field error
	 * @return	string	the translated error
	 */
	function getError($localLangKey, $key = false) {
		$string = @explode ( ':', $localLangKey );
		//t3lib_div::debug($string);
		$config = $this->controller->configurations->getArrayCopy ();
		$path = $string [1] . ':' . $string [2];
		//echo $path;
		if ($path != 'EXT:crud/locallang.xml')
			$pathFallback = 'EXT:crud/locallang.xml';
		$action = $this->controller->action;
		$table = $config['storage.']['nameSpace'];
		//t3lib_div::Debug($string,$key);
		//die();
		if ($string [0] == 'LLL') {
			if (!isset($string[3])) {
				$string[3] = $string[2];
			}
			//$what [0] = $table . "." . $action . "." . $key . "." . $string [3];

			//$what [1] = $table . "." . $key . "." . $string [3];
			$what[0] = $key . '.' . $string[3];
			$what[1] = $string[3];
			//t3lib_div::debug($what);
			foreach ( $what as $key => $str ) {
				$LLL = 'LLL:' . $path . ':' . $str;
				//echo $LLL;
				//die();
				if ($translated = $this->getLL ( $LLL ))
					//echo $translated;
					break;
			}
			if (! $translated && $pathFallback) {
				//echo $pathFallback;
			//	t3lib_div::debug($what);
				$translated = $this->getLL('LLL:' . $pathFallback . ':' . $what[1] );
			}
		}
		if ($translated) {
			return $translated;
		} else {
			return false;
		}
	}

	/**
	 * returns an error for a form element.
	 *
	 * @param 	string	$localLangKey	the error without a path
	 * @param 	string	$key	optional the key of the formelement to get a a special field error
	 * @return	string	the translated error
	 */
	function getFormError($str, $key) {
		$config = $this->controller->configurations->getArrayCopy ();

		$LLL = 'LLL:' . $config['view.']['keyOfPathToLanguageFile'] . ':' . $str;
		//echo "str:".$LLL;
		//die();
		$str = $this->getError ( $LLL, $key );
		return $this->getEvalConfig ( $str, $key );
	}

	/**
	 * replace all amrker in the translated error from the tca eval configuration
	 *
	 * @param 	string	$localLangKey	the error without a path
	 * @param 	string	$key	optional the key of the formelement to get a a special field error
	 * @return	string	the translated error
	 */
	function getEvalConfig($str, $key = false) {
		$setup = $this->controller->configurations->getArrayCopy ();
		//t3lib_div::debug($setup);
		$pars = $this->controller->parameters->getArrayCopy ();
		if ($setup['view.']['setup'][$key]['config.']['internal_type'] == 'file') {
			if (is_array ( $_FILES[$this->getDesignator()]['name'][$key])) {
				foreach ( $_FILES[$this->getDesignator()]['name'][$key] as $uid => $file ) {
					if (! $setup['view.']['setup'][$key][$uid] && strlen ( $file ) >= 2) {
						$files [] = $file;
					}
				}
			}
			if (strlen ( $files ) >= 1) {
				$files = implode ( ', ', $files );
			}
			$setup[$key]['config.']['filename'] = $files;
		}
		$ts = $this->controller->configurations->getArrayCopy ();
		$eval = explode ( ',', $setup['view.']['setup'][$key]['config.']['eval'] );
		foreach ( $eval as $k ) {
			if (! empty ( $k )) {
				$evalTCA [$k] = $k;
			}
		}
		if (is_array ( $setup['view.']['setup'][$key]['config.'] )) {
			foreach ( $setup['view.']['setup'][$key]['config.'] as $name => $val ) {
				if (is_array ( $val ) && $name == 'range') {
					foreach ( $val as $name2 => $val2 ) {
						$marker = '###' . strtoupper($name2) . '###';
						$str_old = $str;
						if ($name2 == 'upper' || $name2 == 'lower' && is_numeric($val2)) {
							if ($evalTCA ['datetime']) {
								$value = strftime ( $this->getLL('LLL:EXT:crud/locallang.xml:datetimeTCA.output'), $val2 );
							}
							elseif($evalTCA ['date'])  {
								$value = strftime ( $this->getLL('LLL:EXT:crud/locallang.xml:dateTCA.output'), $val2 );
							}
							else {
								$value = $val2;
							}
							//t3lib_div::debug($value,$val2);
						}
						$str = str_replace ( $marker, $value, $str_old );
					}
				} else {
					$marker = '###' . strtoupper($name) . '###';
					$str_old = $str;
					if ($name == 'max_size') {
						$val . ' kb';
					}
					$str = str_replace( $marker, $val, $str_old );
				}
			}
		}
		return $str;
	}
}
?>