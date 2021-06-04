-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2021 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @link       http://magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- システム設定マスター
INSERT INTO _system_config 
(sc_id,                sc_value,            sc_name) VALUES
('daily_job',          '0',                 '日次処理実行'),
('daily_job_hour',     '3',                 '日次処理実行時間'), -- 0-23
('daily_job_dt',       '',                  '日次処理完了日時'),
('monthly_job',        '0',                 '月次処理実行'),
('monthly_job_hour',   '5',                 '月次処理実行時間'), -- 0-23
('monthly_job_dt',     '',                  '月次処理完了日時');

-- *** システム標準テーブル ***

