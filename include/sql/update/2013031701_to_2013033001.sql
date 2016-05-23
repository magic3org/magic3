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
-- * @version    SVN: $Id: 2013031701_to_2013033001.sql 5890 2013-04-01 03:21:36Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***

-- システム設定マスター
-- システムの動作に影響する設定を管理する
INSERT INTO _system_config 
(sc_id,               sc_value,                  sc_name) VALUES
('default_theme',         'black-tie',                'フロント画面用jQueryUIテーマ');

-- *** システム標準テーブル ***

