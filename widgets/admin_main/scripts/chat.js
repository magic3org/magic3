/**
 * チャットサンプル用
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2016 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    1.0
 * @link       http://www.magic3.org
 */
var Chat = function(socket)
{
	this.socket = socket;
};
Chat.prototype.sendMessage = function(room, text)
{
	var message = {
		room: room,
		text: text
	};
	this.socket.emit('message', message);
};
Chat.prototype.chageRoom = function(room)
{
	this.socket.emit('join', {
		newRoom: room
	});
};
Chat.prototype.processCommand = function(command)
{
	var wrods = command.split(' ');
	var command = words[0].substring(1, words[0].length).toLowerCase();
	var message = false;
	
	switch(command){
		case 'join':
			words.shift();
			var room = words.join(' ');
			this.changeRoom(room);
			break;
		case 'nick':
			words.shift();
			var name = word.join(' ');
			this.socket.emit('nameAttempt', name);
			break;
		default:
			message = 'Unrecognized command.';
			break;
		return messege;
	}
};
