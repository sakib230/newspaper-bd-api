<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class ProthomAlo extends My_Controller {

    function __construct() {
        parent::__construct();
        // COMMON_DIV_1 --> home , entertainment , northamerica
        // COMMON_DIV_2 --> international , economy , opinion , sports , life-style
        define('COMMON_DIV_1', "//div[contains(@class,'each')and contains(@class, 'col_in')and contains(@class, 'has_image')and contains(@class, 'image_left')and contains(@class, 'content_capability_blog')and contains(@class, 'content_type_article') and contains(@class, 'responsive_image_hide_')]");
        define('COMMON_DIV_2', "//div[contains(@class,'each')and contains(@class, 'col_in')and contains(@class, 'has_image')and contains(@class, 'image_left')and contains(@class, 'content_capability_blog')and contains(@class, 'content_type_article') and contains(@class, 'responsive_image_hide_') and contains(@class, 'm_show_featured_image_as_left') ]");
        define('BANGADESH_DIV', "//div[contains(@class,'each')and contains(@class, 'col_in')and contains(@class, 'has_image')and contains(@class, 'image_top')and contains(@class, 'content_capability_blog')and contains(@class, 'content_type_article') and contains(@class, 'responsive_image_hide_') and contains(@class, 'm_show_image_as_top') ]");
        define('HOME_URL', "http://www.prothom-alo.com/home/featured?page=");
        define('BANGLADESH_URL', "http://www.prothom-alo.com/bangladesh/article?page=");
        define('INTERNATIONAL_URL', "http://www.prothom-alo.com/international/article?page=");
        define('ECONOMY_URL', "http://www.prothom-alo.com/economy/article?page=");
        define('OPINION_URL', "http://www.prothom-alo.com/opinion/article?page=");
        define('SPORTS_URL', "http://www.prothom-alo.com/sports/article?page=");
        define('ENTERTAINMENT_URL', "http://www.prothom-alo.com/entertainment/article?page=");
        define('LIFESTYLE_URL', "http://www.prothom-alo.com/life-style/article?page=");
        define('NORTHAMERICA_URL', "http://www.prothom-alo.com/northamerica/article?page=");
    }

    public function index() {
        echo json_encode("Unavailable");
    }

    public function getNewspaperMenu() {
        $menu['is_success'] = "Y";
        $menu['menu_name'] = array('বাংলাদেশ', 'আন্তর্জাতিক', 'অর্থনীতি', 'মতামত', 'খেলা', 'বিনোদন', 'জীবনযাপন', 'উত্তর আমেরিকা');
        echo json_encode($menu, JSON_UNESCAPED_UNICODE);
    }

    public function getHome() {
        $this->mainProcess(HOME_URL, COMMON_DIV_1);
    }

    public function getBangladesh() {
        $this->mainProcess(BANGLADESH_URL, BANGADESH_DIV);
    }

    public function getInternational() {
        $this->mainProcess(INTERNATIONAL_URL, COMMON_DIV_2);
    }

    public function getEconomy() {
        $this->mainProcess(ECONOMY_URL, COMMON_DIV_2);
    }

    public function getOpinion() {
        $this->mainProcess(OPINION_URL, COMMON_DIV_2);
    }

    public function getSports() {
        $this->mainProcess(SPORTS_URL, COMMON_DIV_2);
    }

    public function getEntertainment() {
        $this->mainProcess(ENTERTAINMENT_URL, COMMON_DIV_1);
    }

    public function getLifestyle() {
        $this->mainProcess(LIFESTYLE_URL, COMMON_DIV_2);
    }

    public function getNorthAmerica() {
        $this->mainProcess(NORTHAMERICA_URL, COMMON_DIV_1);
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
        $newsDetailsObj = $xpath->query("//div[@class='viewport']");
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
        $newsId = ($this->input->post('news_id', true)) ? (int) $this->input->post('news_id', true) : "";

        $pageSourceHtml = $this->newsPaperPageSource($pageSourceUrl . $pageNumber);

        if (!$pageSourceHtml) {
            echo $this->errorMsg(NO_SERVER_CONNECT);
            die();
        }
        $newsQueryArr = array(
            'title' => $div . "//span[@class='title']",
            'newsUrl' => $div . "//a[@class='link_overlay']",
            'imageUrl' => $div . "//div[@class='image']//img",
            'summary' => $div . "//div[@class='summery']",
            'dateTime' => $div . "//span[contains(@class,'time') and contains(@class,'aitm')]"
        );
        //var_dump($pageSourceHtml);
        //exit();
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

//return $newsTitleObj;

        $response = array();
        $i = 0;
        foreach ($newsTitleObj as $a) {
            $newsInfo['news_id'] = $i;
            $newsInfo['title'] = $newsTitleObj->item($i)->nodeValue; // news title
            $newsInfo['summary'] = $newsSummaryObj->item($i)->nodeValue;  // summary
            $newsInfo['news_url'] = "http://www.prothom-alo.com/" . $newsUrlObj->item($i)->getAttribute('href'); //new url
            $newsInfo['image_url'] = "http:".$newsImageObj->item($i)->getAttribute('src');  // image url
            $newsInfo['date_time'] = $newsTimeObj->item($i)->nodeValue;  // date time

            $response[] = $newsInfo;
            $i++;
        }
        if ($response) {
            return $response;
        }
        return false;
    }

}
