Vue.component('calendar', {
    template: '<div ref="calendar"></div>',
    props: {
        events: {
            type: Array,
            required: true
        },

        resources: {
            type: Array,
            required: true
        },

        settings: {
            type: Object,
            required: false,
            default: {minTime: '07:00:00', maxTime: '22:30:00', slotDuration: '00:30:00'},
        },

        editable: {
            type: Boolean,
            required: false,
            default: true
        },

        droppable: {
            type: Boolean,
            required: false,
            default: true
        },
    },

    data () {
        return {
            cal: null,
            statusLoader: false,

        }
    },

    mounted () {
        var self = this;
        self.cal = $(self.$refs.calendar);
        var args = {
            defaultView: 'agendaWeek',
            firstDay: 1,
            timeFormat: 'H:mm',
            selectable: true,
            selectHelper: true,
            lang: 'ru',
            locale: 'ru',
            editable: true,
            header: {
                left: 'today prev,next',
                center: 'title',
                right: ''
                // right: 'agendaWeek'
            },
            customButtons: {
                promptResource: {
                    text: '+ room',
                    click: function() {
                        var title = prompt('Room name');
                        if (title) {
                            this.calendarElement.fullCalendar(
                                'addResource',
                                { title: title },
                                true
                            );
                        }
                    }
                },
            },
            themeSystem: 'bootstrap4',
            slotDuration: self.settings.slotDuration,
            slotLabelFormat: 'H:mm',
            minTime: self.settings.minTime,
            maxTime: self.settings.maxTime,
            contentHeight: 'auto',
            nowIndicator: true,
            views: {
                timelineThreeDays: {
                    type: 'timeline',
                }
            },
            resourceLabelText: 'Менеджеры',
            resourceAreaWidth: '10%',
            resources: function(callback) {
                getResourcesList();
                function getResourcesList() {
                    callback(self.resources)
                }
            },
            // eventSources: [self.events],
            color: 'black',
            textColor: 'yellow',
            allDaySlot: false,
            slotEventOverlap: false,
            events: function(start, end, timezone, callback) {
                getEventsList();
                function getEventsList() {
                    callback(self.events)
                }
            },

            //timeZone: 'local',

            select: function(startDate, endDate, jsEvent, view, resource) {
                startDate = moment(startDate).format('YYYY-MM-DDTHH:mm');
                endDate = moment(endDate).format('YYYY-MM-DDTHH:mm');

                //добавляем в переменные и показываем попап
                // app.openWorkDayAddPopup(startDate,endDate,'');
                app.openWorkDayAddPopup(startDate,endDate);

                console.log(startDate);
                console.log(endDate);
            },

            eventClick: function(event) {
                var char_one = event.id.charAt(0);
                var char_two = event.id.charAt(0);

                //popup создания/удаления слота
                app.openGspModal(
                    moment(event.start._i).format('YYYY-MM-DDTHH:mm'),
                    moment(event.end._i).format('YYYY-MM-DDTHH:mm'),
                    event.id)
            },

            eventDrop: function(event) {
                var char_two = event.id.charAt(0);
                // console.log('lol',event);
                app.changeDateByDragNDrop(
                    moment(event.start._i).format('YYYY-MM-DDTHH:mm'),
                    moment(event.end._i).format('YYYY-MM-DDTHH:mm'),
                    event.id);

            },

            eventResize: function(event) {
                var char_two = event.id.charAt(0);

            },

            viewDestroy: function (view, element) {
                app.defaultDateCustom = moment(view.start);
                app.FilterEventsDate(view.start);
            },

            schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source'
        }

        self.cal.fullCalendar(args);

    },

    watch: {
        resources: function() {
            var self = this;
            this.settings = app.settings;
            self.cal.fullCalendar('option', 'minTime', app.settings.minTime);
            self.cal.fullCalendar('option', 'maxTime', app.settings.maxTime);
            self.cal.fullCalendar('option', 'slotDuration', app.settings.slotDuration);
            self.cal.fullCalendar('refetchResources');
        },

        events: function() {
            var self = this;
            self.cal.fullCalendar( 'gotoDate', app.defaultDateCustom );
            self.cal.fullCalendar('refetchEvents');

        },
    }

})
