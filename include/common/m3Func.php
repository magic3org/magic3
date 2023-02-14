<?php
/**
 * グローバル関数
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2023 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
/**
 * ウィジェット埋め込み
 *
 * テンプレートから固定で直接ウィジェットを呼び出すための関数
 *
 * @param string $widgetId		ウィジェットID
 * @return 						なし
 */
function m3AnchorWidget($widgetId)
{
    global $gEnvManager;

    // ウィジェットのindex.phpファイルのパスを作成
    $widgetIndexFile = $gEnvManager->getWidgetsPath() . '/' . $widgetId . '/index.php';
    if (file_exists($widgetIndexFile)) {
        // 作業中のウィジェットIDを設定
        $gEnvManager->setCurrentWidgetId($widgetId);

        // ウィジェットを実行
        // ウィジェットの呼び出しは、複数回存在する可能性があるのでrequire_once()で呼び出さない
        require $widgetIndexFile;

        // 作業中のウィジェットIDを解除
        $gEnvManager->setCurrentWidgetId('');

        echo '<!-- ' . time() . ' -->' . M3_NL;
    } else {
        echo 'widget not found error: ' . $widgetId;
    }
}
/**
 * 配列を挿入する
 *
 * 配列を保持する配列の指定位置に配列を挿入する
 *
 * @param array $srcArray		挿入先の配列
 * @param array $insertArray	挿入する配列
 * @param int $position			挿入位置。-1のときは最後に追加
 * @return array				挿入後の配列
 */
function array_insert($srcArray, $insertArray, $position = -1)
{
    // 引数$arrayが配列でない場合は空配列を返す
    if (!is_array($srcArray)) {
        return [];
    }

    // 挿入位置を修正
    $position = $position == -1 ? count($srcArray) : $position;

    // 挿入する位置～末尾まで
    $lastArray = array_splice($srcArray, $position);

    // 先頭～挿入前位置までの配列に、挿入する値を追加
    array_push($srcArray, $insertArray);

    // 配列を結合
    return array_merge($srcArray, $lastArray);
}
/**
 * トリミング文字列分割
 *
 * @param string  $delimiter	区切り文字列
 * @param string  $str			変換元文字列
 * @param string  $charList		トリミングする文字のリスト
 * @return array				分割した文字列
 */
function trimExplode($delimiter, $str, $charList = '')
{
    $srcArray = explode($delimiter, $str);
    $destArray = [];
    if (empty($charList)) {
        for ($i = 0; $i < count($srcArray); $i++) {
            $value = trim($srcArray[$i]);
            if (!empty($value)) {
                $destArray[] = $value;
            }
        }
    } else {
        for ($i = 0; $i < count($srcArray); $i++) {
            $value = trim($srcArray[$i], $charList);
            if (!empty($value)) {
                $destArray[] = $value;
            }
        }
    }
    return $destArray;
}
/**
 * パスワードを生成する
 *
 * @param int     $len		文字列長
 * @param string  $str		パスワード生成のために使用する文字
 * @return string			作成したパスワード
 */
function makePassword($len, $str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-+#%?!$@*=')
{
    $l = strlen($str) - 1;
    $pwd = '';
    for ($i = 0; $i < $len; $i++) {
        // 一度使用した文字は再度使わない
        $n = (int) mt_rand(0, $l);
        $newChar = substr($str, $n, 1);
        $str = substr($str, 0, $n) . substr($str, $n + 1);
        $pwd .= $newChar;
        $l--;
    }
    return $pwd;
}
/**
 * 短いハッシュ文字列を生成する
 *
 * @param string  $data		生成元データ
 * @return string			作成したハッシュ文字列
 */
function makeShortHash($data, $algo = 'md5')
{
    return strtr(rtrim(base64_encode(pack('H*', $algo($data))), '='), '+/', '-_');
}
/**
 * 省略文字列を作成
 *
 * @param string  $str		変換元文字列
 * @param int     $len		文字列長
 * @return string			作成した文字列
 */
function makeTruncStr($str, $len)
{
    if (!isset($str)) {
        return '';
    }

    $addStr = '';
    if (function_exists('mb_substr')) {
        if (mb_strlen($str) > $len) {
            $addStr = '…';
        }
        $destStr = mb_substr($str, 0, $len) . $addStr;
    } else {
        if (strlen($str) > $len) {
            $addStr = '...';
        }
        $destStr = substr($str, 0, $len) . $addStr;
    }
    return $destStr;
}
/**
 * 文字数を取得
 *
 * @param string  $str		対象文字列
 * @return int				文字数
 */
function getLetterCount($str)
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($str);
    } else {
        return strlen($str);
    }
}
/**
 * メールアドレスの取り出し
 *
 * 文字列の中から「<」「>」で括られたメールアドレスと名前を取得する
 * 例) 名前<name@sample.domain.com> →　「名前」「name@sample.domain.com」
 *
 * @param string  $str		メールアドレスを取得する対象文字列
 * @param string  $mail		メールアドレス
 * @param string  $name		メールアドレス以外の文字列
 * @return bool				true=取得、false=取得失敗
 */
