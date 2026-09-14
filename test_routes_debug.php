<?php
putenv('APP_DEBUG=true');
$_ENV['APP_DEBUG'] = 'true';
$_SERVER['APP_DEBUG'] = 'true';

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::find(1);
if (!$user) { die("No user"); }
Auth::login($user);

$routes = [
    '/account/hotels/create',
    '/account/discounts',
    '/account/airlines/create'
];

foreach ($routes as $route) {
    echo "Testing Route: $route\n";
    $request = Illuminate\Http\Request::create($route, 'GET');
    $request->headers->set('Accept', 'application/json');
    if (in_array($route, ['/account/discounts', '/account/iata'])) {
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
    }
    $response = $kernel->handle($request);
    echo "Status: " . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() >= 500) {
        $content = json_decode($response->getContent(), true);
        if ($content && isset($content['message'])) {
            echo "Exception: " . $content['message'] . "\n";
            echo "File: " . $content['file'] . ":" . $content['line'] . "\n";
        } else {
            echo substr(strip_tags($response->getContent()), 0, 500) . "\n";
        }
    }
    echo "--------------------------\n";
}
