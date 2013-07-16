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
-- * @version    SVN: $Id: 2012110501_to_2012111401.sql 5409 2012-11-24 10:10:14Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- メニュー定義マスター
ALTER TABLE _menu_def ADD md_title             TEXT                                         NOT NULL;      -- タイトル(HTMLタグ可)

-- テンプレート情報
DELETE FROM _templates WHERE tm_id = '_layout';
INSERT INTO _templates
(tm_id,                           tm_name,                         tm_type, tm_device_type, tm_mobile, tm_available, tm_clean_type, tm_create_dt) VALUES
('_layout',                       '_layout',                       1,       0,              false,     false,        0,             now());

-- *** システム標準テーブル ***
-- 商品カテゴリマスター
ALTER TABLE product_category MODIFY pc_name              VARCHAR(60)    DEFAULT ''                    NOT NULL;      -- 商品カテゴリ名称

-- Eコマース設定マスター
INSERT INTO commerce_config
(cg_id,                    cg_value, cg_name,                        cg_index) VALUES
('hierarchical_category',  '1',      '階層化商品カテゴリー',         17);