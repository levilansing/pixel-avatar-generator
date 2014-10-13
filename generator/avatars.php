<?php

/**
 * Class Avatar
 * a static class for generating avatars from image layers
 */
class Avatar {
	const MAX_SIZE = 512;
	const AVATAR_SIZE = 20;
	private static $layers = ['background', 'skin', 'mouth', 'eyes', 'brow', 'face', 'facewear', 'shirt', 'hair'];
	private static $hairColors = [[240,212,83], [62,33,21], [100,23,15], [143,140,137], [112,83,39], [135,71,0], [129,81,57], [self::AVATAR_SIZE, self::AVATAR_SIZE, self::AVATAR_SIZE]];
	private static $eyeColors = [[1, 101, 59],[66,79,1],[66,66,66],[39,39,39],[0,82,156],[76,44,4],[0,59,108],[0,93,128],[93,105,29]];
	private static $browColor = [99, 54, 32];

	/**
	 * render an avatar as a png straight to the browser
	 * @param int $size
	 * @param string $gender
	 * @param string $id
	 */
	public static function render($size = 400, $gender = null, $id = null) {
		header("Content-type: image/png");
		if ($gender != 'male' && $gender != 'female') {
			$gender = mt_rand(0,1) ? 'male' : 'female';
		}
		$avatar = self::generate($size, $gender, $id);
		imagepng($avatar);
		imagedestroy($avatar);
	}

	/**
	 * generate an avatar from the layers.
	 * @param int $size
	 * @param string $gender
	 * @param null $id
	 * @return resource image
	 */
	public static function generate($size = 400, $gender = 'male', $id = null) {
		$size = max(1,min($size, self::MAX_SIZE));
		if (is_null($id)) {
			$id = rand(0,getrandmax());
		}
		if ($id)
			srand(base_convert(substr(md5($id), 0, 8), 16, 10));

		$layerList = self::getLayerList();
		$imgPath = __DIR__ . DIRECTORY_SEPARATOR . 'layers' . DIRECTORY_SEPARATOR;

		// create a transparent base avatar image
		$avatar = imagecreatetruecolor(self::AVATAR_SIZE,self::AVATAR_SIZE);
		imageSaveAlpha($avatar, true);
		imagefill($avatar, 0, 0, 0x7f << 24);

		// add each layer
		$browColor = self::$browColor;
		$hairColor = self::$hairColors[rand(0, count(self::$hairColors)-1)];
		$eyeColor = self::$eyeColors[rand(0, count(self::$eyeColors)-1)];
		$background = null;
		foreach (self::$layers as $layer) {
			$file = $layerList[$layer][$gender][rand(0, count($layerList[$layer][$gender])-1)];
			$img = imagecreatefrompng($imgPath . $file);
			if ($layer == 'hair' || ($gender=='male' && $layer=='face')) {
				self::recolor($img, $hairColor);
			}
			if ($layer == 'eyes') {
				self::recolor($img, $eyeColor, true);
			}
			if ($layer == 'brow') {
				self::recolor($img, [$browColor[0]+$hairColor[0]/2, $browColor[1]+$hairColor[1]/2, $browColor[2]+$hairColor[2]/2], true);
			}
			if ($layer == 'background') {
				$background = $img;
				continue;
			}
			if (rand(0,1) == 0)
				imageflip($img, IMG_FLIP_HORIZONTAL);
			imagecopy($avatar, $img, 0, 0, 0, 0, self::AVATAR_SIZE, self::AVATAR_SIZE);
			imagedestroy($img);
		}

		// create the output avatar at full size
		$avatarLarge = imagecreatetruecolor($size, $size);

		if (!is_null($background))
			imagecopyresized($avatarLarge, $background, 0, 0, 0, 0, $size, $size, self::AVATAR_SIZE, self::AVATAR_SIZE);

		// add the shadow
		$shadow = self::makeShadowLayer($avatar, 110, $size, $size);
		$offsetX = ceil(.4/self::AVATAR_SIZE*$size);
		$offsetY = ceil(1.3/self::AVATAR_SIZE*$size);
		imagecopy($avatarLarge, $shadow, $offsetX, $offsetY, 0, 0, $size, $size);
		imagedestroy($shadow);

		// add an outline
		$outline = self::makeOutlineLayer($avatar, 0x00222222, .1/self::AVATAR_SIZE*$size+.75, $size, $size);
		imagecopy($avatarLarge, $outline, 0, 0, 0, 0, $size, $size);
		imagedestroy($outline);

		imagecopyresized($avatarLarge, $avatar, 0, 0, 0, 0, $size, $size, self::AVATAR_SIZE, self::AVATAR_SIZE);
		imagedestroy($avatar);
		return $avatarLarge;
	}

