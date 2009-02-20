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
 * Depends on: crud
 * 
 * class for diverse functions wich often need in crud
 *
 * @author Frank Thelemann <f.thelemann@yellowmed.com>
 * @package TYPO3
 * @subpackage tx_crud
 */
final class tx_crud__div {
	
	/**
	 * returns the get/post vars
	 * 
	 * @param 	string	$extKey	the extKey for the params
	 * @return  array	params array
	 */
	static function _GP($extKey) {
		$POST = t3lib_div::_POST ( $extKey );
		$GET = t3lib_div::_GET ( $extKey );
		if (is_array ( $POST ) and is_array ( $GET )) {
			foreach ( $POST as $key => $val )
				$GET [$key] = $val;
			$pars = $GET;
		} elseif (is_array ( $POST ))
			$pars = $POST;
		elseif (is_array ( $GET ))
			$pars = $GET;
		return $pars;
	}
	
	/**
	 * returns the secret action id af a form
	 * 
	 * @param 	array	$setup	the extKey for the params
	 * @param 	string 	$marker if set the md5 will generate with the marker
	 * @return  string	the md5	secret action is
	 */
	static function getActionID($setup = array(), $marker = false) {
		if ($marker)
			return md5 ( $marker );
		else
			return md5 ( $setup ['setup.'] ['marker'] );
	}
	
	/**
	 * returns an action link based on the setup
	 * 
	 * @param 	array	$setup	the setup
	 * @return  string	the action link form
	 */
	static function printActionLink($setup) {
		$action = $setup ['storage.'] ['action'];
		tx_crud__acl::setup ( $setup );
	
		$path = $_SERVER ['REQUEST_URI'];
		if (strlen ( $setup ['setup.'] ['baseURL'] ) >= 3) {
			$baseUrl = explode ( "/", $setup ['setup.'] ['baseURL'] );
			foreach ( $baseUrl as $part ) {
				if (strlen ( $part ) >= 2)
					$path = str_replace ( "/" . $part, "", $path );
			}
			
			$setup ['setup.'] ['baseURL'] . "/";
			$url = $path;
			$baseUrl = '_base_href="' . $setup ['setup.'] ['baseURL'] . '"';
			$url = substr ( str_replace ( "//", "/", $url ), 1, 100 );
		} else {
			$baseUrl = explode ( "/", $_SERVER ['SCRIPT_NAME'] );
			foreach ( $baseUrl as $part ) {
				if (strlen ( $part ) >= 2)
					$path = str_replace ( "/" . $part, "", $path );
			}
			
			$url = $path;
		}
		if ($setup ['enable.'] ['rights'] == 0) {
			$access = true;
		} elseif (is_array ( tx_crud__acl::getOptions () ))
			$access = true;
		if ($access) {
			$url = explode ( "&ajaxTarget", $url );
			$url = $url [0];
			$url = explode ( "?ajaxTarget", $url );
			$url = $url [0];
			$url = str_replace ( "&amp;", "&", $url );
			$url = str_replace ( "&", "&amp;", $url );
			$form = '<div class="crud-icon">' . "\n\t" . '<form  action="' . $url . '" method="post"><div>';
			$image = $setup ['setup.'] ['baseURL'] . 'typo3conf/ext/crud/resources/icons/' . $action . '.gif';
			$form .= '<input type="hidden" name="ajaxTarget" value="' . tx_crud__div::getAjaxTarget ( $setup, "printActionLink" ) . '" />' . "\n\t";
			$form .= '<input type="hidden" name="' . $setup ['setup.'] ['extension'] . '[form]" value="' . tx_crud__div::getActionID ( $setup ) . '" />' . "\n\t";
			$form .= '<input type="hidden" name="aID" value="' . tx_crud__div::getActionID ( $setup ) . '" />' . "\n\t";
			if(isset($_REQUEST['aID']) && !isset($_REQUEST['xID'])) $form .= '<input type="hidden" name="xID" value="'.$_REQUEST['aID'].'" />' . "\n\t";
			elseif(isset($_REQUEST['xID'])) $form .= '<input type="hidden" name="xID" value="'.$_REQUEST['xID'].'" />' . "\n\t";
			$form .= '<input type="hidden" name="mID" value="'.$setup ['setup.'] ['marker'].'" />' . "\n\t";
			$form .= '<input type="hidden" name="' . $setup ['setup.'] ['extension'] . '[icon]" value="1" />' . "\n\t";
			$form .= '<input type="hidden" name="' . $setup ['setup.'] ['extension'] . '[process]" value="' . strtolower ( $action ) . '" />' . "\n\t";
			$form .= '<input type="image" alt="' . $action . '" name="' . $setup ['setup.'] ['extension'] . '[submit]" value="Submit" src="' . $image . '" />' . "</div>\n\t</form>\n</div>\n"; //TODO: Localization
			return $form;
		} elseif ($setup ['icons.'] ['hideIfNoRights'] = ! '1')
			return $image = '<img src="' . $setup ['setup.'] ['baseURL'] . 'typo3conf/ext/crud/resources/icons/' . $action . '_norights.gif" alt="You have no Rights to ' . $action . ' this Record"/>';
	}
	
	/**
	 * returns the ajaxTarget for an function
	 * 
	 * @param 	array	$setup	the configuration setup
	 * @param 	string	$function	the name of the function fotr the ajaxTarget
	 * @return  array	params array
	 */
	static function getAjaxTarget($setup, $function) {
		if ($setup ['view.'] ['ajaxTargets.'] [$function]) {
			return $setup ['view.'] ['ajaxTargets.'] [$function];
		} else {
			return $setup ['view.'] ['ajaxTargets.'] ["default"];
		}
	}
	
