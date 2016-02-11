-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2016 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id$
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- システム設定マスター
INSERT INTO _system_config 
(sc_id,                          sc_value,                  sc_name) VALUES
('system_type',                 '',                   'システム運用タイプ'),

-- ウィジェット情報マスター
ALTER TABLE _widgets ADD wd_visible_condition       TEXT                                         NOT NULL;      -- ウィジェット表示条件。「キー=値」の形式でURLクエリーパラメータを指定。複数のクエリーパラメータ条件は「,」で区切り、条件のまとまりは「;」で区切る。)