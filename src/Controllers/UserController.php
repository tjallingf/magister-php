<?php
    namespace Tjall\Magister\Controllers;

    use Tjall\Magister\Controllers\Controller;
    use Tjall\Magister\Database;

    class UserController extends Controller {
        public static function find(string $id = null): array|null {
            $row = Database::queryOne('SELECT * from `magistraal_users` where `id` = ?', [ $id ]);
            if(!isset($row)) return null;
            
            $row['data'] = json_decode($row['data'], true);
            $row['settings'] = json_decode($row['settings'], true);

            return $row;
        }
    }