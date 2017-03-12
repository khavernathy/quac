<?php

function get_image_extension($filename, $include_dot = true, $shorter_extensions = true) {
  $image_info = @getimagesize($filename);
  if (!$image_info || empty($image_info[2])) {
    return false;
  }

  if (!function_exists('image_type_to_extension')) {
   
    function image_type_to_extensiona ($imagetype, $include_dot = true) {
	
      $extensions = array(
        1  => 'gif',
        2  => 'jpeg',
        3  => 'png',
        4  => 'swf',
        5  => 'psd',
        6  => 'bmp',
        7  => 'tiff',
        8  => 'tiff',
        9  => 'jpc',
        10 => 'jp2',
        11 => 'jpf',
        12 => 'jb2',
        13 => 'swc',
        14 => 'aiff',
        15 => 'wbmp',
        16 => 'xbm',
      );

      $imagetype = (int)$imagetype;
      if (!$imagetype || !isset($extensions[$imagetype])) {
        return false;
      }

      return ($include_dot ? '.' : '') . $extensions[$imagetype];
    }
  }

  $extension = image_type_to_extension($image_info[2], $include_dot);
  if (!$extension) {
    return false;
  }

  if ($shorter_extensions) {
    $replacements = array(
      'jpeg' => 'jpg',
      'tiff' => 'tif',
    );
    $extension = strtr($extension, $replacements);
  }
  return $extension;
}
?>