# uptrends-php-wrapper
PHP Wrapper for the Uptrends.com API. Note: work still in progress!

# Class documentation

## Initializing the class
```php
$uptrends = new Uptrends(array(
  'username' => '<username on uptrends.com>',
  'password' => '<password on uptrends.com>'
));
```

## Sending requests
All post, delete, put, and get requests are handled by the magical function __call. See examples below.

See full API documentation on https://www.uptrends.com/support/kb/api/documentation

### Examples
#### GET calls
```php
$probes = $uptrends->getProbes();

// Decode JSON result
$json = json_decode($probes);

echo '<pre>'
echo print_r($json, true);
echo '</pre>';
```

#### POST/PUT calls
```php
$postCall = $uptrends->postProbes(array(
  'postfields' => array(
    'name' => 'Monitor name',
    'URL' => 'URL to be monitored',
    // ect.
  )
));
```

#### DELETE calls
```php
$deleteCall = $uptrends->deleteProbes(array(
  'prefix' => 'Guid'
));
```

#### Used array keys
* prefix (prefix for the URL)
* postfields (fields to be posted)
* arguments (parameters in the URL)

