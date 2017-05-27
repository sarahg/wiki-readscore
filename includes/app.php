<?php
/**
 * @file app.php
 */

/**
 * Class ArticleList
 *
 * Retrieves a list of articles from a Wikipedia category, sorted by
 * readability score.
 */
class ArticleLister
{
  public $category;
  public $articles;

  public function __construct($category)
  {
    $this->_category = $category;

    $articles = $this->getArticles($category);
    $results = $this->buildSortableList($articles);
    $this->render($results);
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

    // @todo some sorta error handling.

    $results = json_decode($response, TRUE);

    return $results;
  }

  /**
   * Builds a list of articles.
   *
   * @param $articles
   */
  protected function buildSortableList($articles)
  {
    return $articles['query']['categorymembers'];
  }

  /**
   * Returns an HTML table with results.
   *
   * @param $results
   * @return string
   */
  protected function render($results)
  {
    if (empty($results)) {
      echo 'No results found.';
      exit();
    }

    $output = '<table><thead><td>Title</td></thead><tbody>';
    foreach ($results as $row) {
      $output .= '<tr><td>'. $row['title'] .'</td></tr>';
    }
    $output .= '</tbody></table>';

    echo $output;
  }

}

$category = $_POST['category'];
new ArticleLister($category);

?>