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
 * enhanced the lib formBase class with some extra elements
 * 
 * Depends on: lib/div
 *
 * @author Frank Thelemann <f.thelemann@yellowmed.com>
 * @package TYPO3
 * @subpackage tx_crud
 */
class tx_crud__formBase extends tx_lib_formBase {
	
	var $setup;
	var $_rowPattern = '%1$s<dt>%2$s</dt>
					%4$s<dd>%3$s</dd>';
	
	// -------------------------------------------------------------------------------------
	// EXTRA FORM  ELEMENTS
	// -------------------------------------------------------------------------------------
	
	/**
	 * prints an form start tag
	 *
	 * @param	string	$key	the exKey form the form
	 * @param 	array	$attributes	optional attributes like a style for the form
	 * @return	string	the form start tag
	 */
	function begin($key, $attributes = array(),$url=false,$class=false) {
		$this->setIdPrefix ( $this->getDesignator () );
		$attributes ['id'] = $key;
		$attributes ['action'] = $this->action ();
		if($class) $style=' class="'.$class.'"';
		$attributes ['method'] = $this->method ();
		$attributes ['name'] = $this->prefixId . "form";
		unset($attributes['aID']);
		unset($attributes['ajaxTarget']);
		//t3lib_div::Debug($attributes);
		$attrutes ['enctype'] = "multipart/form-data";
		if(!$url) {
			$url = $this->action ();
			$url = str_replace ( "no_cache=1", "", $url );
		}
		//t3lib_div::debug($_SERVER);
		echo "\r\n" . '<form '.$style.' method="post" action="'.$url.'" enctype="multipart/form-data">' . "\r\n";
	}
	
	/**
	 * prints multiple file upload input elements
	 *
	 * @param	string	$key	the exKey form the form
	 * @param 	array	$label	the label for the upload
	 * @return	string	html code for an upload
	 */
	function multiFileRow($key, $label, $attributes = array()) {
		$setup = $this->setup;
		if (! isset ( $attributes ['name'] )) {
			$this->_die ( 'Please set a name attribute for multiFileRow controls.', __FILE__, __LINE__ );
		}
		$y = 0;
		for($i = 0; $i < $setup [$key] ['config.'] ['maxitems']; $i ++) {
			if (strlen ( $setup [$key] ['value'] [$i] ) <= 0) {
				if ($y < $setup [$key] ['config.'] ['size']) {
					$inputs .= '<li><input type="file" title="' . $attributes ['title'] . '" name="' . $attributes ['name'] . '[' . $i . ']" maxlength="' . $attributes ['maxlength'] . '" /></li>' . "\n";
					$y ++;
				}
			} else {
				$files .= "<li>" . $this->makeFilePreview ( $setup [$key] ['value'] [$i] ) . '<a href="' . $setup [$key] ['value'] [$i] . '">' . $setup [$key] ['process'] [$i] . "</a>";
				$files .= "\n" . '<input type="hidden" name="' . $attributes ['name'] . '[' . $i . ']" value="' . $setup [$key] ['value'] [$i] . '" />';
				$files .= "\n" . '<input type="image" src="typo3conf/ext/crud/resources/icons/delete.gif" name="' . $this->getDesignator () . '[remove][' . $key . ']" value="' . $i . '" class="icon "/></li>' . "\n";
			}
		}
		$out = '<ul class="upload ' . $this->getDesignator () . '-multiUpload">' . $files . $inputs . "</ul>\n";
		return $out;
	}
	
