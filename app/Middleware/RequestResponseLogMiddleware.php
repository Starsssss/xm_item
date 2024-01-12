<?php
declare(strict_types=1);
namespace App\Middleware;

use Hyperf\Context\Context;
use Hyperf\Logger\LoggerFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;


/**
 * 请求和响应的日志中间件
 */
class RequestResponseLogMiddleware implements MiddlewareInterface
{
    private ContainerInterface $container;
    private HttpResponse $response;
    private RequestInterface $request;
    protected LoggerInterface $logger;

    /**
     * @param ContainerInterface $container
     * @param HttpResponse $response
     * @param RequestInterface $request
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(ContainerInterface $container, HttpResponse $response, RequestInterface $request,LoggerFactory $loggerFactory)
    {
        $this->container = $container;
        $this->response = $response;
        $this->request = $request;
        // 第一个参数对应日志的 name, 第二个参数对应 config/autoload/logger.php 内的 key
        $this->logger = $loggerFactory->get('log', 'default');
    }

    /**
     * 处理请求并返回响应
     *
     * @param ServerRequestInterface $request 请求对象
     * @param RequestHandlerInterface $handler 请求处理器对象
     * @return ResponseInterface 响应对象
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = Context::get(ResponseInterface::class);
        $response = $response->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            // Headers 可以根据实际情况进行改写。
            ->withHeader('Access-Control-Allow-Headers', 'DNT,Keep-Alive,User-Agent,Cache-Control,Content-Type,Authorization');

        Context::set(ResponseInterface::class, $response);

        if ($request->getMethod() == 'OPTIONS') {
            return $response;
        }

        $request->getMethod();
        $request->getAttributes();
        $request->getBody();
        $request->getHeaders();
        $request->getUploadedFiles();
        $request->getUri();
        $request->getCookieParams();
        $request->getQueryParams();
        $request->getServerParams();

        // $request = ...; // 你的 $request 对象

        $method = strtoupper($request->getMethod());

        $headers = $request->getHeaders();
        $userAgent = $headers['User-Agent'] ?? null; // 需要从headers中提取user-agent

        $uri = $request->getUri();
        $url = $request->url();

        $queryParams = $request->getQueryParams();
        $serverParams = $request->getServerParams();


        $response =$handler->handle($request);
        $requestResponseLog= [
            'method' => $method,
            'url' => $this->request->url(),
            'response_code' => $response->getStatusCode(),
            'body' => $response->getBody()->getContents(),
            'headers' => $headers,
            'user_agent' => $userAgent,
            // 'uri' => $this->request->getUri(),
            'query_params' => $queryParams,
            'params' => $this->request->all(),
            // 'server_params' => $serverParams,
        ];
        // var_dump($requestResponseLog);
        $this->logger->info(json_encode($requestResponseLog));
        return $response;
    }
}
