<?php

/**
* The MIT License
* http://creativecommons.org/licenses/MIT/
*
* phunction 0.8.16 (http://phunction.sf.net/)
* Copyright (c) 2010 Alix Axel <alix-axel@users.sf.net>
**/

class phunction
{
	public function __construct()
	{
		ob_start();
		set_time_limit(0);
		ignore_user_abort(true);
		date_default_timezone_set('GMT');
		error_reporting(E_ALL | E_STRICT);

		if (headers_sent() === false)
		{
			header('Content-Type: text/html; charset=utf-8');

			if (stripos($_SERVER['HTTP_HOST'], 'www.') === 0)
			{
				ph()->HTTP->Redirect(sprintf('%s://%s', getservbyport($_SERVER['SERVER_PORT'], 'tcp'), substr($_SERVER['HTTP_HOST'], 4) . $_SERVER['REQUEST_URI']), true);
			}

			else if (strlen(session_id()) == 0)
			{
				session_start();
			}
		}

		if (version_compare(PHP_VERSION, '5.3.0', '<') === true)
		{
			set_magic_quotes_runtime(false);
		}

		else if ((get_magic_quotes_gpc() === 1) && (version_compare(PHP_VERSION, '6.0.0', '<') === true))
		{
			$_GET = json_decode(stripslashes(json_encode($_GET, JSON_HEX_APOS | JSON_HEX_QUOT)), true);
			$_POST = json_decode(stripslashes(json_encode($_POST, JSON_HEX_APOS | JSON_HEX_QUOT)), true);
			$_COOKIE = json_decode(stripslashes(json_encode($_COOKIE, JSON_HEX_APOS | JSON_HEX_QUOT)), true);
			$_REQUEST = json_decode(stripslashes(json_encode($_REQUEST, JSON_HEX_APOS | JSON_HEX_QUOT)), true);
		}
	}

	public function __get($key)
	{
		$class = __CLASS__ . '_' . $key;

		if (class_exists($class, false) === true)
		{
			return $this->$key = new $class();
		}

		return false;
	}

	public static function APC($key, $value = null, $ttl = 60)
	{
		if (extension_loaded('apc') === true)
		{
			if (isset($value) === true)
			{
				apc_store($key, $value, intval($ttl));
			}

			return apc_fetch($key);
		}

		return (isset($value) === true) ? $value : false;
	}

	public static function Date($format = 'U', $time = 'now')
	{
		$result = new DateTime($time, new DateTimeZone('GMT'));

		if (is_a($result, 'DateTime') === true)
		{
			foreach (array_filter(array_slice(func_get_args(), 2), 'strtotime') as $argument)
			{
				$result->modify($argument);
			}

			return $result->format($format);
		}

		return false;
	}

	public static function DB($query)
	{
		static $db = null;
		static $result = array();

		if (is_null($db) === true)
		{
			if (preg_match('~^(?:mysql|pgsql):~', $query) > 0)
			{
				$db = new PDO(preg_replace('~^(mysql|pgsql):(?:/{2})?([-.\w]+)(?::(\d+))?/(\w+)/?$~', '$1:host=$2;port=$3;dbname=$4', $query), func_get_arg(1), func_get_arg(2));

				if (preg_match('~^mysql:~', $query) > 0)
				{
					self::DB('SET time_zone = ?;', 'GMT');
					self::DB('SET NAMES ? COLLATE ?;', 'utf8', 'utf8_unicode_ci');
				}
			}

			else if (preg_match('~^(?:sqlite|firebird):~', $query) > 0)
			{
				$db = new PDO(preg_replace('~^(sqlite|firebird):(?:/{2})?(.+)$~', '$1:$2', $query));
			}
		}

		else if (is_a($db, 'PDO') === true)
		{
			if (isset($query) === true)
			{
				$hash = md5($query);

				if (empty($result[$hash]) === true)
				{
					$result[$hash] = $db->prepare($query);
				}

				if (is_a($result[$hash], 'PDOStatement') === true)
				{
					if ($result[$hash]->execute(array_slice(func_get_args(), 1)) === true)
					{
						if (preg_match('~^(?:INSERT|REPLACE)~i', $query) > 0)
						{
							return $db->lastInsertId();
						}

						else if (preg_match('~^(?:UPDATE|DELETE)~i', $query) > 0)
						{
							return $result[$hash]->rowCount();
						}

						else if (preg_match('~^(?:SELECT|EXPLAIN)~i', $query) > 0)
						{
							return $result[$hash]->fetchAll(PDO::FETCH_ASSOC);
						}

						return true;
					}
				}

				return false;
			}
		}

		return $db;
	}

	public static function Dump()
	{
		foreach (func_get_args() as $argument)
		{
			echo '<pre style="background: #F2F3F4; padding: 10px; text-align: left;">' . "\n";

			if ((is_array($argument) === true) || (is_object($argument) === true))
			{
				echo htmlspecialchars(print_r($argument, true), ENT_QUOTES, 'UTF-8');
			}

			else
			{
				echo htmlspecialchars(var_export($argument, true), ENT_QUOTES, 'UTF-8');
			}

			echo '</pre>' . "\n";
		}
	}

	public static function Flatten($array)
	{
		$result = array();

		if (is_array($array) === true)
		{
			foreach (new RecursiveIteratorIterator(new RecursiveArrayIterator($array)) as $value)
			{
				$result[] = $value;
			}
		}

		return $result;
	}

	public static function Input($input, $filters = null, $callbacks = null, $required = true)
	{
		if (array_key_exists($input, $_REQUEST) === true)
		{
			$result = array_map('trim', (array) $_REQUEST[$input]);

			if (($required === true) || (count($result) > 0))
			{
				foreach (array_filter(explode('|', $filters), 'is_callable') as $filter)
				{
					if (in_array(false, array_map($filter, $result)) === true)
					{
						return false;
					}
				}
			}

			foreach (array_filter(explode('|', $callbacks), 'is_callable') as $callback)
			{
				$result = array_map($callback, $result);
			}

			return (is_array($_REQUEST[$input]) === true) ? $result : $result[0];
		}

		return ($required === true) ? false : null;
	}

