<?php
/**
 * コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2023 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_mainUsergroupWidgetContainer.php 4467 2011-11-25 03:26:13Z fishbone $
 * @link       http://www.magic3.org
 */
require_once $gEnvManager->getCurrentWidgetContainerPath() . '/admin_mainUserBaseWidgetContainer.php';

class admin_mainUsergroupWidgetContainer extends admin_mainUserBaseWidgetContainer
{
    private $serialNo; // シリアル番号
    private $serialArray = []; // 表示されている項目シリアル番号

    /**
     * コンストラクタ
     */
    function __construct()
    {
        // 親クラスを呼び出す
        parent::__construct();
    }
    /**
     * テンプレートファイルを設定
     *
     * _assign()でデータを埋め込むテンプレートファイルのファイル名を返す。
     * 読み込むディレクトリは、「自ウィジェットディレクトリ/include/template」に固定。
     *
     * @param RequestManager $request		HTTPリクエスト処理クラス
     * @param object         $param			任意使用パラメータ。そのまま_assign()に渡る
     * @return string 						テンプレートファイル名。テンプレートライブラリを使用しない場合は空文字列「''」を返す。
     */
    function _setTemplate($request, &$param)
    {
        $task = $request->trimValueOf('task');
        if ($task == 'usergroup_detail') {
            // 詳細画面
            return 'usergroup_detail.tmpl.html';
        } else {
            return 'usergroup.tmpl.html';
        }
    }
    /**
     * テンプレートにデータ埋め込む
     *
     * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
     *
     * @param RequestManager $request		HTTPリクエスト処理クラス
     * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
     * @param								なし
     */
    function _assign($request, &$param)
    {
        $task = $request->trimValueOf('task');
        if ($task == 'usergroup_detail') {
            // 詳細画面
            return $this->createDetail($request);
        } else {
            // 一覧画面
            return $this->createList($request);
        }
    }
    /**
     * 一覧画面作成
     *
     * @param RequestManager $request		HTTPリクエスト処理クラス
     * @param								なし
     */
    function createList($request)
    {
        $act = $request->trimValueOf('act');

        if ($act == 'delete') {
            // 項目削除の場合
            $listedItem = explode(',', $request->trimValueOf('seriallist'));
            $delItems = [];
            for ($i = 0; $i < count($listedItem); $i++) {
                // 項目がチェックされているかを取得
                $itemName = 'item' . $i . '_selected';
                $itemValue = $request->trimValueOf($itemName) == 'on' ? 1 : 0;

                if ($itemValue) {
                    // チェック項目
                    $delItems[] = $listedItem[$i];
                }
            }
            if (count($delItems) > 0) {
                $ret = $this->_mainDb->delUserGroupBySerial($delItems);
                if ($ret) {
                    // データ削除成功のとき
                    $this->setGuidanceMsg('データを削除しました');
                } else {
                    $this->setAppErrorMsg('データ削除に失敗しました');
                }
            }
        }
        // #### ユーザグループリストを作成 ####
        $this->_mainDb->getAllUserGroup($this->_langId, [$this, 'groupListLoop']); // デフォルト言語で取得

        if (count($this->serialArray) > 0) {
            $this->tmpl->addVar('_widget', 'serial_list', implode(',', $this->serialArray)); // 表示項目のシリアル番号を設定
        } else {
            $this->tmpl->setAttribute('itemlist', 'visibility', 'hidden'); // 項目がないときは、一覧を表示しない
        }
    }
    /**
     * 詳細画面作成
     *
     * @param RequestManager $request		HTTPリクエスト処理クラス
     * @param								なし
     */
    function createDetail($request)
    {
        $userId = $this->gEnv->getCurrentUserId();

        $act = $request->trimValueOf('act');
        $this->serialNo = $request->trimValueOf('serial'); // 選択項目のシリアル番号

        $id = $request->trimValueOf('item_id'); // 識別ID
        $name = $request->trimValueOf('item_name'); // カテゴリー名称
        $index = $request->trimValueOf('item_index'); // 表示順

        $replaceNew = false; // データを再取得するかどうか
        if ($act == 'add') {
            // 項目追加の場合
            // 入力チェック
            $this->checkSingleByte($id, 'ID', false, 1 /*英小文字に制限*/);
            $this->checkInput($name, '名前');
            $this->checkNumeric($index, '表示順');

            // 同じIDがある場合はエラー
            if ($this->_mainDb->getUserGroupById($id, $row)) {
                $this->setMsg(self::MSG_USER_ERR, 'IDが重複しています');
            }

            // エラーなしの場合は、データを登録
            if ($this->getMsgCount() == 0) {
                $ret = $this->_mainDb->addUserGroup($id, $this->_langId, $name, $index, $newSerial);
                if ($ret) {
                    $this->setGuidanceMsg('データを追加しました');

                    // シリアル番号更新
                    $this->serialNo = $newSerial;
                    $replaceNew = true; // データを再取得
                } else {
                    $this->setAppErrorMsg('データ追加に失敗しました');
                }
            }
        } elseif ($act == 'update') {
            // 項目更新の場合
            // 入力チェック
            $this->checkSingleByte($id, 'ID', false, 1 /*英小文字に制限*/);
            $this->checkInput($name, '名前');
            $this->checkNumeric($index, '表示順');

            // エラーなしの場合は、データを登録
            if ($this->getMsgCount() == 0) {
                $ret = $this->_mainDb->updateUserGroup($this->serialNo, $name, $index, $newSerial);
                if ($ret) {
                    $this->setGuidanceMsg('データを更新しました');

                    // 登録済みのカテゴリーを取得
                    $this->serialNo = $newSerial;
                    $replaceNew = true; // データを再取得
                } else {
                    $this->setAppErrorMsg('データ更新に失敗しました');
                }
            }
        } elseif ($act == 'delete') {
            // 項目削除の場合
            $ret = $this->_mainDb->delUserGroupBySerial([$this->serialNo]);
            if ($ret) {
                // データ削除成功のとき
                $this->setGuidanceMsg('データを削除しました');
            } else {
                $this->setAppErrorMsg('データ削除に失敗しました');
            }
        } else {
            // 初期表示
            // 入力値初期化
            if (empty($this->serialNo)) {
                // シリアル番号
                $id = ''; // 識別ID
                $name = ''; // 名前
                $index = $this->_mainDb->getUserGroupMaxIndex($this->_langId) + 1; // 表示順
            } else {
                $replaceNew = true; // データを再取得
            }
        }
        // データを再取得のとき
        if ($replaceNew) {
            $ret = $this->_mainDb->getUserGroupBySerial($this->serialNo, $row);
            if ($ret) {
                // 取得値を設定
                $id = $row['ug_id']; // ID
                $name = $row['ug_name']; // 名前
                $index = $row['ug_sort_order']; // 表示順
                $updateUser = $row['lu_name']; // 更新者
                $updateDt = $row['ug_create_dt']; // 更新日時
            }
        }
        // #### 更新、新規登録部をを作成 ####
        if (empty($this->serialNo)) {
            // シリアル番号のときは新規とする
            $this->tmpl->setAttribute('new_id_field', 'visibility', 'visible'); // 新規ID入力フィールド表示
            $this->tmpl->addVar('new_id_field', 'id', $id); // 識別キー

            $this->tmpl->setAttribute('add_button', 'visibility', 'visible'); // 「新規追加」ボタン
        } else {
            $this->tmpl->setAttribute('id_field', 'visibility', 'visible'); // 固定IDフィールド表示
            $this->tmpl->addVar('id_field', 'id', $id); // 識別キー

            $this->tmpl->setAttribute('update_button', 'visibility', 'visible');
        }
        $this->tmpl->addVar('_widget', 'serial', $this->serialNo);
        $this->tmpl->addVar('_widget', 'name', $name); // 名前
        $this->tmpl->addVar('_widget', 'index', $index); // 表示順

        $this->tmpl->addVar('_widget', 'update_user', $this->convertToDispString($updateUser)); // 更新者
        $this->tmpl->addVar('_widget', 'update_dt', $this->convertToDispDateTime($updateDt)); // 更新日時
    }
    /**
     * 取得したデータをテンプレートに設定する
     *
     * @param int $index			行番号(0～)
     * @param array $fetchedRow		フェッチ取得した行
     * @param object $param			未使用
     * @return bool					true=処理続行の場合、false=処理終了の場合
     */
    function groupListLoop($index, $fetchedRow, $param)
    {
        $serial = $fetchedRow['ug_serial'];

        $row = [
            'index' => $index, // 行番号
            'serial' => $serial, // シリアル番号
            'id' => $this->convertToDispString($fetchedRow['ug_id']), // ID
            'name' => $this->convertToDispString($fetchedRow['ug_name']), // 名前
            'view_index' => $this->convertToDispString($fetchedRow['ug_sort_order']), // 表示順
            'update_user' => $this->convertToDispString($fetchedRow['lu_name']), // 更新者
            'update_dt' => $this->convertToDispDateTime($fetchedRow['ug_create_dt']), // 更新日時
        ];
        $this->tmpl->addVars('itemlist', $row);
        $this->tmpl->parseTemplate('itemlist', 'a');

        // 表示中項目のシリアル番号を保存
        $this->serialArray[] = $serial;
        return true;
    }
}
?>
