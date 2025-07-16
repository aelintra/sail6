<?php

    $var=array();

        $iowait = `iostat -c|awk '/^ /{print $4}'`;
        $var['iowait'] = trim($iowait); 

        $free = shell_exec('free');
        $free = (string)trim($free);
        $free_arr = explode("\n", $free);
        $mem = explode(" ", $free_arr[1]);
        $mem = array_filter($mem);
        $mem = array_merge($mem);
        $memory_usage = round($mem[2]/$mem[1]*100); 
        $var['mem'] = $memory_usage;
        
        $ccount = `sudo /usr/sbin/asterisk -rx 'core show channels count'`;
        $acount = explode(PHP_EOL,$ccount);
        
        preg_match("/^(\d+)/",$acount[1],$matches);
        $var['upcalls'] = $matches[1];

        $rootdev = trim (`df -P / | tail -n 1 | awk '/.*/ { print $1 }'`);
        $diskusage = `df --output=pcent $rootdev | tr -dc '0-9'`;
        $var['disk'] = $diskusage; 

        $numCpus = `/bin/grep -c ^processor /proc/cpuinfo`; 
        $numCpus = trim($numCpus);
        $var['numCpus'] = $numCpus;   
        
        $var['uptime'] = trim(`uptime | awk -F'up ' '{print $2}' | awk -F',' '{print $1}'`);
        $cpu_usage = sys_getloadavg();
        $var['lga'] = round($cpu_usage[0],2 );
        $var['lgb'] = round($cpu_usage[1],2 );
        $var['lgc'] = round($cpu_usage[2],2 );


//    syslog(LOG_WARNING, "system.php sending values");

    if ($var) {      
       echo  json_encode($var, JSON_NUMERIC_CHECK);
    }
?>