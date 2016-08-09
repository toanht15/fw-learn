<?php
AAFW::import ( 'jp.aainc.aafw.text.aafwTemplateTag' );
AAFW::import ( 'jp.aainc.aafw.web.aafwMobileDispatcher' );
/**
 * てきとーにメールを送るクラス
 **/
class aafwMail {
  private $Header  = array ();
  private $Subject = '';
  private $FromAddress = '';
  private $Body    = '';
  private $ContentTypeHTML = 0;
  private $AltText = '';
  private $TO      = array ();
  private $CC      = array ();
  private $BCC     = array ();
  private $isContinuing = false;
  private $Envelope = null;
  private $Encoding = '';
  private $DefaultBCC = '';
  private $SMTPServer = null;
  private $Attachment = null;

  /**
   * てきとーなコンストラクタ
   **/
  public function __construct ( $subject = null , $body = null , $html = false, $encoding = 'ISO-2022-JP', $is_continuing = false ) {
    $this->Subject         = $subject;
    $this->Body            = $body;
    $this->ContentTypeHTML = $html;
    $this->isContinuing    = $continuing_mode;
    $this->Encoding        = $encoding;
  }

  /**
   * 件名を設定する
   * @param 件名
   */
  public function setSubject ( $sbj ) {
    $this->Subject = $sbj;
    return $this;
  }

  /**
   * 本文を設定する
   * @param 本文
   */
  public function setBody ( $body ) {
    $this->Body = $body;
    return $this;
  }

  /**
   * 添付ファイルを設定する
   * @param 添付ファイルのパス
   */
  public function setAttachment ( $at ) {
    $this->Attachment = $at;
    return $this;
  }

  /**
   * SMTPサーバーを設定する
   * @param aafwSMTP
   */
  public function setSMTPServer ( $sv ) {
    $this->SMTPServer = $sv;
    return $this;
  }

  /**
   * てきとーなFROMを設定する
   * @param FROMに設定するメールアドレス
   **/
  public function setFrom ( $address ){
    if ( !$address ) throw new Exception( 'is not mail address' );
    $this->FromAddress = $address;
    $buf = array ();
    foreach ( $this->Header as $row ) {
      if ( !$row || preg_match ( '#^From:#', $row ) ) continue;
      $buf[] = $row;
    }
    $this->Header = $buf;
    $this->Header[] = 'From: ' . $this->encodeAddress ( $address );
    return $this;
  }

  /**
   * てきとーなCCを設定する
   * @param CCに設定するメールアドレス
   **/
  public function setCC ( $address ){
    if ( !$address ) throw new Exception( 'is not mail address' );
    if ( !is_array ( $address ) )$address = array( $address );
    $buf = array ();
    $value = '';
    foreach ( $this->Header as $row ) {
      if ( !$row || preg_match ( '#^Cc:(.+)#', $row ) ) continue;
      $buf[] = $row;
    }
    $this->Header = $buf;
    $tmp = array();
    foreach ( $address as $row ) $tmp[] = $this->encodeAddress ( $row );
    $this->Header[] = 'Cc: ' . join ( ',', $tmp );
    return $this;
  }

  /**
   * てきとーなBCCを設定する
   * @param BCCに設定するメールアドレス
   **/
  public function setBCC ( $address ){
    if ( !$address )             throw new Exception( 'is not mail address' );
    if ( !is_array ( $address ) )$address = array( $address );
    $buf = array ();
    foreach ( $this->Header as $row ) {
      if ( !$row ||preg_match ( '#^Bcc:#', $row ) ) continue;
      $buf[] = $row;
    }
    $this->Header = $buf;
    $tmp = array();
    foreach ( $address as $row ) $tmp[] = $this->encodeAddress ( $row );
    $this->Header[] = 'Bcc: ' . join ( ',', $tmp );
    return $this;
  }

  /**
   * てきとーなReply-Toを設定する
   * @param Reply-Toに設定するメールアドレス
   **/
  public function setReplyTo ( $address ){
    if ( !$address ) throw new Exception( 'is not mail address' );
    $buf = array ();
    foreach ( $this->Header as $row ) {
      if ( !$row || preg_match ( '#^Reply-To:#', $row ) ) continue;
      $buf[] = $row;
    }
    $this->Header = $buf;
    $this->Header[] = 'Reply-To: ' . $this->encodeAddress ( $address );
    return $this;
  }

  /**
   * HTMLメールのときの代替テキストを設定する
   * @param 代替テキスト
   **/
  public function setAltText ( $altText ){
    if(!$this->ContentTypeHTML) throw new Exception( 'this mail is not HTML mail' );
    $this->AltText = $altText;
    return $this;
  }

