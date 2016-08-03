<?php
class moodBadge_Permission extends XenForo_Model {
	public static function debug($e){die(print_r($e,true));}
	public static $_instance = null;
	public $_permModel = null;
	public $_permissionCombinationId = array();
	public $_permissionCombinationByUserId = array();
	public $userPermissionsCache = array();
	
	public static function getInstance(){
		if(self::$_instance==null){
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	public function getPermModel(){
		if($this->_permModel==null){
			$this->_permModel = XenForo_Model::create('XenForo_Model_Permission');
		}
		return $this->_permModel;
	}
	public function getPermissionCombinationById($pcu){
		$pcu = intval($pcu);
		if(!array_key_exists($pcu,$this->_permissionCombinationId)){
			$this->_permissionCombinationId[$pcu] = $this->getPermModel()->getPermissionCombinationById($pcu);
		}
		return $this->_permissionCombinationId[$pcu];
	}
	public function queryUserGroup($uid){
		$uid = intval($uid);
		$q = 'SELECT `xf_permission_combination`.*
				FROM `xf_user`
				INNER JOIN `xf_permission_combination`
					ON (`xf_user`.`permission_combination_id` = `xf_permission_combination`.`permission_combination_id`)
				WHERE `xf_user`.`user_id` = ? ;';
		return $this->_getDb()->fetchRow($q,$uid);
	}
	public function getUserPermissions($uid){
		$uid = intval($uid);
		if(!array_key_exists($uid,$this->userPermissionsCache)){
			$permissions = array();
			$reply = $this->queryUserGroup($uid);
			if(array_key_exists('permission_combination_id',$reply) &&
			   array_key_exists('cache_value',$reply)){
				$combId = $reply['permission_combination_id'];
				$permissions = XenForo_Permission::unserializePermissions($reply['cache_value']);
				if(count($permissions)==0){ // if cache empty
					$permissions = array(); // then get from anoher source? That would take a long time to finish.
				}
			}
			$this->userPermissionsCache[$uid] = $permissions;
		}
		return $this->userPermissionsCache[$uid];
	}
	
	public function userHasPermission($uid,$permGroupId,$permId,$boolean=true,$unsetAction=false){
		$permissions = $this->getUserPermissions($uid);
		$permission = XenForo_Permission::hasPermission($permissions,$permGroupId,$permId);
		if($boolean && is_bool($permission)){
			$permission = boolval($permission);
		}
		else if($permission == 'unset'){
			$permission = $unsetAction;
		}
		else if($permission == 'allow'){
			$permission = true;
		}
		else if($permission == 'deny'){
			$permission = false;
		}
		return $permission;
	}
}
