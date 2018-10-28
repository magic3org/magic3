-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2018 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id$
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- Magic3 v3.0バージョンアップ用スクリプト最終版
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- システム設定マスター
DELETE FROM _system_config WHERE sc_id = 'site_mobile_in_public'; -- 携帯用サイト公開

DELETE FROM _system_config WHERE sc_id = 'site_mobile_url'; -- 携帯用サイトURL
DELETE FROM _system_config WHERE sc_id = 'mobile_auto_redirect'; -- 携帯アクセスの自動遷移
DELETE FROM _system_config WHERE sc_id = 'mobile_use_session'; -- 携帯セッション管理
DELETE FROM _system_config WHERE sc_id = 'mobile_encoding'; -- 携帯用出力変換エンコード
DELETE FROM _system_config WHERE sc_id = 'mobile_charset'; -- 携帯HTML上でのエンコーディング表記
DELETE FROM _system_config WHERE sc_id = 'mobile_default_template'; -- 携帯画面用デフォルトテンプレート
DELETE FROM _system_config WHERE sc_id = 'mobile_default_menu_id'; -- WordPressテンプレートで使用(現在未使用)

-- ウィジェット情報
DELETE FROM _widgets WHERE wd_mobile = true;

-- ページIDマスター
DELETE FROM _page_id WHERE pg_id = 'm_index' AND pg_type = 0;

-- ページ情報マスター
DELETE FROM _page_info WHERE pn_id = 'm_index';

-- テンプレート情報
DELETE FROM _templates WHERE tm_mobile = true;

-- *** システム標準テーブル ***