	/**
	 * prints a image preview or if not an images, an icon for the file extension if exist
	 *
	 * @param	string	$url	the url to the file
	 * @return	string	html code for the preview
	 */
	function makeFilePreview($url) {
		$fileExtension_exploded = explode ( ".", $url );
		$fileExtension = strtolower ( $fileExtension_exploded [count ( $fileExtension_exploded ) - 1] );
		$images = array ("jpg", "jpeg", "png", "gif", "bmp" );
		if (in_array ( $fileExtension, $images )) {
			require_once (PATH_site . 'typo3/sysext/cms/tslib/class.tslib_content.php');
			require_once (PATH_site . 't3lib/class.t3lib_stdgraphic.php');
			require_once (PATH_site . 'typo3/sysext/cms/tslib/class.tslib_gifbuilder.php');
			$setup = $this->setup;
			$size = getimagesize ( $url );
			if ($size [1] > 30) {
				$size [1] = 30;
			}
			if ($size [0] > 30) {
				$size [0] = 30;
			}
			$imageClassName = tx_div::makeInstanceClassName ( 'tx_lib_image' );
			$image = new $imageClassName ( );
			$image->alt ( $setup [$key] ['label'] ); //TODO: img label
			$image->maxWidth ( $size [0] );
			$image->maxHeight ( $size [1] );
			$image->path ( $url );
			return $image->make ();
		} else {
			if (file_exists ( 'typo3conf/ext/crud/resources/icons/files/icon_' . strtolower ( $fileExtension ) . '.gif' )) {
				$img = '<img src="typo3conf/ext/crud/resources/icons/files/icon_' . strtolower ( $fileExtension ) . '.gif" alt="Filetype: ' . $fileExtension . ' " alt="" border="0" />';
				return $img;
			}
		}
	}
	
	/**
	 * prints an rte text field with tinyMCE
	 *
	 * @param	string	$key	the url to the file
	 * @param	string	$label	the label for the element
	 * @param	array	$attributes	the optional attributes for the element	
	 * @return	string	html code for the rte text field
	 */
	function rteRow($key, $label, $attributes = array()) {
		$conf = $this->controller->configurations->getArrayCopy ();
		$setup = $conf ['view'] ['setup.'];
		$rte = $conf ['view.'] ['tinymce.'];
		unset($rte ['enable']);
		foreach ($rte as $name=>$val) {
			if (!empty($val['fields'])) {
				$fields = explode(',', $val['fields'] );
				foreach ( $fields as $key2 => $val2 ) {
					if ($val2 == $key) {
						$class = 'tinymce_' . str_replace ('.', '', $name);
						if ($val ['cols'] > 1) {
							$attributes['cols'] = $val['cols'];
						}
						if ($val['rows'] > 1) {
							$attributes['rows'] = $val['rows'];
						}
					}
				}
			}
		}
		if (! $attributes ['cols']) {
			$attributes ['cols'] = 30;
		}
		if (! $attributes ['rows']) {
			$attributes ['rows'] = 5;
		}
		if (! $class) {
			$class = "tinymce_default";
		}
		$out = '<textarea id="' . $this->getDesignator () . "-" . $key . '" cols="' . $attributes ['cols'] . '" rows="' . $attributes ['rows'] . '" name="' . $this->getDesignator () . '[' . $key . ']' . '" class="' . $class . ' expand">' . $this->_getValue ( $key ) . '</textarea>' . "\n";
		return '<dt><label for="' . $this->getDesignator () . "-" . $key . '">' . $label . "</label></dt>\n\t
				<dd>" . $out . "</dd>\n";
	}
	
	function categorieRow($key,$label,$attributes= array()) {
		
	}
	
