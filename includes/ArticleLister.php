<?php

namespace Readscore;

use DaveChild\TextStatistics as TS;

/**
 * Class ArticleLister
 *
 * Retrieves a list of articles from a Wikipedia category,
 * sorted by readability score.
 */
class ArticleLister
{

  public $category;
  public $results_table;

  public function __construct($category)
  {
    $this->_category = $category;

    $articles = $this->getArticles($category);
    $results = $this->getArticleReadScores($articles);

    $this->results_table = $this->render($results);
  }


  /**
   * Retrieves articles from Wikipedia.
   *
   * @param string $category
   *   A Wikipedia category name, provided by a user.
   * @return array
   *   Array of articles in the given category.
   */
  protected function getArticles($category)
  {
    $limit = 50;
    $request_url = 'https://en.wikipedia.org/w/api.php?action=query&list=categorymembers&cmtitle=Category:'. $category .'&cmlimit='. $limit . '&format=json';
    $results = $this->wikipediaAPIRequest($request_url);
    return $results;
  }


  /**
   * Formats scored articles into an array for rendering.
   *
   * @param $articles
   * @return array
   *   Associative array of article titles and readscores, keyed by page ID.
   */
  protected function getArticleReadScores($articles)
  {
    $scored_articles = array();

    // Loop through results and collect their IDs, titles, first paragraph
    // content, and readability scores.
    foreach ($articles['query']['categorymembers'] as $article) {

      $id = $article['pageid'];
      $first_paragraph = $this->getArticleFirstParagraph($article['title']);

      $scored_articles[$id] = array(
        'title' => $article['title'],
        'readscore' => $this->calculateReadScore($first_paragraph),
      );

      uasort($scored_articles, array($this,'sortByReadscore'));

    }

    return $scored_articles;
  }


  /**
   * Returns an HTML table with results.
   *
   * @param array $results
   * @return string
   */
  protected function render($results)
  {
    $output = array();

    if (empty($results)) {
      echo 'No results found.'; // @todo this could be more helpful
      exit();
    }

    // Build a table for results.
    $output[] = '<table><thead>';

    // Table header.
    $output[] = '<th>Title</th>';
    $output[] = '<th class="int">Readability score</th>';
    $output[] = '</thead><tbody>';

    // Table rows.
    foreach ($results as $page_id => $data) {
      $output[] = '<tr>';
      $output[] = '<td><a title="View article on Wikipedia" href="https://en.wikipedia.org/?curid='. $page_id .'">'. $data['title'] .'</a></td>';
      $output[] = '<td class="int">'. $data['readscore'] .'</td>';
      $output[] = '</tr>';
    }

    $output[] = '</tbody></table>';

    return '<div class="wrapper">' . implode('', $output) . '</div>';
  }


  /**
   * Make an API request to Wikipedia using cURL.
   *
   * @param string $request_url
   * @return array
   */
  public function wikipediaAPIRequest($request_url)
  {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $request_url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($curl);

    curl_close($curl);

    // @todo some sorta error handling
    // try/catch maybe, perhaps check HTTP return code

    $results = json_decode($response, TRUE);
    return $results;
  }


  /**
   * Get the first paragraph of an article.
   *
   * @param string $title
   *   The article's title.
   * @return string
   */
  protected function getArticleFirstParagraph($title)
  {
    $first_paragraph = '';

    // Make an API call to Wikipedia.
    $request_url = 'https://en.wikipedia.org/w/api.php?action=parse&page='. $title .'&format=json&prop=text&section=0';
    $result = $this->wikipediaAPIRequest($request_url);

    // Use DOMDocument to extract the first paragraph.
    if (isset($result['parse']['text']['*'])) {
      $dom = new \DOMDocument;
      libxml_use_internal_errors(TRUE);
      $dom->loadHTML($result['parse']['text']['*']);
      $nodes = $dom->getElementsByTagName('p');
      $first_paragraph = $nodes->item(0)->nodeValue;
    }

    return $first_paragraph;
  }


  /**
   * Calculate the readability score of given text.
   *
   * @param string $text
   * @return int
   *   Readability score for the text.
   */
  protected function calculateReadScore($text)
  {
    $textStatistics = new TS\TextStatistics;
    return $textStatistics->fleschKincaidReadingEase($text);
  }


  /**
   * Compare readscores in order to create an ordered array.
   *
   * @param $a
   * @param $b
   * @return int
   */
  private static function sortByReadscore($a, $b)
  {
    $a = $a['readscore'];
    $b = $b['readscore'];

    if ($a == $b)
    {
      return 0;
    }

    return ($a < $b) ? -1 : 1;
  }


  /**
   * @return string
   */
  public function __toString()
  {
    return $this->results_table;
  }

}