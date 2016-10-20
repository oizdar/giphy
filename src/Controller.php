<?php
namespace main;

class Controller
{
    const LIMIT = 20;

    private static $instance;

    private $template = 'index.phtml';

    private $data = [];

    private function __construct()
    {
        $loader = new \Twig_Loader_Filesystem('templates');
        $this->twig = new \Twig_Environment($loader);
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Execute selected action
     * @return void
     */
    public function execute()
    {
        $method = (isset($_GET['method'])) ? $_GET['method'] : 'search';
        if (method_exists($this, $method)) {
            $this->$method();
        }

        echo $this->twig->render($this->template, $this->data);
    }

    /**
     * Action for SEARCH
     * @return void
     */
    private function search()
    {
        $giphy = new GiphyApi('dc6zaTOxFJmzC');
        $httpStatus = 200;
        if (isset($_POST['phrase'])) {
            $offset = (isset($_POST['offset'])) ? (int)$_POST['offset'] : 0;

            $params = [
                'q' => $_POST['phrase'],
                'limit' => self::LIMIT,
                'offset' => $offset
            ];

            $gifs = json_decode($giphy->searchGifs($params), true);
            $httpStatus = $giphy->getHttpStatus();
            $this->data['gifs'] = $gifs['data'];
            $this->data['meta'] = $gifs['meta'];
            $this->data['pagination'] = $gifs['meta'];
        }
        $this->data['httpStatus'] = $httpStatus;

    }
}
