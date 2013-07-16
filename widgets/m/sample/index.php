<?php
/**
 * index.php
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: index.php 2040 2009-07-03 07:50:09Z fishbone $
 * @link       http://www.magic3.org
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

echo '<form action="must_input" method="post">';
echo '<h1>表示テスト(h1)</h1>';
echo date("Y/m/d H:i:s") . '<br />';
echo '<br />';
echo '<div style="font-size:xx-small;">';
echo 'メニュー(xx-small)<br />';
echo '<select name="test">';
echo '<option value="0">-- 未選択 --</option>';
echo '<option value="1">ダミー1</option>';
echo '<option value="2">ダミー2</option>';
echo '<option value="3">ダミー3</option>';
echo '<option value="4">ダミー4</option>';
echo '<option value="5">ダミー5</option>';
echo '<option value="6">ダミー6</option>';
echo '<option value="7">ダミー7</option>';
echo '<option value="8">ダミー8</option>';
echo '<option value="9">ダミー9</option>';
echo '<option value="10">ダミー10</option>';
echo '</select><br /><br />';
echo '</div>';
echo '<div style="font-size:small;">';
echo 'メニュー(small)<br />';
echo '<select name="test">';
echo '<option value="0">-- 未選択 --</option>';
echo '<option value="1">ダミー1</option>';
echo '<option value="2">ダミー2</option>';
echo '<option value="3">ダミー3</option>';
echo '<option value="4">ダミー4</option>';
echo '<option value="5">ダミー5</option>';
echo '<option value="6">ダミー6</option>';
echo '<option value="7">ダミー7</option>';
echo '<option value="8">ダミー8</option>';
echo '<option value="9">ダミー9</option>';
echo '<option value="10">ダミー10</option>';
echo '</select><br /><br />';
echo '</div>';
echo '<div style="font-size:medium;">';
echo 'メニュー(medium)<br />';
echo '<select name="test">';
echo '<option value="0">-- 未選択 --</option>';
echo '<option value="1">ダミー1</option>';
echo '<option value="2">ダミー2</option>';
echo '<option value="3">ダミー3</option>';
echo '<option value="4">ダミー4</option>';
echo '<option value="5">ダミー5</option>';
echo '<option value="6">ダミー6</option>';
echo '<option value="7">ダミー7</option>';
echo '<option value="8">ダミー8</option>';
echo '<option value="9">ダミー9</option>';
echo '<option value="10">ダミー10</option>';
echo '</select><br /><br />';
echo '</div>';
echo '複数選択<br />';
echo '<select name="test2" size="5" multiple>';
echo '<option value="1">ダミー1</option>';
echo '<option value="2">ダミー2</option>';
echo '<option value="3">ダミー3</option>';
echo '<option value="4">ダミー4</option>';
echo '<option value="5">ダミー5</option>';
echo '<option value="6">ダミー6</option>';
echo '<option value="7">ダミー7</option>';
echo '<option value="8">ダミー8</option>';
echo '<option value="9">ダミー9</option>';
echo '<option value="10">ダミー10</option>';
echo '</select>';
echo '</form><br />';
?>