	/**
	 * string analyse on wordlevel
	 * 
	 * @param 	string	$old	the orginal  string 	
	 * @param 	string	$new	the string to find changes
	 * @return  array	the analyses string array
	 */
	static function diff($old, $new) {
		$maxlen = 0;
		foreach ( $old as $oindex => $ovalue ) {
			$nkeys = array_keys ( $new, $ovalue );
			foreach ( $nkeys as $nindex ) {
				$matrix [$oindex] [$nindex] = isset ( $matrix [$oindex - 1] [$nindex - 1] ) ? $matrix [$oindex - 1] [$nindex - 1] + 1 : 1;
				if ($matrix [$oindex] [$nindex] > $maxlen) {
					$maxlen = $matrix [$oindex] [$nindex];
					$omax = $oindex + 1 - $maxlen;
					$nmax = $nindex + 1 - $maxlen;
				}
			}
		}
		if ($maxlen == 0)
			return array (array ('d' => $old, 'i' => $new ) );
		return array_merge ( $this->diff ( array_slice ( $old, 0, $omax ), array_slice ( $new, 0, $nmax ) ), array_slice ( $new, $nmax, $maxlen ), $this->diff ( array_slice ( $old, $omax + $maxlen ), array_slice ( $new, $nmax + $maxlen ) ) );
	}
	
	/**
	 * marks the changes from 2 string with <del> and <ins> tags
	 * 
	 * @param 	string	$old	the orginal  string 	
	 * @param 	string	$new	the string to find changes
	 * @return  string	the string with marked changes
	 */
	static function htmlDiff($old, $new) {
		$ret = "";
		$diff = $this->diff ( explode ( ' ', $old ), explode ( ' ', $new ) );
		foreach ( $diff as $k ) {
			if (is_array ( $k ))
				$ret .= (! empty ( $k ['d'] ) ? "<del>" . implode ( ' ', $k ['d'] ) . "</del> " : '') . (! empty ( $k ['i'] ) ? "<ins>" . implode ( ' ', $k ['i'] ) . "</ins> " : '');
			else
				$ret .= $k . ' ';
		}
		return $ret;
	}
}

/**
 * Depends on: crud
 * 
 * class for accessing rights based on the access control lists 
 *
 * @author Frank Thelemann <f.thelemann@yellowmed.com>
 * @package TYPO3
 * @subpackage tx_crud
 */
final class tx_crud__acl {
	
	private static $rights = FALSE;
	private static $fields;
	private static $namespace;
	private static $feuser;
	private static $action;
	private static $orgAction;
	private static $nodes;
	private static $type;
	private static $anonymGroup = FALSE;
	private static $cached = FALSE;
	
	// -------------------------------------------------------------------------------------
	// SETUP AND INIT THE ACLS
	// -------------------------------------------------------------------------------------
	

	/**
	 * setup the rights base on the access control lists for the fe_user
	 * 
	 * @param 	array	$setup	the typoscript configuration setup
	 * @return	void
	 */
	static public function setup($setup) {
		if (! is_array ( $setup ['storage.'] ))
			die ( "tx_crud__acl requires a ts setup" );
		tx_crud__acl::$namespace = $setup ['storage.'] ['nameSpace'];
		tx_crud__acl::$orgAction = $setup ['storage.'] ['action'];
		if (strtolower ( $setup ['storage.'] ['action'] ) == "browse")
			$action = "retrieve";
		else
			$action = $setup ['storage.'] ['action'];
		tx_crud__acl::$fields = $setup ['storage.'] ['fields'];
		tx_crud__acl::$action = $action;
		tx_crud__acl::$nodes = $setup ['storage.'] ['nodes'];
		tx_crud__acl::$type = $setup ['enable.'] ['rights'];
		if ($setup ['enable.'] ['anonymGroup']) {
			tx_crud__acl::$anonymGroup = $setup ['enable.'] ['anonymGroup'];
		}
		tx_crud__acl::_init ();
	}
	
