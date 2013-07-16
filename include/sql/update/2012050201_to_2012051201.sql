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
-- * @version    SVN: $Id: 2012050201_to_2012051201.sql 4910 2012-05-18 05:20:20Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***

-- ユーザアクセスログトラン
ALTER TABLE _access_log ADD al_is_first      BOOLEAN        DEFAULT false                 NOT NULL;      -- 最初のアクセスかどうか(クッキー値でチェック)
ALTER TABLE _access_log ADD al_analyzed      BOOLEAN        DEFAULT false                 NOT NULL;      -- ログ解析完了かどうか

-- ページ情報マスター
ALTER TABLE _page_info ADD pn_head_others              TEXT                                         NOT NULL;      -- HEADタグ追加
