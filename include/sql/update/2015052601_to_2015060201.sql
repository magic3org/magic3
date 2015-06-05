-- *
-- * バージョンアップ用スクリプト
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
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***

-- イベント予約設定マスター
DELETE FROM evententry_config WHERE ef_id = 'layout_entry_single';
DELETE FROM evententry_config WHERE ef_id = 'msg_entry_exceed_max';
DELETE FROM evententry_config WHERE ef_id = 'msg_entry_out_of_term';
DELETE FROM evententry_config WHERE ef_id = 'msg_entry_term_expired';
DELETE FROM evententry_config WHERE ef_id = 'msg_entry_stopped';
DELETE FROM evententry_config WHERE ef_id = 'msg_entry_closed';
DELETE FROM evententry_config WHERE ef_id = 'msg_event_closed';
DELETE FROM evententry_config WHERE ef_id = 'msg_entry_user_registered';
INSERT INTO evententry_config
(ef_id,                 ef_value,    ef_name) VALUES
('layout_entry_single', '<div class="entry_info"><div style="float:left;">[#IMAGE#]</div><div class="clearfix"><div>[#CT_SUMMARY#]</div></div><div><span class="event_date">日時：[#DATE#]</span> <span class="event_location">場所：[#CT_PLACE#]</span></div><div><span class="event_contact">連絡先：[#CT_CONTACT#]</span></div></div><div class="evententry_content">[#BODY#]</div><div class="evententry_info"><div>定員: [#CT_QUOTA#]</div><div>参加: [#CT_ENTRY_COUNT#]</div></div><div><strong>会員名: [#CT_MEMBER_NAME#]</strong></div>[#BUTTON|type=ok;title=予約する|予約済み#]',         'レイアウト(記事詳細)'),
('msg_entry_exceed_max',     '予約が定員に達しました',     '予約定員オーバーメッセージ'),
('msg_entry_out_of_term',   '受付期間外です',     '受付期間外メッセージ'),
('msg_entry_term_expired',   '受付期間を終了しました',     '受付期間終了メッセージ'),
('msg_entry_stopped',        '受付は一時中断しています',   '受付中断メッセージ'),
('msg_entry_closed',         '受付を終了しました',         '受付終了メッセージ'),
('msg_event_closed',         'イベントは終了しました',         'イベント終了メッセージ'),
('msg_entry_user_registered',       'このイベントを予約しました', '予約済みメッセージ');


