-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2011 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id: 2011012801_to_2011021901.sql 4005 2011-02-19 07:50:46Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ページ定義マスター
ALTER TABLE _page_def ADD pd_view_control_type INT            DEFAULT 0                     NOT NULL;      -- 表示出力の制御タイプ(0=常時表示、1=ログイン時のみ表示、2=非ログイン時のみ表示)
ALTER TABLE _page_def ADD pd_view_option             TEXT                                         NOT NULL;      -- 表示オプション
