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
//            'workMinutesThisWeek' => 0,
            'workHoursThisMonth' => [
                'hours' => 0,
                'class' => 'indicator-zero-color',
            ],
            'prevWeekSlotsNum' => 0,
//            'workMinutesThisMonth' => 0,
//            'filters' => $filters,
        ];

        $firstMonthDay = date('01.m.Y',strtotime($filters['firstWeekDay']));
        $lastMonthDay = date('t.m.Y',strtotime($filters['firstWeekDay']));
//        $lastMonthDay = date('01.m.Y',strtotime($filters['firstWeekDay'].' +1 month'));

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


        $result['filter'] = $filter;

//        $result['testWeekElems'] = [];

        $recordArr = SlotsTable::getList([
            'select' => ['*'],
            'filter' => $filter,
            'order' => ['ID' => 'ASC'],
        ]);

        while($event = $recordArr->fetch()){

            //текущая неделя
            $diffStartRes = Bitrixfunction::returnDiffBetweenDatesInCurFormat(
                date('d.m.Y H:i:s',strtotime($filters['firstWeekDay'])),
                date('d.m.Y H:i:s',strtotime($event['DATE_FROM'])),'%R%a');
            $diffEndRes = Bitrixfunction::returnDiffBetweenDatesInCurFormat(
                date('d.m.Y H:i:s',strtotime($filters['lastWeekDay'])),
                date('d.m.Y H:i:s',strtotime($event['DATE_TO'])),'%R%a');

            //за месяц
            $diffMonthStartRes = Bitrixfunction::returnDiffBetweenDatesInCurFormat(
                date('d.m.Y H:i:s',strtotime($firstMonthDay)),
                date('d.m.Y H:i:s',strtotime($event['DATE_FROM'])),'%R%a');
            $diffMonthEndRes = Bitrixfunction::returnDiffBetweenDatesInCurFormat(
                date('d.m.Y H:i:s',strtotime($lastMonthDay)),
                date('d.m.Y H:i:s',strtotime($event['DATE_TO'])),'%R%a');

            $event['H'] = Bitrixfunction::returnDiffBetweenDatesInCurFormat(
                date('d.m.Y H:i:s',strtotime($event['DATE_FROM'])),
                date('d.m.Y H:i:s',strtotime($event['DATE_TO'])),'%h');
            $event['M'] = Bitrixfunction::returnDiffBetweenDatesInCurFormat(
                date('d.m.Y H:i:s',strtotime($event['DATE_FROM'])),
                date('d.m.Y H:i:s',strtotime($event['DATE_TO'])),'%i');


//            $result['testWeekElems'][$event['ID']] = [$event['H'],$event['M']];


            //month
            if($diffMonthStartRes >= 0 && $diffMonthEndRes <= 0){
                $result['workHoursThisMonth']['hours'] += $event['H'];
                $result['workHoursThisMonth']['hours'] += $event['M']/60;
                $result['workHoursThisMonth']['class'] = self::getIndicatorColor($result['workHoursThisMonth']['hours'],self::MonthNormaHours);
            }


            //week
            if($diffStartRes >= 0 && $diffEndRes <= 0){
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
                    'h' => $event['H'], //запрет редактирования записи
                    'm' => $event['M'], //запрет редактирования записи
                ];
            }

            //прошлая неделя
            $diffLastWeekStartRes = Bitrixfunction::returnDiffBetweenDatesInCurFormat(
                date('d.m.Y H:i:s',strtotime($previousWeekFirstDay)),
                date('d.m.Y H:i:s',strtotime($event['DATE_FROM'])),'%R%a');
            $diffLastWeekEndRes = Bitrixfunction::returnDiffBetweenDatesInCurFormat(
                date('d.m.Y H:i:s',strtotime($previousWeekLastDay)),
                date('d.m.Y H:i:s',strtotime($event['DATE_FROM'])),'%R%a');
            if($diffLastWeekStartRes >= 0 && $diffLastWeekEndRes <= 0)
                $result['prevWeekSlotsNum']++;

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
                ]);

                ($addRes['result'])
                    ? $result['result'][] = $addRes['result']
                    : $result['errors'][] = $addRes['errors'];

                $strtH++;
                $endH++;
            }
        }

        $result['H_interval'] = $intervalHours;


//        $addRes = Bitrixfunction::addSlot([
//            'DATE_FROM' => new \Bitrix\Main\Type\DateTime(date('d.m.Y H:i:s',strtotime($filters['workDayStart'])),"d.m.Y H:i:s"),
//            'DATE_TO' => new \Bitrix\Main\Type\DateTime(date('d.m.Y H:i:s',strtotime($filters['workDayFinish'])),"d.m.Y H:i:s"),
//            'USER_ID' => $filters['seletedUserId'],
//        ]);
//
//        ($addRes['result'])
//            ? $result['result'] = $addRes['result']
//            : $result['errors'] = $addRes['errors'];

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