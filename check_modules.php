<?php
$files = glob('app/Http/Controllers/*.php');
$valid = ['projects','tickets','invoices','estimates','events','messages','tasks','timelogs','contracts','notices','payments','orders','knowledgebase','bookings','vouchers','clients','employees','attendance','expenses','leaves','leads','holidays','products','reports','settings','bankaccount','accounts','umrah_setup','service_providers','ticketing','customer_types'];
foreach($files as $f) {
    $c = file_get_contents($f);
    if(preg_match_all('/abort_403\(\!in_array\(\'([a-z_]+)\', \$this->user->modules\)\)/', $c, $m)) {
        foreach($m[1] as $mod) {
            if(!in_array($mod, $valid)) {
                echo basename($f).': '.$mod."\n";
            }
        }
    }
}
