<?php

/**
 * Class ArticleLister
 *
 * Retrieves a list of articles from a Wikipedia category, sorted by
 * readability score.
 */
class ArticleLister
{

  public $category;
  public $articles;
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
   * @param $category
   * @return array
   *   Array of matched articles.
   */
  protected function getArticles($category)
  {
    $limit = 50;
    $url = 'https://en.wikipedia.org/w/api.php?action=query&list=categorymembers&cmtitle=Category:'. $category .'&cmlimit='. $limit . '&format=json';

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
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
   * Formats scored articles into an array for rendering.
   *
   * @param $articles
   */
  protected function getArticleReadScores($articles)
  {
    $scored_articles = array();

    // Loop through results and collect their IDs, titles, first paragraph
    // content, and readability scores.
    foreach ($articles['query']['categorymembers'] as $article) {

      $id = $article['pageid'];
      $first_paragraph = $this->getArticleFirstParagraph($id);

      $scored_articles[$id] = array(
        'title' => $article['title'],
        'readscore' => $this->calculateReadScore($first_paragraph),
      );

    }

    return $scored_articles;
  }

  /**
   * Get the first paragraph of an article.
   *
   * @param int $page_id
   *   The article's page ID.
   * @return string
   */
  protected function getArticleFirstParagraph($page_id)
  {
    return ''; // @todo
  }

  /**
   * Calculate the readability score of given text.
   *
   * @param string $text
   * @return int
   */
  protected function calculateReadScore($text)
  {
    return rand(0, 100); // @todo
  }

  /**
   * Returns an HTML table with results.
   *
   * @param $results
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
   * @return string
   */
  public function __toString()
  {
    return $this->results_table;
  }

}