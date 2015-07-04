var port = 8080;
var args = require('system').args;
if (args.length > 1) {
  port = args[1];
  console.log('Run yoServer on port ' + port);
}
var webserver = require('webserver');
var server = webserver.create();
var service = server.listen(port, function(request, response) {
  var page = require('webpage').create();
  page.content = request.post.page;
  page.evaluate(function(data) {
    var event = document.createEvent('Event');
    event.initEvent('yo-go', true, true);
    event.data = data;
    document.dispatchEvent(event);
  }, request.post.data);
  
  response.statusCode = 200;
  response.write(page.content);
  response.close();
});