	public static function Object($object)
	{
		static $result = array();

		if (class_exists($object, false) === true)
		{
			if (array_key_exists($object, $result) === false)
			{
				$result[$object] = new $object();
			}

			return $result[$object];
		}

		else if (is_file($object . '.php') === true)
		{
			$class = basename($object);

			if (array_key_exists($class, $result) === false)
			{
				if (class_exists($class, false) === false)
				{
					require($object . '.php');
				}

				$result[$class] = new $class();
			}

			return $result[$class];
		}

		return false;
	}

	public static function Route($route, $class = null, $function = null, $method = null)
	{
		static $result = null;

		if (strlen($method) * strcasecmp($method, $_SERVER['REQUEST_METHOD']) == 0)
		{
			$matches = array();

			if (is_null($result) === true)
			{
				$result = rtrim(preg_replace('~/+~', '/', substr($_SERVER['PHP_SELF'], strlen($_SERVER['SCRIPT_NAME']))), '/');
			}

			if (preg_match('~' . rtrim(str_replace(array(':any', ':num'), array('[^/]+', '[0-9]+'), $route), '/') . '$~i', $result, $matches) > 0)
			{
				if (empty($class) === true)
				{
					if (empty($function) === true)
					{
						return true;
					}

					exit(call_user_func_array($function, array_slice($matches, 1)));
				}

				exit(call_user_func_array(array(self::Object($class), $function), array_slice($matches, 1)));
			}
		}

		return false;
	}

	public static function Segment($key, $default = false)
	{
		static $result = null;

		if (is_null($result) === true)
		{
			$result = array_values(array_filter(explode('/', substr($_SERVER['PHP_SELF'], strlen($_SERVER['SCRIPT_NAME']))), 'strlen'));
		}

		return self::Value($result, (is_int($key) === true) ? $key : (array_search($key, $result) + 1), $default);
	}

	public static function Sort($array, $reverse = false)
	{
		natcasesort($array);

		if ($reverse === true)
		{
			$array = array_reverse($array, true);
		}

		return $array;
	}

	public static function Value($data, $key, $default = false)
	{
		if ((is_array($data) === true) || (is_object($data) === true))
		{
			foreach ((array) $key as $value)
			{
				if ((array_key_exists($value, $data) === false) && (property_exists($data, $value) === false))
				{
					return $default;
				}

				$data = (is_array($data) === true) ? $data[$value] : $data->$value;
			}

			return $data;
		}

		return $default;
	}

	public static function View($view, $data = null, $return = false)
	{
		if (is_file($view . '.php') === true)
		{
			extract((array) $data);

			if ($return === true)
			{
				if (ob_start() === true)
				{
					require($view . '.php');
				}

				return ob_get_clean();
			}

			require($view . '.php');
		}
	}
}

class phunction_Date extends phunction
{
	public static function Age($date)
	{
		return intval(substr(parent::Date('Ymd') - parent::Date('Ymd', $date), 0, -4));
	}

	public static function Relative($date)
	{
		$date = parent::Date() - parent::Date('U', $date);

		if ($date != 0)
		{
			$units = array
			(
				31536000 => 'year',
				2592000 => 'month',
				604800 => 'week',
				86400 => 'day',
				3600 => 'hour',
				60 => 'minute',
				1 => 'second',
			);

			foreach ($units as $key => $value)
			{
				$result = floor(abs($date) / $key);

				if ($result >= 1)
				{
					return sprintf('%u %s%s %s', $result, $value, ($result == 1) ? '' : 's', ($date >= 1) ? 'ago' : 'from now');
				}
			}
		}

		return 'just now';
	}

	public static function Zodiac($date)
	{
		$date = parent::Date('md', $date);

		if ($date !== false)
		{
			$zodiac = array
			(
				'1222' => 'Capricorn',
				'1122' => 'Sagittarius',
				'1023' => 'Scorpio',
				'0923' => 'Libra',
				'0823' => 'Virgo',
				'0723' => 'Leo',
				'0621' => 'Cancer',
				'0521' => 'Gemini',
				'0421' => 'Taurus',
				'0321' => 'Aries',
				'0220' => 'Pisces',
				'0121' => 'Aquarius',
				'0101' => 'Capricorn',
			);

			foreach ($zodiac as $key => $value)
			{
				if ($key <= $date)
				{
					return $value;
				}
			}
		}

		return false;
	}
}

class phunction_Disk extends phunction
{
	public static function Chmod($path, $chmod = null)
	{
		if (file_exists($path) === true)
		{
			if (is_null($chmod) === true)
			{
				$chmod = (is_file($path) === true) ? 644 : 755;

				if (in_array(get_current_user(), explode('|', 'apache|httpd|nobody|system|webdaemon|www|www-data')) === true)
				{
					$chmod += 22;
				}
			}

			return chmod($path, octdec(intval($chmod)));
		}

		return false;
	}

	public static function Download($path, $speed = null)
	{
		if (is_file($path) === true)
		{
			set_time_limit(0);

			while (ob_get_level() > 0)
			{
				ob_end_clean();
			}

			$size = sprintf('%u', filesize($path));
			$speed = (is_int($speed) === true) ? $size : intval($speed) * 1024;

			header('Expires: 0');
			header('Pragma: public');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Content-Type: application/octet-stream');
			header('Content-Length: ' . $size);
			header('Content-Disposition: attachment; filename="' . basename($path) . '"');
			header('Content-Transfer-Encoding: binary');

			for ($i = 0; $i <= $size; $i = $i + $speed)
			{
				ph()->HTTP->Flush(file_get_contents($path, false, null, $i, $speed));
				ph()->HTTP->Sleep(1);
			}

			exit();
		}

		return false;
	}

	public static function File($path, $content = null, $append = true, $chmod = null, $ttl = null)
	{
		if (isset($content) === true)
		{
			if (file_put_contents($path, $content, ($append === true) ? FILE_APPEND : LOCK_EX) !== false)
			{
				return self::Chmod($path, $chmod);
			}
		}

		else if (is_file($path) === true)
		{
			if ((empty($ttl) === true) || (parent::Date('U', 'now', '-' . filemtime($path) . ' seconds') <= intval($ttl)))
			{
				return file_get_contents($path);
			}

			return unlink($path);
		}

		return false;
	}

