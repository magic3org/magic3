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
-- * @version    SVN: $Id: 2009052001_to_2009060401.sql 1968 2009-06-04 07:57:50Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ウィジェット情報マスター
ALTER TABLE _widgets MODIFY wd_description       TEXT                                         NOT NULL;-- 説明

UPDATE _widgets SET wd_description = '複数のメニューが作成できる1階層の新型デフォルトメニューです。サブメニューも管理できる共通のメニュー定義を使用します。ナビゲーションメニューの位置(画面上部)にある「user3」ポジションに配置するとナビゲーションメニューが表示できます。' WHERE wd_id = 'default_menu' AND wd_deleted = false;

-- *** システム標準テーブル ***
