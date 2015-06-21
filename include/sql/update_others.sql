-- *
-- * _widgetsテーブル更新スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2015 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id$
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- その他のテーブル更新スクリプト
-- 常に最新にする必要があるテーブルを更新
-- 最後に実行されるスクリプトファイル
-- --------------------------------------------------------------------------------------------------

DELETE FROM _nav_item WHERE ni_nav_id = 'helplink';
INSERT INTO _nav_item
(ni_nav_id,  ni_id, ni_index, ni_url,        ni_name) VALUES
('helplink', 1,     1,        'task=configsite',       'サイト運用に必要な最小限の設定を行うには？'),
('helplink', 2,     2,        'task=initwizard_site',       '使用する機能を選択するには？'),
('helplink', 3,     3,        'task=initwizard_site',       'デフォルトページを変更するには？'),
('helplink', 4,     4,        'task=initwizard_site',       'メニューを階層化して定義するには？'),
('helplink', 5,     5,        'task=installdata',       'サンプルデータをインストールするには？'),
('helplink', 6,     6,        'task=templist',       'テンプレートを追加するには？'),
('helplink', 7,     7,        'task=configsys',       'サイトを非公開にするには？'),
('helplink', 8,     8,        'task=configmessage',       'サイト非公開時のメッセージを変更するには？'),
('helplink', 9,     9,        'task=userlist',       '管理者のパスワードを変更するには？');