	public static function Image($source, $crop = null, $scale = null, $merge = null, $sharpen = true, $destination = null)
	{
		$source = @ImageCreateFromString(@file_get_contents($source));

		if (is_resource($source) === true)
		{
			$size = array(ImageSX($source), ImageSY($source));
			$crop = array_values(array_filter(explode('/', $crop), 'is_numeric'));
			$scale = array_values(array_filter(explode('*', $scale), 'is_numeric'));

			if (count($crop) == 2)
			{
				$crop = array($size[0] / $size[1], $crop[0] / $crop[1]);

				if ($crop[0] > $crop[1])
				{
					$size[0] = round($size[1] * $crop[1]);
				}

				else if ($crop[0] < $crop[1])
				{
					$size[1] = round($size[0] / $crop[1]);
				}

				$crop = array(ImageSX($source) - $size[0], ImageSY($source) - $size[1]);
			}

			else
			{
				$crop = array(0, 0);
			}

			if (count($scale) >= 1)
			{
				if (empty($scale[0]) === true)
				{
					$scale[0] = round($scale[1] * $size[0] / $size[1]);
				}

				else if (empty($scale[1]) === true)
				{
					$scale[1] = round($scale[0] * $size[1] / $size[0]);
				}
			}

			else
			{
				$scale = array($size[0], $size[1]);
			}

			$image = ImageCreateTrueColor($scale[0], $scale[1]);

			if (is_resource($image) === true)
			{
				ImageFill($image, 0, 0, IMG_COLOR_TRANSPARENT);
				ImageSaveAlpha($image, true);
				ImageAlphaBlending($image, true);

				if (ImageCopyResampled($image, $source, 0, 0, round($crop[0] / 2), round($crop[1] / 2), $scale[0], $scale[1], $size[0], $size[1]) === true)
				{
					if ($sharpen === true)
					{
						$matrix = array(-1, -1, -1, -1, 16, -1, -1, -1, -1);

						if (function_exists('ImageConvolution') === true)
						{
							ImageConvolution($image, array_chunk($matrix, 3), array_sum($matrix), 0);
						}
					}

					if (isset($merge) === true)
					{
						$merge = @ImageCreateFromString(@file_get_contents($merge));

						if (is_resource($merge) === true)
						{
							ImageCopy($image, $merge, round(0.95 * $scale[0] - ImageSX($merge)), round(0.95 * $scale[1] - ImageSY($merge)), 0, 0, ImageSX($merge), ImageSY($merge));
						}
					}

					if (isset($destination) === true)
					{
						$result = false;

						if (preg_match('~gif$~i', $destination) > 0)
						{
							$destination = preg_replace('~^[.]?gif$~i', '', $destination);

							if (empty($destination) === true)
							{
								header('Content-Type: image/gif');
							}

							$result = ImageGIF($image, $destination);
						}

						else if (preg_match('~png$~i', $destination) > 0)
						{
							$destination = preg_replace('~^[.]?png$~i', '', $destination);

							if (empty($destination) === true)
							{
								header('Content-Type: image/png');
							}

							$result = ImagePNG($image, $destination, 9);
						}

						else if (preg_match('~jpe?g$~i', $destination) > 0)
						{
							$destination = preg_replace('~^[.]?jpe?g$~i', '', $destination);

							if (empty($destination) === true)
							{
								header('Content-Type: image/jpeg');
							}

							$result = ImageJPEG($image, $destination, 90);
						}

						return (empty($destination) === true) ? $result : self::Chmod($destination);
					}
				}
			}
		}

		return false;
	}

	public static function Mime($path, $magic = null)
	{
		$path = self::Path($path);

		if ($path !== false)
		{
			if (function_exists('finfo_open') === true)
			{
				$finfo = finfo_open(FILEINFO_MIME_TYPE, $magic);

				if (is_resource($finfo) === true)
				{
					$result = finfo_file($finfo, $path);
				}

				finfo_close($finfo);
			}

			else if (function_exists('mime_content_type') === true)
			{
				$result = mime_content_type($path);
			}

			else if (function_exists('exif_imagetype') === true)
			{
				$result = image_type_to_mime_type(exif_imagetype($path));
			}

			return preg_replace('~^(.+);.+$~', '$1', $result);
		}

		return false;
	}

	public static function Path($path)
	{
		if (file_exists($path) === true)
		{
			return rtrim(str_replace('\\', '/', realpath($path)), '/') . (is_dir($path) ? '/' : '');
		}

		return false;
	}

	public static function Size($path, $recursive = true)
	{
		$result = 0;

		if (is_dir($path) === true)
		{
			$path = self::Path($path);
			$files = array_diff(scandir($path), array('.', '..'));

			foreach ($files as $file)
			{
				if (is_dir($path . $file) === true)
				{
					$result += ($recursive === true) ? self::Size($path . $file, $recursive) : 0;
				}

				else if (is_file($path . $file) === true)
				{
					$result += sprintf('%u', filesize($path . $file));
				}
			}
		}

		else if (is_file($path) === true)
		{
			$result += sprintf('%u', filesize($path));
		}

		return $result;
	}

	public static function Upload($source, $destination, $chmod = null)
	{
		$result = array();
		$destination = self::Path($destination);

		if ((is_dir($destination) === true) && (array_key_exists($source, $_FILES) === true))
		{
			if (count($_FILES[$source], COUNT_RECURSIVE) == 5)
			{
				foreach ($_FILES[$source] as $key => $value)
				{
					$_FILES[$source][$key] = array($value);
				}
			}

			foreach (array_map('basename', $_FILES[$source]['name']) as $key => $value)
			{
				$result[$value] = false;

				if ($_FILES[$source]['error'][$key] == UPLOAD_ERR_OK)
				{
					$file = ph()->Text->Slug($value, '_', '.');

					if (file_exists($destination . $file) === true)
					{
						$file = substr_replace($file, '_' . md5_file($_FILES[$source]['tmp_name'][$key]), strrpos($value, '.'), 0);
					}

					if (move_uploaded_file($_FILES[$source]['tmp_name'][$key], $destination . $file) === true)
					{
						if (self::Chmod($destination . $file, $chmod) === true)
						{
							$result[$value] = $destination . $file;
						}
					}
				}
			}
		}

		return $result;
	}