	/**
	 * recolor a layer using the alpha channel
	 * @param $img
	 * @param $newColor
	 * @param bool $ignoreWhite
	 */
	private static function recolor($img, $newColor, $ignoreWhite=false) {
		for ($x=0; $x<self::AVATAR_SIZE; $x++) {
			for ($y=0; $y<self::AVATAR_SIZE; $y++) {
				$c = imagecolorsforindex($img, imagecolorat($img, $x, $y));
				if ($c['alpha'] < 127 && (!$ignoreWhite || $c['red'] != 255 || $c['green'] != 255 || $c['blue'] != 255)) {
					imagesetpixel($img, $x, $y, imagecolorallocatealpha($img, $newColor[0], $newColor[1], $newColor[2], $c['alpha']));
				}
			}
		}
	}

	/**
	 * create a semi-transparent black layer to be used as a drop shadow using the alpha chanel
	 * @param $img
	 * @param $alpha
	 * @param $width
	 * @param $height
	 * @return resource
	 */
	private static function makeShadowLayer($img, $alpha, $width, $height) {
		$newImg = imagecreatetruecolor(self::AVATAR_SIZE, self::AVATAR_SIZE);
		imageSaveAlpha($newImg, true);
		imagefill($newImg, 0, 0, 0x7f << 24);
		for ($x=0; $x<self::AVATAR_SIZE; $x++) {
			for ($y=0; $y<self::AVATAR_SIZE; $y++) {
				$c = imagecolorsforindex($img, imagecolorat($img, $x, $y));
				if ($c['alpha'] < 100) {
					imagesetpixel($newImg, $x, $y, ($alpha & 0x7f) << 24);
				}
			}
		}
		$fullSize = imagecreatetruecolor($width, $height);
		imageSaveAlpha($fullSize, true);
		imagefill($fullSize, 0, 0, 0x7f << 24);
		imagecopyresized($fullSize, $newImg, 0, 0, 0, 0, $width, $height, self::AVATAR_SIZE, self::AVATAR_SIZE);
		imagedestroy($newImg);
		return $fullSize;
	}

	/**
	 * draw an outline around the non-transparent pixels
	 * @param $img
	 * @param $color
	 * @param $thickness
	 * @param $width
	 * @param $height
	 * @return resource
	 */
	private static function makeOutlineLayer($img, $color, $thickness, $width, $height) {
		$thickness = (int)$thickness;
		$newImg = imagecreatetruecolor(self::AVATAR_SIZE, self::AVATAR_SIZE);
		imageSaveAlpha($newImg, true);
		imagefill($newImg, 0, 0, 0x7f << 24);
		for ($x=0; $x<self::AVATAR_SIZE; $x++) {
			for ($y=0; $y<self::AVATAR_SIZE; $y++) {
				$c = imagecolorsforindex($img, imagecolorat($img, $x, $y));
				if ($c['alpha'] < 100) {
					imagesetpixel($newImg, $x, $y, $color);
				}
			}
		}
		$fullSize = imagecreatetruecolor($width, $height);
		imageSaveAlpha($fullSize, true);
		imagefill($fullSize, 0, 0, 0x7f << 24);
		imagecopyresized($fullSize, $newImg, $thickness, $thickness, 0, 0, $width, $height, self::AVATAR_SIZE, self::AVATAR_SIZE);
		imagecopy($fullSize, $fullSize, 0, -$thickness*2, 0, 0, $width, $height);
		imagecopy($fullSize, $fullSize, -$thickness*2, 0, 0, 0, $width, $height);
		imagedestroy($newImg);
		return $fullSize;
	}

	/**
	 * generate the list of layer files by layer name and gender
	 * @return array
	 */
	private static function getLayerList() {
		$list = array_fill_keys(self::$layers, null);
		$list = array_map(function() { return ['male' => [], 'female'=>[]]; }, $list);
		foreach (scandir(__DIR__ . DIRECTORY_SEPARATOR . 'layers') as $file) {
			$layer = self::findLayer($file);
			if ($layer) {
				if (self::isForMale($file))
					$list[$layer]['male'][] = $file;
				if (self::isForFemale($file))
					$list[$layer]['female'][] = $file;
			}
		}
		return $list;
	}

	/**
	 * determine the layer name from a file name
	 * @param $fileName
	 * @return null|string
	 */
	private static function findLayer($fileName) {
		$found = '';
		foreach (self::$layers as $layer) {
			if (substr_compare($layer, $fileName, 0, strlen($layer), true) == 0) {
				if (strlen($layer) > strlen($found))
					$found = $layer;
			}
		}
		return $found == '' ? null : $found;
	}

	/**
	 * determine if the file is meant for male avatars
	 * @param $fileName
	 * @return bool
	 */
	private static function isForMale($fileName) {
		return (preg_match('/_f?mf?\.png/', $fileName) > 0 || preg_match('/_[mf]+\.png/', $fileName) == 0);
	}

	/**
	 * determine if the file is meant for female avatars
	 * @param $fileName
	 * @return bool
	 */
	private static function isForFemale($fileName) {
		return (preg_match('/_m?fm?\.png/', $fileName) > 0 || preg_match('/_[mf]+\.png/', $fileName) == 0);
	}
}