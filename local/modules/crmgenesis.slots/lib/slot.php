<?php

namespace Crmgenesis\Slots;

use Bitrix\Main\Localization\Loc;
Loc::loadMessages( __FILE__ );

class SlotTable extends \Bitrix\Main\Entity\DataManager{

    public static function getTableName()
    {
        return 'crmgenesis_slot';
    }

    public static function getUfId()
    {
        return "CRMGENESIS_SLOT";
    }

    public static function getMap(){
        return [
                'ID' => new \Bitrix\Main\Entity\IntegerField('ID', [
                    'primary' => true,
                    'autocomplete' => true,
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
                //тип индивидуальная/групповая/сплит
                'TYPE_ID' => new \Bitrix\Main\Entity\IntegerField('TYPE_ID', [
                    'title' => 'TYPE_ID',
                ]),
                //клуб
                'CLUB_ID' => new \Bitrix\Main\Entity\IntegerField('CLUB_ID', [
                    'title' => 'CLUB_ID',
                ]),
                //зона
                'ZONE_ID' => new \Bitrix\Main\Entity\IntegerField('ZONE_ID', [
                    'title' => 'ZONE_ID',
                ]),
                //локация
                'LOCATION_ID' => new \Bitrix\Main\Entity\IntegerField('LOCATION_ID', [
                    'title' => 'LOCATION_ID',
                ]),
                //возраст с по
                'AGE_FROM' => new \Bitrix\Main\Entity\IntegerField('AGE_FROM', [
                    'title' => 'AGE_FROM',
                ]),
                'AGE_TO' => new \Bitrix\Main\Entity\IntegerField('AGE_TO', [
                    'title' => 'AGE_TO',
                ]),
                //численность группы
                'GROUP_SIZE' => new \Bitrix\Main\Entity\IntegerField('GROUP_SIZE', [
                    'title' => 'GROUP_SIZE',
                ]),
                //название группы
                'GROUP_NAME' => new \Bitrix\Main\Entity\StringField('GROUP_NAME', [
                    'title' => 'GROUP_NAME',
                ]),
                'USER_ID' => new \Bitrix\Main\Entity\IntegerField('USER_ID', [
                    'required' => true,
                    'title' => 'USER_ID',
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
                //пока не пригодилось
                'SERVICE_ID' => new \Bitrix\Main\Entity\IntegerField('SERVICE_ID', [
    //                'required' => true,
                    'title' => 'SERVICE_ID',
                ]),

            ];
    }

}