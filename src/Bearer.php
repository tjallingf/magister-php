<?php
    namespace Tjall\Magister;

    use Tjall\Magister\Helpers\HttpClient;
    use Tjall\Magister\Models\AccountModel;
    use Tjall\Magister\Models\BearerModel;
    use Tjall\Magister\Lib;

    class Bearer {
        public array $data;
        public $refreshCallback;

        public function __construct(array $data) {
            $this->data = $data;
        }
        
        public static function fromOpenidCode(string $openid_code, string $code_verifier): static { 
            $res = HttpClient::static()->post('connect/token', [
                'base_uri' => Lib::MAGISTER_ACCOUNTS_URI,
                'headers' => [
                    'x-api-client-id' => self::HEADER_X_API_CLIENT_ID
                ],
                'form_params' => [
                    'client_id'     => self::CLIENT_ID,
                    'grant_type'    => 'authorization_code',
                    'redirect_uri'  => 'm6loapp://oauth2redirect/',
                    'code'          => $openid_code,
                    'code_verifier' => $code_verifier
                ]
            ]);

            $magister_data = Lib::getJson($res);
            $bearer = new static(BearerModel::fromMagister($magister_data)->toArray());

            $bearer->updateApiUri();
            $bearer->updateAccountId();

            return $bearer;
        }

        public static function fromArray(array $data): static {
            return new static($data);
        }

        public function toArray() {
            return $this->data;
        }

        public function expiresAt() {
            return $this->data['expiresAt'];
        }

        public function expiresSoon(): bool {
            return ($this->data['expiresAt'] - time() < self::EXPIRES_SOON_THRESHOLD);
        }

        public function expired(): bool {
            return ($this->data['expiresAt'] - time() < self::EXPIRED_THRESHOLD);
        }
        
        public function refresh(): void {
            $res = HttpClient::static()->post('connect/token', [
                'base_uri' => Lib::MAGISTER_ACCOUNTS_URI,
                'headers' => [
                    'x-api-client-id' => self::HEADER_X_API_CLIENT_ID
                ],
                'form_params' => [
                    'refresh_token' => $this->data['refreshToken'],
                    'client_id'     => self::CLIENT_ID,
                    'grant_type'    => 'refresh_token'
                ]
            ]);

            $magister_data = Lib::getJson($res);
            $this->data = BearerModel::fromMagister($magister_data)->toArray();

            if(is_callable($this->refreshCallback)) {
                call_user_func($this->refreshCallback, $this);
            }
        }

        public function getAccessToken(bool $auto_refresh = true): string {
            if($auto_refresh && $this->expiresSoon()) {
                $this->refresh();
            }

            return $this->data['accessToken'];
        }

        public function updateApiUri(): void {
            $res = HttpClient::static()->get(
                'https://magister.net/.well-known/host-meta.json', [ 
                    'auth' => $this 
                ]);
            $magister_data = Lib::getJson($res);

            if(!isset($magister_data['links']) || empty($magister_data['links']))
                throw new \Exception('Failed to get links');

            $api_uri = Lib::arrayGetWhere($magister_data['links'], 'rel', 'magister-api', 'href');

            if(!is_string($api_uri))
                throw new \Exception('Failed to get api uri');

            $this->data['apiUri'] = rtrim($api_uri, '/').'/';
        }

        public function updateAccountId(): void {
            $res = HttpClient::static()->get(
                'account', [ 
                    'base_uri' => $this->data['apiUri'], 
                    'auth' => $this 
                ]);
            $magister_data = Lib::getJson($res);

            $this->data['accountId'] = AccountModel::fromMagister($magister_data)->get('id');
        }

        const HEADER_X_API_CLIENT_ID = 'EF15';
        const CLIENT_ID              = 'M6LOAPP';
        const EXPIRED_THRESHOLD      = 2 * 60;
        const EXPIRES_SOON_THRESHOLD = self::EXPIRED_THRESHOLD + 10;
    }