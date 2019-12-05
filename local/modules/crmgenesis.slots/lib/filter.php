<?php

namespace Crmgenesis\Slots;

use \Crmgenesis\Slots\Bitrixfunction,
    \Bitrix\Main\Localization\Loc;

class Filter{

    /*@method: получение ID и роли текущего пользователя + берем lang-строки для счетчиков
     * @return: array*/
    public function getUserRoleAndId(){
        $result = [
            'isAdmin' => false,
            'seletedUserId' => false,
            'error' => false,
            'lang' => [
                'weekText' => Loc::getMessage('CRM_GENESIS_SLOTS_COUNTER_COMPONENT_WEEK_HOURS_TEXT'),
                'monthText' => Loc::getMessage('CRM_GENESIS_SLOTS_COUNTER_COMPONENT_MONTH_HOURS_TEXT'),
                'measureText' => Loc::getMessage('CRM_GENESIS_SLOTS_COUNTER_COMPONENT_MEASURE_TEXT'),
            ],
        ];

        $result['seletedUserId'] = Bitrixfunction::returnCurUserId();
        $result['isAdmin'] = Bitrixfunction::checkUserIfAdmin();

        Bitrixfunction::sentAnswer($result);
    }

    /*@method: получение значений фильтров для отображения в полях/селектов
    * @return: array*/
    public function getDataForFilters(){
        $result = [
            'userList' => [],
            'error' => false,
        ];

        //usersList для фильтра
       $usersList = self::getUsersListForFilter();
       ($usersList['result'])
           ? $result['userList'] = $usersList['result']
           : $result['error'] = $usersList['error'];

        Bitrixfunction::sentAnswer($result);
    }

