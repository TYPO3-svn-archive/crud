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

final class tx_crud__cache  {

	function get($hash) {
		if (t3lib_div::compat_version("4.3")) {
			return $GLOBALS['TSFE']->sys_page->getHash(md5($hash));
		} else {
			$where = 'uuid="'.md5($hash).'"';
			$query = $GLOBALS['TYPO3_DB']->exec_SELECTquery("cached","tx_crud_cached",$where);
			if ($query) {
				$result = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($query);
			}
			if (strlen($result['cached']) > 3) {
				return unserialize($result['cached']);
			} else {
				return false;
			}
		}
	}
	
	function write($hash,$data) {
		if (t3lib_div::compat_version("4.3")) {
			$GLOBALS['TSFE']->sys_page->storeHash( md5($hash), $data, $hash);
		} else {
			$insert['uid'] = "";
			$insert['tstamp'] = time();
			$insert['uuid'] = $hash;
			$insert['cached'] = serialize($data);
			if ($insert['uuid']) {
				$GLOBALS['TYPO3_DB']->exec_INSERTquery("tx_crud_cached",$insert) ;
			}
		}
	}
}

?>