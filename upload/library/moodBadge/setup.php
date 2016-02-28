<?php

class moodBadge_setup
{
	public static function install(){
		moodBadge_sharedStatic::createTableDB();
	}

	public static function reinstall(){
		moodBadge_sharedStatic::dropTableDB();
		moodBadge_sharedStatic::createTableDB();
	}

	public static function uninstall(){
		moodBadge_sharedStatic::dropTableDB();
	}
}
