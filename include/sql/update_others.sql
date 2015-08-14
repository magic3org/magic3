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
(ni_nav_id,  ni_id, ni_index, ni_url,        ni_name, ni_help_body) VALUES
('helplink', 1,     1,        'task=configsite',       'サイト運用に必要な最小限の設定を行うには？', '「サイト情報」の必須項目を設定します。'),
('helplink', 2,     2,        'task=initwizard_content',       '表示するコンテンツや機能を選択するには？', '「システム初期化ウィザード」の「コンテンツ」画面を設定し、次へ進みます。'),
('helplink', 3,     3,        'task=initwizard_page2',       'デフォルトページを変更するには？', '「システム初期化ウィザード」の「ページ2」画面を設定し、次へ進みます。'),
('helplink', 4,     4,        'task=initwizard_menu',       'メニューを階層化して定義するには？', '「システム初期化ウィザード」の「メニュー」画面を設定し、次へ進みます。'),
('helplink', 5,     5,        'task=installdata',       'サンプルデータをインストールするには？', '「データインストール」画面からインストールするデータを選択し「インストール」ボタンを押します。公式サイトのデータを取得することも出来ます。'),
('helplink', 6,     6,        'task=templist',       'テンプレートを追加するには？', '「テンプレート管理」画面の「アップロード」ボタンからZIP圧縮形式のテンプレートを追加します。'),
('helplink', 7,     7,        'task=configsys',       'サイトを非公開にするには？', '「システム基本設定」画面の「サイトの状態」のボタンで制御します。'),
('helplink', 8,     8,        'task=configmessage',       'サイト非公開時のメッセージを変更するには？', '「メッセージ設定」画面の「サイトメンテナンス中」を変更します。'),
('helplink', 9,     9,        'task=userlist',       '管理者のパスワードを変更するには？', '「ユーザ一覧」画面から管理者を選択し、パスワードを変更します。'),
('helplink', 10,     10,        'task=configsys',       '共有SSLを設定するには？', '「システム基本設定」画面の「SSL」の「一般画面にSSLを使用」にチェックを入れ、「共有SSLのルートURL」に共有SSL用のURLを設定します。'),
('helplink', 11,     11,        'task=configsys',       'イントラネット運用するには？', '「システム基本設定」画面の「ネットワーク」の「イントラネット運用」にチェックを入れます。Googleマップ等外部サービスにアクセスする機能が停止になります。');
