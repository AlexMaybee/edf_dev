let app = new Vue({
    // components: {
    //     Multiselect: window.VueMultiselect.default,
    //     Loading: VueLoading
    // },
    el: '#slot_calendar',

    data () {
        return {
            events: [],
            name: 'map',
            defaultDate: moment(new Date).format('YYYY-MM-DD'),
            defaultDateCustom: moment(new Date),
            day_of_week: moment(new Date).format('dddd'),
            resources: [],
            settings: {minTime: '07:00:00', maxTime: '22:30:00', slotDuration: '00:30:00', slotMinute: '30'},
            selectedFilter: {},
            editable: true,
            request_url: '/local/components/crmgenesis/slots.component/ajax.php',
            visible: false,
            categoryList: [],
            categoryListStage: [],
            users: [],
            data_users: [],
            users_id: [],
            data_deals: [],
            deals: [],
            fieldsDeal: {},
            fieldsDealInstallation: {},
            fieldsCategoryDeal: {},
            fieldsDealId: {},
            settings_map: {},
            place: {},
            coords_zoom: [],
            fieldsDealIdInst: {},
            test: 'n',
            meeting_status_id: 256,
            meeting_installation_id: 341,
            meeting_status_list: [],
            meeting_status_deal: false,
            inst_status_deal: false,
            meeting_status_deal_but: true,
            install_status_deal_but: true,
            show_but_inst: false,
            checkCategory: false,
            category_id: null,
            path: '//maps.googleapis.com/maps/api/js?key=AIzaSyA3r8TQakO67thKLESvevnxAZtAXNb_dvo&libraries=places',


            //slots from 25.11.2019


            isAdmin: 0,
            filters: {
                dateFrom: '',
                dateTo: '',
                userID: 0,
            },
            filterValLists: {
                users: [],
            },




        }
    },

    mounted() {

        $(this.$refs.vuemodaladdDealToCalendar).on("hidden.bs.modal", this.doSomethingOnHidden);
        $(this.$refs.vuemodaladdDealToCalendar).on("shown.bs.modal", this.doSomethingOnShow);


        //проверка роли админа + получение всех значений для фильтра
        this.checkRoleAndGetFilters();


    },

    watch: {},

    events: {},

    methods: {

        //my from 25.11.2019
        checkRoleAndGetFilters: function () {

            let self = this,
                headers = {'Content-Type': 'application/json'};

            axios.post(this.request_url,{action:'checkRoleAndGetFilters'}, {headers}).then(response => {
                console.log(response.data);
                if(response.data.isAdmin > 0) {
                    self.isAdmin = response.data.isAdmin;
                    self.filterValLists.users = response.data.users;
                }
                if(response.data.curUserId > 0)
                    self.filters.userID = response.data.curUserId;

                    // $('#addDealToCalendar_2').modal('show');
            }).catch(err => console.log(err));
        },

        selectSlot: function (dateFrom,dateTo) {
            this.filters.dateFrom = dateFrom;
            this.filters.dateTo = dateTo;
            $('#selectSlot').modal('show');
        },

        reserveSlot: function () {
            let self = this,
                headers = {'Content-Type': 'application/json'};
            if(this.filters.userID > 0){
                axios.post(this.request_url,
                    {
                        action:'reserveWorkHoursToSlot',
                        userID: self.filters.userID,
                        dateFrom: this.filters.dateFrom,
                        dateTo: this.filters.dateTo,
                    },
                    {headers}).then(response => {
                    console.log(response.data);}).catch(err => console.log(err));
            }
        },


    }
})
