-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2018 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id$
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
DELETE FROM _mail_form WHERE mf_id = 'skyway_call';
INSERT INTO _mail_form (mf_id,           mf_language_id, mf_subject,         mf_content,                                                                 mf_create_dt) 
VALUES                 ('skyway_call', 'ja',              'SkyWay呼び出し応答用',     'SkyWay呼び出しがありました。以下のURLにアクセスすると応答できます。\n\n[#URL#]',                             now());

-- *** システム標準テーブル ***