function separateMailAddress($str, &$mail, &$name)
{
    $pattern = '/<.*>/'; // メールアドレス検索パターン
    $ret = preg_match($pattern, $str, $matches);
    if ($ret == 1) {
        // メールアドレスが見つかったとき
        $mail = trim($matches[0], ' <>');
        $name = preg_replace($pattern, '', $str);
        return true;
    } else {
        return false;
    }
}
/**
 * メールアドレス形式かどうかのチェック
 *
 * @param string  $str		対象文字列
 * @return bool				true=メールアドレス形式、false=不備
 */
function isMailAddress($str)
{
    return preg_match('/[\w\d\-\.]+\@[\w\d\-\.]+/', $str);
}
/**
 * CSVファイルの切り出し
 *
 * ファイルポインタから行を取得し、解析したデータを配列で返す
 *
 * @param resource  $fp				ファイルポインタ
 * @param int		$delimType		区切りタイプ(0=カンマ、1=タブ)
 * @param string	$encode			変換元エンコーディング
 * @return array					解析後のデータ
 */
function fgetByCsv(&$fp, $delimType = 0, $encode = '')
{
    global $gEnvManager;

    if (feof($fp)) {
        return false;
    }

    // 変換元エンコーディングが設定されていないときはデフォルトを使用
    $encoding = $encode;
    if (empty($encoding)) {
        $encoding = $gEnvManager->getCsvUploadEncoding(); // デフォルトのアップロードエンコーディング取得
        if (empty($encoding)) {
            $encoding = 'SJIS-win';
        }
    }

    if ($delimType == 0) {
        $csv = '';
        while (!feof($fp)) {
            $csv .= mb_convert_encoding(fgets($fp), M3_ENCODING, $encoding);
            if (preg_match_all('/"/', $csv, $matches) % 2 == 0) {
                break;
            }
        }
        if (feof($fp)) {
            return false;
        }

        $values = [];
        $temp = preg_replace('/(?:\x0D\x0A|[\x0D\x0A])?$/', ',', $csv, 1);
        preg_match_all('/("[^"]*(?:""[^"]*)*"|[^,]*),/', $temp, $matches);

        for ($i = 0; $i < count($matches[1]); $i++) {
            if (preg_match('/^"(.*)"$/s', $matches[1][$i], $m)) {
                $matches[1][$i] = preg_replace('/""/', '"', $m[1]);
            }
            $values[] = $matches[1][$i];
        }
        return $values;
    } elseif ($delimType == 1) {
        // UTF-8に変換
        $csv = '';
        while (!feof($fp)) {
            // 空行は読み飛ばす
            $csv = mb_convert_encoding(fgets($fp), M3_ENCODING, $encoding);
            $checkCsv = trim($csv);
            if (!empty($checkCsv)) {
                break;
            }
        }
        if (feof($fp)) {
            return false;
        }

        $values = explode("\t", $csv);
        return $values;
    }
}
/**
 * ファイルの拡張子を取得
 *
 * @param string $path	ファイルパス
 * @return string		拡張子
 */
function getExtension($path)
{
    if (function_exists('mb_ereg')) {
        if (mb_ereg('^.*\.([^\.]*)$', $path, $part)) {
            return strtolower($part[1]);
        }
    } else {
        if (preg_match('/^.*\.([^\.]*)$/', $path, $part)) {
            return strtolower($part[1]);
        }
    }
    return '';
}
/**
 * ファイルの拡張子を除く
 *
 * @param string $path	ファイルパス
 * @return string		拡張子を除いたファイル名
 */
