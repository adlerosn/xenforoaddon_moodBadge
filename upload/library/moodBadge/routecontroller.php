<?php

class moodBadge_routecontroller extends XenForo_ControllerPublic_Abstract{
	public function actionIndex(){
		$visitor = XenForo_Visitor::getInstance();
		if(!$visitor['user_id']){
			throw $this->getNoPermissionResponseException();
		};
		$viewParams = array();
		if (!$this->_input->inRequest('redirect')){
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				$this->getDynamicRedirect(false, false)
			);
		}
		$redirect = $this->_input->filterSingle('redirect',XenForo_Input::STRING);
		if (!$this->_input->inRequest('mood_id')){
			$mbs=moodBadge_sharedStatic::getMoodOptions();
			$mymood=moodBadge_sharedStatic::getMyMood();
			$_xfToken=$visitor['csrf_token_page'];
			$viewParams['html']='';
			foreach($mbs as $mood_id=>$mb){
				$lnkparam=array(
					'_xfToken'=>$_xfToken,
					'redirect'=>$redirect,
					'mood_id'=>$mood_id
				);
				$lnk=XenForo_Link::buildPublicLink('moodchanging','',$lnkparam);
				$viewParams['html'].='
						<li class="">
							<a href="'.$lnk.'" class="'.(($mymood[0]==$mb[0])?'changerSelected':'').'">
								<span class="title" style="margin-left: 5px;">'.$mb[0].'</span>
								<span class="description" style="margin-left: 5px;">'.$mb[1].'</span>
							</a>
						</li>';
				}
			$viewParams['visitor']=$visitor;
			return $this->responseView('XenForo_ViewPublic_Base', 'kiror_floating_mood_changer', $viewParams);
		}
		if($this->_input->inRequest('_xfToken')&&
		   $this->_input->inRequest('redirect')&&
		   $this->_input->inRequest('mood_id')){
			$mood_id = $this->_input->filterSingle('mood_id',XenForo_Input::INT);
			moodBadge_sharedStatic::setMyMood($mood_id);
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				$this->getDynamicRedirect(false, false)
			);
		}
		else{
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				$this->getDynamicRedirect(false, false)
			);
		}
	}
}
