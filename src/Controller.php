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
        $method = (isset($_POST['method'])) ? $_POST['method'] : 'search';
        if (method_exists($this, $method)) {
            $this->$method();
        } else {
            $this->data['meta'] = ['msg' => 'Method doesn\'t Exist'];
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
            $this->data['input'] = $_POST['phrase'];
            $offset = (isset($_POST['offset'])) ? (int)$_POST['offset'] : 0;

            $params = [
                'q' => $_POST['phrase'],
                'limit' => self::LIMIT,
                'offset' => $offset
            ];

            $gifs = json_decode($giphy->searchGifs($params), true);
            $httpStatus = $giphy->getHttpStatus();
            $this->data['meta'] = $gifs['meta'];
            $this->data['pagination'] = $gifs['meta'];
            if (!empty($gifs['data'])) {
                $rating = new Rating();
                foreach ($gifs['data'] as &$gif) {
                    $rated = $rating->getGifRating($gif['id']);
                    if (!$rated) {
                        $gif['rated']['like'] = 0;
                        $gif['rated']['dislike'] = 0;
                    } else {
                        $gif['rated']['like'] =
                            (!empty($rated['like'])) ? $rated['like'] : 0;
                        $gif['rated']['dislike'] =
                            (!empty($rated['dislike'])) ? $rated['dislike'] : 0;
                    }
                }
            }
            $this->data['gifs'] = $gifs['data'];
        } else {
            $this->data['first'] = true;
        }
        $this->data['httpStatus'] = $httpStatus;
    }

    /**
     * Ajax action
     * @return  response in JSON
     */
    private function ajax()
    {
        header('Content-Type: application/json');
        if (isset($_POST['db']) && isset($_POST['id'])) {
            $db = $_POST['db'];
            $rating = new Rating();
            $response = $rating->$db($_POST['id']);
        }
        die(json_encode($response));
    }
}
