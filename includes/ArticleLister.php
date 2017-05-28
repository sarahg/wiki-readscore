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
    $request_url = 'https://en.wikipedia.org/w/api.php?action=query&list=categorymembers&cmprop=ids|title|type&cmtitle=Category:'. $category .'&cmlimit='. $limit . '&format=json';
    $results = $this->wikipediaAPIRequest($request_url);

    // Drop sub-category pages and portal pages.
    if (isset($results['query']['categorymembers'])) {
      foreach ($results['query']['categorymembers'] as $key => $item) {
        if ($item['type'] !== 'page' || strpos($item['title'], 'Portal:') !== FALSE) {
          unset($results['query']['categorymembers'][$key]);
        }
      }
    }

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

    if (isset($articles['query']['categorymembers'])) {

      // Loop through results and collect their IDs and titles.
      foreach ($articles['query']['categorymembers'] as $article) {
        $id = $article['pageid'];
        $titles[] = $article['title'];
        $scored_articles[$id]['title'] = $article['title'];
        $scored_articles[$id]['readscore'] = '--';
      }

      if (!empty($titles)) {
        // Retrieve extracts from all the articles.
        $paragraphs = $this->getArticleFirstParagraphs($titles);

        // Add extracts and readscores to our list.
        foreach ($paragraphs as $id => $text) {
          $scored_articles[$id]['text'] = $text;
          $scored_articles[$id]['readscore'] = $this->calculateReadScore($text);
        }

        // Sort the list by score, lowest to highest.
        uasort($scored_articles, array($this,'sortByReadscore'));
      }
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
   * @return array
   */
  protected function getArticleFirstParagraphs($titles)
  {
    $extracts = array();
    $first_paragraphs = array();

    $titles = rawurlencode(implode('|', $titles));

    // Send continuing queries to the Wikipedia API.
    $request_url = 'https://en.wikipedia.org/w/api.php?format=json&action=query&prop=extracts&titles='. $titles;
    $i = 0;
    do {
      $request_url = $request_url . '&excontinue=' . $i;
      $results = $this->wikipediaAPIRequest($request_url);

      foreach ($results['query']['pages'] as $id => $row) {
        if (isset($row['extract'])) {
          $extracts[$id] = $row['extract'];
        }
      }

      $i++;

    } while(isset($results['continue']));


    // Use DOMDocument to extract the first paragraph of each result.
    foreach ($extracts as $id => $content) {
      if ($content) {
        $dom = new \DOMDocument;
        libxml_use_internal_errors(TRUE);
        $dom->loadHTML($content);
        $nodes = $dom->getElementsByTagName('p');
        $first_paragraphs[$id] = $nodes->item(0)->nodeValue;
      }
    }

    return $first_paragraphs;
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