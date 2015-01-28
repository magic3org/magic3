-- テスト用テーブル
DELETE FROM _widgets WHERE wd_id = 'test_ckeditor';
INSERT INTO _widgets
(wd_id,          wd_name,              wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_has_admin,        wd_add_script_lib_a, wd_install_dt, wd_create_dt) VALUES
('test_ckeditor', 'CKEditorテスト', '1.1.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'CKEditorテスト用', true, 'elfinder21', now(), now());
