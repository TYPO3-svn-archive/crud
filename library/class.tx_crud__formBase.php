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
class tx_crud__formBase extends tx_lib_formBase {

	var $setup;
	var $_rowPattern = '%1$s<dt>%2$s</dt>
					%4$s<dd>%3$s</dd>';

	function makeImage($item_key,$height=30,$width=30,$maxImages=100,$lightbox=1,$wrapAll="",$wrapImage="") {
		$setup = $this->setup;
		$pars = $this->controller->parameters->getArrayCopy();
		$img = $item_key;
		$wrapImage = explode("|",$wrapImage);
		//t3lib_div::debug($img);
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
	
	function begin($key, $attributes = array()) {
		$this->setIdPrefix($this->getDesignator());
		$attributes['id'] = $key;
		$attributes['action'] = $this->action();
		$attributes['method'] = $this->method();
		$attributes['name'] = $this->prefixId."form";
		$attrutes['enctype'] = "multipart/form-data";
		//$attributes = $this->_makeAttributes($attributes);
		$url = $this->action();
		$url = str_replace("no_cache=1","",$url);
		echo "\r\n" . '<form method="post" action="' . $url . '" enctype="multipart/form-data">' . "\r\n";
	}

	function fileRow($key, $label, $attributes=array()) {
		$setup = $this->setup;
		if (!isset($attributes['name'])) {
			$this->_die('Please set a name attribute for FileRow controls.', __FILE__, __LINE__); 
		}
		$out = '<input type="file" title="' . $attributes['title'] . '" name="' . $attributes['name'] . '[]" maxlength="' . $attributes['maxlength'] . '" />' . "\n";
		return '<dt><label for="' . $key .'">' . $label . "</label></dt>\n\t
			<dd>" . $out . "</dd>\n";
	}
	
	function noFileRow($key, $label, $attributes=array()) {
		$setup = $this->setup;
		//debug($setup);
		if (!isset($setup[$key]['value'][0])) {
			$this->_die('Please set a value attribute for noFileRow controls.', __FILE__, __LINE__);
		}
		$out = '<input type="hidden" name="' . $attributes['name'] . '[0]" value="' . $setup[$key]['value'][0] . '" />' . "\n";
		$out .= '<input type="image" src="typo3conf/ext/crud/resources/icons/delete.gif" name="' . $this->getDesignator() . '[remove][' . $key . ']" value="0" class="icon" />' . "\n";
		//if($setup[$key]['config.']['show_thumbs']) $file=$this->makeImage($setup[$key]['value'][0]); 
		//else $file=$setup[$key]['value'][0];
		$file = $this->makeFilePreview($setup[$key]['value'][0]); 
		return '<dt><label for="' . $key . '">' . $label . "</label></dt>\n\t
				<dd>" . $file . $setup[$key]['value'][0] . $out . "</dd>\n";
	}
	
	function multiFileRow($key, $label, $attributes=array()) {
		$setup = $this->setup;
		if (!isset($attributes['name'])) {
			$this->_die('Please set a name attribute for multiFileRow controls.', __FILE__, __LINE__);
		}
		$y = 0;
		for ($i = 0; $i < $setup[$key]['config.']['maxitems']; $i++) {
			//echo $setup[$key]['value'][$i];
			if (strlen($setup[$key]['value'][$i]) <= 0) {
				if ($y < $setup[$key]['config.']['size']) {
					$inputs .= '<li><input type="file" title="' . $attributes['title'] . '" name="' . $attributes['name'] . '[' . $i . ']" maxlength="' . $attributes['maxlength'] . '" /></li>' . "\n";
					$y++;
				}
			} else {
				$files .= "<li>" . $this->makeFilePreview($setup[$key]['value'][$i]).$setup[$key]['process'][$i];
				$files .= "\n" . '<input type="hidden" name="' . $attributes['name'] . '[' . $i . ']" value="' . $setup[$key]['value'][$i] . '" />';
				$files .= "\n" . '<input type="image" src="typo3conf/ext/crud/resources/icons/delete.gif" name="' . $this->getDesignator() . '[remove][' . $key . ']" value="' . $i . '" class="icon "/></li>' . "\n";
			}
		}

		$out = '<ul class="' . $this->getDesignator() . '[multiUpload]">' . $files . $inputs . "</ul>\n";
		return $out;
	}
	
	function makeFilePreview($url) {
		require_once(PATH_site.'t3lib/class.t3lib_stdgraphic.php');
		require_once(PATH_site.'typo3/sysext/cms/tslib/class.tslib_gifbuilder.php');
		$fileExtension_exploded = explode(".",$url);
		$fileExtension = strtolower($fileExtension_exploded[count($fileExtension_exploded)-1]);
		$images = array("jpg","jpeg","png","gif","bmp");
		//$images =
		if (in_array($fileExtension,$images)) {
			$setup = $this->setup;
			$size = getimagesize($url);
			if ($size[1] > 30) {
				$size[1] = 30;
			}
			if ($size[0] > 30) {
				$size[0] = 30;
			}
			$imageClassName = tx_div::makeInstanceClassName('tx_lib_image');
			$image = new $imageClassName();
			$image->alt($setup[$key]['label']); //TODO: img label
			$image->maxWidth($size[0]);
			$image->maxHeight($size[1]);
			$image->path($url);
			return $image->make();
		} else {
			if (file_exists('typo3conf/ext/crud/resources/icons/files/icon_'.strtolower($fileExtension).'.gif')) {
				$img = '<img src="typo3conf/ext/crud/resources/icons/files/icon_' . strtolower($fileExtension) . '.gif" alt="Filetype: ' . $fileExtension . ' " alt="" border="0" />';
				return $img;
			}
		}
	}
	
	function rteRow($key, $label, $attributes = array()) {
		$conf = $this->controller->configurations->getArrayCopy();
		$setup = $conf['view']['setup.'];
		$rte = $conf['view.']['tinymce.'];
		unset($rte['enable']);
		foreach ($rte as $name=>$val) {
			if (!empty($val['fields'])) {
				$fields = explode(",",$val['fields']);
				foreach ($fields as $key2=>$val2) {
					if ($val2 == $key) {
						$class = "tinymce_" . str_replace(".","",$name);
						if ($val['cols'] > 1) {
							$attributes['cols'] = $val['cols'];
						}
						if ($val['rows'] > 1) {
							$attributes['rows'] = $val['rows'];
						}
					}
				}
			}
		}
		if (!$attributes['cols']) {
			$attributes['cols'] = 30;
		}
		if (!$attributes['rows']) {
			$attributes['rows'] = 5;
		}
		if (!$class) {
			$class = "tinymce_default";
		}
		$out = '<textarea id="' . $this->getDesignator() . "-" . $key . '" cols="' . $attributes['cols'] . '" rows="' . $attributes['rows'] . '" name="' . $this->getDesignator() . '[' . $key . ']' . '" class="' . $class . '">' . $this->_getValue($key) . '</textarea>' . "\n";
		return '<dt><label for="' . $this->getDesignator() . "-" . $key . '">' . $label . "</label></dt>\n\t
				<dd>" . $out . "</dd>\n";
	}
    
	function dateTimeRow($key,$label,$attributes=array()) {
		$setup = $this->setup;
//	/	t3lib_div::debug($setup[$key]);
		$date = $setup[$key]['value']['date'];
		$time = $setup[$key]['value']['time'];
		//if (!isset($attributes['name'])) {
		//	$this->_die('Please set a name attribute for fileRow controlls.', __FILE__, __LINE__);
		//}
		$out = '<ul class="crud-datetime" class="clearfix">' . "\n\t";
		$out .= '<li>%%%date%%% <input type="input" name="' . $this->getDesignator() . '[' . $key . '][date]" value="' . $date . '" size="10" maxlength="10" /></li>' . "\n\t";
		$out .= '<li>%%%time%%% <input type="input" name="' . $this->getDesignator() . '[' . $key . '][time]" value="' . $time . '" size="5" maxlength="5" /></li>' . "\n" . '</ul>' . "\n";
		return '<dt><label for="' . $key . '">' . $label . '</label></dt>' . "\n\t" . '
				<dd>' . $out . '</dd>' . "\n";
	}
	
	function multicheckbox($key,$label, $attributes = array()) { 
		$setup = $this->setup;
		if (!isset($attributes['name'])) {
			$this->_die('Please set a name attribute for multicheckbox controlls.', __FILE__, __LINE__);
		}
		if (!is_array($attributes['options.'])) {
			$this->_die('Please set a optins attribute for radio controlls.', __FILE__, __LINE__);
		} else {
			foreach ($attributes['options.'] as $k=>$val) {
				if (is_array($setup[$key]['value']) && in_array($k,$setup[$key]['value'])) {
					$checked = ' checked="checked"';
				} else {
					$checked = '';
				}
				$out .= '<input type="checkbox" class="crud-checkbox" name="' . $attributes['name'] . '" value="' . $k .'" ' . $checked . ' />' . $val . "\n";
			}
			return '<dt><label for="' . $key . '">' . $label . '</label></dt>' . "\n\t" . '
					<dd>' . $out . "</dd>\n";
		}
	}
	
	function radio($key,$label, $attributes = array()) { 
		$setup = $this->setup;
		if (!isset($attributes['name'])) {
			$this->_die('Please set a name attribute for radio controlls.', __FILE__, __LINE__);
		}
		if (!is_array($attributes['options.'])) {
			$this->_die('Please set a optins attribute for radio controlls.', __FILE__, __LINE__);
		} else {
			$radio['type'] = 'radio';
			foreach ($attributes['options.'] as $k=>$val) {
				if ($setup[$key]['value'] == $k) {
					$checked = ' checked="checked"';
				} else {
					$checked = '';
				}
				$out .= '<input type="radio" class="crud-radio" name="' . $attributes['name'] . '" value="' . $k . '" ' . $checked . ' />' . $val . "\n";
			}
		}
		return '<dt><label for="' . $key . '">' . $label . '</label></dt>' . "\n\t" . '
				<dd>' . $out . "</dd>\n";
	}
	
	function select($key, $attributes = array(), $options = NULL) {
		$setup = $this->setup;
		//t3lib_div::debug($setup);
		if ($setup[$key]["sorting."]) {
			$sorting = $setup[$key]["sorting."];
		}
		//t3lib_div::debug($options);
		$attributes = $this->_addId($key, $attributes);
		$attributes = $this->_addName($key, $attributes, (bool) $attributes['multiple']);
		unset($attributes["attributes."]["sorting."]);
		$attributes = $this->_makeAttributes($attributes);
		$options = $options ? $options : $this->getOptionList($key);
		if (!$sorting) {
			
			foreach ($options as $value => $text ) {
				$value = strlen($value) ? $value : $text;
				$selected = $this->selected($key, $value);
				//$multiselect = ' class="select-multi"';
				$value = sprintf(' value="%s"', $value);
				$body .= '<option' . $value . " " . $selected . '>' . $text . '</option>' . "\n\t";
			}
		} else {
			//t3lib_div::debug($sorting);
			$tables=explode(",",$setup[$key]['config.']['allowed']);
			//$multiselect = ' class="select-multi"';
		foreach($sorting as $table=> $entry ) {
			if($setup[$key]['config.']['MM']) $split="__";
			elseif(sizeof($tables)==1) {
				$split="";
				$table="";
			}
			else $split="_";
			$body .= '<optgroup class="crud-optgroup" id="crud-optgroup-' . $table . '" label="' . strtoupper($table) . '">' . "\n\t";
			foreach ($entry as $uid=>$val) {
					$text = $setup[$key]['options.'][$table . $split . $uid];;
					if (!empty($setup[$key]['value'][$table . $split . $uid])) {
						$selected = ' selected="selected"';
					} else {
						$selected = "";
					}
					if ($setup[$key]['options.'][$table . $split . $uid]) {
						$body .= '<option class="crud-select-level1"  ' . $selected . ' value="' . $table . $split . $uid . '">' . $text . '</option>' . "\n\t";
					}
					if (is_array($val)) {
						foreach ($val as $uid2=>$val2) {
							if (!empty($setup[$key]['value'][$table . $split . $uid2])) {
								$selected = ' selected="selected"';
							} else {
								$selected = "";
							}
							$text = $setup[$key]['options.'][$table . $split . $uid2];
							if ($setup[$key]['options.'][$table . $split . $uid2]) {
								$body .= '<option class="crud-select-level2" ' . $selected . ' value="' . $table . $split . $uid2 . '">&nbsp;&nbsp;&nbsp;' . $text . '</option>' . "\n\t";
							}
							if (is_array($val2)) {
								foreach ($val2 as $uid3=>$val3) {
									if (!empty($setup[$key]['value'][$table . $split . $uid3])) {
										$selected = ' selected="selected"';
									} else {
										$selected = "";
									}
									$text = $setup[$key]['options.'][$table . $split . $uid3];
									if ($setup[$key]['options.'][$table . $split . $uid3]) {
										$body .= '<option class="crud-select-level3" ' . $selected . ' value="' . $table . $split . $uid3 . '">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $text . '</option>' . "\n\t";
									}
									if (is_array($val3)) {
										foreach ($val3 as $uid4=>$val4) {
											if (!empty($setup[$key]['value'][$table . "__" . $uid4])) {
												$selected = ' selected="selected"';
											} else {
												$selected = "";
											}
											$text = $setup[$key]['options.'][$table . "__" . $uid4];
											if ($setup[$key]['options.'][$table."__".$uid4]) {
												$body .= '<option class="crud-select-level4" ' . $selected . ' value="' . $table . $split . $uid4 . '">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $text . '</option>' . "\n\t";
											}
											if (is_array($val4)) {
												foreach ($val4 as $uid5=>$val5) {
													if (!empty($setup[$key]['value'][$table . $split . $uid5])) {
														$selected=' selected="selected"';
													} else {
														$selected = "";
													}
													$text = $setup[$key]['options.'][$table . "__" . $uid5];
													if ($setup[$key]['options.'][$table . "__" . $uid5]) {
														$body .= '<option class="crud-select-level5" ' . $selected . ' value="' . $table . $split . $uid5 . '">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $text . '</option>' . "\n";
													}
													if (is_array($val5)) {
														foreach ($val5 as $uid6=>$val6) {
															if (!empty($setup[$key]['value'][$table . $split . $uid6])) {
																$selected = ' selected="selected"';
															} else {
																$selected = "";
															}
															$text = $setup[$key]['options.'][$table . "__" . $uid6];
															if ($setup[$key]['options.'][$table . $split . $uid6]) {
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
					$body .= "</optgroup>\n";
				}
			}
		}
		if ($setup[$key]['reload']) {
			$reload = ' onchange="javascript:this.form.submit();"';
		}
		//$hidden='<input type="hidden" name="'.$this->getDesingator().'[noProcess]'" value="
		return '<select' . $attributes . $reload . $multiselect . '>' . "\n\t" . $body . '</select>' . "\n";
	}
	
	function _checked($name, $comparismValue) {
		$setup = $this->setup;
		$val = $setup[$name];
		if (!empty($setup[$name]['value'])) {
			if ($val['element']=="checkbox" || $val['element']=="checkboxRow" && !empty($val['value'])) {
				return TRUE;
			} elseif (!is_array($val['value']) && $val['value']==$comparismValue) {
				return TRUE;
			} elseif (is_array($val['value']) && in_array($comparismValue,$val['value'])) {
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}

	function captchaRow($key,$entry) {
		if (t3lib_extMgm::isLoaded('captcha')) {
			$captchaHTMLoutput = '<img src="'.t3lib_extMgm::siteRelPath('captcha').'captcha/captcha.php" alt="captcha" /><input type="text" size="5" name="'.$this->getDesignator().'['.$key.']"  value="" />' . "\n";
		} else {
			$captchaHTMLoutput = '%%%error_captcha-HTML-output%%%';
		}
		return $captchaHTMLoutput;
	}
	
	function _getValue($key) {
		return $this->setup[$key]['value'];
	}
}

?>