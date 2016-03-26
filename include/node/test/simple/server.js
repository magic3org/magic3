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

function handler(req, res)
{
	var message = 'Forbidden';
	res.setHeader('Content-Type', 'text/html');
	res.setHeader('Content-Length', message.length);
	res.end(message);
}
function tick()
{
	var now = new Date().toUTCString();
	io.sockets.send(now);
}

setInterval(tick, 1000);

app.listen(2000);
