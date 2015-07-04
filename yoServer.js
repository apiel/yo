var webserver = require('webserver');
var server = webserver.create();
var service = server.listen(8080, function(request, response) {
  //console.log(JSON.stringify(request));
  //console.log(request.post.test);

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