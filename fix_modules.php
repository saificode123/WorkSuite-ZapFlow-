<?php
$map = [
    'booking' => 'bookings',
    'relation' => 'umrah_setup',
    'sector' => 'umrah_setup',
    'service_provider' => 'service_providers',
    'ticket_invoice' => 'ticketing',
    'transport_route' => 'umrah_setup',
    'transport_type' => 'umrah_setup',
    'voucher' => 'vouchers',
];

$dir = new DirectoryIterator('app/Http/Controllers');
foreach ($dir as $fileinfo) {
    if (!$fileinfo->isDot() && $fileinfo->getExtension() == 'php') {
        $path = $fileinfo->getPathname();
        $content = file_get_contents($path);
        $original = $content;
        
        foreach ($map as $bad => $good) {
            $content = preg_replace(
                "/abort_403\(\!in_array\('$bad', \\\$this->user->modules\)\);/",
                "abort_403(!in_array('$good', \$this->user->modules));",
                $content
            );
        }
        
        if ($content !== $original) {
            file_put_contents($path, $content);
            echo "Fixed $path\n";
        }
    }
}
echo "Done.\n";
