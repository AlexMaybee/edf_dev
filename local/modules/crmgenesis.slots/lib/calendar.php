<?php
namespace Crmgenesis\Slots;

use \Crmgenesis\Slots\Bitrixfunction,
    \Bitrix\Main\Localization\Loc;

class Calendar{

    const WeekNormaHours = 40;
    const MonthNormaHours = 160;

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
            'USER_ID' => $filters['seletedUserId'],
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


//        $result['filter'] = $filter;

        $recordArr = SlotsTable::getList([
            'select' => ['*'],
            'filter' => $filter,
            'order' => ['ID' => 'ASC'],
        ]);

        while($event = $recordArr->fetch()){

            //текущая неделя
//            $diffStartRes = Bitrixfunction::returnDiffBetweenDatesInCurFormat(
//                date('d.m.Y H:i:s',strtotime($filters['firstWeekDay'])),
//                date('d.m.Y H:i:s',strtotime($event['DATE_FROM'])),'%R%a');
//            $diffEndRes = Bitrixfunction::returnDiffBetweenDatesInCurFormat(
//                date('d.m.Y H:i:s',strtotime($filters['lastWeekDay'])),
//                date('d.m.Y H:i:s',strtotime($event['DATE_TO'])),'%R%a');

            //за месяц
//            $diffMonthStartRes = Bitrixfunction::returnDiffBetweenDatesInCurFormat(
//                date('d.m.Y H:i:s',strtotime($firstMonthDay)),
//                date('d.m.Y H:i:s',strtotime($event['DATE_FROM'])),'%R%a');
//            $diffMonthEndRes = Bitrixfunction::returnDiffBetweenDatesInCurFormat(
//                date('d.m.Y H:i:s',strtotime($lastMonthDay)),
//                date('d.m.Y H:i:s',strtotime($event['DATE_TO'])),'%R%a');

            $event['H'] = Bitrixfunction::returnDiffBetweenDatesInCurFormat(
                date('d.m.Y H:i:s',strtotime($event['DATE_FROM'])),
                date('d.m.Y H:i:s',strtotime($event['DATE_TO'])),'%h');
            $event['M'] = Bitrixfunction::returnDiffBetweenDatesInCurFormat(
                date('d.m.Y H:i:s',strtotime($event['DATE_FROM'])),
                date('d.m.Y H:i:s',strtotime($event['DATE_TO'])),'%i');


//            $result['testWeekElems'][$event['ID']] = [$event['H'],$event['M']];


            //month
//            if($diffMonthStartRes >= 0 && $diffMonthEndRes <= 0){
            if(strtotime($event['DATE_FROM']) >= strtotime($firstMonthDay) &&
                strtotime($event['DATE_TO']) <= strtotime($lastMonthDay.' 23:59:59')){
                $result['workHoursThisMonth']['hours'] += $event['H'];
                $result['workHoursThisMonth']['hours'] += $event['M']/60;
                $result['workHoursThisMonth']['class'] = self::getIndicatorColor($result['workHoursThisMonth']['hours'],self::MonthNormaHours);
            }


            //week
//            if($diffStartRes >= 0 && $diffEndRes <= 0){
//            date('d.m.Y H:i:s',strtotime($filters['firstWeekDay'])),
//                date('d.m.Y H:i:s',strtotime($event['DATE_FROM'])),'%R%a');
//            date('d.m.Y H:i:s',strtotime($filters['lastWeekDay'])),
//                date('d.m.Y H:i:s',strtotime($event['DATE_TO']))
            if(strtotime($event['DATE_FROM']) >= strtotime($filters['firstWeekDay']) &&
                strtotime($event['DATE_TO']) <= strtotime($filters['lastWeekDay'].' 23:59:59')){
                $result['workHoursThisWeek']['hours'] += $event['H'];
                $result['workHoursThisWeek']['hours'] += $event['M']/60;

                $result['workHoursThisWeek']['class'] = self::getIndicatorColor($result['workHoursThisWeek']['hours'],self::WeekNormaHours);

                $colors = self::selectActivityColor($event['DATE_FROM']);
                $result['result'][] = [
                    'id' => $event['ID'],
                    'title' => 'Встреча #' . $event['ID'],
                    'start' => date('Y-m-d H:i:s', strtotime($event['DATE_FROM'])),
                    'end' => date('Y-m-d H:i:s', strtotime($event['DATE_TO'])),
                    'resourceId' => $event['USER_ID'],
                    'color' => $colors['block'],
                    'textColor' => $colors['text'],
                    'editable' => false, //запрет редактирования записи
//                    'h' => $event['H'], //запрет редактирования записи
//                    'm' => $event['M'], //запрет редактирования записи
                ];
            }

            //прошлая неделя
//            $diffLastWeekStartRes = Bitrixfunction::returnDiffBetweenDatesInCurFormat(
//                date('d.m.Y H:i:s',strtotime($previousWeekFirstDay)),
//                date('d.m.Y H:i:s',strtotime($event['DATE_FROM'])),'%R%a');
//            $diffLastWeekEndRes = Bitrixfunction::returnDiffBetweenDatesInCurFormat(
//                date('d.m.Y H:i:s',strtotime($previousWeekLastDay)),
//                date('d.m.Y H:i:s',strtotime($event['DATE_TO'])),'%R%a');
//            if($diffLastWeekStartRes >= 0 && $diffLastWeekEndRes <= 0) {
            if(strtotime($event['DATE_FROM']) >= strtotime($previousWeekFirstDay) &&
                strtotime($event['DATE_TO']) <= strtotime($previousWeekLastDay.' 23:59:59')){
                $event['DATE_FROM'] =  date('d.m.Y H:i:s',strtotime($event['DATE_FROM']));
                $event['DATE_TO'] =  date('d.m.Y H:i:s',strtotime($event['DATE_TO']));
                $result['test_last_week'][] = $event;
                $result['prevWeekSlotsNum']++;
            }

//            $result['ALL'][$event['ID']] = $event;

        }

