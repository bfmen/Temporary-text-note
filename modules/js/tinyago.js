function ago(val) {
  // https://github.com/odyniec/tinyAgo-js
  // val is datetime in milliseconds
  val = 0 | (Date.now() - val) / 1000;
  var unit, length = {
      second: 60,
      minute: 60,
      hour: 24,
      day: 7,
      week: 4.35,
      month: 12,
      year: 10000
    },
    result;

  var unitMapCN = {
    second: '秒',
    minute: '分钟',
    hour: '小时',
    day: '天',
    week: '周',
    month: '个月',
    year: '年'
  };

  for (unit in length) {
    result = val % length[unit];
    if (!(val = 0 | val / length[unit]))
      return result + ' ' + unitMapCN[unit] + '前';
  }
}
