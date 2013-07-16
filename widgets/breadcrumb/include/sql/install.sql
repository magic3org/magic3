-- ウィジェット登録
DELETE FROM _widgets WHERE wd_id = 'breadcrumb';
INSERT INTO _widgets
(wd_id,        wd_name,          wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                         wd_has_admin, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('breadcrumb', 'パンくずリスト', '1.1.0',    '株式会社 毎日メディアサービス', '株式会社 毎日メディアサービス', 'GPL',      10,                'メニュー定義からパンくずリストを作成', true,        true,           100, 0, 0, now(),    now());