	/**
	 * set the acls from the database
	 * 
	 * @return	void
	 */
	static private function _init() {
		if ($GLOBALS ["TSFE"]->loginUser) {
			$hash = "tx_crud__acl-" . $GLOBALS ['TSFE']->fe_user->user ["uid"] . "-" . tx_crud__acl::$orgAction . "-" . tx_crud__acl::$type;
			;
		} else {
			$hash = "tx_crud__acl-" . $GLOBALS ['TSFE']->fe_user->user ["ses_id"] . "-" . tx_crud__acl::$orgAction . "-" . tx_crud__acl::$type;
		}
		$sessionRights = tx_crud__cache::get ( $hash );
		if ($GLOBALS ['TSFE']->fe_user->user ['uid'] && is_array ( $sessionRights )) {
			tx_crud__acl::$rights = $sessionRights;
		} elseif (! $GLOBALS ['TSFE']->fe_user->user ['uid'] && is_array ( $sessionRights )) {
			tx_crud__acl::$rights = $sessionRights;
		
		} else {
			$fe_groups = @explode ( ',', $GLOBALS ['TSFE']->fe_user->user ['usergroup'] );
			if (! empty ( $fe_groups [0] )) {
				$i = 0;
				foreach ( $fe_groups as $key => $val ) {
					if ($i > 0) {
						$where .= ' OR tx_crud_groups.fe_groups=' . $val;
					} else {
						$where .= ' AND tx_crud_groups.fe_groups=' . $val;
					}
					$i ++;
				}
			}
			if (tx_crud__acl::$anonymGroup) {
				if ($GLOBALS ['TSFE']->fe_user->user ['usergroup']) {
					$where .= ' OR tx_crud_groups.fe_groups=' . tx_crud__acl::$anonymGroup;
				} else {
					$where .= ' AND tx_crud_groups.fe_groups=' . tx_crud__acl::$anonymGroup;
				}
			}
			if (! empty ( $where )) {
				
				if (tx_crud__acl::$type == "owner")
					$roleType = 1;
				elseif (tx_crud__acl::$type == "group")
					$roleType = 2;
				else
					$roleType = 3;
				$where .= " AND tx_crud_roles.deleted=0 AND tx_crud_roles.hidden=0 AND tx_crud_groups.deleted=0 AND tx_crud_groups.hidden=0";
				$query = $GLOBALS ['TYPO3_DB']->exec_SELECT_mm_query ( 'tx_crud_groups.allow_type AS GROUP_TYPE, tx_crud_roles.allow_type AS ROLE_TYPE, tx_crud_groups.uid AS GROUP_UID,tx_crud_groups.pid AS GROUP_PID,tx_crud_groups.title AS GROUP_TITLE,tx_crud_groups.subtitle AS GROUP_SUBTITLE,tx_crud_groups.fe_groups AS GROUP_MEMBERS,tx_crud_roles.uid AS ROLE_UID,tx_crud_roles.pid AS ROLE_PID,tx_crud_roles.title AS ROLE_TITLE,tx_crud_roles.subtitle AS ROLE_SUBTITLE,tx_crud_roles.allow_create AS ROLE_CREATE,tx_crud_roles.allow_retrieve AS ROLE_RETRIEVE,tx_crud_roles.allow_update AS ROLE_UPDATE,tx_crud_roles.allow_delete AS ROLE_DELETE', 'tx_crud_groups', 'tx_crud_groups_roles_mm', 'tx_crud_roles', $where, $groupBy = '', 'tx_crud_roles.sorting', $limit = '' );
			}
			if ($query) {
				for($i = 0; $i < $GLOBALS ['TYPO3_DB']->sql_num_rows ( $query ); $i ++) {
					$GLOBALS ['TYPO3_DB']->sql_data_seek ( $query, $i );
					$result = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $query );
					
					if (($result ['GROUP_TYPE'] == $roleType and $result ['ROLE_TYPE'] == $roleType) or ($result ['ROLE_TYPE'] == 3 and $result ['GROUP_TYPE'] == 3)) {
						tx_crud__acl::$rights [$result ['GROUP_UID']] ['GROUP_UID'] = $result ['GROUP_UID'];
						tx_crud__acl::$rights [$result ['GROUP_UID']] ['GROUP_TYPE'] = $result ['GROUP_TYPE'];
						tx_crud__acl::$rights [$result ['GROUP_UID']] ['GROUP_PID'] = $result ['GROUP_PID'];
						tx_crud__acl::$rights [$result ['GROUP_UID']] ['GROUP_TITLE'] = $result ['GROUP_TITLE'];
						tx_crud__acl::$rights [$result ['GROUP_UID']] ['GROUP_SUBTITLE'] = $result ['GROUP_SUBTITLE'];
						tx_crud__acl::$rights [$result ['GROUP_UID']] ['GROUP_MEMBERS'] = $result ['GROUP_MEMBERS'];
						tx_crud__acl::$rights [$result ['GROUP_UID']] ['GROUP_ROLES'] [$result ['ROLE_UID']] ['ROLE_UID'] = $result ['ROLE_UID'];
						tx_crud__acl::$rights [$result ['GROUP_UID']] ['GROUP_ROLES'] [$result ['ROLE_UID']] ['ROLE_PID'] = $result ['ROLE_PID'];
						tx_crud__acl::$rights [$result ['GROUP_UID']] ['GROUP_ROLES'] [$result ['ROLE_UID']] ['ROLE_TYPE'] = $result ['ROLE_TYPE'];
						tx_crud__acl::$rights [$result ['GROUP_UID']] ['GROUP_ROLES'] [$result ['ROLE_UID']] ['ROLE_TITLE'] = $result ['ROLE_TITLE'];
						tx_crud__acl::$rights [$result ['GROUP_UID']] ['GROUP_ROLES'] [$result ['ROLE_UID']] ['ROLE_SUBTITLE'] = $result ['ROLE_SUBTITLE'];
						$possibleActions = array ("create" => "ROLE_CREATE", "retrieve" => "ROLE_RETRIEVE", "update" => "ROLE_UPDATE", "delete" => "ROLE_DELETE" );
						foreach ( $possibleActions as $key => $val ) {
							$allow = "allow_" . $key;
							$options = $GLOBALS ['TYPO3_DB']->exec_SELECT_mm_query ( '*', 'tx_crud_roles', 'tx_crud_roles_' . $allow . '_mm', 'tx_crud_options', $GLOBALS ['TYPO3_DB']->quoteStr ( 'AND ' . $allow . '=' . $result [$val] . " AND tx_crud_options.deleted=0", 'tx_crud_roles' ), $groupBy = '', 'tx_crud_roles_' . $allow . '_mm.sorting', $limit = '' );
							if ($options) {
								$querySize = $GLOBALS ['TYPO3_DB']->sql_num_rows ( $options );
								for($y = 0; $y < $querySize; $y ++) {
									$GLOBALS ['TYPO3_DB']->sql_data_seek ( $options, $y );
									$option = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $options );
									tx_crud__acl::$rights [$result ['GROUP_UID']] ['GROUP_ROLES'] [$result ['ROLE_UID']] ['ROLE_OPTIONS'] [$val] [$option ['target']] [$option ['value']] = $allow;
								}
							}
						}
					}
				}
			}
			if (tx_crud__acl::$type == "group") {
				tx_crud__acl::_setGroupRoleID ();
			}
			if (tx_crud__acl::$rights) {
				
				tx_crud__cache::write ( $hash, tx_crud__acl::$rights );
			}
		
		}
		
		if (tx_crud__acl::$type == "owner") {
			if ($GLOBALS ['TSFE']->fe_user->user ['uid'] != tx_crud__log::getCreator ( tx_crud__acl::$namespace, tx_crud__acl::$nodes )) {
				foreach ( tx_crud__acl::$rights as $groupid => $roles ) {
					if ($roles ['GROUP_TYPE'] == 1) {
						unset ( tx_crud__acl::$rights [$groupid] );
					}
				}
			}
		}
	}
	
	/**
	 * set the fe_userin the lowest groupmember if the rights from type group
	 * 
	 * @return	void
	 */
	static private function _setGroupRoleID() {
		foreach ( tx_crud__acl::$rights as $groupID => $groups ) {
			$groupRoleID = false;
			if ($groups ['GROUP_TYPE'] == 2 && count ( $groups ['GROUP_ROLES'] > 1 )) {
				$query = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( "crud_role", "tx_crud_users", "crud_group=" . $groups ['GROUP_UID'] . " AND feuser=" . $GLOBALS ['TSFE']->fe_user->user ["uid"] );
				if ($query && $result = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $query )) {
					$groupRoleID = $result ['crud_role'];
				} else {
					foreach ( $groups ['GROUP_ROLES'] as $uid => $array )
						$groupRoleID = $uid;
					$insert ['tstamp'] = time ();
					$insert ['crdate'] = time ();
					$insert ['crud_role'] = $groupRoleID;
					$insert ['crud_group'] = $groups ['GROUP_UID'];
					$insert ['feuser'] = $GLOBALS ['TSFE']->fe_user->user ["uid"];
					$GLOBALS ['TYPO3_DB']->exec_INSERTquery ( "tx_crud_users", $insert );
				}
			} else {
				foreach ( $groups ['GROUP_ROLES'] as $uid => $array )
					$groupRoleID = $uid;
			}
			if ($groupRoleID) {
				$role = tx_crud__acl::$rights [$groupID] ['GROUP_ROLES'] [$groupRoleID];
				unset ( tx_crud__acl::$rights [$groupID] ['GROUP_ROLES'] );
				tx_crud__acl::$rights [$groupID] ['GROUP_ROLES'] [$groupRoleID] = $role;
			}
		}
	}
	
	// -------------------------------------------------------------------------------------
	// GETTER
	// -------------------------------------------------------------------------------------
	

	/**
	 * returns all access control lists for the user based on the setup
	 * 
	 * @param 	boolean		$all	if set all rights will returned, if empty only the rigths based on the setup will returned
	 * @return	void
	 */
	static public function getOptions($all = 0) {
		if ($all == 0) {
			$action = "ROLE_" . strtoupper ( tx_crud__acl::$action );
			if (is_array ( tx_crud__acl::$rights ))
				foreach ( tx_crud__acl::$rights as $groupid => $val ) {
					foreach ( $val ['GROUP_ROLES'] as $roleid => $role ) {
						if (isset ( $role ["ROLE_OPTIONS"] [$action] [strtolower ( tx_crud__acl::$namespace )] ) && is_array ( $role ["ROLE_OPTIONS"] [$action] [strtolower ( tx_crud__acl::$namespace )] )) {
							$fields = explode ( ",", tx_crud__acl::$fields );
							if (is_array ( $fields )) {
								foreach ( $fields as $key => $field ) {
									if ($role ["ROLE_OPTIONS"] [$action] [tx_crud__acl::$namespace] [$field]) {
										$rights [tx_crud__acl::$namespace] [tx_crud__acl::$orgAction] [$field] = $field;
									}
								}
							}
						}
					}
				}
		}
		if (is_array ( $rights )) {
			return $rights;
		} else {
			return FALSE;
		}
	}
	
	/**
	 * returns true if the fe_user has any rights
	 * 
	 * @return	boolean	true is the user has some rights
	 */
	public function hasRights() {
		if ($this->rights) {
			return true;
		}
	}
	
	// -------------------------------------------------------------------------------------
	// API DUMMIES WICH COMES SOON
	// -------------------------------------------------------------------------------------
	

	/**
	 * returns all acl groups for the fe_user
	 * 
	 * @return	array the groups in a array
	 */
	public function getGroups() {
	}
	
	/**
	 * returns all acl roles for the fe_user
	 * 
	 * @return	array	all roles in an array
	 */
	public function getRoles() {
	}
	
	/**
	 * returns all member from a group
	 * 
	 * @return	array	alle meber from a special acl group
	 */
	public function getMembers($groupID) {
	}
	
	// -------------------------------------------------------------------------------------
	// CHECK ACLS HELPER
	// -------------------------------------------------------------------------------------
	

	/**
	 * returns true if the fe_user can create data based on the setup
	 * 
	 * @return	boolean	true is the user can create
	 */
	public function canCreate() {
	}
	
	/**
	 * returns true if the fe_user can retrieve data based on the setup
	 * 
	 * @return	boolean	true is the user can retrieve
	 */
	public function canRetrieve() {
	}
	
	/**
	 * returns true if the fe_user can update data based on the setup
	 * 
	 * @return	boolean	true is the user can update
	 */
	public function canUpdate() {
	}
	
	/**
	 * returns true if the fe_user can delete data based on the setup
	 * 
	 * @return	boolean	true is the user can delete
	 */
	public function canDelete() {
	}
	
	/**
	 * returns true if the fe_user can create acl options
	 * 
	 * @return	boolean	true is the user can create acl options
	 */
	public function canCreateOption() {
	}
	
	/**
	 * returns true if the fe_user can retrieve acl options
	 * 
	 * @return	boolean	true is the user can retrieve acl options
	 */
	public function canRetrieveOption() {
	}
	
	/**
	 * returns true if the fe_user can update acl options
	 * 
	 * @return	boolean	true is the user can update acl options
	 */
	public function canUpdateOption() {
	}
	
	/**
	 * returns true if the fe_user can delete acl options
	 * 
	 * @return	boolean	true is the user can delete acl options
	 */
	public function canDeleteOption() {
	}
	
	/**
	 * returns true if the fe_user can create acl roles
	 * 
	 * @return	boolean	true is the user can create acl roles
	 */
	public function canCreateRole() {
	}
	
	/**
	 * returns true if the fe_user can retrieve acl roles
	 * 
	 * @return	boolean	true is the user can retrieve acl roles
	 */
	public function canRetrieveRole() {
	}
	
	/**
	 * returns true if the fe_user can update acl roles
	 * 
	 * @return	boolean	true is the user can update acl roles
	 */
	public function canUpdateRole() {
	}
	
	/**
	 * returns true if the fe_user can delete acl roles
	 * 
	 * @return	boolean	true is the user can delete acl roles
	 */
	public function canDeleteRole() {
	}
	
	/**
	 * returns true if the fe_user can create acl group
	 * 
	 * @return	boolean	true is the user can create acl group
	 */
	public function canCreateGroup() {
	}
	
	/**
	 * returns true if the fe_user can retrieve acl group
	 * 
	 * @return	boolean	true is the user can retrieve acl group
	 */
	public function canRetrieveGroup() {
	}
	
	/**
	 * returns true if the fe_user can update acl groups
	 * 
	 * @return	boolean	true is the user can update acl group
	 */
	public function canUpdateGroup() {
	}
	
	/**
	 * returns true if the fe_user can delete acl group
	 * 
	 * @return	boolean	true is the user can delete acl group
	 */
	public function canDeleteGroup() {
	}
	
	// -------------------------------------------------------------------------------------
	// ACL ACTIONS
	// -------------------------------------------------------------------------------------
	

	/**
	 * create an acl option
	 * 
	 * @param	array $id	the acl option to create the option
	 * @return	void
	 */
	public function createOption($option) {
	}
	
	/**
	 * create an acl role
	 * 
	 * @param	array $option	the acl option to create the rolw
	 * @return	void
	 */
	public function createRole($option) {
	}
	
	/**
	 * create an acl group
	 * 
	 * @param	array $option	the acl option to create the group
	 * @return	void
	 */
	public function createGroup($option) {
	}
	
	/**
	 * create an acl group
	 * 
	 * @param 	integer	$id	the uid of the option for an update
	 * @param	array $option	the updated acl option 
	 * @return	void
	 */
	public function updateOption($id, $option) {
	}
	
	/**
	 * update an acl role
	 * 
	 * @param 	integer	$id	the uid of the acl role for an update
	 * @param	array $option	the  updated acl role
	 * @return	void
	 */
	public function updateRole($id, $option) {
	}
	
	/**
	 * update an acl group
	 * 
	 * @param 	integer	$id	the uid of the option for an update
	 * @param	array $option	the acl updates acl option 
	 * @return	void
	 */
	public function updateGroup($id, $option) {
	}
	
	/**
	 * delete an acl option
	 * 
	 * @param 	integer	$id	the uid of the option to delete
	 * @return	void
	 */
	public function deleteOption($id) {
	}
	
	/**
	 * delete an acl role
	 * 
	 * @param 	integer	$id	the uid of the role to delete
	 * @return	void
	 */
	public function deleteRole($id) {
	}
	
	/**
	 * delete an acl group
	 * 
	 * @param 	integer	$id	the uid of the group to delete
	 * @return	void
	 */
	public function deleteGroup($id) {
	}

}

