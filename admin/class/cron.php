<?php
const CRON_FILENAME = DATA_DIR.'/rmaps.crontab';
const CRON_LOCKFILE = DATA_DIR.'/rmaps.cronlock';

class CRON {	
	public static function add($period, $custom, $newId){
		$period = ($period == 'custom') ? $custom : '@'.$period;
		$cron_line = $period.' /usr/bin/php /var/www/html/admin/rmap_update_data.php '.$newId."\n";
		
		# insert at our crontab file
		$lp = fopen(CRON_LOCKFILE, "w");
		if (flock($lp, LOCK_EX)) {  // acquire an exclusive lock

			$content = '';
			if(is_file(CRON_FILENAME)){
				$content = file_get_contents(CRON_FILENAME);
			}
			$content .= $cron_line;

			file_put_contents(CRON_FILENAME, $content);

			# update our crontab
			shell_exec('sudo /usr/local/bin/rmaps_crontab.sh '.CRON_FILENAME);

			flock($lp, LOCK_UN);    // release the lock
		} else {
			echo "Error: Couldn't get the lock!";
		}
		fclose($lp);
	}
	
	public static function remove($newId){
		$id_at_end = ' '.$newId."\n";
		
		$lp = fopen(CRON_LOCKFILE, "w");
		if (flock($lp, LOCK_EX)) {  // acquire an exclusive lock
			
			# skip lines ending on our map id
			$lines = file(CRON_FILENAME);
			$fp = fopen(CRON_FILENAME, "w");
			foreach($lines as $line){
				if(!str_ends_with($line, $id_at_end)){
					fwrite($fp, $line);
				}
			}
			fclose($fp);

			# update our crontab
			shell_exec('sudo /usr/local/bin/rmaps_crontab.sh '.CRON_FILENAME);

			flock($lp, LOCK_UN);    // release the lock
			
		} else {
			echo "Error: Couldn't get the lock!";
		}
		fclose($lp);
	}
	
	public static function update($period, $custom, $newId){
		$period = ($period == 'custom') ? $custom : '@'.$period;
		$cron_line = $period.' /usr/bin/php /var/www/html/admin/rmap_update_data.php '.$newId."\n";
		$id_at_end = ' '.$newId."\n";
					
		$lp = fopen(CRON_LOCKFILE, "w");
		if (flock($lp, LOCK_EX)) {  // acquire an exclusive lock
			
			# skip lines ending on our map id
			$lines = file(CRON_FILENAME);
			$fp = fopen(CRON_FILENAME, "w");
			foreach($lines as $line){
				if(str_ends_with($line, $id_at_end)){
					$line = $cron_line;
				}
				fwrite($fp, $line);
			}
			fclose($fp);

			# update our crontab
			shell_exec('sudo /usr/local/bin/rmaps_crontab.sh '.CRON_FILENAME);

			flock($lp, LOCK_UN);    // release the lock
			
		} else {
			echo "Error: Couldn't get the lock!";
		}
		fclose($lp);
	}
	
	public static function get($newId){
		$cron = ['cron_period' => 'never', 'cron_custom' => '*/30 * * * *'];
		$cron_line = null;
		$id_at_end = ' '.$newId."\n";

		$lp = fopen(CRON_LOCKFILE, "w");
		if (flock($lp, LOCK_SH)) {  // acquire an shared read lock
			
			# skip lines ending on our map id
			$lines = file(CRON_FILENAME);
			foreach($lines as $line){
				if(str_ends_with($line, $id_at_end)){
					$cron_line = $line;
					break;
				}
			
			}
			flock($lp, LOCK_UN);    // release the lock
			
		} else {
			echo "Error: Couldn't get the lock!";
		}
		fclose($lp);
		
		if($cron_line){
			$vars = explode(' ', $cron_line);
			if(str_starts_with($vars[0], '@')){
				$cron['cron_period'] = substr($vars[0], 1);
			}else{
				$cron['cron_period'] = 'custom';
				$cron['cron_custom'] = $vars[0].' '.$vars[1].' '.$vars[2].' '.$vars[3].' '.$vars[4];
			}
		}
		return $cron;
		
	}
}

?>