function removeExtension($path)
{
    //	if (version_compare(PHP_VERSION, '5.2.0') < 0){
    $ext = pathinfo($path, PATHINFO_EXTENSION);
    if (empty($ext)) {
        return $path;
    } else {
        return substr($path, 0, strlen($path) - strlen('.' . $ext));
    }
    //	} else {
    //		return pathinfo($path, PATHINFO_FILENAME);		// 日本語パスは解析できない
    //	}
}
/**
 * 相対パスから絶対パスを生成(パスまたはURL)
 *
 * @param string $rel			相対パス
 * @param string $base			基点のパス(パスまたはURL)
 * @return string				絶対パス
 */
function rel2abs($rel, $base)
{
    /* return if already absolute URL */
    if (parse_url($rel, PHP_URL_SCHEME) != '') {
        return $rel;
    }

    /* queries and anchors */
    if ($rel[0] == '#' || $rel[0] == '?') {
        return $base . $rel;
    }

    /* parse base URL and convert to local variables: $scheme, $host, $path */
    extract(parse_url($base));

    /* remove non-directory element from path */
    //	$path = preg_replace('#/[^/]*$#', '', $path);

    /* destroy path if relative url points to root */
    if ($rel[0] == '/') {
        $path = '';
    }

    /* dirty absolute URL // with port number if exists */
    if (parse_url($base, PHP_URL_PORT) != '') {
        $abs = "$host:" . parse_url($base, PHP_URL_PORT) . "$path/$rel";
    } else {
        $abs = "$host$path/$rel";
    }

    /* replace '//' or '/./' or '/foo/../' with '/' */
    $re = ['#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#'];
    for ($n = 1; $n > 0; $abs = preg_replace($re, '/', $abs, -1, $n)) {
    }

    // 絶対パスを返す
    if (empty($scheme)) {
        return $abs;
    } else {
        return $scheme . '://' . $abs;
    }
}
/**
 * ディレクトリ内のファイル一覧の取得
 *
 * @param string $dirPath			ディレクトリのパス
 * @param bool   $fileOnly			ファイル名のみかどうか
 * @return array					ファイル名
 */
function getFileList($dirPath, $fileOnly = false)
{
    $fileList = [];
    if (is_dir($dirPath)) {
        $dir = dir($dirPath);
        while (($file = $dir->read()) !== false) {
            $filePath = $dirPath . '/' . $file;
            // ディレクトリかどうかチェック
            if (strncmp($file, '.', 1) != 0 && $file != '..') {
                if (!$fileOnly || ($fileOnly && is_file($filePath))) {
                    $fileList[] = $file;
                }
            }
        }
        $dir->close();
    }
    sort($fileList); // ファイル名をソート
    return $fileList;
}
/**
 * ファイルの移動
 *
 * @param string $srcFile			変更前ファイルのパス
 * @param string $destFile			変更後ファイルのパス
 * @return bool						true=移動完了、false=移動失敗
 */
function mvFile($srcFile, $destFile)
{
    // 移動先ファイルを削除
    if (file_exists($destFile)) {
        unlink($destFile);
    }

    // コピー先ディレクトリの確認
    $destDir = dirname($destFile);
    if (!file_exists($destDir)) {
        if (!mkdir($destDir, M3_SYSTEM_DIR_PERMISSION, true /*再帰的*/)) {
            return false;
        }
    }

    // ファイルコピー
    $ret = copy($srcFile, $destFile);
    if (!$ret) {
        return false;
    }

    // 移動元のファイル削除
    $ret = unlink($srcFile);
    return $ret;
}
/**
 * 複数ファイルの移動
 *
 * @param string $srcDir			移動するファイルの存在するディレクトリ
 * @param array $filenames			移動するファイル
 * @param string $destDir			移動先ディレクトリ
 * @return bool						true=移動完了、false=移動失敗
 */
function mvFileToDir($srcDir, $filenames, $destDir)
{
    $noErr = true;

    // コピー先ディレクトリの確認
    if (!file_exists($destDir)) {
        if (!mkdir($destDir, M3_SYSTEM_DIR_PERMISSION, true /*再帰的*/)) {
            return false;
        }
    }

    for ($i = 0; $i < count($filenames); $i++) {
        $srcPath = $srcDir . '/' . $filenames[$i];
        if (file_exists($srcPath)) {
            $destPath = $destDir . '/' . $filenames[$i];
            $ret = mvFile($srcPath, $destPath);
            if (!$ret) {
                $noErr = false;
            }
        } else {
            $noErr = false;
            break;
        }
    }
    return $noErr;
}
/**
 * 複数ファイルのコピー
 *
 * @param string $srcDir			移動するファイルの存在するディレクトリ
 * @param array $filenames			移動するファイル
 * @param string $destDir			移動先ディレクトリ
 * @return bool						true=移動完了、false=移動失敗
 */
