<?php
    namespace Tjall\Magister\Controllers;

    use Tjall\Magister\Controllers\Controller;
    use Tjall\Magister\Models\DocumentModel;

    class DocumentController extends Controller {
        protected $model = DocumentModel::class;

        public function index(?int $parent_id = null) {
            $magister_data = $this->request('get', 'personen/{account_id}/bronnen', [
                'query' => [
                    'parentId' => $parent_id
                ]
            ]);

            return $this->formatMultiple($magister_data['Items']);
        }

        public function findLocation(string $id) {
            $uri = 'personen/{account_id}/bronnen/'.$id.'/content';
            $data = $this->request('get', $uri, [
                'query' => [ 'redirect_type' => 'body' ]
            ]);
            return $data['location'];
        }
    }