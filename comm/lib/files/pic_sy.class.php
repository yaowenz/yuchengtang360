<?php
class pic_sy {

/**
* 加给图片加水印
*
* @param strimg $groundImage 要加水印地址
* @param int $waterPos 水印位置
* @param string $waterImage 水印图片地址
* @param string $waterText 文本文字
* @param int $textFont 文字大小
* @param string $textColor 文字颜色
* @param int $minWidth 小于此值不加水印
* @param int $minHeight 小于此值不加水印
* @param float $alpha 透明度
* @return FALSE
* @author liyonghua 2008-10-28 修改中... 
*/
public static function waterMark($groundImage , $waterPos = 0 , $waterImage = "" , $waterText = "" , $textFont = 15 , $textColor = "#FF0000",$minWidth='100',$minHeight='100',$alpha=0.9){ 
	$isWaterImg = FALSE;
	$bg_h = $bg_w = $water_h = $water_w = 0;
	//获取背景图的高，宽
	if(is_file($groundImage) && !empty($groundImage)){
	   $bg = new Imagick();
	   $bg ->readImage($groundImage);
	   $bg_h = $bg->getImageHeight();
	   $bg_w = $bg->getImageWidth();
	   $bg->setImageCompressionQuality(30);
	}
	//获取水印图的高，宽
	if(is_file($waterImage) && !empty($waterImage)){
	   $water = new Imagick($waterImage);
	   $water_h = $water->getImageHeight();
	   $water_w = $water->getImageWidth();
	   $water->setImageCompressionQuality(30);
	}
	//如果背景图的高宽小于水印图的高宽或指定的高和宽则不加水印
	
	 $isWaterImg = TRUE;

	//加水印
	if($isWaterImg){  
	   $dw = new ImagickDraw();  
	   //加图片水印
	   if(is_file($waterImage)){
		$water->setImageOpacity($alpha);
		$dw -> setGravity($waterPos);
		$dw -> composite($water->getImageCompose(),0,0,50,0,$water);
		$bg -> drawImage($dw);
		if(!$bg -> writeImage($groundImage)){
		 return FALSE;
		}  
	   }else{
		//加文字水印
		$dw -> setFontSize($textFont);
		$dw -> setFillColor($textColor);
		$dw -> setGravity($waterPos);
		$dw -> setFillAlpha($alpha);
		$dw -> annotation(0,0,$waterText);
		$bg -> drawImage($dw);
		if(!$bg -> writeImage($groundImage)){
		 return FALSE;
		}
	   }
	}
}

}