<?php
########################################################
# Script Info
# ===========
# File: ImageEditor.php
# Created: 05/06/03
# Modified: 16/05/04
# Author: Ash Young (ash@evoluted.net)
# Website: http://evoluted.net/php/image-editor.htm
# Requirements: PHP with the GD Library
#
# Description
# ===========
# This class allows you to edit an image easily and
# quickly via php.
#
# If you have any functions that you like to see 
# implemented in this script then please just send
# an email to ash@evoluted.net
#
# Limitations
# ===========
# - GIF Editing: this script will only edit gif files
#     your GD library allows this.
#
# Image Editing Functions
# =======================
# resize(int width, int height)
#    resizes the image to proportions specified.
#
# crop(int x, int y, int width, int height)
#    crops the image starting at (x, y) into a rectangle
#    width wide and height high.
#
# addText(String str, int x, int y, Array color)
#    adds the string str to the image at position (x, y)
#    using the colour given in the Array color which
#    represents colour in RGB mode.
#
# addLine(int x1, int y1, int x2, int y2, Array color)
#    adds the line starting at (x1,y1) ending at (x2,y2)
#    using the colour given in the Array color which
#    represents colour in RGB mode.
#
# setSize(int size)
#    sets the size of the font to be used with addText()
#
# setFont(String font)
#    sets the font for use with the addText function. This
#    should be an absolute path to a true type font
#
# shadowText(String str, int x, int y, Array color1, Array color2, int shadowoffset)
#    creates show text, using the font specified by set font.
#    adds the string str to the image at position (x, y)
#    using the colour given in the Array color which
#    represents colour in RGB mode.
#
# Useage
# ======
# First you are required to include this file into your
# php script and then to create a new instance of the
# class, giving it the path and the filename of the
# image that you wish to edit. Like so:
#
# include("ImageEditor.php");
# $imageEditor = new ImageEditor("filename.jpg", "directoryfileisin/");
#
# After you have done this you will be able to edit the
# image easily and quickly. You do this by calling a
# function to act upon the image. See below for function
# definitions and descriptions see above. An example
# would be:
#
# $imageEditor->resize(400, 300);
#
# This would resize our imported image to 400 pixels by
# 300 pixels. To then export the edited image there are
# two choices, out put to file and to display as an image.
# If you are displaying as an image however it is assumed
# that this file will be viewed as an image rather than
# as a webpage. The first line below saves to file, the
# second displays the image.
#
# $imageEditor->outputFile("filenametosaveto.jpg", "directorytosavein/");
#
# $imageEditor->outputImage();
########################################################

class ImageEditor {
  var $x;
  var $y;
  var $type;
  var $img;  
  var $font;
  var $error;
  var $size;

  ########################################################
  # CONSTRUCTOR
  ########################################################
  function ImageEditor($filename=0, $path=0, $col=NULL)
  {
    $this->font = false;
    $this->error = false;
    $this->size = 15;
    if(is_numeric($filename) && is_numeric($path))
    ## IF NO IMAGE SPECIFIED CREATE BLANK IMAGE
    {
      $this->x = $filename;
      $this->y = $path;
      $this->type = "jpg";
      $this->img = imagecreatetruecolor($this->x, $this->y);
      if(is_array($col)) 
      ## SET BACKGROUND COLOUR OF IMAGE
      {
        $colour = ImageColorAllocate($this->img, $col[0], $col[1], $col[2]);
        ImageFill($this->img, 0, 0, $colour);
      }
    }
    else
    ## IMAGE SPECIFIED SO LOAD THIS IMAGE
    {
      ## FIRST SEE IF WE CAN FIND IMAGE

      if(remote_file_exists($path . $filename))
      {
        $file = $path . $filename;
      }
      else if (remote_file_exists($path . "/" . $filename))
      {
        $file = $path . "/" . $filename;
      }
      else
      {
        $this->errorImage("File Could Not Be Loaded");
      }
      
      if(!($this->error)) 
      {
        ## LOAD OUR IMAGE WITH CORRECT FUNCTION
        $this->type = strtolower(end(explode('.', $filename)));
        if ($this->type == 'jpg' || $this->type == 'jpeg') 
        {
          $this->img = @imagecreatefromjpeg($file);
        } 
        else if ($this->type == 'png') 
        {
          $this->img = @imagecreatefrompng($file);
        } 
        else if ($this->type == 'gif') 
        {
          $this->img = @imagecreatefromgif($file);
        }
        ## SET OUR IMAGE VARIABLES
        $this->x = imagesx($this->img);
        $this->y = imagesy($this->img);
      }
    }
  }

