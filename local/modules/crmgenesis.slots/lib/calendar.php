<?php
namespace Crmgenesis\Slots;

use Crmgenesis\Exchange1c\Bitrixfunctions;
use \Crmgenesis\Slots\Bitrixfunction,
    \Bitrix\Main\Localization\Loc;

class Calendar{


    const MonthNormaHours = 160;
    const WeekNormaHours = 40;

    /*
     * @method: Get Calendar Events By Filter: Start/End week date + selected user ID
     * @return: event array
     * */
    public function getUserSlots($filters){
        $result = [
            'errors' => [],
            'result' => [],
            'workHoursThisWeek' => [
                'hours' => 0,
                'class' => 'indicator-zero-color',
            ],
            'workHoursThisMonth' => [
                'hours' => 0,
                'class' => 'indicator-zero-color',
            ],
            'prevWeekSlotsNum' => 0,
//            'filters' => $filters,
        ];

        $firstMonthDay = date('01.m.Y',strtotime($filters['firstWeekDay']));
        $lastMonthDay = date('t.m.Y',strtotime($filters['firstWeekDay']));

        $previousWeekFirstDay = date('d.m.Y',strtotime($filters['firstWeekDay'].' -1 week'));
        $previousWeekLastDay = date('d.m.Y',strtotime($filters['lastWeekDay'].' -1 week'));

        $result['1stDayOfMonth'] = $firstMonthDay;
        $result['lastDayOfMonth'] = $lastMonthDay;

        $result['previousWeekFirstDay'] = $previousWeekFirstDay;
        $result['previousWeekLastDay'] = $previousWeekLastDay;

        $filter = [
            '>=DATE_FROM' => date('d.m.Y',strtotime($firstMonthDay)),
            'USER_ID' => $filters['selectedUserId'],
        ];

        //для получения массива предыдущей недели
        //Если предыдущий ПН был в текущем месяце, фильтруем от него, иначе от ПН прошлой недели
        (date('m',strtotime($previousWeekFirstDay)) == date('m',strtotime($firstMonthDay)))
            ? $filter['>=DATE_FROM'] = date('d.m.Y',strtotime($firstMonthDay))
            : $filter['>=DATE_FROM'] = date('d.m.Y',strtotime($previousWeekFirstDay));

        //если послежний день текущей недели приходится не на этот месяц, то берем конечной датой факт. дату конца недели
        (date('m',strtotime($lastMonthDay)) == date('m',strtotime($filters['lastWeekDay'])))
            ? $filter['<DATE_TO'] = date('d.m.Y',strtotime($lastMonthDay.' +1 day'))
            : $filter['<DATE_TO'] = date('d.m.Y',strtotime($filters['lastWeekDay'].'+1 day'));

        $groupListId = Bitrixfunction::getCoptionValue('SLOT_TRAINING_GROUP_LIST');
        $typeListId = Bitrixfunction::getCoptionValue('SLOT_TYPE_LIST');
        $typeListArr = []; //массив значений типа (индивид., групп., сплит)
        if($typeListId){
            $typeList = Bitrixfunction::getListElements(
                ['IBLOCK_ID' => $typeListId], ['ID', 'NAME'], ['DATE_CREATE' => 'DESC']
            );
            if($typeList)
                $typeListArr = $typeList;
        }

//        $result['test_type'] = $typeListArr;

//        $result['filter'] = $filter;

        $typeValIdIndivid = Bitrixfunction::getCoptionValue('TYPE_INDIVIDUAL_VALUE_ID'); //индивид. ID + кол-во в численности
        $typeValIdSplit = Bitrixfunction::getCoptionValue('TYPE_SPLIT_VALUE_ID');// сплит ID


        $recordArr = SlotTable::getList([
            'select' => ['*'],
            'filter' => $filter,
            'order' => ['ID' => 'ASC'],
        ]);

        while($event = $recordArr->fetch()){

            $event['H'] = Bitrixfunction::returnDiffBetweenDatesInCurFormat(
                date('d.m.Y H:i:s',strtotime($event['DATE_FROM'])),
                date('d.m.Y H:i:s',strtotime($event['DATE_TO'])),'%h');
            $event['M'] = Bitrixfunction::returnDiffBetweenDatesInCurFormat(
                date('d.m.Y H:i:s',strtotime($event['DATE_FROM'])),
                date('d.m.Y H:i:s',strtotime($event['DATE_TO'])),'%i');


//            $result['testWeekElems'][$event['ID']] = [$event['H'],$event['M']];


            //за месяц
            if(strtotime($event['DATE_FROM']) >= strtotime($firstMonthDay) &&
                strtotime($event['DATE_TO']) <= strtotime($lastMonthDay.' 23:59:59')){
                $result['workHoursThisMonth']['hours'] += $event['H'];
                $result['workHoursThisMonth']['hours'] += $event['M']/60;
                $result['workHoursThisMonth']['class'] = self::getIndicatorColor($result['workHoursThisMonth']['hours'],self::MonthNormaHours);
            }


            //текущая неделя
            if(strtotime($event['DATE_FROM']) >= strtotime($filters['firstWeekDay']) &&
                strtotime($event['DATE_TO']) <= strtotime($filters['lastWeekDay'].' 23:59:59')){
                $result['workHoursThisWeek']['hours'] += $event['H'];
                $result['workHoursThisWeek']['hours'] += round($event['M']/60,2);

                $result['workHoursThisWeek']['class'] = self::getIndicatorColor($result['workHoursThisWeek']['hours'],self::WeekNormaHours);

                $colors = self::selectSlotColor($event['TYPE_ID']);

                $eventTitle = Loc::getMessage('CRM_GENESIS_CALENDAR_SLOT_TEXT_EMPTY');

                if($typeListArr){
                    foreach ($typeListArr as $typeElem)
                        if($event['TYPE_ID'] == $typeElem['ID']){
                            $eventTitle = $typeElem['NAME'];

                            //для индифид. берем ФИО клиента из дочернего слота
                            if(
                                ($typeValIdIndivid && $typeValIdIndivid == $event['TYPE_ID'])
                            ||
                                ($typeValIdSplit && $typeValIdSplit == $event['TYPE_ID'])
                            ){
                                $slotBusinessElems = Bitrixfunction::getSlotBusinessList(
                                    ['OWNER_ID' => $event['ID']],['STATUS_ID','CLIENT_ID']
                                );
                                if($slotBusinessElems){
                                    foreach ($slotBusinessElems as $slBelem){
                                        if($slBelem['CLIENT_ID'] > 0){
                                            $slotBContData = Bitrixfunction::getContactData(
                                                ['ID' => $slBelem['CLIENT_ID']],
                                                ['ID','NAME','LAST_NAME']);
                                            if($slotBContData)
                                                $eventTitle.= ' - '.$slotBContData['LAST_NAME'].' '.$slotBContData['NAME'];
                                        }
                                    }

                                }
                            }

                            //!!! здесь нужно будет вывести название группы
                            else{
                                //название группы - ПОЗЖЕ ЗАМЕНИТЬ ПОЛЕ ЗДЕСЬ!!!
                                if($event['GROUP_NAME'] > 0){

                                    //запрос названия группы
                                    if($groupListId){
                                        $groupName = Bitrixfunction::getListElement(['IBLOCK_ID' => $groupListId,'ID' => $event['GROUP_NAME']],
                                            ['ID','NAME']);
                                        if($groupName) $eventTitle .= ' - '.$groupName['NAME'];
                                    }
                                }
                            }

                        }

                }

                $result['result'][] = [
                    'id' => $event['ID'],
                    'title' => $eventTitle,
                    'start' => date('Y-m-d H:i:s', strtotime($event['DATE_FROM'])),
                    'end' => date('Y-m-d H:i:s', strtotime($event['DATE_TO'])),
                    'resourceId' => $event['USER_ID'],
                    'color' => $colors['block'],
                    'textColor' => $colors['text'],
//                    'editable' => false, //запрет редактирования записи

                    'durationEditable' => false, //запрет растягивания, Но модно перемещать элемент
//                    'h' => $event['H'], //запрет редактирования записи
//                    'm' => $event['M'], //запрет редактирования записи
                ];
            }

            //прошлая неделя
            if(strtotime($event['DATE_FROM']) >= strtotime($previousWeekFirstDay) &&
                strtotime($event['DATE_TO']) <= strtotime($previousWeekLastDay.' 23:59:59')){
                $event['DATE_FROM'] = date('d.m.Y H:i:s',strtotime($event['DATE_FROM']));
                $event['DATE_TO'] = date('d.m.Y H:i:s',strtotime($event['DATE_TO']));
//                $result['test_last_week'][] = $event;
                $result['prevWeekSlotsNum']++;
            }

//            $result['ALL'][$event['ID']] = $event;

        }

        Bitrixfunction::sentAnswer($result);
    }

