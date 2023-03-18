<?php
    namespace Tjall\Magister\Controllers;

    use Tjall\Magister\Token;
    use Tjall\Magister\Lib;
    use Tjall\Magister\Sessions\AppSession;
    use Tjall\Magister\Models\Model;

    abstract class Controller {
        protected array $data = [];
        protected AppSession $session;
        protected $model = Model::class;

        public function __construct(AppSession $session) {
            $this->session = $session;
        }

        public function request(
            string $method, 
            string $url, 
            array $options = [],
            int $flags = self::REQUEST_RETURN_DATA
        ) {
            $url = str_replace('{account_id}', $this->session->bearer->data['accountId'], $url);
            
            $res = $this->session->httpClient->request($method, $url, $options);

            if($flags & self::REQUEST_RETURN_RESPONSE) {
                return $res;
            }

            if($flags & self::REQUEST_RETURN_DATA) {
                $data = Lib::getJson($res);
                return $data;
            }
        }

        public function formatMultiple(array $magister_data): array {
            $data = [];

            foreach ($magister_data as $item) {
                array_push($data, $this->format($item));
            }

            return $data;
        }

        public function format(array $magister_data): array {
            return (new $this->model($magister_data, $this))->toArray();
        }

        public function index() {
            throw new \Exception('Method \''.static::class.'::index\' is not implemented.');

            return static::$data;
        }

        public function store(array $data) {
            static::$data = $data;
        }
        
        public function find(string $id) {
            throw new \Exception('Method \''.static::class.'::find\' is not implemented.');
        }

        public function edit(string $id, $data) {
            throw new \Exception('Method \''.static::class.'::edit\' is not implemented.');
        }

        public function destroy(string $id) {
            throw new \Exception('Method \''.static::class.'::destroy\' is not implemented.');
        }

        const REQUEST_RETURN_DATA = 0x1;
        const REQUEST_RETURN_RESPONSE = 0x2;
    }