  /**
   * てきとーに送信する
   * @param 送信メールアドレス (mix-in)
   * @param 置換タグに設定するパラメータ
   * @param CCに設定するメールアドレス (mix-in)
   * @param BCCに設定するメールアドレス (mix-in)
   **/
  public function send ( $TO, $params = null, $CC = null , $BCC = null ){
    $content  = trim ( $this->Body );
    $alt_text = trim ( $this->AltText );
    $subject  = trim ( $this->Subject );

    // 携帯版でHTMLメールを送ろうとした場合は代替テキストを本文に差し替える
    if ( $this->ContentTypeHTML && is_scalar ( $TO ) && aafwMobileDispatcher::isMobile_mail ( $TO ) ) {
      $this->ContentTypeHTML = 0;
      $alt_text = str_replace( array ( "。","、", "><" ), array ( "。\n", "、\n", ">\n<" ), $alt_text );
      $content = $alt_text;
      $alt_text = null;
    }

    if ( !$content ) throw new Exception( "can't send mail with no body" );
    if ( $params ){
      $tmpl     = new aafwTemplateTag ( $content,  $params );
      $content  = $tmpl->evalTag();
      $tmpl     = new aafwTemplateTag ( $alt_text,  $params );
      $alt_text = $tmpl->evalTag();
      $tmpl     = new aafwTemplateTag ( $subject,  $params );
      $subject  = $tmpl->evalTag();
    }
    $content  = str_replace( array ( "\r\n","\r", "\n" ), "\n", $content );
    $alt_text = str_replace( array ( "\r\n","\r", "\n" ), "\n", $alt_text );

    if ( $this->ContentTypeHTML ) {
      $content  = str_replace( array ( "。","、", "><" ), array ( "。\n", "、\n", ">\n<" ), $content );
      $alt_text = str_replace( array ( "。","、", "><" ), array ( "。\n", "、\n", ">\n<" ), $alt_text );
    }

    $content  = $this->breakLine( $content, 800, $this->Encoding );
    $alt_text = $this->breakLine( $alt_text, 800, $this->Encoding );
    $header = '';
    if ( $this->DefaultBCC ) {
      if ( $BCC ) {
        if ( is_array ( $BCC ) ) $BCC[] = $this->DefaultBCC;
        else                     $BCC   = array ( $BCC, $this->DefaultBCC );
      } else {
        $BCC = $this->DefaultBCC;
      }
    }
    if ( $CC )                     $this->setCC ( $CC );
    if ( $BCC  )                   $this->setBCC ( $BCC );
    if ( $this->Header )           $header  = join ( "\n", $this->Header ) . "\n";
    if ( trim ( $alt_text ) )      $header .= "Content-type: multipart/alternative; \n";
    elseif($this->ContentTypeHTML) $header .= 'Content-type: text/html; charset='  . $this->Encoding . "\n";
    elseif($this->Attachment)      $header .= "Content-type: multipart/mixed; \n";
    else                           $header .= 'Content-type: text/plain; charset=' . $this->Encoding . "\n";
    if( trim ( $alt_text ) ){
        //マルチパート化
        $boundary = 'bd_' . md5(uniqid(rand()));
        $header .= "\tboundary=\"$boundary\"\n";
        $tempBody = "--$boundary\n";
        $tempBody .= "Content-Type: text/plain; charset=" . $this->Encoding . "\n";
        $tempBody .= "\n";
        $tempBody .= $alt_text;
        $tempBody .= "\n\n";
        $tempBody .= "--$boundary\n";
        $tempBody .= "Content-Type: text/html; charset=" . $this->Encoding . "\n";
        $tempBody .= "\n";
        $tempBody .= $content;
        $tempBody .= "\n";
        $tempBody .= "\n";
        $tempBody .= "--$boundary--\n";
        $content = $tempBody;
    } else if( $this->Attachment ) {
      //画像付きテキストメール
      if ($this->Attachment['data'] && $this->Attachment['mime'] && $this->Attachment['name'] ) {
        $boundary = 'bd_' . md5(uniqid(rand()));
        $header .= "\tboundary=\"$boundary\"\n";
        $tempBody = "--$boundary\n";
        $tempBody .= "Content-Type: text/plain; charset=" . $this->Encoding . "\n";
        $tempBody .= "Content-Transfer-Encoding: 7bit\n";
        $tempBody .= "\n";
        $tempBody .= $content;
        $tempBody .= "\n\n";
        $tempBody .= "--$boundary\n";
        $tempBody .= sprintf("Content-Type: %s; name=\"%s\"\n", $this->Attachment['mime'], $this->Attachment['name']);
        $tempBody .= sprintf("Content-Disposition: attachment; filename=\"%s\"\n", $this->Attachment['name']);
        $tempBody .= "Content-Transfer-Encoding: base64\n\n";
        $tempBody .= chunk_split(base64_encode(file_get_contents($this->Attachment['data']))) . "\n";
        $tempBody .= "--$boundary--\n";
        $content= $tempBody;
      }
    }
    $content = mb_convert_encoding( $content, $this->Encoding, 'utf8' );
    if ( !$this->Envelope && $this->FromAddress ) $this->Envelope = $this->FromAddress;

    if ( $this->SMTPServer ) {
      $tmp  = array();
      $tmp2 = array();
      if ( !is_array ( $TO ) )  $TO = array ( $TO );
      foreach ( $TO as $row ) {
        $tmp[]  = $this->encodeAddress ( $row );
        if   ( preg_match ( '#<(.+?)>#', $row, $matches ) ) $tmp2[] = $matches[1];
        else                                                $tmp2[] = $row;
      }
      if ( !$this->SMTPServer->send ( array (
        'envelope_from' => $this->encodeAddress ( $this->Envelope ),
        'evenlope_to'   => join ( ',', $tmp2 ),
        'to'            => join ( ',', $tmp ),
        'subject'       => $this->mimeEncode( $subject ),
        'body'          => trim ( $content ),
        'header'        => trim ( $header ),
        ))) var_dump ( $this->SMTPServer->getLogs() );
    } else {
      $tmp = array();
      if ( !is_array ( $TO ) )  $TO = array ( $TO );
      foreach ( $TO as $row ) $tmp[] = $this->encodeAddress ( $row );
      $result = mail (
        join( ',', $tmp ),
        $this->mimeEncode ( $subject ) ,
        trim ( $content ),
        trim ( $header ),
        $this->Envelope ? '-f'.  $this->Envelope :  null
      );
    }
    return $result;
  }