/**
 * Depends on: crud
 * 
 * class for read and write caches. use the new caching system of 4.3, but it has also an fallback to an crud own caching table for lower typo3 versions.
 *
 * @author Frank Thelemann <f.thelemann@yellowmed.com>
 * @package TYPO3
 * @subpackage tx_crud
 */
class tx_crud__cache {
	
	/**
	 * read an caching entry
	 * 
	 * @param 	string	$hash	the hash for the cache to find and read
	 * @return	void
	 */
	public static function get($hash) {
		if (t3lib_div::compat_version ( "4.3" )) {
			return $GLOBALS ['TSFE']->sys_page->getHash ( md5 ( $hash ) );
		} else {
			$where = 'uuid="' . md5 ( $hash ) . '"';
			$query = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( "cached", "tx_crud_cached", $where );
			if ($query) {
				$result = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $query );
			}
			if (strlen ( $result ['cached'] ) > 3) {
				return unserialize ( $result ['cached'] );
			} else {
				return false;
			}
		}
	}
	
	/**
	 * writes an caching entry
	 * 
	 * @param 	string	$hash	the hash id for the cache to write
	 * @param 	mixed	$data	the cache data
	 * @return	void
	 */
	public static function write($hash, $data) {
		if (t3lib_div::compat_version ( "4.3" )) {
			if (is_array ( $data ) && strlen ( $hash ) >= 3)
		//	t3lib_div::debug($data,"schreibe cache");
				$GLOBALS ['TSFE']->sys_page->storeHash ( md5 ( $hash ), $data, $hash );
		
		} else {
			$insert ['uid'] = "";
			$insert ['tstamp'] = time ();
			$insert ['uuid'] = $hash;
			$insert ['cached'] = serialize ( $data );
			if ($GLOBALS ['TYPO3_DB']->exec_INSERTquery ( "tx_crud_cached", $insert ))
				return true;
		}
	}
}

