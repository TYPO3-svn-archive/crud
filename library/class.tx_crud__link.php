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

class tx_crud__html_link extends tx_lib_link {
// -------------------------------------------------------------------------------------
	// Private functions
	// -------------------------------------------------------------------------------------

	/**
	 * Make the full configuration for the typolink function
	 *
	 * @param	string		$type: tag oder url
	 * @return	array		the configuration
	 * @access	private
	 */
	function _makeConfig($type) {
		$conf = Array();
		$this->parameters = is_array($this->parameters) ? $this->parameters : array();
		$this->overruledParameters = is_array($this->overruledParameters) ? $this->overruledParameters : array();
			
		unset($this->overruledParameters['DATA']);
		$parameters = t3lib_div::array_merge_recursive_overrule($this->overruledParameters,$this->parameters);
		foreach ((array) $parameters as $key => $value) {
			$first='&' . rawurlencode( $this->designatorString) . '[' . $key . ']';
			if (!is_array($value)) {   // TODO handle arrays
				if ($this->designatorString) {
					$conf['additionalParams'] .= '&' . rawurlencode( $this->designatorString . '[' . $key . ']') . '=' . rawurlencode($value);
				} else {
					$conf['additionalParams'] .= '&' . rawurlencode($key) . '=' . rawurlencode($value);
				}
			} else {
				//debug($value);
				foreach ($value as $key2 => $value2) {
					$conf['additionalParams'] .= $first . "[" . rawurlencode($key2) . "]=" . rawurlencode($value2);
				}
			}
			//debug($conf['additionalParams']);
		}
		if ($this->noHashBoolean ) {
			$conf['useCacheHash'] = 0;
		} else {
			$conf['useCacheHash'] = 1;
		}
		if ($this->noCacheBoolean) {
			$conf['no_cache'] = 1;
			$conf['useCacheHash'] = 0;
		} else {
			$conf['no_cache'] = 0;
		}
		if ($this->destination !== '') {
			$conf['parameter'] = $this->destination;
		}
		if ($type == 'url') {
			$conf['returnLast'] = 'url';
		}
		if ($this->anchorString) {
			$conf['section'] = $this->anchorString;
		}
		if ($this->targetString) {
			$conf['target'] = $this->targetString;
		}
		if ($this->externalTargetString) {
			$conf['extTarget'] = $this->externalTargetString;
		}
		if ($this->classString) {
			$conf['ATagParams'] .= 'class="' . $this->classString . '" ';
		}
		if ($this->idString) {
			$conf['ATagParams'] .= 'id="' . $this->idString . '" ';
		}
		if ($this->titleString) {
			$title = ($this->titleHasAlreadyHtmlSpecialChars) ? $this->titleString
				: htmlspecialchars($this->titleString);
			$conf['ATagParams'] .= 'title="' . $title . '" ';
		}
		if (is_array($this->tagAttributes) && (count($this->tagAttributes) > 0)) {
			foreach ($this->tagAttributes as $key => $value) {
				$conf['ATagParams'] .= ' ' .  $key . '="' . htmlspecialchars($value) . '" ';
			}
		}
		return $conf;
	}
}

?>