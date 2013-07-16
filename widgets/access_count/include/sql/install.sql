-- ウィジェット登録
DELETE FROM _widgets WHERE wd_id = 'access_count';
INSERT INTO _widgets
(wd_id,          wd_name,              wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,               wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('access_count', 'アクセスカウンター', '2.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'サイトのアクセス数を表示。', true,         false,true,           0, 0, now(), now());