function cpFileToDir($srcDir, $filenames, $destDir)
{
    $noErr = true;

    // コピー先ディレクトリの確認
    if (!file_exists($destDir)) {
        if (!mkdir($destDir, M3_SYSTEM_DIR_PERMISSION, true /*再帰的*/)) {
            return false;
        }
    }

    for ($i = 0; $i < count($filenames); $i++) {
        $srcPath = $srcDir . '/' . $filenames[$i];
        if (file_exists($srcPath)) {
            $destPath = $destDir . '/' . $filenames[$i];
            $ret = copy($srcPath, $destPath);
            if (!$ret) {
                $noErr = false;
            }
        } else {
            $noErr = false;
            break;
        }
    }
    return $noErr;
}
/**
 * ディレクトリの削除
 *
 * @param string $dirname			ディレクトリのパス
 * @return bool						true=削除完了、false=削除失敗
 */
function rmDirectory($dirname)
{
    // ディレクトリが存在しないときは終了
    if (!file_exists($dirname)) {
        return true;
    }

    $ret = false;
    if (is_dir($dirname)) {
        // ディレクトリのとき
        if ($dirHandle = opendir($dirname)) {
            chdir($dirname);
            while ($file = readdir($dirHandle)) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                if (is_dir($file)) {
                    rmDirectory($file);
                } else {
                    unlink($file);
                }
            }
            chdir('..');
            $ret = rmdir($dirname);
            closedir($dirHandle);
        }
    } else {
        // ファイルのとき
        unlink($dirname);
        $ret = true;
    }
    return $ret;
}
/**
 * ディレクトリの移動
 *
 * @param string $srcDir			変更前ディレクトリのパス
 * @param string $destDir			変更後ディレクトリのパス
 * @return bool						true=移動完了、false=移動失敗
 */
function mvDirectory($srcDir, $destDir)
{
    // 移動先ディレクトリを削除
    $ret = rmDirectory($destDir);
    if (!$ret) {
        return false;
    }

    // ディレクトリコピー
    $ret = cpDirectory($srcDir, $destDir);
    if (!$ret) {
        return false;
    }

    // 移動元のディレクトリ削除
    $ret = rmDirectory($srcDir);
    return $ret;
}
/**
 * ディレクトリのコピー
 *
 * @param string $srcDir			変更前ディレクトリのパス
 * @param string $destDir			変更後ディレクトリのパス
 * @return bool						true=処理終了、false=処理失敗
 */
function cpDirectory($srcDir, $destDir)
{
    // ディレクトリが存在しないときは作成
    if (!file_exists($destDir)) {
        if (!mkdir($destDir, M3_SYSTEM_DIR_PERMISSION, true /*再帰的*/)) {
            return false;
        }
    }

    if ($dirHandle = opendir($srcDir)) {
        $ret = true; // 空のときは正常終了
        while ($file = readdir($dirHandle)) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            $filePath = $srcDir . '/' . $file;
            $destFilePath = $destDir . '/' . $file;
            if (is_dir($filePath)) {
                $ret = cpDirectory($filePath, $destFilePath);
            } else {
                $ret = copy($filePath, $destFilePath);
            }
            if (!$ret) {
                break;
            }
        }
        closedir($dirHandle);
        return $ret;
    } else {
        // オープン失敗のとき
        return false;
    }
}
/**
 * ファイルの移動(異なるドライブ間でも移動可)
 *
 * @param string $srcFile			変更前ファイルのパス
 * @param string $destFile			変更後ファイルのパス
 * @return bool						true=移動完了、false=移動失敗
 */
function renameFile($srcFile, $destFile)
{
    if (@rename($srcFile, $destFile)) {
        return true;
    }

    $ret = mvFile($srcFile, $destFile);
    return $ret;
}
/**
 * ファイルへのデータの書き込み
 *
 * @param string $path				書き込み先ファイルフルパス
 * @param string $data				書き込みデータ
 * @param bool $mkDirectory			ファイルまでのディレクトリを作成するかどうか
 * @return bool						true=成功、false=失敗
 */
