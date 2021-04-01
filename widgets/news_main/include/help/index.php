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
 * @copyright  Copyright 2006-2021 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

global $HELP;

// ########## 新着情報 ##########
$HELP['news']['title'] = '新着情報一覧';
$HELP['news']['body'] = '新着情報の一覧です。';
$HELP['news_check']['title'] = '選択用チェックボックス';
$HELP['news_check']['body'] = '編集や削除を行う項目を選択します。赤のアイコンの項目は「表示」に設定されていない項目です。';
$HELP['news_message']['title'] = 'メッセージ';
$HELP['news_message']['body'] = 'メッセージの内容です。任意に文字列を設定できます。[#TITLE#]部はリンク付きのコンテンツタイトルに変換されます。';
$HELP['news_link_status']['title'] = 'リンク先の状態';
$HELP['news_link_status']['body'] = 'リンク先のコンテンツの状態を示します。';
$HELP['news_visible']['title'] = '公開状態';
$HELP['news_visible']['body'] = 'メッセージを公開するかどうかを制御します。';
$HELP['news_content_type']['title'] = 'コンテンツ種別';
$HELP['news_content_type']['body'] = 'リンクから取得したコンテンツ種別です。';
$HELP['news_content_id']['title'] = 'コンテンツID';
$HELP['news_content_id']['body'] = 'リンクから取得したコンテンツIDです。';
$HELP['news_content_title']['title'] = 'コンテンツタイトル';
$HELP['news_content_title']['body'] = 'メッセージの[#TITLE#]部に変換される文字列です。デフォルト値はリンクからコンテンツのタイトルが取得されます。';
$HELP['news_link']['title'] = 'リンク';
$HELP['news_link']['body'] = 'メッセージのリンク先URLを設定します。メッセージに[#TITLE#]が含まれる場合は[#TITLE#]にリンクします。メッセージに[#TITLE#]が含まれない場合はメッセージ全体にリンクします。';
?>
