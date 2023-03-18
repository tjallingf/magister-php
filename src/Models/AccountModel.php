<?php
    namespace Tjall\Magister\Models;

    use Tjall\Magister\Models\Model;

    class AccountModel extends Model {
        protected const MAP = [
            'uuid'               => 'UuId',
            'id'                 =>['Persoon.Id', Model::TYPE_STRING],

            'firstName'          => 'Persoon.Roepnaam',
            'infix'              => 'Persoon.Tussenvoegsel',
            'lastName'           => 'Persoon.Achternaam',
            'initials'           => 'Persoon.Voorletters',

            'officialFirstNames' => 'Persoon.OfficieleVoornamen',
            'officialInfixes'    => 'Persoon.OfficieleTussenvoegsels',
            'officialLastName'   => 'Persoon.OfficieleAchternaam',

            'dateOfBirth'        =>['Persoon.Geboortedatum', Model::TYPE_DATE]
        ];
    }