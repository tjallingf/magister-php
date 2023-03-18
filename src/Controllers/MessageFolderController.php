<?php
    namespace Tjall\Magister\Controllers;

    use Tjall\Magister\Controllers\Controller;
    use Tjall\Magister\Models\MessageFolderModel;

    class MessageFolderController extends Controller {
        protected $model = MessageFolderModel::class;

        public function index() {
            $magister_data = $this->request('get', 'berichten/mappen/alle');
            
            array_push($magister_data['items'], [
                'id' => -1,
                'bovenliggendeId' => 0,
                'aantalOngelezen' => 0,
                'naam' => 'Concepten',
                'links' => [
                    'self' => [
                        'href' => '/api/berichten/concepten'
                    ]
                ]
            ]);

            return $this->formatMultiple($magister_data['items']);
        }
    }