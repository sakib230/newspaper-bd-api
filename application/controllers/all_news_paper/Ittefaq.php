<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Ittefaq extends My_Controller {

    function __construct() {
        parent::__construct();

        define('COMMON_DIV_1', "//div[contains(@class,'menuNewsMore')]");
        define('NATIONAL_URL', "http://www.ittefaq.com.bd/national/");
        define('POLITICS_URL', "http://www.ittefaq.com.bd/politics/");
        define('WHOLECOUNTRY_URL', "http://www.ittefaq.com.bd/wholecountry/");
        define('WORLDNEWS_URL', "http://www.ittefaq.com.bd/world-news/");
        define('SPORTS_URL', "http://www.ittefaq.com.bd/sports/");
        define('ENTERTAINMENT_URL', "http://www.ittefaq.com.bd/entertainment/");
        define('TRADE_URL', "http://www.ittefaq.com.bd/trade/");
        define('COURT_URL', "http://www.ittefaq.com.bd/court/");
    }

    public function index() {
        echo json_encode("Unavailable");
    }

    public function getNewspaperMenu() {
        $menu['is_success'] = "Y";
        $menu['menu_name'] = array('বাংলাদেশ', 'রাজনীতি', 'সারাদেশ', 'বিশ্ব সংবাদ', 'খেলাধুলা', 'বিনোদন', 'বাণিজ্য', 'আদালত');
        echo json_encode($menu, JSON_UNESCAPED_UNICODE);
    }

    public function getNational() {
        $this->mainProcess(NATIONAL_URL, COMMON_DIV_1);
    }

    public function getPolitics() {
        $this->mainProcess(POLITICS_URL, COMMON_DIV_1);
    }

    public function getWholeCountry() {
        $this->mainProcess(WHOLECOUNTRY_URL, COMMON_DIV_1);
    }

    public function getWorldNews() {
        $this->mainProcess(WORLDNEWS_URL, COMMON_DIV_1);
    }

    public function getSports() {
        $this->mainProcess(SPORTS_URL, COMMON_DIV_1);
    }

    public function getEntertainment() {
        $this->mainProcess(ENTERTAINMENT_URL, COMMON_DIV_1);
    }

    public function getTrade() {
        $this->mainProcess(TRADE_URL, COMMON_DIV_1);
    }

    public function getCourt() {
        $this->mainProcess(COURT_URL, COMMON_DIV_1);
    }

    public function getNewsDetails() {
        $newsDetailsUrl = $this->input->post('news_details_url', true);
        $pageSourceHtml = $this->newsPaperPageSource($newsDetailsUrl);
        if (!$pageSourceHtml) {
            echo $this->errorMsg(NO_SERVER_CONNECT);
            die();
        }
        $dom = new DOMDocument();
        $dom->loadHTML(mb_convert_encoding($pageSourceHtml, 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new DOMXPath($dom);
        $newsDetailsObj = $xpath->query("//div[@class='details']");
        $newsDetails = $newsDetailsObj->item(0)->nodeValue;
        if (!$newsDetails) {
            echo $this->errorMsg(NO_DATA_FOUND);
            die();
        }
        $apiResponse = array("is_success" => "Y",
            "news_details" => $newsDetails
        );

        echo json_encode($apiResponse, JSON_UNESCAPED_UNICODE);
    }

    private function mainProcess($pageSourceUrl, $div) {
        $pageNumber = ($this->input->post('page_number', true)) ? (int) $this->input->post('page_number', true) : 1;
        $newsId = ($this->input->post('news_id', true) != 1 ) ? (int) $this->input->post('news_id', true) : 0;

        $pageSourceHtml = $this->newsPaperPageSource($pageSourceUrl . $pageNumber);

        if (!$pageSourceHtml) {
            echo $this->errorMsg(NO_SERVER_CONNECT);
            die();
        }
        $newsQueryArr = array(
            'title' => $div . "//div[@class='headline']",
            'newsUrl' => $div . "//a[@class='more']",
            'imageUrl' => $div . "//img",
            'summary' => $div
        );
        $newsInfoArr = $this->getNewsContent($pageSourceHtml, $newsQueryArr);

        if (!$newsInfoArr) {
            echo $this->errorMsg(NO_DATA_FOUND);
            die();
        }

        $apiResponse = array("is_success" => "Y",
            "news_data" => $newsInfoArr
        );

        echo json_encode($apiResponse, JSON_UNESCAPED_UNICODE);
    }

    private function getNewsContent($html, $newsQueryArr) {
        $dom = new DOMDocument();
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new DOMXPath($dom);
        $newsTitleObj = $xpath->query($newsQueryArr['title']);
        $newsUrlObj = $xpath->query($newsQueryArr['newsUrl']);
        $newsImageObj = $xpath->query($newsQueryArr['imageUrl']);
        $newsSummaryObj = $xpath->query($newsQueryArr['summary']);
        $newsTimeObj = $xpath->query($newsQueryArr['dateTime']);

        $response = array();
        $i = 0;
        foreach ($newsTitleObj as $a) {
            $newsInfo['news_id'] = $i;
            $newsInfo['title'] = $newsTitleObj->item($i)->nodeValue; // news title
            $newsInfo['summary'] = $newsSummaryObj->item($i)->nodeValue;  // summary
            $newsInfo['news_url'] = $newsUrlObj->item($i)->getAttribute('href'); //new url
            $newsInfo['image_url'] = $newsImageObj->item($i)->getAttribute('src');  // image url
            $newsInfo['date_time'] = "";

            $response[] = $newsInfo;
            $i++;
        }
        if ($response) {
            return $response;
        }
        return false;
    }

}