    /*
    * @method метод для сохранения выбранного рабочего времени каждого сотрудника
    * @return events by filter for cur user
    */
    public function addWorkPeriodToCalendar($filters){
        $result = ['errors' => [],'result' => []];

        $start = date('d.m.Y H:i:s',strtotime($filters['workDayStart']));
        $finish = date('d.m.Y H:i:s',strtotime($filters['workDayFinish']));

        list($interV['hours'],$interV['minutes'],$interV['seconds']) = explode(':',$filters['settings']['slotDuration']);

        $h = 0;
        $m = 0;
        $s = 0;
//        $i = 1;

        if($interV['hours'] > 0 || $interV['minutes'] > 0 || $interV['seconds'] > 0){

            while(strtotime($start.'+'.$h.' hours' .'+'.$m.' minutes'.'+'.$s.' seconds')  < strtotime($finish)){

                $dStart = strtotime($start.'+'.$h.' hours ' .'+'.$m.' minutes '.'+'.$s.' seconds ');
                $dEnd = strtotime($start .'+'.($h + $interV['hours']).' hours '
                    .'+'.($m + $interV['minutes']).' minutes '
                    .'+'.($s + $interV['seconds']).' seconds ');

                if(
                    ($dStart >= strtotime(date('d.m.Y',strtotime($start)).' '. $filters['settings']['minTime'])
                        &&
                        $dEnd <= strtotime(date('d.m.Y',strtotime($start)).' '. $filters['settings']['maxTime']))
                    ||
                    ($dStart >= strtotime(date('d.m.Y',strtotime($finish)).' '. $filters['settings']['minTime'])
                        &&
                        $dEnd <= strtotime(date('d.m.Y',strtotime($finish)).' '. $filters['settings']['maxTime']))
                ){
//                    $result['preResult'][] = "<br>Дата # $i:<br>".date('d.m.Y H:i:s',$dStart)
//                        .' - '.
//                        date('d.m.Y H:i:s',$dEnd);


//                    $result['preResult'][] = [
//                        'DATE_FROM' => new \Bitrix\Main\Type\DateTime(
//                            date('d.m.Y H:i:s',$dStart), "d.m.Y H:i:s"),
//                        'DATE_TO' => new \Bitrix\Main\Type\DateTime(
//                            date('d.m.Y H:i:s',$dEnd), "d.m.Y H:i:s"),
//                        'USER_ID' => $filters['selectedUserId'],
//                        'DATE_CREATE' => new \Bitrix\Main\Type\DateTime( date("d.m.Y H:i:s"), "d.m.Y H:i:s" ),
//                        'DATE_MODIFY' => new \Bitrix\Main\Type\DateTime( date("d.m.Y H:i:s"), "d.m.Y H:i:s" ),
//                        'CREATED_BY_ID' => Bitrixfunction::returnCurUserId(), //returnCurUserId
//                        'MODIFY_BY_ID' => Bitrixfunction::returnCurUserId(), //returnCurUserId
//                    ];


                    $addRes = Bitrixfunction::addSlot([
                        'DATE_FROM' => new \Bitrix\Main\Type\DateTime(
                            date('d.m.Y H:i:s',$dStart),"d.m.Y H:i:s"),
                        'DATE_TO' => new \Bitrix\Main\Type\DateTime(
                            date('d.m.Y H:i:s',$dEnd), "d.m.Y H:i:s"),
                        'USER_ID' => $filters['selectedUserId'],
                        'DATE_CREATE' => new \Bitrix\Main\Type\DateTime( date("d.m.Y H:i:s"), "d.m.Y H:i:s" ),
                        'DATE_MODIFY' => new \Bitrix\Main\Type\DateTime( date("d.m.Y H:i:s"), "d.m.Y H:i:s" ),
                        'CREATED_BY_ID' => Bitrixfunction::returnCurUserId(), //returnCurUserId
                        'MODIFY_BY_ID' => Bitrixfunction::returnCurUserId(), //returnCurUserId
                    ]);

                    ($addRes['result'])
                        ? $result['result'][] = $addRes['result']
                        : $result['errors'][] = $addRes['errors'];

                }

                $h += $interV['hours'];
                $m += $interV['minutes'];
                $s += $interV['seconds'];
//                $i++;
            }
        }

//        $result['filters_test'] = $filters;
//        $result['setting'] = date('d.m.Y H:i:s',strtotime($filters['workDayStart']));
//        $result['intervals'] = $interV;

        Bitrixfunction::sentAnswer($result);
    }

