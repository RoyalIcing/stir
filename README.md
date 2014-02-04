stir
====

Record time taken in your PHP website, and display as HTML or JSON.

## Enable and disable easily
(When disabled falls back to safe-to-use empty functions)

```php
// For example in WordPress, only enable if an administrator user is logged in.
define ('STIR_ENABLED', current_user_can('administrator'));
```

## Measure times
```php
// Start measuring:
function displayPage() // example template function
{
	stir('display page');

	displayHTMLHead();
	stirring('display page', 'html head');

	displayNavigation();
	stirring('display page', 'nav');

	$articles = retrieveLatestArticles();
	$articleIndex = 0;
	foreach ($articles as $article):
		stir('display article');
		displayArticle($article);
		stirred('display article');
		$articleIndex++;
	endforeach;
	stirring('display page', 'articles');

	displayFooter();
	stirring('display page', 'footer');

	displayHTMLEnd();
	stirred('display page');
}
```

## Display recorded times in HTML
```php
// End of page.
stirDisplayRecordedTimesForHTML();
?>
</body>
</html>
<?php
```

## Display recorded times in a JSON response
```php
$action = 'get-user-favorites';
$info = getInfoForUserFavorites();

// The following function still works when stir is set to disabled.
stirDisplayJSONInfo($info, $action);
```