function writeFile($path, $data, $mkDirectory = true)
{
    $ret = false; // 戻り値リセット

    // ディレクトリ作成
    $dirPath = dirname($path);
    if (!file_exists($dirPath)) {
        // ディレクトリがないとき
        if (!mkdir($dirPath, M3_SYSTEM_DIR_PERMISSION, true /*再帰的*/)) {
            return false;
        }
    }

    $fp = fopen($path, 'w+');
    if ($fp) {
        if (flock($fp, LOCK_EX)) {
            // 排他ロック
            fwrite($fp, $data);
            flock($fp, LOCK_UN); // ロックを解放
            $ret = true;
        }
        fclose($fp);
        return $ret;
    } else {
        return $ret;
    }
}
/**
 * ディレクトリの書き込みチェック
 *
 * @param string $path				書き込み先ファイルフルパス
 * @return bool						true=削除完了、false=削除失敗
 */
function checkWritableDir($path)
{
    $filename = '_magic3_test'; // 書き込みテスト用ファイル名
    $ret = false;
    if ($fp = @fopen($path . '/' . $filename, 'w')) {
        @fclose($fp);
        @unlink($path . '/' . $filename);
        $ret = true;
    }
    return $ret;
}
/**
 * 指定のパスが基準パス以下にあるかどうかを判断
 *
 * @param string $basePath		基点となるディレクトリの絶対パス
 * @param string $targetPath	対象となるディレクトリの絶対パス
 * @return bool					true=基準パス以下、false=基準パス以外
 */
function isDescendantPath($basePath, $targetPath)
{
    // 相対パスを得る
    $base = explode('/', $basePath);
    $target = explode('/', $targetPath);

    $baseCount = count($base);
    for ($i = 0; $i < $baseCount; $i++) {
        if ($base[$i] != $target[$i]) {
            return false;
        }
    }
    return true;
}
/**
 * ユーザカスタマイズ用パラメータの解析
 *
 * フォーマット　key1:value1;key2:value2;key3:value3;
 *
 * @param string $src			解析対象
 * @return array $dest			解析後の配列(メンバーkye,valueを持つオブジェクトの1次配列が返る)
 */
function parseUserCustomParam($src)
{
    $dest = [];
    $parsedArray = explode(';', $src);
    for ($i = 0; $i < count($parsedArray); $i++) {
        list($key, $value) = explode(':', $parsedArray[$i]);
        $obj = new stdClass();
        $obj->key = trim($key);
        $obj->value = trim($value);
        $dest[] = $obj;
    }
    return $dest;
}
/**
 * URLパラメータを連想配列に変換
 *
 * フォーマット　key1=value1&key2=value2&key3=value3
 *
 * @param string $src			解析対象
 * @return array $dest			解析後の連想配列
 */
function parseUrlParam($src)
{
    $dest = [];
    $parsedArray = explode('&', $src);
    for ($i = 0; $i < count($parsedArray); $i++) {
        list($key, $value) = explode('=', $parsedArray[$i]);
        if (!empty($key)) {
            $dest[$key] = $value;
        }
    }
    return $dest;
}
/**
 * ディレクトリのファイル使用サイズを求める
 *
 * @param string $path		ディレクトリのパス
 * @return int				ディレクトリサイズ(バイト)
 */
function calcDirSize($path)
{
    $size = 0; // ディスク使用サイズ

    if ($dirHandle = @opendir($path)) {
        while ($file = @readdir($dirHandle)) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            // ディレクトリのときはサブディレクトリを計算
            $filePath = $path . '/' . $file;
            if (is_dir($filePath)) {
                $size += calcDirSize($filePath);
            } else {
                $size += @filesize($filePath);
            }
        }
        closedir($dirHandle);
    }
    return $size;
}
/**
 * 文字列メモリ表現から数値メモリ表現を取得
 *
 * @param string $val			メモリ文字列表現
 * @return int					メモリバイト数
 */
