<?php

namespace Crmgenesis\Slots;

use \Crmgenesis\Slots\Bitrixfunction,
    \Bitrix\Main\Localization\Loc;

class Filter{

    public function getFilterValues(){
        $result = [
            'employees' => [],
            'errors' => [],
        ];

        $userList = Bitrixfunction::getUsersByFilter(['ACTIVE' => 'Y'],['ID','LAST_NAME','NAME']);
        if(!$userList) $result['errors'][] = Loc::getMessage('CRM_GENESIS_USERS_FOR_FILTER_GET_ERROR');
        else{
            $result['employees'][] = ['ID' => '', 'NAME' => Loc::getMessage('CRM_GENESIS_NOT_SELECTED')];
            foreach ($userList as $user)
                $result['employees'][] = ['ID' => $user['ID'], 'NAME' => $user['LAST_NAME'].' '.$user['NAME']];
        }
        Bitrixfunction::sentAnswer($result);
    }

}