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

final class  tx_crud__acl   {
	
	static private $rights = FALSE;
	private $fields;
	private $namespace;
	private $feuser; 
	private $action;
	private $anonymGroup = FALSE;
	private $cached = FALSE;
	
	public function __construct($namespace,$fields,$action,$anon=FALSE,$feuser=FALSE) {
		$this->namespace = $namespace;
		$this->orgAction=$action;
		if(strtolower($action)=="browse") $action="retrieve";
		$this->fields = $fields;
		$this->action = $action;
		
		if ($anon) {
			$this->anonymGroup = $anon;
		}
		$this->_init();
	}

	public function getOptions($all=0) {
		//t3lib_div::debug($this->rights);
		if ($all == 0) {
			$action = "ROLE_" . strtoupper($this->action);
			if(is_array($this->rights)) foreach($this->rights as $groupid=>$val) {
				foreach ($val['GROUP_ROLES'] as $roleid=>$role) {
					if (is_array($role["ROLE_OPTIONS"][$action][$this->namespace])) {
						$fields = explode(",",$this->fields);
						if (is_array($fields)) {
							foreach ($fields as $key=>$field) {
						 		if ($role["ROLE_OPTIONS"][$action][$this->namespace][$field]) {
									$rights[$this->namespace][$this->orgAction][$field]=$field;
								}
							}
						}
					}
				}
			}
		}
		if (is_array($rights)) {
			return $rights;
		} else {
			return FALSE;
		}
	}
	
	public function getGroups() {
	}

	public function getRoles() {

	}

	public function getMembers() {}

	public function hasRights() {
		if ($this->rights) {
			return true;
		}
	}
	
	public function canCreate() {}
	
	public function canRetrieve() {}
	
	public function canUpdate() {}
	
	public function canDelete() {}
	
	public function canCreateACL() {}
	
	public function canUpdateACL() {}
	
	public function canDeleteACL() {}
	
	public function createOption($id,$option) {}
	
	public function createRole($id,$option) {}
	
	public function createGroup($feusers,$option) {}
	
	public function updateOption($id,$option) {}
	
	public function updateRole($id,$option) {}
	
	public function updateGroup($id,$option) {}
	
	public function deleteOption($id,$option) {}
	
	public function deleteRole($id,$option) {}
	
	public function deleteGroup($id,$option) {}
	
