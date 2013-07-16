-- ウィジェット登録
DELETE FROM _widgets WHERE wd_id = 's/slide_menu';
INSERT INTO _widgets
(wd_id,        wd_name,           wd_type, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type, wd_add_script_lib, wd_add_script_lib_a,                         wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('s/slide_menu', 'スライドメニュー', 'menu', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'スライドオープンできる2階層のメニューです。共通のメニュー定義を使用します。', 2, 'jquery', 'jquery', true,  true,              true, 3,          1, now(),         now());
