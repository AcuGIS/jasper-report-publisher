<?php
    class backend_Class
    {
			function systemd($op, $id){
				exec('systemctl '.$op.' uwsgi.service', $output, $retval);
				return 0;
			}
			
			function parseSystemd($output){
				$rv = [];
				foreach($output as $l){
					if(preg_match('/^\s+([\w\s]+):\s(.*)/is', $l, $m) === 1){
						$k = strtolower($m[1]);
						if($k == 'loaded'){
							# Loaded: loaded (/etc/systemd/system/tomcat.service; disabled; vendor preset: enabled)
							if((count($m) >= 2) && str_contains($m[2], 'preset')){
								$t = explode(';', $m[2]);
								$rv['enabled'] = trim($t[1]);
							}
						}
						$rv[$k] = $m[2];
					}else if(preg_match('/([0-9]+) \/usr\/bin\/uwsgi .* \-\-ini \/etc\/uwsgi\/apps\-enabled\/(.*)\.ini/', $l, $m)){
						$pid = $m[1];
						$app = $m[2];
						if(isset($rv[$app])){
							array_push($rv[$app], $pid);
						}else{
							$rv[$app] = array($pid);
						}
					}
				}
				return $rv;
			}

			function svc_ctl($name, $op){
				exec('sudo /usr/local/bin/svc_ctl.sh '.$name.' '.$op, $output, $retval);
				return $retval;
			}

			function uwsgi_status(){
				exec('systemctl status uwsgi.service', $output, $retval);
				$status = $this->parseSystemd($output);
				if(!isset($status['enabled'])){
					$status['enabled'] = is_link('/etc/rc5.d/S01uwsgi') ? 'enabled' : 'not';
				}
				return $status;
			}
			
			function tomcat_status(){
				exec('systemctl status tomcat.service', $output, $retval);
				$status = $this->parseSystemd($output);
				return $status;
			}
		}
			