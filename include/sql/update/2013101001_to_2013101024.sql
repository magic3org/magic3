-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2013 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id$
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- 多言語対応文字列マスター(コンテンツ種別)
INSERT INTO _language_string
(ls_type, ls_id,         ls_language_id, ls_value,               ls_name) VALUES
(2,       'dboard',      'ja',           'ダッシュボード',       'ダッシュボード'),
(2,       'search',      'ja',           '検索結果',             '検索結果'),
(2,       'content',     'ja',           '汎用コンテンツ',       '汎用コンテンツ'),
(2,       'product',     'ja',           '商品情報',             '商品情報'),
(2,       'bbs',         'ja',           'BBS',                  'BBS'),
(2,       'blog',        'ja',           'ブログ',               'ブログ'),
(2,       'wiki',        'ja',           'Wiki',                 'Wiki'),
(2,       'user',        'ja',           'ユーザ作成コンテンツ', 'ユーザ作成コンテンツ'),
(2,       'event',       'ja',           'イベント情報',         'イベント情報'),
(2,       'photo',       'ja',           'フォトギャラリー',     'フォトギャラリー');

-- *** システム標準テーブル ***
