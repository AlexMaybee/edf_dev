let app = new Vue({
    el: '#slot_calendar',
    data () {
        return {
            defaultDate: moment(new Date).format('YYYY-MM-DD'),
            defaultDateCustom: moment(new Date),
            events: [],
            filterValueLists: { //объект значений для фильтров
                users: [],
                slotStatusList: [],
                slotServiceList: [],
                slotTypeList: [],
                slotClubList: [],
                slotZonaList: [],
                slotSortedZoneList: [],
                slotLocationList: [],
                slotSortedLocationList: [],
                slotCheckBoxList: [],
                slotSortedUserList: [], //filterValueLists.slotSortedUserList //отсорированные по свведенным буквам пользователи
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
            seletedSlotId: '', //id слота, для удаления/обновления

            slotFilters: { //объект всех ролей gsp Modal
                ageFrom: '', //Возраст с
                ageTo: '', //Возраст до
                club: 0, //клуб в gsp-Modal
                durationMins: '', //длительность в gsp-Modal
                employee: {
                    id: 0,  //ID сотрудника в gsp-Modal, по итогу этот параметр будет осохранияться
                    name: '', //сотрудник в gsp-Modal
                },
                groupName: '', //название группы
                groupSize: '', //численность группы
                location: 0, //локация в gsp-Modal
                periodFrom: moment(new Date).format('YYYY-MM-DD'),
                periodTo: moment(new Date).format('YYYY-MM-DD'),
                type: 0,//тип (индивид., групп., сплит) в gsp-Modal
                zone: 0 //зона
            },
            // slotClub: '', //клуб в gsp-Modal
            // slotEmployee: {
            //     id: 0,  //ID сотрудника в gsp-Modal, по итогу этот параметр будет осохранияться
            //     name: '', //сотрудник в gsp-Modal
            // },
            // slotLocation: '', //локация в gsp-Modal
            // slotPeriodFrom: moment(new Date).format('YYYY-MM-DD'), //период с, gsp-Modal
            // slotPeriodTo: moment(new Date).format('YYYY-MM-DD'), //период с, gsp-Modal
            slotSelectedCheckboxes: [],//массив чекбоксов в gsp-Modal
            // slotType: '', //тип (индивид., групп., сплит) в gsp-Modal
            // slotZone: '', //зона в gsp-Modal
            slotValidateErrors: {  //объект с ошибками для каждого поля
                ageFrom: '',
                ageTo: '',
                checkboxes: '',
                club: '',
                durationMins: '',
                employee: '',
                groupName: '',
                groupSize: '',
                location: '',
                periodFrom: '',
                periodTo: '',
                type: '',
                zona: '',
            },
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




            testUsersList: [],



        }
    },

    // computed: {
    //     userValList: function () {
    //         this.gspUserFilter();
    //     },
    // },

    filters: {
        toUpperCase: function (string) {
            return string.toUpperCase();
        },


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

                    //данные для селектов попап
                    this.getGspModalSelectFields();

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
        // openWorkDayAddPopup: function (dateFrom,dateTo,slotId) {
        openWorkDayAddPopup: function (dateFrom,dateTo) {
            this.workDayStart = dateFrom;
            this.workDayFinish = dateTo;
            $('#workDayInCalendar').modal('show');

            // console.log('PopUp:',dateFrom,dateTo,this.seletedSlotId);
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

        //Popup добавления инфы в уже существующий слот, пока срабатывает и от кнопки "Popup добавления инфы в слот"
        openGspModal: function (dateFrom,dateTo,slotId) {
            this.workDayStart = dateFrom;
            this.workDayFinish = dateTo;
            this.seletedSlotId = slotId;

            //очистка ошибок
            this.resetValidateErrors(this.slotValidateErrors);

            if(this.seletedSlotId > 0)
                this.getSlotData();

            $('#gspModal').modal('show');
        },

        //получение данных выбранного слота
        getSlotData: function(){
            if(this.seletedSlotId > 0){
                axios.post(this.request_url,
                    {action:'getSlotById',
                        filters: {
                            'seletedSlotId':this.seletedSlotId,
                        },
                    }).then(response => {

                    console.log('getSlotData: ',response.data)

                    //если сохранилось, то закрываем попап
                    if(response.data.result){
                        this.slotFilters.ageFrom = response.data.result.AGE_FROM;
                        this.slotFilters.ageTo = response.data.result.AGE_TO;
                        this.slotFilters.club = response.data.result.CLUB_ID;
                        this.gspZoneFilterByClub();
                        this.slotFilters.employee = {
                            id: response.data.result.USER_ID,
                            name: response.data.result.USER_NAME,
                        };

                        this.slotFilters.groupName = response.data.result.GROUP_NAME;
                        this.slotFilters.groupSize = response.data.result.GROUP_SIZE;
                        this.slotFilters.zone = response.data.result.ZONE_ID;
                        this.gspLocationFilterByZone();
                        this.slotFilters.location = response.data.result.LOCATION_ID;
                        this.slotFilters.type = response.data.result.TYPE_ID;
                    }
                    else console.log('v-ERROR:',response.data.errors)
                }).catch(err => console.log(err));
            }
        },

        //удаление слота из календаря
        deleteSlot: function () {
            if(this.seletedUserId){

               // console.log('Del slot # ' + this.seletedSlotId);

                axios.post(this.request_url,
                    {action:'deleteSlot',
                        filters: {
                            'seletedSlotId':this.seletedSlotId,
                        },
                    }).then(response => {

                    // console.log('deleteSlot: ',response.data)

                    //если сохранилось, то закрываем попап
                    if(response.data.result){
                        $('#gspModal').modal('hide');
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

        //получение полей для селектов popup gsp
        getGspModalSelectFields: function(){
            if(this.seletedUserId) {
                console.log('get gsp-popup fields');

                axios.post(this.request_url,
                    {action:'getGspModalSelectFields'}).then(response => {

                        console.log('getGspModalSelectFields: ',response.data)
                        if(response.data.errors.length > 0) console.log('v-ERROR:',response.data.errors);

                        //селекты в gsp-popup
                        this.filterValueLists.slotStatusList = response.data.statusList;
                        this.filterValueLists.slotClubList = response.data.clubList;
                        this.filterValueLists.slotZonaList = response.data.zonaList;
                        this.filterValueLists.slotServiceList = response.data.serviceList;
                        this.filterValueLists.slotLocationList = response.data.locationList;
                        this.filterValueLists.slotTypeList = response.data.typeList;

                        //вывод таблицы с чекбоксами
                        this.filterValueLists.slotCheckBoxList = response.data.table;

                        console.log('slotCheckBoxList: ',this.filterValueLists.slotCheckBoxList)

                }).catch(err => console.log(err));
            }
        },


        //фильтр для поля сотрудник
        gspUserFilter: function () {
            let users = this.filterValueLists.users,
                inputStr = this.slotFilters.employee.name.toLowerCase();

            //обнуляем id пользователя каждый раз, как редактируется поле с именем
            this.slotFilters.employee.id = 0;
            if(inputStr.length > 0){
                this.filterValueLists.slotSortedUserList = users.filter(function (elem) {
                    if(inputStr==='') return true;
                    else return elem.NAME.toLowerCase().indexOf(inputStr) > -1;
                });
            }
            else this.filterValueLists.slotSortedUserList = [];
            // console.log('filter',this.filterValueLists.slotSortedUserList);
        },

        //выбор конкрутного сотрудника в поиске
        selectCurrentUserFromList: function (userObj) {
            this.slotFilters.employee = {
                id: userObj.ID, //сотрудник в gsp-Modal
                name: userObj.NAME,
            };

            //обнуляем массив, чтобы скрыть поле с вариантами
            this.filterValueLists.slotSortedUserList = [];
            // console.log('select empl:', userObj);
            // console.log('select empl:',  this.slotFilters.employee);
        },

        gspZoneFilterByClub: function () {
            let zones = this.filterValueLists.slotZonaList,
                selectedClub = this.slotFilters.club;

            this.slotFilters.zone = 0;
            this.slotFilters.location = 0;

            if(selectedClub > 0){
                this.filterValueLists.slotSortedZoneList = zones.filter(function (elem) {
                    if(selectedClub==='') return true;
                    else return elem.PROPERTY_307_VALUE.indexOf(selectedClub) > -1;
                });
            }
            else {
                this.filterValueLists.slotSortedZoneList = [];
                this.filterValueLists.slotSortedLocationList = [];
            }

            // console.log('filtered zones:',this.filterValueLists.slotSortedZoneList);
        },

        gspLocationFilterByZone: function () {
            let locations = this.filterValueLists.slotLocationList,
                selectedZone = this.slotFilters.zone;

            this.slotFilters.location = 0;

            if(selectedZone > 0){
                this.filterValueLists.slotSortedLocationList = locations.filter(function (elem) {
                    if(selectedZone==='') return true;
                    else return elem.PROPERTY_308_VALUE.indexOf(selectedZone) > -1;
                });
            }
            else this.filterValueLists.slotSortedLocationList = [];

            console.log('filtered locations:',this.filterValueLists.slotSortedLocationList);
        },

        //сборс ошибок у выбранного объекта с ошибками
        resetValidateErrors: function(obj){
            $.each(obj, function (key,val) {
                obj[key] = '';
                // console.log(key,val);
            });
        },

        //проверяем, есть ли тект ошибок в выбранном объекте
        countErrorsInObject: function(obj){
            let num = 0;
            $.each(obj, function (key,val) {
                if(val.length > 0)
                    num++;
                // console.log(key,val);
            });
            console.log('err num:',num);
            return num;
        },

        //Валидация попапа
        validateGspModal: function () {
            let dateRegExp = /^[\d]{4}-[\d]{2}-[\d]{2}$/,
                integerReqExp = /^[\d]+$/;


            console.log('regexp',integerReqExp);


            this.resetValidateErrors(this.slotValidateErrors);

            // console.log('slot ID:',this.seletedSlotId);

            // if(this.slotFilters.type <= 0 )
            if(!integerReqExp.test(this.slotFilters.type) ||
                (integerReqExp.test(this.slotFilters.type) && this.slotFilters.type <= 0 ))
                this.slotValidateErrors.type = 'Выберите Тип!';

            if(!dateRegExp.test(this.slotFilters.periodFrom.trim()))
                this.slotValidateErrors.periodFrom = 'Укажите начало периода!';

            if(!dateRegExp.test(this.slotFilters.periodTo.trim()))
                this.slotValidateErrors.periodTo = 'Укажите окончание периода!';

            // if(this.slotFilters.club <= 0 )
            if(!integerReqExp.test(this.slotFilters.club) ||
                (integerReqExp.test(this.slotFilters.club) && this.slotFilters.club <= 0 ))
                this.slotValidateErrors.club = 'Выберите Клуб!';

            // if(this.slotFilters.zone <= 0 )
            if(!integerReqExp.test(this.slotFilters.zone) ||
                (integerReqExp.test(this.slotFilters.zone) && this.slotFilters.zone <= 0 ))
                this.slotValidateErrors.zona = 'Выберите Зону!';

            // if(this.slotFilters.location <= 0 )
            if(!integerReqExp.test(this.slotFilters.location) ||
                (integerReqExp.test(this.slotFilters.location) && this.slotFilters.location <= 0 ))
                this.slotValidateErrors.location = 'Выберите Локацию!';

            // if(this.slotFilters.ageFrom <= 0 )
            if(!integerReqExp.test(this.slotFilters.ageFrom) ||
                (integerReqExp.test(this.slotFilters.ageFrom) && this.slotFilters.ageFrom <= 0 ))
                this.slotValidateErrors.ageFrom = 'Укажите начальный возраст!';

            // if(this.slotFilters.ageTo <= 0 )
            if(!integerReqExp.test(this.slotFilters.ageTo) ||
                (integerReqExp.test(this.slotFilters.ageTo) && this.slotFilters.ageTo <= 0 ))
                this.slotValidateErrors.ageTo = 'Укажите конечный возраст!';

            // if(this.slotFilters.groupSize <= 0 )
            if(!integerReqExp.test(this.slotFilters.groupSize) ||
                (integerReqExp.test(this.slotFilters.groupSize) && this.slotFilters.groupSize <= 0 ))
                this.slotValidateErrors.groupSize = 'Укажите Численность группы!';

            // if(this.slotFilters.durationMins <= 0 )
            if(!integerReqExp.test(this.slotFilters.durationMins) ||
                (integerReqExp.test(this.slotFilters.durationMins) && this.slotFilters.durationMins <= 0 ))
                this.slotValidateErrors.durationMins = 'Укажите Длительность в минутах!';

            // if(this.slotFilters.employee.id <= 0 )
            if(!integerReqExp.test(this.slotFilters.employee.id) ||
                (integerReqExp.test(this.slotFilters.employee.id) && this.slotFilters.employee.id <= 0 ))
                this.slotValidateErrors.employee = 'Выберите Сотрудника!';

            if(this.slotFilters.groupName <= 0 )
                this.slotValidateErrors.groupName = 'Укажите Название группы!';

            if(this.slotSelectedCheckboxes.length <= 0 )
                this.slotValidateErrors.checkboxes = 'Не выбран ни один чекбокс!';
            // console.log('chbx',this.slotSelectedCheckboxes.length);


            if(this.countErrorsInObject(this.slotValidateErrors) == 0) {
                console.log('Save in DB!!!');
                if (this.seletedSlotId > 0) //это редактирование текущего элемента
                    this.updateSlot();

                //иначе создание какого-то расписания и кучу слотов для выбранного сотрудника
                else
                    this.addFilledSlotsBetweenPeriod();
            }

            //groupName
            console.log('dates Gsp:',this.slotFilters);
            console.log('Validation Gsp:',this.slotValidateErrors);
        },



        //создание слотов за определенный период по отмеченным чекбоксам
        addFilledSlotsBetweenPeriod: function(){
            console.log('ФИЛЬТРЫ: для создания по чекбоксам',this.slotFilters);
            console.log('ЧЕКБОКСЫ: для создания по чекбоксам',this.slotSelectedCheckboxes);

            axios.post(this.request_url,
                {action:'addFilledSlotsBetweenPeriod',
                    filters: this.slotFilters,
                    // slotId: this.seletedSlotId,
                    // workDayStart:this.workDayStart,
                    // workDayFinish: this.workDayFinish,
                    checkboxes: this.slotSelectedCheckboxes,
                }).then(response => {

                console.log('Period Chbxs: ',response.data)

                if(response.data.errors.length > 0) console.log('v-ERROR:',response.data.errors);
                else{

                    // $('#gspModal').modal('hide');
                    // this.resetGspPopupFields();
                    //
                    // this.workDayStart = '';
                    // this.workDayFinish = '';
                    // this.seletedSlotId = '';
                }

                //перезапуск функции получения евентов на выбранную неделю
                // this.getUserSlots();

            }).catch(err => console.log(err));

        },



        //запись выбранных данных в существующем слоте
        updateSlot: function () {
            axios.post(this.request_url,
                {action:'updateSlot',
                    filters: this.slotFilters,
                    slotId: this.seletedSlotId,
                    workDayStart:this.workDayStart,
                    workDayFinish: this.workDayFinish,
                    // checkboxes: this.slotSelectedCheckboxes,
                }).then(response => {

                console.log('updateSlot: ',response.data)

                if(response.data.errors.length > 0) console.log('v-ERROR:',response.data.errors);
                else{

                    $('#gspModal').modal('hide');
                    this.resetGspPopupFields();

                    this.workDayStart = '';
                    this.workDayFinish = '';
                    this.seletedSlotId = '';
                }

                //перезапуск функции получения евентов на выбранную неделю
                this.getUserSlots();

            }).catch(err => console.log(err));
        },

        //обнуляем данные полей после успешного сохранения
        resetGspPopupFields: function () {
            this.slotFilters = { //объект всех ролей gsp Modal
                ageFrom: '', //Возраст с
                ageTo: '', //Возраст до
                club: 0, //клуб в gsp-Modal
                durationMins: '', //длительность в gsp-Modal
                employee: {
                    id: 0,  //ID сотрудника в gsp-Modal, по итогу этот параметр будет осохранияться
                    name: '', //сотрудник в gsp-Modal
                },
                groupName: '', //название группы
                groupSize: '', //численность группы
                location: 0, //локация в gsp-Modal
                periodFrom: moment(new Date).format('YYYY-MM-DD'),
                periodTo: moment(new Date).format('YYYY-MM-DD'),
                type: 0,//тип (индивид., групп., сплит) в gsp-Modal
                zone: 0 //зона
            };
        },

        changeDateByDragNDrop: function (start,finish,id) {
            axios.post(this.request_url,
                {action:'changeDateByDragNDrop',
                    slotId: id,
                    workDayStart: start,
                    workDayFinish: finish,
                }).then(response => {

                // console.log('changeDateByDragNDrop: ',response.data)
                if(response.data.errors.length > 0) console.log('v-ERROR:',response.data.errors);
                this.getUserSlots();
            }).catch(err => console.log(err));

        },

        //4 функуции очистки полей от ненужных символов
        checkAgeFrom: function(){
            this.slotFilters.ageFrom = this.deleteStringSymbols(this.slotFilters.ageFrom,'float');
        },
        checkAgeTo: function(){
            this.slotFilters.ageTo = this.deleteStringSymbols(this.slotFilters.ageTo,'float');
        },
        checkGroupSize: function(){
            this.slotFilters.groupSize = this.deleteStringSymbols(this.slotFilters.groupSize);
        },
        //DEL?
        checkdurationMins: function(){
            this.slotFilters.durationMins = this.deleteStringSymbols(this.slotFilters.durationMins);
        },


        deleteStringSymbols: function (model,float=false) {
            (float == 'float')
                ? model = model.replace(/[^,.\d]$/g,'').replace(',','.')
                : model = model.replace(/[^\d]$/g,'');
            return model;
        },
    }
});
