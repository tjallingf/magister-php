<?php
    namespace Tjall\Magister\Controllers;

    use Tjall\Magister\Controllers\Controller;
    use Tjall\Magister\Models\GradeModel;

    class GradeController extends Controller {
        protected $model = GradeModel::class;

        public function index(?int $top = 500, ?int $skip = 0) {
            $magister_data = $this->request('get', 'personen/{account_id}/cijfers/laatste', [
                'query' => [
                    'top' => $top,
                    'skip' => $skip
                ]
            ]);

            return $this->formatMultiple($magister_data['items']);
        }
        public function find(string $id) {
            $magister_data = $this->request('get', 'personen/{account_id}/afspraken/'.$id);
            return $this->format($magister_data);
        }
    }