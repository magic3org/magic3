-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2013 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id: 2013020801_to_2013021801.sql 5828 2013-03-15 07:33:34Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- テンプレート情報
DELETE FROM _templates WHERE tm_id = '_admin3';
INSERT INTO _templates
(tm_id,                           tm_name,                         tm_type, tm_device_type, tm_mobile, tm_available, tm_clean_type, tm_create_dt) VALUES
('_admin3',                       '_admin3',                       2,       0,              false,     false,        0,             now());

-- 多言語対応文字列マスター
INSERT INTO _language_string
(ls_id,                     ls_language_id, ls_value,                             ls_name) VALUES
('msg_page_not_found',         'ja',           'ページが見つかりません',                 '存在しないページメッセージ');

-- システム設定マスター(管理画面用デフォルトテンプレートを変更)
UPDATE _system_config SET sc_value = '_admin3' WHERE sc_id = 'admin_default_template';
UPDATE _system_config SET sc_value = '1.8' WHERE sc_id = 'admin_jquery_version';
INSERT INTO _system_config 
(sc_id,                          sc_value,                  sc_name) VALUES
('site_mobile_url',       '',                        '携帯用サイトURL'),
('site_smartphone_url',   '',                        'スマートフォン用サイトURL'),
('multi_domain',               '0',                       'マルチドメイン運用');
-- *** システム標準テーブル ***
