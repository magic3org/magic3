-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2014 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id$
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- システム設定マスター
DELETE FROM _system_config WHERE sc_id = 'awstats_data_path';
INSERT INTO _system_config 
(sc_id,               sc_value,           sc_name) VALUES
('awstats_data_path', '../tools/awstats', 'Awstatsデータのデータパス');

-- ウィジェット情報マスター
ALTER TABLE _widgets ADD wd_template_type       TEXT                                         NOT NULL;      -- 対応するテンプレートタイプ(「,」区切りで指定。値=bootstrap,jquerymobile)
ALTER TABLE _widgets ADD wd_latest_version      VARCHAR(10)    DEFAULT ''                    NOT NULL;      -- 最新バージョンのバージョン文字列

-- *** システム標準テーブル ***

