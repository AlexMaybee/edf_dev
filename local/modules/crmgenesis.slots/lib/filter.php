<?php

namespace Crmgenesis\Slots;

use \Crmgenesis\Slots\Bitrixfunction,
    \Bitrix\Main\Localization\Loc;

class Filter{

    /*@method: получение ID и роли текущего пользователя
     * @return: array*/
    public function getUserRoleAndId(){
        $result = [
            'isAdmin' => false,
            'seletedUserId' => false,
            'error' => false,
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