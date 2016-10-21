<?php
namespace main;

/**
 * Main Controller Class
 */
class Controller
{
    /**
     * Default limit constant
     */
    const LIMIT = 20;

    /**
     * Giphy api_key
     */
    const APIKEY = 'dc6zaTOxFJmzC';

    /**
     * Self instance of singleton
     * @var main\Controller
     */
    private static $instance;

    /**
     * Choosen template
     * @var string
     */
    private $template = 'index.phtml';

    /**
     * Data used in template
     * @var array
     */
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
     * Execute selected action method
     * @return void
     */
    public function execute()
    {
        $method = (isset($_POST['method'])) ? $_POST['method'] : 'search';
        $method .= 'Action';

        if (method_exists($this, $method)) {
            $this->$method();
        } else {
            $this->data['meta'] = ['msg' => 'Method doesn\'t Exist'];
        }
        echo $this->twig->render($this->template, $this->data);

    }

    /**
     * Action search
     * @return void
     */
    private function searchAction()
    {
        $giphy = new GiphyApi(self::APIKEY);

        if (isset($_REQUEST['phrase'])) {
            $this->data['phrase'] = $_REQUEST['phrase'];
            $limit = (isset($_REQUEST['limit'])) ? $_REQUEST['limit'] : self::LIMIT;
            $offset = (isset($_GET['page']))
                ? ((int)$_GET['page']-1) * $limit
                : 0;

            $params = [
                'q' => $this->data['phrase'],
                'limit' => $limit,
                'offset' => $offset
            ];
            $gifs = json_decode($giphy->searchGifs($params), true);
            $httpStatus = $giphy->getHttpStatus();
            $this->data['meta'] = $gifs['meta'];
            $this->data['limit'] = $limit;
            if ($httpStatus == 200) {
                $this->prepareGifs($gifs['data']);
                $this->setGifsPagination($gifs['pagination'], $limit);
            }

        } else {
            $httpStatus = 200;
            $this->data['limit'] = self::LIMIT;
            $this->data['first_visit'] = true;
        }
        $this->data['httpStatus'] = $httpStatus;
    }

    /**
     * Prepare Gifs Data
     * @param  array $gifs  Received gifs array from
     * @return [type]       [description]
     */
    private function prepareGifs($gifs)
    {
        if (!empty($gifs)) {
            $rating = new Rating();
            foreach ($gifs as &$gif) {
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
        $this->data['gifs'] = $gifs;
    }

    /**
     * Prepare Gifs Pagination
     */
    private function setGifsPagination($pagination, int $limit)
    {
        $this->data['pagination'] = $pagination;
        $this->data['pagination']['page'] =
            (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
        $this->data['pagination']['url'] =
            'http://'. $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
        $pages = $pagination['total_count']/$limit;
        ($pagination['total_count']%$limit === 0)
            ? $pages -= 1
            : null;
        $this->data['pagination']['pages'] = ceil($pages);
    }

    /**
     * Ajax action
     * @return  response in JSON
     */
    private function ajaxAction()
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
