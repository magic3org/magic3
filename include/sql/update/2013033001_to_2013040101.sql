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
-- * @version    SVN: $Id: 2013033001_to_2013040101.sql 5912 2013-04-07 07:42:11Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***

-- *** システム標準テーブル ***
-- 汎用コンテンツ設定マスター
INSERT INTO content_config
(ng_type,      ng_id,              ng_value,                              ng_name,                              ng_index) VALUES
('',           'use_content_template',      '0', 'コンテンツ単位のテンプレート設定', 0);

-- 汎用コンテンツマスター
ALTER TABLE content ADD cn_template_id       VARCHAR(50)    DEFAULT ''                    NOT NULL;      -- テンプレートID

-- 追加クラスマスター
INSERT INTO _addons
(ao_id,     ao_class_name, ao_name,            ao_description) VALUES
('contentlib', 'contentLib',     '汎用コンテンツライブラリ', '');