	public static function Zip($source, $destination, $chmod = null)
	{
		if (extension_loaded('zip') === true)
		{
			$source = self::Path($source);

			if ($source !== false)
			{
				$zip = new ZipArchive();

				if ($zip->open($source) === true)
				{
					$zip->extractTo($destination);
				}

				else if ($zip->open($destination, ZIPARCHIVE::CREATE) === true)
				{
					if (is_dir($source) === true)
					{
						$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

						foreach ($files as $file)
						{
							$file = self::Path($file);

							if (is_dir($file) === true)
							{
								$zip->addEmptyDir(str_replace($source, '', $file));
							}

							else if (is_file($file) === true)
							{
								$zip->addFromString(str_replace($source, '', $file), self::File($file));
							}
						}
					}

					else if (is_file($source) === true)
					{
						$zip->addFromString(basename($source), self::File($source));
					}
				}

				if ($zip->close() === true)
				{
					return self::Chmod($destination, $chmod);
				}
			}
		}

		return false;
	}
}

class phunction_HTTP extends phunction
{
	public static function Cookie($key, $value = null, $expire = null)
	{
		if (isset($value) === true)
		{
			return (headers_sent() === true) ? false : setcookie($key, $value, intval($expire), '/');
		}

		return parent::Value($_COOKIE, $key);
	}

	public static function FirePHP($message, $label = null, $type = 'LOG')
	{
		static $i = 0;

		if (headers_sent() === false)
		{
			$type = (in_array($type, array('LOG', 'INFO', 'WARN', 'ERROR')) === true) ? $type : 'LOG';

			if (($_SERVER['HTTP_HOST'] == 'localhost') && (strpos($_SERVER['HTTP_USER_AGENT'], 'FirePHP') !== false))
			{
				$message = json_encode(array(array('Type' => $type, 'Label' => $label), $message));

				if ($i == 0)
				{
					header('X-Wf-Protocol-1: http://meta.wildfirehq.org/Protocol/JsonStream/0.2');
					header('X-Wf-1-Plugin-1: http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.3');
					header('X-Wf-1-Structure-1: http://meta.firephp.org/Wildfire/Structure/FirePHP/FirebugConsole/0.1');
				}

				header('X-Wf-1-1-1-' . ++$i . ': ' . strlen($message) . '|' . $message . '|');
			}
		}
	}

	public static function Flush($buffer = null)
	{
		echo $buffer;

		while (ob_get_level() > 0)
		{
			ob_end_flush();
		}

		flush();
	}

	public static function IP($ip = null, $proxy = false)
	{
		if (isset($ip) === true)
		{
			return (ph()->Is->IP($ip) === true) ? $ip : self::IP(null, $proxy);
		}

		else if ($proxy === true)
		{
			foreach (array('HTTP_CLIENT_IP', 'HTTP_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_X_FORWARDED', 'HTTP_X_FORWARDED_FOR') as $key)
			{
				foreach (array_map('trim', explode(',', parent::Value($_SERVER, $key))) as $value)
				{
					if (ph()->Is->IP($value) === true)
					{
						return $value;
					}
				}
			}
		}

		return parent::Value($_SERVER, 'REMOTE_ADDR', '127.0.0.1');
	}

	public static function Method()
	{
		return (strcasecmp('XMLHttpRequest', parent::Value($_SERVER, 'HTTP_X_REQUESTED_WITH')) === 0) ? 'AJAX' : strtoupper(parent::Value($_SERVER, 'REQUEST_METHOD', 'GET'));
	}

	public static function Redirect($url, $permanent = false)
	{
		if (headers_sent() === false)
		{
			header('Location: ' . $url, true, ($permanent === true) ? 301 : 302);
		}

		exit();
	}

	public static function Sleep($time = 1)
	{
		return usleep(intval(floatval($time) * 1000000));
	}
}

class phunction_Is extends phunction
{
	public static function Email($value)
	{
		return (bool) filter_var($value, FILTER_VALIDATE_EMAIL);
	}

	public static function Integer($value, $minimum = null, $maximum = null)
	{
		return (bool) filter_var($value, FILTER_VALIDATE_INT, array('options' => array_filter(array('min_range' => $minimum, 'max_range' => $maximum), 'strlen')));
	}

	public static function IP($value)
	{
		return (bool) filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
	}

	public static function Set($value)
	{
		return (bool) filter_var($value, FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => '~[[:graph:]]~')));
	}

	public static function URL($value)
	{
		return (bool) filter_var($value, FILTER_VALIDATE_URL);
	}
}

class phunction_Math extends phunction
{
	public static function Average()
	{
		$result = 0;
		$arguments = parent::Flatten(func_get_args());

		foreach ($arguments as $argument)
		{
			$result += $argument;
		}

		return ($result / max(1, count($arguments)));
	}

	public static function Benchmark($function, $arguments = null, $iterations = 10000)
	{
		if (is_callable($function) === true)
		{
			$result = microtime(true);

			for ($i = 1; $i <= $iterations; ++$i)
			{
				call_user_func_array($function, (array) $arguments);
			}

			return self::Round(microtime(true) - $result, 8);
		}

		return false;
	}

	public static function Chance($chance, $universe = 100)
	{
		return ($chance >= mt_rand(1, $universe)) ? true : false;
	}

	public static function Checksum($string, $encode = true)
	{
		if ($encode === true)
		{
			$result = 0;
			$string = str_split($string);

			foreach ($string as $value)
			{
				$result = ($result + ord($value) - 48) * 10 % 97;
			}

			return implode('', $string) . sprintf('%02u', (98 - $result * 10 % 97) % 97);
		}

		else if ($string == self::Checksum(substr($string, 0, -2), true))
		{
			return substr($string, 0, -2);
		}

		return false;
	}