  ########################################################
  # RESIZE IMAGE GIVEN X AND Y
  ########################################################
  function resize($new_width, $new_height, $crop_w=0, $crop_h=0, $keep_ratio = true)
  {
    if(!$this->error)
    {
       //*********************************************************
       // Calcul des variables
       //*********************************************************
       if ($new_width == 0) $new_width = $this->x;
       if ($new_height == 0) $new_height = $this->y;

       $L_ratio = $new_width / ( $this->x - 2 * $crop_w );
       $H_ratio = $new_height / ( $this->y - 2 * $crop_h );

       // calcul image destination
       $dst_x = 0;
       $dst_y = 0;
       if ($keep_ratio)
            if ($L_ratio > $H_ratio) {
                 $dst_w = ( $this->x - 2 * $crop_w )* $H_ratio;
                 $dst_h = $new_height; }
            else {
                 $dst_w = $new_width;
                 $dst_h = ( $this->y - 2 * $crop_h ) * $L_ratio; }
       else {
            $dst_w = $new_width;
            $dst_h = $new_height; }

       // calcul image source
       $L_ratio = $dst_w / ( $this->x - $crop_w );
       $H_ratio = $dst_h / ( $this->y - $crop_h );

       if ($H_ratio > $L_ratio) {
             $src_w = ( $this->y - 2 * $crop_h ) * $dst_w / $dst_h;
             $src_x = ($this->x - $src_w)/2 ;
             $src_y = $crop_h;
             $src_h = $this->y - 2 * $crop_h; }
       else {
             $src_h = ( $this->x - 2 * $crop_w ) * $dst_h / $dst_w;
             $src_y = ($this->y - $src_h)/2 ;
             $src_x = $crop_w;
             $src_w = $this->x - 2 * $crop_w; }

       //*********************************************************

       // sizes should be integers
       settype($src_x, 'integer');
       settype($src_y, 'integer');
       settype($src_w, 'integer');
       settype($src_h, 'integer');
       settype($dst_w, 'integer');
       settype($dst_h, 'integer');

       // create new image
       $tmpimage = imagecreatetruecolor($dst_w, $dst_h);
       // imageresampled whill result in a much higher quality than imageresized
       imagecopyresampled($tmpimage, $this->img, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
       imagedestroy($this->img);
       $this->img = $tmpimage;
       $this->y = $dst_h;
       $this->x = $dst_w;
    }
  }
  
  ########################################################
  # CROPS THE IMAGE, GIVE A START CO-ORDINATE AND
  # LENGTH AND HEIGHT ATTRIBUTES
  ########################################################
  function crop($x, $y, $width, $height)
  {
    if(!$this->error)
    {
      $tmpimage = imagecreatetruecolor($width, $height);
      imagecopyresampled($tmpimage, $this->img, 0, 0, $x, $y,
                           $width, $height, $width, $height);
      imagedestroy($this->img);
      $this->img = $tmpimage;
      $this->y = $height;
      $this->x = $width;
    }
  }

  ########################################################
  # ADDS TEXT TO AN IMAGE, TAKES THE STRING, A STARTING
  # POINT, PLUS A COLOR DEFINITION AS AN ARRAY IN RGB MODE
  ########################################################
  function addText($str='', $x=0, $y=0, $col=array(0,0,0), $f_size=2)
  {
    $this->setSize ($f_size);
    if(!$this->error)
    {
      if($this->font) {
        $colour = ImageColorAllocate($this->img, $col[0], $col[1], $col[2]);
        if(!imagettftext($this->img, $this->size, 0, $x, $y, $colour, $this->font, $str)) {
          $this->font = false;
          $this->errorImage("Error Drawing Text");
        }
      }
      else {
        $base_font_size = $f_size/5;
        $colour = ImageColorAllocate($this->img, $col[0], $col[1], $col[2]);
        Imagestring($this->img, $base_font_size, $x, $y, $str, $colour);
      }
    }
  }
  
  function shadowText($str, $x, $y, $col1, $col2, $offset=2) {
   $this->addText($str, $x, $y, $col1);
   $this->addText($str, $x-$offset, $y-$offset, $col2);   

  }
  
  ########################################################
  # ADDS A LINE TO AN IMAGE, TAKES A STARTING AND AN END
  # POINT, PLUS A COLOR DEFINITION AS AN ARRAY IN RGB MODE
  ########################################################
  function addLine($x1, $y1, $x2, $y2, $col) 
  {
    if(!$this->error) 
    {
      $colour = ImageColorAllocate($this->img, $col[0], $col[1], $col[2]);
      ImageLine($this->img, $x1, $y1, $x2, $y2, $colour);
    }
  }

  ########################################################
  # RETURN OUR EDITED FILE AS AN IMAGE
  ########################################################
  function outputImage() 
  {
    if ($this->type == 'jpg' || $this->type == 'jpeg') 
    {
      header("Content-type: image/jpeg");
      imagejpeg($this->img);
    } 
    else if ($this->type == 'png') 
    {
      header("Content-type: image/png");
      imagepng($this->img);
    } 
    else if ($this->type == 'gif') 
    {
      header("Content-type: image/gif");
      imagegif($this->img);
    }
  }

  ########################################################
  # CREATE OUR EDITED FILE ON THE SERVER
  ########################################################
  function outputFile($filename, $path) 
  {
    if ($this->type == 'jpg' || $this->type == 'jpeg') 
    {
      imagejpeg($this->img, ($path . $filename));
    } 
    else if ($this->type == 'png') 
    {
      imagepng($this->img, ($path . $filename));
    } 
    else if ($this->type == 'gif') 
    {
      imagegif($this->img, ($path . $filename));
    }
  }


  ########################################################
  # SET OUTPUT TYPE IN ORDER TO SAVE IN DIFFERENT
  # TYPE THAN WE LOADED
  ########################################################
  function setImageType($type)
  {
    $this->type = $type;
  }
  
  ########################################################
  # ADDS TEXT TO AN IMAGE, TAKES THE STRING, A STARTING
  # POINT, PLUS A COLOR DEFINITION AS AN ARRAY IN RGB MODE
  ########################################################
  function setFont($font) {
    $this->font = $font;
  }

  ########################################################
  # SETS THE FONT SIZE
  ########################################################
  function setSize($size) {
    $this->size = $size;
  }
  
  ########################################################
  # GET VARIABLE FUNCTIONS
  ########################################################
  function getWidth()                {return $this->x;}
  function getHeight()               {return $this->y;}
  function getImageType()            {return $this->type;}

  ########################################################
  # CREATES AN ERROR IMAGE SO A PROPER OBJECT IS RETURNED
  ########################################################
  function errorImage($str)
  {
    $this->error = false;
    $this->x = 235;
    $this->y = 50;
    $this->type = "jpg";
    $this->img = imagecreatetruecolor($this->x, $this->y);
    $this->addText("AN ERROR OCCURED:", 10, 5, array(70,70,0));
    $this->addText($str, 10, 30, array(255,255,255));
    $this->error = true;
  }
  ########################################################
  # ADD A SEMI-TRANSPARENT BOX WITH OPTIONAL TEXT IN IT
  ########################################################
  function AddBox($foot=true, $r=0, $g=0, $b=0, $bh, $text_box='', $rt=255, $gt=255, $bt=255, $text_size=25)
  {
    $box_x1 = '0';
    $box_xi1 = 0;
    $box_x2 = strval($this->GetWidth());
    $img_height = $this->GetHeight();
    $display_text = ' '.utf8_decode($text_box);

    if ($foot) $box_y1 = strval($img_height-$bh); else $box_y1 = '0';
    $box_yi1 = intval($box_y1);
    if ($foot) $box_y2 = strval($img_height); else $box_y2 = strval($bh);

    $colour = ImageColorAllocateAlpha ($this->img, $r, $g, $b, 80);

    Imagefilledrectangle ($this->img, $box_x1, $box_y1, $box_x2, $box_y2, $colour);

    if ($display_text != '')
    {
        $col[0]=$rt; $col[1]=$gt;$col[2]=$bt;
        $this->addText ($display_text, $box_xi1, $box_yi1, $col, $text_size);
    }

  }

}
?>