function convBytes($val)
{
    $val = trim($val);
    $last = strtolower($val[strlen($val) - 1]);

    $numVal = floatval($val);
    switch ($last) {
        case 'g':
            $numVal *= 1024;
        case 'm':
            $numVal *= 1024;
        case 'k':
            $numVal *= 1024;
    }
    return floor($numVal);
}
/**
 * 数値メモリ表現から文字列メモリ表現を取得
 *
 * @param int $val			メモリバイト数
 * @return string			メモリ文字列表現
 */
function convFromBytes($val)
{
    $destVal = (float) $val / 1024.0;
    if ($destVal < 1024) {
        return ceil($destVal) . 'K';
    }
    $destVal = $destVal / 1024.0;
    if ($destVal < 1024) {
        return ceil($destVal) . 'M';
    }
    $destVal = $destVal / 1024.0;
    return ceil($destVal) . 'G';
}
/**
 * URL以外の文字列をエンティティ文字に変換
 *
 * @param string $src		変換元文字列
 * @param bool $keepTags	HTMLタグを変換するかどうか
 * @return string			変換後文字列
 */
function convertToHtmlEntity($src, $keepTags = false)
{
    if (!isset($src)) {
        return '';
    }

    // 変換文字「&<>"」
    if ($keepTags) {
        // タグを変換しないとき
        //$str = "/<[^>]+>/";
        $str = '/[^<>]+[^<>]/';
        $dest = preg_replace_callback($str, '_replace_nottag_callback', $src);
        return $dest;
    } else {
        return htmlentities($src, ENT_COMPAT, M3_HTML_CHARSET);
    }
}
/**
 * エンティティ文字を元の文字列に変換
 *
 * @param string $src		変換元文字列
 * @return string			変換後文字列
 */
function convertFromHtmlEntity($src)
{
    $transTable = get_html_translation_table(HTML_ENTITIES);
    $transTable = array_flip($transTable);
    return strtr($src, $transTable);
}
/**
 * タグ以外変換コールバック関数
 *
 * @param array $matchData		検索マッチデータ
 * @return string				変換後データ
 */
function _replace_nottag_callback($matchData)
{
    return htmlentities($matchData[0], ENT_COMPAT, M3_HTML_CHARSET);
}
/**
 * URLをエンティティ文字に変換
 *
 * @param string $src		変換元文字列
 * @return string			変換後文字列
 */
function convertUrlToHtmlEntity($src)
{
    // 変換文字「&<>"'」
    return htmlspecialchars($src, ENT_QUOTES, M3_HTML_CHARSET);
}
/**
 * 文字列の先頭を比較
 *
 * @param string $src			比較する文字列
 * @param string $startStr		先頭文字列
 * @return bool					true=同じ、false=異なる
 */
function strStartsWith($src, $startStr)
{
    if (strncmp($src, $startStr, strlen($startStr)) == 0) {
        return true;
    } else {
        return false;
    }
}
/**
 * 文字列の最後を比較
 *
 * @param string $src			比較する文字列
 * @param string $endStr		最後の文字列
 * @return bool					true=同じ、false=異なる
 */
function strEndsWith($src, $endStr)
{
    $length = strlen($endStr);
    if (strlen($src) < $length) {
        return false;
    } // PHP 5.1でWarningが出力される問題に対応(2011/11/24)

    if (substr_compare($src, $endStr, -$length, $length) == 0) {
        return true;
    } else {
        return false;
    }
}
/**
 * URLを作成
 *
 * @param string		$baseUrl	URL
 * @param string,array	$addParam 	追加パラメータ
 * @return string					作成したURL
 */
function createUrl($baseUrl, $addParam = '')
{
    $url = $baseUrl;

    // 追加パラメータがある場合
    if (!empty($addParam)) {
        if (strEndsWith($url, '.php') || strEndsWith($url, '/')) {
            $url .= '?';
        }

        if (is_array($addParam)) {
            // 配列の場合
            foreach (array_keys($addParam) as $key) {
                if (!strEndsWith($url, '?')) {
                    $url .= '&';
                }
                $url .= urlencode($key) . '=' . urlencode($addParam[$key]);
            }
        } else {
            if (!strEndsWith($url, '?')) {
                $url .= '&';
            }
            $url .= trim($addParam, '&');
        }
    }
    return $url;
}
/**
 * 指定したURLパラメータを削除
 *
 * @param string $url			URL
 * @param array	 $param			削除対象のURLパラメータ(「key」「value」のセットの配列)
 * @return string				パラメータ削除後のURL
 */