	public static function Deviation()
	{
		$result = self::Average(func_get_args());
		$arguments = parent::Flatten(func_get_args());

		foreach ($arguments as $key => $value)
		{
			$arguments[$key] = pow($value - $result, 2);
		}

		return sqrt(self::Average($arguments));
	}

	public static function Enum($id)
	{
		static $enum = array();

		if (func_num_args() > 1)
		{
			$result = 0;

			if (empty($enum[$id]) === true)
			{
				$enum[$id] = array();
			}

			foreach (array_unique(array_slice(func_get_args(), 1)) as $argument)
			{
				if (empty($enum[$id][$argument]) === true)
				{
					$enum[$id][$argument] = pow(2, count($enum[$id]));
				}

				$result += $enum[$id][$argument];
			}

			return $result;
		}

		return false;
	}

	public static function ifMB($entity, $reference, $amount = 0.00)
	{
		$stack = 923;
		$weights = array(62, 45, 53, 15, 50, 5, 49, 34, 81, 76, 27, 90, 9, 30, 3);
		$argument = sprintf('%03u%04u%08u', $entity, $reference % 10000, round($amount * 100));

		foreach (str_split($argument) as $key => $value)
		{
			$stack += $value * $weights[$key];
		}

		return array
		(
			'entity' => '10559',
			'reference' => sprintf('%03u%04u%02u', $entity, $reference % 10000, 98 - ($stack % 97)),
			'amount' => number_format($amount, 2, '.', ''),
		);
	}

	public static function Prime($number)
	{
		if (function_exists('gmp_prob_prime') === true)
		{
			return (gmp_prob_prime(abs($number)) > 0) ? true : false;
		}

		return (preg_match('~^1?$|^(11+?)\1+$~', str_repeat('1', abs($number))) + preg_last_error() === 0) ? true : false;
	}

	public static function Probability($data, $number = 1)
	{
		$result = array();

		if (is_array($data) === true)
		{
			$data = array_map('abs', $data);
			$number = min(max(1, abs($number)), count($data));

			while ($number-- > 0)
			{
				$chance = 0;
				$probability = mt_rand(1, array_sum($data));

				foreach ($data as $key => $value)
				{
					$chance += $value;

					if ($chance >= $probability)
					{
						$result[] = $key;

						if (array_key_exists($key, $data) === true)
						{
							unset($data[$key]);
						}

						break;
					}
				}
			}
		}

		return $result;
	}

	public static function Round($number, $precision = 0)
	{
		return number_format($number, intval($precision), '.', '');
	}
}

class phunction_Net extends phunction
{
	public static function Captcha($code = null, $background = null)
	{
		if (strlen(session_id()) > 0)
		{
			if (is_null($code) === true)
			{
				$result = simplexml_load_string(self::CURL('http://services.sapo.pt/Captcha/Get/'));

				if (is_a($result, 'SimpleXMLElement') === true)
				{
					$_SESSION['ph_captcha'] = strval($result->code);

					if ($result->msg == 'ok')
					{
						$result = strval($result->id);
						$background = ltrim($background, '#');

						if (strlen($background) > 0)
						{
							$result .= sprintf('&background=%s', $background);

							if (hexdec($background) < 0x7FFFFF)
							{
								$result .= sprintf('&textcolor=%s', 'ffffff');
							}
						}

						return sprintf('http://services.sapo.pt/Captcha/Show/?id=%s', strtolower($result));
					}
				}
			}

			return (strcasecmp(trim($code), parent::Value($_SESSION, 'ph_captcha')) === 0);
		}

		return false;
	}

	public static function CURL($url, $data = null, $method = 'GET', $options = array())
	{
		$result = false;

		if ((extension_loaded('curl') === true) && (ph()->Is->URL($url) === true))
		{
			$curl = curl_init($url);

			if (is_resource($curl) === true)
			{
				curl_setopt($curl, CURLOPT_FAILONERROR, true);
				curl_setopt($curl, CURLOPT_AUTOREFERER, true);
				curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

				if (preg_match('~^(?:GET|HEAD)$~i', $method) > 0)
				{
					curl_setopt($curl, CURLOPT_HTTPGET, true);

					if (preg_match('~^(?:HEAD)$~i', $method) > 0)
					{
						curl_setopt($curl, CURLOPT_NOBODY, true);
						curl_setopt($curl, CURLOPT_HEADER, true);
					}
				}

				else if (preg_match('~^(?:POST)$~i', $method) > 0)
				{
					curl_setopt($curl, CURLOPT_POST, true);

					if ((is_array($data) === true) && (preg_match('~"[^"]+":"@[^"]+"~', json_encode($data)) === 0))
					{
						$data = http_build_query($data, '', '&');
					}

					curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
				}

				else if (preg_match('~^(?:PUT|DELETE)$~i', $method) > 0)
				{
					curl_setopt($curl, CURLOPT_CUSTOMREQUEST, strtoupper($method));

					if (preg_match('~^(?:PUT)$~i', $method) > 0)
					{
						curl_setopt($curl, CURLOPT_POSTFIELDS, (is_array($data) === true) ? json_encode($data) : $data);
					}
				}

				if (array_key_exists('HTTP_USER_AGENT', $_SERVER) === true)
				{
					curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
				}

				if (is_array($options) === true)
				{
					curl_setopt_array($curl, $options);
				}

				for ($i = 1; $i <= 3; ++$i)
				{
					$result = curl_exec($curl);

					if (($i == 3) || ($result !== false))
					{
						break;
					}

					usleep(pow(2, $i - 2) * 1000000);
				}

				curl_close($curl);
			}
		}

		return $result;
	}

	public static function Currency($data, $amount = null)
	{
		if (($result = parent::APC(__METHOD__ . ':' . $data)) === false)
		{
			$result = self::CURL('http://www.webservicex.net/CurrencyConvertor.asmx/ConversionRate', array_combine(array('ToCurrency', 'FromCurrency'), explode('|', $data)), 'POST');

			if ($result !== false)
			{
				$result = parent::APC(__METHOD__ . ':' . $data, floatval(self::XML($result, '//double', 0)), 3600);
			}
		}

		if ((is_numeric($result) === true) && (is_numeric($amount) === true))
		{
			$result *= floatval($amount);
		}

		return $result;
	}

