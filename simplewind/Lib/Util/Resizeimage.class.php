<?php
use Think\Log;

class Resizeimage
{
    //图片类型
    public $type;
    //实际宽度
    public $width;
    //实际高度
    public $height;
    //改变后的宽度
    public $resize_width;
    //改变后的高度
    public $resize_height;
    //是否裁图
    public $cut;
    //源图象
    public $srcimg;
    //源图像地址
    public $path;
    //目标图象地址
    public $dstimg;
    //临时创建的图象
    public $im;

    /**
     * 初始化属性
     * @param string $path			源图像地址,地址最后要加"/"
     * @param int 	$wid			宽度
     * @param int 	$hei			高度
     * @param int 	$c				是否剪切，$c=1时，图片剪切
     */
    public function initAttribute($path,$wid, $hei,$c){
    	$this->path=$path;
    	$this->resize_width = $wid;
    	$this->resize_height = $hei;
    	$this->cut = $c;
    	
    }
    /**
     * 生成缩略图
     * @param string $img	图片名称
     * @param int $wid		
     * @param int $hei		
     * @param int $c		
     * @return boolean
     */
    function resize($img,$dst='')
    {
    	$result=false;
    	$this->srcimg = $this->path.$img;
    	if(empty($dst)){
	    	$this->dst_img($img);
    	}else{
    		$this->dstimg=$dst;
    	}
        //图片的类型
		$this->type = strtolower(substr(strrchr($this->srcimg,"."),1));
        //初始化图象
        $this->initi_img();
        $this->width = imagesx($this->im);
        $this->height = imagesy($this->im);
        //生成图象
        $result=$this->newimg();
        ImageDestroy ($this->im);
        return $result;
    }
    function newimg()
    {
    	$result=false;
        //改变后的图象的比例
        $resize_ratio = ($this->resize_width)/($this->resize_height);
        if($this->resize_width>$this->width){
        	$this->resize_width=$this->width;
        	$this->resize_height=$this->height;
        }else if($this->resize_height>$this->height){
        	$this->resize_width=$this->width;
        	$this->resize_height=$this->height;
        }
        //实际图象的比例
        $ratio = ($this->width)/($this->height);
        if(($this->cut)=="1"){				//裁图
            if($ratio>=$resize_ratio) { 	//高度优先
                $newimg = imagecreatetruecolor($this->resize_width,$this->resize_height);
                imagecopyresampled($newimg, $this->im, 0, 0, 0, 0, $this->resize_width,$this->resize_height, (($this->height)*$resize_ratio), $this->height);
                $result=ImageJpeg ($newimg,$this->dstimg);
            }
            if($ratio<$resize_ratio){ 		//宽度优先
                $newimg = imagecreatetruecolor($this->resize_width,$this->resize_height);
                imagecopyresampled($newimg, $this->im, 0, 0, 0, 0, $this->resize_width, $this->resize_height, $this->width, (($this->width)/$resize_ratio));
                $result=ImageJpeg ($newimg,$this->dstimg);
            }
        }else {//不裁图
            if($ratio>=$resize_ratio){
                $newimg = imagecreatetruecolor($this->resize_width,($this->resize_width)/$ratio);
                imagecopyresampled($newimg, $this->im, 0, 0, 0, 0, $this->resize_width, ($this->resize_width)/$ratio, $this->width, $this->height);
                $result=ImageJpeg ($newimg,$this->dstimg);
                $this->resize_height=($this->resize_width)/$ratio;
            }
            if($ratio<$resize_ratio){
                $newimg = imagecreatetruecolor(($this->resize_height)*$ratio,$this->resize_height);
                imagecopyresampled($newimg, $this->im, 0, 0, 0, 0, ($this->resize_height)*$ratio, $this->resize_height, $this->width, $this->height);
                $result=ImageJpeg ($newimg,$this->dstimg);
                $this->resize_width=($this->resize_height)*$ratio;
            }
        }
        return $result;
    }
    //初始化图象
    function initi_img()
    {
    	ini_set("memory_limit", "60M");
        if($this->type=="jpg")
        {
            $this->im = imagecreatefromjpeg($this->srcimg);
        }
        if($this->type=="gif")
        {
            $this->im = imagecreatefromgif($this->srcimg);
        }
        if($this->type=="png")
        {
            $this->im = imagecreatefrompng($this->srcimg);
        }
    }
    //图象目标地址
    function dst_img($img){
    	$thumb=$this->path."thumb/";
        if(!file_exists($thumb)){
        	//生成缩略图所在的文件夹
        	mkdir($thumb,0777);
        }
        $this->dstimg = $thumb.$img;
    }
    
    
    /**
     * 设置是否剪切
     * @param int $cut	$cut=1时，图片剪切
     */
    public function setCut($cut){
    	$this->cut=$cut;
    }
    
    /**
     * 设置改变后尺寸
     * @param int $wid	宽度
     * @param int $hei	高度
     */
    public function setSize($wid,$hei){
    	$this->resize_width=$wid;
    	$this->resize_height=$hei;
    }
}
?>