  /**
   * DefaultBccのセッタ
   * @param セットしたい値
   **/
  public function setDefaultBcc ( $str ) {
    $this->DefaultBCC = $str;
  }

  //指定バイトごとに改行を付与
  //$htmlTagProtectをtrueに設定するとHTMLタグが無効にならないように
  //$bytePerLineを越えてキリのいいところで改行を付与する
  private function breakLine($str, $bytePerLine, $encoding = 'ISO-2022-JP', $br = "\n", $htmlTagProtect = true){

    //指定文字コードに変換
    $cmbStr = mb_convert_encoding($str, $encoding, 'utf8');
    $cmbBr = mb_convert_encoding($br, $encoding, 'utf8');

    //改行文字で分解
    $lineArr = explode($cmbBr, $cmbStr);

    //行数分ループ
    foreach($lineArr as $line){
      $line_cnt++;

      if ( strlen ( bin2hex ( $line ) ) / 2 < $bytePerLine ) {
        $ret .= $line . $cmbBr;
        continue;
      }

      //文字数を取得
      $strLen = mb_strlen($line, $encoding);

      //文字数分ループ
      $tempStr = '';
      for($i=0; $i<$strLen; $i++){
        $tempStr .= mb_substr($line, $i, 1, $encoding);

        //1行当たりのバイト数に達したとき改行を付与
        if(strlen(bin2hex($tempStr)) / 2 >= $bytePerLine){

          //HTMLタグ保護
          if($htmlTagProtect){
            //タグの途中（タグの意味が保障されない状態）で改行しないように保護
            if(preg_match('/<[^>]*[^\s;>]$|<[^\s]*$/', $tempStr) > 0){
              continue;
            }
          }

          //改行付与
          if($i != ($strLen - 1)){
            $tempStr .= $cmbBr;
          }

          //戻り値に追加
          $ret .= $tempStr;
          $tempStr = '';
        }
      }

      //指定バイト数に達さない半端な文字を戻り値に追加
      if($tempStr != ''){
        $ret .= $tempStr;
      }

      //分解したときに取り除いた改行を付与
      if($line_cnt != count($lineArr)){
        $ret .= $cmbBr;
      }

      $tempStr = '';
    }
    return mb_convert_encoding($ret, 'utf8', $encoding);
  }


  /**
   * てきとーにメールアドレスをエンコードする
   * @param したいアドレス ( mixi-in )
   * @return した結果 ( string )
   **/
   private function encodeAddress ( $address ) {
     if ( !$address )  return '';
     if ( is_scalar ( $address ) ) $address = array ( $address );
     $buf = array ();
     foreach ( $address as $str ) {
       if ( preg_match ( '#(.+?)<(.+?)>#', $str, $tmp ) ) $buf[] = $this->mimeEncode ( $tmp[1] ) . '<' . $tmp[2] . '>';
       else                                               $buf[] = $str;
     }
     return join( ',', $buf );
  }

  /**
   * てきとーにmimeEncodeする
   * @param したい文字
   **/
  private function mimeEncode ( $str ) {
    return '=?iso-2022-jp?B?' . base64_encode( mb_convert_encoding( $str, "JIS", 'UTF-8' ) ) . '?=';
  }
}
