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
-- * @version    SVN: $Id: 2009101101_to_2009101301.sql 2419 2009-10-16 04:31:02Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ウィジェットパラメータマスター
ALTER TABLE _widget_param ADD wp_meta_title        TEXT                                         NOT NULL;      -- METAタグ、タイトル
ALTER TABLE _widget_param ADD wp_meta_description  TEXT                                         NOT NULL;      -- METAタグ、ページ要約
ALTER TABLE _widget_param ADD wp_meta_keywords     TEXT                                         NOT NULL;      -- METAタグ、検索用キーワード

-- キャッシュトラン
ALTER TABLE _cache ADD ca_meta_title        TEXT                                         NOT NULL;      -- METAタグ、タイトル
ALTER TABLE _cache ADD ca_meta_description  TEXT                                         NOT NULL;      -- METAタグ、ページ要約
ALTER TABLE _cache ADD ca_meta_keywords     TEXT                                         NOT NULL;      -- METAタグ、検索用キーワード

-- *** システム標準テーブル ***
-- コンテンツ(検索対象となる)マスター
ALTER TABLE content ADD cn_meta_title       TEXT                                         NOT NULL;      -- METAタグ、タイトル

-- *** EC用テーブル ***
-- 商品情報マスター
ALTER TABLE product ADD pt_meta_title        TEXT                                         NOT NULL;      -- METAタグ、タイトル
ALTER TABLE product ADD pt_meta_description  TEXT                                         NOT NULL;      -- METAタグ、ページ要約
ALTER TABLE product ADD pt_meta_keywords     TEXT                                         NOT NULL;      -- METAタグ、検索用キーワード

