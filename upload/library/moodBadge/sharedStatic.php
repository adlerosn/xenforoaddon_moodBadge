<?php

class moodBadge_sharedStatic
{
	public static $moodbadge = array(
		 0=>array('?','Not informed'),
		 1=>array('😂','Very happy'),
		 2=>array('😃','Happy'),
		 3=>array('😍','In love'),
		 4=>array('😋','Hungry'),
		 5=>array('😉','Clever'),
		 6=>array('😌','In peace'),
		 7=>array('😐','Neutral'),
		 8=>array('😒','Unamused'),
		 9=>array('😕','Confused'),
		10=>array('😢','Crying'),
		11=>array('😞','Sad'),
		12=>array('😠','Angry'),
		13=>array('😴','Sleepy'),
		14=>array('😔','Bored'),
		15=>array('😷','Sick')
		);
	
	public static function getMoodOptions(){
		$bdgs = self::$moodbadge;
		$xfopt = XenForo_Application::get('options');
		$extra = $xfopt->moodBadgeExtras;
		foreach($extra as $itm){
			$bdgs[]=$itm;
		}
		return $bdgs;
	}
	
	public static function render_AdminCP_CustomFieldsAdder(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit){
		$t = $preparedOption['option_value'];
		
		$choices = array();
		foreach($t as $entry){
			$choices[] = array(
				is_string($entry[0]) ? $entry[0] : '',
				is_string($entry[1]) ? $entry[1] : ''
			);
		}

		$editLink = $view->createTemplateObject('option_list_option_editlink', array(
			'preparedOption' => $preparedOption,
			'canEditOptionDefinition' => $canEdit
		));

		return $view->createTemplateObject('kiror_option_template_custom_badge_adder', array(
			'fieldPrefix' => $fieldPrefix,
			'listedFieldName' => $fieldPrefix . '_listed[]',
			'preparedOption' => $preparedOption,
			'formatParams' => $preparedOption['formatParams'],
			'editLink' => $editLink,

			'choices' => $choices,
			'nextCounter' => count($choices)
		));
	}
	
	public static function verifier_AdminCP_CustomFieldsAdder(array &$emojis, XenForo_DataWriter $dw, $fieldName){
		$output = array();

		foreach ($emojis AS $candidate){
			if (!isset($candidate[0]) || !isset($candidate[1]) || strlen($candidate[0])<=0 || strlen($candidate[1])<=0){
				continue;
			}

			$tmp = array($candidate[0], $candidate[1]);
			if ($tmp && !in_array($tmp,$output)){
				$output[] = $tmp;
			}
		}

		$emojis = $output;

		return true;
	}
	
