-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2020 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id$
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- テンプレート情報マスター
ALTER TABLE _templates MODIFY tm_custom_params     MEDIUMTEXT                              NOT NULL;      -- カスタマイズ用パラメータ

-- *** システム標準テーブル ***
-- 不要テーブル削除(ウィジェットパラメータ更新マスター)
DROP TABLE IF EXISTS _widget_param_update;

-- 汎用コンテンツマスター
ALTER TABLE content ADD cn_generator         VARCHAR(20)    DEFAULT ''                    NOT NULL;      -- コンテンツ作成アプリケーション(値=artisteer,themler,nicepage)

-- テンプレート情報
DELETE FROM _templates WHERE tm_id = 'nicepage_sample4';
INSERT INTO _templates
(tm_id,              tm_name,             tm_type, tm_generator, tm_version) VALUES
('nicepage_sample4', 'nicepage_sample4',  2,       'nicepage',   '2.25.0');