/**
 * Depends on: crud
 * 
 * class for read and write update histories
 *
 * @author Mattes Angelus <m.angelus@yellowmed.com>
 * @package TYPO3
 * @subpackage tx_crud
 */
final class tx_crud__histories {
	
	/**
	 * read all historie entries for a record
	 * 
	 * @param 	string	$table	the name of the table
	 * @param 	integer	$record	the uid of the record 
	 * @return	void
	 */
	public static function read($table, $record) {
		$starttime = microtime ( true );
		$queryHistory = $GLOBALS ['TYPO3_DB']->sql_query ( 'SELECT * FROM tx_crud_histories WHERE
			crud_table="' . $table . '" AND crud_record=' . $record . ' ORDER BY tstamp DESC' );
		
		$numHistories = $GLOBALS ['TYPO3_DB']->sql_affected_rows ( $queryHistory );
		
		for($i = 0; $i < $numHistories; $i ++) {
			$result [$i] = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $queryHistory );
		}
		$stop = microtime ( true );
		return $result;
	}
	
	/**
	 * write a history entry
	 * 
	 * @param 	string	$table	the name of the table
	 * @param 	integer	$record	the uid of the record 
	 * @param 	array	$oldData	the orginal record data before the update
	 * @return	void
	 */
	public static function write($table, $record, $oldData) {
		$starttime = microtime ( true );
		$insertHistory = array ();
		$insertHistory ['tstamp'] = time ();
		$insertHistory ['crud_table'] = $table;
		$insertHistory ['crud_record'] = $record;
		$insertHistory ['crdate'] = time ();
		$insertHistory ['cruser_id'] = $GLOBALS ['TSFE']->fe_user->user ['uid'];
		$insertHistory ['title'] = date ( 'd.m.Y-H:i:s' ) . '#' . $table . '#' . $record . '#' . $GLOBALS ['TSFE']->id;
		$insertHistory ['crud_user'] = $GLOBALS ['TSFE']->fe_user->user ["username"];
		$insertHistory ['crud_data'] = serialize ( $oldData );
		$insert = $GLOBALS ['TYPO3_DB']->exec_INSERTquery ( 'tx_crud_histories', $insertHistory );
		if (! $insert) {
			echo '%%%error_crud-history-query%%%';
		}
	}
	
