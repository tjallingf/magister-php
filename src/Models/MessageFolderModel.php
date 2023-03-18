<?php
    namespace Tjall\Magister\Models;

    use Tjall\Magister\Models\Model;

    class MessageFolderModel extends Model {
        protected const MAP = [
            'id'          =>['id', Model::TYPE_STRING],
            'rel'         =>['id', Model::REMAP],
            'name'        => 'naam',
            'unreadCount' => 'aantalOngelezen',
            'parentId'    =>['bovenliggendeId', Model::REMAP]
        ];

        protected function remap__rel(int $id): string|int {
            switch($id) {
                case 1: return 'inbox';
                case 2: return 'sent';
                case 3: return 'trash';
                case -1: return 'drafts';
                default: return $id;
            }
        }

        protected function remap__parentId(int $parentId) {
            if($parentId === 0) return null;
            return $parentId;
        }
    }