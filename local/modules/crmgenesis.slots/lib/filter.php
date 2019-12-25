<?php

namespace Crmgenesis\Slots;

use \Crmgenesis\Slots\Bitrixfunction,
    \Bitrix\Main\Localization\Loc;

class Filter{

    const HOLE_ADMINS_COPTION_TEXT = 'HOLE_ADMINISTRATOR_GROUP';
    const PRODUCT_CATALOG_ID = 27;
    const PRODUCTS_SECTION_ID = 195;

    /*@method: получение ID и роли текущего пользователя + берем lang-строки для счетчиков
     * @return: array*/
    public function getUserRoleAndId(){
        $result = [
            'isAdmin' => false,
            'selectedUser' => [],
            'error' => false,
            'lang' => [
                'weekText' => Loc::getMessage('CRM_GENESIS_SLOTS_COUNTER_COMPONENT_WEEK_HOURS_TEXT'),
                'monthText' => Loc::getMessage('CRM_GENESIS_SLOTS_COUNTER_COMPONENT_MONTH_HOURS_TEXT'),
                'measureText' => Loc::getMessage('CRM_GENESIS_SLOTS_COUNTER_COMPONENT_MEASURE_TEXT'),
            ],
        ];

        //получаем ID группы админов залов, т.к. они тоже могут редактировать чужие раписания
        $userGroupsIds = [];
        $holeAdminsGroupId = Bitrixfunction::getCoptionValue(self::HOLE_ADMINS_COPTION_TEXT);
        $curUserId = Bitrixfunction::returnCurUserId();
        if($holeAdminsGroupId > 0){
            $usersGroup = Bitrixfunction::getUserGroups(
                ['USER_ID'=>$GLOBALS["USER"]->GetID(),'GROUP.ACTIVE'=>'Y'],
                ['GROUP_ID','GROUP_NAME'=>'GROUP.NAME'],
                ['GROUP.ID'=>'DESC']
                );
            if($usersGroup)
                foreach ($usersGroup as $group)
                    $userGroupsIds[] = $group['GROUP_ID'];
        }

        if($userGroupsIds && $holeAdminsGroupId){
            if(in_array($holeAdminsGroupId,$userGroupsIds))
                $result['isAdmin'] = $holeAdminsGroupId;
        }
        else
            $result['isAdmin'] = Bitrixfunction::checkUserIfAdmin();

        $result['selectedUser'] = [
            'id' => $curUserId,
            'name' => $GLOBALS["USER"]->getLastName().' '.$GLOBALS["USER"]->getFirstName(),
        ];

        $result['hole_admins_group'] = $userGroupsIds;

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
            'clubList' => [],
            'contactList' => [],
            'errors' => [],
            'locationList' => [],


            'productList' => [],


            'statusList' => [],
            'serviceList' => [],
            'table' => [
                'ths' => [],
                'tds' => [],
            ],
            'typeList' => [],
            'typeIdVal' => [
                Bitrixfunction::getCoptionValue('TYPE_INDIVIDUAL_VALUE_ID') => 1, //индивид. ID + кол-во в численности
                Bitrixfunction::getCoptionValue('TYPE_SPLIT_VALUE_ID') => 2, // сплит ID + кол-во в численности
            ],
            'groupTrainingList' => [],
            'zonaList' => [],
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


        //получение списка контактов


        $zonaListId = Bitrixfunction::getCoptionValue('SLOT_ZONA_LIST');
        if(!$zonaListId) $result['errors'][] = Loc::getMessage('CRM_GENESIS_C_OPTION_GET_SLOT_ZONA_LIST_ID_ERROR');
        else{
//            $zonaList = Bitrixfunction::getListElementsWithProperties( ['IBLOCK_ID' => $zonaListId],['ID','NAME'],['DATE_CREATE' => 'DESC']

            //!!! ВКЛЮЧЕН ДРЕВНИЙ МЕТОД И БЕЗ МНОЖЕТСВЕННЫХ ПОЛЕЙ!!!
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

            //!!! ВКЛЮЧЕН ДРЕВНИЙ МЕТОД И БЕЗ МНОЖЕТСВЕННЫХ ПОЛЕЙ!!!
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

            $serviceList = Bitrixfunction::getListElemsOld(
                ['IBLOCK_ID' => $serviceListId, '!PROPERTY_322' => false],['ID','NAME','PROPERTY_322'], ['DATE_CREATE' => 'DESC']
            );


//            if($serviceList){
//
//                $wholeResult = [];
//
//                $serviceListByEmployee = Bitrixfunction::getListElemsOld(
//                    ['IBLOCK_ID' => 58, '!PROPERTY_323' => false],['ID','NAME','PROPERTY_320','PROPERTY_323'], ['DATE_CREATE' => 'DESC']
//                );
//                if(!$serviceListByEmployee) Loc::getMessage('CRM_GENESIS_C_OPTION_GET_SERVICE_LIST_BY_USERS_RESULT_ERROR');
//                else {
//                        foreach ($serviceList as $service){
//
//                            foreach ($serviceListByEmployee as $sortedBy){
//                                if($service['PROPERTY_322_VALUE'] == $sortedBy['PROPERTY_323']){
//                                    $service['CURRENT_USER_ID'] = $sortedBy['PROPERTY_320_VALUE'];
//                                    $wholeResult[] = $service;
//                                }
//
//                            }
//
//                        }
//                }
//                $result['test_kkk'] = $serviceListByEmployee;
//                $result['test_DDD'] = $wholeResult;
//
//            }

//            $serviceList = Bitrixfunction::getListElements(
//                ['IBLOCK_ID' => $serviceListId],['ID','NAME','PROPERTY_322'],['DATE_CREATE' => 'DESC']
//            );
            (!$serviceList)
                ? $result['errors'][] = Loc::getMessage('CRM_GENESIS_C_OPTION_GET_SERVICE_LIST_RESULT_ERROR')
                :$result['serviceList'] = $serviceList;
        }

        //список групповых тренировок
        $groupTrainingListId = Bitrixfunction::getCoptionValue('SLOT_TRAINING_GROUP_LIST');
        if(!$groupTrainingListId) $result['errors'][] = Loc::getMessage('CRM_GENESIS_C_OPTION_GET_TRAINING_GROUP_LIST_ID_ERROR');
        else{
//            $groupTrainingList = Bitrixfunction::getListElements(
//                ['IBLOCK_ID' => $groupTrainingListId],['ID','NAME'],['DATE_CREATE' => 'DESC']

            //!!! ВКЛЮЧЕН ДРЕВНИЙ МЕТОД И БЕЗ МНОЖЕТСВЕННЫХ ПОЛЕЙ!!!
            $groupTrainingList = Bitrixfunction::getListElemsOld(
                ['IBLOCK_ID' => $groupTrainingListId],['ID','NAME','ACTIVE_FROM','ACTIVE_TO',
                    'PROPERTY_309','PROPERTY_310','PROPERTY_311',
                    'PROPERTY_312','PROPERTY_313','PROPERTY_314',
                    'PROPERTY_315','PROPERTY_316'
                ],
                ['DATE_CREATE' => 'DESC']
            );
            (!$groupTrainingList)
                ? $result['errors'][] = Loc::getMessage('CRM_GENESIS_C_OPTION_GET_TRAINING_GROUP_LIST_RESULT_ERROR')
                : $result['groupTrainingList'] = $groupTrainingList;
        }

        //получение списка товаров
        $productList = Bitrixfunction::getListElemsOld(
            ['IBLOCK_ID' => self::PRODUCT_CATALOG_ID,'IBLOCK_SECTION_ID' => self::PRODUCTS_SECTION_ID],['ID','NAME'],
            ['DATE_CREATE' => 'DESC']);
        (!$productList)
            ? $result['errors'][] = Loc::getMessage('CRM_GENESIS_C_OPTION_GET_PRODUCTS_LIST_RESULT_ERROR',[
                '#DIR#' => self::PRODUCTS_SECTION_ID])
            :  $result['productList'] = $productList;


        Bitrixfunction::sentAnswer($result);
    }



    /*@method: получение списка клиентов по фамилии/номеру телефона
    * @return: array*/
    public function getContactsByNameOrPhone($filter){
        $result = [
            'contactList' => [],
            'error' => false,
        ];

//        (preg_match('/^(+)?[0-9]+$/'))
//            ? $searchByField = '%PHONE'
//            : $searchByField = '%LAST_NAME';

//        $result['contactList'] = $contactsArr;


        //ЗАМЕНИТЬ НА НОРМАЛЬНЫЙ МЕТОД!!!
        $contactsArr = Bitrixfunction::getContactsList(
            ['%LAST_NAME' => $filter['contactName']],
            ['ID','NAME','LAST_NAME'],
            ['LAST_NAME' => 'DESC'],
           ''
        );
        if($contactsArr)
            foreach ($contactsArr as $contact)
                $result['contactList'][] = ['ID' => $contact['ID'], 'NAME' => $contact['LAST_NAME'].' '.$contact['NAME']];

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