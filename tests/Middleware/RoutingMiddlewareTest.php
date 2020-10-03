<?php

declare(strict_types = 1);

namespace Larium\Framework\Middleware;

use Larium\Framework\Action\DefaultAction;
use Larium\Framework\Contract\Routing\RequestArguments;
use Larium\Framework\Contract\Routing\Router;
use Larium\Framework\Http\ResponseFactory;
use Larium\Framework\Http\ServerRequestFactory;
use Larium\Framework\RequestHandler\RequestHandler;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RoutingMiddlewareTest extends TestCase
{
    public function testShouldMatchRoute(): void
    {
        $m = new RoutingMiddleware($this->getRouter(DefaultAction::class, ['page' => 1]));
        $request = (new ServerRequestFactory)->createServerRequest('GET', 'https://example.com/page/1');

        $m->process($request, $this->createRequestHandler());
    }

    private function getRouter(string $action, array $args): Router
    {
        $mock = $this->getMockBuilder(Router::class)
            ->setMethods(['match'])
            ->getMock();

        $requestArguments = new RequestArguments($action, $args);

        $mock->expects($this->once())
            ->method('match')
            ->willReturn($requestArguments);

        return $mock;
    }

    private function createRequestHandler(): RequestHandlerInterface
    {
        $mock = $this->getMockBuilder(RequestHandlerInterface::class)
            ->setMethods(['handle'])
            ->getMock();

        $mock->expects($this->once())
            ->method('handle')
            ->with($this->callback(function (ServerRequestInterface $r) {
                return $r->getAttribute('_action') === DefaultAction::class
                    && $r->getAttribute('page') === 1;
            }))
            ->willReturn((new ResponseFactory())->createResponse());

        return $mock;
    }
}
