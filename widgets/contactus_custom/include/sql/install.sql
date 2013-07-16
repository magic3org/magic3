-- ウィジェット登録
DELETE FROM _widgets WHERE wd_id = 'contactus_custom';
INSERT INTO _widgets
(wd_id,              wd_name,                          wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                             wd_read_scripts, wd_read_css,wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('contactus_custom', 'カスタムお問い合わせ', '1.1.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'カスタマイズ可能なお問い合わせメール送信', false,           false,     '', 'jquery.tablednd', true,         false,               true,                true, 0,          0, now(),         now());
