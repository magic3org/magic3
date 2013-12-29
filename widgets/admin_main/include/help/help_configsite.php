<?php
/**
 * ヘルプリソースファイル
 * index.php
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: help_configsite.php 5498 2012-12-30 12:22:58Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCommonPath()				. '/helpConv.php' );

class help_configsite extends HelpConv
{
	/**
	 * ヘルプ用データを設定
	 *
	 * @return array 				ヘルプ用データ
	 */
	function _setData()
	{
		// ########## 基本情報 ##########
		$helpData = array(
			'configsite_siteinfo' => array(	
				'title' =>	$this->_('Site Information'),			// サイト情報
				'body' =>	$this->_('Input basic site information.')		// システムで必要なサイトの情報です。
			),
			'configsite_sitename' => array(
				'title' =>	$this->_('Site Name'),			// サイト名
				'body' =>	$this->_('Input site name.')		// サイトの名前を設定します。
			),
			'configsite_email' => array(
				'title' =>	$this->_('Site Email'),			// Eメールアドレス
				'body' =>	$this->_('Input default email address. It is required. It can be format of name@example.com, name&lt; name@example.com&gt;. This address is used as email sender from this system to user, and used as email receiver from user to this system.<br />If as receiver, it can send email by bcc and cc. The format is "address1;cc:address2;bcc:address3". ')		// このサイトのデフォルトのEメールアドレスです。このアドレスは必須項目です。「name@example.com」や「名前&lt; name@example.com&gt;」の設定が可能です。<br />このアドレスは、システムからユーザへ送信する場合の送信元アドレスとして、またはこのシステム上でユーザがメールを送信した場合に送信先アドレスとして使用されます。ユーザからのメール送信先は、次のフォーマットで「CC」や「BCC」でのメール送信も可能です。フォーマット「アドレス1;cc:アドレス2;bcc:アドレス3」。
			),
			'configsite_slogan' => array(
				'title' =>	$this->_('Site Slogan'),			// サイトスローガン
				'body' =>	$this->_('Input site slogan on the header image.')		// ヘッダ画像上に表示するサイトスローガンを設定します。
			),
			'configsite_copyright' => array(
				'title' =>	$this->_('Site Copyright'),			// 著作権
				'body' =>	$this->_('Input site copyright. It is used as rss data copyright.')		// RSS配信データの著作権表示に使用されます。
			),
			'configsite_pagehead' => array(	
				'title' =>	$this->_('Page Header Information (Default)'),			// ページヘッダ情報(デフォルト値)
				'body' =>	$this->_('Input header meta tag string on html. It is default string if each page does not have meta tag string.')		// HTMLのヘッダ部のmetaタグに出力する文字列を設定します。個々のページで設定されていない場合のデフォルト値です。
			),
			'configsite_title' => array(
				'title' =>	$this->_('Header Title'),			// タイトル名
				'body' =>	$this->_('Input meta title string. It is used as web browser window title.')		// ヘッダ部のtitleタグに設定される文字列です。Webブラウザの画面タイトルとして表示されます。
			),
			'configsite_description' => array(
				'title' =>	$this->_('Site Description'),			// サイト説明
				'body' =>	$this->_('Input meta description for site with string with 120 letters. It appears on searched list by google.')		// サイトの説明のためにヘッダ部のdescriptionタグに設定される文字列です。120文字程度で記述します。<br />Googleでは検索結果に表示されます。
			),
			'configsite_keywords' => array(
				'title' =>	$this->_('Header Keywords'),			// 検索キーワード
				'body' =>	$this->_('Input meta Keywords string with max 10 counts separeted by comma.')		// ヘッダ部のkeywordsタグに設定される文字列です。検索エンジン用のキーワードを「,」区切りで10個以下で記述します。
			),
			'configsite_others' => array(
				'title' =>	$this->_('Header Others (by Tag Style)'),			// その他(タグ形式)
				'body' =>	$this->_('Input other tag string in meta area.')		// ヘッダ部に出力するタグを設定します。
			),
		);
		return $helpData;
	}
}
?>