	public static function Email($to, $from, $subject, $message, $cc = null, $bcc = null, $attachments = null, $smtp = null)
	{
		$content = array();
		$boundary = sprintf('=%s=', rtrim(base64_encode(uniqid()), '='));

		if (extension_loaded('imap') === true)
		{
			$header = array
			(
				'Date' => parent::Date('r'),
				'Message-ID' => sprintf('<%s@%s>', md5(microtime(true)), $_SERVER['HTTP_HOST']),
				'MIME-Version' => '1.0',
			);

			foreach (array('from', 'to', 'cc', 'bcc') as $email)
			{
				$$email = array_filter(filter_var_array(preg_replace('~\s|[<>]|%0[ab]|[[:cntrl:]]~i', '', (is_array($$email) === true) ? $$email : explode(',', $$email)), FILTER_VALIDATE_EMAIL));

				if (count($$email) > 0)
				{
					$header[ucfirst($email)] = array();

					foreach ($$email as $key => $value)
					{
						$key = preg_replace('~%0[ab]|[[:cntrl:]]~i', '', $key);
						$value = (is_array($value) === true) ? $value : explode('@', $value);

						if (preg_match('~[^\x20-\x7F]~', $key) > 0)
						{
							$key = '=?UTF-8?B?' . base64_encode($key) . '?=';
						}

						$header[ucfirst($email)][] = imap_rfc822_write_address($value[0], $value[1], preg_replace('~^\d+$~', '', $key));
					}
				}
			}

			if (count($from) * (count($to) + count($cc) + count($bcc)) > 0)
			{
				$header['Sender'] = $header['Reply-To'] = $header['From'][0];
				$header['Subject'] = preg_replace('~%0[ab]|[[:cntrl:]]~i', '', $subject);
				$header['Return-Path'] = sprintf('<%s>', implode('', array_slice($from, 0, 1)));
				$header['Content-Type'] = sprintf('multipart/alternative; boundary="%s"', $boundary);

				foreach (array_fill_keys(array('plain', 'html'), trim(str_replace("\r", '', $message))) as $key => $value)
				{
					if ($key == 'html')
					{
						$value = trim(imap_binary($value));
					}

					else if ($key == 'plain')
					{
						$value = preg_replace('~<br[^>]*>~i', "\n", strip_tags(preg_replace('~.*<body(?:\s[^>]*)?>(.+?)</body>.*~is', '$1', $value), '<a><p><br><li>'));

						if (preg_match('~</?[a-z][^>]*>~i', $value) > 0)
						{
							$value = strip_tags(preg_replace(array('~<a[^>]+?href="(.+?)"[^>]*>(.+?)</a>~is', '~<p[^>]*>(.+?)</p>~is', '~<li[^>]*>(.+?)</li>~is'), array('$2 ($1)', "\n\n$1\n\n", "\n - $1"), $value));
						}

						$value = implode("\n", array_map('imap_8bit', explode("\n", preg_replace('~\n{3,}~', "\n\n", trim($value)))));
					}

					$value = array
					(
						sprintf('Content-Type: text/%s; charset=utf-8', $key), 'Content-Disposition: inline',
						sprintf('Content-Transfer-Encoding: %s', ($key == 'html') ? 'base64' : 'quoted-printable'), '', $value, '',
					);

					$content = array_merge($content, array('--' . $boundary), $value);
				}

				$content[] = '--' . $boundary . '--';

				if (isset($attachments) === true)
				{
					$boundary = str_rot13($boundary);
					$attachments = array_filter((array) $attachments, 'is_file');

					if (count($attachments) > 0)
					{
						array_unshift($content, '--' . $boundary, 'Content-Type: ' . $header['Content-Type'], '');

						foreach ($attachments as $key => $value)
						{
							$key = (is_int($key) === true) ? basename($value) : $key;

							if (preg_match('~[^\x20-\x7F]~', $key) > 0)
							{
								$key = '=?UTF-8?B?' . base64_encode($key) . '?=';
							}

							$value = array
							(
								sprintf('Content-Type: application/octet-stream; name="%s"', $key),	sprintf('Content-Disposition: attachment; filename="%s"', $key),
								'Content-Transfer-Encoding: base64', '', trim(imap_binary(file_get_contents($value))), '',
							);

							$content = array_merge($content, array('--' . $boundary), $value);
						}

						$header['Content-Type'] = sprintf('multipart/mixed; boundary="%s"', $boundary);
						$content[] = '--' . $boundary . '--';
					}
				}

				foreach ($header as $key => $value)
				{
					$value = (is_array($value) === true) ? implode(', ', $value) : $value;
					$header[$key] = iconv_mime_encode($key, $value, array('scheme' => 'Q', 'input-charset' => 'UTF-8', 'output-charset' => 'UTF-8'));

					if ($header[$key] === false)
					{
						$header[$key] = iconv_mime_encode($key, $value, array('scheme' => 'B', 'input-charset' => 'UTF-8', 'output-charset' => 'UTF-8'));
					}

					if (preg_match('~^[\x20-\x7F]*$~', $value) > 0)
					{
						$header[$key] = wordwrap(iconv_mime_decode($header[$key], 0, 'UTF-8'), 76, "\r\n" . ' ', true);
					}
				}

				if (isset($smtp) === true)
				{
					$result = null;
					$stream = stream_socket_client($smtp);

					if (is_resource($stream) === true)
					{
						$data = array('HELO ' . $_SERVER['HTTP_HOST']);
						$result .= substr(ltrim(fread($stream, 8192)), 0, 3);

						if (preg_match('~^220~', $result) > 0)
						{
							$auth = array_slice(func_get_args(), 8, 2);

							if (count($auth) == 2)
							{
								$data = array_merge($data, array('AUTH LOGIN'), array_map('base64_encode', $auth));
							}

							$data[] = sprintf('MAIL FROM: <%s>', implode('', array_slice($from, 0, 1)));

							foreach (array_merge(array_values($to), array_values($cc), array_values($bcc)) as $value)
							{
								$data[] = sprintf('RCPT TO: <%s>', $value);
							}

							$data[] = 'DATA';
							$data[] = implode("\r\n", array_merge(array_diff_key($header, array('Bcc' => true)), array(''), $content, array('.')));
							$data[] = 'QUIT';

							while (preg_match('~^220(?>250(?>(?>334){1,2}(?>235)?)?(?>(?>250){1,}(?>354(?>250)?)?)?)?$~', $result) > 0)
							{
								if (fwrite($stream, array_shift($data) . "\r\n") !== false)
								{
									$result .= substr(ltrim(fread($stream, 8192)), 0, 3);
								}
							}

							if (count($data) > 0)
							{
								if (fwrite($stream, array_pop($data) . "\r\n") !== false)
								{
									$result .= substr(ltrim(fread($stream, 8192)), 0, 3);
								}
							}
						}

						fclose($stream);
					}

					return (preg_match('~221$~', $result) > 0) ? true : false;
				}

				return @mail(null, substr($header['Subject'], 9), implode("\n", $content), implode("\r\n", array_diff_key($header, array('Subject' => true))));
			}
		}

		return false;
	}

