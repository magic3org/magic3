-- テスト用テーブル
DELETE FROM _widgets WHERE wd_id = 'test_install';
INSERT INTO _widgets
(wd_id,          wd_name,              wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,         wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('test_install', 'インストールテスト', '1.1.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'インストールテスト用', 0,               1, -1, now(), now());

DROP TABLE IF EXISTS test_table;
CREATE TABLE test_table (
    xx_id                VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- ID
    xx_name              VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 文字列
    PRIMARY KEY  (xx_id)
) TYPE=innodb;
INSERT INTO test_table (xx_id, xx_name) VALUES
('01', '日本語'),
('02', '英語');