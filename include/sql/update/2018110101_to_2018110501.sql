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
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ウィジェットカテゴリマスター
DELETE FROM _widget_category;
INSERT INTO _widget_category
(wt_id,         wt_parent_id,  wt_name,                wt_sort_order, wt_visible) VALUES
('',            '',            'その他',               100,           true),
('content',     'content',     '汎用コンテンツ',       1,             true),
('blog',        'blog',        'ブログ',               2,             true),
('bbs',         'bbs',         'BBS',                  3,             false),
('commerce',    'commerce',    'Eコマース',            4,             false),
('photo',       'photo',       'フォトギャラリー',     5,             false),
('event',       'event',       'イベント情報',         6,             false),
('wiki',        'wiki',        'Wiki',                 7,             false),
('member',      'member',      '会員',                 9,             false),
('subcontent',  'subcontent',  '補助コンテンツ',       20,            true),
('searchform/', 'searchform/', '検索・お問い合わせ',   21,            true),
('search',      'searchform/', '検索',                 22,            true),
('form',        'searchform/', 'お問い合わせ',         23,            true),
('menu',        'menu',        'メニュー',             24,            true),
('image',       'image',       '画像',                 25,            true),
('design',      'design',      'デザイン',             26,            true),
('meta',        '',            'メタ機能',             27,            true),
('analytics',   '',            'サイト解析',           28,            true),
('admin',       'admin',       '管理画面用',           50,            true),
('test',        'test',        'テスト用',             200,           true);

-- 追加クラスマスター
DELETE FROM _addons WHERE ao_id = 'chatbotlib';
INSERT INTO _addons (ao_id,        ao_class_name, ao_name,                    ao_description, ao_index)
VALUES              ('chatbotlib', 'chatbotLib',  'チャットボットライブラリ', '',             3);

-- *** システム標準テーブル ***
-- チャットボット対話ログトラン
DROP TABLE IF EXISTS chatbot_log;
CREATE TABLE chatbot_log (
    cb_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    cb_message           TEXT                                         NOT NULL,      -- 対話入力メッセージ
    cb_bot_message       TEXT                                         NOT NULL,      -- チャットボット応対メッセージ
	cb_dest_message      TEXT                                         NOT NULL,      -- サーバ処理済み応対メッセージ
    cb_type              VARCHAR(4)     DEFAULT ''                    NOT NULL,      -- チャットボットタイプ(luis=Microsoft LUIS,repl=DOCOMO Repl-AI)
    cb_widget_id         VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- ウィジェットID(ファイル名)
    cb_client_id         CHAR(32)       DEFAULT ''                    NOT NULL,      -- クライアントID
    cb_access_log_serial INT            DEFAULT 0                     NOT NULL,      -- アクセスログシリアル番号
    cb_dt                TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 記録日時
    PRIMARY KEY          (cb_serial)
) ENGINE=innodb;
