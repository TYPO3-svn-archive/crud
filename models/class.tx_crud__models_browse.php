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
 * Depends on: liv/div
 *
 * @author Frank Thelemann <f.thelemann@yellowmed.com>
 * @package TYPO3
 * @subpackage tx_crud
 */
include (t3lib_extMgm::extPath ( 'crud' ) . 'models/class.tx_crud__models_retrieve.php');
class tx_crud__models_browse extends tx_crud__models_retrieve {
	
	var $start = 0;
	var $limit = 5;
	var $data;
	var $count = 0;
	var $page = 0;
	var $panelAction = "BROWSE";
	
		
	// -------------------------------------------------------------------------------------
	// database browse queries
	// -------------------------------------------------------------------------------------
	
	/**
	 * overwrite of the query call in common
	 * 
	 * @return  void
	 */	
	public public function processQuery() {
		$this->browseQuery ();
	}
		
	/**
	 * makes the browse query
	 * 
	 * @return  void
	 */	
	private function browseQuery() {
		if (! is_array ( $this->processData )) {
			$start = microtime ( true );
			$this->preQuery ();
			$typoscript = $this->controller->configurations->getArrayCopy ();
			$config = $typoscript;
			$this->limit = $config ['view.'] ['limit'];
			$pars = $this->controller->parameters->getArrayCopy ();
			if (! empty ( $pars ['limit'] )) {
				$this->limit = $pars ['limit'];
			}
			if ($this->page >= 1) {
				$this->start = $this->limit * $this->page;
			}
			if (! empty ( $pars ['upper'] )) {
				$sort = " ORDER BY " . $pars ['upper'] . " ASC";
			} elseif (! empty ( $pars ['lower'] )) {
				$sort = " ORDER BY " . $pars ['lower'] . " DESC";
			} else {
				$sort = "";
			}
			$where = $this->getFilterWhere ();
			if (! isset ( $_GET ['q'] ) && ! $pars ['search']) {
				$count = $this->cached ['COUNTER'];
			}
			if (! isset ( $_GET ['q'] ) && $count < 1) {
				$countQuery = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( "count(uid)", $this->getStorageNameSpace (), $where );
				if ($countQuery) {
					$countResult = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $countQuery );
					$count = $countResult ['count(uid)'];
					if (! $pars ['search']) {
						if ($config ['enable.'] ['caching'])
							$this->cache ["COUNTER"] = $count;
					}
				}
			}
			$this->size = $count;
			if (isset ( $_REQUEST ['q'] )) {
				$this->limit = 1000000;
				$this->start = 0;
				$this->size = 1000;
			
			}
			if ($typoscript ['additionalWhere']) {
				$where .= " AND " . $typoscript ['additionalWhere'];
			}
			$sql = "select uid,pid," . $this->getStorageFields () . " from " . $this->getStorageNameSpace () . " where " . $where . $sort . " LIMIT " . $this->start . "," . ($this->limit);
			$query = $GLOBALS ['TYPO3_DB']->sql_query ( $sql );
			if ($query) {
				$querySize = $GLOBALS ['TYPO3_DB']->sql_affected_rows ( $query );
				for($i = 0; $i < $querySize; $i ++) {
					if ($i < $this->size) {
						$GLOBALS ['TYPO3_DB']->sql_data_seek ( $query, $i );
						$res = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $query );
						if (isset ( $_REQUEST ['q'] )) {
							$found = fale;
							foreach ( $res as $key => $val ) {
								if (is_array ( $this->autocompleteArray [$key] )) {
									if (is_array ( $this->uidsMM [$res ['uid']] ) && isset ( $this->html [$key] ['config.'] ['MM'] )) {
										$this->processData [$res ['uid']] [$key] = $this->uidsMM [$res ['uid']];
									} 
									elseif (! isset ( $this->html [$key] ['config.'] ['MM'] )) {
										foreach ( $this->autocompleteArray [$key] as $uid => $value ) {
											$val_exploded = explode ( ",", $val );
											foreach ( $val_exploded as $v )
												if (isset ( $this->autocompleteArray [$key] [$v] )) {
													if ($v == $uid)
														$this->processData [$res ['uid']] [$key] [$v] = $value;
												}
										}
									}
								} 
								elseif ($this->findSearchWord ( $res [$key] )) {
									$this->processData [$res ['uid']] [$key] = $this->findSearchWord ( $res [$key] );
									$match = true;
									$found [$this->processData [$res ['uid']] [$key] = $this->processData [$res ['uid']] [$key]];
								}
							}
						
						} 
						else
							$this->processData [$res ['uid']] = $res;
					}
				}
			} else {
				$this->mode = "QUERY_ERROR";
			}
			$this->postQuery ();
			$config ['view.'] ['count'] = $this->size;
			$config ['view.'] ['limit'] = $this->limit;
			$config ['view.'] ['page'] = $this->page;
			$config ['view.'] ['start'] = $this->start;
			$this->controller->configurations = new tx_lib_object ( $config );
		}
	}
	
	/**
	 * returns the where clause for the browse query
	 * 
	 * @return  string	sql where clasue
	 */	
	private function getFilterWhere() {
		$pars = $this->controller->parameters->getArrayCopy ();
		$typoscript = $this->controller->configurations->getArrayCopy ();
		$config = $typoscript;
		$search = "(pid=" . $this->panelRecord . " AND hidden=0 AND deleted=0";
		if (strlen ( $this->bigTCA ['languageField'] ) >= 3) {
			if (strlen ( $GLOBALS ['TSFE']->config ['config'] ['sys_language_uid'] >= 1 ))
				$search .= " AND " . $this->bigTCA ['languageField'] . "=" . $GLOBALS ['TSFE']->config ['config'] ['sys_language_uid'];
			else
				$search .= " AND " . $this->bigTCA ['languageField'] . "=0";
		}
		$table = $this->panelTable;
		if ($this->bigTCA ['enablecolumns'] ['fe_group']) {
			$search .= " AND (" . $table . "." . $this->bigTCA ['enablecolumns'] ['fe_group'] . "=0";
			if ($GLOBALS ['TSFE']->fe_user->user ['usergroup']) {
				$fegroups = explode ( ",", $GLOBALS ['TSFE']->fe_user->user ['usergroup'] );
				foreach ( $fegroups as $groupid ) {
					$search .= " OR " . $table . "." . $this->bigTCA ['enablecolumns'] ['fe_group'] . " IN ($groupid)";
				}
			}
			$search .= ")";
		}
		$search .= ")";
		unset ( $pars ['page'] );
		unset ( $pars ['lower'] );
		unset ( $pars ['upper'] );
		unset ( $pars ['limit'] );
		if (! is_array ( $pars ['search'] ) && strlen ( $pars ['search'] ) >= 1 || isset ( $_REQUEST ['q'] )) {
			$words [] = $pars ['search'];
			$fields = explode ( ",", "uid,pid," . $this->getStorageFields () );
			foreach ( $fields as $key => $val ) {
				if ($this->html [$val] ['search'] == 1) {
					$searchFields [$val] = $val;
				}
			}
			$i = 0;
			if (is_array ( $searchFields )) {
				foreach ( $searchFields as $key => $val ) {
					foreach ( $words as $k => $v ) {
						if ($i == 0) {
							//
							$textsearch .= " AND (" . $val . " like '%" . urldecode ( $v ) . "%'";
						} else {
							$textsearch .= " OR " . $val . " like '%" . urldecode ( $v ) . "%'";
						}
						$close = true;
					}
					$i ++;
				}
			}
			if (isset ( $_REQUEST ['q'] ))
				foreach ( $this->html as $item_key => $entry ) {
					echo $item_key;
					if (isset ( $entry ['options.'] ) && ! isset ( $entry ['config.'] ['MM'] ) && $entry ['config.'] ['type'] != "check") {
						foreach ( $entry ['options.'] as $key => $val ) {
							echo $val;
							$val = $this->getLL ( $val, 1 );
							if ($this->findSearchWord ( $val )) {
								$close = true;
								$this->autocompleteArray [$item_key] [$key] = $val;
								$textsearch .= " OR FIND_IN_SET(" . $key . "," . $item_key . ")";
							}
						}
					}
					if (isset ( $entry ['options.'] ) && isset ( $entry ['config.'] ['MM'] )) {
						foreach ( $entry ['options.'] as $key => $val ) {
							$val = $this->getLL ( $val, 1 );
							if ($this->findSearchWord ( $val )) {
								$this->autocompleteArray [$item_key] [$key] = $val;
								$MM [$item_key] [$key] = $key;
							}
						}
					}
				}
		}
		if (is_array ( $MM )) {
			$uids = $this->getMMFilterWhere ( $MM );
			if (is_array ( $uids )) {
				$this->uidsMM = $uids;
				$textsearch .= " OR ";
				if ($uids)
					foreach ( $uids as $uid => $mmVlaues ) {
						$textsearch .= $OR . "uid=" . $uid;
						$OR = " OR ";
					}
				$close = true;
			}
		}
		if (strlen ( $textsearch ) >= 3) $search .= $textsearch;
		$eval = array ();
		if (is_array ( $pars ['search'] ) && ! isset ( $_REQUST ['q'] )) {
			foreach ( $pars ['search'] as $key => $val ) {
				$filter = $pars ['search'] [$key];
				if (strlen ( $val ['min'] ) >= 1 && is_array ( $filter ) && $filter ['min'] >= 0) {
					if (! $date) {
						$search .= " AND " . $key . " >= " . $val ['min'];
					} else {
						$search .= " AND " . $key . " <= " . $val ['min'];
					}
				}
				if (strlen ( $val ['max'] ) >= 1 && is_array ( $filter ) && isset ( $filter ['max'] ) && $filter ['max'] >= 0) {
					if (! $date) {
						$search .= " AND " . $key . " <= " . $val ['max'];
					} else {
						$search .= " AND " . $key . " >= " . $val ['max'];
					}
				}
				if (is_array ( $filter ) && isset ( $filter ['integer'] ) && $filter ['leng'] > 0) {
					$search .= " AND " . $key . " like '" . $val . "%'";
				}
				if (is_array ( $filter ) && strlen ( $filter ['is'] ) >= 1) {
					
					if (is_array ( $filter ['is'] )) {
						foreach ( $filter ['is'] as $k => $v )
							if (strlen ( $v ) >= 1) {
								$v_exploded = explode ( ",", $v );
								foreach ( $v_exploded as $value )
									$search .= " AND FIND_IN_SET(" . $value . "," . $key . ")";
							}
					} else {
						if (strlen ( $filter ['is'] ) >= 1) {
							$v_exploded = explode ( ",", $filter ['is'] );
							foreach ( $v_exploded as $value ) {
								if (is_numeric ( $value ))
									$search .= " AND FIND_IN_SET(" . $value . "," . $key . ")";
								else
									$search .= " AND " . $key . "='" . $value . "'";
							}
						}
					}
				}
				if (is_array ( $filter ) && strlen ( $filter ['mm'] ) >= 1) {
					$v_exploded = explode ( ",", $filter ['mm'] );
					foreach ( $v_exploded as $value )
						$mm [$key] [$value] = $value;
				}
				if (! is_array ( $filter ) && ! empty ( $val )) {
					if ($val == "on")
						$val = 1;
					elseif ($val == "off")
						$val = 0;
					if (is_array ( $eval ) && in_array ( 'int', $eval ) || in_array ( 'integer', $eval )) {
						$search .= " AND " . $key . " = $val";
					} elseif (is_numeric ( $val )) {
						$search .= " AND " . $key . " = '$val'";
					} else {
						$search .= " AND " . $key . " like '%$val%'";
					}
				}
			}
			if (is_array ( $mm )) {
				if (is_array ( $uids = $this->getMMFilterWhere ( $mm ) )) {
					$search .= " AND (";
					foreach ( $uids as $uid => $mmValues ) {
						$search .= $OR . "uid=" . $uid;
						$OR = " OR ";
					}
					$search .= ")";
				}
			}
		}
		if ($close)
			$search .= ")";
		return $search;
	}
	
	/**
	 * returns the where mm clause for the browse query if an field has mm values
	 * 
	 * @param 	
	 * @return  string	sql where clasue
	 */	
	function getMMFilterWhere($mm) {
		if (is_array ( $mm )) foreach ( $mm as $item_key => $val ) {
			$config = $this->html [$item_key];
			$mmTable = $config ['config.'] ['MM'];
			foreach ( $val as $value => $key ) {
				$value3 = explode ( "__", $value );
				$array [$item_key] [$mmTable] [$value3 [1]] = $value3 [0];
			}
		}
		foreach ( $array as $item_key => $array2 ) {
			foreach ( $array2 as $mmTable => $records ) {
				foreach ( $records as $uid => $table ) {
					$table_exploded = explode ( "__", $table );
					$table = $table_exploded [0];
					if (isset ( $config ['allowed'] )) {
						$where = "(tablenames=\"" . $table . "\" AND uid_foreign=" . $uid . ")";
					} else {
						$where = "uid_foreign=" . $uid . "";
					}
					if (! isset ( $_GET ['q'] )) {
						if (isset ( $locals ))
							$last [] = "uid_foreign=" . $locals;
						$locals = $uid;
						if (is_array ( $data )) {
							$where .= " AND (";
							foreach ( $data as $uid => $values ) {
								$where .= $OR . "uid_local=" . $uid;
								$OR = " OR ";
							}
							$where .= ")";
							if (is_array ( $last )) {
								$where .= " AND (" . implode ( " OR ", $last ) . ")";
							}
						}
					}
					$query = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( "uid_local,uid_foreign,tablenames", $mmTable, $where );
					if ($query) {
						for($i = 0; $i < $GLOBALS ['TYPO3_DB']->sql_affected_rows ( $query ); $i ++) {
							$GLOBALS ['TYPO3_DB']->sql_data_seek ( $query, $i );
							$result = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $query );
							$res [] = $result;
							if (strlen ( $result ['tablenames'] ) <= 1)
								$table = $config ['config.'] ['foreign_table'];
							else
								$table = $result ['tablenames'];
							$data [$result ['uid_local']] [$table . "__" . $result ['uid_foreign']] = $table . "__" . $result ['uid_foreign'];
							$this->mmCount [$table . "__" . $result ['uid_foreign']] [$result ['uid_local']] = $table . "__" . $result ['uid_foreign'];
							$next = true;
						}
					}
				}
			}
		}
		return $data;
	}
	
	/**
	 * dummy fro overwrite the common function because in the browse we need no redirects
	 * 
	 * @param 	array	the browse data array
	 * @return  void
	 */
	public function _nextStep() {
		
	}
	
	// -------------------------------------------------------------------------------------
	// some helpers for the autocomplete
	// -------------------------------------------------------------------------------------
	
	/**
	 * sorts the browse results for the autocomplete
	 * 
	 * @param 	array	the browse data array
	 * @return  void
	 */	
	protected function sortAutocomplete($data) {
		$data = $this->processData;
		if (is_array ( $data ))
			foreach ( $data as $uid => $entry )
				foreach ( $entry as $key => $word ) {
					$match [$key] = $word;
					if ($this->autocompleteArray [$key]) {
						foreach ( $word as $value )
							$words [$key] [$value] [] = $value;
					} else
						$words [$key] [strtolower ( $word )] [] = strtolower ( $word );
				}
		$orgAutoArray = $this->autocompleteArray;
		if (is_array ( $words )) {
			foreach ( $words as $item_key => $data ) {
				foreach ( $data as $word => $values ) {
					$counts [$item_key] [$word] ['count'] = count ( $values );
					if (isset ( $this->autocompleteArray [$item_key] )) {
						unset ( $orgAutoArray [$item_key] );
						if (isset ( $this->html [$item_key] ['config.'] ['MM'] )) {
							$key = array_keys ( $this->autocompleteArray [$item_key], $word );
							$countsOptions [$item_key] [$this->html [$item_key] ['options.'] [$word]] ['value'] = $this->html [$item_key] ['options.'] [$word];
							$count [$item_key] [$word] = count ( $values );
							$countsOptions [$item_key] [$this->html [$item_key] ['options.'] [$word]] ['count'] = count ( $values );
						} else {
							$key = array_keys ( $this->autocompleteArray [$item_key], $word );
							$countsOptions [$item_key] [$key [0]] ['value'] = $this->getLL ( $this->html [$item_key] ['options.'] [$key [0]] );
							$count [$item_key] [$key [0]] = count ( $values );
							$countsOptions [$item_key] [$key [0]] ['count'] = count ( $values );
						}
					} else {
						$countsFree [$item_key] [$word] ['value'] = urlencode ( $word );
						$countsFree [$item_key] [$word] ['count'] = count ( $values );
						$count [$item_key] [$word] = count ( $values );
					}
				}
			}
		}
		if (is_array ( $count ))
			foreach ( $count as $item_key => $data ) {
				arsort ( $data, SORT_NUMERIC );
				$sortedCount [$item_key] = $data;
			}
		if (is_array ( $countsOptions )) {
			foreach ( $countsOptions as $item_key => $values ) {
				$string = implode ( " ", array_keys ( $values ) );
				$sorted = $sortedCount [$item_key];
				$autoOptions = "";
				$autoLabel = "";
				foreach ( $sorted as $word => $count ) {
					$sorted = $values [$word];
					$autoLabel .= $this->getLL ( $this->html [$item_key] ['options.'] [$word], 1 ) . " ";
					if (isset ( $this->html [$item_key] ['config.'] ['MM'] )) {
						$word_exploded = explode ( "__", $word );
						$autoOptions .= $this->getLL ( $this->html [$item_key] ['options.'] [$word], 1 ) . "|" . $this->getDesignator () . "[search][" . $item_key . "][mm]|" . $count . "|" . $word . "\n";
					} else {
						$autoOptions .= $this->getLL ( $this->html [$item_key] ['options.'] [$word], 1 ) . "|" . $this->getDesignator () . "[search][" . $item_key . "][is]|" . $count . "|" . $word . "\n";
					}
				}
				$auto .= $autoLabel . "|" . $this->getLL ( $this->html [$item_key] ['label'] ) . "\n" . $autoOptions;
			}
		}
		if (is_array ( $countsFree )) {
			foreach ( $countsFree as $item_key => $values ) {
				foreach ( $values as $word => $data ) {
					$free [$word] = $data ['count'];
				}
			}
			$freeCount = $free;
			foreach ( $free as $word => $count ) {
				$free2 = $free;
				unset ( $free2 [$word] );
				foreach ( $free2 as $word2 => $count2 ) {
					if (strpos ( trim ( strtolower ( $word2 ) ), trim ( $word ) ) !== false) {
						$freeCount [$word] += $count2;
					}
				}
			}
			asort ( $freeCount, SORT_NUMERIC );
			$freeCount = array_reverse ( $freeCount );
			$auto .= implode ( " ", array_keys ( $free ) ) . "| free Search\n";
			foreach ( $freeCount as $word => $count ) {
				$auto .= $word . "|" . $this->getDesignator () . "[search]|" . $count . "|" . urlencode ( $word ) . "\n";
			}
		}
		if (strlen ( $auto ) > 1)
			return $auto;
		else
			return "noResults|No Results";
	}
	
	/**
	 * finds the search word from $_GET['q'] in a string
	 * 
	 * @param 	string	the string to search in
	 * @return  string	the word if in the search from $_GET['q']
	 */	
	function findSearchWord($string) {
		$string = strip_tags ( $string );
		$string = str_replace ( ".", " ", $string );
		$string = str_replace ( ",", " ", $string );
		$string = str_replace ( "!", " ", $string );
		$string = str_replace ( "?", " ", $string );
		$string = str_replace ( "\"", " ", $string );
		$string = str_replace ( ":", " ", $string );
		$string = str_replace ( "&nbsp;", " ", $string );
		$string = str_replace ( "'", " ", $string );
		$words = explode ( " ", $string );
		foreach ( $words as $line => $word ) {
			if (strpos ( trim ( strtolower ( $word ) ), trim ( strtolower ( $_REQUEST ['q'] ) ) ) !== false) {
				return trim ( $word );
			}
		}
	}
}
?>