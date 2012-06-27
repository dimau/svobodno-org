/**
 Docs: http://learn.javascript.ru/tutorial/lib
*/

var Eventer = new function() {

  function on(eventName, handler) {
    // создать свойство obj.eventer_handlers[eventName], если его нет
    if (!this._eventerHandlers) {
      this._eventerHandlers = {};
    }
    if (!this._eventerHandlers[eventName]) {
      this._eventerHandlers[eventName] = [];
    }

    // добавить обработчик в массив
    this._eventerHandlers[eventName].push(handler);
  }

  function trigger(eventName, args) {

    if (!this._eventerHandlers || !this._eventerHandlers[eventName]) {
      return; // обработчиков для события нет
    }

    // вызовать обработчики
    var handlers = this._eventerHandlers[eventName];
    for(var i=0; i<handlers.length; i++) {
      handlers[i].apply(this, args);
    }

  }

  this.extend = function(obj) {
    obj.on = on;
    obj.trigger = trigger;
  }

}
