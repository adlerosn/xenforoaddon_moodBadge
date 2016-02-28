<?php

class moodBadge_routeinterface implements XenForo_Route_Interface
{
	public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
	{
		return $router->getRouteMatch('moodBadge_routecontroller', $routePath);
	}
}
