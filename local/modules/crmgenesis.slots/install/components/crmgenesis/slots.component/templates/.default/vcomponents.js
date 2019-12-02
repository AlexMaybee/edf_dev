/*@component: 1. Hours animate component
*/
Vue.component('animate-time-counters',{
    props: {
        hours: 0,
        myClass: '',
        text: '',
        measure: '',
    },
    data: function(){
        return{
            hourStart: 0,
        }
    },
    watch: {
        hours: function () {
            this.animateHour();
        },
    },
    computed: {
        hourNew: function () {
            return this.hourStart.toFixed(1);
        },
    }, methods: {
        animateHour: function () {
            var self = this;
            var id = setInterval(function () {
                if(self.hourStart < self.hours) {
                    return self.hourStart += 0.5;
                }
                else if(self.hourStart > self.hours){
                    return self.hourStart -= 0.5;
                }
                else clearInterval(id);
            },50)
        },
    },
    mounted: function () {
        this.animateHour();
    },
    template:
        `<div>
            <span>{{text}}
                <span :class="myClass">{{hourNew}} </span>{{measure}}
            </span>
        </div>`,
});