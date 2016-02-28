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
		13=>array('😴','Sleepy')
		);
	
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
		$dbc=XenForo_Application::get('db');
		$q='SELECT mood FROM `kiror_moodbadge_users` WHERE uid='.$uid.' LIMIT 1;';
		$mood=$dbc->fetchRow($q)['mood'];
		if(is_int($mood) && array_key_exists($mood,(self::$moodbadge))){
			$m = self::$moodbadge;
			return $m[$mood];
		}else{
			$m=self::$moodbadge;
			return $m[0];
		}
	}
	
	public static function hasMoodDefined($uid){
		$uid=intval($uid);
		$dbc=XenForo_Application::get('db');
		$q='SELECT mood FROM `kiror_moodbadge_users` WHERE uid='.$uid.' LIMIT 1;';
		$mood=$dbc->fetchRow($q)['mood'];
		if(is_int($mood) && array_key_exists($mood,(self::$moodbadge))){
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
	
	public static function getMoodOptions(){
		return self::$moodbadge;
	}
}
