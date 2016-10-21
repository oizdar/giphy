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
        if (isset($_REQUEST['phrase'])) {
            $this->data['phrase'] = $_REQUEST['phrase'];
            $offset = (isset($_GET['page']))
                ? ((int)$_GET['page']-1)*self::LIMIT
                : 0;
            $params = [
                'q' => $_REQUEST['phrase'],
                'limit' => self::LIMIT,
                'offset' => $offset
            ];

            $gifs = json_decode($giphy->searchGifs($params), true);
            $httpStatus = $giphy->getHttpStatus();
            $this->data['meta'] = $gifs['meta'];
            $this->data['pagination'] = $gifs['pagination'];
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
                    if (isset($_COOKIE[$gif['id']])) {
                        $gif['rated']['user'] = $_COOKIE[$gif['id']];
                    }
                }
            }
            $this->data['gifs'] = $gifs['data'];

            $this->data['pagination']['page'] =
                (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
            $this->data['pagination']['url'] =
                'http://'. $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
            $pages = $gifs['pagination']['total_count']/self::LIMIT;
            ($gifs['pagination']['total_count']%self::LIMIT === 0)
                ? $pages -= 1
                : null;
            $this->data['pagination']['pages'] = ceil($pages);

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
        if (isset($_POST['action']) && isset($_POST['id'])) {
            $id = $_POST['id'];
            $action = 'add'.ucfirst($_POST['action']);

            $rating = new Rating();
            $remove = null;
            if (isset($_COOKIE[$id])) {
                $cookie = $_COOKIE[$id];
                $method = 'remove' . ucfirst($cookie);
                $remove = $rating->$method($id);
                if ($remove) {
                    unset($_COOKIE[$id]);
                }

            }
            $response = $rating->$action($id);
            $response['removed'] = $remove;
            setcookie($id, $_POST['action']);
        }
        die(json_encode($response));
    }
}
