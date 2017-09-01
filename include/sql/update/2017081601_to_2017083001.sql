-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2017 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id$
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***

-- テンプレート情報
DELETE FROM _templates WHERE tm_id = 'shop-isle';
INSERT INTO _templates
(tm_id,                     tm_name,          tm_type, tm_device_type, tm_mobile, tm_use_bootstrap, tm_available, tm_clean_type, tm_info_url, tm_create_dt) VALUES
('shop-isle',                'shop-isle',       100,       0,              false,     false,             true,        0,             'https://themeisle.com/',          now());

-- *** システム標準テーブル ***
-- 商品情報マスター
ALTER TABLE product ADD pt_user_limited      BOOLEAN        DEFAULT false                 NOT NULL;      -- 参照ユーザを制限
ALTER TABLE product ADD pt_active_start_dt   TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL;      -- 公開期間(開始)
ALTER TABLE product ADD pt_active_end_dt     TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL;      -- 公開期間(終了)
ALTER TABLE product ADD pt_thumb_filename    TEXT                                         NOT NULL;      -- サムネールファイル名(「;」区切り)
ALTER TABLE product ADD pt_thumb_src         TEXT                                         NOT NULL;      -- サムネールの元のファイル(リソースディレクトリからの相対パス)
ALTER TABLE product ADD pt_option_fields     TEXT                                         NOT NULL;      -- 追加フィールド
