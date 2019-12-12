let app = new Vue({
    el: '#slot_calendar',
    data () {
        return {
            defaultDate: moment(new Date).format('YYYY-MM-DD'),
            defaultDateCustom: moment(new Date),
            events: [],
            filterValueLists: { //объект значений для фильтров
                users: [],
                slotCheckBoxList: [],
                slotClubList: [],
                slotGroupTrainingList: [], //Список групповых тренировок
                slotLocationList: [],


                slotRequestedContacts: [], //список контактов для отображения, поиск по имени/телефону, запросом


                slotServiceList: [],
                slotSortedLocationList: [],
                slotSortedTrainingGroupList: [],

                slotSortedZoneList: [],
                slotStatusList: [],
                slotTypeList: [],
                slotZonaList: [],

                slotSortedUserList: [], // УДАЛИТЬ???? ИЛИ ПЕРЕДЕЛАТЬ НА КОМПОНЕНТотсорированные по свведенным буквам пользователи
                sortedUserList: [], //filterValueLists.slotSortedUserList //отсорированные по свведенным буквам пользователи

            },
            firstWeekDay: '',
            isAdmin: false,
            info: { //используем для компонента-всплывашки-предупреждашки
                buttonSuccessText: '',
                buttonRejectText: '',
                modalTitle: '',
                popupClass: '',
                rejectFunction: '',
                successFunction: '',
                text: '',
            },
            lang: {
                weekHourText: '',
                monthHourText: '',
                measureText: '',
            },
            prevWeekSlotsNum: 0,
            resources: [],
            request_url: '/local/components/crmgenesis/slots.component/ajax.php',
            settings: {minTime: '07:00:00', maxTime: '22:30:00', slotDuration: '00:60:00', slotMinute: '60'},
            selectedUser: { //Главный фильтр пользователей
                name: '',
                id: 0,
            },

            seletedSlotId: '', //id слота, для удаления/обновления

            slotFilters: { //объект всех ролей gsp Modal
                ageFrom: '', //Возраст с
                ageTo: '', //Возраст до


                // currentContact: { //объект поиска контакта по строке
                //     'name': '',
                //     'id': 0,
                // }, // в поле ввода
                contacts: [], //массив выбранных контактов, множ.
                // contactsLimit: 1, //макс. кол-во контактов


                club: 0, //клуб в gsp-Modal
                durationMins: 60, //длительность в gsp-Modal
                employee: {
                    id: 0,  //ID сотрудника в gsp-Modal, по итогу этот параметр будет осохранияться
                    name: '', //сотрудник в gsp-Modal
                },
                groupName: '', //название группы
                groupId: 0, //это для select групп
                group: {
                    name: '', //name
                    id: 0, //id
                },
                groupSize: '', //численность группы
                location: 0, //локация в gsp-Modal
                periodFrom: moment(new Date).format('YYYY-MM-DD'),
                periodTo: moment(new Date).format('YYYY-MM-DD'),
                slotShowGroupNameFieldDefault: 10005000, //Значение для отображения поля для ввода названия Группы => создания новой
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
                contacts: '',
                club: '',
                durationMins: '',
                employee: '',
                groupId: '',
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

    //подключение компонента библиотеки MULTISELECt от Вани
    components: {
        Multiselect: window.VueMultiselect.default,
        // Loading: VueLoading
    },

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

                console.log('User Data Result:',response.data);

                if(Object.keys(response.data.selectedUser).length > 0) {

                    this.selectedUser = response.data.selectedUser;

                    //потом заменить, если что, переменную this.slotFilters.employee на this.selectedUser
                    this.slotFilters.employee = this.selectedUser;

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
                    // console.log('seleted User Id:',this.selectedUser,this.isAdmin)

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
            if(this.selectedUser.id > 0) {
                axios.post(this.request_url,
                    {
                        action: 'getUserSlots',
                        filters:
                            {
                                'firstWeekDay': this.firstWeekDay,
                                'lastWeekDay': moment(this.firstWeekDay).day(+7).format('YYYY-MM-DD'),
                                'selectedUserId': this.selectedUser.id,
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
            if(this.selectedUser.id > 0){
                axios.post(this.request_url,
                    {action:'addWorkPeriodToCalendar',
                        filters: {
                            'workDayStart':this.workDayStart,
                            'workDayFinish': this.workDayFinish,
                            'selectedUserId': this.selectedUser.id,
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
                        this.gspGroupFilterByLocation();
                        this.slotFilters.type = response.data.result.TYPE_ID;
                    }
                    else console.log('v-ERROR:',response.data.errors)
                }).catch(err => console.log(err));
            }
        },

        //удаление слота из календаря
        deleteSlot: function () {
            if(this.selectedUser.id > 0){

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

        //получение полей для селектов popup gsp
        getGspModalSelectFields: function(){
            if(this.selectedUser.id > 0) {
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

                        this.filterValueLists.slotGroupTrainingList = response.data.groupTrainingList;

                        //вывод таблицы с чекбоксами
                        this.filterValueLists.slotCheckBoxList = response.data.table;

                        console.log('slotCheckBoxList: ',this.filterValueLists.slotCheckBoxList)

                }).catch(err => console.log(err));
            }
        },




        //12.12 фильтр для поля сотрудник
        userFilter: function () {
            let users = this.filterValueLists.users,
                inputStr = this.selectedUser.name.toLowerCase();

            //обнуляем id пользователя каждый раз, как редактируется поле с именем
            this.selectedUser.id = 0;
            if(inputStr.length > 0){
                this.filterValueLists.sortedUserList = users.filter(function (elem) {
                    if(inputStr==='') return true;
                    else return elem.NAME.toLowerCase().indexOf(inputStr) > -1;
                });
            }
            else this.filterValueLists.sortedUserList = [];
            // console.log('filter',this.filterValueLists.sortedUserList);
        },

        //12.12 - Выбор конкретного сотрудника в поиске главного фильтра
        selectCurrentUserFromListMain: function (userObj) {
            this.selectedUser = {
                id: userObj.ID, //сотрудник в gsp-Modal
                name: userObj.NAME,
            };

            //передача текущего пользователя в gspModal
            this.slotFilters.employee = this.selectedUser;

            //обнуляем массив, чтобы скрыть поле с вариантами
            this.filterValueLists.sortedUserList = [];

            //перезапуск функции получения евентов на выбранную неделю
            this.getUserSlots();

            console.log('selected user:', this.selectedUser);
        },


        //-----------------фильтр для поля сотрудник gspModal--------------------!!!
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
        //-----------------фильтр для поля сотрудник gspModal--------------------!!!

        //-----------------выбор конкретного сотрудника в поиске gspModal ------------!!!
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
        //-----------------выбор конкретного сотрудника в поиске gspModal ------------!!!





        gspZoneFilterByClub: function () {
            let zones = this.filterValueLists.slotZonaList,
                selectedClub = this.slotFilters.club;

            this.slotFilters.zone = 0;
            this.slotFilters.location = 0;
            this.slotFilters.groupId = 0;

            if(selectedClub > 0){
                this.filterValueLists.slotSortedZoneList = zones.filter(function (elem) {
                    if(selectedClub==='') return true;
                    else return elem.PROPERTY_307_VALUE.indexOf(selectedClub) > -1;
                });
            }
            else {
                this.filterValueLists.slotSortedZoneList = [];
                this.filterValueLists.slotSortedLocationList = [];
                this.filterValueLists.slotSortedTrainingGroupList = [];
            }

            // console.log('filtered zones:',this.filterValueLists.slotSortedZoneList);
        },

        gspLocationFilterByZone: function () {
            let locations = this.filterValueLists.slotLocationList,
                selectedZone = this.slotFilters.zone;

            this.slotFilters.location = 0;
            this.slotFilters.groupId = 0;

            if(selectedZone > 0){
                this.filterValueLists.slotSortedLocationList = locations.filter(function (elem) {
                    if(selectedZone === '') return true;
                    else return elem.PROPERTY_308_VALUE.indexOf(selectedZone) > -1;
                });
            }
            else {
                this.filterValueLists.slotSortedLocationList = [];
                this.filterValueLists.slotSortedTrainingGroupList = [];
            }

            console.log('filtered locations:',this.filterValueLists.slotSortedLocationList);
        },

        //сортировка групп по локации (или хз, по чем они там захотят!!!
        gspGroupFilterByLocation: function(){
          let groups = this.filterValueLists.slotGroupTrainingList,
              selectedLocation = this.slotFilters.location;

          // console.log('GROUP SORT',selectedLocation);

            this.slotFilters.groupId = 0;

            if(selectedLocation > 0){
                this.filterValueLists.slotSortedTrainingGroupList = groups.filter(function (elem) {
                    if(selectedLocation === '') return true;
                    else return elem.PROPERTY_313_VALUE.indexOf(selectedLocation) > -1;
                });
            }
            else this.filterValueLists.slotSortedTrainingGroupList = [];
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
                (integerReqExp.test(this.slotFilters.ageFrom) && this.slotFilters.ageFrom < 0 ))
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

            if(this.slotFilters.groupId <= 0)
                this.slotValidateErrors.groupId = 'Выберите группу!';

            if(this.slotFilters.groupId == this.slotFilters.slotShowGroupNameFieldDefault
            && this.slotFilters.groupName <= 0 )
                this.slotValidateErrors.groupName = 'Укажите Название группы!';
            // if(this.slotFilters.groupName <= 0 )
            //     this.slotValidateErrors.groupName = 'Укажите Название группы!';

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

                    $('#gspModal').modal('hide');
                    // this.resetGspPopupFields();
                    // this.slotSelectedCheckboxes = [];

                    // this.workDayStart = '';
                    // this.workDayFinish = '';
                    // this.seletedSlotId = '';
                }

                //перезапуск функции получения евентов на выбранную неделю
                this.getUserSlots();

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
                contacts: [],
                club: 0, //клуб в gsp-Modal
                durationMins: 60, //длительность в gsp-Modal
                employee: {
                    id: 0,  //ID сотрудника в gsp-Modal, по итогу этот параметр будет осохранияться
                    name: '', //сотрудник в gsp-Modal
                },
                groupId: 0,
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
            this.slotFilters.ageFrom = this.deleteStringSymbols(this.slotFilters.ageFrom);
        },
        checkAgeTo: function(){
            this.slotFilters.ageTo = this.deleteStringSymbols(this.slotFilters.ageTo);
        },
        checkGroupSize: function(){
            this.slotFilters.groupSize = this.deleteStringSymbols(this.slotFilters.groupSize);
        },
        //DEL?
        checkdurationMins: function(){
            this.slotFilters.durationMins = this.deleteStringSymbols(this.slotFilters.durationMins);
        },


        deleteStringSymbols: function (model) {
            // (float == 'float')
            //     ? model = model.replace(/[^,.\d]$/g,'').replace(',','.')
            //     : model = model.replace(/[^\d]$/g,'');
            // return model;
            return model = model.replace(/[^\d]$/g,'');
        },

        //наджатие на "копировать предыд. неделю" + показываем попап
        clickOnCopyPrevWeek: function () {
            this.info = {
                buttonSuccessText: "Копировать",
                buttonRejectText: "Отмена",
                modalTitle: "Внимание!",
                popupClass: 'alert alert-warning',
                rejectFunction: this.clearInfoParams,
                successFunction: this.copyPreviousWeekSlots,
                text: "Данное действие может привести к задвоению слотов. Выполнить операцию?",
            };

            // console.log(this.info);

            $('#infoModalCenter').modal('show');
        },

        //копирование слотов с предыдущей недели
        copyPreviousWeekSlots: function () {
            if(this.selectedUser.id > 0) {
                axios.post(this.request_url,
                    {action:'copyPreviousWeekSlots',
                        filters: {
                            'firstWeekDay': this.firstWeekDay,
                            'lastWeekDay': moment(this.firstWeekDay).day(+7).format('YYYY-MM-DD'),
                            'selectedUserId': this.selectedUser.id,
                        },
                    }).then(response => {

                    // console.log('copyPreviousWeekSlots: ',response.data)

                    if(response.data.errors.length > 0) console.log('v-ERROR:',response.data.errors);
                    $('#infoModalCenter').modal('hide');
                    //перезапуск функции получения евентов на выбранную неделю
                    this.getUserSlots();

                }).catch(err => console.log(err));
            }
        },

        //очистка параметров info, применять всегда при отмене!!!
        clearInfoParams: function () {
            this.resetValidateErrors(this.info);
        },

        //поиск Контактов по имени
        // getContactsToFilter: function () {
        //
        //
        //     if(this.slotFilters.currentContact.name.length > 0){
        //         // this.filterValueLists.slotSortedTrainingGroupList = groups.filter(function (elem) {
        //         //     if(selectedLocation === '') return true;
        //         //     else return elem.PROPERTY_313_VALUE.indexOf(selectedLocation) > -1;
        //         // });
        //
        //         console.log('Yo, Multiselect!', this.slotFilters.currentContact.name);
        //
        //
        //         axios.post(this.request_url,
        //             {action:'getContactsByNameOrPhone',
        //                 filters: {
        //                     'contactName': this.slotFilters.currentContact.name,
        //                 },
        //             }).then(response => {
        //
        //             console.log('CONTACTS: ',response.data.contactList)
        //
        //             if(response.data.error.length > 0) console.log('v-ERROR:',response.data.error);
        //             this.filterValueLists.slotRequestedContacts = response.data.contactList;
        //
        //
        //         }).catch(err => console.log(err));
        //
        //     }
        //     else this.filterValueLists.slotRequestedContacts = [];
        // },
        //
        // selectCurrentContactFromList: function (contactObj) {
        //   console.log('selectCurrentContactFromList',contactObj);
        //     this.slotFilters.contacts.push(contactObj);
        //     this.slotFilters.currentContact.name = '';
        //     this.filterValueLists.slotRequestedContacts = [];
        //
        // },

        testGetContacts: function (value) {

            console.log('value: ',value)


                axios.post(this.request_url,
                    {action:'getContactsByNameOrPhone',
                        filters: {
                            'contactName': value,
                        },
                    }).then(response => {

                    console.log('CONTACTS: ',response.data.contactList)

                    if(response.data.error.length > 0) console.log('v-ERROR:',response.data.error);
                    this.filterValueLists.slotRequestedContacts = response.data.contactList;
                }).catch(err => console.log(err));
        },

        showContactsMaxLimitError: function (value) {
            return `Нельзя выбрать больше ${value} значений!`;
        }

    }
});
