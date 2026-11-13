# Titan-Network-Filter PHP SDK

`composer require titancsa/titan-network-filter`

Windows x64 PHP8.2+; enable FFI extension and `ffi.enable=true`.

```php
require "vendor/autoload.php";
$client=new \TitanCSA\Products\TitanNetworkFilter\Client();
try {print_r($client->call("status"));} finally {$client->close();}
```

[API and operation schema](docs/API.md). Matching driver installed separately.
