-- テスト用テーブル
DELETE FROM _widgets WHERE wd_id = 'test_install';
INSERT INTO _widgets
(wd_id,          wd_name,              wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,         wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('test_install', 'インストールテスト', '1.1.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'インストールテスト用', 0,               1, -1, now(), now());

UPDATE test_table SET xx_name = 'ニホンゴ' WHERE xx_id = '01';
UPDATE test_table SET xx_name = 'エイゴ' WHERE xx_id = '02';
