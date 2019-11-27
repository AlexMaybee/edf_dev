let app = new Vue({
    // components: {
    //     Multiselect: window.VueMultiselect.default,
    //     Loading: VueLoading
    // },
    el: '#slot_calendar',

    data () {
        return {
            curUserId: 1,
            defaultDate: moment(new Date).format('YYYY-MM-DD'),
            defaultDateCustom: moment(new Date),
            day_of_week: moment(new Date).format('dddd'),
            editable: true,
            events: [],
            isAdmin: 0,
            resources: [],
            settings: {minTime: '07:00:00', maxTime: '22:30:00', slotDuration: '00:30:00', slotMinute: '30'},
            selectedFilter: {

                firstWeekDay: '',//передаем дату первого дня недели
                slotDateFrom: '', //иначе в попапе не выберет нужную дату, а будет дд.мм.ггг
                slotDateTo: '', //иначе в попапе не выберет нужную дату, а будет дд.мм.ггг

                //usersArr: [],
                // curUserId: 0,
            },
            request_url: '/local/components/crmgenesis/slots.component/ajax.php',

        }
    },

    mounted() {

        $(this.$refs.vuemodaladdDealToCalendar).on("hidden.bs.modal", this.doSomethingOnHidden);
        $(this.$refs.vuemodaladdDealToCalendar).on("shown.bs.modal", this.doSomethingOnShow);

        //при загрузке страницы вычисляем дату понедельника текущей недели, а при --
        // -- переключении календаря получаем из него (Event) - FilterEventsDate
        this.selectedFilter['firstWeekDay'] = this.getCurWeekendMondayDate(this.defaultDate);
        // this.getCurWeekendMondayDate(moment(new Date(2020,0,3)));


        //проверка роли админа + получение всех значений для фильтра
        this.checkRoleAndGetFilters();

        console.log('FILTERS M',this.selectedFilter);

        //загрузка данных по евентам
        this.getEventsByFilter();


    },

    watch: {

    },

    events: {},

    methods: {

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

        //функция смены даты
        FilterEventsDate: function(date) {
            if(date) {

                //обновляем дату первого дня недели при переходе в календаре
                this.selectedFilter.firstWeekDay = moment(date).format('YYYY-MM-DD');
                this.getEventsByFilter();
                // console.log('updated!!!',this.selectedFilter);
            }
        },

        //проверка isAdmin + получение значений для отображения в фильтры
        checkRoleAndGetFilters: function () {

            let self = this,
                headers = {'Content-Type': 'application/json'};

            axios.post(this.request_url,{action:'checkRoleAndGetFilters'}, {headers}).then(response => {

                // console.log(response.data);

                if(response.data.isAdmin > 0) {
                    self.isAdmin = response.data.isAdmin;
                    self.selectedFilter.usersArr = response.data.users;
                }
                if(response.data.curUserId > 0)
                    self.curUserId = response.data.curUserId;
            }).catch(err => console.log(err));
        },

        //запрос евентов пользователя
        getEventsByFilter: function(){
            let self = this;
            this.selectedFilter.curUserId = this.curUserId;

            headers = {'Content-Type': 'application/json'};
            // if(this.selectedFilter.curUserId > 0){
                axios.post(this.request_url,
                    {action:'getEventsByFilter',filters: this.selectedFilter},
                    {headers}).then(response => {

                        if(response.data.result.length > 0) self.events = response.data.result;
                        console.log('getEventsByFilter',response.data);

                    }).catch(err => console.log(err));
            // }

        },


        openEventPopup: function (dateFrom,dateTo) {
            this.selectedFilter.slotDateFrom = dateFrom;
            this.selectedFilter.slotDateTo = dateTo;

            console.log('filters',this.selectedFilter);

            $('#addEventToCalendar').modal('show');


        },

        addEventToCalendar: function () {
            let self = this,
                headers = {'Content-Type': 'application/json'};
            if(this.selectedFilter.curUserId > 0){
                axios.post(this.request_url,
                    {action:'addEventToCalendar',filters: this.selectedFilter},
                    {headers}).then(response => {

                        console.log(response.data)

                        //если сохранилось, то закрываем попап
                        if(response.data.result){
                            $('#addEventToCalendar').modal('hide');
                            //перезапуск функции получения евентов на выбранную неделю
                        }
                        else console.log('v-ERROR:',response.data.errors)

                    }).catch(err => console.log(err));
            }
        },


    }
})
