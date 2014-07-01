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
-- テンプレート情報マスター
ALTER TABLE _templates ADD tm_version      VARCHAR(10)    DEFAULT ''                    NOT NULL;      -- テンプレートバージョン文字列

-- ニュースメッセージトラン
ALTER TABLE news ADD nw_device_type       INT            DEFAULT 0                     NOT NULL;      -- 端末タイプ(0=PC、1=携帯、2=スマートフォン)
ALTER TABLE news ADD nw_summary           VARCHAR(100)   DEFAULT ''                    NOT NULL;      -- 概要
ALTER TABLE news ADD nw_sort_order        INT            DEFAULT 0                     NOT NULL;      -- ソート順
ALTER TABLE news ADD nw_mark              INT            DEFAULT 0                     NOT NULL;      -- 付加マーク(0=なし、1=新規)
ALTER TABLE news ADD nw_status            SMALLINT       DEFAULT 0                     NOT NULL;      -- 状態(0=未設定、1=非公開、2=公開)

-- ウィジェット情報マスター
ALTER TABLE _widgets ADD wd_content_widget_id         VARCHAR(50)    DEFAULT ''                    NOT NULL;      -- コンテンツ編集用のウィジェット

-- *** システム標準テーブル ***

