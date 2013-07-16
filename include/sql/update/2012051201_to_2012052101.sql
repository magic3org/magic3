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
-- * @version    SVN: $Id: 2012051201_to_2012052101.sql 4927 2012-05-29 09:35:47Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- 言語マスター
ALTER TABLE _language MODIFY ln_id       VARCHAR(5)     DEFAULT ''                    NOT NULL;      -- 言語ID
ALTER TABLE _language MODIFY ln_name     TEXT NOT NULL;       -- 言語名称
ALTER TABLE _language ADD ln_image_filename      VARCHAR(20)    DEFAULT ''                    NOT NULL;      -- 画像ファイル名
ALTER TABLE _language ADD ln_available         BOOLEAN        DEFAULT true                  NOT NULL;      -- メニューから選択可能かどうか
DELETE FROM _language;
INSERT INTO _language
(ln_id, ln_name,          ln_name_en,   ln_priority, ln_image_filename) VALUES
('ja',  '日本語',         'Japanese',   0,           'jp.png'),
('en',  '英語',           'English',    1,           'gb.png'),
('de',  'ドイツ語',       'German',     2,           'de.png'),
('fr',  'フランス語',     'French',     3,           'fr.png'),
('es',  'スペイン語',     'Spanish',    4,           'es.png'),
('it',  'イタリア語',     'Italian',    5,           'it.png'),
('pt',  'ポルトガル語',   'Portuguese', 6,           'pt.png'),
('zh',  '中国語',         'Chinese',    7,           'cn.png'),
('ko',  '韓国語',         'Korean',     8,           'kr.png'),
('th',  'タイ語',         'Thai',       9,           'th.png'),
('id',  'インドネシア語', 'Indonesian', 10,          'id.png'),
('ru',  'ロシア語',       'Russian',    11,          'ru.png'),
('ar',  'アラビア語',     'Arabic',     12,          'ae.png'),
('zh-cn',  '中国語(簡体)',         'Chinese(Simplified)',    20,           'cn.png'),
('zh-tw',  '中国語(繁体)',         'Chinese(Traditional)',    21,           'tw.png');

-- 汎用コンテンツ設定マスター
DROP TABLE IF EXISTS content_config;
CREATE TABLE content_config (
    ng_type              VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- コンテンツタイプ
    ng_id                VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- ID(key)
    ng_value             TEXT                                         NOT NULL,      -- 値
    ng_name              VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 名称
    ng_description       VARCHAR(80)    DEFAULT ''                    NOT NULL,      -- 説明
    ng_index             INT            DEFAULT 0                     NOT NULL,      -- ソート用
    PRIMARY KEY          (ng_type,      ng_id)
) TYPE=innodb;
INSERT INTO content_config
(ng_type,   ng_id,                  ng_value,    ng_name,                              ng_index) VALUES
('',        'use_password',         '0',         'パスワードアクセス制御',                 1),
('',        'password_content',         'このコンテンツはパスワードが必要です。<br />パスワードを入力してください。',         'パスワード画面コンテンツ',                 2);
