-- *
-- * バージョンアップテスト用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2008 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id: test.sql 1264 2008-11-22 02:40:42Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップテスト用スクリプト
-- --------------------------------------------------------------------------------------------------

-- 型変更
ALTER TABLE _test1 MODIFY t1_name   TEXT                                         NOT NULL,
                   MODIFY t1_index  SMALLINT         DEFAULT 0                   NOT NULL;
ALTER TABLE _test1 MODIFY t1_value  VARCHAR(1000)    DEFAULT ''                  NOT NULL;

-- フィールド追加
ALTER TABLE _test1 ADD tmp1 VARCHAR(50)    DEFAULT ''                    NOT NULL;
ALTER TABLE _test1 ADD tmp2 TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL;

ALTER TABLE _test1 ADD tmp3 TEXT                                         NOT NULL,
                   ADD tmp4 SMALLINT       DEFAULT 0                     NOT NULL,
                   ADD tmp5 TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL;
		       
-- フィールド名変更
ALTER TABLE _test1 CHANGE t1_description tmp6 TEXT                                         NOT NULL;