function removeUrlParam($url, $param)
{
    // URLを分割
    list($destUrl, $query) = explode('?', $url);
    if (empty($query)) {
        return $url;
    }

    // URLを解析
    $queryArray = [];
    $parsedUrl = parse_url($url);
    if (!empty($parsedUrl['query'])) {
        parse_str($parsedUrl['query'], $queryArray);
    } // クエリーの解析

    $destArray = [];
    $keys = array_keys($queryArray);
    $keyCount = count($keys);
    $paramCount = count($param);
    for ($i = 0; $i < $keyCount; $i++) {
        $key = $keys[$i];
        $value = $queryArray[$key];

        for ($j = 0; $j < $paramCount; $j++) {
            $paramKey = $param[$j]['key'];
            $paramValue = $param[$j]['value'];
            if ($key == $paramKey && $value == $paramValue) {
                break;
            }
        }
        if ($j == $paramCount) {
            $destArray[$key] = $value;
        }
    }
    // パラメータを連結
    if (count($destArray) > 0) {
        $keys = array_keys($destArray);
        $keyCount = count($keys);

        $destParam = '';
        for ($i = 0; $i < $keyCount; $i++) {
            $key = $keys[$i];
            $value = $destArray[$key];
            if ($i > 0) {
                $destParam .= '&';
            }
            $destParam .= $key . '=' . $value;
        }
        // 最後に「=」がある場合は削除(Wiki名対応)
        $destParam = rtrim($destParam, '=');
        $destUrl .= '?' . $destParam;
    }
    return $destUrl;
}
/**
 * URLがグローバルで有効かどうかチェック
 *
 * @param string $url		URL
 * @return 					true=有効、false=無効
 */
function checkGlobalUrl($url)
{
    $siteUrl = parse_url($url);
    $ip = $siteUrl['host'];

    if (version_compare(phpversion(), '5.2.0') >= 0) {
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            // IPの場合
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    } else {
        $longIp = ip2long($ip);
        if ($longIp == -1 || $longIp === false) {
            // IPアドレスでないとき
            return true;
        } else {
            // IPアドレスのとき
            $privAddrs = [
                '10.0.0.0|10.255.255.255',
                '172.16.0.0|172.31.255.255',
                '192.168.0.0|192.168.255.255',
                '169.254.0.0|169.254.255.255',
                '127.0.0.0|127.255.255.255',
            ];
            if ($longIp != -1) {
                foreach ($privAddrs as $privAddr) {
                    list($start, $end) = explode('|', $privAddr);
                    if ($longIp >= ip2long($start) && $longIp <= ip2long($end)) {
                        return false;
                    } // プライベートIP
                }
            }
            return true;
        }
    }
}
/**
 * 日付をW3C-DTFフォーマット(YYYY-MM-DDThh:mm:ss.sTZD(例：2001-08-02T10:45:23.5+09:00)で取得
 *
 * @param timestamp $src	タイムスタンプ型の日時
 * @return string				変換後文字列
 */
function getW3CDate($src = null)
{
    $src = $src === null ? time() : strtotime($src);
    $date = substr_replace(date('Y-m-d\TH:i:sO', $src), ':', -2, 0);
    return $date;
}
/**
 * 日付をRFC822フォーマット(例：Sun, 29 Aug 2004 15:42:09 +0900)で取得
 *
 * @param timestamp $src	タイムスタンプ型の日時
 * @return string				変換後文字列
 */
function getRFC822Date($src = null)
{
    $src = $src === null ? time() : strtotime($src);
    $date = date(DATE_RFC822, $src);
    return $date;
}
/**
 * 簡易版デバッグ出力
 *
 * @param mixed $msg			string型のとき=出力メッセージ、それ以外のとき=出力変数
 * @return なし
 */
function debug($msg)
{
    global $gLogManager;

    if (is_string($msg)) {
        $gLogManager->debug(__METHOD__, $msg);
    } else {
        $gLogManager->debug(__METHOD__, var_export($msg, true));
    }
    // コールスタック情報出力
    //$gLogManager->debug(__METHOD__, var_export(debug_backtrace(2), true));
}
/**
 * 簡易版デバッグ出力(時間付き)
 *
 * @param string $msg			出力メッセージ
 * @return なし
 */
function debugtime($msg = '')
{
    debug(sprintf('%01.03f', microtime(true) - M3_MTIME) . ' ' . $msg);
}
?>
