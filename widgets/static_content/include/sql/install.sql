-- ウィジェット登録
DELETE FROM _widgets WHERE wd_id = 'static_content';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('static_content',    '固定コンテンツビュー', '', '1.1.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, '固定でコンテンツを表示。コンテンツはデフォルトコンテンツビューと共有。',          false,           false,       true,         true,        true,         false,               true,true,           0, 2, 1, now(), now());
