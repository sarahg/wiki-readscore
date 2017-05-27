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
  public $results_table;

  public function __construct($category)
  {
    $this->_category = $category;

    $articles = $this->getArticles($category);
    $results = $this->buildSortableList($articles);
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
    foreach ($results as $row) {
      $output[] = '<tr>';
      $output[] = '<td>'. $row['title'] .'</td>';
      $output[] = '<td class="int">0</td>';
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

$category = $_POST['category'];
echo new ArticleLister($category);

?>