<?php

namespace NPRadio\Controller;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;

class IndexController implements ControllerProviderInterface
{
    /**
     * Returns routes to connect to the given application.
     *
     * @param Application $app An Application instance
     *
     * @return ControllerCollection A ControllerCollection instance
     */
    public function connect(Application $app)
    {
        /** @var ControllerCollection $controller */
        $controller = $app['controllers_factory'];

        $controller->get('/', function (Application $app) {
            return $app['twig']->render('index.html.twig');
        });

        return $controller;
    }
}