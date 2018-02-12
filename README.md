# practice-area
## 2018/02プレミアテスト
<dl>
  <dt>■問一</dt>
  <dt>実行ファイル</dt>
  <dd>/bat/201802q1_getQrCodes.php</dd>
  <dt>作成されるQRコードの格納ディレクトリ</dt>
  <dd>/files/qr_code/</dd>
</dl>
<dl>
  <dt>■問二</dt>
  <dt>実行ファイル</dt>
  <dd>/bat/201802q2_MorphologicalAnalysis.php</dd>
  <dt>ライブラリ導入手順</dt>
  <dd>
```

$ # rootユーザ・centos7系で実行  
$ # 各種インストールに必要なものをインストール  
$ yum -y install gcc-c++ patch  
$ # ①mecabのインストール  
$ cd /usr/local/src/  
$ wget -O mecab-0.996.tar.gz "https://drive.google.com/uc?export=download&id=0B4y35FiV1wh7cENtOXlicTFaRUE"  
$ tar zxvf mecab-0.996.tar.gz  
$ cd mecab-0.996  
$ ./configure  
$ make  
$ make check  
$ make install  
  
$ # ②辞書のインストール  
$ cd /usr/local/src/mecab-0.996/  
$ mkdir library  
$ cd library  
$ git clone --depth 1 https://github.com/neologd/mecab-ipadic-neologd.git  
$ cd mecab-ipadic-neologd/  
$ ./bin/install-mecab-ipadic-neologd -n  
  
$ # ③php-mecabのインストール  
$ cd /usr/local/src/  
$ git clone https://github.com/rsky/php-mecab.git  
$ cd /usr/local/src/php-mecab/mecab  
$ phpize  
  
$ # (1)  
$ which php-config  
$ # (2)  
$ which mecab-config  
$ # ※上記(1)(2)で出力されたパスを下記の記述に追記する  
$ ./configure --with-php-config=(1) --with-mecab=(2)  
$ make  
$ make test  
$ make install  
  
$ # extensionファイル作成  
$ vim /etc/php.d/mecab.ini  
$ # 「extension=mecab.so」を追記  
$ systemctl restart httpd  
  
$ # 動作確認  
$ php -r 'phpinfo();' | grep 'mecab' -i  

```
  </dd>
</dl>

## 2018/01プレミアテスト
<dl>
  <dt>■問一</dt>
  <dt>実行ファイル</dt>
  <dd>/bat/getProductData.php</dd>
  <dt>出力されるＣＳＶファイルの格納ディレクトリ</dt>
  <dd>/files/csv/</dd>
  <dt>ＣＳＶファイルの名称</dt>
  <dd>ProductData_[Ymd]_[His].csv</dd>
</dl>

<dl>
  <dt>■問二</dt>
  <dt>実行ファイル</dt>
  <dd>/bat/showExcelData.php</dd>
  <dt>対象のエクセルファイル</dt>
  <dd>/files/excel/data.xlsx</dd>
</dl>

## 2017/12プレミアテスト
<dl>
  <dt>■問一</dt>
  <dt>実行ファイル</dt>
  <dd>/bat/getRealDonaldTrump.php</dd>
</dl>

<dl>
  <dt>■問二</dt>
  <dt>実行ファイル</dt>
  <dd>/bat/getHighClassHotelsInOkinawa.php</dd>
</dl>

