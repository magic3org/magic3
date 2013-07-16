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
-- * @version    SVN: $Id: 2012042401_to_2012050201.sql 4897 2012-05-03 12:02:53Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ページ定義マスター
ALTER TABLE _page_def ADD pd_top_content             TEXT                                         NOT NULL;      -- 上部コンテンツ
ALTER TABLE _page_def ADD pd_bottom_content             TEXT                                         NOT NULL;      -- 下部コンテンツ
ALTER TABLE _page_def ADD pd_show_readmore BOOLEAN        DEFAULT false                  NOT NULL;      -- 「もっと読む」ボタンを表示するかどうか
ALTER TABLE _page_def ADD pd_readmore_title             VARCHAR(40)    DEFAULT ''                    NOT NULL;      -- 「もっと読む」タイトル
ALTER TABLE _page_def ADD pd_readmore_url             TEXT                                         NOT NULL;      -- 「もっと読む」リンク先URL

