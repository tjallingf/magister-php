<?php
    namespace Tjall\Magister\Models;

    use Tjall\Magister\Models\Model;
    use Tjall\Magister\Controllers\MessageController;
    use Router\Router;

    class MessageModel extends Model {
        protected ?string $meetingUrl = null;

        protected const MAP = [
            'id'             =>['id', Model::TYPE_STRING],
            'subject'        => 'onderwerp',
            'sender'         =>['afzender', Model::REMAP],
            'content'        =>['inhoud', Model::REMAP],
            'hasPriority'    => ['heeftPrioriteit', Model::TYPE_BOOL],
            'hasAttachments' =>['heeftBijlagen', Model::TYPE_BOOL],
            'isRead'         =>['isGelezen', Model::TYPE_BOOL],
            'sentAt'         =>['verzondenOp', Model::TYPE_DATETIME_OR_NULL],
            'forwardedAt'    =>['doorgestuurdOp', Model::TYPE_DATETIME_OR_NULL],
            'repliedAt'      =>['beantwoordOp', Model::TYPE_DATETIME_OR_NULL],
            'recipients'     =>['ontvangers', Model::REMAP],
            'attachments'    =>['heeftBijlagen', Model::REMAP]
        ];

        protected function remap__attachments(bool $heeftBijlagen, array $data, array $magister_data): array|null {
            if(!$heeftBijlagen || $data['content'] === false) return null;

            $bijlagen = $this->controller->request('get', $magister_data['links']['bijlagen']['href'])['items'];

            return array_map(function ($bijlage) {
                return [
                    'id'          => strval($bijlage['id']),
                    'name'        => $bijlage['naam'],
                    'contentType' => $bijlage['contentType'],
                    'size'        => $bijlage['grootte'],
                ];
            }, $bijlagen);
        }

        protected function remap__content($inhoud): string|null {
            if(!isset($inhoud)) return null;
            return $inhoud;
        }

        protected function remap__recipients(?array $ontvangers): array|null {
            if(!$ontvangers) return null;

            return array_map(function($ontvanger) {
                list($name, $class) = self::seperateNaam($ontvanger['weergavenaam']);
                return [
                    'id'     => strval($ontvanger['id']),
                    'name'   => $name,
                    'suffix' => $class
                ];
            }, $ontvangers);
        }

        protected function remap__sender(?array $afzender): array|null {
            if(!$afzender) return null;

            list($name, $suffix) = self::seperateNaam($afzender['naam']);
            return [
                'id'     => strval($afzender['id']),
                'name'   => $name,
                'suffix' => $suffix
            ];
        }

        protected function seperateNaam(string $naam) {
            $suffix_strpos = strpos($naam, '(');
            if($suffix_strpos === false) return [ $naam, null ];

            return [
                trim(substr($naam, 0, $suffix_strpos)),
                trim(substr($naam, $suffix_strpos), '() ')
            ];
        }
    }