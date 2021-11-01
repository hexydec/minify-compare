<?php
declare(strict_types = 1);
namespace hexydec\minify;

class compareView {

	protected $model;

	public function __construct(compareModel $model) {
		$this->model = $model;
	}

	public function drawCompare(array $urls, bool $cache = true) : string {

		// render the table
		$table = $this->compile([
			'minifiers' => array_keys($this->model->minifiers),
			'urls' => $urls
		], __DIR__.'/templates/table-ajax.php');

		// wrap in a template
		return $this->compile([
			'title' => $this->model->config['title'],
			'table' => $table
		], __DIR__.'/templates/template.php');
	}

	public function drawMinifierOutput(string $minifier, string $url) {
		if (($input = $this->model->fetch($url)) !== false) {
			return $this->model->minify($minifier, $input, $url);
		}
		return null;
	}

	/**
	 * Compiles dynamically generated content into a PHP template
	 *
	 * @param array $content An array containing the values to be compiled
	 * @param string $template The absolute/relative (Relative if $prefix) filepath to the template file
	 * @return string The compiled HTML
	 */
	public static function compile(array $content, string $template) : string { // Compiles an Array with a PHP file
		${'template-66f6181bcb4cff4cd38fbc804a036db6'} = $template; // use a weird name to make sure the variable doesn't get overwritten
		foreach ($content AS ${'key-66f6181bcb4cff4cd38fbc804a036db6'} => ${'value-66f6181bcb4cff4cd38fbc804a036db6'}) {
			$${'key-66f6181bcb4cff4cd38fbc804a036db6'} = ${'value-66f6181bcb4cff4cd38fbc804a036db6'}; // manual extract to allow for vars with dashes
		}
		if (is_array($content)) {
			unset($content); // may have been overwritten
		}
		ob_start();
		require(${'template-66f6181bcb4cff4cd38fbc804a036db6'});
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}
}
