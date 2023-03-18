<?php
    namespace Tjall\Magister\Controllers;

    use Tjall\Magister\Controllers\Controller;
    use Tjall\Magister\Models\AppointmentModel;

    class AppointmentController extends Controller {
        protected $model = AppointmentModel::class;

        public function index(int $from = null, int $until = null): array {
            $from_ymd  = date('Y-m-d', $from);
            $until_ymd = date('Y-m-d', $until);

            $magister_data = $this->request('get', 'personen/{account_id}/afspraken', [
                'query' => [
                    'van' => $from_ymd,
                    'tot' => $until_ymd
                ]
            ]);

            return self::formatMultiple($magister_data['Items']);
        }

        public function find(string $id) {
            $magister_data = $this->request('get', 'personen/{account_id}/afspraken/'.$id);
            return self::format($magister_data);
        }

        public function findAttachmentLocation(string $attachment_id) {
            $uri = 'personen/{account_id}/afspraken/bijlagen/'.$attachment_id;
            $data = $this->request('get', $uri, [
                'query' => [ 'redirect_type' => 'body' ]
            ]);
            return $data['location'];
        }

        public function groupByDay(array $appointments, int $from, int $until): array {
            $groups = [];

            for ($time=$from; $time <= $until; $time=$time+24*60*60) { 
                $key = date('Y-m-d', $time);
                $groups[$key] = [
                    'date' => date('c', $time), 
                    'appointments' => []
                ];
            }
            
            foreach ($appointments as $appointment) {
                $key = date('Y-m-d', strtotime($appointment['startsAt']));
                if(!isset($groups[$key])) continue;
                array_push($groups[$key]['appointments'], $appointment);
            }

            return array_values($groups);
        }
    }