<?php
    namespace Tjall\Magister\Models;

    use Tjall\Magister\Models\Model;

    class BearerModel extends Model {
        protected const MAP = [
            'expiresAt'    =>['expires_in', Model::CUSTOM],
            'accessToken'  => 'access_token',
            'refreshToken' => 'refresh_token'
        ];
        
        function custom__expiresAt(int $expires_in): int {
            return time() + $expires_in;
        }
    }