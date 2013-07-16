-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2012 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id: 2012100101_to_2012100901.sql 5276 2012-10-11 07:36:37Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ログインユーザマスター
ALTER TABLE _login_user ADD lu_tmp_password          CHAR(32)       DEFAULT ''                    NOT NULL;      -- 仮パスワード(MD5)

-- メール内容
DELETE FROM _mail_form WHERE mf_id = 'send_tmp_password';
INSERT INTO _mail_form (mf_id,           mf_language_id, mf_subject,         mf_content,                                                                 mf_create_dt) 
VALUES                 ('send_tmp_password', 'ja',           '仮パスワード送信', '仮パスワードを送信します。\nこのパスワードでログインし、パスワードを再設定してください。\n\nパスワード　[#PASSWORD#]',                               now());

-- *** システム標準テーブル ***
