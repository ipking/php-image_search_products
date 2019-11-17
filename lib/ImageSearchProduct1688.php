<?php

class ImageSearchProduct1688
{
	private function curl($url, $headers=array(), $post_data=array()){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_URL, $url);
		if($headers){
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}
		if($post_data){
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		}
		$html = curl_exec($ch);
		curl_close($ch);
		return $html;
	}
	
	private function build_param_str($params){
		$param = [];
		foreach($params as $key=>$value){
			$param[] = $key.'='.$value;
		}
		return join('&',$param);
	}
	
	private function createRandomStr($length = 16) {
		$chars = "ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678";
		$str = "";
		for ($i = 0; $i < $length; $i++) {
			$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}
		return $str;
	}
	
	private function get_header(){
		$headers = [
			"Origin: https://www.1688.com",
			"User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36",
			"Accept: */*",
			"Cache-Control: no-cache",
			"Referer:https://www.1688.com/",
		];
		return $headers;
	}
	
	private function get_data_set(){
		$url = 'https://open-s.1688.com/openservice/.htm';
		$callback = "jQuery_".time();
		$params = [
			"callback"   => $callback,
			"serviceIds" => "cbu.searchweb.config.system.currenttime",
			"outfmt"     => "jsonp",
		];
		$url = $url.'?'.$this->build_param_str($params);
		$data = $this->curl($url,$this->get_header());
		
		$json = str_replace($callback.'(','',$data);
		$json = str_replace(');','',$json);
		$data_set = json_decode($json,1)['cbu.searchweb.config.system.currenttime']['dataSet'];
		return $data_set;
	}
	
	private function get_signature($data_set){
		$url = 'https://open-s.1688.com/openservice/ossDataService';
		$appkey = "pc_tusou;{$data_set}";
		$callback = "jQuery_".time();
		
		$params = [
			"appName"=> "pc_tusou",
			"appKey"=> base64_encode($appkey),
			"callback"=> $callback
		];
		
		$url = $url.'?'.$this->build_param_str($params);
		$data =  $this->curl($url,$this->get_header());
		$json = str_replace($callback.'(','',$data);
		$json = str_replace(');','',$json);
		$set = json_decode($json,1)['data'];
		return $set;
	}
	
	
	private function upload_image_by_url($image_url,$policy,$OSSAccessKeyId,$signature){
		$url = 'https://cbusearch.oss-cn-shanghai.aliyuncs.com/';
		
		$key = "cbuimgsearch/".$this->createRandomStr(10).(time() * 1000).".jpg";
		$name = "1.jpg";
		$post_data = [
			"name"=> $name,
			"key"=> $key,
			"policy"=> $policy,
			"OSSAccessKeyId"=> $OSSAccessKeyId,
			"success_action_status"=> "200",
			"callback"=> "",
			"signature"=> $signature,
			"file"=> file_get_contents($image_url),
		];
		$content = $this->curl($url,$this->get_header(),$post_data);
		if($content){
			//说明有问题
			return $content;
		}
		$target_url = "https://s.1688.com/youyuan/index.htm?tab=imageSearch&imageType=oss&imageAddress=".$key;
		
		return $target_url;
	}
	
	public function getByUrl($image_url){
		$data_set = $this->get_data_set();
		$sign_data = $this->get_signature($data_set);
		$url = $this->upload_image_by_url($image_url,$sign_data['policy'],$sign_data['accessid'],$sign_data['signature']);
		return $url;
	}
}