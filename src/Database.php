<?php
    namespace Tjall\Magister;

    use mysqli;
    use Exception;
    use Router\Overrides;
    use Router\Response;


    class Database {
        protected static mysqli $mysqli;

        public static function connect(array $credentials) {
            self::$mysqli = new mysqli(
                $credentials['hostname'], 
                $credentials['username'], 
                $credentials['password'], 
                $credentials['database']
            );
        }

        public static function query(string $query, array $params = []): array {
            $stmt = self::$mysqli->prepare($query);
            
            if(count($params)) {
                $param_chars = '';
                foreach ($params as $param) {
                    $param_chars .= self::getParamChar($param);
                }

                $stmt->bind_param($param_chars, ...$params);
            }

            if(!$stmt->execute())
                throw new Exception('Failed to execute statement.');

            $result = $stmt->get_result();
            if(!$result) return [];

            return $result->fetch_all(MYSQLI_ASSOC);
        }

        public static function queryOne(string $query, array $params = []): array|null {
            return @self::query($query, $params)[0];
        }

        public static function getParamChar(&$param): string {
            if(is_float($param))  return 'd';
            if(is_int($param))    return 'i';

            // Force param to be an empty string if
            // it is not of a valid type.
            if(!is_string($param)) $param = '';
            return 's';
        }
    }