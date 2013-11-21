-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2010 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id: 2009121701_to_2010010501.sql 2809 2010-01-24 10:06:04Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------
-- *** システムベーステーブル ***
-- ウィジェット情報
DELETE FROM _widgets WHERE wd_id = 'bbs_2ch_main';
INSERT INTO _widgets
(wd_id,          wd_name,                  wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                wd_read_scripts, wd_read_css, wd_add_script_lib, wd_add_script_lib_a, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('bbs_2ch_main', '2ちゃんねる風BBSメイン', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '2ちゃんねる風掲示板のメイン', false,           false,       'jquery.cookie',                       '', true,         true,        true,         false,               false,true,           0, 2, 2, now(), now());

-- *** システム標準テーブル ***
-- BBS(2ch)設定マスター
DROP TABLE IF EXISTS bbs_2ch_config;
CREATE TABLE bbs_2ch_config (
    tg_board_id          VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- 掲示板ID(空文字列=デフォルト)
    tg_id                VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- ID(key)
    tg_value             TEXT                                         NOT NULL,      -- 値
    tg_name              VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 名称
    tg_description       VARCHAR(80)    DEFAULT ''                    NOT NULL,      -- 説明
    tg_index             INT            DEFAULT 0                     NOT NULL,      -- ソート用
    PRIMARY KEY          (tg_board_id,  tg_id)
) TYPE=innodb;

-- BBS(2ch)スレッドマスター
DROP TABLE IF EXISTS bbs_2ch_thread;
CREATE TABLE bbs_2ch_thread (
    th_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    th_board_id          VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- 掲示板ID(空文字列=デフォルト)
    th_id                CHAR(32)       DEFAULT ''                    NOT NULL,      -- スレッドID
    
    th_subject           TEXT                                         NOT NULL,      -- 件名
    th_message_count     INT            DEFAULT 0                     NOT NULL,      -- 投稿数
    th_access_count      INT            DEFAULT 0                     NOT NULL,      -- 参照数
    th_dt                TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 日時
    th_log_serial        INT            DEFAULT 0                     NOT NULL,      -- BBSアクセスログシリアル番号
    
    th_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    th_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    th_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    th_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    th_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (th_serial),
    UNIQUE               (th_board_id,  th_id)
) TYPE=innodb;

-- BBS(2ch)スレッドメッセージトラン
DROP TABLE IF EXISTS bbs_2ch_thread_message;
CREATE TABLE bbs_2ch_thread_message (
    te_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    te_board_id          VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- 掲示板ID(空文字列=デフォルト)
    te_thread_id         CHAR(32)       DEFAULT ''                    NOT NULL,      -- スレッドID
    te_index             INT            DEFAULT 0                     NOT NULL,      -- 投稿番号(1以上)
    
    te_user_name         VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- 投稿者名
    te_email             VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- Eメールアドレス
    te_message           TEXT                                         NOT NULL,      -- 投稿文
    te_status_param      TEXT                                         NOT NULL,      -- 投稿文状態
    te_regist_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 投稿日時
    te_log_serial        INT            DEFAULT 0                     NOT NULL,      -- BBSアクセスログシリアル番号

    te_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    te_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    te_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (te_serial),
    UNIQUE               (te_board_id,  te_thread_id,  te_index)
) TYPE=innodb;

INSERT INTO bbs_2ch_config 
(tg_id,                   tg_value,                     tg_name) VALUES
('title',                 '掲示板',                     '掲示板タイトル'),
('top_image',             'tubo.gif',                   'トップ表示画像'),
('title_color',           '#000000',                    'タイトル背景色'),
('top_link',              '',                           'トップ画像のリンク先'),
('bg_color',              '#EFEFEF',                    '背景色'),
('bg_image',            'ba.gif',                     '背景画像'),
('noname_name',           '名無しさん＠お腹いっぱい。', '名前未設定時の表示名'),
('makethread_color',      '#CCFFCC',                    ''),
('menu_color',            '#CCFFCC',                    ''),
('thread_color',          '#EFEFEF',                    ''),
('text_color',            '#000000',                    ''),
('name_color',            'green',                      '投稿者名文字色'),
('link_color',            '#0000FF',                    ''),
('alink_color',           '#FF0000',                    ''),
('vlink_color',           '#660099',                    ''),
('err_message_color',     '#FF0000',                    'エラーメッセージ文字色'),
('subject_color',         '#FF0000',                    '件名文字色'),
('thread_count',         '10',                         'トップ画面に表示するスレッド最大数'),
('menu_thread_count',       '40',                         'メニューに表示するスレッド最大数'),
('res_count',             '10',                         'トップ画面に表示するレス最大数'),
('link_number',           '15',                         ''),
('unicode',               'pass',                       ''),
('delete_name',           'あぼーん',                   ''),
('subject_length',         '200',                   '件名最大長'),
('name_length',            '60',                   '投稿者名最大長'),
('email_length',           '60',                   'emailアドレス最大長'),
('message_length',         '2000',                   '投稿文最大長'),
('line_length',            '300',                   '投稿文行長'),
('line_count',             '50',                   '投稿文行数'),
('res_anchor_link_count',  '10',                   'レスアンカーリンク数'),
('thread_tatesugi',       '',                   ''),
('nanashi_check',         '',                   ''),
('timecount',             '',                   ''),
('timeclose',             '',                   ''),
('proxy_check',           '',                   ''),
('oversea_thread',        '',                   ''),
('oversea_proxy',         '',                   ''),
('disp_id',               '',                   ''),
('force_id',              '',                   ''),
('no_id',                 '',                   ''),
('keeplogcount',          '4096',                   'ログファイル保持数'),
('thread_res',            '500',                   '1スレッドに投稿できるレス数の上限'),
('thread_max_msg',        'あれ、<NUM>超えちゃったみたい…書き込めないや…<br />　　　 ∧∧ 　　　　　　　　　　 ∧,,∧<br />　　　（；ﾟДﾟ） 　　　　　　　　　ミﾟДﾟ,,彡 　おｋｋ<br />　　　ﾉ つ▼〔|￣￣］ 　　　　 ▽⊂　ﾐ 　　　新スレいこうぜ<br />　～（,,⊃〔￣||====]～～［］⊂,⊂,,,;;ﾐ@',                   'レスオーバー時のメッセージ'),
('thread_bytes',          '524288',                   '1スレッドの上限(バイト)'),
('file_upload',                '0',                   'ファイルアップ許可'),
('max_bytes',             '300000',                   'アップロード上限(バイト)'),
('max_w',                 '120',                   'サムネイル画像の幅'),
('max_h',                 '160',                   'サムネイル画像の高さ'),
('teletype',              '1',                   '等幅フォント機能'),
('name_774',              '1',                   'スレッド内名無し名変更機能'),
('force_774',             '1',                   '名無しへ強制変更機能'),
('force_no_id',           '1',                   'IDなし機能'),
('force_sage',            '1',                   'sage強制機能'),
('force_stars',           '1',                   'レス要キャップ機能'),
('force_normal',          '1',                   'スレッド内VIP機能解除'),
('force_name',            '1',                   '名前入力強制機能'),
('force_up',              '0',                   'アップロード機能'),
('gz_flag',               '0',                   'gzip圧縮をする'),
('jikan_kisei',           '0',                   '時間規制'),
('jikan_start',           '22',                   '規制開始時間(0-23)'),
('jikan_end',             '2',                   '規制終了時間(0-23)'),
('bbs_guide',             '掲示板の規則等を書いてください。',                    '掲示板規則'),
('bbs_style',             '1',                   '掲示板のスタイル(0=テンプレート、1=2ch)'),
('show_email',            '0',                   'Eメールアドレスを表示'),
('autolink',              '1',                   '自動的にリンクを作成'),
('msg_thread_end',        'このスレッドは${maxnum}を超えました。 <br /> もう書けないので、新しいスレッドを立ててくださいです。。。 ', 'スレッドの終了メッセージ');

