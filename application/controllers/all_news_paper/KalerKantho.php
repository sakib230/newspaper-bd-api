<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class KalerKantho extends My_Controller {
	function __construct() {
        parent::__construct();
        define('COMMON_DIV_1', "//div[contains(@class,'col-xs-12')and contains(@class, 'col-sm-6')and contains(@class, 'col-md-6')and contains(@class, 'n_row')]");
        
        define('NATIONAL_URL', "http://www.kalerkantho.com/online/national/");
		define('POLITICS_URL', "http://www.kalerkantho.com/online/Politics/");
		define('COURT_URL', "http://www.kalerkantho.com/online/Court/");
		define('WORLD_URL', "http://www.kalerkantho.com/online/world/");
		define('COUNTRYNEWS_URL', "http://www.kalerkantho.com/online/country-news/");
		define('BUSINESS_URL', "http://www.kalerkantho.com/online/business/");
		define('SPORT_URL', "http://www.kalerkantho.com/online/sport/");
		define('ENTERTAINMENT_URL', "http://www.kalerkantho.com/online/entertainment/");
    }
	
	public function index() {
        echo json_encode("Unavailable");
    }
	
	public function getNewspaperMenu() {
        $menu['is_success'] = "Y";
        $menu['menu_name'] = array('জাতীয়', 'রাজনীতি', 'আইন-আদালত', 'বিদেশ', 'সারাবাংলা', 'বাণিজ্য', 'খেলাধুলা', 'বিনোদন');
        echo json_encode($menu, JSON_UNESCAPED_UNICODE);
    }
	
	public function getNational() {
        $this->mainProcess(NATIONAL_URL, COMMON_DIV_1);
    }
	
	public function getPolitics() {
        $this->mainProcess(POLITICS_URL, COMMON_DIV_1);
    }
	
	public function getCourt() {
        $this->mainProcess(COURT_URL, COMMON_DIV_1);
    }
	
	public function getWorld() {
        $this->mainProcess(WORLD_URL, COMMON_DIV_1);
    }
	
	public function getCountryNews() {
        $this->mainProcess(COUNTRYNEWS_URL, COMMON_DIV_1);
    }
	
	public function getBusiness() {
        $this->mainProcess(BUSINESS_URL, COMMON_DIV_1);
    }
	
	public function getSport() {
        $this->mainProcess(SPORT_URL, COMMON_DIV_1);
    }
	
	public function getEntertainment() {
        $this->mainProcess(ENTERTAINMENT_URL, COMMON_DIV_1);
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
        $newsDetailsObj = $xpath->query("//div[@class='some-class-name2']");
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

        $pageSourceHtml = $this->newsPaperPageSource($pageSourceUrl . $newsId);

        if (!$pageSourceHtml) {
            echo $this->errorMsg(NO_SERVER_CONNECT);
            die();
        }
        $newsQueryArr = array(
            'title' => $div . "//a[@class='title']",
            'newsUrl' => $div . "//a[@class='title']",
            'imageUrl' => $div . "//img[@class='img-responsive']",
            'summary' => $div . "//div[@class='col-xs-8 summary']"
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

        $response = array();
        $i = 0;
        foreach ($newsTitleObj as $a) {
            $newsInfo['news_id'] = $i;
            $newsInfo['title'] = $newsTitleObj->item($i)->nodeValue; // news title
            $newsInfo['summary'] = $newsSummaryObj->item($i)->nodeValue;  // summary
            $newsInfo['news_url'] = "http://www.kalerkantho.com/" . $newsUrlObj->item($i)->getAttribute('href'); //new url
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