    /*
    * @method метод для удаления выбранного слота сотрудника по ID
    * @return bool
    */
    public function deleteSlotFromCalendar($filters){
        $result = ['result' => false,'errors' => []];

        $slotBusinessElems = Bitrixfunction::getSlotBusinessList(['OWNER_ID' => $filters['seletedSlotId']],['ID']);
        if($slotBusinessElems){
            foreach ($slotBusinessElems as $elem) {
                $slotBusinessDelRes = Bitrixfunction::deleteSlotBusiness($elem['ID']);
                if($slotBusinessDelRes['errors']) $result['errors'] = $slotBusinessDelRes['errors'];
            }
        }

        if(!$result['errors'])
            $result = Bitrixfunction::deleteSlot($filters['seletedSlotId']);
        Bitrixfunction::sentAnswer($result);
    }

    /*
    * @method: обновляет выбранный слот  !!! НЕ ВСТАВЯЛЕТ ВРЕМЯ И ID обновлявшего!!!!
    * @return arr*/
    public function updateSlotInCalendar($filters,$slotId,$workDateStart,$workDateFinish){
        $result = [
            'errors' => [],
            'result' => [],
        ];

        //UPD 25.12.2019
        //Нужен запрет изменения если даты затрагивают уже занятый клиентами слот.


        //ОТМЕНА!!!
        //сначала получаем слоты в разрезе начала и окончания текущего слота, исключая текущий
        //потом для каждого из них ищем слоты из табл. № 2
        //удаляем слоты в табл. №1, у которых нет дочерних слотов в табл. № 2
        //если в табл. № 2 дочерние слоты есть, оставляем, пусть перекрещиваются.

//        $result['filt'] = $filters;

//        $trainerSlotsInPeriod = [];
        $trainerEmptySlots = [];
        $trainerSlotsFilter = [
            'USER_ID' => $filters['employee']['id'],
            '>=DATE_FROM' => date('d.m.Y H:i:s',strtotime($workDateStart)),
            '<DATE_FROM' => date('d.m.Y H:i:s',strtotime($workDateFinish)),
//            '<=DATE_TO' => date('d.m.Y H:i:s',strtotime($workDateFinish)),
            '!ID' => $slotId,
        ];
        $trainerSlotsArr = \Crmgenesis\Slots\SlotTable::getList([
            'select' => ['ID','DATE_FROM','DATE_TO'],
            'filter' => $trainerSlotsFilter,
            'order' => ['ID' => 'ASC'],
        ]);
        while($ob = $trainerSlotsArr->fetch()){
//            $res[] = $ob;
//            $trainerSlotsInPeriod[] = $ob['ID'];


            $subslotArr = \Crmgenesis\Slots\Slot_businessTable::getList([
                'select' => ['*'],//['ID','OWNER_ID'],
                'filter' => ['OWNER_ID' => $ob['ID']],
                'order' => ['ID' => 'ASC'],
            ])->fetchAll();

            if($subslotArr) {

                $result['errors'][] = Loc::getMessage('CRM_GENESIS_CALENDAR_GET_CLICKED_SLOT_CHECK_IF_NOT_EMPTY_ERROR',[
                    '#START#' => date('d.m.Y H:i:s',strtotime($ob['DATE_FROM'])),
                    '#END#' => date('d.m.Y H:i:s',strtotime($ob['DATE_TO'])),
                ]);
            }
            else
                $trainerEmptySlots[] = $ob['ID'];
        }

//        $result['test_slots1.0'] = $trainerSlotsInPeriod;

//        if($trainerSlotsInPeriod){
//            $subSlots = Bitrixfunction::getSlotBusinessList(['OWNER_ID' => $trainerSlotsInPeriod],['OWNER_ID'],['ID' => 'DESC']);
//            if($subSlots){
//                foreach ($subSlots as $subslot)
//                    $position = array_search($subslot['OWNER_ID'],$trainerSlotsInPeriod);
//                if(preg_match('/^[\d]+$/',$position)) //echo "<br>position is $position<br>";
//                    unset($trainerSlotsInPeriod[$position]);
//            }
//
//            if(count($trainerSlotsInPeriod) > 0){
//                foreach ($trainerSlotsInPeriod as $trainerSlot) {
//                    $slotDelRes = Bitrixfunction::deleteSlot($trainerSlot);
//                    if(!$slotDelRes['result']) $result['errors'][] = $slotDelRes['errors'];
////                    else $result['result'][] = $slotDelRes['result'];
//                }
//
//            }
//
//        }

//        $result['test_slots1.1'] = $trainerSlotsInPeriod;
//        $result['test_slots2'] = $subSlots;

        //25.12.2019 Если ошибок от пересечения с занятыми нет, то удаляем пустые слоты и обновляем редактируемый
        if(!$result['errors']){
            foreach ($trainerEmptySlots as $emptySlot) {
                    $slotDelRes = Bitrixfunction::deleteSlot($emptySlot);
                    if(!$slotDelRes['result']) $result['errors'][] = $slotDelRes['errors'];
//                    else $result['result'][] = $slotDelRes['result'];
                }

            if(!$result['errors']){
                //теперь обновляем текущий слот
                $updFields = [
                    'DATE_FROM' => new \Bitrix\Main\Type\DateTime(date('d.m.Y H:i:s',strtotime($workDateStart)),"d.m.Y H:i:s"),
                    'DATE_TO' => new \Bitrix\Main\Type\DateTime(date('d.m.Y H:i:s',strtotime($workDateFinish)),"d.m.Y H:i:s"),
                    'STATUS_ID' => 0, //пока не понятно для чего
                    'TYPE_ID' => $filters['type'],
                    'CLUB_ID' => $filters['club'],
                    'ZONE_ID' => $filters['zone'],
                    'LOCATION_ID' => $filters['location'],
                    'AGE_FROM' => $filters['ageFrom'],
                    'AGE_TO' => $filters['ageTo'],
                    'GROUP_SIZE' => $filters['groupSize'],

                    'GROUP_NAME' => $filters['groupId'], //заменить поле на GROUP_ID

                    'SERVICE_ID' => $filters['service'],

                    'USER_ID' => $filters['employee']['id'],
                    'DATE_MODIFY' => new \Bitrix\Main\Type\DateTime( date("d.m.Y H:i:s"), "d.m.Y H:i:s" ),
                    'MODIFY_BY_ID' => Bitrixfunction::returnCurUserId(), //returnCurUserId
                ];

                $slotUpdRes = Bitrixfunction::updateSlot($slotId,$updFields);
                if(!$slotUpdRes['result']) $result['errors'][] = $slotUpdRes['errors'];

                //создаем /обновляем слоты во второй таблице!!!
                else{
                    $slotBusinessArr = Bitrixfunction::getSlotBusinessList(['OWNER_ID' => $slotId],['ID','CLIENT_ID']);

                    //если есть уже созданные слоты, то удаляем и вместо создаем новые !!!
                    //ЭТО ВРЕМЕННАЯ МЕРА!!!
                    if($slotBusinessArr){
                        foreach ($slotBusinessArr as $elem) {
                            $slotBusinessDelRes = Bitrixfunction::deleteSlotBusiness($elem['ID']);
                            if($slotBusinessDelRes['errors']) $result['errors'][] = $slotBusinessDelRes['errors'];
                        }
                    }
                    if(!$result['errors'] && count($filters['contacts']) > 0){

                        if(count($filters['contacts']) > 0){

                            $defStatusId = Bitrixfunction::getCoptionValue('SLOT_DEFAULT_STATUS');

                            foreach ($filters['contacts'] as $contact){
                                $addBusinessRes = Bitrixfunction::addSlotBusiness([
                                    'CLIENT_ID' => $contact['ID'], //ID каждого клиента
                                    'DATE_FROM' =>  new \Bitrix\Main\Type\DateTime(date('d.m.Y H:i:s',strtotime($workDateStart)),"d.m.Y H:i:s"),
                                    'DATE_TO' => new \Bitrix\Main\Type\DateTime(date('d.m.Y H:i:s',strtotime($workDateFinish)),"d.m.Y H:i:s"),
                                    'STATUS_ID' => $defStatusId, //пока не понятно для чего
                                    'OWNER_ID' => $slotId,
                                    'DATE_CREATE' => new \Bitrix\Main\Type\DateTime( date("d.m.Y H:i:s"), "d.m.Y H:i:s" ),
                                    'DATE_MODIFY' => new \Bitrix\Main\Type\DateTime( date("d.m.Y H:i:s"), "d.m.Y H:i:s" ),
                                    'CREATED_BY_ID' => Bitrixfunction::returnCurUserId(),
                                    'MODIFY_BY_ID' => Bitrixfunction::returnCurUserId(),

                                    'TYPE_ID' => $filters['type'],
                                ]);

                                if(!$addBusinessRes['result'])
//                                    $result['errors'][] = 'Ошибка при удалении/создании элемента в 2й таблицы с ID первой = '.$slotId;
                                    $result['errors'] = array_merge($addBusinessRes['errors'],$result['errors']);
                                else $result['result'][] = $addBusinessRes['result'];
                            }
                        }
                        else
                            $result['errors'][] = 'Нельзя создать слоты 2й таблицы без контактов, слот 1й таблицы № '.$slotId;

                    }
                }
            }


        }




//        $result['userUPDfields'] = $updFields;

        Bitrixfunction::sentAnswer($result);
    }



