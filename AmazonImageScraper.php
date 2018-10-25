<?php
/**
 * Created by PhpStorm.
 * User: fedo
 * Date: 5/25/2017
 * Time: 3:11 PM
 */

class AmazonImageScraper
{
	/**
	 * @param $asin , string, The item you want photos for
	 * @param $storage_loc , string, a path to the storage location
	 */
	public function getImage($name,$asin, $storage_loc)
	{
		if ($this->asin_is_unique($asin, $storage_loc) && $this->filePathCheck($storage_loc)) {

			$html = $this->curlRequest('https://www.amazon.com/dp/' . $asin);
			@$dom = new DOMDocument;
			libxml_use_internal_errors(true);
			if (!$dom->loadHTML($html)) {
				$errors = null;
				foreach (libxml_get_errors() as $error) {
					$errors .= $error->message . "<br/>";
				}
				print "libxml errors:<br>$errors";
				libxml_clear_errors();
				die();
			}
			$links = $dom->getElementsByTagName('img');
			$count = 0;
			foreach ($links as $link) {

				if (strpos($link->getAttribute('src'), "https://images-na.ssl-images-amazon.com/images/I/") !== false && (strpos($link->getAttribute('src'), "SS40") !== false || strpos($link->getAttribute('src'), "_QL70_.") !== false ) ) {

					$image = ($link->getAttribute('src'));
					echo "$image     ";

					$image = substr($image, 0, -9) . "SL1500_.jpg";
					echo "<a href='$image'>$asin</a><br>";
					$raw = $this->curlRequest($image);
					$fp = fopen('photos\\' . $name . "_$count.jpg", 'wb');
					fwrite($fp, $raw);
					fclose($fp);
					$count++;

				}
			}
		}
	}

	/**
	 * @param $file_path , string, a path to the storage location
	 * @return bool
	 */
	private function filePathCheck($file_path)
	{
		if (!file_exists($file_path)) {
			mkdir($file_path, 0777, true);
		}
		return true;
	}

	/**
	 * @param $asin , string, The item you want photos for
	 * @param $storage_loc , string, a path to the storage location
	 * @return bool
	 */
	private function asinIsUnique($asin, $storage_loc)
	{
		if (file_exists("$storage_loc/$asin" . "_0.jpg")) {
			echo 'We already have photos for this item';
			return false;
		}
		return true;
	}

	/**
	 * @param $link
	 * @return mixed curl results
	 */
	private function curlRequest($link)
	{
		$ch = curl_init($link);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
		curl_setopt($ch, CURLOPT_TIMEOUT, 200);
		curl_setopt($ch, CURLOPT_AUTOREFERER, false);
		curl_setopt($ch, CURLOPT_REFERER, "http://google.com");
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE); // Follows redirect responses.
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$buff = curl_exec($ch);
		curl_close($ch);
		return $buff;
	}
}

