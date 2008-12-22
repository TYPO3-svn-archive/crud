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
 * Depends on: crud
 *
 * @author Frank Thelemann <f.thelemann@yellowmed.com>
 * @package TYPO3
 * @subpackage tx_crud
 */

final class  tx_crud__log {

	public static function read($action, $nodes, $nameSpace, $setup,$pageOnly = false) {
		//creation date
		$where = '';
		if ($pageOnly) {
			$where = 'crud_page=' . $GLOBALS['TSFE']->id . ' AND ';
		}
		// creation data
		$queryCreate = $GLOBALS['TYPO3_DB']->sql_query('SELECT crud_username AS
			user, cruser_id AS id, tstamp FROM tx_crud_log WHERE
			crud_table="' . $nameSpace . '" AND crud_action="create" AND
			crud_record=' . $nodes);
		$numCreates = $GLOBALS['TYPO3_DB']->sql_affected_rows($queryCreate);
		if ($numCreates > 0) {
			$result['create'] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($queryCreate);
		}
		// update and retrieve counts
		$queryCounts = $GLOBALS['TYPO3_DB']->sql_query('SELECT crud_action,
			COUNT(*) AS anzahl FROM tx_crud_log GROUP BY crud_action,
			crud_table, crud_record HAVING (crud_action="update" OR
			crud_action="retrieve") AND ' . $where . 'crud_table="' .
			$nameSpace . '" AND crud_record=' . $nodes);

		if ($queryCounts) {
			//echo 'update and retrieve data';
			$numCounts = $GLOBALS['TYPO3_DB']->sql_affected_rows($queryCounts);

			for ($i = 0; $i < $numRows; $i++) {
				$temp = $GLOBALS['TYPO3_DB']->sql_fetch_row($queryCounts);
//				/echo $temp[0].$temp[1];
				$result[$temp[0]]['count'] = $temp[1];
				if ($temp[1] > 0) {
					$query[$i] = $GLOBALS['TYPO3_DB']->sql_query('
						SELECT crud_username AS user, cruser_id AS id, tstamp,
						crud_page FROM tx_crud_log WHERE
						crud_table="' . $nameSpace . '" AND ' . $where .
						'crud_action="' . $temp[0] . '" AND
						crud_record=' . $nodes . ' ORDER BY tstamp DESC LIMIT '
						. $setup['read.']['max']);
					$numData = $GLOBALS['TYPO3_DB']->sql_affected_rows($query[$i]);
					for ($j = 0; $j < $numData; $j++) {
						$result[$temp[0]][$j] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($query[$i]);
					}
				}
			}
		}
		return $result;
	}

	public static function write($action, $nodes, $nameSpace, $setup) {
		if ($action == 'retrieve') {
			// allmost retrieved in this session?
			$query = $GLOBALS['TYPO3_DB']->sql_query('SELECT * FROM tx_crud_log
				WHERE crud_table="' . $nameSpace . '" AND crud_action="retrieve"
				AND crud_session="' . $GLOBALS['TSFE']->fe_user->id . '"
				AND crud_record=' . $nodes);
			$numRows = $GLOBALS['TYPO3_DB']->sql_affected_rows($query);
			if ($numRows > 0) {
				return;
			}
		}
		$legalActions = explode(',', $setup['write.']['actions']);
		if (in_array($action, $legalActions)) {
			$insertLog = array();
			$insertLog['pid'] = $setup['write.']['pid'];
			$insertLog['tstamp'] = time();
			$insertLog['crdate'] = time();
			$insertLog['cruser_id'] = $GLOBALS['TSFE']->fe_user->user['uid'];
			$insertLog['title'] = date('d.m.Y-H:i:s') . '#' . $action . '#' . $nodes . '#' . $GLOBALS['TSFE']->id;
			$insertLog['crud_action'] = $action;
			$insertLog['crud_table'] = $nameSpace;
			$insertLog['crud_record'] = $nodes;
			$insertLog['crud_page'] = $GLOBALS['TSFE']->id;
			$insertLog['crud_user'] = $GLOBALS['TSFE']->fe_user->user['uid'];
			$insertLog['crud_session'] = $GLOBALS['TSFE']->fe_user->id;
			$insertLog['crud_username'] = $GLOBALS['TSFE']->fe_user->username;
			$insert = $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_crud_log',$insertLog);
		//	print_r($insertLog);
			if (!$insert) {
				echo '%%%error_crud-log-query%%%';
			}
		}
	}
}
?>