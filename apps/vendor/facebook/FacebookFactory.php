<?php
require_once 'facebook.php';
class FacebookFactory {

	private static $Instances = null;

	public static function getInstance($appProperties) {
		if (!self::$Instances) {
			self::$Instances = new facebook($appProperties);
		}
		return self::$Instances;
	}

	public static function fbRedirect($fbUrl) {
		echo "<script type='text/javascript'>top.location.href = '$fbUrl';</script>";
		exit();
	}

	public static function fbCheckLogin($appProperties = null, $nextUrl = null, $checkPermission = true, $facebookBotThrough = false, $needRedirectFlg = true) {
		if ($facebookBotThrough && preg_match('/^facebookexternalhit/', $_SERVER['HTTP_USER_AGENT']))
			return;

		$settings = aafwApplicationConfig::getInstance();
		$facebook = self::getInstance($appProperties);

        $tmp_scopes = '';
		foreach ($settings->Facebook['Admin']['Permissions'] as $scope) {
			$tmp_scopes .= $scope . ',';
		}
		$scopes = rtrim($tmp_scopes, ',');

        $userId = $facebook->getUser();
        $redirect_flg = false;
        if ($userId) {
            if ($checkPermission) {
                try {
                    $currentScope = $facebook->api('/me/permissions');
                    $arrScope = explode(",", $scopes);
                    $checkScopes = array();
                    foreach ($arrScope as $scope) {
                        $checkScopes[$scope] = false;
                        foreach($currentScope['data'] as $cur) {
                            if($cur['permission'] == $scope) {
                                if($cur['status'] == "granted") {
                                    $checkScopes[$scope] = true;
                                }
                                break;
                            }
                        }
                        if (!$checkScopes[$scope]) {
                            $redirect_flg = TRUE;
                            break;
                        }
                    }
                }catch (FacebookApiException $fae) {
                    $redirect_flg = TRUE;
                }
            }
        } else {
            $redirect_flg = true;
        }

		$next = "";
		if ($nextUrl) {
			$next = $nextUrl;
		} else {
			$next = (((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || '443' == $_SERVER['HTTP_X_FORWARDED_PORT']) ? 'https://' : 'http://');
			$next .= $settings->Domain . $_SERVER['REQUEST_URI'];
		}
		if ($redirect_flg && $needRedirectFlg) {
			if ($nextUrl) {
				$loginUrl = $facebook->getLoginUrl(array(
					'redirect_uri' => $next,
					'scope' => $scopes
				));
			} else {
				$loginUrl = $facebook->getLoginUrl(array(
					'scope' => $scopes
				));
			}
			self::fbRedirect($loginUrl);
		} else {
			return $facebook;
		}
	}


	public static function getContentFromUrl($url) {
		$url = str_replace("&amp;", "&", urldecode(trim($url)));

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$contents = curl_exec($ch);

		return $contents;
	}

	public static function getLongAccessToken($access_token_temp, $params=array()){
		$url = 'https://graph.facebook.com/oauth/access_token?client_id='.$params['appId'].'&client_secret='.$params['secret'].'&grant_type=fb_exchange_token&fb_exchange_token='.$access_token_temp;
		$result = self::getContentFromUrl($url);
		$return = array();
		if(!$result) return false;
		$ary = explode("&", $result);
		if(count($ary)>0){
			foreach($ary as $item){
				list($key, $val) = explode("=", $item);
				$return[$key] = $val;
			}
		}
		return $return;
	}

}