	/**
	 * returns the dates of the last update histories in a confguration setup from a view/template
	 * 
	 * @return	array	the dates of the last updates
	 */
	public static function getHistoryDates() {
		$config = $this->controller->configurations->getArrayCopy ();
		$result [- 1] = "%%%now%%%";
		for($i = 0; $i < count ( $config ['view.'] ['histories'] ); $i ++) {
			$result [$i] = strftime ( $this->getLLfromKey ( "datetimeTCA.output" ), $config ['view.'] ['histories'] [$i] ['tstamp'] );
		
		}
		return $result;
	}
	
	/**
	 * returns the diffents of 2 history entries
	 * 
	 * @param 	string	$old	the first history entry
	 * @param 	string	$new	the second history entry
	 * @return	array	the highlighted array with the 2 history entries
	 */
	public static function getHistoryDiff($old, $new) {
		$config = $this->controller->configurations->getArrayCopy ();
		if (isset ( $new ) && $new < count ( $config ['view.'] ['histories'] ) && $new >= - 1 && isset ( $old ) && $old < count ( $config ['view.'] ['histories'] ) && $old >= - 1) {
			$data = $config ['view.'] ['data'];
			if ($new == - 1 || $old == - 1) {
				foreach ( $data as $uid => $record ) {
					$config ['view.'] ['histories'] [- 1] ['crud_data'] = serialize ( $data [$uid] );
				}
			}
			$histData1 [1] = unserialize ( $config ['view.'] ['histories'] [$new] ['crud_data'] );
			$histData1 = $this->renderPreview ( $histData1, 1 );
			$histData2 [1] = unserialize ( $config ['view.'] ['histories'] [$old] ['crud_data'] );
			$histData2 = $this->renderPreview ( $histData2, 1 );
			foreach ( $data as $uid => $record ) {
				foreach ( $record as $key => $val ) {
					$label = $this->getLL ( $config ['view.'] ['setup'] [$key] ['label'] );
					$result [$label] = "";
					$relConfig = $config ['view.'] ['setup'] [$key];
					if (is_array ( $relConfig ['options.'] )) {
						$hist_exploded1 = explode ( ",", $histData1 [1] [$key] );
						$hist_exploded2 = explode ( ",", $histData2 [1] [$key] );
						if (count ( $hist_exploded1 ) >= count ( $hist_exploded2 ))
							$border = count ( $hist_exploded1 );
						else
							$border = count ( $hist_exploded2 );
						foreach ( $hist_exploded1 as $histMMkey1 => $histMMValue1 ) {
							if (in_array ( $histMMValue1, $hist_exploded2 ))
								$result [$label] .= $this->getLL ( $histMMValue1, 1 );
							else
								$result [$label] .= "<ins>" . $this->getLL ( $histMMValue1, 1 ) . "</ins>";
							if ($hisMMkey1 < $border - 1)
								$result [$label] .= ", ";
						}
						foreach ( $hist_exploded2 as $histMMkey2 => $histMMValue2 ) {
							if (! in_array ( $histMMValue2, $hist_exploded1 )) {
								$result [$label] .= "<del>" . $this->getLL ( $histMMValue2, 1 ) . "</del>";
								if ($hisMMkey2 < $border - 1)
									$result [$label] .= ", ";
							}
						}
					} else {
						$result [$label] .= tx_crud__div::htmlDiff ( $this->getLL ( $histData1 [1] [$key], 1 ), $this->getLL ( $histData2 [1] [$key], 1 ) );
					}
				}
			}
			return $result;
		}
		return null;
	}
}

/**
 * Depends on: crud
 * 
 * class for lock and unlock records at updating
 *
 * @author Mattes Angelus <m.angelus@yellowmed.com>
 * @package TYPO3
 * @subpackage tx_crud
 */
final class tx_crud__lock {
	
	public static $status;
	
	/**
	 * init of an locking/unlocking mechanism
	 * 
	 * @param 	string	$table	the name of the db table
	 * @param 	string	$uid	the uid of the record
	 * @param	array	$config	the configuration setup
	 * @return 	void
	 */
	public static function init($table, $uid, $config) {
		tx_crud__lock::delOldLocks ( $config );
		$lock = tx_crud__lock::isLocked ( $table, $uid );
		
		if ($lock == $GLOBALS ['TSFE']->fe_user->id) {
			tx_crud__lock::$status = "UNLOCKED";
			tx_crud__lock::updateLock ( $table, $uid );
		} elseif (! $lock) {
			tx_crud__lock::$status = "UNLOCKED";
			tx_crud__lock::writeLock ( $table, $uid, $config );
		} else {
			tx_crud__lock::$status = "LOCKED";
		}
	}
	
