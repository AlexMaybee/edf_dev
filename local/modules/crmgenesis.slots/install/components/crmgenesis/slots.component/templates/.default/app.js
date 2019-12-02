let app = new Vue({
    el: '#slot_calendar',
    data () {
        return {
            defaultDate: moment(new Date).format('YYYY-MM-DD'),
            defaultDateCustom: moment(new Date),
            // editable: true,
            events: [],
            filterValueLists: { //объект значений для фильтров
                users: [],
            },
            firstWeekDay: '',
            isAdmin: false,
            lang: {
                weekHourText: '',
                monthHourText: '',
                measureText: '',
            },
            prevWeekSlotsNum: 0,
            resources: [],
            request_url: '/local/components/crmgenesis/slots.component/ajax.php',
            settings: {minTime: '07:00:00', maxTime: '22:30:00', slotDuration: '00:60:00', slotMinute: '60'},
            seletedUserId: '',
            seletedSlotId: '', //id слота, если
            workDayStart: '', //дата начала рабочего дня при выборе
            workDayFinish: '', //дата окончания раюочего дня при выборе
            workHoursThisWeek: {
                hours: 0,
                class: '',
            },
            workHoursThisMonth: {
                hours: 0,
                class: '',
            },
        }
    },

    mounted() {

        $(this.$refs.vuemodaladdDealToCalendar).on("hidden.bs.modal", this.doSomethingOnHidden);
        $(this.$refs.vuemodaladdDealToCalendar).on("shown.bs.modal", this.doSomethingOnShow);

        //при загрузке страницы вычисляем дату понедельника текущей недели, а при --
        // -- переключении календаря получаем из него (Event) - FilterEventsDate
        this.firstWeekDay = this.getCurWeekendMondayDate(this.defaultDate);

        //данные пользователя + загрузка евентов календаря
        this.getUserRoleAndId();
    },

    watch: {

        //изменение понедельника календаря -> запрос новых евентов по датам
        firstWeekDay: function () {
            console.log('first week day changed to ',this.firstWeekDay);
            this.getUserSlots();
        },

    },

    events: {},

    methods: {

        //28.11.2019 Отдельно получаем ID текущего пользователя и права, если админ, то получаем фильтр пользователей
        getUserRoleAndId: function(){
            axios.post(this.request_url,{action:'getUserRoleAndId'}).then(response => {

                // console.log('User Data Result:',response.data);

                if(response.data.seletedUserId != false) {
                    this.seletedUserId = response.data.seletedUserId;

                    //langs
                    this.lang.weekHourText = response.data.lang.weekText;
                    this.lang.monthHourText = response.data.lang.monthText;
                    this.lang.measureText = response.data.lang.measureText;

                    // console.log('lang',response.data.lang);

                    if(response.data.isAdmin != false){
                        this.isAdmin = response.data.isAdmin;

                        //запрос в пользователей для фильтра
                        this.getDataForFilters();
                    }
                    // console.log('seleted User Id:',this.seletedUserId,this.isAdmin)

                    //здесь запрос данных календаря при загрузке для конкретного пользователя
                    this.getUserSlots();
                    // console.log('Monday Date is:', this.firstWeekDay);

                }
            }).catch(err => console.log(err));
        },

        //28.11.2019 Получение списка пользователей для фильтра, если тек. юзер == админ
        getDataForFilters: function(){
            axios.post(this.request_url,{action:'getDataForFilters'}).then(response => {

                // console.log('Data for Filters Result:',response.data);

                if(response.data.error != false)
                    console.log('v-ERROR:',response.data.error);
                else{

                    //users List to filter
                    (response.data.userList.length > 0)
                        ? this.filterValueLists.users = response.data.userList
                        : this.filterValueLists.users = [];


                }

            }).catch(err => console.log(err));
        },

        //28.11.2019 Переделка функции получения записей календаря в промежутке выбранной недели +  id выбранного пользователя
        // + добавлен подсчет кол-ва выбранных часов за неделю + за месяц
        getUserSlots: function(){
            if(this.seletedUserId) {
                axios.post(this.request_url,
                    {
                        action: 'getUserSlots',
                        filters:
                            {
                                'firstWeekDay': this.firstWeekDay,
                                'lastWeekDay': moment(this.firstWeekDay).day(+7).format('YYYY-MM-DD'),
                                'seletedUserId': this.seletedUserId,
                            },
                    }).then(response => {

                        this.workHoursThisWeek = response.data.workHoursThisWeek;
                        this.workHoursThisMonth = response.data.workHoursThisMonth;
                        this.prevWeekSlotsNum = response.data.prevWeekSlotsNum; //кол-во созданных слотов на прошлой неделе

                    //тупо передаем значение, если даже пусто, т.к. пользователя можно выбрать в фильтре другого
                    this.events = response.data.result;

                    console.log('Calendar Events for User', response.data);

                }).catch(err => console.log(err));
            }
        },

        //получение понедельника текущей недели, для вскр == 0 нужно брать пн (-6 дней)
        getCurWeekendMondayDate: function(curdate){
            let cudDateNumber = moment(curdate).weekday(),
                weekendMondayDate = '';

            (cudDateNumber == 0)
                ? weekendMondayDate = moment(curdate).weekday(cudDateNumber - 1).weekday(1).format('YYYY-MM-DD')
                //? weekendMondayDate = moment(curdate).day(-6).format('YYYY-MM-DD')
                : weekendMondayDate = moment(curdate).weekday(1).format('YYYY-MM-DD');
            return weekendMondayDate;
            },

        //обновляем дату первого дня недели при переходе в календаре
        FilterEventsDate: function(date) {
            if(date)
                this.firstWeekDay = moment(date).format('YYYY-MM-DD');
        },

        //открытие попапа при выборе пустых слотов
        openWorkDayAddPopup: function (dateFrom,dateTo,slotId) {
            this.workDayStart = dateFrom;
            this.workDayFinish = dateTo;
            this.seletedSlotId = slotId;
            $('#workDayInCalendar').modal('show');

            console.log('PopUp:',dateFrom,dateTo,slotId,this.seletedSlotId);
        },

        //создание слотов в календаре (разеляем выбраный день на слоты по 1 часу)
        addWorkPeriodToCalendar: function () {
            if(this.seletedUserId){
                axios.post(this.request_url,
                    {action:'addWorkPeriodToCalendar',
                        filters: {
                            'workDayStart':this.workDayStart,
                            'workDayFinish': this.workDayFinish,
                            'seletedUserId': this.seletedUserId,
                        },
                    }).then(response => {

                        // console.log('addWorkPeriodToCalendar: ',response.data)

                        //если сохранилось, то закрываем попап
                        if(response.data.result.length > 0){
                            $('#workDayInCalendar').modal('hide');
                        }
                        else console.log('v-ERROR:',response.data.errors)

                        //перезапуск функции получения евентов на выбранную неделю
                        this.getUserSlots();

                        //очищаем поля с датами периодов и ID слота
                        this.workDayStart = '';
                        this.workDayFinish = '';
                        this.seletedSlotId = '';
                    }).catch(err => console.log(err));
            }
        },

        //удаление слота из календаря
        deleteSlot: function () {
            if(this.seletedUserId){
                console.log('Del slot # ' + this.seletedSlotId);

                axios.post(this.request_url,
                    {action:'deleteSlot',
                        filters: {
                            'seletedSlotId':this.seletedSlotId,
                        },
                    }).then(response => {

                    // console.log('deleteSlot: ',response.data)

                    //если сохранилось, то закрываем попап
                    if(response.data.result){
                        $('#workDayInCalendar').modal('hide');
                    }
                    else console.log('v-ERROR:',response.data.errors)

                    //перезапуск функции получения евентов на выбранную неделю
                    this.getUserSlots();

                    //очищаем поля с датами периодов и ID слота
                    this.workDayStart = '';
                    this.workDayFinish = '';
                    this.seletedSlotId = '';
                }).catch(err => console.log(err));

            }
        },

        //копирование слотов с предыдущей недели
        copyPreviousWeekSlots: function () {
            if(this.seletedUserId) {
                axios.post(this.request_url,
                    {action:'copyPreviousWeekSlots',
                        filters: {
                            'firstWeekDay': this.firstWeekDay,
                            'lastWeekDay': moment(this.firstWeekDay).day(+7).format('YYYY-MM-DD'),
                            'seletedUserId': this.seletedUserId,
                        },
                    }).then(response => {

                    // console.log('copyPreviousWeekSlots: ',response.data)

                    if(response.data.errors.length > 0) console.log('v-ERROR:',response.data.errors);

                    //перезапуск функции получения евентов на выбранную неделю
                    this.getUserSlots();

                }).catch(err => console.log(err));
            }
        },

        //ТЕСТ попап добавления инфы в сохраненный слот
        openGspModal: function () {
            $('#gspModal').modal('show');
        },

    }
})