	public static function GeoIP($ip = null, $proxy = false)
	{
		$result = self::XML(self::CURL('http://ipinfodb.com/ip_query_country.php?ip=' . ph()->HTTP->IP($ip, $proxy)), '//response/countrycode', 0);

		if ($result !== false)
		{
			return strval($result);
		}

		return false;
	}

	public static function Online()
	{
		return ph()->Is->IP(gethostbyname('google.com'));
	}

	public static function OpenID($url = null, $realm = null, $return = null, $redirect = true)
	{
		$data = array();

		if (parent::Value($_REQUEST, 'openid_mode') !== false)
		{
			if (strcmp('id_res', parent::Value($_REQUEST, 'openid_mode')) === 0)
			{
				$data['openid.ns'] = (array_key_exists('openid_op_endpoint', $_REQUEST) === true) ? 'http://specs.openid.net/auth/2.0' : null;
				$data['openid.sig'] = $_REQUEST['openid_sig'];
				$data['openid.mode'] = 'check_authentication';
				$data['openid.signed'] = $_REQUEST['openid_signed'];
				$data['openid.assoc_handle'] = $_REQUEST['openid_assoc_handle'];

				foreach (explode(',', parent::Value($_REQUEST, 'openid_signed')) as $value)
				{
					$data['openid.' . $value] = $_REQUEST['openid_' . str_replace('.', '_', $value)];
				}

				if (preg_match('~is_valid\s*:\s*true~i', self::CURL(self::OpenID($_REQUEST['openid_identity'], false, false, false), array_filter($data, 'strlen'), 'POST')) > 0)
				{
					return parent::Value($_REQUEST, 'openid_claimed_id', parent::Value($_REQUEST, 'openid_identity'));
				}
			}
		}

		else if (($result = self::CURL($url)) !== false)
		{
			$xml = self::XML($result);
			$server = strval(self::XML($xml, '//xrd/service/uri', 0, self::XML($xml, '//head/link[@rel="openid.server" or @rel="openid2.provider"]/@href', 0)));

			if (ph()->Is->URL($server) === true)
			{
				if ($redirect === true)
				{
					$realm = (isset($realm) === true) ? $realm : sprintf('%s://%s/', getservbyport($_SERVER['SERVER_PORT'], 'tcp'), $_SERVER['HTTP_HOST']);
					$return = (isset($return) === true) ? $return : sprintf('%s://%s', getservbyport($_SERVER['SERVER_PORT'], 'tcp'), $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
					$delegate = (preg_match('~http://specs[.]openid[.]net/auth/2[.]0/server~', $result) > 0) ? 'http://specs.openid.net/auth/2.0/identifier_select' : $url;

					if (preg_match('~rel="openid[.]delegate"|<[^>]*Delegate[^>]*>~i', $result) > 0)
					{
						$delegate = parent::Value(ph()->Text->Regex($result, '<([^>]*)Delegate[^>]*>([^>]+)</\1Delegate>', 1), 0, strval(self::XML($xml, '//head/link[@rel="openid.delegate"]/@href', 0, $delegate)));
					}

					if (preg_match('~rel="openid2[.]provider"|http://specs[.]openid[.]net/auth/2[.]0~i', $result) > 0)
					{
						$data['openid.ns'] = 'http://specs.openid.net/auth/2.0';

						if (preg_match('~rel="openid2[.]local_id"|<(Local|Canonical)ID[^>]*>~i', $result) > 0)
						{
							$delegate = parent::Value(ph()->Text->Regex($result, '<(Local|Canonical)ID[^>]*>([^>]+)</\1ID>', 1), 0, strval(self::XML($xml, '//head/link[@rel="openid2.local_id"]/@href', 0, $delegate)));
						}
					}

					$data['openid.mode'] = 'checkid_setup';
					$data['openid.return_to'] = $return;
					$data['openid.claimed_id'] = $data['openid.identity'] = $delegate;
					$data['openid.' . ((array_key_exists('openid.ns', $data) === true) ? 'realm' : 'trust_root')] = $realm;

					ph()->HTTP->Redirect(sprintf('%s%s%s', $server, (strpos($server, '?') !== false) ? '&' : '?', http_build_query($data, '', '&')));
				}

				return $server;
			}
		}

		return false;
	}

	public static function PayPal($email, $status = 'Completed', $sandbox = false)
	{
		static $result = null;

		if ((is_null($result) === true) && (preg_match('~^(?:.+[.])?paypal[.]com$~', gethostbyaddr($_SERVER['REMOTE_ADDR'])) > 0))
		{
			$result = self::CURL('https://www' . (($sandbox !== true) ? '' : '.sandbox') . '.paypal.com/cgi-bin/webscr/', array_merge(array('cmd' => '_notify-validate'), $_POST), 'POST');
		}

		if (strcmp('VERIFIED', $result) === 0)
		{
			$email = strlen($email) * strcasecmp($email, parent::Value($_POST, 'receiver_email'));
			$status = strlen($status) * strcasecmp($status, parent::Value($_POST, 'payment_status'));

			if (($email == 0) && ($status == 0))
			{
				return (object) $_POST;
			}
		}

		return false;
	}

	public static function XML($xml, $xpath = null, $key = null, $default = false)
	{
		libxml_use_internal_errors(true);

		if (extension_loaded('SimpleXML') === true)
		{
			if (is_string($xml) === true)
			{
				$dom = new DOMDocument();

				if ($dom->loadHTML($xml) === true)
				{
					return self::XML(simplexml_import_dom($dom), $xpath, $key, $default);
				}
			}

			else if (is_a($xml, 'SimpleXMLElement') === true)
			{
				if (isset($xpath) === true)
				{
					$xml = $xml->xpath($xpath);

					if (isset($key) === true)
					{
						$xml = parent::Value($xml, $key, $default);
					}
				}

				return $xml;
			}
		}

		return false;
	}
}

class phunction_Text extends phunction
{
	public static function _($single, $plural = null, $number = null, $domain = null)
	{
		if (extension_loaded('gettext') === true)
		{
			if (isset($domain) === true)
			{
				$domain = str_replace('LC_MESSAGES/', '', $domain);

				if (strpos($domain, '/') !== false)
				{
					$path = preg_replace('', '', $domain);

					if (stripos($domain, 'LC_MESSAGES') !== false)
					{
						$locale = preg_replace('');
					}
				}

				bindtextdomain('myapp', './locale');
				bind_textdomain_codeset('myapp', 'UTF-8');
				textdomain('myapp');
			}

			if ((isset($plural) === true) && (isset($number) === true))
			{
				return ngettext($single, $plural, $number);
			}

			return gettext($single);
		}

		return ((isset($plural) === true) && (abs($number) !== 1)) ? $plural : $single;
	}

