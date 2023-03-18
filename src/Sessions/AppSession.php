<?php
    namespace Tjall\Magister\Sessions;

    use Tjall\Magister\Bearer;
    use Tjall\Magister\Helpers\HttpClient;
    use Tjall\Magister\Models\AccountModel;
    use Tjall\Magister\Lib;

    class AppSession {
        public Bearer $bearer;
        public HttpClient $httpClient;

        public function __construct(Bearer $bearer) {
            $this->bearer = $bearer;

            $this->httpClient = new HttpClient([
                'auth'     => $bearer,
                'base_uri' => $bearer->data['apiUri']
            ]); 
        }
    }