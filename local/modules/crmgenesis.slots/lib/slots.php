<?php

namespace Crmgenesis\Slots;

use Bitrix\Main\Localization\Loc;
Loc::loadMessages( __FILE__ );

class SlotsTable extends \Bitrix\Main\Entity\DataManager{

    public static function getTableName()
    {
        return 'crmgenesis_slots';
    }

    public static function getUfId()
    {
        return "CRMGENESIS_SLOTS";
    }

    public static function getMap(){
        return [
            'ID' => new \Bitrix\Main\Entity\IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),
            'DATE_FROM' => new \Bitrix\Main\Entity\DatetimeField('DATE_FROM', [
                'required' => true,
                'title' => 'DATE_FROM',
            ]),
            'DATE_TO' => new \Bitrix\Main\Entity\DatetimeField('DATE_TO', [
                'required' => true,
                'title' => 'DATE_TO',
            ]),
            'STATUS_ID' => new \Bitrix\Main\Entity\IntegerField('STATUS_ID', [
//                'required' => true,
                'title' => 'STATUS_ID',
            ]),
            'SERVICE_ID' => new \Bitrix\Main\Entity\IntegerField('SERVICE_ID', [
//                'required' => true,
                'title' => 'SERVICE_ID',
            ]),
            'LOCATION_ID' => new \Bitrix\Main\Entity\IntegerField('LOCATION_ID', [
//                'required' => true,
                'title' => 'LOCATION_ID',
            ]),
            'USER_ID' => new \Bitrix\Main\Entity\IntegerField('USER_ID', [
                'required' => true,
                'title' => 'USER_ID',
            ]),
            'AGE_FROM' => new \Bitrix\Main\Entity\IntegerField('AGE_FROM', [
                'title' => 'AGE_FROM',
            ]),
            'AGE_TO' => new \Bitrix\Main\Entity\IntegerField('AGE_TO', [
                'title' => 'AGE_TO',
            ]),

            ];
    }

}