	public static function createTableDB(){
		$dbc=XenForo_Application::get('db');
		$q='CREATE TABLE IF NOT EXISTS `kiror_moodbadge_users` (
		uid INT PRIMARY KEY,
		mood INT
		) CHARACTER SET utf8 COLLATE utf8_general_ci;';
		$dbc->query($q);
	}
	
	public static function dropTableDB(){
		$dbc=XenForo_Application::get('db');
		$q='DROP TABLE IF EXISTS `kiror_moodbadge_users`;';
		$dbc->query($q);
	}
	
	public static function setMyMood($moodcode){
		$moodcode=intval($moodcode);
		$visitor = XenForo_Visitor::getInstance();
		$uid = $visitor['user_id'];
		if(!is_int($uid)) $uid = 0;
		return self::setMood($uid,$moodcode);
	}

	public static function setMood($uid, $moodcode){
		$uid=intval($uid);
		$moodcode=intval($moodcode);
		$dbc=XenForo_Application::get('db');
		$q='DELETE FROM `kiror_moodbadge_users` WHERE uid='.$uid.';';
		$dbc->query($q);
		$q='INSERT INTO `kiror_moodbadge_users` (uid,mood) VALUES ('.$uid.','.$moodcode.');';
		$dbc->query($q);
	}
	
	public static function getMyMood(){
		$visitor = XenForo_Visitor::getInstance();
		$uid = $visitor['user_id'];
		if(!is_int($uid)) $uid = 0;
		return self::getMood($uid);
	}
	
	public static function getMood($uid){
		$uid=intval($uid);
		if (!self::userHasPermission($uid,'forum','moodbadgeset')){
			$m=self::getMoodOptions();
			return $m[0];
		}
		$dbc=XenForo_Application::get('db');
		$q='SELECT mood FROM `kiror_moodbadge_users` WHERE uid='.$uid.' LIMIT 1;';
		$mood=$dbc->fetchRow($q)['mood'];
		if(is_int($mood) && array_key_exists($mood,(self::getMoodOptions()))){
			$m = self::getMoodOptions();
			return $m[$mood];
		}else{
			$m=self::getMoodOptions();
			return $m[0];
		}
	}
	
	public static function hasMoodDefined($uid){
		$uid=intval($uid);
		if (!self::userHasPermission($uid,'forum','moodbadgeset')){
			return false;
		}
		$dbc=XenForo_Application::get('db');
		$q='SELECT mood FROM `kiror_moodbadge_users` WHERE uid='.$uid.' LIMIT 1;';
		$mood=$dbc->fetchRow($q)['mood'];
		if(is_int($mood) && array_key_exists($mood,(self::getMoodOptions()))){
			return ($mood != 0);
		}else{
			return false;
		}
	}
	
	public static function hasMoodCallback($contents, array $params, XenForo_Template_Abstract $template){
		return strtolower(self::getMood(intval($params['uid'])));
	}
		
	public static function getMoodCharCallback($contents, array $params, XenForo_Template_Abstract $template){
		return self::getMood(intval($params['uid']))[0];
	}
	
	public static function getMoodTitleCallback($contents, array $params, XenForo_Template_Abstract $template){
		return self::getMood(intval($params['uid']))[1];
	}
	
	public static function getMoodTitleLowerCallback($contents, array $params, XenForo_Template_Abstract $template){
		return strtolower(self::getMood(intval($params['uid']))[1]);
	}
	
	public static function getMoodTitleLowerIfDefinedCallback($contents, array $params, XenForo_Template_Abstract $template){
		if(!(self::hasMoodDefined(intval($params['uid'])))){
			return '';
		}
		else return $params['pre'].strtolower(self::getMood(intval($params['uid']))[1]).$params['aft'];
	}
	
	public static function renderMoodInThreadViewCallback($contents, array $params, XenForo_Template_Abstract $template){
		if(!(self::hasMoodDefined(intval($params['uid'])))){
			return '';
		}
		else{
			$m = self::getMood(intval($params['uid']));
			return '<dl class="pairsJustified" title="'.$m[1].'"><dt>Mood:</dt><dd>'.$m[0].'</dd></dl>';
		}
	}
	
	public static function renderMoodInProfileInfoCallback($contents, array $params, XenForo_Template_Abstract $template){
		if(!(self::hasMoodDefined(intval($params['uid'])))){
			return '';
		}
		else{
			$m = self::getMood(intval($params['uid']));
			return '<dl><dt>Mood:</dt><dd>'.$m[1].' - '.$m[0].'</dd></dl>';
		}
	}
	
	public static function getUserPermissions($uid){
		$uid = intval($uid);
		$userModel = XenForo_Model::create('XenForo_Model_User');
		$user = $userModel->getUserById($uid);
		$pci = array_key_exists('permission_combination_id',$user) ? $user['permission_combination_id'] : null;
		$gpc = array_key_exists('global_permission_cache',$user) ? $user['global_permission_cache'] : null;
		$permarr = array();
		if (!$gpc){
			$permarr = XenForo_Model::create('XenForo_Model_Permission')->rebuildPermissionCombinationById($pci);
			if(!$permarr){$permarr = array();};
		}else{
			if($gpc){
				$permarr = XenForo_Permission::unserializePermissions($gpc);
				if(!$permarr){$permarr = array();};
			}else{
				$permarr = array();
			}
		}
		return $permarr;
	}
	
	public static function userHasPermission($uid,$permGroupId,$permId){
		$permissions = self::getUserPermissions($uid);
		return XenForo_Permission::hasPermission($permissions,'forum','moodbadgeset');
	}
}
