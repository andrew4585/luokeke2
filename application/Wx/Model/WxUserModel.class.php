<?php
namespace Wx\Model;
use Common\Model\CommonModel;

use Think\Log;

class WxUserModel extends CommonModel{

    public $wechat;
    /**
     * 用户关注
     * @param string $openid	用户openid
     */
    public function subscribe($openid){
        $model_user = D("Users");
        //查看微信用户表中是否存在用户
        $hasUser = $this->where("openid='$openid'")->find();
        if($hasUser){
            //检查用户表中是否存在该用户（正常情况下，不会出现不存在的情况）
            $hasUser2 = $model_user->where("openid='$openid'")->find();
            if($hasUser2){
                unset($data);
                $data['is_subscribe'] = 1;
                $data['subscribe_time'] = time();
                $this->where("openid='$openid'")->setField($data);
                return true;
            }else{
                //该情况除非程序含有逻辑错误，否则逻辑上不会发生，纯属作者杞人忧天^_^
                $this->where("openid='$openid'")->delete();
                Log::record("微信表中的用户存在，而用户表用户不存在，请检查程序逻辑");
                return $this->subscribe($openid);
            }
        }else{
            //将用户信息存入微信用户表中
            unset($data);
            $this->getThinkWechat();
            $user	= $this->wechat->user($openid);
            $data['openid']			= $openid;
            $data['nick_name']		= $user['nickname'];
            $data['sex']			= $user['sex'];
            $data['headimgurl']		= $user['headimgurl'];
            $data['subscribe_time']	= $user['subscribe_time'];
            $result=$this->add($data);
            if($result){
                //将用户存入用户表中
                $data1['openid']    = $openid;
                $data1['user_from'] = 'wechat';
                $data1['user_login']= $user['nickname'];
                $data1['avatar']    = $user['headimgurl'];
                $data1['sex']		= $user['sex'];
                $data1['user_type'] = 2;
                $data1['create_time']=date('Y-m-d H:i:s',$user['subscribe_time']);
                $useradd = $model_user->add($data1);
                if($useradd){
                    return true;
                }else{
                    $this->where("openid='$openid'")->delete();
                    return false;
                }
            }else{
                return false;
            }
        }
    }
    
    /**
     * 获取关注用户的openid
     * @throws \Exception
     */
    public function getOpenidList(){
        $this->getThinkWechat();
        $restr=$this->wechat->getOpenIdList();
        $restr=json_decode($restr,true);
        if(!empty($restr[errmsg])){
            throw new \Exception($restr[errmsg]);
        }else{
            return $restr['data']['openid'];
        }
    }
    
    /**
     * 取消关注
     * @param unknown_type $openid
     */
    public function delSubscribe($openid){
        $model_group=D("WxGroup");
        $where="openid='$openid'";
        $groupid=$this->where($where)->getField("groupid");
        $model_group->where("id=".$groupid)->setDec("count",1);
        $data['is_subscribe'] = 0;
        $data['del_time']	  = time();
        $result=$this->where($where)->setField($data);
        return $result;
    }
    
    /**
     * 获取所有关注用户的openid
     */
    public function getOpenidBySubUser(){
        return $this->field("openid")->where("is_subscribe=1")->select();
    }
    
    /**
     * 设置用户分组(通过微信api)
     */
    public function set_groupid($openid){
        $this->getThinkWechat();
        $restr=$this->wechat->getUserGroupid($openid);
        $restr=json_decode($restr,true);
        if(!empty($restr['errcode'])){
            Log::record("获取分组信息失败，错误信息:{$restr['errcode']},openid:$openid");
            return false;
        }
        $groupid=$restr['groupid'];
        $result = $this->where("openid='$openid'")->setField("groupid",$groupid);
        return $result;
    }
    
    /**
     * 创建微信实例
     */
    public function getThinkWechat(){
        if(!$this->wechat){
            $model_config = D("WxConfig");
            import("Think.WX.ThinkWechat");
            $this->wechat = new \ThinkWechat($model_config->val("appid"),$model_config->val("appsecret"));
        }
    }
    
	protected function _before_write(&$data) {
		parent::_before_write($data);
	}
	
	
}
