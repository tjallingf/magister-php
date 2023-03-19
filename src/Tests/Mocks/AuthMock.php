<?php
    namespace Tjall\Magister\Tests\Mocks;

    use Tjall\Magister\Sessions\AppSession;
    use Tjall\Magister\Sessions\LoginSession;

    class AuthMock {
        static ?AppSession $session = null;

        static function getSession(): AppSession {
            if(!isset(static::$session))
                static::login();
                
            return static::$session;
        }

        static function getLoginCredentials(): array {
            $filepath = dirname(__FILE__, 4).'/tests/credentials.secret.json';
            return json_decode(file_get_contents($filepath), true);
        }

        static function login(): void {
            $credentials = static::getLoginCredentials();
            $login_session = new LoginSession();

            $login_session->performChallenge('tenant', $credentials['tenant']);
            $login_session->performChallenge('username', $credentials['username']);
            $login_session->performChallenge('password', $credentials['password']);
            
            static::$session = $login_session->submit();
        }
    }