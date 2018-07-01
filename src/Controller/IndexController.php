<?php

declare(strict_types=1);

namespace NPRadio\Controller;

use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class IndexController
{
    /** @var \Slim\Views\Twig */
    private $view;

    /**
     * IndexController constructor.
     *
     * @param ContainerInterface $container
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __construct(ContainerInterface $container)
    {
        $this->view = $container->get('view');
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return Response
     */
    public function getIndex(Request $request, Response $response, array $args): Response
    {
        return $this->view->render($response, 'index.html.twig');
    }
}