    /*
     * @method: создание множества слотов + заполнение второй таблицы по чекбоксам в указанный период
     * @return: array
     * */
    public function addFilledSlotsBetweenPeriod($filters,$checkboxes){
        $result = [
            'errors' => [],
            'result' => [],
        ];

        $countDaysBetween = Bitrixfunction::returnDiffBetweenDatesInCurFormat(
            date ('d.m.Y',strtotime($filters['periodFrom'])), date('d.m.Y',strtotime($filters['periodTo'])),'%R%a');


        $defStatusId = Bitrixfunction::getCoptionValue('SLOT_DEFAULT_STATUS');


//        $result['count_dif_D'] = $countDaysBetween;
//        $result['count_dif_D_Num'] = (int)$countDaysBetween;



        // 1я цифра - № дня недели, 2я - время начала
        if($countDaysBetween > 0){
            foreach ($checkboxes as $chbx){
                $chbxArr = explode('_',$chbx);

                for ($i = 0; $i <= (int)$countDaysBetween; $i++) {
                    $weekDay = Bitrixfunction::getDayWeek(date('d.m.Y',strtotime($filters['periodFrom'].' +'.$i.'day')));
//                    $result['lolo'][] = $chbxArr[0].' - '.$weekDay;

                    if($chbxArr[0] == $weekDay){

                        $addRes = Bitrixfunction::addSlot([
                            'DATE_FROM' => new \Bitrix\Main\Type\DateTime(date('d.m.Y H:i:s',strtotime($filters['periodFrom'].' '.$chbxArr[1].':00:00 +'.$i.' day')),"d.m.Y H:i:s"),
                            'DATE_TO' => new \Bitrix\Main\Type\DateTime(date('d.m.Y H:i:s',strtotime($filters['periodFrom'].' '.$chbxArr[1].':00:00 +'.$i.' day + 1 hour')),"d.m.Y H:i:s"),
                            'STATUS_ID' => 0, //пока не понятно для чего
                            'TYPE_ID' => $filters['type'],
                            'CLUB_ID' => $filters['club'],
                            'ZONE_ID' => $filters['zone'],
                            'LOCATION_ID' => $filters['location'],
                            'AGE_FROM' => $filters['ageFrom'],
                            'AGE_TO' => $filters['ageTo'],
                            'GROUP_SIZE' => $filters['groupSize'],
                            'GROUP_NAME' => $filters['groupName'],
                            'USER_ID' => $filters['employee']['id'],
                            'DATE_CREATE' => new \Bitrix\Main\Type\DateTime( date("d.m.Y H:i:s"), "d.m.Y H:i:s" ),
                            'DATE_MODIFY' => new \Bitrix\Main\Type\DateTime( date("d.m.Y H:i:s"), "d.m.Y H:i:s" ),
                            'CREATED_BY_ID' => Bitrixfunction::returnCurUserId(),
                            'MODIFY_BY_ID' => Bitrixfunction::returnCurUserId(), //returnCurUserId


                            'GROUP_NAME' => $filters['groupId'], //заменить поле на GROUP_ID

                            'SERVICE_ID' => $filters['service'],

                        ]);

//                        $result['new_test_arr'][] = $addRes;
                        if(!$addRes['result']) $result['errors'][] = 'Ошибка при создании элемента в 1й таблицы с датой';
                        else{
                            if(count($filters['contacts']) > 0){
                                foreach ($filters['contacts'] as $contact){
                                    $addBusinessRes = Bitrixfunction::addSlotBusiness([
                                        'CLIENT_ID' => $contact['ID'], //ID каждого клиента
                                        'DATE_FROM' => new \Bitrix\Main\Type\DateTime(date('d.m.Y H:i:s',strtotime($filters['periodFrom'].' '.$chbxArr[1].':00:00 +'.$i.' day')),"d.m.Y H:i:s"),
                                        'DATE_TO' => new \Bitrix\Main\Type\DateTime(date('d.m.Y H:i:s',strtotime($filters['periodFrom'].' '.$chbxArr[1].':00:00 +'.$i.' day + 1 hour')),"d.m.Y H:i:s"),
                                        'STATUS_ID' => $defStatusId, //пока не понятно для чего
                                        'OWNER_ID' => $addRes['result'],
                                        'DATE_CREATE' => new \Bitrix\Main\Type\DateTime( date("d.m.Y H:i:s"), "d.m.Y H:i:s" ),
                                        'DATE_MODIFY' => new \Bitrix\Main\Type\DateTime( date("d.m.Y H:i:s"), "d.m.Y H:i:s" ),
                                        'CREATED_BY_ID' => Bitrixfunction::returnCurUserId(),
                                        'MODIFY_BY_ID' => Bitrixfunction::returnCurUserId(),

                                        'TYPE_ID' => $filters['type'],
                                    ]);

                                    if(!$addBusinessRes['result']) $result['errors'][] = 'Ошибка при создании элемента в 2й таблицы с ID первой = '.$addRes['result'];
                                    else $result['result'][] = $addBusinessRes['result'];
                                }
                            }
                            else
                                $result['errors'][] = 'Нельзя создать слоты 2й таблицы без контактов, слот 1й таблицы № '.$addRes['result'];

                        }
                    }
                }
            }
        }

        Bitrixfunction::sentAnswer($result);
    }




