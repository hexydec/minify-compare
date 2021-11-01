<?php
declare(strict_types = 1);
namespace hexydec\minify;

class compareModel {

	public $minifiers = [];
	public $urls = [];
	public $config = [
		'title' => null,
		'cache' => true,
		'ratelimit' => 2000,
		'validator' => null
	];
	public $action = null;
	public $minifier = null;
	public $errors = [];

	public function __construct(array $minifiers, array $urls, array $config) {
		$this->minifiers = $minifiers;
		$this->urls = $urls;
		$this->config = array_merge($this->config, $config);
	}

	public function fetch(string $url, bool $cache = true) {
		$file = $cache ? \dirname(__DIR__).'/cache/'.\trim(\preg_replace('/[^0-9a-z]++/i', '-', $url), '-').'.cache' : null;
		if ($file && \file_exists($file)) {
			$url = $file;
		}
		$context = \stream_context_create([
			'http' => [
				'headers' => [
					'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
					'Accept-Language: en-GB,en;q=0.5',
					'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:87.0) Gecko/20100101 Firefox/87.0'
				],
				'timeout' => 10
			]
		]);
		$html = \file_get_contents($url, false, $context);
		if ($url !== $file) {
			$dir = \dirname($file);
			if (!\is_dir($dir)) {
				\mkdir($dir, 0755, true);
			}
			\file_put_contents($file, $html);
		}
		return $html ? $html : false;
	}

	public function minify(string $minifier, string $input, string $url) {
		\set_time_limit(30);

		// Setup the environment
		$_SERVER['HTTP_HOST'] = \parse_url($url, PHP_URL_HOST);
		$_SERVER['REQUEST_URI'] = \parse_url($url, PHP_URL_PATH);
		$_SERVER['HTTPS'] = \mb_strpos($url, 'https://') === 0 ? 'on' : '';

		// setup error handler
		\set_error_handler(function (int $type, string $msg, ?string $file = null, ?int $line = null) use ($minifier) {
			if (!isset($this->errors[$minifier])) {
				$this->errors[$minifier] = [];
			}
			$this->errors[$minifier][] = [
				'type' => 'user',
				'code' => $type,
				'msg' => $msg,
				'file' => $file,
				'line' => $line
			];
		});

		// setup exception handler
		\set_exception_handler(function ($e) use ($minifier) {
			if (!isset($this->errors[$minifier])) {
				$this->errors[$minifier] = [];
			}
			$this->errors[$minifier][] = [
				'type' => 'error',
				'code' => $e->getCode(),
				'msg' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine()
			];
		});

		// minify
		return \call_user_func($this->minifiers[$minifier], $input, $url);
	}

	protected function validate(string $code, bool $cache = true) : ?array {
		if (!empty($this->config['validator'])) {

			// pull from cache
			$file = $this->config['cache'] ? \dirname(__DIR__).'/cache/'.\md5($code).'.json' : null;
			if ($cache && \file_exists($file) && ($json = \file_get_contents($file)) !== false && ($data = \json_decode($json, true)) !== null) {
				return $data;
			} else {

				// rate limit
				static $last = 0;
				$now = \microtime(true);
				if ($last + $this->config['ratelimit'] > $now) {
					\usleep(($this->config['ratelimit'] - ($now - $last)) * 1000);
				}
				$last = \microtime(true);

				// run the validator
				if (($errors = $this->config['validator']($code)) !== false) {

					// save to cache
					if ($file) {
						\file_put_contents($file, \json_encode($errors));
					}
					return $errors;
				}
			}
		}
		return null;
	}

	public function compare(string $url, bool $cache = true, int $index = null) {

		// fetch the URL
		if (($input = $this->fetch($url, $cache)) !== false) {
			$stats = [
				'url' => $url,
				'index' => $index,
				'input' => \strlen($input),
				'inputgzip' => \strlen(\gzencode($input)),
				'inputerrors' => $this->validate($input, $cache),
				'minifiers' => [],
				'best' => [],
				'worst' => []
			];

			// test each minifier
			$i = 0;
			foreach ($this->minifiers AS $key => $item) {

				// minify
				$start = \microtime(true);
				$output = $this->minify($key, $input, $url);
				$finish = \microtime(true);

				// calculate stats
				$stat = [
					'index' => $index.'-'.$i,
					'code' => '?action=code&minifier='.\urlencode($key).'&url='.\urlencode($url),
					'errors' => $this->errors[$key] ?? [],
					'output' => \strlen($output),
					'outputgzip' => $output ? \strlen(\gzencode($output)) : 0,
					'time' => $finish - $start,
					'outputerrors' => $this->validate($output, $cache)
				];
				$stat['irregular'] = (\count($stat['outputerrors']) > \count($stats['inputerrors'])) || ($stat['output'] < ($stats['input'] * 0.4));
				$stat['diff'] = $stat['output'] - $stats['input'];
				$stat['ratio'] = 100 - ((100 / $stats['input']) * $stat['output']);
				$stat['diffgzip'] = $stat['outputgzip'] - $stats['inputgzip'];
				$stat['ratiogzip'] = 100 - ((100 / $stats['inputgzip']) * $stat['outputgzip']);
				$stats['minifiers'][$key] = $stat;
				$i++;
			}
			return $stats;
		}
		return false;
	}
}
