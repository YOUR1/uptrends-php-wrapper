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
##### List probes
```php
$probes = $uptrends->getProbes();
```

##### Probe statistics
```php
$today = new DateTime('now');
$lastYear = $today->modify('-1 year');
$format = 'Y/m/d';

$probeStatistics = $uptrends->getProbes(array(
  'prefix' => 'GUID/statistics',
  'parameters' => array(
		'start' => $lastYear->format($format),
		'end' => $today->format($format),
		'dimension' => 'Week'
	)
));
```

##### Probegroup statistics:
```php
$today = new DateTime('now');
$lastYear = $today->modify('-1 year');
$format = 'Y/m/d';

$probes = $uptrends->getProbegroups(array(
  'prefix' => 'GUID/statistics',
	'parameters' => array(
		'start' => $lastYear->format($format),
		'end' => $today->format($format),
		'dimension' => 'Week'
	)
));
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
* parameters (parameters in the URL)


# Authors
* Youri van den Bogert (<yvdbogert@archixl.nl>)
