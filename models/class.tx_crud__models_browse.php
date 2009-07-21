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
include_once (t3lib_extMgm::extPath ( 'crud' ) . 'models/class.tx_crud__models_retrieve.php');
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
		
		if(!$this->query) $this->browseQuery ();
	}
		
	/**
	 * makes the browse query
	 * 
	 * @return  void
	 */	
	private function browseQuery() {
		$pars = $this->controller->parameters->getArrayCopy ();
		//t3lib_div::debug($pars);
		$this->query=true;
		if (! is_array ( $this->processData )) {
			$start = microtime ( true );
			$this->preQuery ();
			$typoscript = $this->controller->configurations->getArrayCopy ();
			//t3lib_div::debug($typoscript);
			$config = $typoscript;
			
			if($config['view.']['limit']>=1) $this->limit = $config ['view.'] ['limit'];
			$pars = $this->controller->parameters->getArrayCopy ();
			if ($pars ['page'] >=1) {
				$this->page = $pars ['page'];
			}
			if ($pars ['limit'] >=1) {
				$this->limit = $pars ['limit'];
			}
			if ($this->page >= 1) {
				$this->start = $this->limit * $this->page;
			}
			//echo $this->page;
			if (! empty ( $pars ['upper'] )) {
				$sort = " ORDER BY " . $pars ['upper'] . " ASC";
			} elseif (! empty ( $pars ['lower'] )) {
				$sort = " ORDER BY " . $pars ['lower'] . " DESC";
			} else {
				$sort = "";
			}
			
			$where = $this->getFilterWhere ();
			//echo $where;
			if (! isset ( $_REQUEST['q'] ) && $count < 1) {
				$countQuery = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( "count(uid)", $this->getStorageNameSpace (), $where );
				if ($countQuery) {
					$countResult = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $countQuery );
					$count = $countResult ['count(uid)'];
				}
			}
			$this->size = $count;
			if (isset ( $_REQUEST ['q'] )) {
				$this->limit = 1000000;
				$this->start = 0;
				//$this->size = 500000;
			}
			if (is_array($typoscript ['storage.']['defaultFields.'])) {
				$where .= " AND ";
				foreach($typoscript ['storage.']['defaultFields.'] as $key=>$val) 
				{
					$exploded=explode(",",$val);
					
					foreach($exploded as $val2) if(strlen($val2)>=1){
						$where .= $OR. $key."=".$val2;
						$OR=" OR ";
					}
				}
			}
			$sql = "select uid,pid," . $this->getStorageFields () . " from " . $this->getStorageNameSpace () . " where " . $where . $sort . " LIMIT " . $this->start . "," . ($this->limit);
			//echo $sql;
			//die();
			//if($_REQUEST['debug'])t3lib_div::debug($sql,"sql");
			//if($_REQUEST['debug'])t3lib_div::debug($count,"results");
			$query = $GLOBALS ['TYPO3_DB']->sql_query ( $sql );
			if ($query) {
				//$querySize = $GLOBALS ['TYPO3_DB']->sql_affected_rows ( $query );
				while($res = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $query )) {
					//if ($i < $this->size) {
						//$GLOBALS ['TYPO3_DB']->sql_data_seek ( $query, $i );
						//$res = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $query );
						//t3lib_div::debug($_REQUEST);
						if (isset ( $_REQUEST ['q'] )) {
							$found = false;
							//echo "is q";
							if(!$found) foreach ( $res as $key => $val ) {
								if (is_array ( $this->autocompleteArray [$key] )) {
									//echo "exist";
									if (!$found && is_array ( $this->uidsMM [$res ['uid']] ) && isset ( $this->html [$key] ['config.'] ['MM'] )) {
										$this->processData [$res ['uid']] [$key] = $this->uidsMM [$res ['uid']];
										//$found=true;
									} 
									elseif (!$found && ! isset ( $this->html [$key] ['config.'] ['MM'] )) {
										foreach ( $this->autocompleteArray [$key] as $uid => $value ) {
											$val_exploded = explode ( ",", $val );
											foreach ( $val_exploded as $v )
												if (isset ( $this->autocompleteArray [$key] [$v] )) {
													if ($v == $uid)
														$this->processData [$res ['uid']] [$key] [$v] = $value;
														//$found=true;
												}
										}
									}
								} 
								elseif (!$found && $this->findSearchWord ( $res [$key] )) {
									$this->processData [$res ['uid']] [$key] = $this->findSearchWord ( $res [$key] );
									//$found = true;
									//$found [$this->processData [$res ['uid']] [$key] = $this->processData [$res ['uid']] [$key]];
								}
							}
						
						} 
						else
							$this->processData [$res ['uid']] = $res;
					//}
				}
			} else {
				$this->mode = "QUERY_ERROR";
			}
			$this->postQuery ();
			$config = $this->controller->configurations->getArrayCopy ();
			///t3lib_div::debug($this->processData);
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
/**
	 * returns the where clause for the browse query
	 * 
	 * @return  string	sql where clasue
	 */	
	public function getFilterWhere() {
		$pars = $this->controller->parameters->getArrayCopy ();
		$typoscript = $this->controller->configurations->getArrayCopy ();
		$config = $typoscript;
		$search = "(hidden=0 AND deleted=0";
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
		if(strlen($pid)>=1)  {
			$pids = explode(",",$config['storage.']['nodes']);
			$search.="AND (";
			if(is_array($pids)) foreach($pids as $pid) {
				$search.=$OR."pid=".$pid;
				$OR=" OR ";
			}
			$search.=")";
		}
		unset ( $pars ['page'] );
		unset ( $pars ['lower'] );
		unset ( $pars ['upper'] );
		unset ( $pars ['limit'] );
		if (! is_array ( $pars ['find'] ) && strlen ( $pars ['find'] ) >= 1 || isset ( $_REQUEST ['q'] )) {
			$words  = explode(" ",$pars ['find']);
			$fields = explode ( ",", "uid,pid," . $this->getStorageFields () );
			foreach ( $fields as $key => $val ) {
				if ($this->html [$val] ['search'] == 1) {
					$searchFields [$val] = $val;
				}
			}
			$i = 0;
			if (is_array ( $searchFields) && !isset($_REQUEST['q'])) {
				$textsearch .= " AND (";
				$y=0;
				
				foreach ( $searchFields as $key => $val ) {
					$z=0;
					//if($y==0) $textsearch.= "(";
					//else {
						///$textsearch .= " OR ";
					//}
					//$OR="";
					//echo $key;
					foreach ( $words as $k => $v ) {
						
						$textsearch .= $OR . $val . " like '%" . urldecode ( $v ) . "%'";
						$OR=" OR ";
						$close = true;
						
						$z++;
					}
					$y++;
					//$textsearch .= ") ";
					$i ++;
				}
			}
//			/echo $search; die();
			$OR="";
			if (isset ( $_REQUEST ['q'] )) {
				if(!$close) $textsearch.=" AND (";
				foreach ( $this->html as $item_key => $entry ) {
					//t3lib_div::Debug($entry);
					
					
					if (is_array( $entry ['options.'] ) && ! isset ( $entry ['config.'] ['MM'] ) && $entry ['config.'] ['type'] != "check") {
						foreach ( $entry ['options.'] as $key => $val ) {
							//echo $val;
							$val = $this->getLL ( $val, 1 );
							if ($this->findSearchWord ( $val )) {
								$close = true;
								//echo $val;
								$this->autocompleteArray [$item_key] [$key] = $val;
								$textsearch .= $OR."FIND_IN_SET(" . urldecode($key) . "," . $item_key . ")";
								$OR=" OR ";
							}
							
						}
					}
					elseif (isset ( $entry ['options.'] ) && isset ( $entry ['config.'] ['MM'] )) {
						foreach ( $entry ['options.'] as $key => $val ) {
							$val = $this->getLL ( $val, 1 );
							if ($this->findSearchWord ( $val )) {
								$this->autocompleteArray [$item_key] [$key] = $val;
								$close=true;
								//echo $val;
								$MM [$item_key] [$key] = $key;
							}
						}
					}
					elseif($entry['config.']['type']=="input" || $entry['config.']['type']=="text") {
							
							$textsearch .= $OR." ". $item_key . " like '%" . urldecode ( $_REQUEST['q'] ) . "%'";
							$close=true;
							$OR=" OR ";
					}
					//$OR=" OR ";
					
					$close=true;
				}
			}
		}
		$OR="";
		if($close) $textsearch.=") "; 
		//t3lib_div::debug($this->autocompleteArray);
	//	t3lib_div::debug($MM);;
	//echo $textsearch;die();
		if (is_array ( $MM )) {
			$uids = $this->getMMFilterWhere ( $MM );
			if (is_array ( $uids )) {
				
				$this->uidsMM = $uids;
				$textsearch .= " AND (  ";
				if ($uids)
					foreach ( $uids as $uid => $mmVlaues ) {
						$textsearch .= $OR . "uid=" . $uid;
						$OR = " OR ";
					}
				$close = true;
			}
			$textsearch=")";
		}
		
		if (strlen ( $textsearch ) >= 3) $search .= $textsearch;
		//echo $search;die();
		$eval = array ();
		
		if (is_array ( $pars ['search'] ) && ! isset ( $_REQUST ['q'] )) {
			foreach ( $pars ['search'] as $key => $val ) {
				$OR="";
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
					} 
					if (strlen ( $filter ['is'] ) >= 1) {
							$v_exploded = explode ( ",", $filter ['is'] );
							$search.=" AND(";
							foreach ( $v_exploded as $value ) {
								if (is_numeric ( $value ))
									$search .= $OR." FIND_IN_SET(" . urldecode($value) . "," . $key . ")";
								else
									$search .= $OR." " . $key . " like '%" . urldecode($value). "%'";
									$OR=" OR ";
							}
							$search.=")";
					}
					
				}
				if (is_array ( $filter ) && strlen ( $filter ['not'] ) >= 1) {
					$OR="";
					$search.= "AND( ";
					if(strlen($filter['not'])>=1) {
						$filter_exploded=explode(",",$filter['not']);
						foreach($filter_exploded as $single) {
							$search .= $OR.$key ." != '$single'";
							$OR=" AND ";
						}
					}
					$search.=") ";
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
						$search .= " AND " . $key . " like '%".urldecode($val)."%'";
					}
				}
			}
			$OR="";
			if (is_array ( $mm )) {
				foreach ( $mm as $key => $mmValues ) {
					$search .= " AND uid IN (SELECT uid_local from ".$this->html[$key]['config.']['MM']." where";
					foreach($mmValues as $val){
						$uid=explode("__",$val);
						$search .= $OR." uid_foreign=".$uid[1];
						$OR = " OR ";
					}
				}
				$search .= ")";
			}
		}
		
		//if ($close)
			//$search .= ")";
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
	    //if($_REQUEST['debug']) t3lib_div::debug($this->processData,"data");
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
						if(is_array($this->html[$item_key])) {
							$countsFree [$item_key] [$word] ['value'] = urlencode ( $word );
							$countsFree [$item_key] [$word] ['count'] = count ( $values );
							$count [$item_key] [$word] = count ( $values );
						}
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
		//if($_REQUEST['debug']) t3lib_div::debug($sortedCount,"sortedCount");
		//t3lib_div::debug($words,"words");
		if (is_array ( $countsFree )) {
			foreach ( $countsFree as $item_key => $values ) {
				foreach ( $values as $word => $data ) {
					//$allWords[$word]=$word;
					$free [$item_key][$word] = $data ['count'];
				}
			}
			$freeCount=$free;;
			//$free2 = $free;
			//if($_REQUEST['debug'])t3lib_div::debug($freeCount,"before");
			//if($_REQUEST['debug'])t3lib_div::debug($free2,"allWords");			
			foreach ( $free as $item_key=>$data) foreach ( $data as $word => $count ) {
				$allCount+=$count;
				$free2=$free;
				unset ( $free2[$item_key][$word] ); 
				foreach ( $free2[$item_key] as $word2 => $count2 ) {
					$word=mb_convert_encoding($word, "UTF-8", "UTF-8" );
					$word=strtolower(@iconv("UTF-8", "UTF-8//IGNORE", $word ));
					$word2=mb_convert_encoding($word2, "UTF-8", "UTF-8" );
					$word2=strtolower(@iconv("UTF-8", "UTF-8//IGNORE", $word2 ));
					if (strpos ( trim ( strtolower ( $word2 ) ), trim ( $word ) ) !== false) {
						$freeCount[$item_key] [$word] += $count2;
					}
					//elseif($_REQUEST['debug']) t3lib_div::debug($word,$word2);
				}
			}
			
			//t3lib_div::debug($freeCount,"pre");
			foreach($freeCount as $key=>$array) {
				arsort ( $array, SORT_NUMERIC );
				//$array = array_reverse ( $array );
				$freeCount[$key]=$array;
			}
			//t3lib_div::debug($freeCount,"post");
			//t3lib_div::debug($freeCount);
			$header = "| ".$this->getLL ("LLL:EXT:crud/locallang.xml:autocompleteResults")." \"".$_GET['q']."\" in ".$this->getLL ( $this->bigTCA['title'],1 )."\n";
			foreach($freeCount as $item_key=>$array) {
				$words=implode ( " ", array_keys ( $array) ) ;
				$allWords.=$words;
				//t3lib_div::debug($this->TCA);
				$auto .= $words. "| ".$this->getLL ( $this->html[$item_key] ['label'],1 ) ."\n";
				foreach ( $array as $word => $count ) {
					$auto .= $word . "|" . $this->getDesignator () . "[search][".$item_key."] |" . $count . "|" . urlencode ( $word ) . "\n";
				}
			}
			$out.=$allWords.$header.$auto;
			$auto=$out;
		}
		if (strlen ( $auto ) > 1)
			return $auto.$allWords."|".$this->getDesignator()."[search]|Alle Suchergebnisse\n";
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
		$string = mb_convert_encoding($string, "UTF-8", "UTF-8" );
		$string = @iconv("UTF-8", "UTF-8//IGNORE", $string );
		$what=mb_convert_encoding($_REQUEST['q'], "UTF-8", "UTF-8" );
		$what=@iconv("UTF-8", "UTF-8//IGNORE", $what );
		//t3lib_div::debug($what,$string."d");
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
			if ((strlen($word)>=2 && strlen($what)>=2) && strpos ( trim ( strtolower ( $word ) ), trim ( strtolower ( $what ) ) ) !== false) {
			//if(preg_match("/".strtolower($_REQUEST['q'])."/", strtolower($word), $matches))
				return trim ( $word );
				//echo "YESSS: ".$word;
			}
			//else echo "nor found in ".$word."<br>";
		}
	}
	
	
	

	
	function getStaticValues() {
		$config=$this->controller->configurations->getArrayCopy();
		$pars=$this->controller->parameters->getArrayCopy();
		if(strlen($config['getExistingValues.'][$this->panelTable."."]['fields'])>=1) {
			$pars=$this->controller->parameters->getArrayCopy();
			$search =$this->getFilterWhere();
			$hash=md5("exitingValues".$search);
			if(is_array($this->cached['existingValues'][$hash])) return $this->cached['existingValues'][$hash];
			$fields_exploded=explode(",",$config['getExistingValues.'][$this->panelTable."."]['fields']);
			foreach($fields_exploded as $field) {
				if(!isset($this->html[$field]['config.']['MM'])) {
					$fields[]=strtolower($field);
				}
				else {
					$mm = $this->getStaticMM($field,$mm);
				}
			}
			//t3lib_div::debug($mm);
			$i=0;
			if(is_array($fields))$fields=implode(",",$fields);
			else return false;
			//t3lib_div::debug($fields)
			$tables=strtolower($this->panelTable);
			$query=$GLOBALS['TYPO3_DB']->exec_SELECTquery("uid,".$fields,$tables,$search);
			if($query) {			
				while($result = mysql_fetch_assoc($query)) {
					$i++;
    				$fields_array=explode(",",$fields);
    				foreach($fields_array as $field) {
    					if(strlen($result[$field])>=1) $data[$this->panelTable][$field][$result[$field]]+=1;
    				}
    				if(is_array($mm[$result['uid']])) { 
    					foreach($mm[$result['uid']] as $field=>$mms) {
    					
    							 $mmTable=$this->html[$field]['config.']['foreign_table'];
								 foreach($mms as $uid)  $data[$this->panelTable][$field][$mmTable."__".$uid]+=1;
    						
    					}
    				}
				}
			}
 	        $start=  microtime(true);
			
			if(is_array($data)) foreach($data as $table=>$rows) {
				foreach($rows as $field=>$counts) {
					foreach($counts as $uids=>$count) {
						$uids_exploded=explode(",",$uids);
						if(sizeof($uids_exploded)>1) {
							foreach($uids_exploded as $uid) {
								unset($data[$table][$field][$uids]);
								$data[$table][$field][$uid]+=$count;
							}
						}
					}
				}
				
			}
			if(is_array($data))foreach($data[$this->panelTable] as $field=>$values) {
				$sorted="";
				if(is_array($values)){
					asort($values, SORT_NUMERIC );
					$values = array_reverse($values,true);
					$data[$this->panelTable][$field] = $values;
				}
			}	
			//t3lib_div::debug($data);
			if($i>10) $this->cache['existingValues'][$hash]=$data;
			if(is_array($data)) return $data;
		}	
		
	}
	
	
	function getStaticMM($field,$mm) {
		$setup=$this->html[$field];
		$pars=$this->controller->parameters->getArrayCopy();
		if(isset($pars['search'][$field]['mm'])) {
			$values=explode(",",$pars['search'][$field]['mm']);
		
			foreach($values as $value) {
				$value=explode("__",$value);
				$uids.=$OR."uid_foreign=".$value[1];
				$OR = " OR ";
			}
			$where=" where ";
			//$uids.=")";
		}
		if($setup['config.']['MM']) {
			$filter_exploded = explode("__",$filter);
			if(isset($setup['config.']['allowed']) && explode(",",$setup['config.']['allowed'])>=2) {
				$tablenames= 'tablenames="'.$filter_exploded[0].'"';
				$where=" where ";
			}
			//else $tablenames = "";
			
			if(isset($uids) && isset($tablenames))$uids=" AND (".$uids.")";
			$query=$GLOBALS['TYPO3_DB']->sql_query("select uid_foreign,uid_local from ".$setup['config.']['MM']." ".$where.$tablenames.$uids);
			if($query) {
				while($result = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($query)) {
					$mm[$result['uid_local']][$field][$result['uid_foreign']]=$result['uid_foreign'];
				}
			}
			return $mm;
		}
	}
	
	function getCategories() {
		$search=$this->getFilterWhere();
		//echo $search;
		//$hash=md5("existingCategories".$search);
		$pars=$this->controller->parameters->getArrayCopy();
		//t3lib_div::debug($pars);
		
		if(is_array($this->cached['existingCategories'][$hash])) return $this->cached['existingCategories'][$hash];
		require_once(t3lib_extMgm::extPath('categories') . 'lib/class.tx_categories_treeview.php');
		$menu=new tx_categories_treeview;
		$typoscript=$this->controller->configurations->getArrayCopy();
		$config=$typoscript;
		$conf=$typoscript['getExistingCategories.'];
		$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['yellowmed']);
		$y=0;
		foreach($conf as $id=>$value)  {
			$rootCats=explode(",",$value['rootId']);
			if(is_array($rootCats)) foreach($rootCats as $rootCat) {
				$menu->tree=false;
				$menu->getTree($rootCat,999);
				$rootTree=$menu->tree;
				//echo "firstlevel";
				$i=1;
				if(is_array($rootTree))foreach($rootTree as $key=>$cat) {
					$cats[$rootCat]['title']=$value['name'];
					$cats[$rootCat]['subs'][$cat['row']['uid']]['title']=$cat['row']['title'];
					$cats[$rootCat]['subs'][$cat['row']['uid']]['exist']=0;
					//echo "-".$cat['row']['uid'];
					$menu->tree=false;
					if($menu->getTree($cat['row']['uid'])>=1) {
						$subCats=$menu->tree;
						if(is_array($subCats)) { 
							$i++;
							foreach($subCats as $subkey=>$subval) {
								$cats[$rootCat]['subs'][$cat['row']['uid']]['subs'][$subval['row']['uid']]['title']=$subval['row']['title'];
								//TODO :more levels
								$field=explode(":",$value['field']);
								$sql="select count(".$field[1].") from ".$field[0]." where ".$field[1]." in(".$subval['row']['uid'].") AND ".$search;
								//echo $sql;
								//echo "--".$subval['row']['uid'];
								$query=$GLOBALS['TYPO3_DB']->sql_query($sql);
								if($query)$result = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($query);
								///t3lib_div::debug($result);
								$cats[$rootCat]['subs'][$cat['row']['uid']]['subs'][$subval['row']['uid']]['title']=$subval['row']['title'];
								if($result['count('.$field[1].')']>=1) {
									$cats[$rootCat]['subs'][$cat['row']['uid']]['exist']+=$result['count('.$field[1].')'];;
									$cats[$rootCat]['exist']+=$result['count('.$field[1].')'];
									$cats[$rootCat]['subs'][$cat['row']['uid']]['subs'][$subval['row']['uid']]['exist']=$result['count('.$field[1].')'];
									//t3lib_div::debug($subval);
								}
								$menu->tree=false;
								if($menu->getTree($subval['row']['uid'])>=1) {
									
									$subSubCats=$menu->tree;
									if(is_array($subSubCats)) { 
									foreach($subSubCats as $subsubkey=>$subsubval) {
										//echo "---".$subsubval['row']['uid'];
										$cats[$rootCat]['subs'][$cat['row']['uid']]['subs'][$subval['row']['uid']]['subs'][$subsubval['row']['uid']]['title']=$subsubval['row']['title'];
										//TODO :more levels
										$field=explode(":",$value['field']);
										$sql="select count(".$field[1].") from ".$field[0]." where ".$field[1]." in(".$subsubval['row']['uid'].") AND ".$search;
										//echo $sql;
										$query=$GLOBALS['TYPO3_DB']->sql_query($sql);
										if($query)$result = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($query);
									//3lib_div::debug($result);
										//$cats[$rootCat]['subs'][$cat['row']['uid']]['subs'][$subval['row']['uid']]['title']=$subval['row']['title'];
										if($result['count('.$field[1].')']>=1) {
											$cats[$rootCat]['subs'][$cat['row']['uid']]['exist']+=$result['count('.$field[1].')'];;
											$cats[$rootCat]['exist']+=$result['count('.$field[1].')'];
											$cats[$rootCat]['subs'][$cat['row']['uid']]['subs'][$subval['row']['uid']]['exist']+=$result['count('.$field[1].')'];
											$cats[$rootCat]['subs'][$cat['row']['uid']]['subs'][$subval['row']['uid']]['subs'][$subsubval['row']['uid']]['exist']=$result['count('.$field[1].')'];
											
										}
									}
									$menu->tree=false;
										if($menu->getTree($subsubval['row']['uid'])>=1) {
									
										$subSubSubCats=$menu->tree;
										//t3lib_div::debug($subSubSubCats);
										}
									}

								}
							}
						}
					}
					else {
						//t3lib_div::debug($cats,"cats");
						//$sql="select count(".$field[1].") from ".$field[0]." where ".$field[1]." in(".$subval['row']['uid'].") AND ".$search;
						//echo $sql;
						//$query=$GLOBALS['TYPO3_DB']->sql_query($sql);
						if($query)$result = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($query);
						$cats[$rootCat]['subs'][$cat['row']['uid']]['subs'][$subval['row']['uid']]['title']=$subval['row']['title'];
						if($result['count('.$field[1].')']>=1) {
							$cats[$rootCat]['subs'][$cat['row']['uid']]['exist']+=$result['count('.$field[1].')'];;
							$cats[$rootCat]['exist']+=$result['count('.$field[1].')'];
							$cats[$rootCat]['subs'][$cat['row']['uid']]['subs'][$subval['row']['uid']]['exist']=$result['count('.$field[1].')'];
						}
					}
				}
			}
		}
		//t3lib_div::debug($cats);
		if($i>10) $this->cache['existingCategories'][$hash]=$cats;
		return $cats;
	}
	
	
}
?>