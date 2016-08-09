<?php
class common
{
	/**
	 * //Generate sort url
	 *
	 * @param string $url
	 * @param string $sortField
	 * @param string $direction {ASC, DESC}
	 * @param string $label
	 * @param array $params
	 *
	 * params = array(){
	 * 	'p' => $page,
	 * 	'keyword'=>$keyword,
	 * 	...
	 * 	'sort' => $this->params['order'],
	 * };
	 */
	public static function getSortUrl($url, $sortField, $direction, $label, $params=array()){
		$uri = '';
		$icon = '';
		//Detected sort direction
		if($params['sort']['name'] == $sortField ){
			$direction = ('ASC' == $params['sort']['direction'])?'DESC':'ASC';
		}

		if( $params['$direction'] ){
			$params['$direction'] = $direction;
		}

		if($params){
			foreach($params as $key => $value){
				if( 'sort' != $key && '' != $value){
					$uri .= "&$key=$value";
				}
			}
		}

		$uri .= "&sort=$sortField&order=$direction";
		$uri = ltrim($uri, '&');

		if( 'ASC' == $direction){
			$icon = '<span class="icon-circle-arrow-down"></span>';
		}else{
			$icon = '<span class="icon-circle-arrow-up"></span>';
		}

		$uri = $url . '?' . $uri;

		if( $params['sort']['name'] == $sortField){
			return '<a href="' . $uri . '">' . $label.$icon . '</a>';
		}else{
			return '<a href="' . $uri . '">' . $label . '</a>';
		}

	}
	
	static function getUrl($url, $params=array()) {
		$uri = '';
	
		if ($params) {
			foreach ($params as $key => $value) {
				$uri .= "&$key=$value";
			}
		}
	
		$uri = ltrim($uri, '&');
		$uri = $url . '?' . $uri;
	
		return $uri;	
	}
	
	protected function _strip_html_tags($text) {
		// PHP's strip_tags() function will remove tags, but it
		// doesn't remove scripts, styles, and other unwanted
		// invisible text between tags.  Also, as a prelude to
		// tokenizing the text, we need to insure that when
		// block-level tags (such as <p> or <div>) are removed,
		// neighboring words aren't joined.
		$text = preg_replace(
		array(
		// Remove invisible content
                            '@<head[^>]*?>.*?</head>@siu',
                            '@<style[^>]*?>.*?</style>@siu',
                            '@<script[^>]*?.*?</script>@siu',
                            '@<object[^>]*?.*?</object>@siu',
                            '@<embed[^>]*?.*?</embed>@siu',
                            '@<applet[^>]*?.*?</applet>@siu',
                            '@<noframes[^>]*?.*?</noframes>@siu',
                            '@<noscript[^>]*?.*?</noscript>@siu',
                            '@<noembed[^>]*?.*?</noembed>@siu',
		// Add line breaks before & after blocks
                            '@<((br)|(hr))@iu',
                            '@</?((address)|(blockquote)|(center)|(del))@iu',
                            '@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
                            '@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
                            '@</?((table)|(th)|(td)|(caption))@iu',
                            '@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
                            '@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
                            '@</?((frameset)|(frame)|(iframe))@iu',
		),
		array(
                            ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',
                            "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0",
                            "\n\$0", "\n\$0",
		),
		$text);

		// Remove all remaining tags and comments and return.
		return strip_tags($text);
	}
	
	public static function excerpt($str, $length=200, $mb_flg = true) {
		$str = self::_strip_html_tags($str);
		
		if( $mb_flg ){
			$str1 = mb_substr($str, 0, $length, 'utf8');
		}else{
			$str1 = substr($str, 0, $length);
		}

		if(strlen($str) > $length)
			return $str1 . ' ... ';
		return $str1;
	}
}