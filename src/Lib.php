<?php
    namespace Tjall\Magister;
    
    phpinfo();

    use Psr\Http\Message\ResponseInterface;

    class Lib {
        const HOST_MAGISTER = 'https://magister.net/';
        const HOST_MAGISTER_ACCOUNTS = 'https://accounts.magister.net';

        public static function randomHex(int $length = 32): string {
            return bin2hex(openssl_random_pseudo_bytes($length / 2));
        }

        public static function randomString(int $length = 32, string $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'): string {
            return substr(str_shuffle(str_repeat($chars, $length)), 0, $length);
        }

        public static function base64UrlEncode(string $input): string {
            return str_replace(['=', '+', '/'], ['', '-', '_'], base64_encode($input));
        }

        public static function base64UrlDecode(string $input): string {
            return base64_decode(str_replace(['-', '_'], ['+', '/'], $input));
        }

        public static function parseUrlQuery(string $url): array {
            parse_str(parse_url($url, PHP_URL_QUERY), $url_query);
            return $url_query;
        }

        public static function arrayGetWhere(array $array, string $sibling_key, $sibling_value, ?string $return_key = null) {
            return array_column($array, $return_key, $sibling_key)[$sibling_value];
        }

        public static function arrayGet(array $arr, string $path) {
            if(strpos($path, '.') === false) {
                return @$arr[$path];
            }

            $path_exploded = explode('.', $path);

            $value = $arr;

            foreach ($path_exploded as $key) {
                $value = @$value[$key];
            }

            return $value;
        }

        public static function arraySet(array &$arr, string $path, $data): void {
            if(strpos($path, '.') === false) {
                $arr[$path] = $data;
                return;
            }

            $path_exploded = explode('.', $path);

            $current = &$arr;
            foreach($path_exploded as $key) {
                $current = &$current[$key];
            }

            $current = $data;
        }

        public static function getJson(ResponseInterface $res): mixed {
            return json_decode((string) $res->getBody(), true);
        }
    }