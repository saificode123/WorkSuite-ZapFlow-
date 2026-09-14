<?php
$files = [
    'app/Http/Controllers/AirlineController.php', 
    'app/Http/Controllers/IataController.php', 
    'app/DataTables/Travel/AirlineDataTable.php', 
    'app/DataTables/Travel/IataDataTable.php', 
    'resources/views/travel/airlines/ajax/create.blade.php', 
    'resources/views/travel/airlines/ajax/edit.blade.php', 
    'resources/views/travel/iata/ajax/create.blade.php', 
    'resources/views/travel/iata/ajax/edit.blade.php'
];

foreach($files as $f) { 
    if(file_exists($f)) { 
        $c = file_get_contents($f); 
        $c = str_replace('iata_code', 'code', $c); 
        file_put_contents($f, $c); 
        echo "Fixed $f\n"; 
    } 
}
