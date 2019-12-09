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
                    'DATE_CREATE' => new \Bitrix\Main\Type\Date( date("d.m.Y H:i:s"), "d.m.Y H:i:s" ),
                    'CREATED_BY_ID' => Bitrixfunction::returnCurUserId(), //returnCurUserId
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
    public function updateSlotInCalendar($filters,$slotId,$workDateStart,$workDateFinish){
        $result = [
            'errors' => [],
            'result' => [],
        ];
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
            'GROUP_NAME' => $filters['groupName'],
            'USER_ID' => $filters['employee']['id'],
            'DATE_MODIFY' => new \Bitrix\Main\Type\Date( date("d.m.Y H:i:s"), "d.m.Y H:i:s" ),
            'MODIFY_BY_ID' => Bitrixfunction::returnCurUserId(), //returnCurUserId
        ];
        $result = Bitrixfunction::updateSlot($slotId,$updFields);
        $result['userUPDfields'] = $updFields;

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


        $result['count_dif_D'] = $countDaysBetween;
        $result['count_dif_D_Num'] = (int)$countDaysBetween;



        // 1я цифра - № дня недели, 2я - время начала
        if($countDaysBetween > 0){
            foreach ($checkboxes as $chbx){
                $chbxArr = explode('_',$chbx);

                for ($i = 0; $i <= (int)$countDaysBetween; $i++) {
                    $weekDay = Bitrixfunction::getDayWeek(date('d.m.Y',strtotime($filters['periodFrom'].' +'.$i.'day')));
                    $result['lolo'][] = $chbxArr[0].' - '.$weekDay;

                    if($chbxArr[0] == $weekDay){



                        $addRes = Bitrixfunction::addSlot([
//                            'DATE_FROM1' => date('d.m.Y H:i:s',strtotime($filters['periodFrom'].' '.$chbxArr[1].':00:00 +'.$i.' day')),
//                            'DATE_TO1' => date('d.m.Y H:i:s',strtotime($filters['periodFrom'].' '.$chbxArr[1].':00:00 +'.$i.' day + 1 hour')),

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
                            'DATE_CREATE' => new \Bitrix\Main\Type\Date( date("d.m.Y H:i:s"), "d.m.Y H:i:s" ),
                            'CREATED_BY_ID' => Bitrixfunction::returnCurUserId(),
                        ]);

                        $result['new_test_arr'][] = $addRes;
                        if(!$addRes['result']) $result['errors'][] = 'Ошибка при создании элемента в 1й таблицы с датой';
                        else{
                            $addBusinessRes = Bitrixfunction::addSlotBusiness([
                                'DATE_FROM' => new \Bitrix\Main\Type\DateTime(date('d.m.Y H:i:s',strtotime($filters['periodFrom'].' '.$chbxArr[1].':00:00 +'.$i.' day')),"d.m.Y H:i:s"),
                                'DATE_TO' => new \Bitrix\Main\Type\DateTime(date('d.m.Y H:i:s',strtotime($filters['periodFrom'].' '.$chbxArr[1].':00:00 +'.$i.' day + 1 hour')),"d.m.Y H:i:s"),
                                'STATUS_ID' => $defStatusId, //пока не понятно для чего
                                'OWNER_ID' => $addRes['result'],
                                'DATE_CREATE' => new \Bitrix\Main\Type\Date( date("d.m.Y H:i:s"), "d.m.Y H:i:s" ),
                                'CREATED_BY_ID' => Bitrixfunction::returnCurUserId(),
                            ]);

                            if(!$addBusinessRes['result']) $result['errors'][] = 'Ошибка при создании элемента в 2й таблицы с ID первой = '.$addRes['result'];
                            else $result['result'][] = $addBusinessRes['result'];
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


        $recordArr = SlotTable::getList([
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