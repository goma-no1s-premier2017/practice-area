<?php
/**
 * PHPExcelを利用したエクセルコントローラ
 */
class ExcelController
{
    protected $excel;

    public function __construct($file_path)
    {
        require_once DIR_PHPEXCEL . 'PHPExcel.php';
        require_once DIR_PHPEXCEL . 'PHPExcel/IOFactory.php';

        $this->excel = PHPExcel_IOFactory::load($file_path);
    }

    /**
     * テーブル形式で記述されているのエクセルデータを取得して配列で返す
     * 一応テーブル内に空白が存在するも許容するようにした
     *
     * @param string $sheet_name 対象シート名
     * @param int $start_row テーブルデータ左上端の行番号
     * @param int $start_column  テーブルデータ左上端の列番号
     */
    public function getTableData($sheet_name, $start_row, $start_column)
    {
        $res = [];

        $excel = $this->excel;
        // 指定のシートをアクティブにする
        $sheet = $excel->getActiveSheet($sheet_name);

        // スタートするセル位置
        $active_row    = $start_row;
        $active_column = $start_column;
        // 最初のループはヘッダー
        $is_first = true;
        $key      = "header";
        // データ作成のため行列の各種カウンター
        $row_counter    = 0;
        $column_counter = 0;
        $null_counter   = 0;
        while (true) {
            // 指定セルから値を取得
            $value  = $sheet->getCellByColumnAndRow($active_row, $active_column)->getValue();

            if ($value === null) {
                $null_counter++;

                if ($is_first === true) {
                    // 初回ループ終了後
                    // 終了判定用にヘッダー数を取得
                    $header_counter = count($res[$key]);
                    // bodyデータ取得処理に切り替える
                    $key      = "body";
                    $is_first = false;

                } elseif ($header_counter > $active_row) {
                    // 対象範囲内のセルに空白があった場合は次の列へ
                    $active_row++;
                    $row_counter++;
                    continue;
                } elseif ($header_counter === $null_counter) {
                    // 対象範囲内のセルがすべてnull値だった場合は終了
                    break;
                } else {
                    // それ以外の場合は次の行へ
                    $column_counter++;
                }

                // アクティブなセルを次の行の左端列へ移行
                $active_column++;
                $active_row = $start_row;

                // 各種カウンターを初期化
                $row_counter  = 0;
                $null_counter = 0;
                continue;
            }

            if ($key === "header") {
                $res[$key][$row_counter] = $value;
            } elseif ($key === "body") {
                $res[$key][$column_counter][$res["header"][$row_counter]] = $value;
            }

            // 右隣のセルをアクティブにする
            $active_row++;
            $row_counter++;
        }
        return $res;
    }
}