        Bitrixfunction::sentAnswer($result);
    }

    /*
    * @method метод для сохранения выбранного рабочего времени каждого сотрудника
     *
     *       !!! НЕ ВСТАВЯЛЕТ ВРЕМЯ И ID создвшего!!!!
     *
    * @return events by filter for cur user
    */
    public function addWorkPeriodToCalendar($filters){
        $result = ['errors' => [],'result' => []];

        $start = date('d.m.Y H:i:s',strtotime($filters['workDayStart']));
        $finish = date('d.m.Y H:i:s',strtotime($filters['workDayFinish']));

        $intervalHours = Bitrixfunction::returnDiffBetweenDatesInCurFormat($start,$finish,'%R%h');

        if($intervalHours > 0){
            $strtH = 0;
            $endH = 1;

            while(strtotime($start.'+'.$strtH.' hour') < strtotime($finish)){
                $result['test'][] = date('d.m.Y H:i:s',strtotime($start.'+'.$strtH.' hour'))
                    .' - '.date('d.m.Y H:i:s',strtotime($start.'+'.$endH.' hour'));

                $addRes = Bitrixfunction::addSlot([
                    'DATE_FROM' => new \Bitrix\Main\Type\DateTime(
                        date('d.m.Y H:i:s',strtotime($start.'+'.$strtH.' hour')),
                        "d.m.Y H:i:s"),
                    'DATE_TO' => new \Bitrix\Main\Type\DateTime(
                        date('d.m.Y H:i:s',strtotime($start.'+'.$endH.' hour')),
                        "d.m.Y H:i:s"),
                    'USER_ID' => $filters['seletedUserId'],
//                    'DATE_CREATE' => new \Bitrix\Main\Type\DateTime('now', "d.m.Y H:i:s"),
//                    'CREATED_BY_ID' => Bitrixfunction::returnCurUserId(), //returnCurUserId
                ]);

                ($addRes['result'])
                    ? $result['result'][] = $addRes['result']
                    : $result['errors'][] = $addRes['errors'];

                $strtH++;
                $endH++;
            }
        }

//        $result['H_interval'] = $intervalHours;

        Bitrixfunction::sentAnswer($result);
    }

    /*
    * @method метод для удаления выбранного слота сотрудника по ID
    * @return bool
    */
    public function deleteSlotFromCalendar($filters){
        $result = Bitrixfunction::deleteSlot($filters['seletedSlotId']);
        Bitrixfunction::sentAnswer($result);
    }

    /*
    * @method: обновляет выбранный слот  !!! НЕ ВСТАВЯЛЕТ ВРЕМЯ И ID обновлявшего!!!!
    * @return arr*/
    public function updateSlotInCalendar($filters,$slotId){
        $result = [
            'errors' => [],
            'result' => [],
        ];
        $updFields = [
//            'DATE_FROM' => new \Bitrix\Main\Type\DateTime(
//                date('d.m.Y H:i:s',strtotime($start.'+'.$strtH.' hour')),
//                "d.m.Y H:i:s"),
//            'DATE_TO' => new \Bitrix\Main\Type\DateTime(
//                date('d.m.Y H:i:s',strtotime($start.'+'.$endH.' hour')),
//                "d.m.Y H:i:s"),
            'AGE_FROM' => $filters['ageFrom'],
            'AGE_TO' => $filters['ageTo'],
            'USER_ID' => $filters['employee']['id'],
            'LOCATION_ID' => $filters['location'],
//            'DATE_MODIFY' => new \Bitrix\Main\Type\DateTime(date("d.m.Y H:i:s"), "d.m.Y H:i:s"),
//            'MODIFY_BY_ID' => Bitrixfunction::returnCurUserId(), //returnCurUserId
        ];
        $result = Bitrixfunction::updateSlot($slotId,$updFields);
        $result['userUPDfields'] = $updFields;

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

        $slotArr = SlotsTable::getList([
            'select' => ['*'],
            'filter' => ['ID' => $filters['seletedSlotId']],
            'order' => ['DATE_FROM' => 'ASC'],
        ]);
        if($slotData = $slotArr->fetch()){
//            $slotData['DATE_FROM'] =  date('Y-m-d H:i:s', strtotime($slotData['DATE_FROM']));
//            $slotData['DATE_TO'] =  date('Y-m-d H:i:s', strtotime($slotData['DATE_TO']));
            if($slotData['USER_ID']){
                $userData = \Bitrix\Main\UserTable::getList([
                    'select' => ['ID','LAST_NAME','NAME'],
                    'filter' => ['ID' => $slotData['USER_ID']],
                ])->fetch();
                if($userData)
                    $slotData['USER_NAME'] = $userData['LAST_NAME'].' '.$userData['NAME'];
            }
            $result['result'] = $slotData;
        }
        else $result['error'] = Loc::getMessage('CRM_GENESIS_CALENDAR_GET_CLICKED_SLOT_BY_ID_ERROR');
        $result['tets'] = $slotArr;

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


        $recordArr = SlotsTable::getList([
            'select' => ['USER_ID','DATE_FROM','DATE_TO'],
            'filter' => [
                '>=DATE_FROM' => date('d.m.Y',strtotime($filters['firstWeekDay'].' -1 week')),
                '<=DATE_TO' => date('d.m.Y',strtotime($filters['lastWeekDay'].' -6 days')),
                'USER_ID' => $filters['seletedUserId'],
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


    /*@method: в зависимости от даты начала эвента меняем цвет
    @return: array, цвет блока + цвет текста*/
    private function selectActivityColor($dateStart){
        $diffRes = Bitrixfunction::returnDiffBetweenDatesInCurFormat(
            date ('d.m.Y'), date('d.m.Y',strtotime($dateStart)),'%R%a');
        switch (true){
            case ($diffRes < 0): //прошлый день
                $color = [
                    'block' => '#000',
                    'text' => '#fff'
                ];
                break;
            case ($diffRes > 0): //будущий день
                $color = [
                    'block' => '#007bff',
                    'text' => '#fff'
                ];
                break;
            default:   //текущий день
                $color = [
                    'block' => '#d00000',
                    'text' => '#fff'
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