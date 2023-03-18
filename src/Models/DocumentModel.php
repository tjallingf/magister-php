<?php
    namespace Tjall\Magister\Models;

    use Tjall\Magister\Models\Model;
    use Mimey\MimeTypes;

    class DocumentModel extends Model {
        const MAP = [
            'id'            =>['Id', Model::TYPE_NUMBER],
            'type'          =>['BronSoort', Model::REMAP],
            'name'          => 'Naam',
            'size'          =>['Grootte', Model::REMAP],
            'contentType'   =>['ContentType', Model::REMAP],
            'modifiedAt'    =>['GewijzigdOp', Model::TYPE_DATETIME_OR_NULL],
            'createdAt'     =>['GemaaktOp', Model::TYPE_DATETIME_OR_NULL],
            'createdBy'     => 'GeplaatstDoor'
        ];

        function remap__type(int $bron_soort): string {
            switch($bron_soort) {
                case 0: return 'folder';
                case 1: return 'file';
            }
        }

        function remap__size(int $grootte, array $data): int|null {
            if($data['type'] !== 'file') return null;
            return $grootte;
        }

        function remap__contentType(string $content_type, array $data): string|null {
            if($data['type'] !== 'file') return null;
            return $content_type;
        }
    }