<?php
namespace hexydec\minify;

class compare {

	protected $model;
	protected $view;

	public function __construct(array $minifiers, array $config) {
		$this->model = new compareModel($minifiers, $config);
		$this->view = new compareView($this->model);

		if (!isset($_GET['action']) || $_GET['action'] !== 'code') {

		} elseif (!isset($_GET['minifier']) || !in_array($_GET['minifier'], array_keys($this->model->minifiers))) {

		} elseif (!empty($_GET['url'])) {
			$this->model->action = 'code';
			$this->model->minifier = $_GET['minifier'];
			$this->model->url = $_GET['url'];
		}
	}

	public function drawPage($urls, string $selector = null, bool $cache = true) {
		if ($this->model->action === 'code') {
			if (($code = $this->view->drawMinifierOutput($this->model->minifier, $this->model->url)) === null) {
				trigger_error('The minifier didn\'t output any code', E_USER_WARNING);
			} else {
				header('Content-type: text/plain');
				exit($code);
			}
		} elseif (is_string($urls)) {
			if (!$selector) {
				trigger_error('Please specify a selector to scrape the URLs with', E_USER_WARNING);
			} else {
				return $this->view->drawCompareScrape($urls, $selector, $cache);
			}
		} else {
			return $this->view->drawCompare($urls, $cache);
		}
	}
}
