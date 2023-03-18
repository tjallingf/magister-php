<?php
    namespace Tjall\Magister\Helpers;

    use Tjall\Magister\Token;
    use Tjall\Magister\Bearer;
    use GuzzleHttp\Client;
    use GuzzleHttp\Cookie\CookieJar;
    use GuzzleHttp\Handler\CurlHandler;
    use Psr\Http\Message\RequestInterface;
    use Psr\Http\Message\ResponseInterface;
    use GuzzleHttp\HandlerStack;
    use GuzzleHttp\Middleware;

    class HttpClient extends Client {
        protected static CookieJar $cookieJar;
        protected static HttpClient $instance;

        public function __construct(array $config = []) {
            if(!isset(static::$cookieJar))
                static::$cookieJar = new CookieJar();

            $handler = new CurlHandler();
            $stack = new HandlerStack($handler);
            $stack->push(Middleware::redirect());
            $stack->push(Middleware::httpErrors());
            $stack->push(Middleware::cookies());
            $stack->push($this->customHandler());

            parent::__construct(array_replace([
                'debug'   => false,
                'handler' => $stack,
                'cookies' => $this::$cookieJar,
                'user_agent' => mt_rand(999, 10000),
                'timeout' => 5
            ], $config));
        }
        
        public static function static(): static {
            if(!isset(static::$instance)) 
                static::$instance = new static();

            return static::$instance;
        }

        protected function customHandler(): callable {
            return function (callable $handler) {
                return function (RequestInterface $req, array $options) use($handler) {
                    $this->authMiddleware($req, $options);

                    $promise = $handler($req, $options);
                    return $promise->then(
                        function (ResponseInterface $res) use($options) {
                            return $res;
                        }
                    );
                };
            };
        }

        protected function authMiddleware(RequestInterface &$req, array $options) {
            $auth = @$options['auth'];
            if(!isset($auth)) 
                return;

            if($auth instanceof Bearer) {
                $req = $req->withHeader('Authorization', 'Bearer '.$auth->getAccessToken());
                return;
            }
                    
            if($auth === 'xsrf') {
                $xsrf_token = self::$cookieJar->getCookieByName('XSRF-TOKEN')?->getValue();

                if(isset($xsrf_token)) {
                    $req = $req->withHeader('x-xsrf-token', $xsrf_token);
                }

                return;
            } 

            trigger_error('Invalid auth: '.$auth::class, E_USER_WARNING);
        }
    }