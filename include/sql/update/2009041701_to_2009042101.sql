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
-- * @version    SVN: $Id: 2009041701_to_2009042101.sql 1882 2009-05-10 08:50:55Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- システム設定マスター
INSERT INTO _system_config 
(sc_id,                          sc_value,                  sc_name) VALUES
('mobile_charset',               'Shift_JIS',               '携帯HTML上でのエンコーディング表記');

-- ページ定義マスター
ALTER TABLE _page_def ADD pd_title_visible        BOOLEAN        DEFAULT true                  NOT NULL;      -- タイトルを表示するかどうか

-- テンプレート情報マスター
ALTER TABLE _templates ADD tm_clean_type          INT            DEFAULT 0                     NOT NULL;      -- 出力のクリーン処理(0=処理なし,0以外=クリーン処理実行)

-- テンプレート情報
-- テンプレートを再登録
TRUNCATE TABLE _templates;
INSERT INTO _templates
(tm_id,                     tm_name,                   tm_type, tm_mobile, tm_clean_type, tm_create_dt) VALUES
('jt_millennium_expresso',  'jt_millennium_expresso',  0,       false,     0,             now()),
('baladibol',               'baladibol',               0,       false,     0,             now()),
('ellipse',                 'ellipse',                 0,       false,     0,             now()),
('freshgreen',              'freshgreen',              0,       false,     0,             now()),
('siteground44',            'siteground44',            0,       false,     0,             now()),
('browns',                  'browns',                  0,       false,     0,             now()),
('ec_simple_n2',            'ec_simple_n2',            0,       false,     0,             now()),
('ec_simple_n2_pink',       'ec_simple_n2_pink',       0,       false,     0,             now()),
('ec_simple_n3',            'ec_simple_n3',            0,       false,     0,             now()),
('ec_simple_n3_aqua',       'ec_simple_n3_aqua',       0,       false,     0,             now()),
('ec_simple_w2',            'ec_simple_w2',            0,       false,     0,             now()),
('ec_simple_w3',            'ec_simple_w3',            0,       false,     0,             now()),
('simple_green1',           'simple_green1',           0,       false,     0,             now()),
('carnival',                'carnival',                0,       false,     0,             now()),
('what',                    'what',                    0,       false,     0,             now()),
('tft0006j_back_to_nature', 'tft0006j_back_to_nature', 1,       false,     1,             now()),
('pwc007_music',            'pwc007_music',            1,       false,     1,             now()),
('m/default',               'm/default',               0,       true,      0,             now());

-- ウィジェット情報
DELETE FROM _widgets WHERE wd_id = 'templateChanger';
INSERT INTO _widgets (wd_id, wd_name, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_install_dt, wd_create_dt) VALUES
('templateChanger',    'テンプレートチェンジャー',   '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'エンドユーザからテンプレートの変更を可能にする。', 'jquery', '',         false,           false,       true,         true,        false,        false,               false,true,           0, now(), now());
DELETE FROM _widgets WHERE wd_id = 'youtube2';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_install_dt, wd_create_dt) VALUES
('youtube2',     'YouTube2',           'YOUT', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'YouTubeの投稿動画を表示。',         false,           false,       true,         true,        true,        false,               true,true,           0, now(), now());
DELETE FROM _widgets WHERE wd_id = 'm/adtag';
INSERT INTO _widgets
(wd_id,      wd_name,   wd_version, wd_params, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_mobile, wd_has_admin, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('m/adtag', '広告タグ', '1.0.0',    '',        'Naoki Hirata', 'Magic3.org', 'GPL',      10, '広告タグを埋め込む。',               true,      true,         true,              now(),         now());

UPDATE _widgets SET wd_name = 'デフォルトメインメニュー(廃止予定)', wd_status = -1 WHERE wd_id = 'default_mainmenu' AND wd_deleted = false;

-- *** システム標準テーブル ***
