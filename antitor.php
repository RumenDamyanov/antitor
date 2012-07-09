<?php
/**
 * @name antitor.php
 * @author Rumen Damyanov <dev@rumenx.com>
 */

/* CONFIG */

$ip = '';               // your server ip address (only ipv4 supported)
$ht = '.htaccess';      // path to your .htaccess file (or '' for none)
$apf = 'antitor.rules'; // path to your apf rules file (or '' for none)

/* DON'T EDIT BELOW */

if(empty($ip) || (empty($ht) && empty($apf))) break;

$feed = 'http://check.torproject.org/cgi-bin/TorBulkExitList.py?ip='.$ip;
$feed_tmp = 'tmp.txt';
@file_put_contents($feed_tmp, file_get_contents($feed));

if (!empty($apf))
{
    @file_put_contents($apf, file_get_contents($feed_tmp));
}

if (!empty($ht))
{
    $ht_read = @fopen($ht, 'r');
    $start = $end = false;
    $count = 0;

    if ($ht_read)
    {
        while(!feof($ht_read))
        {
            $line = fgets($ht_read);
            $count++;

            if (strpos($line, "antitor.php start") !== false) $start = $count;
            if (strpos($line, "antitor.php end") !== false) $end = $count;
        }
        fclose($ht_read);
    }

    $ht_read = @fopen($ht, 'r');

    if ($ht_read)
    {
        if ($start != false && $end != false)
        {
            global $start, $end;
            $count = 0;
            $ht_tmp = "";

            while(!feof($ht_read))
            {
                $line = fgets($ht_read);
                $count++;

                if ($count < $start || $count > $end) $ht_tmp .= $line;  
            }
            fclose($ht_read);
            unlink($ht);
            @file_put_contents($ht, $ht_tmp);
        }
        }

    $ht_write = @fopen($ht, 'a');
    $feed_read = @fopen($feed_tmp, "r");

    if ($feed_read)
    {
        $header = "\n### antitor.php start (don't remove this line)\norder allow, deny\n";
        $footer = "allow from all\n### antitor.php end (don't remove this line)\n";
        @fwrite($ht_write, $header);

        while(!feof($feed_read))
        { 
                $line = fgets($feed_read);
                if (strlen($line) < 16 && strlen($line) > 6)
                {
                        $line = 'deny from ' . $line;
                        @fwrite($ht_write, $line);
                }
        }
        fclose($feed_read);
    }
    @fwrite($ht_write, $footer);
    fclose($ht_write);
}
unlink($feed_tmp);