	/**
	 * prints an date with time form element
	 *
	 * @param	string	$key	the key of the form element
	 * @param	string	$label	the label for the element
	 * @param	array	$attributes	the optional attributes for the element	
	 * @return	string	html code for the datime form element
	 */
	function dateTimeRow($key, $label, $attributes = array()) {
		$setup = $this->setup;
		$date = $setup [$key] ['value'] ['date'];
		$time = $setup [$key] ['value'] ['time'];
		//t3lib_div::debug($setup[$key]['config.'],$key);
		if(isset($setup[$key]['config.']['range'])){
			$render = 'onRender: function(date) {
							return {
								disabled: (date.valueOf() < ' . $setup[$key]['config.']['range']['lower']. '000  || date.valueOf() > ' . $setup[$key]['config.']['range']['upper'] .'000)
							}
						},';
		}
		if($GLOBALS['TSFE']->config['config']['language'] == 'de') { 
			$locale = "locale: {
							days: ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag'],
							daysShort: ['Son', 'Mon', 'Die', 'Mit', 'Don', 'Fre', 'Sam', 'Son'],
							daysMin: ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So'],
							months: ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'],
							monthsShort: ['Jan', 'Feb', 'Mrz', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'],
							weekMin: 'KW'
						},"; 
		} // TODO: andere Sprachen, wenn nötig
		$output = explode(' ', $setup[$key]['config.']['output']);
		$out = '<ul class="crud-datetime" class="clearfix">' . "\n\t";
		$out .= '<li>%%%date%%% <input type="input" id="' . $this->getDesignator () . '-' . $key .'" name="' . $this->getDesignator () . '[' . $key . '][date]" value="' . $date . '" size="10" maxlength="10" /></li>' . "\n\t";
		$out .= '<li>%%%time%%% <input type="input" name="' . $this->getDesignator () . '[' . $key . '][time]" value="' . $time . '" size="5" maxlength="5" /></li>' . "\n" . '</ul>' . "\n";
		return '<dt><label for="' . $key . '">' . $label . '</label></dt>' . "\n\t" . '
				<dd>' . $out . '</dd>' . "\n".
				"<script type=\"text/javascript\">
					$('#" . $this->getDesignator () . '-' . $key ."').DatePicker({
						eventName: 'focus',
						format:'" . $output[1] . "',
						date: $('#" . $this->getDesignator () . '-' . $key ."').val(),
						current: $('#" . $this->getDesignator () . '-' . $key ."').val(),
						starts: 1,
						position: 'right',
						onBeforeShow: function(){
							if ( $('#" . $this->getDesignator () . '-' . $key ."').val().length > 0 ) {
								$('#" . $this->getDesignator () . '-' . $key ."').DatePickerSetDate($('#" . $this->getDesignator () . '-' . $key ."').val(), true);
							}
						}," . $render . $locale . "
						onChange: function(formated, dates) {
							if(!isNaN(dates.valueOf())) {
								$('#" . $this->getDesignator () . '-' . $key ."').val(formated);
								$('#" . $this->getDesignator () . '-' . $key ."').DatePickerHide();
							}
						}
					});
				</script>";
	}
	
	/**
	 * prints an date with time form element
	 *
	 * @param	string	$key	the key of the form element
	 * @param	string	$label	the label for the element
	 * @param	array	$attributes	the optional attributes for the element	
	 * @return	string	html code for the datime form element
	 */
	function dateRow($key, $label, $attributes = array()) {
		$setup = $this->setup;
		$date = $setup [$key] ['value'];
		if(isset($setup[$key]['config.']['range'])){
			$render = 'onRender: function(date) {
							return {
								disabled: (date.valueOf() < ' . $setup[$key]['config.']['range']['lower']. '000  || date.valueOf() > ' . $setup[$key]['config.']['range']['upper'] .'000)
							}
						},';// . ' || date.valueOf() > ' . $setup[$key]['config.']['range']['upper'] .
		}
		if($GLOBALS['TSFE']->config['config']['language'] == 'de') { 
			$locale = "locale: {
							days: ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag'],
							daysShort: ['Son', 'Mon', 'Die', 'Mit', 'Don', 'Fre', 'Sam', 'Son'],
							daysMin: ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So'],
							months: ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'],
							monthsShort: ['Jan', 'Feb', 'Mrz', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'],
							weekMin: 'KW'
						},"; 
		} // TODO: andere Sprachen, wenn nötig
		//t3lib_div::debug($setup[$key]['config.'],$key);
		$out .= '<input type="input" id="' . $this->getDesignator () . '-' . $key . '" name="' . $this->getDesignator () . '[' . $key . ']" value="' . $date . '" size="10" maxlength="10" />' . "\n\t";	
		return '<dt><label for="' . $key . '">' . $label . '</label></dt>' . "\n\t" . '
				<dd>' . $out . '</dd>' . "\n".
				"<script type=\"text/javascript\">
					$('#" . $this->getDesignator () . '-' . $key ."').DatePicker({
						eventName: 'focus',
						format:'" . $setup[$key]['config.']['output'] . "',
						date: $('#" . $this->getDesignator () . '-' . $key ."').val(),
						current: $('#" . $this->getDesignator () . '-' . $key ."').val(),
						starts: 1,
						position: 'right',
						onBeforeShow: function(){
							if ( $('#" . $this->getDesignator () . '-' . $key ."').val().length > 0 ) {
								$('#" . $this->getDesignator () . '-' . $key ."').DatePickerSetDate($('#" . $this->getDesignator () . '-' . $key ."').val(), true);
							}
						}," . $render . $locale . "
						onChange: function(formated, dates) {
							if(!isNaN(dates.valueOf())) {
								$('#" . $this->getDesignator () . '-' . $key ."').val(formated);
								$('#" . $this->getDesignator () . '-' . $key ."').DatePickerHide();
							}
						}
					});
				</script>";
	}
	
	/**
	 * prints an mutiple checkbox form element
	 *
	 * @param	string	$key	the key of the form element
	 * @param	string	$label	the label for the element
	 * @param	array	$attributes	the optional attributes for the element	
	 * @return	string	html code for the multiple checkbox form element
	 */
	function multicheckbox($key, $label, $attributes = array()) {
		$setup = $this->setup;
		if (! isset ( $attributes ['name'] )) {
			$this->_die ( 'Please set a name attribute for multicheckbox controlls.', __FILE__, __LINE__ );
		}
		if (! is_array ( $attributes ['options.'] )) {
			$this->_die ( 'Please set a optins attribute for radio controlls.', __FILE__, __LINE__ );
		} else {
			foreach ( $attributes ['options.'] as $k => $val ) {
				if (is_array ( $setup [$key] ['value'] ) && in_array ( $k, $setup [$key] ['value'] )) {
					$checked = ' checked="checked"';
				} else {
					$checked = '';
				}
				$out .= '<input type="checkbox" class="crud-checkbox" name="' . $attributes ['name'] . '" value="' . $k . '" ' . $checked . ' />' . $val . "\n";
			}
			return '<dt><label for="' . $key . '">' . $label . '</label></dt>' . "\n\t" . '
					<dd>' . $out . "</dd>\n";
		}
	}
	
	/**
	 * prints an radio form element
	 *
	 * @param	string	$key	the key of the form element
	 * @param	string	$label	the label for the element
	 * @param	array	$attributes	the optional attributes for the element	
	 * @return	string	html code for the radio form element
	 */
	function radio($key, $label, $attributes = array()) {
		$setup = $this->setup;
		if (! isset ( $attributes ['name'] )) {
			$this->_die ( 'Please set a name attribute for radio controlls.', __FILE__, __LINE__ );
		}
		if (! is_array ( $attributes ['options.'] )) {
			$this->_die ( 'Please set a optins attribute for radio controlls.', __FILE__, __LINE__ );
		} else {
			$radio ['type'] = 'radio';
			foreach ( $attributes ['options.'] as $k => $val ) {
				if ($setup [$key] ['value'] == $k) {
					$checked = ' checked="checked"';
				} else {
					$checked = '';
				}
				$out .= '<input type="radio" class="crud-radio" name="' . $attributes ['name'] . '" value="' . $k . '" ' . $checked . ' />' . $val . "\n";
			}
		}
		return '<dt><label for="' . $key . '">' . $label . '</label></dt>' . "\n\t" . '
				<dd>' . $out . "</dd>\n";
	}
	
	/**
	 * prints an select form element
	 *
	 * @param	string	$key	the key of the form element
	 * @param	string	$label	the label for the element
	 * @param	array	$attributes	the optional attributes for the element	
	 * @param 	array	$options	the values for the select
	 * @return	string	html code for the select form element
	 */
	function select($key, $attributes = array(), $options = NULL) {
		$setup = $this->setup;
		if ($setup [$key]['sorting.']) {
			$sorting = $setup[$key]['sorting.'];
		}
		$attributes = $this->_addId ( $key, $attributes );
		$attributes = $this->_addName ( $key, $attributes, ( bool ) $attributes ['multiple'] );
		unset ( $attributes['attributes.']['sorting.'] );
		$attributes = $this->_makeAttributes ( $attributes );
		$options = $options ? $options : $this->getOptionList ( $key );
		if (! $sorting) {
			if (is_array ( $options )) {
				foreach ( $options as $value => $text ) {
					$value = strlen( $value ) ? $value : $text;
					$selected = $this->selected ( $key, $value );
					$value = sprintf( ' value="%s"', $value );
					$body .= "\r\t\t" . '<option' . $value . ' ' . $selected . '>' . $text . '</option>';
				}
			}
		} else {
			$tables = explode ( ',', $setup[$key]['config.']['allowed'] );
			foreach ( $sorting as $table => $entry ) {
				if ($setup[$key]['config.']['MM']) {
					$split = '__';
				} elseif (sizeof ( $tables ) == 1) {
					$split = '';
					$table = '';
				} else {
					$split = '_';
				}
				$body .= "\r\t" . '<optgroup class="crud-optgroup" id="crud-optgroup-' . $table . '" label="' . strtoupper ( $table ) . '">';
				foreach ( $entry as $uid => $val ) {
					$text = $setup [$key] ['options.'] [$table . $split . $uid];
					if (! empty ( $setup [$key] ['value'] [$table . $split . $uid] )) {
						$selected = ' selected="selected"';
					} else {
						$selected = '';
					}
					if ($setup [$key] ['options.'] [$table . $split . $uid]) {
						$body .= "\r\t\t" . '<option class="crud-select-level1"  ' . $selected . ' value="' . $table . $split . $uid . '">' . $text . '</option>';
					}
					if (is_array ( $val )) {
						foreach ( $val as $uid2 => $val2 ) {
							if (! empty ( $setup [$key] ['value'] [$table . $split . $uid2] )) {
								$selected = ' selected="selected"';
							} else {
								$selected = "";
							}
							$text = $setup [$key] ['options.'] [$table . $split . $uid2];
							if ($setup [$key] ['options.'] [$table . $split . $uid2]) {
								$body .= '<option class="crud-select-level2" ' . $selected . ' value="' . $table . $split . $uid2 . '">&nbsp;&nbsp;&nbsp;' . $text . '</option>' . "\n\t";
							}
							if (is_array ( $val2 )) {
								foreach ( $val2 as $uid3 => $val3 ) {
									if (! empty ( $setup [$key] ['value'] [$table . $split . $uid3] )) {
										$selected = ' selected="selected"';
									} else {
										$selected = "";
									}
									$text = $setup [$key] ['options.'] [$table . $split . $uid3];
									if ($setup [$key] ['options.'] [$table . $split . $uid3]) {
										$body .= '<option class="crud-select-level3" ' . $selected . ' value="' . $table . $split . $uid3 . '">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $text . '</option>' . "\n\t";
									}
									if (is_array ( $val3 )) {
										foreach ( $val3 as $uid4 => $val4 ) {
											if (! empty ( $setup [$key] ['value'] [$table . "__" . $uid4] )) {
												$selected = ' selected="selected"';
											} else {
												$selected = "";
											}
											$text = $setup [$key] ['options.'] [$table . "__" . $uid4];
											if ($setup [$key] ['options.'] [$table . "__" . $uid4]) {
												$body .= '<option class="crud-select-level4" ' . $selected . ' value="' . $table . $split . $uid4 . '">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $text . '</option>' . "\n\t";
											}
											if (is_array ( $val4 )) {
												foreach ( $val4 as $uid5 => $val5 ) {
													if (! empty ( $setup [$key] ['value'] [$table . $split . $uid5] )) {
														$selected = ' selected="selected"';
													} else {
														$selected = "";
													}
													$text = $setup [$key] ['options.'] [$table . "__" . $uid5];
													if ($setup [$key] ['options.'] [$table . "__" . $uid5]) {
														$body .= '<option class="crud-select-level5" ' . $selected . ' value="' . $table . $split . $uid5 . '">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $text . '</option>' . "\n";
													}
													if (is_array ( $val5 )) {
														foreach ( $val5 as $uid6 => $val6 ) {
															if (! empty ( $setup [$key] ['value'] [$table . $split . $uid6] )) {
																$selected = ' selected="selected"';
															} else {
																$selected = "";
															}
															$text = $setup [$key] ['options.'] [$table . "__" . $uid6];
															if ($setup [$key] ['options.'] [$table . $split . $uid6]) {
																$body .= '<option class="crud-select-level6" ' . $selected . ' value="' . $table . $split . $uid6 . '">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $text . '</option>' . "\n\t";
															}
														}
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
				$body .= '</optgroup>' . "\n";
			}
		}
		//t3lib_div::debug($setup[$key]);
		if ($setup [$key] ['reload']) {
			$reload = ' onchange="javascript:ajax4onClick(this);"';
		}
		return "\r\t" . '<select' . $attributes . $reload . $multiselect . '>' . $body . "\r\t" . '</select>' . "\r";
	}
	
	/**
	 * prints an captcha form element
	 *
	 * @param	string	$key	the name of the form element
	 * @param	array	$entry	the setup for the captcha entry
	 * @return	string	 
	 */
	function captchaRow($key, $entry) {
		if (t3lib_extMgm::isLoaded ( 'captcha' )) {
			$captchaHTMLoutput = '<img src="' . t3lib_extMgm::siteRelPath ( 'captcha' ) . 'captcha/captcha.php" alt="captcha" /><input type="text" size="5" name="' . $this->getDesignator () . '[' . $key . ']"  value="" />' . "\n";
		} else {
			$captchaHTMLoutput = '%%%error_captcha-HTML-output%%%';
		}
		return $captchaHTMLoutput;
	}
	
	/**
	 * prints a category select form
	 *
	 * @param	string	$key	the key of the form element
	 * @param	string	$label	the label for the element
	 * @param	array	$attributes	the optional attributes for the element	
	 * @param 	array	$options	the values for the select
	 * @return	string	html code for the select form element
	 */
	function categoryRow($key, $label, $attributes = array(), $options = NULL){
		$setup = $this->setup[$key];
		//echo $key;
		//if($key=="category") t3lib_div::Debug($setup);
		if(isset($setup['config.']['MM'])) $process = $setup['processMM'];
		else  $process = $setup['process'];
		$rows['options'] = $setup['options.'];
		$rows['sorting'] = $setup['sorting.'];
		//t3lib_div::Debug($setup);
		foreach ($rows['sorting'] as $key=>$value) {
			if (is_array($value['sub'])) {
				$subBody = "\r\t" . '<optgroup label="' . $value['title'] . '">';
				foreach ($value['sub'] as $subKey=>$subValue) {
					if (is_array($subValue['sub'])) {
						$subSubBody = "\r\t" . '<optgroup label="' . $subValue['title'] . '">';
						foreach ($subValue['sub'] as $subSubKey=>$subSubValue) {
							if (isset($process[$subSubKey])) {
								$selected = ' selected="selected"';
							} else {
								$selected = '';
							}
							$subSubBody .= "\r\t\t" . '<option' . $selected . ' value="' . $subSubKey . '">' . $subSubValue['title'] . '</option>';
						}
						$subSubBody .= '</optgroup>';
						$subBody .= $subSubBody;
					} else {
						if (isset($process[$subKey])) {
							$selected = ' selected="selected"';
						} else {
							$selected = '';
						}
						$subBody .= "\r\t\t" . '<option' . $selected . ' value="' . $subKey . '">' . $subValue['title'] . '</option>';
					}
				}
				$subBody .= "\r\t" . '</optgroup>';
				$body .= $subBody;
			} else {
				if (isset($process[$key])) {
					$selected = ' selected="selected"';
				} else {
					$selected = '';
				}
				$body .= "\r\t\t" . '<option' . $selected . ' value="' . $key . '">' . $value['title'] . '</option>';
			}
		}
		$out .= "\r" . '<label="' . $key . '">';
		if ($setup['config.']['maxitems'] > 1) {
			$multiple = ' multiple="multiple"';
		}
	//	return $out.'<dd><select  size="8" multiple="multiple" id="'.$setup['key'].'" name="'.$this->getDesignator().'['.$setup['key'].'][]">'.$body.'</select><br /><b>Auswahl:</b><div class="'.$setup['key'].'"></div></dd>';
			// FEIXME: wenn man hier das <dd> entfernt, kommt nichts mehr im FE an ????, aber das <dd> wird eh nicht im HTML ausgegeben?
		return $out . '<dd><select size="' . $setup['config.']['size'] . '"' . $multiple . 'id="' . $setup['key'] . '" name="' . $this->getDesignator() . '[' . $setup['key'] . '][]">' . $body . '</select>' . "\r";
	}

	// -------------------------------------------------------------------------------------
	// SOME FORM HELPERS
	// -------------------------------------------------------------------------------------
	
	/**
	 * renders images from a setup automatic
	 *
	 * @param	string	$item_key	the key in the setup
	 * @param	integer	$height		the height of the image 
	 * @param	integer	$width		the hwidth of the image 
	 * @param	integer	$maxImages	how much images should rendered maximal
	 * @param	boolean	$lightbox	should a lightbox be active
	 * @param	string	$wrapAll	wrap some html about all images
	 * @param	string	$wrapImage	wrap some html about every single image
	 * @return	string	the image
	 */
	function makeImage($item_key, $height = 30, $width = 30, $maxImages = 100, $lightbox = 1, $wrapAll = "", $wrapImage = "") {
		$setup = $this->setup;
		$pars = $this->controller->parameters->getArrayCopy ();
		$img = $item_key;
		$wrapImage = explode ( "|", $wrapImage );
		if (is_array ( $img )) {
			$i = 0;
			foreach ( $img as $key => $val ) {
				$url = $val;
				if (file_exists ( $url ) && $i < $maxImages) {
					$size = @getimagesize ( $url );
					if ($size [1] > $height) {
						$size [1] = $height;
					}
					if ($size [0] > $width) {
						$size [0] = $width;
					}
					require_once (PATH_site . 't3lib/class.t3lib_stdgraphic.php');
					require_once (PATH_site . 'typo3/sysext/cms/tslib/class.tslib_gifbuilder.php');
					$imageClassName = tx_div::makeInstanceClassName ( 'tx_lib_image' );
					$image = new $imageClassName ( );
					$image->alt ( $setup [$key] ['label'] ); //TODO img label
					$image->maxWidth ( $size [0] );
					$image->maxHeight ( $size [1] );
					$image->path ( $url );
					if ($lightbox) {
						$images .= '<a href="' . $url . '" rel="lightbox[lb26]">' . $wrapImage [0] . $image->make () . $wrapImage [1] . '</a>'; //TODO: [lb26]
					} else {
						$images .= $wrapImage [0] . $image->make () . $wrapImage [1];
					}
					unset ( $image );
				} else {
					echo "%%%error_no-image%%%";
				}
				$i ++;
			}
			$wrapAll = explode ( "|", $wrapAll );
			if ($images) {
				return $wrapAll [0] . $images . $wrapAll [1];
			}
		}
	}
	
	/**
	 * check if an checkbox or radio is checked
	 *
	 * @param	string	$name	the name of the form element
	 * @param	string	$comparismValue	the value for the element
	 * @return	boolean
	 */
	function _checked($name, $comparismValue) {
		$setup = $this->setup;
		$val = $setup [$name];
		if (! empty ( $setup [$name] ['value'] )) {
			if ($val ['element'] == "checkbox" || $val ['element'] == "checkboxRow" && ! empty ( $val ['value'] )) {
				return TRUE;
			} elseif (! is_array ( $val ['value'] ) && $val ['value'] == $comparismValue) {
				return TRUE;
			} elseif (is_array ( $val ['value'] ) && in_array ( $comparismValue, $val ['value'] )) {
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}

	/**
	 * check if an select is selected
	 *
	 * @param	string	$key	the name of the form element
	 * @return	string	ther selected value
	 */
	function _getValue($key) {
		return $this->setup [$key] ['value'];
	}
}
?>