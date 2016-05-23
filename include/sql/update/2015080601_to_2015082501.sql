-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2015 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id$
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- システム設定マスター
INSERT INTO _system_config 
(sc_id,                          sc_value,                  sc_name) VALUES
('default_sub_template',            '',            'PCフロント画面用デフォルトサブテンプレート');

-- ページ情報マスター
ALTER TABLE _page_info ADD pn_sub_template_id           VARCHAR(20)    DEFAULT ''                    NOT NULL;      -- サブテンプレートID

-- テンプレート情報マスター
ALTER TABLE _templates ADD tm_generator      VARCHAR(20)    DEFAULT ''                    NOT NULL;      -- テンプレート作成アプリケーション(値=artisteer,themler)

-- テンプレート情報
DELETE FROM _templates WHERE tm_id = 'themler_sample1';
INSERT INTO _templates
(tm_id,                    tm_name,                 tm_type, tm_generator, tm_version) VALUES
('themler_sample1',        'themler_sample1',       2,       'themler',    '1.0.39');

