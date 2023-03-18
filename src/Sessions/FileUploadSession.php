<?php
    namespace Tjall\Magister\Sessions;

    use Tjall\Magister\Sessions\UserSession;
    use Tjall\Magister\Lib;

    class MagisterFileUpload {
        protected string $filename;
        protected string $tmpName;
        
        public function __construct(array $file) {
            $this->filename = $file['name'];
            $this->tmpName = $file['tmp_name'];
        }

        public function upload() {
            $res = UserSession::$httpClient->post('bestanden', [
                'multipart' => [
                    [
                        'name' => pathinfo($this->tmpName, PATHINFO_FILENAME),
                        'filename' => $this->filename,
                        'contents' => fopen($this->tmpName, 'r')
                    ]
                ]
            ]);
            $data = Lib::getJson($res);

            return [
                'id' => $data[0]['id'],
                'filename' => $data[0]['naam']
            ];
        }
    }