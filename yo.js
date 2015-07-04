var system = require('system');
var args = system.args;

//page.content = '<html><body><p>Hello world</p></body></html>';

if (args.length > 1) {
  var page = require('webpage').create();
  page.open(args[1], function (status) {
    if (status === 'success') {
      if (args.length > 2) {
        var fs = require('fs');
        var json = fs.read(args[2]);
        var data = JSON.parse(json);
        page.evaluate(function(data) {
          var event = document.createEvent('Event');
          event.initEvent('yo-go', true, true);
          event.data = data;
          document.dispatchEvent(event);
        }, data);
        console.log(page.content); 
      }
    }
    phantom.exit();
  });
}
else {
  phantom.exit();
}

/*
var system = require('system');
var args = system.args;

if (args.length > 1) {
  //console.log(args);
  page.content = args[1];
  //JSON.parse()

}

console.log(page.content);
phantom.exit();
*/
