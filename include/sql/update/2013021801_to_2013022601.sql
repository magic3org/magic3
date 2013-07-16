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
-- * @version    SVN: $Id: 2013021801_to_2013022601.sql 5756 2013-02-28 13:57:25Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***

-- *** システム標準テーブル ***
-- 汎用コンテンツマスター
ALTER TABLE content ADD cn_script_lib    TEXT                                         NOT NULL;      -- 共通スクリプトライブラリ(ライブラリ名で指定、「,」区切りで複数指定可)
ALTER TABLE content ADD cn_script        TEXT                                         NOT NULL;      -- Javascriptスクリプト

-- 汎用コンテンツ設定マスター
INSERT INTO content_config
(ng_type,      ng_id,              ng_value,                              ng_name,                              ng_index) VALUES
('',           'use_jquery',      '0', 'jQueryスクリプト作成', 0),
('smartphone', 'use_jquery',      '0', 'jQueryスクリプト作成', 0);
