<?php
declare(strict_types = 1);
namespace hexydec\minify;

class compare {

	protected $model;
	protected $view;

	public function __construct(array $minifiers, array $urls, array $config) {
		$this->model = new compareModel($minifiers, $urls, $config);
		$this->view = new compareView($this->model);

		// get the output from a specific minifier
		if (($_GET['action'] ?? null) === 'code' && isset($this->model->minifiers[$_GET['minifier'] ?? '']) && !empty($_GET['url'])) {
			$this->model->action = 'code';
			$this->model->minifier = $_GET['minifier'];
			$this->model->url = $_GET['url'];

		// run a test
		} elseif (isset($_GET['index'], $urls[$_GET['index']])) {
			if (($data = $this->model->compare($urls[$_GET['index']], $config['cache'], $_GET['index'])) !== false) {
				$json = \json_encode($data);
				\header('Content-type: application/json');
				\header('Content-length: '.\strlen($json));
				\header('Cache-control: no-store');
				exit($json);
			} else {
				\http_response_code(500);
				exit();
			}
		}
	}

	public function drawPage() {
		if ($this->model->action === 'code') {
			if (($code = $this->view->drawMinifierOutput($this->model->minifier, $this->model->url)) === null) {
				trigger_error('The minifier didn\'t output any code', E_USER_WARNING);
			} else {
				header('Content-type: text/plain');
				exit($code);
			}
		} else {
			return $this->view->drawCompare($this->model->urls, $this->model->config['cache']);
		}
	}
}