    /*
   * @method: получает даныне выбранного слота
   * @return arr*/
    public function getSlotInCalendar($filters){
        $result = [
            'error' => false,
            'result' => [],
        ];

        $slotsBusinessArr = [];
        $slotBusObj = Slot_businessTable::getList([
            'select' => ['*'],
            'filter' => ['OWNER_ID' => $filters['seletedSlotId']],
            'order' => ['DATE_FROM' => 'ASC'],
        ]);
        while ($ob = $slotBusObj->fetch()){
            $ob['CLIENT_ARR'] = [];
            if($ob['CLIENT_ID'] > 0) {

                //!!! ПРИ ЗАМЕНЕ БОКА НА ДРУГОЙ В МАССИВ ДОБВАИТЬ ID слота 2й табл + статус !!! $ob['CLIENT_ARR']
                $contactData = Bitrixfunction::getContactData(['ID' => $ob['CLIENT_ID']],['ID','NAME','LAST_NAME']);
                if($contactData) $ob['CLIENT_ARR'] = ['ID' => $contactData['ID'], 'NAME' => $contactData['LAST_NAME'].' '.$contactData['NAME']];

            }

            $slotsBusinessArr[] = $ob;
        }
        $result['test_business_slots'] = $slotsBusinessArr;



        //главный слот - календарь
        $slotArr = SlotTable::getList([
            'select' => ['*'],
            'filter' => ['ID' => $filters['seletedSlotId']],
            'order' => ['DATE_FROM' => 'ASC'],
        ]);
        if($slotData = $slotArr->fetch()){
//            $slotData['DATE_FROM'] =  date('Y-m-d H:i:s', strtotime($slotData['DATE_FROM']));
//            $slotData['DATE_TO'] =  date('Y-m-d H:i:s', strtotime($slotData['DATE_TO']));
            if($slotData['USER_ID']){
                $userData = \Bitrix\Main\UserTable::getList([
                    'select' => ['*'],
                    'filter' => ['ID' => $slotData['USER_ID']],
                ])->fetch();
                if($userData)
                    $slotData['USER_NAME'] = $userData['LAST_NAME'].' '.$userData['NAME'];
            }

            //втсвляем данные второй таблицы
            $slotData['SLOTS_BUSINESS'] = [];
            if($slotsBusinessArr)
                foreach ($slotsBusinessArr as $slotBusiness) {
                    if($slotData['ID'] == $slotBusiness['OWNER_ID'])
                        $slotData['SLOTS_BUSINESS'][] = $slotBusiness['CLIENT_ARR'];
                }

            $result['result'] = $slotData;

        }
        else $result['error'] = Loc::getMessage('CRM_GENESIS_CALENDAR_GET_CLICKED_SLOT_BY_ID_ERROR');
//        $result['tets'] = $slotArr;

        Bitrixfunction::sentAnswer($result);
    }