	private function _init() {  // TODO: merge rights von user und ses wenn login stauts changed und cache nur fuer jeweilige gruppen und nicht pro user
		//debug($this->action);
		if ($GLOBALS["TSFE"]->loginUser) {
			$sessionRights = $GLOBALS["TSFE"]->fe_user->getKey("user","tx_crud__acl");
		} else {
			$sessionRights = $GLOBALS["TSFE"]->fe_user->getKey("ses","tx_crud__acl");
		}
		if ($GLOBALS['TSFE']->fe_user->user['uid'] && is_array($sessionRights)) {
			 $this->rights = $sessionRights;
			//debug($this->rights,"cached mit Login");
		} elseif(!$GLOBALS['TSFE']->fe_user->user['uid'] && is_array($sessionRights)) {
			 $this->rights = $sessionRights;
			 //debug($this->rights,"cached ohne login");
		} else {
			//echo "hole acl aus db";
			$fe_groups = @explode(',',$GLOBALS['TSFE']->fe_user->user['usergroup']);
			if (!empty($fe_groups[0]))  {
				$i = 0;
				foreach ($fe_groups as $key=>$val) {
					if ($i > 0) {
						$where .= ' OR tx_crud_groups.fe_groups=' . $val;
					} else {
						$where .= ' AND tx_crud_groups.fe_groups=' . $val;
					}
					$i++;
				}
			}
			if ($this->anonymGroup) {
				if ($GLOBALS['TSFE']->fe_user->user['usergroup']) {
					$where .=' OR tx_crud_groups.fe_groups=' . $this->anonymGroup;
				} else {
					$where .=' AND tx_crud_groups.fe_groups=' . $this->anonymGroup;
				}
			}
			if (!empty($where)) {
				$where .= " AND tx_crud_roles.deleted=0 AND tx_crud_roles.hidden=0 AND tx_crud_groups.deleted=0 AND tx_crud_groups.hidden=0";
				$query = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query('tx_crud_groups.uid AS GROUP_UID,tx_crud_groups.pid AS GROUP_PID,tx_crud_groups.title AS GROUP_TITLE,tx_crud_groups.subtitle AS GROUP_SUBTITLE,tx_crud_groups.fe_groups AS GROUP_MEMBERS,tx_crud_roles.uid AS ROLE_UID,tx_crud_roles.pid AS ROLE_PID,tx_crud_roles.title AS ROLE_TITLE,tx_crud_roles.subtitle AS ROLE_SUBTITLE,tx_crud_roles.allow_create AS ROLE_CREATE,tx_crud_roles.allow_retrieve AS ROLE_RETRIEVE,tx_crud_roles.allow_update AS ROLE_UPDATE,tx_crud_roles.allow_delete AS ROLE_DELETE','tx_crud_groups','tx_crud_groups_roles_mm','tx_crud_roles',$where,$groupBy='','tx_crud_roles.sorting',$limit='');
			}
			if ($query) {
				for ($i = 0; $i < $GLOBALS['TYPO3_DB']->sql_num_rows($query); $i++) {
					$GLOBALS['TYPO3_DB']->sql_data_seek($query,$i);
					$result = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($query);
					$this->rights[$result['GROUP_UID']]['GROUP_UID'] = $result['GROUP_UID'];
					$this->rights[$result['GROUP_UID']]['GROUP_PID'] = $result['GROUP_PID'];
					$this->rights[$result['GROUP_UID']]['GROUP_TITLE'] = $result['GROUP_TITLE'];
					$this->rights[$result['GROUP_UID']]['GROUP_SUBTITLE'] = $result['GROUP_SUBTITLE'];
					$this->rights[$result['GROUP_UID']]['GROUP_MEMBERS'] = $result['GROUP_MEMBERS'];
					$this->rights[$result['GROUP_UID']]['GROUP_ROLES'][$result['ROLE_UID']]['ROLE_UID'] = $result['ROLE_UID'];
					$this->rights[$result['GROUP_UID']]['GROUP_ROLES'][$result['ROLE_UID']]['ROLE_PID'] = $result['ROLE_PID'];
					$this->rights[$result['GROUP_UID']]['GROUP_ROLES'][$result['ROLE_UID']]['ROLE_TITLE'] = $result['ROLE_TITLE'];
					$this->rights[$result['GROUP_UID']]['GROUP_ROLES'][$result['ROLE_UID']]['ROLE_SUBTITLE'] = $result['ROLE_SUBTITLE'];
					$possibleActions = array("create"=>"ROLE_CREATE","retrieve"=>"ROLE_RETRIEVE","update"=>"ROLE_UPDATE","delete"=>"ROLE_DELETE");
					foreach ($possibleActions as $key=>$val) {
						$allow = "allow_".$key;
						$options = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query('*','tx_crud_roles','tx_crud_roles_' . $allow . '_mm','tx_crud_options',$GLOBALS['TYPO3_DB']->quoteStr('AND ' . $allow . '='.$result[$val]." AND tx_crud_options.deleted=0",'tx_crud_roles'),$groupBy='','tx_crud_roles_' . $allow . '_mm.sorting',$limit='');
						if ($options) {
							for ($y = 0; $y < $GLOBALS['TYPO3_DB']->sql_num_rows($options); $y++) {
								$GLOBALS['TYPO3_DB']->sql_data_seek($options,$y);
								$option = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($options);
								$this->rights[$result['GROUP_UID']]['GROUP_ROLES'][$result['ROLE_UID']]['ROLE_OPTIONS'][$val][$option['target']][$option['value']] = $allow;
							}
						}
					}
				}
			}
			if ($this->rights) {
				//debug($this->rights);
				if ($GLOBALS["TSFE"]->loginUser) {
					$GLOBALS["TSFE"]->fe_user->setKey("user","tx_crud__acl",$this->rights);
					//echo"store acl user session";
				} else {
					$GLOBALS["TSFE"]->fe_user->setKey("ses","tx_crud__acl",$this->rights);
					//echo "store acl ses session";
				}
			}
		}
	}
}

?>