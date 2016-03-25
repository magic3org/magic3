/**
 * Node.jsインターフェイス
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2016 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    0.1
 * @link       http://www.magic3.org
 */
/**
 * メイン処理
 */
var app = require('http').createServer(handler);
var io = require('socket.io').listen(app);

var fs = require('fs');
var html = fs.readFileSync('index.html', 'utf8');

function handler(req, res)
{
	res.setHeader('Content-Type', 'text/html');
	res.setHeader('Content-Length', Buffer.byteLength(html, 'utf8'));
	res.end(html);
}
function tick()
{
	var now = new Date().toUTCString();

	io.sockets.send(now);
// 	console.log("Sending at " + now);
}

app.listen(2000);

setInterval(tick, 1000);
/*
var app = require('express')();
app.get('/', function (req, res) {
  res.send('Hello World!');
});

var server = app.listen(3000);
*/
/*
var io = require('socket.io').listen(server, {log:true, origins:'*:*'});

io.sockets.on('connection', function(socket){
   console.log("connection");
});
*/
/*
var http = require('http');
var fs = require('fs');
var path = require('path');
var mime = require('mime');
var cache = {};

function send404(response)
{
	response.writeHead(404, {'Content-Type' : 'text/plain'});
	response.write('Error 404: resource not found.');
	response.end();
}
function sendFile(response, filePath, fileContents)
{
	response.writeHead
	(
		200,
		{"Content-type": mime.lookup(path.basename(filePath))}
	);
	response.end(fileContents);
}
function serveStatic(response, cache, absPath)
{
	if (cache[absPath]){
		sendFile(response, absPath, cache[absPath]);
	} else {
		fs.exists(absPath, function(exists){
			if (exists){
				fs.readFile(absPath, function(err, data){
					if (err){
						send404(response);
					} else {
						cache[absPath] = data;
						sendFile(response, absPath, data);
					}
				});
			} else {
				send404(response);
			}
		});
	}
}
var server = http.createServer(function(request, response){
	var filePath = false;
	if (request.url == '/'){
		filePath = 'public/index.html';
	} else {
		filePath = 'public' + request.url;
	}
	var absPath = './' + filePath;
	serveStatic(response, cache, absPath);
});
server.listen(3000, function(){
	console.log("Server listening on port 3000.");
});
var chatServer = require('./lib/chat_server');
chatServer.listen(server);
*/