	/**
	 * delete old lock entries
	 * 
	 * @param	array	$config	the configuration setup
	 * @return 	boolean
	 */
	public static function delOldLocks($config) {
		$queryDelOldLocks = $GLOBALS ['TYPO3_DB']->sql_query ( 'DELETE FROM tx_crud_locks WHERE
			' . time () . ' - tstamp > ' . $config ['timeout'] );
		return $queryDelOldLocks;
	}
	
	/**
	 * checks if an element is locked
	 * 
	 * @param 	string	$table	the name of the db table
	 * @param 	string	$uid	the uid of the record
	 * @return 	boolean
	 */
	public static function isLocked($table, $record) {
		$queryIsLocked = $GLOBALS ['TYPO3_DB']->sql_query ( 'SELECT crud_session FROM tx_crud_locks WHERE
			crud_table = "' . $table . '" AND crud_record = ' . $record );
		$numLocks = $GLOBALS ['TYPO3_DB']->sql_affected_rows ( $queryIsLocked );
		
		if ($numLocks == 0) {
			return false;
		} else {
			$result = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $queryIsLocked );
			return $result ['crud_session'];
		}
	}
	
	/**
	 * unlock an element
	 * 
	 * @param 	string	$table	the name of the db table
	 * @param 	string	$uid	the uid of the record
	 * @return 	boolean
	 */
	public static function unlock($table, $record) {
		$queryDelLock = $GLOBALS ['TYPO3_DB']->sql_query ( 'DELETE FROM tx_crud_locks WHERE
			crud_table = "' . $table . '" AND crud_record = ' . $record );
		return $queryDelLock;
	}
	
	/**
	 * lock an element
	 * 
	 * @param 	string	$table	the name of the db table
	 * @param 	string	$uid	the uid of the record
	 * @param 	$config	$config	the configurations setup
	 * @return 	boolean
	 */
	public static function writeLock($table, $record, $config) {
		$insertLock = array ();
		$insertLock ['tstamp'] = time ();
		$insertLock ['crdate'] = time ();
		$insertLock ['pid'] = $config ['pid'];
		$insertLock ['cruser_id'] = $GLOBALS ['TSFE']->fe_user->user ['uid'];
		$insertLock ['crud_table'] = $table;
		$insertLock ['crud_record'] = $record;
		$insertLock ['crud_user'] = $GLOBALS ['TSFE']->fe_user->user ["username"];
		$insertLock ['crud_session'] = $GLOBALS ['TSFE']->fe_user->id;
		$insert = $GLOBALS ['TYPO3_DB']->exec_INSERTquery ( 'tx_crud_locks', $insertLock );
		return $insert;
	}
	
	/**
	 * update the lock status timeout
	 * 
	 * @param 	string	$table	the name of the db table
	 * @param 	string	$uid	the uid of the record
	 * @return 	boolean
	 */
	public static function updateLock($table, $record) {
		$queryUpdate = $GLOBALS ['TYPO3_DB']->sql_query ( 'UPDATE tx_crud_locks SET tstamp = ' . time () . ' WHERE crud_table = "' . $table . '" AND crud_record = ' . $record . ' AND crud_user = "' . $GLOBALS ['TSFE']->fe_user->user ["username"] . '" AND crud_session="' . $GLOBALS ['TSFE']->fe_user->id . '"' );
		
		return $queryUpdate;
	}
}

/**
 * Depends on: crud
 * 
 * class for reading and writing logs for all action like creating,updating,deleting and retrieving
 *
 * @author Mattes Angelus <m.angelus@yellowmed.com>
 * @package TYPO3
 * @subpackage tx_crud
 */
final class tx_crud__log {
	
	/**
	 * read an log entry
	 * 
	 * @param 	string	$action	the log action
	 * @param 	string	$nodes	the uid of the record
	 * @param 	string	$nameSpace	the name of the table
	 * @param 	array	$setup	the configuration setup	for the logs
	 * @param 	boolean	$pageOnly	show only logs from a special page
	 * @return 	array	the array with the logs
	 */
	public function read($action, $nodes, $nameSpace, $setup, $pageOnly = false) {
		$legalActions = explode ( ',', $setup ['read.'] ['actions'] );
		if (! in_array ( $action, $legalActions )) {
			return;
		}
		$result = array ();
		$where = '';
		if ($pageOnly) {
			$where = 'crud_page=' . $GLOBALS ['TSFE']->id . ' AND ';
		}
		$queryCreate = $GLOBALS ['TYPO3_DB']->sql_query ( 'SELECT * FROM tx_crud_log WHERE
			crud_table="' . $nameSpace . '" AND crud_action="create" AND
			crud_record=' . $nodes );
		$numCreates = $GLOBALS ['TYPO3_DB']->sql_affected_rows ( $queryCreate );
		if ($numCreates > 0) {
			$temp = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $queryCreate );
			$result ['create'] ['user'] = $temp ['crud_user'];
			$result ['create'] ['id'] = $temp ['cruser_id'];
			$result ['create'] ['tstamp'] = $temp ['tstamp'];
		}
		$queryCounts = $GLOBALS ['TYPO3_DB']->sql_query ( 'SELECT * FROM tx_crud_log WHERE (crud_action="update" OR
			crud_action="retrieve") AND ' . $where . 'crud_table="' . $nameSpace . '" AND crud_record=' . $nodes );
		$stop2 = microtime ( true );
		if ($queryCounts) {
			$numCounts = $GLOBALS ['TYPO3_DB']->sql_affected_rows ( $queryCounts );
			$result ['update'] ['count'] = 0;
			$result ['retrieve'] ['count'] = 0;
			for($i = 0; $i < $numCounts; $i ++) {
				$temp = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $queryCounts );
				//				/echo $temp[0].$temp[1];
				$result [$temp ['crud_action']] ['count'] += $temp ['crud_cardinality'];
				if ($temp ['crud_cardinality'] > 0) {
					$query [$i] = $GLOBALS ['TYPO3_DB']->sql_query ( '
						SELECT * FROM tx_crud_log WHERE
						crud_table="' . $nameSpace . '" AND ' . $where . 'crud_action="' . $temp ['crud_action'] . '" AND
						crud_record=' . $nodes . ' ORDER BY tstamp DESC' );
					$numData = min ( $GLOBALS ['TYPO3_DB']->sql_affected_rows ( $query [$i] ), $setup ['read.'] ['max'] );
					for($j = 0; $j < $numData; $j ++) {
						$temp2 = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $query [$i] );
						$result [$temp ['crud_action']] [$j] ['user'] = $temp2 ['crud_user'];
						$result [$temp ['crud_action']] [$j] ['id'] = $temp2 ['cruser_id'];
						$result [$temp ['crud_action']] [$j] ['tstamp'] = $temp2 ['tstamp'];
						$result [$temp ['crud_action']] [$j] ['crud_page'] = $temp2 ['crud_page'];
					}
				}
			}
		}
		return $result;
	}
	
	/**
	 * write an log entry
	 * 
	 * @param 	string	$action	the log action
	 * @param 	string	$nodes	the uid of the record
	 * @param 	string	$nameSpace	the name of the table
	 * @param 	array	$setup	the configuration setup	for the logs
	 * @return 	boolean
	 */
	 public function write($action, $nodes, $nameSpace, $setup) {
		$insertLog = array ();
		if ($action == 'retrieve') {
			$query = $GLOBALS ['TYPO3_DB']->sql_query ( 'SELECT * FROM tx_crud_log
				WHERE crud_table="' . $nameSpace . '" AND crud_action="retrieve"
				AND crud_session="' . $GLOBALS ['TSFE']->fe_user->id . '"
				AND crud_record=' . $nodes );
			$numRows = $GLOBALS ['TYPO3_DB']->sql_affected_rows ( $query );
			if ($numRows > 0) {
				return;
			}
		}
		$legalActions = explode ( ',', $setup ['write.'] ['actions'] );
		if (in_array ( $action, $legalActions )) {
			if ($action == 'retrieve' || $action == 'update') {
				// mehr als 10 geloggte daten fuer den record und die action
				$query = $GLOBALS ['TYPO3_DB']->sql_query ( 'SELECT * FROM tx_crud_log
					WHERE crud_table="' . $nameSpace . '" AND crud_action="' . $action . '"
					AND crud_record=' . $nodes . ' ORDER BY tstamp DESC 
					LIMIT ' . $setup ['write.'] ['max'] . ',18446744073709551615' );
				$numRows = $GLOBALS ['TYPO3_DB']->sql_affected_rows ( $query );
				if ($numRows > 0) {
					$where = '';
					$kard = 1;
					for($i = 0; $i < $numRows; $i ++) {
						$tempResult = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $query );
						$where .= "uid=" . $tempResult ['uid'] . " OR ";
						$kard += $tempResult ['crud_cardinality'];
					}
					$query = $GLOBALS ['TYPO3_DB']->sql_query ( 'DELETE FROM tx_crud_log
						WHERE ' . substr ( $where, 0, strlen ( $where ) - 4 ) );
					$insertLog ['crud_cardinality'] = $kard;
				}
			} else if ($action == 'delete') {
				$query = $GLOBALS ['TYPO3_DB']->sql_query ( 'DELETE FROM tx_crud_log
					WHERE crud_record=' . $nodes );
			}
			$insertLog ['pid'] = $setup ['write.'] ['pid'];
			$insertLog ['tstamp'] = time ();
			$insertLog ['crdate'] = time ();
			$insertLog ['cruser_id'] = $GLOBALS ['TSFE']->fe_user->user ['uid'];
			$insertLog ['title'] = date ( 'd.m.Y-H:i:s' ) . '#' . $action . '#' . $nodes . '#' . $GLOBALS ['TSFE']->id;
			$insertLog ['crud_action'] = $action;
			$insertLog ['crud_table'] = $nameSpace;
			$insertLog ['crud_record'] = $nodes;
			$insertLog ['crud_page'] = $GLOBALS ['TSFE']->id;
			$insertLog ['crud_session'] = $GLOBALS ['TSFE']->fe_user->id;
			$insertLog ['crud_user'] = $GLOBALS ['TSFE']->fe_user->user ["username"];
			$insert = $GLOBALS ['TYPO3_DB']->exec_INSERTquery ( 'tx_crud_log', $insertLog );
			if (! $insert) {
				echo '%%%error_crud-log-query%%%';
			}
		}
	}
	
	/**
	 * return the creator of a log by date
	 * 
	 * @param 	string	$table	the name of the table
	 * @param 	string	$record	the uid of the record
	 * @return 	integer	the uid of fe_user
	 */
	 public function getCreatorByData($table, $record) {
		$where = 'crud_table="' . $table . '" AND crud_action="create" AND crud_record=' . $record;
		$query = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( "cruser_id", "tx_crud_log", $where );
		if ($query && $result = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $query )) {
			return $result ['cruser_id'];
		}
	}
	
	/**
	 * return the creator of a log in the configurations setup of an view
	 * 
	 * @return 	integer	the uid of fe_user
	 */
	public function getCreator() {
		$config = $this->controller->configurations->getArrayCopy ();
		if (! $config ['enable.'] ['logging']) {
			return false;
		}
		if (isset ( $config ['view.'] ['logs'] ['create'] )) {
			return $config ['view.'] ['logs'] ['create'] ['user'];
		} else {
			return false;
		}
	}
	
	/**
	 * return the date of log create in the configurations setup of an view
	 * 
	 * @return 	mixed	the date of the log
	 */
	public function getCreationDate() {
		$config = $this->controller->configurations->getArrayCopy ();
		if (! $config ['enable.'] ['logging']) {
			return false;
		}
		if (isset ( $config ['view.'] ['logs'] ['create'] )) {
			return strftime ( $this->getLLfromKey ( "datetimeTCA.output" ), $config ['view.'] ['logs'] ['create'] ['tstamp'] );
		} else {
			return false;
		}
	}
	
	/**
	 * return the count of log entries in the configurations setup of an view
	 * 
	 * @param	$action	the action for the log
	 * @return 	mixed	the log count
	 */
	public function getLogUserCount($action) {
		$config = $this->controller->configurations->getArrayCopy ();
		if (! $config ['enable.'] ['logging']) {
			return false;
		}
		if (isset ( $config ['view.'] ['logs'] [$action] )) {
			return $config ['view.'] ['logs'] [$action] ['count'];
		} else {
			return false;
		}
	}
	
	/**
	 * return the last user of a log in the configurations setup of an view
	 * 
	 * @param	$action	the action for the log
	 * @return 	string	the user
	 */
	public function getLastLogUser($action) {
		$config = $this->controller->configurations->getArrayCopy ();
		if (! $config ['enable.'] ['logging']) {
			return false;
		}
		if (isset ( $config ['view.'] ['logs'] [$action] [0] )) {
			return $config ['view.'] ['logs'] [$action] [0] ['user'];
		} else {
			return false;
		}
	}
	
	/**
	 * return the last date of a log in the configurations setup of an view
	 * 
	 * @param	$action	the action for the log
	 * @return 	string	the last log date 
	 */
	public function getLastLogDate($action) {
		$config = $this->controller->configurations->getArrayCopy ();
		if (! $config ['enable.'] ['logging']) {
			return false;
		}
		if (isset ( $config ['view.'] ['logs'] [$action] [0] )) {
			return strftime ( $this->getLLfromKey ( "datetimeTCA.output" ), $config ['view.'] ['logs'] [$action] [0] ['tstamp'] );
		} else {
			return false;
		}
	}
}

?>