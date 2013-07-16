-- ウィジェットの登録
DELETE FROM _widgets WHERE wd_id = 'custom_search_box';
INSERT INTO _widgets
(wd_id,               wd_name,            wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                                     wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('custom_search_box', 'カスタム検索連携', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'カスタム検索に検索結果を表示する検索ウィジェット', 'jquery', '',     true,         true,true,               1, -1, now(),    now());
