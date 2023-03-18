<?php
    namespace Tjall\Magister\Sessions;

    use Tjall\Magister\Helpers\HttpClient;
    use Tjall\Magister\Lib;
    use Tjall\Magister\Token;
    use Tjall\Magister\Bearer;
    use GuzzleHttp\Exception\RequestException;
    use GuzzleHttp\RedirectMiddleware;
    use Psr\Http\Message\ResponseInterface;
    use Exception;

    class LoginSession {
        protected string $authCode;
        protected string $loginUrl;
        protected string $authorizeUrl;
        protected string $codeChallenge;
        protected string $codeVerifier;
        protected string $sessionId;
        protected string $returnUrl;
        protected HttpClient $httpClient;

        public function __construct() {
            $this->httpClient = new HttpClient([
                'base_uri' => Lib::MAGISTER_ACCOUNTS_URI,
                'auth' => 'xsrf'
            ]);
            $this->codeVerifier  = $this->generateCodeVerifier();
            $this->codeChallenge = $this->generateCodeChallenge();
            $this->authorizeUrl  = $this->generateAuthorizeUrl();

            $this->prepare();
        }

        public function submit(): Bearer {
            try {
                $res = $this->httpClient->get($this->returnUrl, [
                    'allow_redirects' => false
                ]);

                $redirect_callback_url = $res->getHeaderLine('Location');

                if(!isset($redirect_callback_url))
                    throw new Exception('Failed to get redirect callback url.');

                parse_str(parse_url($redirect_callback_url, PHP_URL_FRAGMENT), $url_fragment);
                $openid_code = @$url_fragment['code'];

                if(!is_string($openid_code))
                    throw new Exception('Failed to get open id code.');

                $bearer = Bearer::fromOpenidCode($openid_code, $this->codeVerifier);
                
                return $bearer;
            } catch(RequestException $e) {
                throw $e;
            }
        }

        public function performChallenge(string $challenge, string $challenge_value): bool {
            if($challenge != 'tenant' && $challenge != 'username' && $challenge != 'password')
                throw new Exception("Invalid challenge: '$challenge'.");

            try {
                $this->httpClient->post('challenges/'.$challenge, [
                    'json' => [
                        $challenge  => $challenge_value,
                        'authCode'  => $this->authCode,
                        'returnUrl' => $this->returnUrl,
                        'sessionId' => $this->sessionId
                    ]
                ]);

                return true;
            } catch(RequestException $e) {
                if(!$e->hasResponse()) 
                    throw new Exception("Failed to perform challenge '$challenge'.");
                
                if($e->getResponse()->getStatusCode() == '400')
                    return false;

                throw $e;
            }
        }

        protected function prepare(): void {
            // Request the login page
            $res = $this->httpClient->get($this->authorizeUrl, [
                'allow_redirects' => [ 'track_redirects' => true ]
            ]);
            
            $history = $res->getHeader(RedirectMiddleware::HISTORY_HEADER);

            $login_url = @$history[1];

            if(!str_starts_with($login_url, Lib::MAGISTER_ACCOUNTS_URI.'/account/login'))
                throw new Exception('Failed to get login url.');

            // Remember loginUrl
            $this->loginUrl = $login_url;

            // Get login page url query
            $login_url_query = Lib::parseUrlQuery($login_url);

            // Remember sessionId and returnUrl
            $this->sessionId = $login_url_query['sessionId'];
            $this->returnUrl = $login_url_query['returnUrl'];

            // Get auth code from login page body
            $this->authCode = $this->getAuthCode($res->getBody());
        }

        protected function generateAuthorizeUrl() {
            $state = $this->generateState();
            $nonce = $this->generateNonce();

            return 'connect/authorize?client_id=M6LOAPP'.
                   '&redirect_uri=m6loapp%3A%2F%2Foauth2redirect%2F&scope=openid%20profile%20offline_access%20magister.mobile%20magister.ecs&response_type=code%20id_token'.
                   "&state={$state}&nonce={$nonce}&code_challenge={$this->codeChallenge}&code_challenge_method=S256";
        }

        protected function generateNonce() {
            return Lib::generateRandomHex(32);
        }

        protected function generateState() {
            return Lib::generateRandomHex(32);
        }

        protected function generateCodeVerifier() {
            return Lib::generateRandomString(128);
        }

        protected function generateCodeChallenge() {
            return Lib::base64UrlEncode(pack('H*', hash('sha256', $this->codeVerifier)));
        }

        protected function getAuthCode(string $login_page_body) {
            // Get the url of the /js/account-*.js script, in which the auth code is hidden.
            preg_match('/(?<=src=")js\/account-[^"]+/', $login_page_body, $account_script_src_matches);
            
            if(!count($account_script_src_matches))
                throw new Exception('Failed to get account script source.');

            $account_script_src = $account_script_src_matches[0];
            
            // TODO: implement authCode storage
            // // Read the last known authcode from storage, and return the stored auth code
            // // if the script url is the same. The script url is different with a new auth code.
            // $latest_auth_code = Storage::get('latest_auth_code');
            // if(@$latest_auth_code['accountScriptSrc'] === $account_script_src)
            //     return @$latest_auth_code['authCode'];

            // Get the contents of the /js/account-*.js script if 
            // the last known auth code is no longer usable.
            $account_script_contents = $this->httpClient->get($account_script_src)->getBody();
            
            preg_match('/this\.session\.getSessionData().*\.map/', $account_script_contents, $matches);

            // Ends with '(o=["2cfe","09a41628","30b44a","a9856c"],["1","1"].map'
            $match = $matches[0];

            $start = strpos($match, '["');

            // Equals to '["2cfe","09a41628","30b44a","a9856c"],["1","1"]'
            $arrays_string = substr($match, $start, strlen($match) - $start - 4);
            
            list($auth_code_array, $indexes_array) = json_decode('['.$arrays_string.']', true);
            if(!is_array($auth_code_array) || !is_array($indexes_array)) {
                throw new Exception('Failed to get auth code.');
            }
            
            // Create the auth code string
            $auth_code = @$auth_code_array[$indexes_array[0]].@$auth_code_array[$indexes_array[1]];
            
            // TODO: implement authCode storage
            // // Store the new auth code for later use
            // Storage::set('latest_auth_code', [
            //     'accountScriptSrc' => $account_script_src,
            //     'authCode' => $auth_code
            // ]);

            return $auth_code;
        }
    }