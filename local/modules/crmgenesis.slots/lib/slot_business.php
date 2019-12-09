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
            'OWNER_ID' => new \Bitrix\Main\Entity\IntegerField('OWNER_ID', [
                'title' => 'OWNER_ID',
            ]),
            'STATUS_ID' => new \Bitrix\Main\Entity\IntegerField('STATUS_ID', [
//                'required' => true,
                'title' => 'STATUS_ID',
            ]),
            'DATE_FROM' => new \Bitrix\Main\Entity\DatetimeField('DATE_FROM', [
                'required' => true,
                'title' => 'DATE_FROM',
            ]),
            'DATE_TO' => new \Bitrix\Main\Entity\DatetimeField('DATE_TO', [
                'required' => true,
                'title' => 'DATE_TO',
            ]),
            'DATE_CREATE' => new \Bitrix\Main\Entity\DatetimeField('DATE_CREATE', [
                'title' => 'DATE_CREATE',
            ]),
            'DATE_MODIFY' => new \Bitrix\Main\Entity\DatetimeField('DATE_MODIFY', [
                'title' => 'DATE_MODIFY',
            ]),
            'CREATED_BY_ID' => new \Bitrix\Main\Entity\IntegerField('CREATED_BY_ID', [
                'title' => 'CREATED_BY_ID',
            ]),
            'MODIFY_BY_ID' => new \Bitrix\Main\Entity\IntegerField('MODIFY_BY_ID', [
                'title' => 'MODIFY_BY_ID',
            ]),
            'CLIENT_ID' => new \Bitrix\Main\Entity\IntegerField('CLIENT_ID', [
//                'required' => true,
                'title' => 'CLIENT_ID',
            ]),
            'TYPE_ID' => new \Bitrix\Main\Entity\IntegerField('TYPE_ID', [
//                'required' => true,
                'title' => 'TYPE_ID',
            ]),
        ];
    }

}