<?php
    namespace Tjall\Magister\Controllers;

    use Tjall\Magister\Controllers\Controller;
    use Tjall\Magister\Models\MessageModel;

    class MessageController extends Controller {
        protected $model = MessageModel::class;

        public function index(?int $top = 500, ?int $skip = 0, string $folder_id = 'inbox'): array {
            $href = self::getHref($folder_id);
            
            $magister_data = $this->request('get', $href, [
                'query' => [
                    'top' => $top,
                    'skip' => $skip
                ]
            ]);

            return self::formatMultiple($magister_data['items']);
        }

        public function find(string $message_id) {
            $magister_data = $this->request('get', "/api/berichten/berichten/$message_id");
            return self::format($magister_data);
        }

        public function updateIsRead(string $message_id, bool $is_read = true): void {
            $this->request('patch', '/api/berichten/berichten', [
                'json' => [
                    'berichten' => [
                        [
                            'berichtId' => $message_id,
                            'operations' => [
                                [
                                    'op' => 'replace',
                                    'path' => '/isGelezen',
                                    'value' => $is_read
                                ]
                            ]
                        ]
                    ]
                ]
            ]);
        }

        public function findAttachmentLocation(string $attachment_id): string {
            $uri = '/api/berichten/bijlagen/'.$attachment_id.'/download';
            $data = $this->request('get', $uri, [
                'query' => [ 'redirect_type' => 'body' ]
            ]);
            return $data['location'];
        }

        public function getHref(string $folder_id): string {
            switch($folder_id) {
                case 'drafts':
                    return 'berichten/concepten';
                case 'inbox':
                    return 'berichten/postvakin/berichten';
                case 'sent':
                    return 'berichten/verzondenitems/berichten';
                case 'trash':
                    return 'berichten/verwijderdeitems/berichten';    
                default:
                    return "berichten/mappen/{$folder_id}/berichten";
            }
        }
    }