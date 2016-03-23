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
