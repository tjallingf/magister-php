<?php
    namespace Tjall\Magister\Tests\Mocks;

    use Tjall\Magister\Bearer;

    class AuthMock {
        protected static Bearer $bearer;

        static function getLoginCredentials(): array {
            $filepath = dirname(__FILE__, 4).'/tests/credentials.secret.json';
            return json_decode(file_get_contents($filepath), true);
        }

        static function setBearer(Bearer $bearer) {
            static::$bearer = $bearer;
        }

        static function getBearer() {
            return static::$bearer;
        }
    }