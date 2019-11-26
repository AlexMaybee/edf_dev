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

            filters: {
                employee: false,
            },
            filterValLists: {
                employees: [],
            },


        }
    },

    mounted() {

        $(this.$refs.vuemodaladdDealToCalendar).on("hidden.bs.modal", this.doSomethingOnHidden);
        $(this.$refs.vuemodaladdDealToCalendar).on("shown.bs.modal", this.doSomethingOnShow);


        //получение всех значений для фильтра
        this.getValuesListForFilters();


    },



    watch: {
        users: function() {
            if (this.users.length > 0) {
                this.selectedFilter.ASSIGNED_BY_ID = [];
                this.resources = [];
                for(let i = 0; i < this.users.length; i++) {
                    let params = {
                        id : this.users[i].id,
                        title: this.users[i].name,
                        rendering: 'background'
                    }
                    this.resources.push(params);
                    this.selectedFilter.ASSIGNED_BY_ID.push(this.users[i].id);
                }
                this.filterElement();
            } else {
                this.filterCategory();
            }

        },

        defaultDate: function () {
            this.defaultDateCustom = moment(this.defaultDate);
            this.day_of_week = moment(this.defaultDate).format('dddd');
            this.filterElement();
            // self.cal.fullCalendar( 'next' );
        },

        fieldsDealId: function() {
            if(this.fieldsDealId) {
                // this.fieldsDeal.DEAL_ID = this.fieldsDealId.id;
                // this.getDealId(this.fieldsDealId.id, false, false, false);
            }
        },
    },

    events: {

    },

    methods: {


        FilterEventsDate(date) {
            if(date) {
                this.selectedFilter['VIEW_DAY'] = date;
                this.day_of_week = moment(date).format('dddd');
                this.getDealList();
            }
        },

        filterElement() {
            if (typeof google === 'object' && typeof google.maps === 'object') {

            } else {
                if (this.selectedFilter.SHOW_MAP) {
                    //this.includeScript(this.path);
                }
            }

            this.getDealList();
            this.getDealListFilter('');
        },

        filterElementMap() {
            is_operator = false;
            if (typeof google === 'object' && typeof google.maps === 'object') {
                if (this.selectedFilter.SHOW_MAP) {
                    //this.includeScript(this.path);
                    is_operator = true;
                } else {
                    this.clearBox('map');
                }
            } else {
                if (this.selectedFilter.SHOW_MAP) {
                    //this.includeScript(this.path);
                    is_operator = true;
                } else {
                    this.clearBox('map');
                }
            }
            this.showMap();
            this.getDealList();
            this.getDealListFilter('');
        },

        clearBox(elementID) {
            document.getElementById(elementID).innerHTML = "";
        },

        filterCategory() {
            if(this.selectedFilter.CATEGORY_ID || this.selectedFilter.CATEGORY_ID == 0) {
                if(is_operator) {
                    this.selectedFilter.SHOW_MAP = is_operator;
                }
                this.category_id = this.selectedFilter.CATEGORY_ID;
                this.getUsersList(this.selectedFilter.CATEGORY_ID);
            }
        },

        includeScript(url) {
            var script = document.createElement('script');
            script.src = url;
            document.getElementsByTagName('head')[0].appendChild(script);
        },

        showMap() {
            let headers = {'Content-Type': 'application/json'}
            axios.post(this.request_url, [{action: 'getUpdateFieldsShowMap', value: this.selectedFilter.SHOW_MAP}], {headers}).then(response => {
                console.log(response.data);
            })
                .catch(err => console.log(err));
        },

        getCategoryList() {
            let headers = {'Content-Type': 'application/json'}
            axios.post(this.request_url, [{action: 'getCategoryList'}], {headers}).then(response => {
                this.categoryList = response.data;
            })
                .catch(err => console.log(err));
        },

        getCategoryStageList() {
            let headers = {'Content-Type': 'application/json'}
            axios.post(this.request_url, [{action: 'getCategoryStageList'}], {headers}).then(response => {
                this.categoryListStage = response.data;
            })
                .catch(err => console.log(err));
        },

        getUsersList(group_id) {
            let self = this;
            self.resources = [];
            self.settings = {};
            self.users_id = [];
            let headers = {'Content-Type': 'application/json'}
            axios.post(this.request_url, [{action: 'getUsersList', group_id: group_id}], {headers}).then(response => {
                this.data_users = response.data.res;
                self.selectedFilter.ASSIGNED_BY_ID = [];
                if(response.data.res.length > 0) {
                    for (i = 0; i < response.data.res.length; i++) {
                        let params = {
                            id : response.data.res[i].id,
                            title: response.data.res[i].name,
                            rendering: 'background'
                        }
                        self.resources.push(params);
                        self.users_id.push(response.data.res[i].id);
                        self.selectedFilter.ASSIGNED_BY_ID.push(response.data.res[i].id);
                    }

                    if(response.data.res_shedule) {
                        self.settings = response.data.res_shedule;
                    }

                    if(response.data.settings_map.ID) {
                        self.settings_map = {lat: response.data.settings_map.PROPERTY_LAT_VALUE, lng: response.data.settings_map.PROPERTY_LNG_VALUE, zoom: response.data.settings_map.PROPERTY_ZOOM_VALUE, mid: response.data.settings_map.PROPERTY_MID_VALUE};
                        var urlParams = new URLSearchParams(window.location.search);
                        if(urlParams.get('DEAL_ID')) {
                            if(urlParams.get('DEAL_ID') > 0) {
                                var check_key = this.findObjectByKey(this.events, urlParams.get('DEAL_ID'), 'id');
                                if(check_key < 0) {
                                    this.getDealId(urlParams.get('DEAL_ID'), false, false, false);
                                    return false;
                                }
                            }
                        }
                    }


                    if(self.users_id.length > 0) {
                        self.filterElement();
                        //self.getDealList(self.users_id, group_id);
                    }
                }
            })
                .catch(err => console.log(err));
        },

        getDealList() {

            this.visible = true
            let self = this;
            let headers = {'Content-Type': 'application/json'}
            axios.post(this.request_url, [{action: 'getDealList', filter: this.selectedFilter}], {headers}).then(response => {
                self.events = response.data.DEALS_CALENDAR;
                self.visible = false;
                var urlParams = new URLSearchParams(window.location.search);
                if(urlParams.get('DEAL_ID')) {
                    if(urlParams.get('DEAL_ID') > 0) {
                        var check_key = self.findObjectByKey(this.events, urlParams.get('DEAL_ID'), 'id');
                        if(check_key < 0) {
                            self.getDealId(urlParams.get('DEAL_ID'), false, false, false);
                            return false;
                        }
                    }
                }
                this.clickerTest();
            })
                .catch(err => console.log(err));
        },


        getDealListFilter(val) {
            //this.visible = true
            let self = this;
            let headers = {'Content-Type': 'application/json'}
            axios.post(this.request_url, [{action: 'getDealListFilter', filter: this.selectedFilter, alph: val}], {headers}).then(response => {
                self.data_deals = response.data.DEALS;
                //self.visible = false;
            })
                .catch(err => console.log(err));
        },


        getMeetingStatusList() {
            let self = this;
            let headers = {'Content-Type': 'application/json'}
            axios.post(this.request_url, [{action: 'getMeetingStatusList', id: [this.meeting_status_id, this.meeting_installation_id]}], {headers}).then(response => {
                self.meeting_status_list = response.data;
            })
                .catch(err => console.log(err));
        },

        addDealToCalendar() {
            if (!this.fieldsDeal.DEAL_ID) {
                alert('Выберите сделку');
                return false;
            }
            this.visible = true;
            let self = this;
            let headers = {'Content-Type': 'application/json'}
            axios.post(this.request_url, [{action: 'addDealToCalendar', data: this.fieldsDeal}], {headers}).then(response => {
                self.visible = false;
                if(response.data != true) {
                    alert(response.data);
                    self.getDealList();
                }
                else {
                    window.history.pushState('1', 'Title', '?DEAL_ID=');
                    if(self.fieldsDeal.UF_MEETING_STATUS == 179) {
                        self.getShowInstDealId(self.fieldsDeal.DEAL_ID);
                    }
                    self.fieldsDeal = {};
                    if(self.selectedFilter.CATEGORY_ID || self.selectedFilter.CATEGORY_ID == 0) {
                        self.getUsersList(self.selectedFilter.CATEGORY_ID);
                        $('#addDealToCalendar').modal('hide');
                    }
                }
            })
                .catch(err => console.log(err));
        },


        addDealToCalendarInstallation() {

            if (!this.fieldsDealIdInst.id) {
                alert('Выберите сделку');
                return false;
            }

            this.visible = true;
            if(this.fieldsDealInstallation.UF_INST_STATUS == 344) {
                if(Date.parse(this.fieldsDealInstallation.FROM_DATE) < Date.parse(new Date())) {
                    alert("Время установки меньше за текущее время. Установите правильное время.");
                    return false;
                }
            }

            this.fieldsDealInstallation['DEAL_ID'] = this.fieldsDealIdInst.id;
            let self = this;
            let headers = {'Content-Type': 'application/json'}
            axios.post(this.request_url, [{action: 'addDealToCalendarInstallation', data: this.fieldsDealInstallation}], {headers}).then(response => {
                self.visible = false;
                if(response.data != true) {
                    alert(response.data);
                    self.getDealList();
                } else {
                    self.fieldsDealInstallation = {};
                    self.getUsersList(self.selectedFilter.CATEGORY_ID);
                    $('#addDealToCalendar').modal('hide');
                    $('#addDealToCalendar_2').modal('hide');
                }
            })
                .catch(err => console.log(err));
        },

        getOpenDeal(manager, start, end) {
            let self = this;

            this.show_but_inst = false;
            //this.fieldsDeal.DEAL_ID = manager;
            this.fieldsDeal.ASSIGNED_BY_ID = manager;
            this.fieldsDeal.FROM_DATE = start;
            this.fieldsDeal.TO_DATE = end;

            var urlParams = new URLSearchParams(window.location.search);
            if(urlParams.get('DEAL_ID')) {
                if(urlParams.get('DEAL_ID') > 0) {
                    var check_key = this.findObjectByKey(this.events, urlParams.get('DEAL_ID'), 'id');
                    if(check_key < 0) {
                        this.getDealId(urlParams.get('DEAL_ID'), false, false);
                        return false;
                    }
                }
            }
            this.getDealId(null, false, false);
            this.clickerTest();
            $('#addDealToCalendar').modal('show');
            return true
        },

        findObjectByKey(array, value, key) {
            /*if (array != null) {
                for (var i = 0; i < array.length; i++) {
                    if (array[i][key] === value) {
                        return i;
                    }
                }
                return -1;
            }*/
            return -1;
        },

        getDealId(ID, status, done, show_popup = true) {
            let self = this;
            let headers = {'Content-Type': 'application/json'}
            axios.post(this.request_url, [{action: 'getDealId', id: ID}], {headers}).then(response => {

                if(response.data) {

                    self.fieldsDeal.CLIENT_NAME = response.data.CONTACT_FULL_NAME;
                    self.fieldsDeal.CLIENT_ID = response.data.CONTACT_ID;
                    self.fieldsDeal.COMMENTS = response.data.COMMENTS;
                    self.fieldsDeal.LOCATION = response.data.UF_LOCATION;
                    self.fieldsDeal.LOCATION_COORDS = (response.data.LOCATION_COORDS) ? '|' + response.data.LOCATION_COORDS : '';

                    if(!response.data.UF_MEETING_STATUS) {
                        self.fieldsDeal.UF_MEETING_STATUS = 178;
                    } else {
                        self.fieldsDeal.UF_MEETING_STATUS = response.data.UF_MEETING_STATUS;
                    }
                    if(!show_popup) {
                        if (response.data.LOCATION_COORDS) {
                            var params = {
                                'id': 'm1',
                                'title': response.data.UF_LOCATION,
                                'markers': response.data.LOCATION_COORDS.split(';')
                            };
                            self.events.push(params);
                        }
                    }

                    //} else {
                    //  self.fieldsDeal.UF_MEETING_STATUS = response.data.UF_MEETING_STATUS;
                    //}

                    if(response.data.UF_MEETING_STATUS == 178) {
                        self.meeting_status_deal = true;
                    } else if (response.data.UF_MEETING_STATUS == 180) {
                        self.meeting_status_deal = true;
                    } else {
                        self.meeting_status_deal = false;
                    }


                    var cities = ['132', '131', '334', '333', '332'],
                        day = 2;
                    if(cities.indexOf(self.selectedFilter.CATEGORY_ID) > -1) {
                        day = 5;
                    }
                    if(moment(new Date).subtract(day, "days").format('YYYY-MM-DD') > moment(self.fieldsDeal.FROM_DATE).format('YYYY-MM-DD')) {
                        self.meeting_status_deal = false;
                    }

                    if (status == true) {
                        self.selectedFilter.CATEGORY_ID = response.data.UF_CRM_5ADF41776517E;
                        self.clickerTest();
                        if (done) {
                            self.filterCategory();
                        }
                    }

                    if (status == false) {
                        if (self.fieldsDealId.id != ID) {
                            self.fieldsDealId = {'id': response.data.ID, 'name': response.data.TITLE};
                        }
                        self.clickerTest();
                        if(show_popup) {
                            $('#addDealToCalendar').modal('show');
                        }

                    }
                }
            })
                .catch(err => console.log(err));
        },

        getShowDealId(ID) {
            let self = this;
            this.fieldsDeal.DEAL_ID = ID;
            let headers = {'Content-Type': 'application/json'}
            axios.post(this.request_url, [{action: 'getDealId', id: ID}], {headers}).then(response => {
                if(response.data) {
                    if (self.fieldsDealId.id != ID) {
                        self.fieldsDealId = {'id': response.data.ID, 'name': response.data.TITLE};
                        self.fieldsDealIdInst = {'id': response.data.ID, 'name': response.data.TITLE};
                    }
                    self.fieldsDeal.COMMENTS = response.data.COMMENTS;
                    self.fieldsDeal.LOCATION = response.data.UF_LOCATION;
                    self.fieldsDeal.LOCATION_COORDS = (response.data.LOCATION_COORDS) ? '|' + response.data.LOCATION_COORDS : '';
                    self.fieldsDeal.ASSIGNED_BY_ID = response.data.ASSIGNED_BY_ID;
                    self.fieldsDeal.FROM_DATE = response.data.UF_TIME_BEGIN;
                    self.fieldsDeal.TO_DATE = response.data.UF_TIME_END;
                    self.fieldsDeal.UF_MEETING_STATUS = response.data.UF_MEETING_STATUS;

                    if (response.data.UF_MEETING_STATUS == 178) {
                        self.meeting_status_deal = true;
                        self.meeting_status_deal_but = true;
                        self.show_but_inst = false;
                    } else if(response.data.UF_MEETING_STATUS == 179 && !response.data.FROM_DATE) {
                        self.meeting_status_deal_but = false;
                        self.show_but_inst = true;
                    } else {
                        self.meeting_status_deal = false;
                        self.show_but_inst = false;
                    }

                    if(response.data.UF_MEETING_STATUS == 179) {
                        self.show_but_inst = true;
                    }

                    var cities = ['132', '131', '334', '333', '332'],
                        day = 2;
                    if(cities.indexOf(self.selectedFilter.CATEGORY_ID) > -1) {
                        day = 5;
                    }
                    if(moment(new Date).subtract(day, "days").format('YYYY-MM-DD') > moment(self.fieldsDeal.FROM_DATE).format('YYYY-MM-DD')) {
                        self.meeting_status_deal = false;
                    }

                    if(response.data.LOCATION_COORDS) {
                        self.coords_zoom = response.data.LOCATION_COORDS.split(';')
                    }

                    self.clickerTest();
                    $('#addDealToCalendar').modal('show');
                }
            })
                .catch(err => console.log(err));
        },


        getShowInstDealId(ID) {
            let self = this;
            this.fieldsDeal.DEAL_ID = ID;
            let headers = {'Content-Type': 'application/json'}
            axios.post(this.request_url, [{action: 'getDealId', id: ID}], {headers}).then(response => {
                if(response.data) {
                    if (self.fieldsDealIdInst.id != ID) {
                        self.fieldsDealIdInst = {'id': response.data.ID, 'name': response.data.TITLE};
                    }

                    self.fieldsDeal.CLIENT_NAME = response.data.CONTACT_FULL_NAME;
                    self.fieldsDeal.CLIENT_ID = response.data.CONTACT_ID;
                    self.fieldsDealInstallation.ASSIGNED_BY_ID = response.data.UF_CRM_1555075059;
                    self.fieldsDealInstallation.UF_INST_STATUS = (response.data.UF_CRM_1555401213) ? response.data.UF_CRM_1555401213 : 344;
                    self.fieldsDealInstallation.INSTALLATION = response.data.UF_CRM_5AAFA5DAF3925;
                    self.fieldsDealInstallation.FROM_DATE = response.data.UF_TIME_BEGIN_INST;
                    self.fieldsDealInstallation.TO_DATE = response.data.UF_TIME_END_INST;


                    if(!response.data.UF_LOCATION_INST) {
                        self.fieldsDealInstallation.LOCATION = response.data.UF_LOCATION;
                        self.fieldsDealInstallation.LOCATION_COORDS = (response.data.LOCATION_COORDS) ? '|' + response.data.LOCATION_COORDS : '';
                    } else {
                        self.fieldsDealInstallation.LOCATION = response.data.UF_LOCATION_INST;
                        self.fieldsDealInstallation.LOCATION_COORDS = (response.data.LOCATION_COORDS_INST) ? '|' + response.data.LOCATION_COORDS_INST : '';
                    }


                    if (response.data.UF_CRM_1555401213 == 344) {
                        self.inst_status_deal = true;
                        /*self.meeting_status_deal_but = true;
                         self.show_but_inst = false;*/
                    } else {
                        self.inst_status_deal = true; //false
                        self.install_status_deal_but = true; //false
                    }

                    var cities = ['132', '131', '334', '333', '332'],
                        day = 2;
                    if(cities.indexOf(self.selectedFilter.CATEGORY_ID) > -1) {
                        day = 5;
                    }
                    if(moment(new Date).subtract(day, "days").format('YYYY-MM-DD') > moment(self.fieldsDealInstallation.FROM_DATE).format('YYYY-MM-DD')) {
                        self.inst_status_deal = false;
                    }

                    if(!response.data.UF_CRM_1555401213) {
                        self.install_status_deal_but = true;
                    }

                    if(response.data.LOCATION_COORDS_INST) {
                        self.coords_zoom = response.data.LOCATION_COORDS_INST.split(';')
                    }

                    self.clickerTest();
                    $('#addDealToCalendar_2').modal('show');
                }
            })
                .catch(err => console.log(err));
        },


        doSomethingOnHidden() {
            this.fieldsDeal = {};
            this.clickerTest();
        },

        clickerTest() {
            if(this.test == 'n') {
                this.test = 'y';
            } else {
                this.test = 'n';
            }
        },

        onChangeDealList (value) {
            if(!value) {
                this.fieldsDealId = {}
            }
        },

        onSearchDealTitle (value) {
            this.getDealListFilter(value);
        },

        doSomethingOnShow() {

        },

        addDealToInstallation() {
            $('#addDealToCalendar_2').modal('show');
            this.fieldsDealInstallation.UF_INST_STATUS = 344;
            this.clickerTest();
        },

        getDayWeekRu(day) {
            var d = ''
            switch (day) {
                case 'Monday':
                    d = 'Понедельник';
                    break;
                case 'Tuesday':
                    d = 'Вторник';
                    break;
                case 'Wednesday':
                    d = 'Среда';
                    break;
                case 'Thursday':
                    d = 'Четверг';
                    break;
                case 'Friday':
                    d = 'Пятница';
                    break;
                case 'Saturday':
                    d = 'Суббота';
                    break;
                case 'Sunday':
                    d = 'Воскресенье';
                    break;
                default:
                    d = 'Не определено';
            }
            return d;
        },


        //my from 25.11.2019
        getValuesListForFilters: function () {

            console.log('Man 1 List filters!');
            let self = this;
            let headers = {'Content-Type': 'application/json'}
            axios.post(this.request_url, [{action: 'getValuesListForFilters'}], {headers}).then(response => {

                console.log(response);
                if(response.data) {

                    // self.clickerTest();
                    // $('#addDealToCalendar_2').modal('show');
                }
            })
                .catch(err => console.log(err));


        },


    }
})
