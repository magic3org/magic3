-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2009 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id: 2009022501_to_2009030901.sql 1572 2009-03-12 02:46:52Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ウィジェット情報マスター
DELETE FROM _widgets WHERE wd_id = 'css_add';
INSERT INTO _widgets
(wd_id,   wd_name, wd_version, wd_params, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_install_dt, wd_create_dt) VALUES
('css_add', 'CSS追加',  '1.0.0',    '',        'Naoki Hirata', 'Magic3.org', 'GPL',      10, 'テンプレートのCSSに加えてCSS定義を追加するためのウィジェットです。画面上には何も表示されません。',               false,           false,       true,         true,        true,         false,                                true,                true,              0,               now(),         now());

-- *** EC用テーブル ***
-- Eコマース設定マスター
INSERT INTO commerce_config (cg_id,                     cg_value, cg_name,                        cg_index)
VALUES                      ('permit_non_member_order', '0',      '非会員からの注文受付',           0);

-- 商品注文書トラン
ALTER TABLE order_sheet ADD oe_client_id      CHAR(32)       DEFAULT ''                     NOT NULL;      -- クライアントID
ALTER TABLE order_sheet ADD oe_custm_email    VARCHAR(40)    DEFAULT ''                     NOT NULL;      -- Eメールアドレス
ALTER TABLE order_sheet ADD oe_deliv_email    VARCHAR(40)    DEFAULT ''                     NOT NULL;      -- Eメールアドレス
ALTER TABLE order_sheet ADD oe_bill_email     VARCHAR(40)    DEFAULT ''                     NOT NULL;      -- Eメールアドレス

-- 商品受注トラン
ALTER TABLE order_header ADD or_custm_email   VARCHAR(40)    DEFAULT ''                     NOT NULL;      -- Eメールアドレス
ALTER TABLE order_header ADD or_deliv_email   VARCHAR(40)    DEFAULT ''                     NOT NULL;      -- Eメールアドレス
ALTER TABLE order_header ADD or_bill_email    VARCHAR(40)    DEFAULT ''                     NOT NULL;      -- Eメールアドレス