    /*
     * @method: копирует слоты с предыдущей недели на текущую
     * @return arr*/
    public function copyPreviousWeekSlots($filters){
        $result = [
            'errors' => [],
            'result' => [],
        ];


        $recordArr = SlotTable::getList([
            'select' => ['ID','USER_ID','DATE_FROM','DATE_TO'],
            'filter' => [
                '>=DATE_FROM' => date('d.m.Y',strtotime($filters['firstWeekDay'].' -1 week')),
                '<=DATE_TO' => date('d.m.Y',strtotime($filters['lastWeekDay'].' -6 days')),
                'USER_ID' => $filters['selectedUserId'],
            ],
            'order' => ['DATE_FROM' => 'ASC'],
        ])->fetchAll();

        if($recordArr){
            foreach ($recordArr as $record){
                $addRes = Bitrixfunction::addSlot([
                    'DATE_FROM' => new \Bitrix\Main\Type\DateTime(
                        date('d.m.Y H:i:s',strtotime($record['DATE_FROM'].' +1 week')),
                        "d.m.Y H:i:s"),
                    'DATE_TO' => new \Bitrix\Main\Type\DateTime(
                        date('d.m.Y H:i:s',strtotime($record['DATE_TO'].' +1 week')),
                        "d.m.Y H:i:s"),
                    'USER_ID' => $record['USER_ID'],
                ]);
                ($addRes['result'])
                    ? $result['result'][] = $addRes['result']
                    : $result['errors'][] = $addRes['errors'];
            }

        }
//        $result['test_arr'] = $recordArr;

        Bitrixfunction::sentAnswer($result);
    }

