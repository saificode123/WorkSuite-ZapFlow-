<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::create(
        '/account/login', 'GET'
    )
);

// Get an admin user
$user = App\Models\User::find(1);
Auth::login($user);

$routes = [
    '/account/hotels/create',
    '/account/discounts',
    '/account/airlines/create',
    '/account/transporters/create',
    '/account/iata'
];

foreach ($routes as $route) {
    echo "Testing Route: $route\n";
    try {
        $request = Illuminate\Http\Request::create($route, 'GET');
        // If it's a datatable route, it needs ajax
        if (in_array($route, ['/account/discounts', '/account/iata'])) {
            $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        }
        $response = $kernel->handle($request);
        echo "Status: " . $response->getStatusCode() . "\n";
        if ($response->getStatusCode() >= 500) {
            echo substr($response->getContent(), 0, 500) . "\n";
        }
    } catch (\Exception $e) {
        echo "Exception: " . $e->getMessage() . "\n";
        echo $e->getTraceAsString() . "\n";
    }
    echo "--------------------------\n";
}
