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
('helplink', 1,     1,        '',       '使い方を簡単に理解するには？', 'チュートリアルマニュアル(http://doc.magic3.org/index.php?%E3%83%81%E3%83%A5%E3%83%BC%E3%83%88%E3%83%AA%E3%82%A2%E3%83%AB)を参考にします。'),
('helplink', 2,     2,        'task=configsite',       'サイト運用に必要な最小限の設定を行うには？', '「サイト情報」の必須項目を設定します。'),
('helplink', 3,     3,        'task=initwizard_content',       '表示するコンテンツや機能を選択するには？', '「管理画面カスタムウィザード」の「コンテンツ」画面を設定し、次へ進みます。'),
('helplink', 4,     4,        'task=initwizard_page2',       'デフォルトページを変更するには？', '「管理画面カスタムウィザード」の「ページ2」画面を設定し、次へ進みます。'),
('helplink', 5,     5,        'task=initwizard_menu',       'メニューを階層化して定義するには？', '「管理画面カスタムウィザード」の「メニュー」画面を設定し、次へ進みます。'),
('helplink', 6,     6,        'task=installdata',       'サンプルデータをインストールするには？', '「データインストール」画面からインストールするデータを選択し「インストール」ボタンを押します。公式サイトのデータを取得することも出来ます。'),
('helplink', 7,     7,        'task=templist',       'テンプレートを追加するには？', '「テンプレート管理」画面の「アップロード」ボタンからZIP圧縮形式のテンプレートを追加します。'),
('helplink', 8,     8,        '',                    'テンプレートを作成するには？', 'Artisteer(http://www.artisteer.com/)やThemler(https://themler.com/)などのツールが利用できます。Joomla!タイプでテンプレートを作成します。'),
('helplink', 9,     9,        'task=configsys',       'サイトを非公開にするには？', '「システム基本設定」画面の「サイトの状態」のボタンで制御します。'),
('helplink', 10,     10,        'task=configmessage',       'サイト非公開時のメッセージを変更するには？', '「メッセージ設定」画面の「サイトメンテナンス中」を変更します。'),
('helplink', 11,     11,        'task=userlist',       '管理者のパスワードを変更するには？', '「ユーザ一覧」画面から管理者を選択し、パスワードを変更します。'),
('helplink', 12,     12,        'task=configsys',       'SSLを設定するには？', '「システム基本設定」画面の「SSL」の項目にチェックを入れると「https://～」でアクセスできるようになります。'),
('helplink', 13,     13,        'task=configsys',       '共有SSLを設定するには？', '「システム基本設定」画面の「SSL」の「フロント画面にSSLを使用」にチェックを入れ、「共有SSLのルートURL」に共有SSL用のURLを設定します。'),
('helplink', 14,     14,        'task=configsys',       'イントラネット運用するには？', '「システム基本設定」画面の「ネットワーク」の「イントラネット運用」にチェックを入れます。Googleマップ等外部サービスにアクセスする機能が停止になります。'),
('helplink', 15,     15,        '',       'サイトのURLを変更するには？', '管理画面からは変更できません。直接、[Magic3ルート]/include/siteDef.phpファイルを修正します。'),
('helplink', 16,     16,        '',                    'GoogleマップAPIキーを取得するには？', 'Googleのサイト(https://developers.google.com/maps/web/)画面右上の「キーを取得」から取得できます。');