    /*
     @method: change date by drag slot in calendar
     @return: array
     * */
    public function changeDateByDragNDrop($slotId,$workDateStart,$workDateFinish,$selectedUserId){
        $result = [
            'errors' => [],
            'result' => [],
        ];

        $trainerSlotsFilter = [
            'USER_ID' => $selectedUserId,
            '>=DATE_FROM' => date('d.m.Y H:i:s',strtotime($workDateStart)),
            '<DATE_FROM' => date('d.m.Y H:i:s',strtotime($workDateFinish)),
            '!ID' => $slotId,

//            'LOGIC' => 'OR',
//            [],[]
        ];
        $trainerSlotsArr = \Crmgenesis\Slots\SlotTable::getList([
            'select' => ['ID','DATE_FROM','DATE_TO'],
            'filter' => $trainerSlotsFilter,
            'order' => ['ID' => 'ASC'],
        ]);

        $siblingSlots = [];
        while($ob = $trainerSlotsArr->fetch()){
            $ob['DATE_FROM'] = date('d.m.Y H:i:s',strtotime($ob['DATE_FROM']));
            $ob['DATE_TO'] = date('d.m.Y H:i:s',strtotime($ob['DATE_TO']));
            $siblingSlots[] = $ob;
        }


        if($siblingSlots){
                $result['errors'][] = Loc::getMessage('CRM_GENESIS_CALENDAR_GET_DRAG_N_DROP_SLOT_CHECK_IF_NOT_EMPTY_ERROR');
        }
        else{
            $slotsBusinessArr = Bitrixfunction::getSlotBusinessList(['OWNER_ID' => $slotId],['ID']);
            if($slotsBusinessArr)
                foreach ($slotsBusinessArr as $slotBusiness){
                    $slotBusinessUpdFields = [
                        'DATE_FROM' => new \Bitrix\Main\Type\DateTime(date('d.m.Y H:i:s',strtotime($workDateStart)),"d.m.Y H:i:s"),
                        'DATE_TO' => new \Bitrix\Main\Type\DateTime(date('d.m.Y H:i:s',strtotime($workDateFinish)),"d.m.Y H:i:s"),
                        'DATE_MODIFY' => new \Bitrix\Main\Type\DateTime( date("d.m.Y H:i:s"), "d.m.Y H:i:s" ),
                        'MODIFY_BY_ID' => Bitrixfunction::returnCurUserId(), //returnCurUserId
                    ];
                    $slotBusinessUpdRes = Bitrixfunction::updateSlotBusiness($slotBusiness['ID'],$slotBusinessUpdFields);
                    if(!$slotBusinessUpdRes['result']) $result['errors'][] = $slotBusinessUpdRes['errors'];
                }


            if(!$result['errors']){
                $updFields = [
                    'DATE_FROM' => new \Bitrix\Main\Type\DateTime(date('d.m.Y H:i:s',strtotime($workDateStart)),"d.m.Y H:i:s"),
                    'DATE_TO' => new \Bitrix\Main\Type\DateTime(date('d.m.Y H:i:s',strtotime($workDateFinish)),"d.m.Y H:i:s"),
                    'DATE_MODIFY' => new \Bitrix\Main\Type\DateTime( date("d.m.Y H:i:s"), "d.m.Y H:i:s" ),
                    'MODIFY_BY_ID' => Bitrixfunction::returnCurUserId(), //returnCurUserId
                ];
                $result = Bitrixfunction::updateSlot($slotId,$updFields);

            }
        }


//        $result['test'] = [$slotId,$workDateStart,$workDateFinish,$selectedUserId];
        $result['OTHER_SLOTS'] = $siblingSlots;

//        $result['test_buu'] = $slotsBusinessArr;

        Bitrixfunction::sentAnswer($result);
    }


