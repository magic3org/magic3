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
-- * @version    SVN: $Id: 2012090701_to_2012091901.sql 5252 2012-09-29 14:10:42Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ページIDマスター
DELETE FROM _page_id WHERE pg_id = 'reguser' AND pg_type = 1;
INSERT INTO _page_id 
(pg_id,         pg_type, pg_default_sub_id, pg_path,       pg_name,                         pg_description,                       pg_priority, pg_device_type, pg_active, pg_visible, pg_mobile, pg_editable, pg_admin_menu, pg_analytics) VALUES
('reguser',     1,       '',                '',            'ユーザ登録',                    'ユーザ登録画面用',                 12,          0,              true,      true,      false,     true, false, false);

-- *** システム標準テーブル ***
-- ブログ設定マスター
INSERT INTO blog_config
(bg_id,                   bg_value,    bg_name,                              bg_index) VALUES
('layout_comment_list',   '[#AVATAR#]<dl><dt>[#TITLE#] <small>[#CM_AUTHOR#] [#CM_DATE#] [#CM_TIME#] [#URL#]</small></dt><dd>[#BODY#]</dd></dl>', 'コンテンツレイアウト(コメント一覧)',               0),
('s:layout_comment_list', '[#AVATAR#]<dl><dt>[#TITLE#] <small>[#CM_AUTHOR#] [#CM_DATE#] [#CM_TIME#] [#URL#]</small></dt><dd>[#BODY#]</dd></dl>', 'コンテンツレイアウト(コメント一覧)',               0);