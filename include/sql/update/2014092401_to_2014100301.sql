-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2014 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id$
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- システム設定マスター
DELETE FROM _system_config WHERE sc_id = 'site_logo_filename';
DELETE FROM _system_config WHERE sc_id = 'avatar_format';
INSERT INTO _system_config 
(sc_id,                sc_value,                            sc_name) VALUES
('site_logo_filename', 'sm=logo_80c.png;lg=logo_200c.png',        'サイトロゴファイル名'),
('avatar_format',      'sm=32c.png;md=80c.png;lg=128c.png', 'アバター仕様');


-- *** システム標準テーブル ***
