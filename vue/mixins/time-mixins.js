import moment from "moment";

export default {
  filters: {
    formDateTime: formDateTimeInternal,
    formDate: formDateInternal,
    formTime: formTimeInternal,
  },
  methods: {
    formDateTime: formDateTimeInternal,
    formDate: formDateInternal,
    formTime: formTimeInternal,
  },
};

function formDateTimeInternal(date) {
  const format = this.strings.format_date_time;
  const timeString = moment.unix(date).format(format);
  const timeSuffix = this.strings.format_time_suffix;
  return `${timeString} ${timeSuffix}`;
}
function formDateInternal(date) {
  const format = this.strings.format_date;
  return moment.unix(date).format(format);
}
function formTimeInternal(date) {
  const format = this.strings.format_time;
  const timeString = moment.unix(date).format(format);
  const timeSuffix = this.strings.format_time_suffix;
  return `${timeString} ${timeSuffix}`;
}
