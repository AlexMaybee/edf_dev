<?php

namespace Crmgenesis\Slots;

use Bitrix\Main\Localization\Loc;
Loc::loadMessages( __FILE__ );

class Slot_businessTable extends \Bitrix\Main\Entity\DataManager{

    public static function getTableName()
    {
        return 'crmgenesis_slot_business';
    }

    public static function getUfId()
    {
        return "CRMGENESIS_SLOT_BUSINESS";
    }

    public static function getMap(){
        return [
            'ID' => new \Bitrix\Main\Entity\IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),
            'CLIENT_ID' => new \Bitrix\Main\Entity\IntegerField('CLIENT_ID', [
                'required' => true,
                'title' => 'CLIENT_ID',
            ]),
            'TYPE_ID' => new \Bitrix\Main\Entity\IntegerField('TYPE_ID', [
                'required' => true,
                'title' => 'TYPE_ID',
            ]),
            'STATUS' => new \Bitrix\Main\Entity\IntegerField('STATUS', [
                'required' => true,
                'title' => 'STATUS',
            ]),
        ];
    }

}