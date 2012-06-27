/**
 Docs: http://learn.javascript.ru/tutorial/lib
*/

function fixEvent(e, _this) {
  e = e || window.event;

  if (!e.currentTarget) e.currentTarget = _this;
  if (!e.target) e.target = e.srcElement;

  if (!e.relatedTarget) {
    if (e.type == 'mouseover') e.relatedTarget = e.fromElement;
    if (e.type == 'mouseout') e.relatedTarget = e.toElement;
  }

  if (e.pageX == null && e.clientX != null ) {
    var html = document.documentElement;
    var body = document.body;

    e.pageX = e.clientX + (html.scrollLeft || body && body.scrollLeft || 0);
    e.pageX -= html.clientLeft || 0;

    e.pageY = e.clientY + (html.scrollTop || body && body.scrollTop || 0);
    e.pageY -= html.clientTop || 0;
  }

  if (!e.which && e.button) {
    e.which = e.button & 1 ? 1 : ( e.button & 2 ? 3 : ( e.button & 4 ? 2 : 0 ) )
  }

  return e;
}

// event.type должен быть keypress
// не могу интегрировать в fixEvent, т.к. charCode read only
function getChar(event) {
  if (event.which == null) {
    if (event.keyCode < 32) return null;
    return String.fromCharCode(event.keyCode) // IE
  }

  if (event.which!=0 && event.charCode!=0) {
    if (event.which < 32) return null;
    return String.fromCharCode(event.which)   // остальные
  }

  return null; // специальная клавиша
}

function getCoords(elem) {
    var box = elem.getBoundingClientRect();

    var body = document.body;
    var docElem = document.documentElement;

    var scrollTop = window.pageYOffset || docElem.scrollTop || body.scrollTop;
    var scrollLeft = window.pageXOffset || docElem.scrollLeft || body.scrollLeft;

    var clientTop = docElem.clientTop || body.clientTop || 0;
    var clientLeft = docElem.clientLeft || body.clientLeft || 0;

    var top  = box.top +  scrollTop - clientTop;
    var left = box.left + scrollLeft - clientLeft;

    return { top: Math.round(top), left: Math.round(left) };
}


function addClass(elem, clazz) {
  var c = elem.className.split(' ');
  for(var i=0; i<c.length; i++) {
    if (c[i] == clazz) return;
  }

  if (elem.className == '') elem.className = clazz;
  else elem.className += ' ' + clazz;
}


function removeClass(elem, clazz) {
  var c = elem.className.split(' ');
  for(var i=0; i<c.length; i++) {
    if (c[i] == clazz) c.splice(i--, 1);
  }

  elem.className = c.join(' ');
}

function hasClass(el, cls) {
  for(var c = el.className.split(' '),i=c.length-1; i>=0; i--) {
    if (c[i] == cls) return true;
  }
  return false;
}
