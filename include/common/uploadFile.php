<?php
/**
 * ファイルアップロードクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2016 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
/**
 * 定数クラス
 */
class uploadFileDef
{
	const FILE_PARAM_NAME = 'file';			// 送信側INPUTタグのname値
}
/**
 * Handle file uploads via XMLHttpRequest
 */
class uploadFileXhr
{
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path) {    
        $input = fopen("php://input", "r");
        $temp = tmpfile();
        $realSize = stream_copy_to_stream($input, $temp);
        fclose($input);
        
        if ($realSize != $this->getSize()){            
            return false;
        }
        
        $target = fopen($path, "w");        
        fseek($temp, 0, SEEK_SET);
        stream_copy_to_stream($temp, $target);
        fclose($target);
        
        return true;
    }
    function getName() {
        return $_GET[uploadFileDef::FILE_PARAM_NAME];
    }
    function getSize() {
        if (isset($_SERVER["CONTENT_LENGTH"])){
            return (int)$_SERVER["CONTENT_LENGTH"];            
        } else {
            throw new Exception('Getting content length is not supported.');
        }      
    }   
}

/**
 * Handle file uploads via regular form post (uses the $_FILES array)
 */
class uploadFileForm
{  
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path) {
        if(!move_uploaded_file($_FILES[uploadFileDef::FILE_PARAM_NAME]['tmp_name'], $path)){
            return false;
        }
        return true;
    }
    function getName() {
        return $_FILES[uploadFileDef::FILE_PARAM_NAME]['name'];
    }
    function getSize() {
        return $_FILES[uploadFileDef::FILE_PARAM_NAME]['size'];
    }
}

class uploadFile
{
    private $allowedExtensions = array();
    private $sizeLimit = 10485760;			// アップロード可能なファイルの最大サイズ
    private $file;

    function __construct(array $allowedExtensions = array(), $sizeLimit = null)
	{
		global $gSystemManager;
		
        $allowedExtensions = array_map("strtolower", $allowedExtensions);
        $this->allowedExtensions = $allowedExtensions;
		
		// ***** アップロードファイルの最大サイズはPHPの設定が最大でそれ以下の範囲で設定可能とする *****
        //$this->sizeLimit = $sizeLimit;
		$maxFileSize = $gSystemManager->getMaxFileSizeForUpload(true);
		if (empty($sizeLimit) || $sizeLimit > $maxFileSize){
			$this->sizeLimit = $maxFileSize;
		} else {
			$this->sizeLimit = $sizeLimit;
		}

        //$this->checkServerSettings();       // サーバ環境チェックなし
        if (isset($_GET[uploadFileDef::FILE_PARAM_NAME])) {
            $this->file = new uploadFileXhr();
        } elseif (isset($_FILES[uploadFileDef::FILE_PARAM_NAME])) {
            $this->file = new uploadFileForm();
        } else {
            $this->file = false; 
        }
    }
    
/*    private function checkServerSettings(){
        $postSize = $this->toBytes(ini_get('post_max_size'));
        $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));
        
        if ($postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit){
            $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';
            die("{'error':'increase post_max_size and upload_max_filesize to $size'}");    
        }        
    }*/
    
/*    private function toBytes($str){
        $val = trim($str);
        $last = strtolower($str[strlen($str)-1]);
        switch($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;        
        }
        return $val;
    }*/
    
    /**
     * Returns array('success'=>true) or array('error'=>'error message')
     */
    function handleUpload($uploadDirectory)
	{
		global $gSystemManager;
		
		$uploadDirectory = rtrim($uploadDirectory, '/\\');
		
        if (!is_writable($uploadDirectory)){
            return array('error' => "Server error. Upload directory isn't writable.");
        }
        
        if (!$this->file){
            return array('error' => 'No files were uploaded.');
        }
        
		// ファイルサイズのチェック
        $size = $this->file->getSize();
		
		// PHPの設定上限に達している場合は0が返る
        if ($size == 0){
			// IE8では、アップロードファイルのサイズが「upload_max_filesize」よりも大きいと0が返る
			if (isset($_SERVER["CONTENT_LENGTH"])){
				// ファイルサイズの上限がPHP設定値よりも小さく設定されている場合は、設定値を表示
				if ($this->sizeLimit < $gSystemManager->getMaxFileSizeForUpload(true)){
					$errorMsg = 'File size is too large. ' . $_SERVER['CONTENT_LENGTH'] . ' bytes exceeds max upload filesize(' . $this->sizeLimit . ' bytes).';
				} else {
					$displayMaxSize = $gSystemManager->getMaxFileSizeForUpload();
					$errorMsg = 'File size is too large. ' . $_SERVER['CONTENT_LENGTH'] . ' bytes exceeds max upload filesize(' . $displayMaxSize . ' bytes).';
				}
			} else {
				$errorMsg = 'File is empty';
			}
			return array('error' => $errorMsg);
        }
        
		// 上限サイズを超える場合
		if ($size > $this->sizeLimit) {
			//return array('error' => 'File is too large');
			$errorMsg = 'File size is too large. ' . $size . ' bytes exceeds max upload filesize(' . $this->sizeLimit . ' bytes).';
			return array('error' => $errorMsg);
		}
		
		$filename = strtr($this->file->getName(), ' ', '_');	// 半角スペースをアンダーバーに変換(Firefox対応)
        $pathinfo = pathinfo($filename);
//        $filename = $pathinfo['filename'];
        $ext = $pathinfo['extension'];

        if($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)){
            $these = implode(', ', $this->allowedExtensions);
            return array('error' => 'File has an invalid extension, it should be one of '. $these . '.');
        }
        
        /*if(!$replaceOldFile){
            /// don't overwrite previous files that were uploaded
            while (file_exists($uploadDirectory . $filename . '.' . $ext)) {
                $filename .= rand(10, 99);
            }
        }*/
		// ファイルが存在する場合はエラー
		$fileId = md5(uniqid(rand(), true));
		$newFilePath = $uploadDirectory . DIRECTORY_SEPARATOR . $fileId;
		if (file_exists($newFilePath)){
			return array('error' => 'File already exists.');
		}
        
        if ($this->file->save($newFilePath)){
			$fileInfo = array('fileid' => $fileId, 'filename' => $filename, 'path' => $newFilePath, 'size' => $size);
            return array('success' => true, 'file' => $fileInfo);
        } else {
            return array('error'=> 'Could not save uploaded file.' .
                'The upload was cancelled, or server error encountered');
        }
    }
}
