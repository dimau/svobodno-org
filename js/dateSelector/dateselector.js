/**
 * options:
 * yearFrom {number} начальный год в селекторе
 * yearTo {number} конечный год в селекторе
 * value {Date} текущая выбранная дата
*/
function DateSelector(options) {
  Eventer.extend(this);
  var months = 'января февраля марта апреля мая июня июля августа сентября октября ноября декабря'.split(' ');

  var value = options.value;
  var elem;
  var yearSelect, monthSelect, daySelect;

  var self = this;

  function render() {
    elem = document.createElement('div');
    elem.className = "date-selector";

    yearSelect = document.createElement('select');
    yearSelect.className = "year";
    yearSelect.onchange = onSelectChange;
    yearSelect.innerHTML = renderYearOptions();

    monthSelect = document.createElement('select');
    monthSelect.className = "month";
    monthSelect.onchange = onSelectChange;
    monthSelect.innerHTML = renderMonthOptions();

    daySelect = document.createElement('select');
    daySelect.className = "day";
    daySelect.onchange = onSelectChange;
    daySelect.innerHTML = renderDayOptions();

    // при изменении месяца правим дни
    adjustDayOptions(value.getFullYear(), value.getMonth());

    elem.appendChild(daySelect);
    elem.appendChild(monthSelect);
    elem.appendChild(yearSelect);
  }

  this.getValue = function() {
    return value;
  };

  this.setValue = function(newValue, disableEvent) {
    value = newValue;
    yearSelect.value = value.getFullYear();
    monthSelect.value = value.getMonth();

    daySelect.value = value.getDate();

    if (!disableEvent) {
      self.trigger("select", [value]);
    }
  };

  this.getElement = function() {
    if (!elem) render();
    return elem;
  };

  function onSelectChange(e) {
    e = fixEvent(e);


    if (e.target.className == "month" || e.target.className == "year") {
      // поправить день с учетом месяца и, возможно, високосного года
      adjustDayOptions(yearSelect.value, monthSelect.value);
    }

    if (e.target.className == "month") {
      // если я сделаю сначала value.setMonth(),
      // то может получится некорректная дата типа 31 марта -> 31 февраля,
      // которая автоскорректируется в 2 марта, т.е месяц не поставится.
      // поэтому сначала именно setDate.
      value.setDate(daySelect.value);
      value.setMonth(monthSelect.value);
    }

    if (e.target.className == "day") {
      value.setDate(daySelect.value);
    }

    if (e.target.className == "year") {
      value.setFullYear(yearSelect.value);
    }




    self.trigger("select", [value]);
  }

  function renderMonthOptions() {
    var opts = [];
    for(var i=0; i<12; i++) {
      opts.push({ name: months[i], value: i });
    }

    return renderOptions(opts, value.getMonth());
  }

  function adjustDayOptions(year, month) {
    var d = new Date(year, month, 32);
    while (d.getMonth() != month) {
      d.setDate(d.getDate()-1);
    }
    var maxDay = d.getDate();

    // укоротить селект и изменить номер дня, если он слишком велик
    var selectedIndex = Math.min(daySelect.selectedIndex, maxDay-1);
    while (daySelect.options.length > maxDay) {
      daySelect.removeChild(daySelect.options[daySelect.options.length-1]);
    }
    daySelect.selectedIndex = selectedIndex;

    // добавить дни, если новый месяц дольше
    while (daySelect.options.length < maxDay) {
      var newDay = daySelect.options.length + 1;
      // new Option(name, value, selected) - короткий синтаксис для создания <option>
      daySelect.appendChild(new Option(newDay, newDay));
    }
  }

  function renderDayOptions() {
    var opts = [];
    for(var i=1; i<=31; i++) {
      opts.push({ name: i, value: i });
    }

    return renderOptions(opts, value.getDate());
  }


  function renderYearOptions() {
    var opts = [];
    for(var i=options.yearFrom; i<=options.yearTo; i++) {
      opts.push({ name: i, value: i });
    }

    return renderOptions(opts, value.getFullYear());
  }

  function renderOptions(opts, value) {
    var html = '';

    for(var i=0; i<opts.length; i++) {
      var selected = opts[i].value == value ? ' selected' : '';
      html += '<option value="'+opts[i].value+'"'+selected+'>'+opts[i].name+'</option>';
    }

    return html;
  }
}
