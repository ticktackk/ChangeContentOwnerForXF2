var TickTackk = window.TickTackk || {};
TickTackk.ChangeContentOwner = TickTackk.ChangeContentOwner || {};

!function($, window, document, _undefined)
{
    "use strict";

    XF.DateInput = XF.extend(XF.DateInput, {
        __backup: {
            'init': 'tckChangeContentOwner__init'
        },

        options: $.extend({}, XF.DateInput.prototype.options, {
            allowSettingTime: null,
            useTwentyFourHour: false
        }),

        init: function ()
        {
            if (!this.options.allowSettingTime)
            {
                this.tckChangeContentOwner__init();
                return;
            }

            var minDate = this.options.minDate,
                maxDate = this.options.maxDate;
            if (minDate)
            {
                var minTime = Date.parse(minDate.replace(/-/g, '/'));
                minDate = new Date(minTime);
            }
            if (maxDate)
            {
                var maxTime = Date.parse(maxDate.replace(/-/g, '/'));
                maxDate = new Date(maxTime);
            }

            var self = this,
                $target = this.$target,
                initialValue = $target.val(),
                config = {
                    onSelect: function ()
                    {
                        var pad = function (number)
                        {
                            if (number < 10)
                            {
                                return '0' + number;
                            }
                            return number;
                        };

                        var date = this._d,
                            day = String(date.getDate()),
                            month = String(date.getMonth() + 1),
                            year = String(date.getFullYear()),
                            hour = String(date.getHours()),
                            minute = String(date.getMinutes()),
                            second = String(date.getSeconds());

                        self.$target.val(year + '-' + pad(month) + '-' + pad(day) + ' ' + pad(hour) + ':' + pad(minute) + ':' + pad(second));
                        self.$target.trigger('input');
                    },
                    onOpen: function ()
                    {
                        if ($target.prop('readonly'))
                        {
                            this.hide();
                        }
                    },
                    showTime: true,
                    firstDay: this.options.weekStart,
                    minDate: minDate,
                    maxDate: maxDate,
                    disableWeekends: this.options.disableWeekends,
                    yearRange: this.options.yearRange,
                    showWeekNumber: this.options.showWeekNumber,
                    showDaysInNextAndPreviousMonths: this.options.showDaysInNextAndPreviousMonths,
                    i18n: {
                        previousMonth: '',
                        nextMonth: '',
                        weekdays: [0, 1, 2, 3, 4, 5, 6].map(function (day)
                        {
                            return XF.phrase('day' + day)
                        }),
                        weekdaysShort: [0, 1, 2, 3, 4, 5, 6].map(function (day)
                        {
                            return XF.phrase('dayShort' + day)
                        }),
                        months: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11].map(function (month)
                        {
                            return XF.phrase('month' + month)
                        }),
                        midnight: XF.phrase('midnight'),
                        noon: XF.phrase('noon')
                    },
                    isRTL: XF.isRtl(),
                    defaultDate: new Date(Date.parse(initialValue)),
                    setDefaultDate: true,
                    showSeconds: true,
                    use24hour: self.options.useTwentyFourHour
                };

            this.$target.pikaday(config);
            this.$target.val(initialValue);
            this.$target.trigger('input');
        }
    });
}
(jQuery, window, document);