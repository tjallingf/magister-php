<?php
    namespace Tjall\Magister\Models;

    use Tjall\Magister\Models\Model;

    class GradeModel extends Model {
        protected const MAP = [
            'id'           =>['kolomId', Model::TYPE_STRING],
            'description'  => 'omschrijving',
            'enteredAt'    =>['ingevoerdOp', Model::TYPE_DATETIME],
            'value'        =>['waarde', Model::REMAP],
            'valueString'  => 'waarde',
            'isSufficient' =>['isVoldoende', Model::TYPE_BOOL],
            'weight'       => 'weegfactor',
            'doesCount'    =>['teltMee', Model::TYPE_BOOL],
            'mustMakeUp'   =>['moetInhalen', Model::TYPE_BOOL],
            'hasExemption' =>['heeftVrijstelling', Model::TYPE_BOOL],
            'discipline'   =>['vak', Model::REMAP]
        ];

        protected function remap__value(string $waarde): float|null {
            $is_numeric = (strpos($waarde, ',') > 0);
            if($is_numeric) return floatval(str_replace(',', '.', $waarde));

            return null;
        }

        protected function remap__discipline(array $vak): array {
            return [
                'code' => $vak['code'],
                'name' => $vak['omschrijving']
            ];
        }
    }