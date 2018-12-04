<?php
/**
 * Created by PhpStorm.
 * User: gzq
 * Date: 2018/11/27
 * Time: 16:21
 * curl 发送函数
 */
namespace Gaozhongqiang\Curl;
class curl{
    /**
     * post 发送信息
     * @param string $url 请求的url
     * @param array $post_data 请求的数据
     * @param int $is_decode url参数是否加密
     * @param int $timeout 超时时间
     * @param array $modify_header 头部信息
     * @return array 【返回的数据，请求url返回的状态码】
     */
    public static function curl_post_send($url,$post_data=array(),$is_decode=0,$timeout=-1,$modify_header=array())
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        //设置返回值存储在变量中
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        /****************************************************************************************
         * 全部数据使用HTTP协议中的 "POST" 操作来发送。 这个参数可以是 urlencoded 后的字符串，类似'para1=val1&para2=val2&...'，
         * 也可以使用一个以字段名为键值，字段数据为值的数组。 如果value是一个数组，Content-Type头将会被设置成multipart/form-data。
         ****************************************************************************************/
        $header = array('Expect:');
        if (!empty($is_decode)) {
            $header[] = "Content-type:application/x-www-form-urlencoded";
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        } else {
            $header[] = "Content-type:multipart/form-data";
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        }
        if (!empty($modify_header) && is_array($modify_header)) {
            $header = array_merge($header, $modify_header);
        }
        curl_setopt($ch, CURLOPT_HEADER, 0);
        /**************************************************************************
         * curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-type: text/plain'));
         * php使用curl出现Expect:100-continue解决方法
         * 解决方法如下，就是发送请求时，header中包含一个空的Expect。
         *****************************************************************************/
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $timeout != -1 && curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $output = curl_exec($ch);
        $http_state_arr = curl_getinfo($ch);
        curl_close($ch);
        return array($output, $http_state_arr["http_code"]);
    }
    /**
     * get 发送信息
     * @param string $url 请求的url
     * @param array $post_data 请求的数据
     * @param int $is_decode url参数是否加密
     * @param int $timeout 超时时间
     * @param array $modify_header 头部信息
     * @return array 【返回的数据，请求url返回的状态码】
     */
    public static function curl_get_send($url,$get_data=array(),$is_decode=0,$timeout=-1,$modify_header=array())
    {
        $url = self::query_string_url($url, $get_data, $is_decode);
        $ch = curl_init();

        if(stripos($url,"https://")!==FALSE){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }

        $header = array();
        if (!empty($modify_header) && is_array($modify_header)) {
            $header = array_merge($header, $modify_header);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLINFO_HEADER_OUT, 0);
        $timeout != -1 && curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $output = curl_exec($ch);
        $http_state_arr = curl_getinfo($ch);
        curl_close($ch);
        return array($output, $http_state_arr["http_code"]);
    }
    public static function query_string_url($url,$get_data,$is_decode){
        if(empty($url) || empty($get_data)){
            return $url;
        }
        $http_build_query_nodecode = function($queryArr){
            if(empty($queryArr)){
                return "";
            }
            $returnArr=array();
            foreach($queryArr as $key => $value){
                $returnArr[]=$key."=".$value;
            }
            return implode("&",$returnArr);
        };
        $queryString=!empty($is_decode) ? http_build_query($get_data) : $http_build_query_nodecode($get_data);
        return strripos($url,"?")!==false ? $url."&".$queryString : $url."?".$queryString;
    }
}