    /*@method: получение значений селектов для полей GSP-modal
     @ return: array*/
    public function getGspModalSelectFields(){
        $result = [
            'typeList' => [],
            'clubList' => [],
            'zonaList' => [],
            'locationList' => [],
            'statusList' => [],
            'serviceList' => [],
            'table' => [
                'ths' => [],
                'tds' => [],
            ],
            'errors' => [],
        ];

        //получения массива дней и часов для чекбоксов
        $result['table']['ths'] = [['NAME' => Loc::getMessage('CRM_GENESIS_SLOTS_THS_START_TEXT'), 'ID' => 0]];

        //вывод всех дней недели в фильтр битрикс
        $i = 0;
        $strtDay = strtotime('last monday');
        while($i < 7){
            array_push($result['table']['ths'],
                ['NAME' => FormatDate('D',strtotime(date('d.m.Y',$strtDay).'+'.$i.' day')),'ID' => $i+1]);
            $i++;
        }

        $strtTime = 7;
        while($strtTime < 23){
            $stringArr = [
                [
                'TIME' => date('H:i',
                    mktime($strtTime, 0, 0, getdate()['mon'], getdate()['mday'], getdate()['year'])),
                'ID' => $strtTime,
                ],
            ];
            $strtDay = 1;
            while($strtDay <= 7){
                array_push($stringArr,['TIME' => $strtTime, 'DAY' => $strtDay]);
                $strtDay++;
            }
            array_push($result['table']['tds'],$stringArr);
            $strtTime++;
        }


        //спискм значений для select gsp-popup
        $typeListId = Bitrixfunction::getCoptionValue('SLOT_TYPE_LIST');
        if(!$typeListId) $result['errors'][] = Loc::getMessage('CRM_GENESIS_C_OPTION_GET_SLOT_TYPE_LIST_ID_ERROR');
        else {
            $typeList = Bitrixfunction::getListElements(
                ['IBLOCK_ID' => $typeListId], ['ID', 'NAME'], ['DATE_CREATE' => 'DESC']
            );
            (!$typeList)
                ? $result['errors'][] = Loc::getMessage('CRM_GENESIS_C_OPTION_GET_SLOT_TYPE_LIST_RESULT_ERROR')
                : $result['typeList'] = $typeList;
        }

        $clubListId = Bitrixfunction::getCoptionValue('SLOT_CLUB_LIST');
        if(!$clubListId) $result['errors'][] = Loc::getMessage('CRM_GENESIS_C_OPTION_GET_SLOT_CLUB_LIST_ID_ERROR');
        else {
            $clubList = Bitrixfunction::getListElements(
                ['IBLOCK_ID' => $clubListId], ['ID', 'NAME'], ['DATE_CREATE' => 'DESC']
            );
            (!$clubList)
                ? $result['errors'][] = Loc::getMessage('CRM_GENESIS_C_OPTION_GET_SLOT_CLUB_LIST_RESULT_ERROR')
                : $result['clubList'] = $clubList;
        }

        $zonaListId = Bitrixfunction::getCoptionValue('SLOT_ZONA_LIST');
        if(!$zonaListId) $result['errors'][] = Loc::getMessage('CRM_GENESIS_C_OPTION_GET_SLOT_ZONA_LIST_ID_ERROR');
        else{
//            $zonaList = Bitrixfunction::getListElementsWithProperties( ['IBLOCK_ID' => $zonaListId],['ID','NAME'],['DATE_CREATE' => 'DESC']

            //!!! ВКЛЮЧЕН ДРЕВНИЙ МЕТОД!!!
            $zonaList = Bitrixfunction::getListElemsOld(
                ['IBLOCK_ID' => $zonaListId],['ID','NAME','PROPERTY_307'],['DATE_CREATE' => 'DESC']
            );
            (!$zonaListId)
                ? $result['errors'][] = Loc::getMessage('CRM_GENESIS_C_OPTION_GET_SLOT_ZONA_LIST_RESULT_ERROR')
                : $result['zonaList'] = $zonaList;
        }

        $locationListId = Bitrixfunction::getCoptionValue('SLOT_LOCATION_LIST');
        if(!$locationListId) $result['errors'][] = Loc::getMessage('CRM_GENESIS_C_OPTION_GET_LOCATION_LIST_ID_ERROR');
        else{
//            $locationList = Bitrixfunction::getListElements(
//                ['IBLOCK_ID' => $locationListId],['ID','NAME'],['DATE_CREATE' => 'DESC']

            //!!! ВКЛЮЧЕН ДРЕВНИЙ МЕТОД!!!
            $locationList = Bitrixfunction::getListElemsOld(
                ['IBLOCK_ID' => $locationListId],['ID','NAME','PROPERTY_308'],['DATE_CREATE' => 'DESC']
            );
            (!$locationList)
                ? $result['errors'][] = Loc::getMessage('CRM_GENESIS_C_OPTION_GET_LOCATION_LIST_RESULT_ERROR')
                : $result['locationList'] = $locationList;
        }

        $statusListId = Bitrixfunction::getCoptionValue('SLOT_STATUS_LIST');
        if(!$statusListId) $result['errors'][] = Loc::getMessage('CRM_GENESIS_C_OPTION_GET_STATUS_LIST_ID_ERROR');
        else{
            $statuseList = Bitrixfunction::getListElements(
                ['IBLOCK_ID' => $statusListId],['ID','NAME'],['DATE_CREATE' => 'DESC']
            );
            (!$statuseList)
                ? $result['errors'][] = Loc::getMessage('CRM_GENESIS_C_OPTION_GET_STATUS_LIST_RESULT_ERROR')
                : $result['statusList'] = $statuseList;
        }
        $serviceListId = Bitrixfunction::getCoptionValue('SLOT_SERVISE_LIST');
        if(!$serviceListId) $result['errors'][] = Loc::getMessage('CRM_GENESIS_C_OPTION_GET_SERVICE_LIST_ID_ERROR');
        else{
            $serviceList = Bitrixfunction::getListElements(
                ['IBLOCK_ID' => $serviceListId],['ID','NAME'],['DATE_CREATE' => 'DESC']
            );
            (!$serviceList)
                ? $result['errors'][] = Loc::getMessage('CRM_GENESIS_C_OPTION_GET_SERVICE_LIST_RESULT_ERROR')
                : $result['serviceList'] = $serviceList;
        }







        Bitrixfunction::sentAnswer($result);
    }


    /*@method: получение значений для фильтра пользователей
    * @return: array*/
    private function getUsersListForFilter(){
        $result = [
            'error' => false,
            'result' => [],
        ];

        $userList = Bitrixfunction::getUsersByFilter(['ACTIVE' => 'Y'],['ID','LAST_NAME','NAME']);
        if(!$userList) $result['error'] = Loc::getMessage('CRM_GENESIS_USERS_FOR_FILTER_GET_ERROR');
        else{
            $result['result'][] = ['ID' => '', 'NAME' => Loc::getMessage('CRM_GENESIS_NOT_SELECTED')];
            foreach ($userList as $user)
                $result['result'][] = ['ID' => $user['ID'], 'NAME' => $user['LAST_NAME'].' '.$user['NAME']];
        }

        return $result;
    }

}