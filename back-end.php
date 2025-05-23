<?php
function fetchArticles() {
    $url = "https://www.theverge.com";
    $baseUrl = "https://www.theverge.com";

    $context = stream_context_create([
        'http' => ['header' => 'User-Agent: Mozilla/5.0']
    ]);

    $htmlContent = @file_get_contents($url, false, $context);
    if (!$htmlContent) return [];

    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML($htmlContent);
    libxml_clear_errors();

    $xpath = new DOMXPath($dom);
    $nodes = $xpath->query("//a[contains(@href, '/202') and starts-with(@href, '/202')]");

    $articles = [];
    foreach ($nodes as $node) {
        $title = trim($node->textContent);
        $link = $node->getAttribute('href');

        if (empty($title) || strlen($title) < 5) continue;

        if (strpos($link, 'http') !== 0) {
            $link = rtrim($baseUrl, '/') . '/' . ltrim($link, '/');
        }

        if (strpos($link, 'theverge.com') === false) continue;

        if (preg_match('/\/(\d{4})\/(\d{2})\/(\d{2})\//', $link, $matches)) {
            $date = strtotime("{$matches[1]}-{$matches[2]}-{$matches[3]}");
            if ($date >= strtotime("2022-01-01")) {
                $articles[] = [
                    'title' => $title,
                    'link' => $link,
                    'date' => $date
                ];
            }
        }
    }
    usort($articles, function($a, $b) {
        return $b['date'] - $a['date'];
    });

    return $articles;
}

$articles = fetchArticles();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Title Aggregator - The Verge</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: white;
            color: black;
        }
        h1 {
            text-align: center;
        }
        .article-list {
            list-style-type: none;
            padding: 0;
        }
        .article-item {
            margin-bottom: 10px;
        }
        .article-item a {
            text-decoration: none;
            color: black;
        }
        .article-item a:hover {
            color: gray;
        }
    </style>
</head>
<body>
    <h1>Article Titles from The Verge (Since January 1, 2022)</h1>
    <ul class="article-list">
        <?php foreach ($articles as $article): ?>
            <li class="article-item">
                <a href="<?= $article['link'] ?>" target="_blank"><?= $article['title'] ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
