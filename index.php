<?php

function dump($data){
	echo "<pre>";
	var_dump($data);
}
//刷新方法
function refresh_txcdn_cache($arr){
	$secretId='';//后台获取
	$secretKey='';//后台获取
	$action='PurgeUrlsCache';
	$version="2018-06-06";
	//刷新地址自行修改
	
	$PRIVATE_PARAMS=[];
	 foreach($arr as $k=>$v){
		 $PRIVATE_PARAMS[ 'Urls.'.$k ]=$v;
	 }

	$HttpUrl="cdn.tencentcloudapi.com/";
	$HttpMethod="GET";
	$isHttps =true;
		
	$COMMON_PARAMS = array(
					'Action' =>$action,
					'Timestamp' =>time(),
					'Nonce' => rand(),
					'SecretId' => $secretId,
					'Version'=>$version,		
					);
	return CreateRequest($HttpUrl,$HttpMethod,$COMMON_PARAMS,$secretKey, $PRIVATE_PARAMS, $isHttps);
}


function CreateRequest($HttpUrl,$HttpMethod,$COMMON_PARAMS,$secretKey, $PRIVATE_PARAMS, $isHttps)
{
        //$FullHttpUrl = $HttpUrl."/v2/index.php";
        $FullHttpUrl = $HttpUrl;
        /***************对请求参数 按参数名 做字典序升序排列，注意此排序区分大小写*************/
        $ReqParaArray = array_merge($COMMON_PARAMS, $PRIVATE_PARAMS);
        ksort($ReqParaArray);
        $SigTxt = $HttpMethod.$FullHttpUrl."?";

        $isFirst = true;
        foreach ($ReqParaArray as $key => $value)
        {
                if (!$isFirst) 
                {
                        $SigTxt = $SigTxt."&";
                }
                $isFirst= false;

                /*拼接签名原文时，如果参数名称中携带_，需要替换成.*/
                if(strpos($key, '_'))
                {
                        $key = str_replace('_', '.', $key);
                }

                $SigTxt=$SigTxt.$key."=".$value;
        }

        /*********************根据签名原文字符串 $SigTxt，生成签名 Signature******************/
        $Signature = base64_encode(hash_hmac('sha1', $SigTxt, $secretKey, true));

        /***************拼接请求串,对于请求参数及签名，需要进行urlencode编码********************/
        $Req = "Signature=".urlencode($Signature);
        foreach ($ReqParaArray as $key => $value)
        {
                $Req=$Req."&".$key."=".urlencode($value);
        }
		
        /*********************************发送请求********************************/
        if($HttpMethod === 'GET')
        {
                if($isHttps === true)
                {
                        $Req="https://".$FullHttpUrl."?".$Req;
                }
                else
                {
                        $Req="http://".$FullHttpUrl."?".$Req;
                }
		
                $Rsp = file_get_contents($Req);
        }
        else
        {
			if($isHttps === true)
			{
					$Rsp= SendPost("https://".$FullHttpUrl,$Req,$isHttps);
			}
			else
			{
					$Rsp= SendPost("http://".$FullHttpUrl,$Req,$isHttps);
			}
        }

      //输出返回数据
        $rst=json_decode($Rsp,true);
		//dump($rst);
		if($rst && !isset($rst['Response']['Error'])){
			echo '刷新成功';
			return 1;
		}else{
			
			echo '刷新失败';
			dump($rst);
			return 0;
		}
}

function SendPost($FullHttpUrl, $Req, $isHttps)
{
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $Req);
		
        curl_setopt($ch, CURLOPT_URL, $FullHttpUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($isHttps === true) {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,  false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  false);
        }

        $result = curl_exec($ch);
        return $result;
}

$arr=[
	'http://tieie.com/about.html',
	'http://tieie.com/index.html'

];

$rst=refresh_txcdn_cache($arr);
var_dump($rst);