    /*@method: в зависимости от даты начала эвента меняем цвет
    @return: array, цвет блока + цвет текста*/
    private function selectSlotColor($slotType){

//        $diffRes = Bitrixfunction::returnDiffBetweenDatesInCurFormat(
//            date ('d.m.Y'), date('d.m.Y',strtotime($dateStart)),'%R%a');
        switch ($slotType){
            case (28996): //индивидуальная
                $color = [
                    'block' => '#054ab1',
                    'text' => '#fff'
                ];
                break;
            case (28995): //групповая
                $color = [
                    'block' => '#d46b00',
                    'text' => '#fff'
                ];
                break;
            case(28994):   //сплит
                $color = [
                    'block' => '#04751e',
                    'text' => '#fff'
                    ];
                break;
            default:   //не выбран тип слота, т.е. пустой
                $color = [
                    'block' => '#c6cdd3',
                    'text' => '#535c69'
                ];
                break;
        }
        return $color;
    }

    //цвет индикатора часов календаря (неделя/месяц)
    private function getIndicatorColor($hours,$periodNormaHours){
        switch(true){
            case (($hours/$periodNormaHours * 100) > 0 && ($hours/$periodNormaHours * 100) < 50):
                $colorClass = 'indicator-bad-color';
                break;
            case (($hours/$periodNormaHours * 100) >= 50 && ($hours/$periodNormaHours * 100) < 80):
                $colorClass = 'indicator-better-color';
                break;
            case (($hours/$periodNormaHours * 100) >= 80 && ($hours/$periodNormaHours * 100) < 95):
                $colorClass = 'indicator-good-color';
                break;
            case (($hours/$periodNormaHours * 100) >= 95 && ($hours/$periodNormaHours * 100) <= 100):
                $colorClass = 'indicator-best-color';
                break;
            case (($hours/$periodNormaHours * 100) > 100):
                $colorClass = 'indicator-over-color';
                break;
            default:
                $colorClass = 'indicator-zero-color';
                break;
        }
        return $colorClass;
    }


}
