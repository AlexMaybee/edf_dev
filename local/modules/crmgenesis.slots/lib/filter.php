<?php

namespace Crmgenesis\Slots;

use \Crmgenesis\Slots\Bitrixfunction,
    \Bitrix\Main\Localization\Loc;

class Filter{

    public function checkRoleAndGetFilterValues(){
        $result = [
            'users' => [],
            'isAdmin' => 0,
            'curUserId' => 0,
            'error' => false,
        ];

        $result['curUserId'] = Bitrixfunction::returnCurUserId();

        if(Bitrixfunction::checkUserIfAdmin()){
            $result['isAdmin'] = 1;

            $userList = Bitrixfunction::getUsersByFilter(['ACTIVE' => 'Y'],['ID','LAST_NAME','NAME']);
            if(!$userList) $result['error'] = Loc::getMessage('CRM_GENESIS_USERS_FOR_FILTER_GET_ERROR');
            else{
                $result['users'][] = ['ID' => '', 'NAME' => Loc::getMessage('CRM_GENESIS_NOT_SELECTED')];
                foreach ($userList as $user)
                    $result['users'][] = ['ID' => $user['ID'], 'NAME' => $user['LAST_NAME'].' '.$user['NAME']];
            }
        }


        Bitrixfunction::sentAnswer($result);
    }

}