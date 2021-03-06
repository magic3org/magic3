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
-- システム設定マスター
INSERT INTO _system_config 
(sc_id,                        sc_value,       sc_name) VALUES
('default_content_type',       'blog',         'デフォルトコンテンツタイプ'),                -- WordPressテンプレートで使用(現在未使用)
('default_menu_id',            'main_menu',    'フロント画面用デフォルトメニューID'),        -- WordPressテンプレートで使用(現在未使用)
('mobile_default_menu_id',     'm_main_menu',  '携帯画面用デフォルトメニューID'),            -- WordPressテンプレートで使用(現在未使用)
('smartphone_default_menu_id', 's_main_menu',  'スマートフォン画面用デフォルトメニューID');  -- WordPressテンプレートで使用(現在未使用)

-- テンプレート情報
DELETE FROM _templates WHERE tm_id = 'wisteria';
INSERT INTO _templates
(tm_id,                     tm_name,          tm_type, tm_device_type, tm_mobile, tm_use_bootstrap, tm_available, tm_clean_type, tm_info_url, tm_create_dt) VALUES
('wisteria',                'wisteria',       100,       0,              false,     false,             true,        0,             'https://wpfriendship.com/',          now());


-- *** システム標準テーブル ***
-- 汎用コンテンツマスター
ALTER TABLE content ADD cn_thumb_src     TEXT                                         NOT NULL;      -- サムネールの元のファイル(リソースディレクトリからの相対パス)

-- ブログエントリー(記事)マスター
ALTER TABLE blog_entry ADD be_thumb_src     TEXT                                         NOT NULL;      -- サムネールの元のファイル(リソースディレクトリからの相対パス)

