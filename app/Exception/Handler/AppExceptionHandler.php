<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Exception\Handler;

use App\Components\Response;
use App\Components\ResponseContent;
use App\Exception\BusinessException;
use Hyperf\Context\ApplicationContext;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\HttpMessage\Exception\HttpException;
use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Hyperf\Context\Context;


class AppExceptionHandler extends ExceptionHandler
{
    protected $logger;
    /**
     * @var Response
     */
    protected $response;

    public function __construct(ContainerInterface $container, Response $response)
    {
        $this->logger = $container->get(LoggerFactory::class)->get('exception');
        $this->response = $response;
    }

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $this->logger->error(sprintf('报错啦,异常:%s,在文件[%s]的第[%s]行\n[%s]', $throwable->getMessage(), $throwable->getFile(), $throwable->getLine(),$throwable->getTraceAsString()));
        // $this->logger->error($throwable->getTraceAsString());
        $formatter = ApplicationContext::getContainer()->get(FormatterInterface::class);
        // 业务异常类
        if ($throwable instanceof BusinessException) {
            return $this->response->fail($throwable->getCode(), $throwable->getMessage());
        }

        // HttpException
        if ($throwable instanceof HttpException) {
            return $this->response->fail($throwable->getStatusCode(), $throwable->getMessage());
        }
        //自定义标准格式化异常日志
        // $this->logger->error($formatter->format($throwable));
        $requestResponseLog = Context::get('requestResponseLog');
        $responseContent = new ResponseContent($throwable->getCode(), [], $throwable->getMessage());
        $requestResponseLog['response_body']=(string)$responseContent;
        $requestResponseLog['http_code']= $response->getStatusCode();
        $this->logger->info('请求响应日志',$requestResponseLog);
        return $this->response->failContent($responseContent);
        // return $this->response->fail(500, env('APP_ENV') == 'dev' ? $throwable->getMessage() : 'Server Error');
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
