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
-- * @version    SVN: $Id: 2013061301_to_2013062901.sql 6159 2013-07-02 00:06:40Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- 添付ファイルマスター
ALTER TABLE _attach_file ADD af_original_url              TEXT                                         NOT NULL;      -- 取得元URL

-- システム設定マスター(管理画面用デフォルトテンプレートを変更)
UPDATE _system_config SET sc_value = '1.7' WHERE sc_id = 'admin_jquery_version';

-- *** システム標準テーブル ***
-- 汎用コメント設定マスター
ALTER TABLE comment_config ADD cf_image_max_upload    INT            DEFAULT 0                     NOT NULL;      -- 画像の最大アップロード数