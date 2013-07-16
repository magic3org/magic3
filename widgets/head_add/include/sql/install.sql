-- ウィジェット登録
DELETE FROM _widgets WHERE wd_id = 'head_add';
INSERT INTO _widgets
(wd_id,      wd_name,    wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_has_admin, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('head_add', 'HEAD追加', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'HTMLのHEADタグ内に文字列を追加する', true,        true,           3, 1, now(), now());
