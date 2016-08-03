<?php
class moodBadge_Permission {
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
	public function getPermissionCombinationByUserId($pcu){
		$pcu = intval($pcu);
		if(!array_key_exists($pcu,$this->_permissionCombinationByUserId)){
			$this->_permissionCombinationByUserId[$pcu] = $this->getPermModel()->getPermissionCombinationByUserId($pcu);
		}
		return $this->_permissionCombinationByUserId[$pcu];
	}
	public function getUserPermissionsUncached($uid){
		$uid = intval($uid);
		$permarr = $this->getPermissionCombinationByUserId($uid);
		if(!$permarr) $permarr = array();
		return $permarr;
	}
	public function getUserPermissions($uid){
		$uid = intval($uid);
		if(!array_key_exists($uid,$this->userPermissionsCache)){
			$reply = $this->getUserPermissionsUncached($uid);
			$combId = $reply['permission_combination_id'];
			$permissions = XenForo_Permission::unserializePermissions($reply['cache_value']);
			if(true||count($permissions)==0){ // if cache empty
				$permissions = XenForo_Permission::unserializePermissions($this->getPermissionCombinationById($combId)['cache_value']);
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
	
