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
-- * @version    SVN: $Id: 2009120801_to_2009121701.sql 2703 2009-12-17 09:36:35Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システム標準テーブル ***
-- ウィジェット情報
DELETE FROM _widgets WHERE wd_id = 'portal_updateinfo';
INSERT INTO _widgets
(wd_id,              wd_name,                          wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_has_rss, wd_install_dt, wd_create_dt) VALUES
('portal_updateinfo', 'コンテンツ更新情報(ポータル用)', '1.0.0',    '株式会社 毎日メディアサービス', '株式会社 毎日メディアサービス', 'GPL',      10,                'テナントサーバのコンテンツ更新情報を表示', true,         false,               false,                true, 0,          0, true, now(),         now());
