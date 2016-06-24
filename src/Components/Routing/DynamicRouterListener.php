<?php

namespace Components\Routing;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

class DynamicRouterListener extends RouterListener
{
    /**
     * @var RouteCollection
     */
    protected $routes;

    protected $path;

    function __construct(RequestStack $requestStack)
    {
        $this->path = $requestStack->getCurrentRequest()->getPathInfo() ?: '/';
        
        $this->routes = new RouteCollection();
        parent::__construct(
            new UrlMatcher($this->routes, new RequestContext()), $requestStack
        );

        $this->loadRoutes();
    }

    protected function loadRoutes()
    {
        $pathInfo = explode('/', $this->path);
        $pathInfo = array_filter($pathInfo);

        $controller = 'AppBundle:Default:index';

        switch(count($pathInfo)) {
            case 3:
                $controller = str_ireplace('index', array_pop($pathInfo), $controller);
            case 2:
                $controller = str_ireplace('Default', ucfirst(array_pop($pathInfo)), $controller);
            case 1:
                $controller = str_ireplace('App', ucfirst(array_pop($pathInfo)), $controller);
        }

        $defaults = [
            '_controller' => $controller,
        ];

        $this->routes->add(
            'dynamic_route_' . ($this->routes->count() + 1),
            new Route(
                $this->path,
                $defaults,
                $requirements = []
            )
        );
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        try {
            parent::onKernelRequest($event);
        } catch(NotFoundHttpException $e) {
        }
    }
}