	public static function Comify($array, $last = ' and ')
	{
		$array = array_filter(array_unique((array) $array), 'strlen');

		if (count($array) >= 3)
		{
			$array = array(implode(', ', array_slice($array, 0, -1)), implode('', array_slice($array, -1)));
		}

		return implode($last, $array);
	}

	public static function Crypt($string, $key)
	{
		if (extension_loaded('mcrypt') === true)
		{
			$key = md5($key);
			$result = self::Regex($string, '^([0-9a-zA-Z/+]*={0,2})[0-9a-f]{40}$', array(1, 0));

			if (self::Hash($result, $key) == self::Regex($string, '^[0-9a-zA-Z/+]*={0,2}([0-9a-f]{40})$', array(1, 0)))
			{
				$result = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, base64_decode($result), MCRYPT_MODE_CBC, md5($key)), "\0");
			}

			else
			{
				$result = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $string, MCRYPT_MODE_CBC, md5($key)));

				if (self::Regex($result, '^[a-zA-Z0-9/+]*={0,2}$') === true)
				{
					$result .= self::Hash($result, $key);
				}
			}

			return $result;
		}

		return false;
	}

	public static function Cycle()
	{
		static $i = 0;

		if (func_num_args() > 0)
		{
			return func_get_arg($i++ % func_num_args());
		}

		return $i = 0;
	}

	public static function GUID()
	{
		if (function_exists('com_create_guid') === true)
		{
			return trim(com_create_guid(), '{}');
		}

		return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
	}

	public static function Hash($string, $salt = null, $pepper = null)
	{
		return sha1($salt . $string . $pepper);
	}

	public static function Mnemonic($mnemonic)
	{
		$result = null;
		$charset = array(str_split('aeiou'), str_split('bcdfghjklmnpqrstvwxyz'));

		for ($i = 1; $i <= $mnemonic; ++$i)
		{
			$result .= $charset[$i % 2][array_rand($charset[$i % 2])];
		}

		return $result;
	}

	public static function Namify($string)
	{
		$string = trim(preg_replace('~\s*\b(\p{L}+)\b.+\b(\p{L}+)\b\s*~iu', '$1 $2', $string));

		if (preg_match('~^[\x20-\x7F]*$~', $string) > 0)
		{
			return ucwords($string);
		}

		return $string;
	}

	public static function Obfuscate($string)
	{
		return (strlen($string) > 0) ? ('&#' . implode(';&#', array_map('ord', str_split($string))) . ';') : false;
	}

	public static function Regex($string, $pattern, $key = null, $modifiers = null, $flag = PREG_PATTERN_ORDER)
	{
		$matches = array();

		if (preg_match_all('~' . $pattern . '~' . $modifiers, $string, $matches, $flag) > 0)
		{
			if (isset($key) === true)
			{
				return ($key === true) ? $matches : parent::Value($matches, $key);
			}

			return true;
		}

		return false;
	}

	public static function Slug($string, $slug = '-', $extra = null)
	{
		return strtolower(trim(preg_replace('~[^0-9a-z' . preg_quote($extra, '~') . ']+~i', $slug, self::Unaccent($string)), $slug));
	}

	public static function Tag($tag, $content = null, $attributes = array())
	{
		if (is_array($attributes) === true)
		{
			ksort($attributes);

			foreach (array_map('htmlspecialchars', $attributes) as $key => $value)
			{
				if (is_bool($value) === true)
				{
					$value = ($value === true) ? $key : null;
				}

				$attributes[$key] = sprintf(' %s="%s"', $key, $value);
			}
		}

		if (in_array($tag, array('br', 'hr', 'img', 'input', 'link', 'meta')) === true)
		{
			return sprintf('<%s%s />', $tag, implode('', $attributes)) . "\n";
		}

		return sprintf('<%s%s>%s</%s>', $tag, implode('', $attributes), htmlspecialchars($content), $tag) . "\n";
	}

	public static function Truncate($string, $limit, $more = '...')
	{
		$string = trim($string);

		if (strlen(utf8_decode($string)) > $limit)
		{
			return preg_replace('~^(.{1,' . $limit . '}(?<=\S)(?=\s)|.{' . $limit . '}).*$~su', '$1', $string) . $more;
		}

		return $string;
	}

	public static function Unaccent($string)
	{
		return html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8');
	}

	public static function XSS($string)
	{
		return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
	}
}

function ph()
{
	static $result = null;

	if (is_null($result) === true)
	{
		$result = new phunction();
	}

	return $result;
}

?>